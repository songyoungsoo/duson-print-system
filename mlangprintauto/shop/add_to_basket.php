<?php
require_once __DIR__ . '/../../includes/ensure_shop_temp_columns.php';

session_start();
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결에 실패했습니다.']);
    exit;
}

mysqli_set_charset($connect, "utf8");

ensure_shop_temp_columns($connect);

// POST 데이터 받기
$product_type = $_POST['product_type'] ?? 'sticker';

// 스티커 전용 필드
$jong = $_POST['jong'] ?? '';
$garo = $_POST['garo'] ?? '';
$sero = $_POST['sero'] ?? '';
$mesu = $_POST['mesu'] ?? '';
$uhyung = intval($_POST['uhyung'] ?? 0);
$domusong = $_POST['domusong'] ?? '';

// 공통 필드 (다른 상품용)
$MY_type = $_POST['MY_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$MY_comment = $_POST['MY_comment'] ?? '';

// 가격 정보
$st_price = intval($_POST['st_price'] ?? 0);
$st_price_vat = intval($_POST['st_price_vat'] ?? 0);

// 상품별 입력값 검증
if ($product_type === 'sticker') {
    if (empty($jong) || empty($garo) || empty($sero) || empty($mesu)) {
        echo json_encode(['success' => false, 'message' => '필수 입력값이 누락되었습니다.']);
        exit;
    }
} else {
    if (empty($MY_type) || empty($st_price)) {
        echo json_encode(['success' => false, 'message' => '필수 입력값이 누락되었습니다.']);
        exit;
    }
}

// 장바구니 테이블이 없으면 생성
$create_table_query = "CREATE TABLE IF NOT EXISTS shop_temp (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_type VARCHAR(50) NOT NULL DEFAULT 'sticker',
    jong VARCHAR(200),
    garo VARCHAR(50),
    sero VARCHAR(50),
    mesu VARCHAR(50),
    uhyung INT(1) DEFAULT 0,
    domusong VARCHAR(200),
    st_price INT DEFAULT 0,
    st_price_vat INT DEFAULT 0,
    MY_comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_product_type (product_type)
)";

if (!mysqli_query($connect, $create_table_query)) {
    echo json_encode(['success' => false, 'message' => '테이블 생성 오류: ' . mysqli_error($connect)]);
    exit;
}

// 장바구니에 추가 (상품 타입별로 다른 처리)
if ($product_type === 'sticker') {
    $insert_query = "INSERT INTO shop_temp (session_id, product_type, jong, garo, sero, mesu, uhyung, domusong, st_price, st_price_vat) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $insert_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssissii", 
            $session_id, $product_type, $jong, $garo, $sero, $mesu, $uhyung, $domusong, $st_price, $st_price_vat);
    }
} else {
    // 카다록 등 다른 상품
    $insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, ordertype, MY_comment, st_price, st_price_vat) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $insert_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssssii", 
            $session_id, $product_type, $MY_type, $MY_Fsd, $PN_type, $MY_amount, $ordertype, $MY_comment, $st_price, $st_price_vat);
    }
}

if ($stmt) {
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => '장바구니에 추가되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect)]);
}

mysqli_close($connect);
?>