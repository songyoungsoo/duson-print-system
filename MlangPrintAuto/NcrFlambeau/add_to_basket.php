<?php
// 명함 성공 패턴 적용 - 안전한 JSON 응답 처리
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

session_start();
$session_id = session_id();

include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 안전한 JSON 응답 함수 (명함 패턴)
function safe_json_response($success = true, $data = null, $message = '') {
    ob_clean(); // 이전 출력 완전 정리
    
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 데이터 받기
$product_type = $_POST['product_type'] ?? 'ncrflambeau';
$MY_type = $_POST['MY_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$calculated_price = $_POST['calculated_price'] ?? 0;
$calculated_vat_price = $_POST['calculated_vat_price'] ?? 0;

// 필수 필드 검증
if (empty($MY_type) || empty($MY_Fsd) || empty($PN_type) || empty($MY_amount) || empty($ordertype)) {
    safe_json_response(false, null, '모든 옵션을 선택해주세요.');
}

if (empty($calculated_price) || empty($calculated_vat_price)) {
    safe_json_response(false, null, '가격 정보가 없습니다. 다시 계산해주세요.');
}

try {
    // shop_temp 테이블에 필요한 컬럼이 있는지 확인하고 없으면 추가
    $required_columns = [
        'session_id' => 'VARCHAR(255)',
        'product_type' => 'VARCHAR(50)',
        'MY_type' => 'VARCHAR(50)',
        'MY_Fsd' => 'VARCHAR(50)',
        'PN_type' => 'VARCHAR(50)',
        'MY_amount' => 'VARCHAR(50)',
        'ordertype' => 'VARCHAR(50)',
        'st_price' => 'INT(11)',
        'st_price_vat' => 'INT(11)',
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
                error_log("컬럼 추가 실패: $column_name - " . mysqli_error($db));
            }
        }
    }

    // 기본 정보 먼저 삽입 (명함 패턴)
    $insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, ordertype, st_price, st_price_vat) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($db, $insert_query);
    if (!$stmt) {
        throw new Exception('쿼리 준비 실패: ' . mysqli_error($db));
    }
    
    $price = intval($calculated_price);
    $vat_price = intval($calculated_vat_price);
    
    mysqli_stmt_bind_param($stmt, "sssssssii", 
        $session_id, $product_type, $MY_type, $MY_Fsd, $PN_type, $MY_amount, $ordertype, $price, $vat_price);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('기본 정보 삽입 실패: ' . mysqli_stmt_error($stmt));
    }
    
    $basket_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);
    
    // 추가 정보 업데이트 (파일 업로드 관련)
    $work_memo = $_POST['work_memo'] ?? '';
    $upload_method = $_POST['upload_method'] ?? '';
    $uploaded_files = $_POST['uploaded_files'] ?? '';
    
    if (!empty($work_memo) || !empty($upload_method) || !empty($uploaded_files)) {
        $update_query = "UPDATE shop_temp SET work_memo = ?, upload_method = ?, uploaded_files = ? WHERE no = ?";
        $update_stmt = mysqli_prepare($db, $update_query);
        
        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "sssi", $work_memo, $upload_method, $uploaded_files, $basket_id);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
        }
    }
    
    // 성공 로그
    error_log("NcrFlambeau 장바구니 추가 성공: basket_id=$basket_id, session_id=$session_id");
    
    safe_json_response(true, ['basket_id' => $basket_id], '장바구니에 추가되었습니다.');
    
} catch (Exception $e) {
    error_log("NcrFlambeau 장바구니 추가 오류: " . $e->getMessage());
    safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . $e->getMessage());
}

mysqli_close($db);
?>