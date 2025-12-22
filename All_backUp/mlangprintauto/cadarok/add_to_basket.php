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
$Section = $_POST['Section'] ?? ''; // 카다록 용지 재질
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'cadarok';

// 추가 정보
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

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

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그
error_log("카다록 장바구니 추가 - 받은 데이터: " . print_r($_POST, true));

// 디버그 로그 파일에 기록
$debug_log_file = __DIR__ . '/debug_cart.log';
$log_time = date('Y-m-d H:i:s');
$log_message = "\n[$log_time] 카다록 장바구니 추가:\n";
$log_message .= "기본 정보: MY_type=$MY_type, Section=$Section, POtype=$POtype, MY_amount=$MY_amount\n";
$log_message .= "가격 정보: price=$price, vat_price=$vat_price\n";
$log_message .= "추가 옵션 (JSON): $additional_options_json\n";
$log_message .= "추가 옵션 총액: $additional_options_total\n";
$log_message .= "POST 데이터: " . print_r($_POST, true) . "\n";
$log_message .= str_repeat('-', 80) . "\n";
@file_put_contents($debug_log_file, $log_message, FILE_APPEND); // @ 오류 억제

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
$upload_result = StandardUploadHandler::processUpload('cadarok', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

error_log("카다록 업로드 결과: " . count($uploaded_files) . " 개 파일, 경로: $img_folder");

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
    'work_memo' => 'TEXT',
    'upload_method' => 'VARCHAR(50)',
    'uploaded_files' => 'TEXT',
    'ThingCate' => 'VARCHAR(255)',
    'ImgFolder' => 'VARCHAR(255)',
    // 추가 옵션 컬럼들 (JSON 방식)
    'additional_options' => 'TEXT',
    'additional_options_total' => 'INT DEFAULT 0'
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

// 장바구니에 추가 (추가 옵션 JSON 방식)
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype, st_price, st_price_vat,
                additional_options, additional_options_total, work_memo, upload_method, uploaded_files, ThingCate, ImgFolder)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssiisisssss",
        $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype, $price, $vat_price,
        $additional_options_json, $additional_options_total,
        $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder);
    
    if (mysqli_stmt_execute($stmt)) {
        $basket_id = mysqli_insert_id($db);
        
        // 추가 정보는 별도 업데이트로 처리
        mysqli_stmt_close($stmt);
        
        // 추가 정보 업데이트 (레거시 경로 정보 포함)
        $files_json = json_encode($uploaded_files);
        
        $update_query = "UPDATE shop_temp SET work_memo = ?, upload_method = ?, uploaded_files = ?, ThingCate = ?, ImgFolder = ? WHERE no = ?";
        $update_stmt = mysqli_prepare($db, $update_query);
        
        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "sssssi", $work_memo, $upload_method, $files_json, $thing_cate, $img_folder, $basket_id);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
        }
        
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

?>
