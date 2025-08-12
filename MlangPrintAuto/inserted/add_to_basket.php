<?php
session_start();
$session_id = session_id();

// 데이터베이스 연결
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$connect = mysqli_connect($host, $user, $password, $dataname);
if (!$connect) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결에 실패했습니다.']);
    exit;
}

mysqli_set_charset($connect, "utf8");

// POST 데이터 받기
$MY_type = $_POST['MY_type'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$POtype = $_POST['POtype'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = intval($_POST['price'] ?? 0);
$vat_price = intval($_POST['vat_price'] ?? 0);

// 디버깅을 위한 로그 (나중에 제거 가능)
error_log("장바구니 추가 데이터: MY_type=$MY_type, PN_type=$PN_type, MY_Fsd=$MY_Fsd, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype, price=$price, vat_price=$vat_price");

// 입력값 검증
if (empty($MY_type) || empty($PN_type) || empty($MY_Fsd) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    echo json_encode(['success' => false, 'message' => '필수 입력값이 누락되었습니다.']);
    exit;
}

// 장바구니 테이블이 없으면 생성
$create_table_query = "CREATE TABLE IF NOT EXISTS shop_temp (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    MY_type VARCHAR(50),
    PN_type VARCHAR(50),
    MY_Fsd VARCHAR(50),
    MY_amount VARCHAR(50),
    POtype VARCHAR(10),
    ordertype VARCHAR(50),
    st_price INT DEFAULT 0,
    st_price_vat INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($connect, $create_table_query)) {
    echo json_encode(['success' => false, 'message' => '테이블 생성 오류: ' . mysqli_error($connect)]);
    exit;
}

// 전단지용 필드들이 없으면 추가
$required_columns = [
    'product_type' => "VARCHAR(50) NOT NULL DEFAULT 'leaflet'",
    'MY_type' => "VARCHAR(50)",
    'PN_type' => "VARCHAR(50)", 
    'MY_Fsd' => "VARCHAR(50)",
    'MY_amount' => "VARCHAR(50)",
    'POtype' => "VARCHAR(10)",
    'ordertype' => "VARCHAR(50)"
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($connect, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        if (!mysqli_query($connect, $add_column_query)) {
            echo json_encode(['success' => false, 'message' => "컬럼 $column_name 추가 오류: " . mysqli_error($connect)]);
            exit;
        }
    }
}

// 장바구니에 추가
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, POtype, ordertype, st_price, st_price_vat) 
                VALUES (?, 'leaflet', ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($connect, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssii", $session_id, $MY_type, $PN_type, $MY_Fsd, $MY_amount, $POtype, $ordertype, $price, $vat_price);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => '장바구니에 추가되었습니다.']);
    } else {
        $error_msg = mysqli_stmt_error($stmt);
        echo json_encode(['success' => false, 'message' => '장바구니 추가 중 오류가 발생했습니다: ' . $error_msg]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    $error_msg = mysqli_error($connect);
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류가 발생했습니다: ' . $error_msg]);
}

mysqli_close($connect);
?>