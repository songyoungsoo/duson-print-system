<?php
session_start();
header('Content-Type: application/json');

// 디버깅을 위한 에러 로깅
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../lib/func.php";
$connect = dbconn();

try {
    // 데이터베이스 연결 확인
    if (!$connect) {
        throw new Exception('데이터베이스 연결에 실패했습니다: ' . mysqli_connect_error());
    }
    
    // POST 데이터 확인
    if (empty($_POST)) {
        throw new Exception('POST 데이터가 없습니다.');
    }
    
    if (!isset($_POST['action']) || $_POST['action'] !== 'add_to_basket_cadarok') {
        throw new Exception('잘못된 요청입니다. action: ' . ($_POST['action'] ?? 'null'));
    }
    
    $session_id = session_id();
    
    // 카다록 전용 데이터 받기
    $product_type = $_POST['product_type'] ?? 'cadarok';
    $type = $_POST['type'] ?? '';
    $size = $_POST['size'] ?? '';
    $paper_type = $_POST['paper_type'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $order_type = $_POST['order_type'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $price_vat = (float)($_POST['price_vat'] ?? 0);
    
    // 추가 정보
    $MY_type = $_POST['MY_type'] ?? '';
    $MY_Fsd = $_POST['MY_Fsd'] ?? '';
    $PN_type = $_POST['PN_type'] ?? '';
    $MY_amount = $_POST['MY_amount'] ?? '';
    $ordertype = $_POST['ordertype'] ?? '';
    
    // 입력값 검증
    if (empty($type) || empty($size) || empty($paper_type) || empty($amount)) {
        throw new Exception('필수 옵션을 모두 선택해주세요.');
    }
    
    if ($price <= 0) {
        throw new Exception('가격 정보가 올바르지 않습니다.');
    }
    
    // 장바구니 테이블 구조에 맞게 데이터 준비
    // shop_temp 테이블 구조: session_id, parent, jong, garo, sero, mesu, domusong, uhyung, st_price, st_price_vat, regdate
    
    // 카다록의 경우 기존 스티커 테이블 구조와 다르므로 별도 처리
    // jong 필드에 카다록 정보를 JSON 형태로 저장
    $cadarok_info = json_encode([
        'product_type' => $product_type,
        'type' => $type,
        'size' => $size,
        'paper_type' => $paper_type,
        'amount' => $amount,
        'order_type' => $order_type,
        'MY_type' => $MY_type,
        'MY_Fsd' => $MY_Fsd,
        'PN_type' => $PN_type,
        'MY_amount' => $MY_amount,
        'ordertype' => $ordertype
    ], JSON_UNESCAPED_UNICODE);
    
    $regdate = time();
    
    // SQL 인젝션 방지를 위한 이스케이프 처리
    $session_id = mysqli_real_escape_string($connect, $session_id);
    $cadarok_info = mysqli_real_escape_string($connect, $cadarok_info);
    
    // 카다록 전용 장바구니 테이블이 있는지 확인하고 없으면 생성
    $table_check = mysqli_query($connect, "SHOW TABLES LIKE 'shop_temp_cadarok'");
    if (mysqli_num_rows($table_check) == 0) {
        $create_table = "CREATE TABLE shop_temp_cadarok (
            no INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL,
            product_type VARCHAR(50) DEFAULT 'cadarok',
            type_name VARCHAR(100),
            size_name VARCHAR(100),
            paper_type VARCHAR(100),
            amount VARCHAR(50),
            order_type VARCHAR(100),
            cadarok_data TEXT,
            st_price DECIMAL(10,2),
            st_price_vat DECIMAL(10,2),
            regdate INT,
            INDEX(session_id)
        )";
        
        if (!mysqli_query($connect, $create_table)) {
            throw new Exception('장바구니 테이블 생성 중 오류가 발생했습니다.');
        }
    }
    
    // 장바구니에 추가
    $query = "INSERT INTO shop_temp_cadarok (
        session_id, product_type, type_name, size_name, paper_type, amount, order_type, 
        cadarok_data, st_price, st_price_vat, regdate
    ) VALUES (
        '$session_id', '$product_type', '" . mysqli_real_escape_string($connect, $type) . "', 
        '" . mysqli_real_escape_string($connect, $size) . "', '" . mysqli_real_escape_string($connect, $paper_type) . "', 
        '" . mysqli_real_escape_string($connect, $amount) . "', '" . mysqli_real_escape_string($connect, $order_type) . "', 
        '$cadarok_info', '$price', '$price_vat', '$regdate'
    )";
    
    if (mysqli_query($connect, $query)) {
        echo json_encode([
            'success' => true,
            'message' => '장바구니에 추가되었습니다.',
            'data' => [
                'type' => $type,
                'size' => $size,
                'paper_type' => $paper_type,
                'amount' => $amount,
                'order_type' => $order_type,
                'price' => number_format($price),
                'price_vat' => number_format($price_vat)
            ]
        ]);
    } else {
        throw new Exception('데이터베이스 저장 중 오류가 발생했습니다: ' . mysqli_error($connect));
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'post_data' => $_POST,
            'session_id' => session_id(),
            'connect_status' => $connect ? 'connected' : 'not connected',
            'error_line' => $e->getLine(),
            'error_file' => $e->getFile()
        ]
    ]);
}

if ($connect) {
    mysqli_close($connect);
}
?>