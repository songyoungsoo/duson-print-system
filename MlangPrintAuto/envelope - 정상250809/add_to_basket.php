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
$PN_type = $_POST['PN_type'] ?? ''; // Section 역할
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'envelope';

// 입력값 검증
if ($action !== 'add_to_basket') {
    error_response('잘못된 액션입니다.');
}

if (empty($MY_type) || empty($PN_type) || empty($POtype) || empty($MY_amount) || empty($ordertype)) {
    error_response('필수 정보가 누락되었습니다.');
}

// 세션 ID 생성
$session_id = session_id();
if (empty($session_id)) {
    session_start();
    $session_id = session_id();
}

// 장바구니에 추가
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, POtype, MY_amount, ordertype, st_price, st_price_vat) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssii", $session_id, $product_type, $MY_type, $PN_type, $POtype, $MY_amount, $ordertype, $price, $vat_price);
    
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
