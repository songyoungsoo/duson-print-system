<?php
// 공통 응답 함수 포함 (출력 버퍼링 및 에러 처리 포함)
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/DataAdapter.php';  // Phase 2: 데이터 표준화

// JSON 헤더 우선 설정
header('Content-Type: application/json; charset=utf-8');

// 전체 에러 처리
try {
    error_log("=== 상품권 장바구니 처리 시작 ===");

    // 세션 시작
    session_start();
    error_log("세션 시작 완료");

    // 공통 함수 포함
    error_log("functions.php 포함 시작");
    include "../../includes/functions.php";
    error_log("functions.php 포함 완료");

    error_log("StandardUploadHandler.php 포함 시작");
    require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
    error_log("StandardUploadHandler.php 포함 완료");

    error_log("db.php 포함 시작");
    include "../../db.php";
    error_log("db.php 포함 완료");

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터 받기
$action = $_POST['action'] ?? '';
$MY_type = $_POST['MY_type'] ?? '';
$Section = $_POST['Section'] ?? ''; // 상품권 재질
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'merchandisebond';

// 추가 정보
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 프리미엄 옵션 데이터 수집 및 검증
$premium_options = [];
$premium_total = 0;

// 프리미엄 옵션 데이터 추출
if (isset($_POST['premium_options_total'])) {
    $premium_total = intval($_POST['premium_options_total']);

    // 상품권 프리미엄 옵션들 (명함과 동일)
    $option_names = ['foil', 'numbering', 'perforation', 'rounding', 'creasing'];

    foreach ($option_names as $option) {
        if (isset($_POST["{$option}_enabled"]) && $_POST["{$option}_enabled"] == '1') {
            $premium_options["{$option}_enabled"] = true;

            // 타입이 있는 옵션들 (코팅, 엠보싱, 금박)
            if (isset($_POST["{$option}_type"]) && !empty($_POST["{$option}_type"])) {
                $premium_options["{$option}_type"] = $_POST["{$option}_type"];
            }

            // 개별 가격 저장
            if (isset($_POST["{$option}_price"])) {
                $premium_options["{$option}_price"] = intval($_POST["{$option}_price"]);
            }
        }
    }

    $premium_options['premium_options_total'] = $premium_total;
}

// JSON 형태로 변환
$premium_options_json = json_encode($premium_options, JSON_UNESCAPED_UNICODE);

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그 (안전하게)
try {
    error_log("상품권 장바구니 추가 시작");
    error_log("POST 데이터 키들: " . implode(', ', array_keys($_POST)));
    error_log("파일 데이터: " . (isset($_FILES['uploaded_files']) ? "있음" : "없음"));
} catch (Exception $e) {
    error_log("디버그 로깅 오류: " . $e->getMessage());
}

if (empty($MY_type) || empty($Section) || empty($POtype) || empty($MY_amount) || empty($ordertype)) {
    $missing_fields = [];
    if (empty($MY_type)) $missing_fields[] = 'MY_type';
    if (empty($Section)) $missing_fields[] = 'Section';
    if (empty($POtype)) $missing_fields[] = 'POtype';
    if (empty($MY_amount)) $missing_fields[] = 'MY_amount';
    if (empty($ordertype)) $missing_fields[] = 'ordertype';
    
    safe_json_response(false, null, '필수 정보가 누락되었습니다: ' . implode(', ', $missing_fields));
}

// 세션 ID 가져오기
$session_id = session_id();

// 디버그 로깅 강화
error_log("=== Cart Debug Info ===");
error_log("Session ID: " . $session_id);
error_log("Action: " . $action);
error_log("MY_type: " . $MY_type);
error_log("Section: " . $Section);
error_log("POtype: " . $POtype);
error_log("MY_amount: " . $MY_amount);
error_log("Price: " . $price);
error_log("Work memo length: " . strlen($work_memo));

// 파일 업로드 처리 (StandardUploadHandler 사용)
$upload_result = StandardUploadHandler::processUpload('merchandisebond', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

error_log("상품권 업로드 결과: " . count($uploaded_files) . " 개 파일, 경로: $img_folder");

// 필요한 컬럼이 있는지 확인하고 없으면 추가
$required_columns = [
    'session_id' => 'VARCHAR(255)',
    'product_type' => 'VARCHAR(50)',
    'MY_type' => 'VARCHAR(50)',
    'Section' => 'VARCHAR(50)',
    'POtype' => 'VARCHAR(50)',
    'MY_amount' => 'VARCHAR(50)',
    'ordertype' => 'VARCHAR(50)',
    'st_price' => 'INT(11)',
    'st_price_vat' => 'INT(11)',
    'premium_options' => 'TEXT',
    'premium_options_total' => 'INT(11)',
    'work_memo' => 'TEXT',
    'upload_method' => 'VARCHAR(50)',
    'uploaded_files' => 'TEXT',
    'ThingCate' => 'VARCHAR(255)',
    'ImgFolder' => 'VARCHAR(255)'
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

// 상품권 옵션명 조회
$MY_type_name = '';
$Section_name = '';
$POtype_name = '';

// MY_type 이름 조회
if (!empty($MY_type)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'MerchandiseBond'";
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
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'MerchandiseBond'";
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

// POtype 이름 설정
switch ($POtype) {
    case '1':
        $POtype_name = '단면칼라';
        break;
    case '2':
        $POtype_name = '양면칼라';
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
    'POtype' => $POtype,
    'POtype_name' => $POtype_name,
    'MY_amount' => $MY_amount,
    'ordertype' => $ordertype,
    'price' => $price,
    'vat_price' => $vat_price,
    'premium_options' => $premium_options_json,
    'quantity_display' => $quantity_display_from_dropdown  // ★ Pass dropdown text to DataAdapter
];

$standard_data = DataAdapter::legacyToStandard($legacy_data, 'merchandisebond');

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

error_log("Phase 2: 상품권 표준 데이터 생성 완료 - spec_type: $spec_type, price_supply: $price_supply");

// ✅ 장바구니에 추가 - 레거시 + 표준 필드 모두 저장 (Dual-Write)
$insert_query = "INSERT INTO shop_temp (
    session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype,
    st_price, st_price_vat, premium_options, premium_options_total,
    work_memo, upload_method, uploaded_files, ThingCate, ImgFolder,
    spec_type, spec_material, spec_size, spec_sides, spec_design,
    quantity_value, quantity_unit, quantity_sheets, quantity_display,
    price_supply, price_vat, price_vat_amount,
    product_data_json, data_version
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    // Phase 2: 30개 파라미터 (레거시 16개 + 표준 14개)
    // 타입 순서: session_id(s), product_type(s), MY_type(s), Section(s), POtype(s), MY_amount(s), ordertype(s),
    //            st_price(d), st_price_vat(d), premium_options(s), premium_options_total(i),
    //            work_memo(s), upload_method(s), uploaded_files(s), ThingCate(s), ImgFolder(s),
    //            spec_type(s), spec_material(s), spec_size(s), spec_sides(s), spec_design(s),
    //            quantity_value(d), quantity_unit(s), quantity_sheets(i), quantity_display(s),
    //            price_supply(i), price_vat(i), price_vat_amount(i), product_data_json(s), data_version(i)
    // ✅ 2026-01-15: 타입 문자열 수정 - 위치13 upload_method(s), 위치25 quantity_display(s)
mysqli_stmt_bind_param($stmt, "sssssssddsissssssssssdsisiiisi",
        // 레거시 필드 (16개)
        $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype,
        $price, $vat_price, $premium_options_json, $premium_total,
        $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder,
        // 표준 필드 (14개)
        $spec_type, $spec_material, $spec_size, $spec_sides, $spec_design,
        $quantity_value, $quantity_unit, $quantity_sheets, $quantity_display,
        $price_supply, $price_vat, $price_vat_amount,
        $product_data_json, $data_version
    );

    if (mysqli_stmt_execute($stmt)) {
        $basket_id = mysqli_insert_id($db);
        mysqli_stmt_close($stmt);

        $response_data = [
            'basket_id' => $basket_id,
            'uploaded_files_count' => count($uploaded_files),
            'img_folder' => $img_folder,
            'thing_cate' => $thing_cate
        ];

        safe_json_response(true, $response_data, '장바구니에 추가되었습니다.');
        
    } else {
        mysqli_stmt_close($stmt);
        safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
    }
} else {
    safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db));
}

mysqli_close($db);

} catch (Exception $e) {
    // 치명적인 오류 처리
    error_log("상품권 장바구니 치명적 오류: " . $e->getMessage());
    error_log("오류 스택: " . $e->getTraceAsString());
    safe_json_response(false, null, '시스템 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
} catch (Error $e) {
    // PHP 7+ Fatal Error 처리
    error_log("상품권 장바구니 Fatal Error: " . $e->getMessage());
    error_log("오류 스택: " . $e->getTraceAsString());
    safe_json_response(false, null, '치명적인 오류가 발생했습니다. 관리자에게 문의해주세요.');
}

?>
