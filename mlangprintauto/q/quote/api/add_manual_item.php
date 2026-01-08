<?php
/**
 * 견적서 수기 품목 추가 API
 * 계산기 없이 직접 품목을 입력할 수 있는 엔드포인트
 *
 * POST 파라미터:
 * - product_name (필수): 품목명
 * - specification: 규격 설명
 * - quantity_display (필수): 수량 표시 (단위 포함, 예: "1,000매")
 * - price_supply (필수): 공급가액
 * - notes: 비고
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/safe_json_response.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// DB 연결 확인
if (!isset($db) || !$db) {
    safe_json_response(false, null, 'DB 연결 실패');
}
mysqli_set_charset($db, "utf8mb4");

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    safe_json_response(false, null, 'POST 요청만 허용됩니다.');
}

// === 필수 필드 검증 ===
$product_name = trim($_POST['product_name'] ?? '');
$specification = trim($_POST['specification'] ?? '');
$quantity_display = trim($_POST['quantity_display'] ?? '');
$price_supply = intval($_POST['price_supply'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

if (empty($product_name)) {
    safe_json_response(false, null, '품목명은 필수입니다.');
}

if (empty($quantity_display)) {
    safe_json_response(false, null, '수량은 필수입니다.');
}

if ($price_supply <= 0) {
    safe_json_response(false, null, '공급가액은 0보다 커야 합니다.');
}

// === quantity_display 단위 검증 ===
// 단위가 없으면 자동으로 '개' 추가
if (!preg_match('/[매연부권개장]/u', $quantity_display)) {
    // 숫자만 있는 경우 단위 추가
    $numeric = preg_replace('/[^0-9]/', '', $quantity_display);
    if (!empty($numeric)) {
        $quantity_display = number_format(intval($numeric)) . '개';
    } else {
        $quantity_display = '1개';
    }
}

// === 가격 계산 ===
$price_vat_amount = intval($price_supply * 0.1);  // VAT 10%
$price_vat = $price_supply + $price_vat_amount;   // 합계

// === 세션 정보 ===
$session_id = session_id();
$regdate = time();

// === quotation_temp 삽입 ===
$query = "INSERT INTO quotation_temp (
    session_id, product_type, jong, spec_size, quantity_display,
    price_supply, price_vat, price_vat_amount, st_price, st_price_vat,
    MY_comment, regdate, data_version, is_manual_entry
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $query);

if (!$stmt) {
    safe_json_response(false, null, 'DB 쿼리 준비 실패: ' . mysqli_error($db));
}

$product_type = 'manual';  // 수기 입력 표시
$data_version = 3;         // Phase 3 표준 필드 사용
$is_manual_entry = 1;      // 수기 입력 플래그

// bind_param 검증: 14개 placeholder, 14개 type, 14개 변수
// s=session_id, s=product_type, s=jong(품목명), s=spec_size(규격),
// s=quantity_display, i=price_supply, i=price_vat, i=price_vat_amount,
// i=st_price, i=st_price_vat, s=MY_comment, i=regdate, i=data_version, i=is_manual_entry
mysqli_stmt_bind_param(
    $stmt,
    "sssssiiiiisiii",
    $session_id,
    $product_type,
    $product_name,       // jong 필드에 품목명 저장
    $specification,      // spec_size 필드에 규격 저장
    $quantity_display,
    $price_supply,
    $price_vat,
    $price_vat_amount,
    $price_supply,       // st_price (레거시 호환)
    $price_vat,          // st_price_vat (레거시 호환)
    $notes,              // MY_comment에 비고 저장
    $regdate,
    $data_version,
    $is_manual_entry
);

if (!mysqli_stmt_execute($stmt)) {
    safe_json_response(false, null, 'DB 삽입 실패: ' . mysqli_stmt_error($stmt));
}

$inserted_id = mysqli_insert_id($db);
mysqli_stmt_close($stmt);

// === 성공 응답 ===
safe_json_response(true, [
    'id' => $inserted_id,
    'product_name' => $product_name,
    'specification' => $specification,
    'quantity_display' => $quantity_display,
    'price_supply' => $price_supply,
    'price_vat' => $price_vat,
    'price_vat_amount' => $price_vat_amount
], '수기 품목이 추가되었습니다.');
