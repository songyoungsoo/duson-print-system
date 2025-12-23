<?php
/**
 * 견적서 저장 API
 * POST JSON 데이터를 받아 quotations 테이블에 저장
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
    jsonError('담당자명은 필수입니다.');
}

// 세션 ID
$session_id = session_id();

// 데이터 추출
$deliveryType = trim($data['deliveryType'] ?? '');
$deliveryPrice = intval($data['deliveryPrice'] ?? 0);
$customItems = $data['customItems'] ?? [];
$totalSupply = intval($data['totalSupply'] ?? 0);
$totalVat = intval($data['totalVat'] ?? 0);
$totalPrice = intval($data['totalPrice'] ?? 0);

// 장바구니 아이템 조회
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

if (empty($cart_items)) {
    jsonError('장바구니가 비어있습니다.');
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

// JSON 변환
$cart_items_json = json_encode($cart_items, JSON_UNESCAPED_UNICODE);
$custom_items_json = json_encode($customItems, JSON_UNESCAPED_UNICODE);

// 관리자 ID (로그인된 경우, 비로그인 시 0)
$created_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// 유효기간 (7일 후)
$expires_at = date('Y-m-d', strtotime('+7 days'));

// 공개 링크용 토큰 생성 (64자 랜덤)
$public_token = bin2hex(random_bytes(32));

// 견적서 저장
$insert_query = "INSERT INTO quotations (
    quotation_no, public_token, session_id, customer_name,
    cart_items_json, delivery_type, delivery_price, custom_items_json,
    total_supply, total_vat, total_price,
    status, created_by, expires_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if (!$stmt) {
    jsonError('쿼리 준비 실패: ' . mysqli_error($db), 500);
}

mysqli_stmt_bind_param($stmt, "ssssssisiiiiis",
    $quotation_no,
    $public_token,
    $session_id,
    $customerName,
    $cart_items_json,
    $deliveryType,
    $deliveryPrice,
    $custom_items_json,
    $totalSupply,
    $totalVat,
    $totalPrice,
    $created_by,
    $expires_at
);

if (!mysqli_stmt_execute($stmt)) {
    jsonError('견적서 저장 실패: ' . mysqli_stmt_error($stmt), 500);
}

$quotation_id = mysqli_insert_id($db);
mysqli_stmt_close($stmt);

// 공개 링크 URL 생성
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$public_url = $base_url . '/mlangprintauto/shop/quotation_view.php?token=' . $public_token;

// 성공 응답
jsonSuccess([
    'quotation_no' => $quotation_no,
    'quotation_id' => $quotation_id,
    'public_token' => $public_token,
    'public_url' => $public_url,
    'message' => '견적서가 저장되었습니다.'
]);
?>
