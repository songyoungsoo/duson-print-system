<?php
// 오류 디버깅 활성화
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 세션 ID 확인
$session_id = session_id();
if (empty($session_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "세션 오류"]);
    exit;
}

// 공통 데이터베이스 연결 사용
include "../../db.php";
if (!$db) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "데이터베이스 연결 실패"]);
    exit;
}

mysqli_set_charset($db, "utf8mb4");

// JSON 응답 함수
function success_response($data = null, $message = "성공") {
    header('Content-Type: application/json');
    echo json_encode(["success" => true, "message" => $message, "data" => $data]);
    exit;
}

function error_response($message) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

// POST 데이터 받기
$MY_type = $_POST['MY_type'] ?? '';        // 구분
$Section = $_POST['Section'] ?? '';       // 재질
$PN_type = $_POST['PN_type'] ?? '';       // 규격
$MY_amount = $_POST['MY_amount'] ?? '';    // 수량
$POtype = $_POST['POtype'] ?? '';          // 인쇄면
$ordertype = $_POST['ordertype'] ?? '';    // 디자인편집
$price = intval($_POST['price'] ?? 0);
$vat_price = intval($_POST['vat_price'] ?? 0);

// 디버깅을 위한 로그 (나중에 제거 가능)
error_log("포스터 장바구니 추가 데이터: MY_type=$MY_type, Section=$Section, PN_type=$PN_type, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype, price=$price, vat_price=$vat_price");

// 입력값 검증 (PN_type는 선택적 파라미터)
if (empty($MY_type) || empty($Section) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    error_response('필수 입력값이 누락되었습니다.');
}

// 장바구니 테이블이 없으면 생성
$create_table_query = "CREATE TABLE IF NOT EXISTS shop_temp (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_type VARCHAR(50) NOT NULL DEFAULT 'poster',
    MY_type VARCHAR(50),
    TreeSelect VARCHAR(50),
    Section VARCHAR(50),
    PN_type VARCHAR(50),
    MY_amount VARCHAR(50),
    POtype VARCHAR(10),
    ordertype VARCHAR(50),
    st_price INT DEFAULT 0,
    st_price_vat INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_session (session_id),
    KEY idx_product (product_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($db, $create_table_query)) {
    error_response('테이블 생성 오류: ' . mysqli_error($db));
}

// 필수 컬럼들이 없으면 추가 (기존 테이블 호환성)
$required_columns = [
    'product_type' => "VARCHAR(50) NOT NULL DEFAULT 'poster'",
    'Section' => "VARCHAR(50)",
    'PN_type' => "VARCHAR(50)"
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($db, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        mysqli_query($db, $add_column_query); // 오류 무시 (이미 있을 수 있음)
    }
}

// 장바구니에 추가 (PN_type 포함)
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, PN_type, MY_amount, POtype, ordertype, st_price, st_price_vat) 
                VALUES (?, 'poster', ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssii", $session_id, $MY_type, $Section, $PN_type, $MY_amount, $POtype, $ordertype, $price, $vat_price);
    
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($db);
        success_response(['id' => $insert_id], '장바구니에 추가되었습니다.');
    } else {
        error_response('장바구니 추가 실패: ' . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
} else {
    error_response('데이터베이스 준비 실패: ' . mysqli_error($db));
}

mysqli_close($db);
?>