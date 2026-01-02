<?php
/**
 * 리플렛 장바구니 추가
 * inserted/add_to_basket.php와 동일한 구조로 통일
 * flyer_mesu: 전단지/리플렛 전용 매수 필드
 */
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 🔍 디버그: 받은 데이터 로깅
error_log("=== LEAFLET DEBUG START ===");
error_log("_FILES 내용: " . print_r($_FILES, true));
error_log("_POST 키 목록: " . implode(', ', array_keys($_POST)));
error_log("=== LEAFLET DEBUG END ===");

// POST 데이터
$session_id = session_id();
$product_type = $_POST['product_type'] ?? 'leaflet'; // ✅ 기본값 leaflet
$MY_type = $_POST['MY_type'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = intval($_POST['calculated_price'] ?? $_POST['price'] ?? 0);
$vat_price = intval($_POST['calculated_vat_price'] ?? $_POST['vat_price'] ?? 0);
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';

// 추가 옵션
$additional_options = [
    'coating_enabled' => intval($_POST['coating_enabled'] ?? 0),
    'coating_type' => $_POST['coating_type'] ?? '',
    'coating_price' => intval($_POST['coating_price'] ?? 0),
    'folding_enabled' => intval($_POST['folding_enabled'] ?? 0),
    'folding_type' => $_POST['folding_type'] ?? '',
    'folding_price' => intval($_POST['folding_price'] ?? 0),
    'creasing_enabled' => intval($_POST['creasing_enabled'] ?? 0),
    'creasing_lines' => intval($_POST['creasing_lines'] ?? 0),
    'creasing_price' => intval($_POST['creasing_price'] ?? 0)
];
$additional_options_json = json_encode($additional_options, JSON_UNESCAPED_UNICODE);
$additional_options_total = intval($_POST['additional_options_total'] ?? 0);

// 필수 필드 검증
if (empty($MY_type) || empty($PN_type) || empty($MY_Fsd) || empty($POtype) || empty($MY_amount) || empty($ordertype)) {
    safe_json_response(false, null, '필수 정보가 누락되었습니다.');
}

// ✅ quantityTwo(매수) 조회 - 리플렛 가격 테이블에서 실제 매수 가져오기
// flyer_mesu: 전단지/리플렛 전용 매수 필드 (스티커용 mesu와 분리)
$flyer_mesu = 0;
$qty_query = "SELECT quantityTwo FROM mlangprintauto_leaflet
              WHERE style = ? AND Section = ? AND TreeSelect = ? AND POtype = ? AND quantity = ?
              LIMIT 1";
$qty_stmt = mysqli_prepare($db, $qty_query);
if ($qty_stmt) {
    mysqli_stmt_bind_param($qty_stmt, "sssss", $MY_type, $PN_type, $MY_Fsd, $POtype, $MY_amount);
    mysqli_stmt_execute($qty_stmt);
    $qty_result = mysqli_stmt_get_result($qty_stmt);
    if ($qty_row = mysqli_fetch_assoc($qty_result)) {
        $flyer_mesu = intval($qty_row['quantityTwo']);
    }
    mysqli_stmt_close($qty_stmt);
}
error_log("리플렛 매수 조회: MY_amount=$MY_amount, flyer_mesu=$flyer_mesu");

// ✅ 파일 업로드 처리 (StandardUploadHandler 사용)
$upload_result = StandardUploadHandler::processUpload('leaflet', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$upload_count = count($uploaded_files);

error_log("리플렛 업로드 결과: $upload_count 개 파일, 경로: $img_folder");

// uploaded_files를 JSON으로 변환 (테이블의 uploaded_files 컬럼에 저장)
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

// ✅ 리플렛: quantity = MY_amount(연수), unit = '연'
$quantity = floatval($MY_amount);  // 0.5, 1, 1.5 등
$unit = '연';

// INSERT (quantity, unit, flyer_mesu 컬럼 추가)
$sql = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, flyer_mesu, quantity, unit, POtype, ordertype, st_price, st_price_vat, additional_options, additional_options_total, ImgFolder, ThingCate, uploaded_files)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $sql);
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($db));
    safe_json_response(false, null, 'SQL 준비 실패: ' . mysqli_error($db));
}

// 디버깅 로그
error_log("Leaflet add_to_basket - Session: $session_id, Product: $product_type, ImgFolder: $img_folder");
error_log("Uploaded files JSON: " . $uploaded_files_json);

// 🔧 FIX: 18개 필드에 맞는 타입 문자열
// 1-6: s,s,s,s,s,s (session~MY_amount)
// 7-9: i,d,s (flyer_mesu=정수, quantity=실수, unit=문자열)
// 10-11: s,s (POtype, ordertype)
// 12-13: i,i (price, vat_price)
// 14-15: s,i (additional_options, additional_options_total)
// 16-18: s,s,s (ImgFolder, ThingCate, uploaded_files)
mysqli_stmt_bind_param($stmt, "ssssssidsssiisisss",
    $session_id, $product_type, $MY_type, $PN_type, $MY_Fsd, $MY_amount, $flyer_mesu, $quantity, $unit, $POtype, $ordertype,
    $price, $vat_price, $additional_options_json, $additional_options_total,
    $img_folder, $thing_cate, $uploaded_files_json);

if (mysqli_stmt_execute($stmt)) {
    $basket_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);

    error_log("Leaflet basket success - ID: $basket_id, flyer_mesu: $flyer_mesu");

    safe_json_response(true, [
        'basket_id' => $basket_id,
        'uploaded_files_count' => count($uploaded_files),
        'upload_path' => $img_folder,
        'flyer_mesu' => $flyer_mesu
    ], '장바구니에 추가되었습니다.');

} else {
    $error = mysqli_stmt_error($stmt);
    error_log("Leaflet execute failed: " . $error);
    mysqli_stmt_close($stmt);
    safe_json_response(false, null, '장바구니 추가 실패: ' . $error);
}

mysqli_close($db);
?>
