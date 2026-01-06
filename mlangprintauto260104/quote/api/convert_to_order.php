<?php
/**
 * 견적서 → 주문 변환 API
 * POST 요청으로 승인된 견적서를 주문으로 변환
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../includes/QuoteManager.php';
require_once __DIR__ . '/../../../includes/EmailNotification.php';

function jsonResponse($success, $data = [], $message = '') {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// 고정 9개 품목 타입
const FIXED_PRODUCTS = [
    'inserted',      // 전단지
    'namecard',      // 명함
    'envelope',      // 봉투
    'sticker',       // 스티커
    'msticker',      // 자석스티커
    'cadarok',       // 카다록
    'littleprint',   // 포스터/소량인쇄
    'merchandisebond', // 상품권
    'ncrflambeau'    // NCR양식
];

// 품목명 → 타입 매핑
const PRODUCT_NAME_TO_TYPE = [
    '전단지' => 'inserted',
    '명함' => 'namecard',
    '봉투' => 'envelope',
    '스티커' => 'sticker',
    '자석스티커' => 'msticker',
    '카다록' => 'cadarok',
    '포스터' => 'littleprint',
    '상품권' => 'merchandisebond',
    'NCR양식' => 'ncrflambeau'
];

try {
    $manager = new QuoteManager($db);

    // 견적서 ID
    $quoteId = intval($_POST['quote_id'] ?? 0);
    if (!$quoteId) {
        jsonResponse(false, [], '견적서 ID가 필요합니다.');
    }

    // 확인 플래그
    $confirm = ($_POST['confirm'] ?? '0') === '1';
    if (!$confirm) {
        jsonResponse(false, [], '변환 확인이 필요합니다.');
    }

    // 견적서 조회
    $quote = $manager->getQuoteById($quoteId);
    if (!$quote) {
        jsonResponse(false, [], '견적서를 찾을 수 없습니다.');
    }

    // 상태 확인 (accepted 또는 sent 상태만 변환 가능)
    if (!in_array($quote['status'], ['accepted', 'sent'])) {
        jsonResponse(false, [], '승인된 견적서만 주문으로 변환할 수 있습니다. (현재 상태: ' . $quote['status'] . ')');
    }

    // 이미 변환된 경우
    if ($quote['status'] === 'converted' || !empty($quote['converted_order_no'])) {
        jsonResponse(false, [], '이미 주문으로 변환된 견적서입니다. (주문번호: ' . $quote['converted_order_no'] . ')');
    }

    // 견적서 품목 조회
    $items = $manager->getQuoteItems($quoteId);
    if (empty($items)) {
        jsonResponse(false, [], '견적서에 품목이 없습니다.');
    }

    // 트랜잭션 시작
    $db->begin_transaction();

    try {
        $createdOrders = [];
        $orderGroupId = $quote['quote_no'] . '-G' . time();
        $firstOrderNo = null;

        // 배송 주소 파싱
        $deliveryAddress = $quote['delivery_address'] ?? '';
        $zip1 = $deliveryAddress;
        $zip2 = '';

        // 주소에서 상세주소 분리 시도 (쉼표나 공백 기준)
        if (strpos($deliveryAddress, ',') !== false) {
            $parts = explode(',', $deliveryAddress, 2);
            $zip1 = trim($parts[0]);
            $zip2 = isset($parts[1]) ? trim($parts[1]) : '';
        }

        foreach ($items as $index => $item) {
            // 품목 타입 결정
            $type = 'custom'; // 기본값은 직접입력

            // product_type이 있으면 사용
            if (!empty($item['product_type']) && in_array($item['product_type'], FIXED_PRODUCTS)) {
                $type = $item['product_type'];
            }
            // 품목명으로 타입 매핑 시도
            elseif (!empty($item['product_name']) && isset(PRODUCT_NAME_TO_TYPE[$item['product_name']])) {
                $type = PRODUCT_NAME_TO_TYPE[$item['product_name']];
            }

            // is_custom_product 자동 결정 (Phase B)
            $isCustomProduct = ($type === 'custom') ? 1 : 0;

            // Type_1 JSON 생성 (11개 신규 필드 포함)
            $type1Data = [
                // 기본 필드
                'product_type' => $type,
                'product_name' => $item['product_name'],
                'specification' => $item['specification'] ?? '',
                'formatted_display' => $item['formatted_display'] ?? ($item['specification'] ?? ''),
                'source' => 'quote',
                'quote_no' => $quote['quote_no'],

                // 수량/단위
                'quantity' => floatval($item['quantity']),
                'unit' => $item['unit'] ?? '개',

                // 가격 정보
                'supply_price' => intval($item['supply_price']),
                'vat_amount' => intval($item['vat_amount']),
                'total_price' => intval($item['total_price']),

                // ===== 11개 신규 필드 =====
                // 1-4. 제품 사양 필드
                'MY_type' => $item['MY_type'] ?? '',
                'PN_type' => $item['PN_type'] ?? '',
                'MY_Fsd' => $item['MY_Fsd'] ?? '',
                'POtype' => $item['POtype'] ?? '',

                // 5-6. 연수/매수 필드
                'MY_amount' => floatval($item['MY_amount'] ?? 0),
                'mesu' => intval($item['mesu'] ?? 0),

                // 7. 인쇄 타입
                'ordertype' => $item['ordertype'] ?? ''
            ];

            // 8. product_data (JSON 필드를 배열로 파싱)
            if (!empty($item['product_data'])) {
                $productData = json_decode($item['product_data'], true);
                if ($productData) {
                    $type1Data['product_data'] = $productData;
                }
            }

            // 10-11. 추가 옵션 필드
            if (!empty($item['additional_options'])) {
                $additionalOptions = json_decode($item['additional_options'], true);
                if ($additionalOptions) {
                    $type1Data['additional_options'] = $additionalOptions;
                }
            }
            $type1Data['additional_options_total'] = intval($item['additional_options_total'] ?? 0);

            // source_data가 있으면 포함 (하위 호환성)
            if (!empty($item['source_data'])) {
                $sourceData = json_decode($item['source_data'], true);
                if ($sourceData) {
                    $type1Data['calculator_data'] = $sourceData;
                }
            }

            $type1Json = json_encode($type1Data, JSON_UNESCAPED_UNICODE);

            // 가격 정보
            $supplyPrice = intval($item['supply_price']) ?? 0;
            $vatAmount = intval($item['vat_amount']) ?? 0;
            $totalPrice = intval($item['total_price']) ?? 0;

            // money 필드 설정
            $money2 = 0; // 디자인비 (견적서에서는 0)
            $money3 = $vatAmount; // VAT
            $money4 = $supplyPrice; // 공급가 (인쇄비)
            $money5 = $totalPrice; // 합계

            // 변수 준비
            $customerName = $quote['customer_name'] ?? '';
            $customerEmail = $quote['customer_email'] ?? '';
            $customerCompany = $quote['customer_company'] ?? '';
            $customerPhone = $quote['customer_phone'] ?? '';
            $deliveryType = $quote['delivery_type'] ?? '';
            $notes = $quote['notes'] ?? '';
            $productName = $item['product_name'] ?? '';
            $specification = $item['specification'] ?? '';
            $itemQuantity = floatval($item['quantity'] ?? 1);
            $itemUnit = $item['unit'] ?? '개';
            $orderSeq = $index + 1;
            $itemId = intval($item['id'] ?? 0);
            $quoteNo = $quote['quote_no'] ?? '';

            // 주문 INSERT (26개 파라미터 - Phase B: product_type, is_custom_product 추가)
            $insertQuery = "INSERT INTO mlangorder_printauto (
                Type, Type_1, product_type, is_custom_product,
                money_2, money_3, money_4, money_5,
                name, email, bizname, phone, Hendphone,
                zip1, zip2, delivery,
                cont, date, OrderStyle,
                quote_id, quote_no, quote_item_id,
                custom_product_name, custom_specification,
                quantity, unit, order_group_id, order_group_seq
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, NOW(), '2',
                ?, ?, ?,
                ?, ?,
                ?, ?, ?, ?
            )";

            $stmt = $db->prepare($insertQuery);
            if (!$stmt) {
                throw new Exception('쿼리 준비 실패: ' . $db->error);
            }

            // 26개 파라미터 타입 정의 (Phase B):
            // ss = Type, Type_1 (2)
            // si = product_type, is_custom_product (2) = 4
            // iiii = money_2, money_3, money_4, money_5 (4) = 8
            // sssss = name, email, bizname, phone, Hendphone (5) = 13
            // sss = zip1, zip2, delivery (3) = 16
            // s = cont (1) = 17
            // isi = quote_id, quote_no, quote_item_id (3) = 20
            // ss = custom_product_name, custom_specification (2) = 22
            // dssi = quantity, unit, order_group_id, order_group_seq (4) = 26
            $stmt->bind_param(
                "sssiiiiisssssssssisissdssi",
                $type, $type1Json, $type, $isCustomProduct,
                $money2, $money3, $money4, $money5,
                $customerName, $customerEmail, $customerCompany, $customerPhone, $customerPhone,
                $zip1, $zip2, $deliveryType,
                $notes,
                $quoteId, $quoteNo, $itemId,
                $productName, $specification,
                $itemQuantity, $itemUnit, $orderGroupId, $orderSeq
            );

            if (!$stmt->execute()) {
                throw new Exception('주문 생성 실패: ' . $stmt->error);
            }

            $orderNo = $db->insert_id;
            $stmt->close();

            // 첫 번째 주문번호 저장
            if ($firstOrderNo === null) {
                $firstOrderNo = $orderNo;
            }

            $createdOrders[] = [
                'no' => $orderNo,
                'Type' => $type,
                'product_name' => $productName,
                'supply_price' => $supplyPrice,
                'total_price' => $totalPrice
            ];
        }

        // 견적서 상태 업데이트
        $updateQuery = "UPDATE quotes SET status = 'converted', converted_order_no = ? WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $convertedOrderNo = (string)$firstOrderNo;
        $updateStmt->bind_param("si", $convertedOrderNo, $quoteId);

        if (!$updateStmt->execute()) {
            throw new Exception('견적서 상태 업데이트 실패: ' . $updateStmt->error);
        }
        $updateStmt->close();

        // 트랜잭션 커밋
        $db->commit();

        // 이메일 발송 (트랜잭션 외부에서 실행)
        $emailNotification = new EmailNotification();
        $order_numbers_array = array_column($createdOrders, 'no');
        $total_price = array_sum(array_column($createdOrders, 'total_price'));

        $email_sent = $emailNotification->sendOrderConvertedNotification(
            $quote['customer_email'],
            $quote['customer_name'],
            $quote['quote_no'],
            $order_numbers_array,
            count($createdOrders),
            $total_price
        );

        if (!$email_sent) {
            error_log("견적→주문 전환 이메일 발송 실패: quote_no={$quote['quote_no']}, email={$quote['customer_email']}");
        }

        jsonResponse(true, [
            'orders' => $createdOrders,
            'order_count' => count($createdOrders),
            'first_order_no' => $firstOrderNo,
            'order_group_id' => $orderGroupId,
            'quote_no' => $quote['quote_no'],
            'email_sent' => $email_sent
        ], count($createdOrders) . '건의 주문이 생성되었습니다.');

    } catch (Exception $e) {
        // 트랜잭션 롤백
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    jsonResponse(false, [], '오류: ' . $e->getMessage());
}
?>
