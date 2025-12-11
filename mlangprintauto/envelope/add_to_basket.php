<?php
// 공통 응답 함수 포함 (출력 버퍼링 및 에러 처리 포함)
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';

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

// 필요한 컬럼이 있는지 확인하고 없으면 추가
$required_columns = [
    'envelope_tape_enabled' => 'TINYINT(1) DEFAULT 0',
    'envelope_tape_quantity' => 'INT(11) DEFAULT 0',
    'envelope_tape_price' => 'INT(11) DEFAULT 0',
    'envelope_additional_options_total' => 'INT(11) DEFAULT 0',
    'MY_type_name' => 'VARCHAR(100) DEFAULT NULL',
    'Section_name' => 'VARCHAR(100) DEFAULT NULL',
    'POtype_name' => 'VARCHAR(100) DEFAULT NULL',
    'ImgFolder' => 'VARCHAR(255) DEFAULT NULL',
    'ThingCate' => 'VARCHAR(255) DEFAULT NULL'
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($db, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        if (!mysqli_query($db, $add_column_query)) {
            error_log("컬럼 $column_name 추가 오류: " . mysqli_error($db));
            safe_json_response(false, null, "데이터베이스 설정 오류가 발생했습니다. 관리자에게 문의하세요.");
        } else {
            error_log("컬럼 $column_name 성공적으로 추가됨");
        }
    }
}

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

// ✅ 봉투: quantity = MY_amount, unit = '매'
$quantity = floatval($MY_amount);
$unit = '매';

// 장바구니에 추가 - 모든 필드 포함 (uploaded_files, ThingCate, ImgFolder 추가)
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, POtype, MY_amount, quantity, unit, ordertype, st_price, st_price_vat, envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price, envelope_additional_options_total, MY_type_name, Section_name, POtype_name, work_memo, upload_method, uploaded_files, ThingCate, ImgFolder)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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

    error_log("bind_param 전 - 파라미터 수: 16개");
    error_log("=== 봉투 옵션명 디버그 ===");
    error_log("MY_type_name: " . $MY_type_name);
    error_log("Section_name: " . $Section_name);
    error_log("POtype_name: " . $POtype_name);

    $bind_result = mysqli_stmt_bind_param($stmt, "ssssssdssiiiiisssssssss",
        $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $quantity, $unit, $ordertype,
        $price, $vat_price, $tape_enabled, $tape_quantity,
        $envelope_tape_price_int, $envelope_additional_options_total_int,
        $MY_type_name, $Section_name, $POtype_name,
        $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder);

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
