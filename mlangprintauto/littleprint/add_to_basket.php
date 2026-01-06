<?php
// 안전한 JSON 응답 및 표준 업로드 핸들러
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
require_once __DIR__ . '/../../includes/DataAdapter.php';  // Phase 2: 데이터 표준화

// 공통 함수 포함
include "../../includes/functions.php";

// 세션 및 데이터베이스 설정
$session_id = check_session();
include "../../db.php";
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터 받기
$product_type = $_POST['product_type'] ?? 'littleprint';  // 제품 타입
$MY_type = $_POST['MY_type'] ?? '';        // 구분
$Section = $_POST['Section'] ?? $_POST['TreeSelect'] ?? '';  // 재질 (TreeSelect 호환)
$PN_type = $_POST['PN_type'] ?? '';       // 규격
$MY_amount = $_POST['MY_amount'] ?? '';    // 수량
$POtype = $_POST['POtype'] ?? '';          // 인쇄면
$ordertype = $_POST['ordertype'] ?? '';    // 디자인편집
$price = intval($_POST['calculated_price'] ?? $_POST['price'] ?? 0);
$vat_price = intval($_POST['calculated_vat_price'] ?? $_POST['vat_price'] ?? 0);

// 추가 옵션 데이터 받기 (JSON 방식)
$additional_options = [
    'coating_enabled' => isset($_POST['coating_enabled']) ? intval($_POST['coating_enabled']) : 0,
    'coating_type' => isset($_POST['coating_type']) ? $_POST['coating_type'] : '',
    'coating_price' => isset($_POST['coating_price']) ? intval($_POST['coating_price']) : 0,
    'folding_enabled' => isset($_POST['folding_enabled']) ? intval($_POST['folding_enabled']) : 0,
    'folding_type' => isset($_POST['folding_type']) ? $_POST['folding_type'] : '',
    'folding_price' => isset($_POST['folding_price']) ? intval($_POST['folding_price']) : 0,
    'creasing_enabled' => isset($_POST['creasing_enabled']) ? intval($_POST['creasing_enabled']) : 0,
    'creasing_lines' => isset($_POST['creasing_lines']) ? intval($_POST['creasing_lines']) : 0,
    'creasing_price' => isset($_POST['creasing_price']) ? intval($_POST['creasing_price']) : 0
];
$additional_options_json = json_encode($additional_options, JSON_UNESCAPED_UNICODE);
$additional_options_total = isset($_POST['additional_options_total']) ? intval($_POST['additional_options_total']) : 0;

// 디버깅을 위한 로그 (나중에 제거 가능)
error_log("=== 포스터 장바구니 디버그 ===");
error_log("전체 POST 데이터: " . print_r($_POST, true));
error_log("전체 FILES 데이터: " . print_r($_FILES, true));
error_log("받은 값: MY_type=$MY_type, Section=$Section, PN_type=$PN_type, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype");
error_log("가격: price=$price, vat_price=$vat_price");
error_log("추가 옵션: $additional_options_json, total=$additional_options_total");

// 디버그 로그 파일에 기록 (오류 억제)
$debug_log_file = __DIR__ . '/debug_cart.log';
$log_time = date('Y-m-d H:i:s');
$log_message = "\n[$log_time] 포스터 장바구니 추가:\n";
$log_message .= "기본 정보: MY_type=$MY_type, Section=$Section, PN_type=$PN_type, MY_amount=$MY_amount\n";
$log_message .= "가격 정보: price=$price, vat_price=$vat_price\n";
$log_message .= "추가 옵션 (JSON): $additional_options_json\n";
$log_message .= "추가 옵션 총액: $additional_options_total\n";
$log_message .= "POST 데이터: " . print_r($_POST, true) . "\n";
$log_message .= str_repeat('-', 80) . "\n";
@file_put_contents($debug_log_file, $log_message, FILE_APPEND); // @ 오류 억제

// 입력값 검증 (PN_type는 선택적 파라미터)
if (empty($MY_type) || empty($Section) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    $missing_fields = [];
    if (empty($MY_type)) $missing_fields[] = 'MY_type (받은값: ' . ($MY_type ?: 'empty') . ')';
    if (empty($Section)) $missing_fields[] = 'Section/TreeSelect (받은값: ' . ($Section ?: 'empty') . ', POST[TreeSelect]: ' . ($_POST['TreeSelect'] ?? 'none') . ')';
    if (empty($MY_amount)) $missing_fields[] = 'MY_amount (받은값: ' . ($MY_amount ?: 'empty') . ')';
    if (empty($POtype)) $missing_fields[] = 'POtype (받은값: ' . ($POtype ?: 'empty') . ')';
    if (empty($ordertype)) $missing_fields[] = 'ordertype (받은값: ' . ($ordertype ?: 'empty') . ')';

    error_log("❌ 입력값 검증 실패: " . implode(', ', $missing_fields));
    safe_json_response(false, null, '필수 입력값이 누락되었습니다: ' . implode(', ', $missing_fields));
}

// 장바구니 테이블이 없으면 생성
$create_table_query = "CREATE TABLE IF NOT EXISTS shop_temp (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    MY_type VARCHAR(50),
    TreeSelect VARCHAR(50),
    PN_type VARCHAR(50),
    MY_amount VARCHAR(50),
    POtype VARCHAR(10),
    ordertype VARCHAR(50),
    st_price INT DEFAULT 0,
    st_price_vat INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($db, $create_table_query)) {
    safe_json_response(false, null, '테이블 생성 오류: ' . mysqli_error($db));
}

// 포스터용 필드들이 없으면 추가
$required_columns = [
    'product_type' => "VARCHAR(50) NOT NULL DEFAULT 'littleprint'",
    'MY_type' => "VARCHAR(50)",
    'Section' => "VARCHAR(50)",
    'MY_amount' => "VARCHAR(50)",
    'POtype' => "VARCHAR(10)",
    'ordertype' => "VARCHAR(50)",
    // 추가 옵션 필드들 (JSON 방식)
    'additional_options' => "TEXT",
    'additional_options_total' => "INT DEFAULT 0"
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($db, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        if (!mysqli_query($db, $add_column_query)) {
            safe_json_response(false, null, "컬럼 $column_name 추가 오류: " . mysqli_error($db));
        }
    }
}

// ✅ 파일 업로드 처리 (StandardUploadHandler 사용)
$upload_result = StandardUploadHandler::processUpload('littleprint', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$upload_folder_db = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$upload_count = count($uploaded_files);

error_log("포스터 업로드 결과: $upload_count 개 파일, 경로: $upload_folder_db");

// ImgFolder 컬럼 추가
$check_column_query = "SHOW COLUMNS FROM shop_temp LIKE 'ImgFolder'";
$column_result = mysqli_query($db, $check_column_query);
if (mysqli_num_rows($column_result) == 0) {
    $add_column_query = "ALTER TABLE shop_temp ADD COLUMN ImgFolder VARCHAR(255)";
    mysqli_query($db, $add_column_query);
}

// 파일 정보 JSON 변환
$files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

// 포스터 단위는 '매' (sheets)
$unit = '매';

// 포스터 옵션명 조회
$MY_type_name = '';
$Section_name = '';
$PN_type_name = '';
$POtype_name = '';

// MY_type 이름 조회
if (!empty($MY_type)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'LittlePrint'";
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

// Section 이름 조회 (재질)
if (!empty($Section)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'LittlePrint'";
    $name_stmt = mysqli_prepare($db, $name_query);
    if ($name_stmt) {
        mysqli_stmt_bind_param($name_stmt, "s", $Section);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        if ($name_row = mysqli_fetch_assoc($name_result)) {
            $Section_name = $name_row['title'];
        }
        mysqli_stmt_close($name_stmt);
    }
}

// PN_type 이름 조회 (규격)
if (!empty($PN_type)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'LittlePrint'";
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

// POtype 이름 설정 (인쇄면)
switch ($POtype) {
    case '1':
        $POtype_name = '단면';
        break;
    case '2':
        $POtype_name = '양면';
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
    'Section' => $Section,
    'Section_name' => $Section_name,
    'PN_type' => $PN_type,
    'PN_type_name' => $PN_type_name,
    'POtype' => $POtype,
    'POtype_name' => $POtype_name,
    'MY_amount' => $MY_amount,
    'ordertype' => $ordertype,
    'price' => $price,
    'vat_price' => $vat_price,
    'additional_options' => $additional_options_json,
    'quantity_display' => $quantity_display_from_dropdown  // ★ Pass dropdown text to DataAdapter
];

$standard_data = DataAdapter::legacyToStandard($legacy_data, 'littleprint');

// 표준 필드 추출
$spec_type = $standard_data['spec_type'];
$spec_material = $standard_data['spec_material'];
$spec_size = $standard_data['spec_size'];
$spec_sides = $standard_data['spec_sides'];
$spec_design = $standard_data['spec_design'];
$quantity_value = $standard_data['quantity_value'];
$quantity_unit = $standard_data['quantity_unit'];
$quantity_sheets = $standard_data['quantity_sheets'];
$quantity_display = $standard_data['quantity_display'];  // ★ Use value from DataAdapter
$price_supply = $standard_data['price_supply'];
$price_vat = $standard_data['price_vat'];
$price_vat_amount = $standard_data['price_vat_amount'];
$product_data_json = json_encode($standard_data, JSON_UNESCAPED_UNICODE);
$data_version = 2;  // Phase 2 신규 데이터

error_log("Phase 2: 포스터 표준 데이터 생성 완료 - spec_type: $spec_type, price_supply: $price_supply");

// ✅ 장바구니에 추가 - 레거시 + 표준 필드 모두 저장 (Dual-Write)
$insert_query = "INSERT INTO shop_temp (
    session_id, product_type, MY_type, Section, PN_type, MY_amount, POtype, ordertype, unit,
    st_price, st_price_vat, additional_options, additional_options_total,
    ImgFolder, ThingCate, uploaded_files,
    spec_type, spec_material, spec_size, spec_sides, spec_design,
    quantity_value, quantity_unit, quantity_sheets, quantity_display,
    price_supply, price_vat, price_vat_amount,
    product_data_json, data_version
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    // Phase 2: 30개 파라미터 (레거시 16개 + 표준 14개)
    // 타입 순서: session_id(s), product_type(s), MY_type(s), Section(s), PN_type(s), MY_amount(s), POtype(s), ordertype(s), unit(s),
    //            st_price(d), st_price_vat(d), additional_options(s), additional_options_total(i),
    //            ImgFolder(s), ThingCate(s), uploaded_files(s),
    //            spec_type(s), spec_material(s), spec_size(s), spec_sides(s), spec_design(s),
    //            quantity_value(d), quantity_unit(s), quantity_sheets(i), quantity_display(s),
    //            price_supply(i), price_vat(i), price_vat_amount(i), product_data_json(s), data_version(i)
    mysqli_stmt_bind_param($stmt, "sssssssssddsissssssssdsisiiisi",
        // 레거시 필드 (16개)
        $session_id, $product_type, $MY_type, $Section, $PN_type, $MY_amount, $POtype, $ordertype, $unit,
        $price, $vat_price, $additional_options_json, $additional_options_total,
        $upload_folder_db, $thing_cate, $files_json,
        // 표준 필드 (14개)
        $spec_type, $spec_material, $spec_size, $spec_sides, $spec_design,
        $quantity_value, $quantity_unit, $quantity_sheets, $quantity_display,
        $price_supply, $price_vat, $price_vat_amount,
        $product_data_json, $data_version
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $message = '장바구니에 추가되었습니다.';
        if ($upload_count > 0) {
            $message .= " (파일 {$upload_count}개 업로드 완료)";
        }
        safe_json_response(true, ['upload_count' => $upload_count], $message);
    } else {
        safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);
} else {
    safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db));
}

mysqli_close($db);
?>