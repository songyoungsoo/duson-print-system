<?php
// 공통 함수 포함
include "../../includes/functions.php";

// 세션 및 데이터베이스 설정
$session_id = check_session();
include "../../db.php";
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터 받기
$MY_type = $_POST['MY_type'] ?? '';        // 구분
$TreeSelect = $_POST['TreeSelect'] ?? '';  // 종이종류
$PN_type = $_POST['PN_type'] ?? '';        // 종이규격
$MY_amount = $_POST['MY_amount'] ?? '';    // 수량
$POtype = $_POST['POtype'] ?? '';          // 인쇄면
$ordertype = $_POST['ordertype'] ?? '';    // 디자인편집
$price = intval($_POST['price'] ?? 0);
$vat_price = intval($_POST['vat_price'] ?? 0);

// 디버깅을 위한 로그 (나중에 제거 가능)
error_log("포스터 장바구니 추가 데이터: MY_type=$MY_type, TreeSelect=$TreeSelect, PN_type=$PN_type, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype, price=$price, vat_price=$vat_price");

// 입력값 검증
if (empty($MY_type) || empty($TreeSelect) || empty($PN_type) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    error_response('필수 입력값이 누락되었습니다.');
}

// 장바구니 테이블이 없으면 생성
$create_table_query = "CREATE TABLE IF NOT EXISTS shop_temp (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    MY_type VARCHAR(50),
    TreeSelect VARCHAR(50),
    PN_type VARCHAR(50),
    MY_amount VARCHAR(50),
    POtype VARCHAR(10),
    ordertype VARCHAR(50),
    st_price INT DEFAULT 0,
    st_price_vat INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($db, $create_table_query)) {
    error_response('테이블 생성 오류: ' . mysqli_error($db));
}

// 포스터용 필드들이 없으면 추가
$required_columns = [
    'product_type' => "VARCHAR(50) NOT NULL DEFAULT 'poster'",
    'MY_type' => "VARCHAR(50)",
    'TreeSelect' => "VARCHAR(50)",
    'PN_type' => "VARCHAR(50)", 
    'MY_amount' => "VARCHAR(50)",
    'POtype' => "VARCHAR(10)",
    'ordertype' => "VARCHAR(50)"
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

// 장바구니에 추가
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, TreeSelect, PN_type, MY_amount, POtype, ordertype, st_price, st_price_vat) 
                VALUES (?, 'poster', ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssii", $session_id, $MY_type, $TreeSelect, $PN_type, $MY_amount, $POtype, $ordertype, $price, $vat_price);
    
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