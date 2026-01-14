<?php
/**
 * 견적서 → 주문 전환 API
 * 승인된 견적서를 mlangorder_printauto 테이블에 주문으로 전환
 * ProcessOrder_unified.php와 동일한 형태로 저장
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// 에러 응답 함수
function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// 성공 응답 함수
function jsonSuccess($data) {
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// DB 연결
require_once __DIR__ . '/../../db.php';

if (!$db) {
    jsonError('데이터베이스 연결 실패', 500);
}

/**
 * 전단지 매수 DB 조회 (SSOT 준수 - 계산 금지)
 * @param mysqli $db DB 연결
 * @param float $reams 연 수량
 * @param string $myType 색상 (MY_type/style)
 * @param string $pnType 규격 (PN_type/Section)
 * @param string $myFsd 용지 (MY_Fsd/TreeSelect)
 * @param string $poType 인쇄면 (POtype)
 * @return int 매수 (DB에서 조회된 값, 없으면 0)
 */
function lookupInsertedSheets($db, $reams, $myType = '', $pnType = '', $myFsd = '', $poType = '') {
    // 모든 조건이 있으면 정확한 조회
    if (!empty($myType) && !empty($pnType) && !empty($myFsd) && !empty($poType)) {
        $stmt = mysqli_prepare($db, "SELECT quantityTwo FROM mlangprintauto_inserted WHERE style = ? AND Section = ? AND quantity = ? AND TreeSelect = ? AND POtype = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssdss", $myType, $pnType, $reams, $myFsd, $poType);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            if (!empty($row['quantityTwo'])) {
                return intval($row['quantityTwo']);
            }
        }
    }

    // 조건이 부족하면 수량만으로 대표값 조회 (가장 많이 사용되는 값)
    $stmt = mysqli_prepare($db, "SELECT quantityTwo, COUNT(*) as cnt FROM mlangprintauto_inserted WHERE quantity = ? AND quantityTwo > 0 GROUP BY quantityTwo ORDER BY cnt DESC LIMIT 1");
    if (!$stmt) {
        return 0;
    }
    mysqli_stmt_bind_param($stmt, "d", $reams);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return intval($row['quantityTwo'] ?? 0);
}

mysqli_set_charset($db, 'utf8mb4');

// POST JSON 데이터 파싱
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    jsonError('잘못된 요청 형식입니다.');
}

// 필수 필드 검증
$quote_id = intval($data['quote_id'] ?? $data['quotation_id'] ?? 0);
if ($quote_id <= 0) {
    jsonError('견적서 ID가 필요합니다.');
}

// 트랜잭션 시작
mysqli_begin_transaction($db);

try {
    // 1. 견적서 조회 (quotes 테이블)
    $quote_query = "SELECT * FROM quotes WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($db, $quote_query);
    mysqli_stmt_bind_param($stmt, "i", $quote_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $quote = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$quote) {
        throw new Exception('견적서를 찾을 수 없습니다.');
    }

    // 이미 전환된 견적서 확인
    if ($quote['status'] === 'converted') {
        throw new Exception('이미 주문으로 전환된 견적서입니다.');
    }

    // 2. 견적서 품목 조회 (quote_items 테이블)
    $items_query = "SELECT * FROM quote_items WHERE quote_id = ? ORDER BY item_no ASC";
    $stmt = mysqli_prepare($db, $items_query);
    mysqli_stmt_bind_param($stmt, "i", $quote_id);
    mysqli_stmt_execute($stmt);
    $items_result = mysqli_stmt_get_result($stmt);
    $items = [];
    while ($row = mysqli_fetch_assoc($items_result)) {
        $items[] = $row;
    }
    mysqli_stmt_close($stmt);

    if (empty($items)) {
        throw new Exception('견적서에 품목이 없습니다.');
    }

    // 3. 고객 정보 가져오기
    $customer_name = $quote['customer_name'] ?? '';
    $customer_email = $quote['customer_email'] ?? '';
    $customer_phone = $quote['customer_phone'] ?? '';
    $customer_company = $quote['customer_company'] ?? '';

    // 배송 정보
    $delivery_address = $quote['delivery_address'] ?? '';

    // 4. 각 품목을 mlangorder_printauto 테이블에 주문으로 저장
    // ProcessOrder_unified.php와 동일한 INSERT 쿼리 사용
    $insert_query = "INSERT INTO mlangorder_printauto (
        no, Type, ImgFolder, uploaded_files, Type_1, money_4, money_5, name, email, zip, zip1, zip2,
        phone, Hendphone, cont, date, OrderStyle, ThingCate,
        coating_enabled, coating_type, coating_price,
        folding_enabled, folding_type, folding_price,
        creasing_enabled, creasing_lines, creasing_price,
        additional_options_total,
        premium_options, premium_options_total,
        envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price,
        envelope_additional_options_total
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $order_stmt = mysqli_prepare($db, $insert_query);
    if (!$order_stmt) {
        throw new Exception('주문 쿼리 준비 실패: ' . mysqli_error($db));
    }

    $order_count = 0;
    $order_numbers = [];

    foreach ($items as $item) {
        // 새 주문번호 생성
        $no_query = "SELECT COALESCE(MAX(no), 0) + 1 as new_no FROM mlangorder_printauto";
        $no_result = mysqli_query($db, $no_query);
        $no_row = mysqli_fetch_assoc($no_result);
        $new_no = intval($no_row['new_no']);

        // 제품 타입명 결정
        $product_type = $item['product_type'] ?? 'custom';
        $product_type_names = [
            'inserted' => '전단지',
            'leaflet' => '전단지',
            'namecard' => '명함',
            'envelope' => '봉투',
            'sticker' => '스티커',
            'msticker' => '자석스티커',
            'cadarok' => '카다록',
            'littleprint' => '포스터',
            'merchandisebond' => '상품권',
            'ncrflambeau' => '양식지',
            'custom' => '수동입력'
        ];
        $product_type_name = $product_type_names[$product_type] ?? $item['product_name'];

        // source_data에서 원본 데이터 가져오기
        $source_data = json_decode($item['source_data'] ?? '{}', true);
        if (!is_array($source_data)) {
            $source_data = [];
        }

        // 파일 정보
        $img_folder = $source_data['ImgFolder'] ?? '';
        $uploaded_files = $source_data['uploaded_files'] ?? '';
        if (is_array($uploaded_files)) {
            $uploaded_files = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);
        }
        $thing_cate = $source_data['ThingCate'] ?? '';

        // formatted_display 생성 (ProcessOrder_unified.php와 동일한 형식)
        $formatted_display = buildFormattedDisplay($item, $source_data, $product_type);

        // Type_1 JSON 생성
        // 🔧 전단지/리플렛의 경우 매수(quantityTwo) 자동 계산
        $my_amount_value = $source_data['MY_amount'] ?? $item['quantity'];
        $quantityTwo_value = intval($source_data['quantityTwo'] ?? $source_data['mesu'] ?? 0);

        // 매수가 없고 전단지/리플렛일 경우 DB에서 조회 (계산 금지 - SSOT 준수)
        if ($quantityTwo_value === 0 && in_array($product_type, ['inserted', 'leaflet'])) {
            $reams_float = floatval($my_amount_value);
            if ($reams_float > 0) {
                $quantityTwo_value = lookupInsertedSheets(
                    $db,
                    $reams_float,
                    $source_data['MY_type'] ?? '',
                    $source_data['PN_type'] ?? '',
                    $source_data['MY_Fsd'] ?? '',
                    $source_data['POtype'] ?? ''
                );
            }
        }

        $type_1_data = [
            'product_type' => $product_type,
            'MY_type' => $source_data['MY_type'] ?? '',
            'MY_Fsd' => $source_data['MY_Fsd'] ?? '',
            'PN_type' => $source_data['PN_type'] ?? '',
            'POtype' => $source_data['POtype'] ?? '',
            'MY_amount' => $my_amount_value,
            'quantityTwo' => $quantityTwo_value,
            'ordertype' => $source_data['ordertype'] ?? '',
            'formatted_display' => $formatted_display,
            'quote_no' => $quote['quote_no'],
            'quote_id' => $quote_id,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $product_info = json_encode($type_1_data, JSON_UNESCAPED_UNICODE);

        // 가격 정보
        $supply_price = strval($item['supply_price']);
        $vat_price = strval($item['total_price']);

        // 작업 메모 (견적서 참조만 표시 - 제품명/규격은 Type_1에 이미 포함됨)
        $cont = "[견적서: " . $quote['quote_no'] . "]";
        // 견적서 비고가 있는 경우에만 추가
        if (!empty($item['notes'])) {
            $cont .= "\n비고: " . $item['notes'];
        }

        // 주문 날짜
        $date = date('Y-m-d H:i:s');
        $order_style = '1'; // 견적서에서 변환된 주문 → 견적접수 상태

        // 추가 옵션 (source_data에서 가져오기)
        $coating_enabled = intval($source_data['coating_enabled'] ?? 0);
        $coating_type = $source_data['coating_type'] ?? '';
        $coating_price = intval($source_data['coating_price'] ?? 0);
        $folding_enabled = intval($source_data['folding_enabled'] ?? 0);
        $folding_type = $source_data['folding_type'] ?? '';
        $folding_price = intval($source_data['folding_price'] ?? 0);
        $creasing_enabled = intval($source_data['creasing_enabled'] ?? 0);
        $creasing_lines = intval($source_data['creasing_lines'] ?? 0);
        $creasing_price = intval($source_data['creasing_price'] ?? 0);
        $additional_options_total = intval($source_data['additional_options_total'] ?? 0);

        // 프리미엄 옵션
        $premium_options = $source_data['premium_options'] ?? '';
        if (is_array($premium_options)) {
            $premium_options = json_encode($premium_options, JSON_UNESCAPED_UNICODE);
        }
        $premium_options_total = intval($source_data['premium_options_total'] ?? 0);

        // 봉투 옵션
        $envelope_tape_enabled = intval($source_data['envelope_tape_enabled'] ?? 0);
        $envelope_tape_quantity = intval($source_data['envelope_tape_quantity'] ?? 0);
        $envelope_tape_price = intval($source_data['envelope_tape_price'] ?? 0);
        $envelope_additional_options_total = intval($source_data['envelope_additional_options_total'] ?? 0);

        // 주소 정보
        $zip = '';
        $zip1 = $delivery_address;
        $zip2 = '';

        // 전화번호
        $hendphone = $customer_phone;

        // 34개 파라미터 바인딩 (ProcessOrder_unified.php와 동일)
        // i + Type(s) + ImgFolder(s) + uploaded_files(s) + Type_1(s) + money_4(s) + money_5(s) + name(s) + email~ThingCate(10s) + coating(isi) + folding(isi) + creasing(iii) + additional(i) + premium(si) + envelope(iiii)
        mysqli_stmt_bind_param($order_stmt, 'issssssssssssssssssisiisiiiisiiiii',
            $new_no, $product_type_name, $img_folder, $uploaded_files, $product_info, $supply_price, $vat_price,
            $customer_name, $customer_email, $zip, $zip1, $zip2,
            $customer_phone, $hendphone, $cont, $date, $order_style, $thing_cate,
            $coating_enabled, $coating_type, $coating_price,
            $folding_enabled, $folding_type, $folding_price,
            $creasing_enabled, $creasing_lines, $creasing_price,
            $additional_options_total,
            $premium_options, $premium_options_total,
            $envelope_tape_enabled, $envelope_tape_quantity, $envelope_tape_price,
            $envelope_additional_options_total
        );

        if (!mysqli_stmt_execute($order_stmt)) {
            throw new Exception('주문 저장 실패: ' . mysqli_stmt_error($order_stmt));
        }

        $order_numbers[] = $new_no;
        $order_count++;

        error_log("견적서 → 주문 변환: quote_id={$quote_id}, order_no={$new_no}, product={$product_type_name}");
    }

    mysqli_stmt_close($order_stmt);

    // 5. 견적서 상태를 'converted'로 업데이트
    $update_query = "UPDATE quotes SET status = 'converted', responded_at = NOW(), updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($stmt, "i", $quote_id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('견적서 상태 업데이트 실패: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

    // 트랜잭션 커밋
    mysqli_commit($db);

    // 성공 응답
    jsonSuccess([
        'quote_id' => $quote_id,
        'quote_no' => $quote['quote_no'],
        'order_count' => $order_count,
        'order_numbers' => $order_numbers,
        'message' => '견적서가 주문으로 전환되었습니다. (' . $order_count . '개 품목)',
        'redirect_url' => '/admin/mlangprintauto/admin.php?mode=OrderView&no=' . $order_numbers[0]
    ]);

} catch (Exception $e) {
    // 트랜잭션 롤백
    mysqli_rollback($db);
    error_log("견적서 → 주문 변환 실패: " . $e->getMessage());
    jsonError($e->getMessage(), 500);
}

/**
 * formatted_display 생성 함수 (ProcessOrder_unified.php와 동일한 형식)
 */
function buildFormattedDisplay($item, $source_data, $product_type) {
    global $db;

    $product_name = $item['product_name'];
    $specification = $item['specification'] ?? '';
    $quantity = $item['quantity'];
    $unit = $item['unit'] ?? '개';

    // 전단지/리플렛의 경우 "수량: X연 (Y매)" 형식
    if (in_array($product_type, ['inserted', 'leaflet'])) {
        $reams = $source_data['MY_amount'] ?? $quantity;
        $sheets = intval($source_data['quantityTwo'] ?? $source_data['mesu'] ?? 0);

        // 매수가 없으면 DB에서 조회 (계산 금지 - SSOT 준수)
        if ($sheets === 0) {
            $reams_float = floatval($reams);
            if ($reams_float > 0) {
                $sheets = lookupInsertedSheets(
                    $db,
                    $reams_float,
                    $source_data['MY_type'] ?? '',
                    $source_data['PN_type'] ?? '',
                    $source_data['MY_Fsd'] ?? '',
                    $source_data['POtype'] ?? ''
                );
            }
        }

        // 카테고리명 조회
        $color_name = getCategoryNameFromDB($db, $source_data['MY_type'] ?? '');
        $paper_name = getCategoryNameFromDB($db, $source_data['MY_Fsd'] ?? '');
        $size_name = getCategoryNameFromDB($db, $source_data['PN_type'] ?? '');
        $sides = ($source_data['POtype'] ?? '1') == '1' ? '단면' : '양면';
        $design = ($source_data['ordertype'] ?? '') == 'total' ? '디자인+인쇄' : '인쇄만';

        if ($sheets > 0) {
            $qty_display = number_format($reams, 1) . "연 (" . number_format($sheets) . "매)";
        } else {
            $qty_display = number_format($reams, 1) . "연";
        }

        $formatted = "인쇄색상: $color_name\n";
        $formatted .= "용지: $paper_name\n";
        $formatted .= "규격: $size_name\n";
        $formatted .= "인쇄면: $sides\n";
        $formatted .= "수량: $qty_display\n";
        $formatted .= "디자인: $design";

        return $formatted;
    }

    // 명함
    if ($product_type === 'namecard') {
        $paper = getCategoryNameFromDB($db, $source_data['MY_type'] ?? '');
        $section = getCategoryNameFromDB($db, $source_data['Section'] ?? '');
        $sides = ($source_data['POtype'] ?? '1') == '1' ? '단면' : '양면';

        $formatted = "용지: $paper\n";
        $formatted .= "규격: $section\n";
        $formatted .= "인쇄면: $sides\n";
        // 소수점 처리: 0.5연 등 표시 (정수면 정수로, 소수면 1자리)
        $qty_display = ($quantity == intval($quantity)) ? number_format($quantity) : number_format($quantity, 1);
        $formatted .= "수량: " . $qty_display . "$unit";

        return $formatted;
    }

    // 봉투
    if ($product_type === 'envelope') {
        $envelope_type = getCategoryNameFromDB($db, $source_data['MY_type'] ?? '');
        $size = getCategoryNameFromDB($db, $source_data['Section'] ?? '');
        $sides = ($source_data['POtype'] ?? '1') == '1' ? '단면' : '양면';

        $formatted = "봉투종류: $envelope_type\n";
        $formatted .= "규격: $size\n";
        $formatted .= "인쇄면: $sides\n";
        // 소수점 처리: 0.5연 등 표시
        $qty_display = ($quantity == intval($quantity)) ? number_format($quantity) : number_format($quantity, 1);
        $formatted .= "수량: " . $qty_display . "$unit";

        return $formatted;
    }

    // 기타 제품
    $formatted = "품명: $product_name\n";
    if (!empty($specification)) {
        $formatted .= "사양: $specification\n";
    }
    // 소수점 처리: 0.5연 등 표시
    $qty_display = ($quantity == intval($quantity)) ? number_format($quantity) : number_format($quantity, 1);
    $formatted .= "수량: " . $qty_display . "$unit";

    return $formatted;
}

/**
 * 카테고리 이름 조회 헬퍼 함수
 */
function getCategoryNameFromDB($db, $code) {
    if (empty($code)) return '';

    $query = "SELECT Value FROM mlangprintauto_cate WHERE Code = ? LIMIT 1";
    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) return $code;

    mysqli_stmt_bind_param($stmt, "s", $code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $row ? $row['Value'] : $code;
}
?>
