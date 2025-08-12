<?php
session_start();
header('Content-Type: application/json');

$HomeDir = "../../";
include "../lib/func.php";
$connect = dbconn();

try {
    if ($_POST['action'] !== 'order') {
        throw new Exception('잘못된 요청입니다.');
    }
    
    // 세션에서 주문 데이터 확인
    if (!isset($_SESSION['temp_order'])) {
        throw new Exception('주문 정보가 없습니다. 다시 계산해주세요.');
    }
    
    $order_data = $_SESSION['temp_order'];
    
    // 고객 정보
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $customer_memo = trim($_POST['customer_memo'] ?? '');
    
    // 입력값 검증
    if (empty($customer_name) || empty($customer_phone)) {
        throw new Exception('이름과 연락처는 필수입니다.');
    }
    
    // 주문번호 생성
    $order_number = 'ST' . date('Ymd') . sprintf('%04d', rand(1, 9999));
    
    // 주문 데이터베이스 저장
    $sql = "INSERT INTO sticker_orders (
        order_number, customer_name, customer_phone, customer_email, 
        customer_address, customer_memo, jong, garo, sero, mesu, 
        uhyung, domusong, price, price_vat, order_date, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";
    
    $stmt = mysqli_prepare($connect, $sql);
    if (!$stmt) {
        throw new Exception('데이터베이스 오류가 발생했습니다.');
    }
    
    mysqli_stmt_bind_param($stmt, 'sssssssiiiisii', 
        $order_number, $customer_name, $customer_phone, $customer_email,
        $customer_address, $customer_memo, $order_data['jong'], 
        $order_data['garo'], $order_data['sero'], $order_data['mesu'],
        $order_data['uhyung'], $order_data['domusong'], 
        $order_data['price'], $order_data['price_vat']
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('주문 저장 중 오류가 발생했습니다.');
    }
    
    // 세션 정리
    unset($_SESSION['temp_order']);
    
    // 주문 완료 이메일 발송 (선택사항)
    if (!empty($customer_email)) {
        sendOrderConfirmationEmail($customer_email, $order_number, $order_data);
    }
    
    echo json_encode([
        'success' => true,
        'order_number' => $order_number,
        'message' => '주문이 성공적으로 접수되었습니다.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 주문 확인 이메일 발송 함수 (선택사항)
function sendOrderConfirmationEmail($email, $order_number, $order_data) {
    $subject = "[스티커 주문] 주문 접수 확인 - " . $order_number;
    $message = "
    주문번호: {$order_number}
    재질: {$order_data['jong']}
    크기: {$order_data['garo']}mm x {$order_data['sero']}mm
    수량: {$order_data['mesu']}매
    총 금액: " . number_format($order_data['price_vat']) . "원 (부가세 포함)
    
    빠른 시일 내에 연락드리겠습니다.
    감사합니다.
    ";
    
    $headers = "From: noreply@yourdomain.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($email, $subject, $message, $headers);
}
?>