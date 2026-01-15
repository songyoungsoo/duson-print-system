<?php
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
require_once __DIR__ . '/../../includes/DataAdapter.php';  // Phase 2: 데이터 표준화

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

// 필수 필드 검증 (상세 디버깅 포함)
$missing_fields = [];
if (empty($MY_type)) $missing_fields[] = 'MY_type';
if (empty($PN_type)) $missing_fields[] = 'PN_type';
if (empty($MY_Fsd)) $missing_fields[] = 'MY_Fsd';
if (empty($POtype)) $missing_fields[] = 'POtype';
if (empty($MY_amount)) $missing_fields[] = 'MY_amount';
if (empty($ordertype)) $missing_fields[] = 'ordertype';

if (!empty($missing_fields)) {
    error_log("누락된 필드: " . implode(', ', $missing_fields));
    error_log("받은 값들: MY_type=$MY_type, PN_type=$PN_type, MY_Fsd=$MY_Fsd, POtype=$POtype, MY_amount=$MY_amount, ordertype=$ordertype");
    safe_json_response(false, null, '필수 정보가 누락되었습니다: ' . implode(', ', $missing_fields));
}

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

// 🆕 매수(mesu) 처리: MY_amountRight에서 숫자만 추출 (예: "2000장" → 2000)
$mesu = 0;
if (!empty($_POST['MY_amountRight'])) {
    $my_amount_right = $_POST['MY_amountRight'];
    // "장" 또는 다른 문자 제거, 숫자만 추출
    $mesu = intval(preg_replace('/[^0-9]/', '', $my_amount_right));
    error_log("전단지 매수 수신: MY_amountRight = '$my_amount_right' → mesu = $mesu");
} else {
    error_log("⚠️ MY_amountRight 누락 - mesu는 0으로 저장됨");
}

// 전단지 옵션명 조회
$MY_type_name = '';
$MY_Fsd_name = '';
$PN_type_name = '';
$POtype_name = '';

// MY_type 이름 조회
if (!empty($MY_type)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'Inserted'";
    $name_stmt = mysqli_prepare($db, $name_query);
    if ($name_stmt) {
        mysqli_stmt_bind_param($name_stmt, "s", $MY_type);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        if ($name_row = mysqli_fetch_assoc($name_result)) {
            $MY_type_name = $name_row['title'];
        }
        mysqli_stmt_close($name_stmt);
    }
}

// MY_Fsd 이름 조회 (용지)
if (!empty($MY_Fsd)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'Inserted'";
    $name_stmt = mysqli_prepare($db, $name_query);
    if ($name_stmt) {
        mysqli_stmt_bind_param($name_stmt, "s", $MY_Fsd);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        if ($name_row = mysqli_fetch_assoc($name_result)) {
            $MY_Fsd_name = $name_row['title'];
        }
        mysqli_stmt_close($name_stmt);
    }
}

// PN_type 이름 조회 (규격)
if (!empty($PN_type)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'Inserted'";
    $name_stmt = mysqli_prepare($db, $name_query);
    if ($name_stmt) {
        mysqli_stmt_bind_param($name_stmt, "s", $PN_type);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        if ($name_row = mysqli_fetch_assoc($name_result)) {
            $PN_type_name = $name_row['title'];
        }
        mysqli_stmt_close($name_stmt);
    }
}

// POtype 이름 설정 (도수)
switch ($POtype) {
    case '1':
        $POtype_name = '단면칼라';
        break;
    case '2':
        $POtype_name = '양면칼라';
        break;
    case '4':
        $POtype_name = '단면흑백';
        break;
    case '5':
        $POtype_name = '양면흑백';
        break;
    default:
        $POtype_name = '';
}

// ★ NEW: Receive quantity_display from JavaScript (dropdown text)
$quantity_display_from_dropdown = $_POST['quantity_display'] ?? '';

// ✅ Phase 2: 표준 데이터 생성 (레거시 → 표준)
$legacy_data = [
    'MY_type' => $MY_type,
    'MY_type_name' => $MY_type_name,
    'MY_Fsd' => $MY_Fsd,
    'MY_Fsd_name' => $MY_Fsd_name,
    'PN_type' => $PN_type,
    'PN_type_name' => $PN_type_name,
    'POtype' => $POtype,
    'POtype_name' => $POtype_name,
    'MY_amount' => $MY_amount,
    'mesu' => $mesu,
    'ordertype' => $ordertype,
    'Order_PriceForm' => $price,
    'Total_PriceForm' => $vat_price,
    'additional_options' => $additional_options_json,
    'quantity_display' => $quantity_display_from_dropdown  // ★ Pass dropdown text to DataAdapter
];

$standard_data = DataAdapter::legacyToStandard($legacy_data, 'inserted');

// 표준 필드 추출
$spec_type = $standard_data['spec_type'];
$spec_material = $standard_data['spec_material'];
$spec_size = $standard_data['spec_size'];
$spec_sides = $standard_data['spec_sides'];
$spec_design = $standard_data['spec_design'];
$quantity_value = $standard_data['quantity_value'];
$quantity_unit = $standard_data['quantity_unit'];
$quantity_sheets = $standard_data['quantity_sheets'];
$quantity_display = $standard_data['quantity_display'];  // ★ Use value from DataAdapter (includes dropdown text)
$price_supply = $standard_data['price_supply'];
$price_vat = $standard_data['price_vat'];
$price_vat_amount = $standard_data['price_vat_amount'];
$product_data_json = json_encode($standard_data, JSON_UNESCAPED_UNICODE);
$data_version = 2;  // Phase 2 신규 데이터

error_log("Phase 2: 전단지 표준 데이터 생성 완료 - spec_type: $spec_type, price_supply: $price_supply");

// ✅ 장바구니에 추가 - 레거시 + 표준 필드 모두 저장 (Dual-Write)
$sql = "INSERT INTO shop_temp (
    session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, POtype, ordertype,
    st_price, st_price_vat, additional_options, additional_options_total, mesu,
    ImgFolder, ThingCate, uploaded_files,
    spec_type, spec_material, spec_size, spec_sides, spec_design,
    quantity_value, quantity_unit, quantity_sheets, quantity_display,
    price_supply, price_vat, price_vat_amount,
    product_data_json, data_version
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $sql);
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($db));
    safe_json_response(false, null, 'SQL 준비 실패: ' . mysqli_error($db));
}

// 디버깅 로그
error_log("Inserted add_to_basket - Session: $session_id, Product: $product_type, ImgFolder: $img_folder, mesu: $mesu");
error_log("Uploaded files JSON: " . $uploaded_files_json);

// Phase 2: 30개 파라미터 (레거시 16개 + 표준 14개)
// 타입 순서: session_id(s), product_type(s), MY_type(s), PN_type(s), MY_Fsd(s), MY_amount(s), POtype(s), ordertype(s),
//            st_price(d), st_price_vat(d), additional_options(s), additional_options_total(i), mesu(s),
//            ImgFolder(s), ThingCate(s), uploaded_files(s),
//            spec_type(s), spec_material(s), spec_size(s), spec_sides(s), spec_design(s),
//            quantity_value(d), quantity_unit(s), quantity_sheets(i), quantity_display(s),
//            price_supply(i), price_vat(i), price_vat_amount(i),
//            product_data_json(s), data_version(i)
// ✅ 2026-01-15: 타입 문자열 수정 - 위치12 additional_options_total(i), 위치13 mesu(s)
mysqli_stmt_bind_param($stmt, "ssssssssddsisssssssssdsisiiisi",
    // 레거시 필드 (16개)
    $session_id, $product_type, $MY_type, $PN_type, $MY_Fsd, $MY_amount, $POtype, $ordertype,
    $price, $vat_price, $additional_options_json, $additional_options_total, $mesu,
    $img_folder, $thing_cate, $uploaded_files_json,
    // 표준 필드 (14개)
    $spec_type, $spec_material, $spec_size, $spec_sides, $spec_design,
    $quantity_value, $quantity_unit, $quantity_sheets, $quantity_display,
    $price_supply, $price_vat, $price_vat_amount,
    $product_data_json, $data_version
);

if (mysqli_stmt_execute($stmt)) {
    $basket_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);

    error_log("Inserted basket success - ID: $basket_id");

    safe_json_response(true, [
        'basket_id' => $basket_id,
        'uploaded_files_count' => count($uploaded_files),
        'upload_path' => $img_folder
    ], '장바구니에 추가되었습니다.');

} else {
    $error = mysqli_stmt_error($stmt);
    error_log("Inserted execute failed: " . $error);
    mysqli_stmt_close($stmt);
    safe_json_response(false, null, '장바구니 추가 실패: ' . $error);
}

mysqli_close($db);
?>
