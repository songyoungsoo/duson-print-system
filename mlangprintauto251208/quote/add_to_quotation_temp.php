<?php
/**
 * 견적서 임시 품목 추가 API
 * 계산기 모달에서 AJAX로 호출하여 quotation_temp 테이블에 저장
 * shop_temp와 동일한 55개 필드 구조 사용
 */

require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    safe_json_response(false, null, 'POST 요청만 허용됩니다.');
}

// === 1. 기본 필드 추출 ===
$session_id = session_id();
$order_id = null;  // 견적 단계에서는 NULL
$parent = null;
$product_type = $_POST['product_type'] ?? '';

// === 2. 스티커 전용 필드 (다른 제품은 NULL) ===
$jong = $_POST['jong'] ?? null;
$garo = $_POST['garo'] ?? null;
$sero = $_POST['sero'] ?? null;
$mesu = $_POST['mesu'] ?? null;
$domusong = $_POST['domusong'] ?? null;
$uhyung = intval($_POST['uhyung'] ?? 0);  // 0=인쇄만, 1=디자인+인쇄

// === 3. 제품 스펙 필드 ===
$MY_type = $_POST['MY_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';

// ✅ quantity 우선 사용 (계산기에서 전송한 연 단위 소수점 값)
// 전단지는 quantity=연 단위 (0.5, 1, 1.5 등), 다른 제품은 MY_amount 그대로 사용
$quantity_from_calculator = $_POST['quantity'] ?? null;  // 계산기에서 전송한 quantity
if ($quantity_from_calculator !== null) {
    $MY_amount = $quantity_from_calculator;  // 연 단위 (소수점 포함)
} else {
    $MY_amount = $_POST['MY_amount'] ?? '';  // 폴백: 원래 MY_amount 값
}

$POtype = $_POST['POtype'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$Section = $_POST['Section'] ?? '';
$TreeSelect = $_POST['TreeSelect'] ?? null;

// === 4. 가격 필드 ===
$st_price = intval($_POST['calculated_price'] ?? $_POST['st_price'] ?? 0);
$st_price_vat = intval($_POST['calculated_vat_price'] ?? $_POST['st_price_vat'] ?? 0);

// === 5. 메모/코멘트 ===
$MY_comment = $_POST['MY_comment'] ?? null;
$work_memo = $_POST['work_memo'] ?? '';

// === 6. 파일 업로드 필드 ===
$img = $_POST['img'] ?? null;
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = null;  // 레거시 필드
$upload_folder = null;  // 레거시 필드

// === 7. StandardUploadHandler를 통한 파일 업로드 처리 ===
$upload_result = StandardUploadHandler::processUpload($product_type, $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

error_log("quotation_temp 업로드: " . count($uploaded_files) . "개 파일, 경로: $img_folder");

// === 8. 추가 옵션 필드 ===
// 코팅 옵션
$coating_enabled = intval($_POST['coating_enabled'] ?? 0);
$coating_type = $_POST['coating_type'] ?? '';
$coating_price = intval($_POST['coating_price'] ?? 0);

// 접지 옵션
$folding_enabled = intval($_POST['folding_enabled'] ?? 0);
$folding_type = $_POST['folding_type'] ?? '';
$folding_price = intval($_POST['folding_price'] ?? 0);

// 오시 옵션
$creasing_enabled = intval($_POST['creasing_enabled'] ?? 0);
$creasing_lines = intval($_POST['creasing_lines'] ?? 0);
$creasing_price = intval($_POST['creasing_price'] ?? 0);

// 추가 옵션 총액
$additional_options_total = intval($_POST['additional_options_total'] ?? 0);

// 추가 옵션 JSON (레거시 호환)
$additional_options = [
    'coating_enabled' => $coating_enabled,
    'coating_type' => $coating_type,
    'coating_price' => $coating_price,
    'folding_enabled' => $folding_enabled,
    'folding_type' => $folding_type,
    'folding_price' => $folding_price,
    'creasing_enabled' => $creasing_enabled,
    'creasing_lines' => $creasing_lines,
    'creasing_price' => $creasing_price
];
$additional_options_json = json_encode($additional_options, JSON_UNESCAPED_UNICODE);

// 선택 옵션 (레거시)
$selected_options = $_POST['selected_options'] ?? null;

// === 9. 프리미엄 옵션 ===
$premium_options = $_POST['premium_options'] ?? null;
$premium_options_total = intval($_POST['premium_options_total'] ?? 0);

// === 10. 봉투 전용 옵션 ===
$envelope_tape_enabled = intval($_POST['envelope_tape_enabled'] ?? 0);
$envelope_tape_quantity = intval($_POST['envelope_tape_quantity'] ?? 0);
$envelope_tape_price = intval($_POST['envelope_tape_price'] ?? 0);
$envelope_additional_options_total = intval($_POST['envelope_additional_options_total'] ?? 0);

// === 11. 한글명 필드 (옵션) ===
$MY_type_name = $_POST['MY_type_name'] ?? null;
$Section_name = $_POST['Section_name'] ?? null;
$POtype_name = $_POST['POtype_name'] ?? null;

// === 12. 고객 정보 ===
$customer_name = $_POST['customer_name'] ?? null;
$customer_phone = $_POST['customer_phone'] ?? null;

// === 13. 원본 파일명 ===
$original_filename = $_POST['original_filename'] ?? null;

// === 14. DB INSERT (53개 필드 - no와 regdate 제외) ===
$query = "INSERT INTO quotation_temp (
    session_id, order_id, parent, product_type,
    jong, garo, sero, mesu, domusong, uhyung,
    MY_type, MY_Fsd, PN_type, MY_amount, POtype, ordertype,
    st_price, st_price_vat,
    MY_comment, img,
    Section, TreeSelect,
    work_memo, upload_method, uploaded_files_info, upload_folder,
    uploaded_files, ThingCate, ImgFolder,
    coating_enabled, coating_type, coating_price,
    folding_enabled, folding_type, folding_price,
    creasing_enabled, creasing_lines, creasing_price,
    additional_options_total, selected_options, additional_options,
    premium_options, premium_options_total,
    envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price, envelope_additional_options_total,
    MY_type_name, Section_name, POtype_name,
    customer_name, customer_phone,
    original_filename,
    regdate
) VALUES (
    ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?,
    ?, ?,
    ?, ?,
    ?, ?,
    ?, ?, ?, ?,
    ?, ?, ?,
    ?, ?, ?,
    ?, ?, ?,
    ?, ?, ?,
    ?, ?, ?,
    ?, ?,
    ?, ?, ?, ?,
    ?, ?, ?,
    ?, ?,
    ?,
    UNIX_TIMESTAMP()
)";

// 🔴 CRITICAL: bind_param 타입 문자열 검증 (COUNT 3 TIMES!)
// Placeholder 개수
$placeholder_count = substr_count($query, '?');

// 타입 문자열 (53개 파라미터)
$type_string =
    "ssss" .        // session_id, order_id, parent, product_type (4)
    "sssssi" .      // jong, garo, sero, mesu, domusong, uhyung (6)
    "ssssss" .      // MY_type, MY_Fsd, PN_type, MY_amount, POtype, ordertype (6)
    "ii" .          // st_price, st_price_vat (2)
    "ss" .          // MY_comment, img (2)
    "ss" .          // Section, TreeSelect (2)
    "ssss" .        // work_memo, upload_method, uploaded_files_info, upload_folder (4)
    "sss" .         // uploaded_files, ThingCate, ImgFolder (3)
    "isi" .         // coating_enabled, coating_type, coating_price (3)
    "isi" .         // folding_enabled, folding_type, folding_price (3)
    "iii" .         // creasing_enabled, creasing_lines, creasing_price (3)
    "iss" .         // additional_options_total, selected_options, additional_options (3)
    "si" .          // premium_options, premium_options_total (2)
    "iiii" .        // envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price, envelope_additional_options_total (4)
    "sss" .         // MY_type_name, Section_name, POtype_name (3)
    "ss" .          // customer_name, customer_phone (2)
    "s";            // original_filename (1)
    // Total: 53 parameters

$type_count = strlen($type_string);

// 검증
error_log("=== bind_param 검증 ===");
error_log("Placeholders (?): $placeholder_count");
error_log("Type string length: $type_count");

if ($placeholder_count !== $type_count) {
    error_log("❌ MISMATCH DETECTED!");
    safe_json_response(false, null, "bind_param count mismatch: placeholders=$placeholder_count, types=$type_count");
}

$stmt = mysqli_prepare($db, $query);

if (!$stmt) {
    error_log("quotation_temp INSERT prepare 실패: " . mysqli_error($db));
    safe_json_response(false, null, 'DB 준비 실패: ' . mysqli_error($db));
}

// bind_param 실행 (53개 파라미터)
mysqli_stmt_bind_param($stmt, $type_string,
    $session_id, $order_id, $parent, $product_type,
    $jong, $garo, $sero, $mesu, $domusong, $uhyung,
    $MY_type, $MY_Fsd, $PN_type, $MY_amount, $POtype, $ordertype,
    $st_price, $st_price_vat,
    $MY_comment, $img,
    $Section, $TreeSelect,
    $work_memo, $upload_method, $uploaded_files_info, $upload_folder,
    $uploaded_files_json, $thing_cate, $img_folder,
    $coating_enabled, $coating_type, $coating_price,
    $folding_enabled, $folding_type, $folding_price,
    $creasing_enabled, $creasing_lines, $creasing_price,
    $additional_options_total, $selected_options, $additional_options_json,
    $premium_options, $premium_options_total,
    $envelope_tape_enabled, $envelope_tape_quantity, $envelope_tape_price, $envelope_additional_options_total,
    $MY_type_name, $Section_name, $POtype_name,
    $customer_name, $customer_phone,
    $original_filename
);

if (!mysqli_stmt_execute($stmt)) {
    error_log("quotation_temp INSERT 실행 실패: " . mysqli_stmt_error($stmt));
    safe_json_response(false, null, 'DB 저장 실패: ' . mysqli_stmt_error($stmt));
}

$item_id = mysqli_insert_id($db);
mysqli_stmt_close($stmt);

error_log("quotation_temp 저장 성공 - ID: $item_id, Product: $product_type");

// 성공 응답
safe_json_response(true, [
    'quotation_temp_id' => $item_id,
    'session_id' => $session_id,
    'product_type' => $product_type,
    'uploaded_files_count' => count($uploaded_files),
    'upload_path' => $img_folder,
    'price' => $st_price,
    'vat_price' => $st_price_vat
], '견적서에 추가되었습니다.');

mysqli_close($db);
?>
