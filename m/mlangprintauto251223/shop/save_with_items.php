<?php
/**
 * 견적서 저장 API (품목별 저장 - quotation_items 테이블 사용)
 * POST JSON 데이터를 받아 quotations 및 quotation_items 테이블에 저장
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

mysqli_set_charset($db, 'utf8mb4');

// POST JSON 데이터 파싱
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    jsonError('잘못된 요청 형식입니다.');
}

// 필수 필드 검증
$customerName = trim($data['customerName'] ?? '');
if (empty($customerName)) {
    jsonError('고객명은 필수입니다.');
}

// 세션 ID
$session_id = session_id();

// 데이터 추출
$customerEmail = trim($data['customerEmail'] ?? ''); // 선택사항
$deliveryType = trim($data['deliveryType'] ?? '');
$deliveryPrice = intval($data['deliveryPrice'] ?? 0);
$deliveryVat = intval($data['deliveryVat'] ?? round($deliveryPrice * 0.1));
$paymentTerms = trim($data['paymentTerms'] ?? '발행일로부터 7일');
$deliveryAddress = trim($data['deliveryAddress'] ?? '');
$items = $data['items'] ?? []; // 품목 배열
$totalSupply = intval($data['totalSupply'] ?? 0);
$totalVat = intval($data['totalVat'] ?? 0);
$totalPrice = intval($data['totalPrice'] ?? 0);

// 품목 검증 (최소 1개 필요)
if (empty($items) || count($items) == 0) {
    jsonError('최소 1개 이상의 품목이 필요합니다.');
}

// 장바구니 아이템 조회 (참조용)
$cart_items = [];
$cart_query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no ASC";
$stmt = mysqli_prepare($db, $cart_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $cart_items[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// 견적번호 생성 (QT-담당자명-YYYYMMDD-NNN)
$today = date('Ymd');

// 담당자명에서 특수문자 제거 (파일명/DB 안전)
$safe_customer_name = preg_replace('/[^가-힣a-zA-Z0-9]/u', '', $customerName);
// 담당자명이 너무 길면 10자로 제한
if (mb_strlen($safe_customer_name) > 10) {
    $safe_customer_name = mb_substr($safe_customer_name, 0, 10);
}

$quotation_no_prefix = "QT-{$safe_customer_name}-{$today}-";

// 오늘 해당 담당자의 견적서 개수 조회
$count_query = "SELECT COUNT(*) as cnt FROM quotations WHERE quotation_no LIKE ?";
$stmt = mysqli_prepare($db, $count_query);
$like_pattern = $quotation_no_prefix . '%';
mysqli_stmt_bind_param($stmt, "s", $like_pattern);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$count_row = mysqli_fetch_assoc($count_result);
$next_number = intval($count_row['cnt']) + 1;
mysqli_stmt_close($stmt);

$quotation_no = $quotation_no_prefix . str_pad($next_number, 3, '0', STR_PAD_LEFT);

// 중복 체크 및 번호 증가
$check_query = "SELECT id FROM quotations WHERE quotation_no = ?";
while (true) {
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $quotation_no);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_stmt_close($stmt);
        break;
    }
    mysqli_stmt_close($stmt);
    $next_number++;
    $quotation_no = $quotation_no_prefix . str_pad($next_number, 3, '0', STR_PAD_LEFT);
}

// JSON 변환 (레거시 호환성 - cart_items_json은 빈 배열로 저장)
$cart_items_json = json_encode($cart_items, JSON_UNESCAPED_UNICODE);

// 관리자 ID (로그인된 경우, 비로그인 시 0)
$created_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// 유효기간 (7일 후)
$expires_at = date('Y-m-d', strtotime('+7 days'));

// 공개 링크용 토큰 생성 (64자 랜덤)
$public_token = bin2hex(random_bytes(32));

// 트랜잭션 시작
mysqli_begin_transaction($db);

try {
    // 1. 견적서 저장 (quotations 테이블)
    $insert_query = "INSERT INTO quotations (
        quotation_no, public_token, session_id, customer_name, customer_email,
        cart_items_json, delivery_type, delivery_price, delivery_vat, payment_terms, delivery_address,
        total_supply, total_vat, total_price,
        status, created_by, expires_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)";

    $stmt = mysqli_prepare($db, $insert_query);
    if (!$stmt) {
        throw new Exception('견적서 쿼리 준비 실패: ' . mysqli_error($db));
    }

    // 16 params: quotation_no(s) + public_token(s) + session_id(s) + customer_name(s) + customer_email(s) +
    // cart_items_json(s) + delivery_type(s) + delivery_price(i) + delivery_vat(i) + payment_terms(s) +
    // delivery_address(s) + total_supply(i) + total_vat(i) + total_price(i) + created_by(i) + expires_at(s)
    mysqli_stmt_bind_param($stmt, "sssssssiississiis",
        $quotation_no,
        $public_token,
        $session_id,
        $customerName,
        $customerEmail,
        $cart_items_json,
        $deliveryType,
        $deliveryPrice,
        $deliveryVat,
        $paymentTerms,
        $deliveryAddress,
        $totalSupply,
        $totalVat,
        $totalPrice,
        $created_by,
        $expires_at
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('견적서 저장 실패: ' . mysqli_stmt_error($stmt));
    }

    $quotation_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);

    // 2. 품목 저장 (quotation_items 테이블)
    $item_query = "INSERT INTO quotation_items (
        quotation_id, item_type, product_type, product_name, specification,
        quantity, unit, unit_price, supply_price, vat_price, total_price,
        source_cart_id, calculator_session, details, sort_order
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $item_stmt = mysqli_prepare($db, $item_query);
    if (!$item_stmt) {
        throw new Exception('품목 쿼리 준비 실패: ' . mysqli_error($db));
    }

    $sort_order = 0;

    foreach ($items as $item) {
        // 품목 데이터 추출
        $product_name = trim($item['product_name'] ?? '');
        $product_name_custom = trim($item['product_name_custom'] ?? '');

        // 품명: custom이 있으면 custom 사용, 없으면 select 값 사용
        $final_product_name = !empty($product_name_custom) ? $product_name_custom : $product_name;

        if (empty($final_product_name)) {
            continue; // 빈 행 스킵
        }

        $specification = trim($item['specification'] ?? '');
        $quantity = intval($item['quantity'] ?? 1);
        $unit = trim($item['unit'] ?? '개');
        $unit_price = floatval($item['unit_price'] ?? 0);
        $supply_price = intval($item['supply_price'] ?? 0);
        $vat_price = intval($item['vat_price'] ?? 0);
        $total_price_item = intval($item['total_price'] ?? 0);

        // item_type 결정
        $item_type = 'manual'; // 기본값: 직접입력
        $product_type = null;
        $source_cart_id = null;
        $calculator_session = null;
        $details = null;

        // 장바구니 품목인지 확인
        if (isset($item['source']) && $item['source'] === 'cart' && isset($item['cart_no'])) {
            $item_type = 'cart';
            $source_cart_id = intval($item['cart_no']);

            // 장바구니에서 product_type 및 details 가져오기
            foreach ($cart_items as $cart_item) {
                if (intval($cart_item['no']) === $source_cart_id) {
                    $product_type = $cart_item['product_type'] ?? null;

                    // details JSON 구성 (장바구니의 주요 정보 저장)
                    $details_array = [
                        'MY_type' => $cart_item['MY_type'] ?? null,
                        'Section' => $cart_item['Section'] ?? null,
                        'POtype' => $cart_item['POtype'] ?? null,
                        'MY_amount' => $cart_item['MY_amount'] ?? null,
                        'ordertype' => $cart_item['ordertype'] ?? null,
                        'premium_options' => $cart_item['premium_options'] ?? null,
                        'uploaded_files' => $cart_item['uploaded_files'] ?? null,
                        'ImgFolder' => $cart_item['ImgFolder'] ?? null
                    ];
                    $details = json_encode($details_array, JSON_UNESCAPED_UNICODE);
                    break;
                }
            }
        }
        // 계산기에서 온 품목인지 확인 (향후 구현 시)
        elseif (isset($item['calculator_data'])) {
            $item_type = 'calculator';
            $calculator_session = json_encode($item['calculator_data'], JSON_UNESCAPED_UNICODE);
            $product_type = $item['calculator_data']['product_type'] ?? null;
            $details = $calculator_session; // 계산기 데이터 전체 저장
        }
        // 직접입력 품목
        else {
            $item_type = 'manual';
            $product_type = null; // 직접입력은 product_type 없음
        }

        // 품목 저장
        mysqli_stmt_bind_param($item_stmt, "issssisdiiissi",
            $quotation_id,
            $item_type,
            $product_type,
            $final_product_name,
            $specification,
            $quantity,
            $unit,
            $unit_price,
            $supply_price,
            $vat_price,
            $total_price_item,
            $source_cart_id,
            $calculator_session,
            $details,
            $sort_order
        );

        if (!mysqli_stmt_execute($item_stmt)) {
            throw new Exception('품목 저장 실패: ' . mysqli_stmt_error($item_stmt));
        }

        $sort_order++;
    }

    mysqli_stmt_close($item_stmt);

    // 트랜잭션 커밋
    mysqli_commit($db);

    // 공개 링크 URL 생성
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $public_url = $base_url . '/mlangprintauto/shop/quotation_view.php?token=' . $public_token;

    // 성공 응답
    jsonSuccess([
        'quotation_no' => $quotation_no,
        'quotation_id' => $quotation_id,
        'public_token' => $public_token,
        'public_url' => $public_url,
        'items_saved' => $sort_order,
        'message' => '견적서 및 ' . $sort_order . '개 품목이 저장되었습니다.'
    ]);

} catch (Exception $e) {
    // 트랜잭션 롤백
    mysqli_rollback($db);
    jsonError($e->getMessage(), 500);
}
?>
