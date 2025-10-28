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
$Section = $_POST['Section'] ?? '';       // 재질
$PN_type = $_POST['PN_type'] ?? '';       // 규격
$MY_amount = $_POST['MY_amount'] ?? '';    // 수량
$POtype = $_POST['POtype'] ?? '';          // 인쇄면
$ordertype = $_POST['ordertype'] ?? '';    // 디자인편집
$price = intval($_POST['price'] ?? 0);
$vat_price = intval($_POST['vat_price'] ?? 0);

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

// 디버깅을 위한 로그 (나중에 제거 가능)
error_log("포스터 장바구니 전체 POST 데이터: " . print_r($_POST, true));
error_log("포스터 장바구니 추가 데이터: MY_type=$MY_type, Section=$Section, PN_type=$PN_type, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype, price=$price, vat_price=$vat_price");
error_log("포스터 추가 옵션 (JSON): $additional_options_json, total=$additional_options_total");

// 디버그 로그 파일에 기록 (오류 억제)
$debug_log_file = __DIR__ . '/debug_cart.log';
$log_time = date('Y-m-d H:i:s');
$log_message = "\n[$log_time] 포스터 장바구니 추가:\n";
$log_message .= "기본 정보: MY_type=$MY_type, Section=$Section, PN_type=$PN_type, MY_amount=$MY_amount\n";
$log_message .= "가격 정보: price=$price, vat_price=$vat_price\n";
$log_message .= "추가 옵션 (JSON): $additional_options_json\n";
$log_message .= "추가 옵션 총액: $additional_options_total\n";
$log_message .= "POST 데이터: " . print_r($_POST, true) . "\n";
$log_message .= str_repeat('-', 80) . "\n";
@file_put_contents($debug_log_file, $log_message, FILE_APPEND); // @ 오류 억제

// 입력값 검증 (PN_type는 선택적 파라미터)
if (empty($MY_type) || empty($Section) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    $missing_fields = [];
    if (empty($MY_type)) $missing_fields[] = 'MY_type';
    if (empty($Section)) $missing_fields[] = 'Section';
    if (empty($MY_amount)) $missing_fields[] = 'MY_amount';
    if (empty($POtype)) $missing_fields[] = 'POtype';
    if (empty($ordertype)) $missing_fields[] = 'ordertype';

    error_response('필수 입력값이 누락되었습니다: ' . implode(', ', $missing_fields));
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
    'Section' => "VARCHAR(50)",
    'MY_amount' => "VARCHAR(50)",
    'POtype' => "VARCHAR(10)",
    'ordertype' => "VARCHAR(50)",
    // 추가 옵션 필드들 (JSON 방식)
    'additional_options' => "TEXT",
    'additional_options_total' => "INT DEFAULT 0"
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

// 장바구니에 추가 (추가 옵션 JSON 방식)
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, PN_type, MY_amount, POtype, ordertype, st_price, st_price_vat,
                additional_options, additional_options_total)
                VALUES (?, 'poster', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssiisi",
        $session_id, $MY_type, $Section, $PN_type, $MY_amount, $POtype, $ordertype, $price, $vat_price,
        $additional_options_json, $additional_options_total);
    
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