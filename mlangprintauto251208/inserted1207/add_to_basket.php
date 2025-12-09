<?php
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 🔍 디버그: 받은 데이터 로깅
error_log("=== DEBUG START ===");
error_log("_FILES 내용: " . print_r($_FILES, true));
error_log("_POST 키 목록: " . implode(', ', array_keys($_POST)));
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("Content-Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'not set'));
error_log("=== DEBUG END ===");

// POST 데이터
$session_id = session_id();
$product_type = $_POST['product_type'] ?? 'inserted'; // ✅ 기본값 inserted로 수정
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

// ✅ quantityTwo(매수) 조회 - 전단지 가격 테이블에서 실제 매수 가져오기
$mesu = '';
$qty_query = "SELECT quantityTwo FROM mlangprintauto_inserted
              WHERE style = ? AND Section = ? AND TreeSelect = ? AND POtype = ? AND quantity = ?
              LIMIT 1";
$qty_stmt = mysqli_prepare($db, $qty_query);
if ($qty_stmt) {
    mysqli_stmt_bind_param($qty_stmt, "sssss", $MY_type, $PN_type, $MY_Fsd, $POtype, $MY_amount);
    mysqli_stmt_execute($qty_stmt);
    $qty_result = mysqli_stmt_get_result($qty_stmt);
    if ($qty_row = mysqli_fetch_assoc($qty_result)) {
        $mesu = $qty_row['quantityTwo'];
    }
    mysqli_stmt_close($qty_stmt);
}
error_log("전단지 매수 조회: MY_amount=$MY_amount, mesu=$mesu");

// ✅ 파일 업로드 처리 (StandardUploadHandler 사용)
$upload_result = StandardUploadHandler::processUpload('inserted', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$upload_count = count($uploaded_files);

error_log("전단지 업로드 결과: $upload_count 개 파일, 경로: $img_folder");

// uploaded_files를 JSON으로 변환 (테이블의 uploaded_files 컬럼에 저장)
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

// 개별 옵션 값 추출 (DB 컬럼용)
$coating_enabled = $additional_options['coating_enabled'];
$coating_type = $additional_options['coating_type'];
$coating_price = $additional_options['coating_price'];
$folding_enabled = $additional_options['folding_enabled'];
$folding_type = $additional_options['folding_type'];
$folding_price = $additional_options['folding_price'];
$creasing_enabled = $additional_options['creasing_enabled'];
$creasing_lines = $additional_options['creasing_lines'];
$creasing_price = $additional_options['creasing_price'];

// INSERT (개별 옵션 컬럼 추가 - 코팅/접지/오시)
$sql = "INSERT INTO shop_temp (
    session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, quantityTwo, mesu, POtype, ordertype,
    st_price, st_price_vat, additional_options, additional_options_total,
    coating_enabled, coating_type, coating_price,
    folding_enabled, folding_type, folding_price,
    creasing_enabled, creasing_lines, creasing_price,
    ImgFolder, ThingCate, uploaded_files
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $sql);
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($db));
    safe_json_response(false, null, 'SQL 준비 실패: ' . mysqli_error($db));
}

// 디버깅 로그
error_log("Inserted add_to_basket - Session: $session_id, Product: $product_type, ImgFolder: $img_folder");
error_log("Uploaded files JSON: " . $uploaded_files_json);
error_log("옵션: coating=$coating_enabled/$coating_type/$coating_price, folding=$folding_enabled/$folding_type/$folding_price, creasing=$creasing_enabled/$creasing_lines/$creasing_price");

// 26개 파라미터 검증:
// 1-10: session(s) + product(s) + MY_type(s) + PN_type(s) + MY_Fsd(s) + MY_amount(s) + quantityTwo(s) + mesu(s) + POtype(s) + ordertype(s)
// 11-14: price(i) + vat(i) + options_json(s) + options_total(i)
// 15-17: coating_enabled(i) + coating_type(s) + coating_price(i)
// 18-20: folding_enabled(i) + folding_type(s) + folding_price(i)
// 21-23: creasing_enabled(i) + creasing_lines(i) + creasing_price(i)
// 24-26: folder(s) + cate(s) + files(s)
mysqli_stmt_bind_param($stmt, "ssssssssssiisiisiisiiiisss",
    $session_id, $product_type, $MY_type, $PN_type, $MY_Fsd, $MY_amount, $mesu, $mesu, $POtype, $ordertype,
    $price, $vat_price, $additional_options_json, $additional_options_total,
    $coating_enabled, $coating_type, $coating_price,
    $folding_enabled, $folding_type, $folding_price,
    $creasing_enabled, $creasing_lines, $creasing_price,
    $img_folder, $thing_cate, $uploaded_files_json);

if (mysqli_stmt_execute($stmt)) {
    $basket_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);

    error_log("Inserted basket success - ID: $basket_id");

    // 견적서 모달용 데이터 포함 (매수 정보 추가)
    safe_json_response(true, [
        'cart_no' => $basket_id,
        'basket_id' => $basket_id,
        'uploaded_files_count' => count($uploaded_files),
        'upload_path' => $img_folder,
        'product_name' => '전단지',
        'specification' => "{$MY_Fsd}절 / {$PN_type} / {$ordertype}",
        'quantity' => intval($MY_amount),            // 연 수
        'quantityTwo' => intval($mesu),              // 매수 (장)
        'quantity_display' => number_format(intval($mesu)) . '장',  // 표시용
        'unit_price' => intval($mesu) > 0 ? round($price / intval($mesu), 2) : 0,  // 장당 단가
        'supply_price' => $price,
        'vat_price' => $vat_price,
        'total_price' => $vat_price
    ], '장바구니에 추가되었습니다.');

} else {
    $error = mysqli_stmt_error($stmt);
    error_log("Inserted execute failed: " . $error);
    mysqli_stmt_close($stmt);
    safe_json_response(false, null, '장바구니 추가 실패: ' . $error);
}

mysqli_close($db);
?>
