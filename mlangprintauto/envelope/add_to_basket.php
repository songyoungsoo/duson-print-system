<?php
// 공통 응답 함수 포함 (출력 버퍼링 및 에러 처리 포함)
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
require_once __DIR__ . '/../../includes/DataAdapter.php';  // Phase 2: 데이터 표준화
require_once __DIR__ . '/../../includes/ensure_shop_temp_columns.php';

// JSON 헤더 우선 설정
header('Content-Type: application/json; charset=utf-8');

// 세션 시작
session_start();

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

ensure_shop_temp_columns($db);

// POST 데이터 받기
$action = $_POST['action'] ?? '';
$MY_type = $_POST['MY_type'] ?? '';
$Section = $_POST['Section'] ?? ''; // 봉투 재질
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'envelope';

// 추가 정보
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 봉투 추가 옵션 데이터 (간소화)
$envelope_tape_enabled = $_POST['envelope_tape_enabled'] ?? '';
$envelope_tape_price = $_POST['envelope_tape_price'] ?? 0;
$envelope_additional_options_total = $_POST['envelope_additional_options_total'] ?? 0;

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그
error_log("=== 봉투 장바구니 추가 시작 ===");
error_log("받은 POST 데이터: " . print_r($_POST, true));
error_log("세션 ID: " . session_id());
error_log("데이터베이스 연결 상태: " . ($db ? "OK" : "실패"));

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
$upload_result = StandardUploadHandler::processUpload('envelope', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

error_log("봉투 업로드 결과: " . count($uploaded_files) . " 개 파일, 경로: $img_folder");

// 봉투 옵션명 조회
$MY_type_name = '';
$Section_name = '';
$POtype_name = '';

// MY_type 이름 조회
if (!empty($MY_type)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'Envelope'";
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

// Section 이름 조회
if (!empty($Section)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'Envelope'";
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
        $POtype_name = '마스터1도';
        break;
    case '2':
        $POtype_name = '마스터2도';
        break;
    case '3':
        $POtype_name = '칼라4도(옵셋)';
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
    'envelope_tape_enabled' => $envelope_tape_enabled,
    'envelope_tape_price' => $envelope_tape_price,
    'envelope_additional_options_total' => $envelope_additional_options_total,
    'quantity_display' => $quantity_display_from_dropdown  // ★ Pass dropdown text to DataAdapter
];

$standard_data = DataAdapter::legacyToStandard($legacy_data, 'envelope');

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

error_log("Phase 2: 봉투 표준 데이터 생성 완료 - spec_type: $spec_type, price_supply: $price_supply");

// ✅ 장바구니에 추가 - 레거시 + 표준 필드 모두 저장 (Dual-Write)
$insert_query = "INSERT INTO shop_temp (
    session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype,
    st_price, st_price_vat,
    envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price, envelope_additional_options_total,
    MY_type_name, Section_name, POtype_name,
    work_memo, upload_method, uploaded_files, ThingCate, ImgFolder,
    spec_type, spec_material, spec_size, spec_sides, spec_design,
    quantity_value, quantity_unit, quantity_sheets, quantity_display,
    price_supply, price_vat, price_vat_amount,
    product_data_json, data_version
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

error_log("SQL 쿼리: " . $insert_query);
$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    error_log("mysqli_prepare 성공");
    // 추가 옵션 데이터 처리 (간소화)
    $tape_enabled = !empty($envelope_tape_enabled) ? 1 : 0;
    $tape_quantity = 0;

    if ($tape_enabled) {
        // 메인 수량을 테이프 수량으로 사용
        $tape_quantity = intval($MY_amount);
    }

    // 디버그 로깅
    error_log("=== 봉투 장바구니 저장 디버그 ===");
    error_log("tape_enabled: " . $tape_enabled);
    error_log("tape_quantity: " . $tape_quantity);
    error_log("envelope_tape_price: " . intval($envelope_tape_price));
    error_log("envelope_additional_options_total: " . intval($envelope_additional_options_total));
    error_log("Basic data: session_id=$session_id, product_type=$product_type, MY_type=$MY_type, Section=$Section, POtype=$POtype, MY_amount=$MY_amount, ordertype=$ordertype, price=$price, vat_price=$vat_price");

    // 정수값들을 변수에 할당 (참조 전달을 위해)
    $envelope_tape_price_int = intval($envelope_tape_price);
    $envelope_additional_options_total_int = intval($envelope_additional_options_total);

    error_log("bind_param 전 - 파라미터 수: 35개 (레거시 21개 + 표준 14개)");
    error_log("=== 봉투 옵션명 디버그 ===");
    error_log("MY_type_name: " . $MY_type_name);
    error_log("Section_name: " . $Section_name);
    error_log("POtype_name: " . $POtype_name);

    // Phase 2: 35개 파라미터 (레거시 21개 + 표준 14개)
    // 타입 순서: session_id(s), product_type(s), MY_type(s), Section(s), POtype(s), MY_amount(s), ordertype(s),
    //            st_price(d), st_price_vat(d), envelope_tape_enabled(i), envelope_tape_quantity(i),
    //            envelope_tape_price(i), envelope_additional_options_total(i), MY_type_name(s), Section_name(s), POtype_name(s),
    //            work_memo(s), upload_method(s), uploaded_files(s), ThingCate(s), ImgFolder(s),
    //            spec_type(s), spec_material(s), spec_size(s), spec_sides(s), spec_design(s),
    //            quantity_value(d), quantity_unit(s), quantity_sheets(i), quantity_display(s),
    //            price_supply(i), price_vat(i), price_vat_amount(i), product_data_json(s), data_version(i)
    $bind_result = mysqli_stmt_bind_param($stmt, "sssssssddiiissssssssssssssdsiiiiisi",
        // 레거시 필드 (21개)
        $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype,
        $price, $vat_price, $tape_enabled, $tape_quantity,
        $envelope_tape_price_int, $envelope_additional_options_total_int,
        $MY_type_name, $Section_name, $POtype_name,
        $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder,
        // 표준 필드 (14개)
        $spec_type, $spec_material, $spec_size, $spec_sides, $spec_design,
        $quantity_value, $quantity_unit, $quantity_sheets, $quantity_display,
        $price_supply, $price_vat, $price_vat_amount,
        $product_data_json, $data_version
    );

    if (!$bind_result) {
        error_log("mysqli_stmt_bind_param 실패: " . mysqli_stmt_error($stmt));
        safe_json_response(false, null, 'bind_param 오류가 발생했습니다.');
    }

    error_log("bind_param 성공, execute 시도 중...");
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
        $error_msg = mysqli_stmt_error($stmt);
        error_log("봉투 장바구니 저장 실패: " . $error_msg);
        error_log("SQL: " . $insert_query);
        mysqli_stmt_close($stmt);
        safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . $error_msg);
    }
} else {
    $error_msg = mysqli_error($db);
    error_log("봉투 장바구니 prepare 실패: " . $error_msg);
    error_log("SQL: " . $insert_query);
    safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . $error_msg);
}

mysqli_close($db);

?>
