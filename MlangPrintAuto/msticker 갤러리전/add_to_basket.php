<?php
// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터 받기
$action = $_POST['action'] ?? '';
$MY_type = $_POST['MY_type'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';     // Section 역할
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'msticker';

// 입력값 검증
if ($action !== 'add_to_basket') {
    error_response('잘못된 액션입니다.');
}

if (empty($MY_type) || empty($PN_type) || empty($MY_amount) || empty($ordertype)) {
    error_response('필수 정보가 누락되었습니다.');
}

// 세션 ID 생성
$session_id = session_id();
if (empty($session_id)) {
    session_start();
    $session_id = session_id();
}

// 필요한 컬럼이 있는지 확인하고 없으면 추가
$required_columns = [
    'session_id' => 'VARCHAR(255)',
    'product_type' => 'VARCHAR(50)',
    'MY_type' => 'VARCHAR(50)',
    'TreeSelect' => 'VARCHAR(50)',
    'PN_type' => 'VARCHAR(50)',
    'MY_amount' => 'VARCHAR(50)',
    'POtype' => 'VARCHAR(50)',
    'ordertype' => 'VARCHAR(50)',
    'st_price' => 'INT(11)',
    'st_price_vat' => 'INT(11)'
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($db, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        if (!mysqli_query($db, $add_column_query)) {
            error_response("컬럼 $column_name 추가 오류: " . mysqli_error($db));
        }
    }
}

// 장바구니에 추가 (msticker는 TreeSelect가 없으므로 빈 문자열로 설정)
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, TreeSelect, PN_type, MY_amount, POtype, ordertype, st_price, st_price_vat) 
                VALUES (?, 'msticker', ?, '', ?, ?, '', ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssii", $session_id, $MY_type, $PN_type, $MY_amount, $ordertype, $price, $vat_price);
    
    if (mysqli_stmt_execute($stmt)) {
        success_response(null, '장바구니에 추가되었습니다.');
    } else {
        error_response('장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
} else {
    error_response('데이터베이스 오류가 발생했습니다: ' . mysqli_error($db));
}

mysqli_close($db);
?>