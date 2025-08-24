<?php
// 공통 응답 함수 포함 (출력 버퍼링 및 에러 처리 포함)
require_once __DIR__ . '/../../includes/safe_json_response.php';

// JSON 헤더 우선 설정
header('Content-Type: application/json; charset=utf-8');

// 세션 시작
session_start();
$session_id = session_id();

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";
$connect = $db;

// 데이터베이스 연결 체크
check_db_connection($connect);
mysqli_set_charset($connect, "utf8");

// POST 데이터 받기
$action = $_POST['action'] ?? '';
$product_type = $_POST['product_type'] ?? 'sticker';

// 스티커 전용 필드
$jong = $_POST['jong'] ?? '';
$garo = $_POST['garo'] ?? '';
$sero = $_POST['sero'] ?? '';
$mesu = $_POST['mesu'] ?? '';
$uhyung = intval($_POST['uhyung'] ?? 0);
$domusong = $_POST['domusong'] ?? '';

// 가격 정보
$st_price = intval($_POST['st_price'] ?? 0);
$st_price_vat = intval($_POST['st_price_vat'] ?? 0);

// 추가 정보 (명함 시스템과 동일)
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 공통 필드 (다른 상품용)
$MY_type = $_POST['MY_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$MY_comment = $_POST['MY_comment'] ?? '';

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그
error_log("스티커 장바구니 추가 - 받은 데이터: " . print_r($_POST, true));

// 상품별 입력값 검증
if ($product_type === 'sticker') {
    if (empty($jong) || empty($garo) || empty($sero) || empty($mesu)) {
        $missing_fields = [];
        if (empty($jong)) $missing_fields[] = 'jong';
        if (empty($garo)) $missing_fields[] = 'garo';
        if (empty($sero)) $missing_fields[] = 'sero';
        if (empty($mesu)) $missing_fields[] = 'mesu';
        
        safe_json_response(false, null, '필수 정보가 누락되었습니다: ' . implode(', ', $missing_fields));
    }
} else {
    if (empty($MY_type) || empty($st_price)) {
        safe_json_response(false, null, '필수 입력값이 누락되었습니다.');
    }
}

// 세션 ID 가져오기
$session_id = session_id();

// 디버그 로깅 강화
error_log("=== Sticker Cart Debug Info ===");
error_log("Session ID: " . $session_id);
error_log("Action: " . $action);
error_log("Jong: " . $jong);
error_log("Size: " . $garo . "x" . $sero);
error_log("Quantity: " . $mesu);
error_log("Price: " . $st_price);
error_log("Work memo length: " . strlen($work_memo));

// 파일 업로드 처리
$uploaded_files = [];
$upload_directory = "../../uploads/" . date("Y/m/d") . "/" . $_SERVER['REMOTE_ADDR'] . "/";

if (!empty($_FILES['uploaded_files'])) {
    // 디렉토리 생성
    if (!file_exists($upload_directory)) {
        mkdir($upload_directory, 0755, true);
    }
    
    foreach ($_FILES['uploaded_files']['name'] as $key => $filename) {
        if ($_FILES['uploaded_files']['error'][$key] == UPLOAD_ERR_OK) {
            $temp_file = $_FILES['uploaded_files']['tmp_name'][$key];
            $target_filename = time() . "_" . $filename;
            $target_path = $upload_directory . $target_filename;
            
            if (move_uploaded_file($temp_file, $target_path)) {
                $uploaded_files[] = [
                    'original_name' => $filename,
                    'saved_name' => $target_filename,
                    'path' => $target_path,
                    'size' => $_FILES['uploaded_files']['size'][$key]
                ];
            }
        }
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

// 필요한 컬럼이 있는지 확인하고 없으면 추가
$required_columns = [
    'session_id' => 'VARCHAR(255) NOT NULL',
    'product_type' => "VARCHAR(50) NOT NULL DEFAULT 'sticker'",
    'jong' => 'VARCHAR(200)',
    'garo' => 'VARCHAR(50)',
    'sero' => 'VARCHAR(50)', 
    'mesu' => 'VARCHAR(50)',
    'uhyung' => 'INT(1) DEFAULT 0',
    'domusong' => 'VARCHAR(200)',
    'MY_type' => 'VARCHAR(50)',
    'MY_Fsd' => 'VARCHAR(50)',
    'PN_type' => 'VARCHAR(50)',
    'MY_amount' => 'VARCHAR(50)',
    'ordertype' => 'VARCHAR(50)',
    'MY_comment' => 'TEXT',
    'st_price' => 'INT(11) DEFAULT 0',
    'st_price_vat' => 'INT(11) DEFAULT 0',
    'work_memo' => 'TEXT',
    'upload_method' => 'VARCHAR(50)',
    'uploaded_files' => 'TEXT',
    'ThingCate' => 'VARCHAR(255)',
    'ImgFolder' => 'VARCHAR(255)'
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($connect, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        if (!mysqli_query($connect, $add_column_query)) {
            safe_json_response(false, null, "컬럼 $column_name 추가 오류: " . mysqli_error($connect));
        }
    }
}

// 장바구니에 추가 - 기본 필드만으로 단순화 (명함 시스템과 동일)
if ($product_type === 'sticker') {
    $insert_query = "INSERT INTO shop_temp (session_id, product_type, jong, garo, sero, mesu, uhyung, domusong, st_price, st_price_vat) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $insert_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssissii", 
            $session_id, $product_type, $jong, $garo, $sero, $mesu, $uhyung, $domusong, $st_price, $st_price_vat);
        
        if (mysqli_stmt_execute($stmt)) {
            $basket_id = mysqli_insert_id($connect);
            
            // 추가 정보는 별도 업데이트로 처리
            mysqli_stmt_close($stmt);
            
            // 추가 정보 업데이트
            $files_json = json_encode($uploaded_files);
            $thing_cate = 'sticker_' . $jong;
            $img_folder = $upload_directory;
            
            $update_query = "UPDATE shop_temp SET work_memo = ?, upload_method = ?, uploaded_files = ?, ThingCate = ?, ImgFolder = ? WHERE no = ?";
            $update_stmt = mysqli_prepare($connect, $update_query);
            
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "sssssi", $work_memo, $upload_method, $files_json, $thing_cate, $img_folder, $basket_id);
                mysqli_stmt_execute($update_stmt); // 이 부분은 실패해도 기본 장바구니 저장은 성공
                mysqli_stmt_close($update_stmt);
            }
            
            $response_data = [
                'basket_id' => $basket_id,
                'uploaded_files_count' => count($uploaded_files),
                'upload_directory' => $upload_directory
            ];
            
            safe_json_response(true, $response_data, '장바구니에 추가되었습니다.');
            
        } else {
            mysqli_stmt_close($stmt);
            safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
        }
    } else {
        safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect));
    }
} else {
    // 카다록 등 다른 상품
    $insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, ordertype, MY_comment, st_price, st_price_vat) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $insert_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssssii", 
            $session_id, $product_type, $MY_type, $MY_Fsd, $PN_type, $MY_amount, $ordertype, $MY_comment, $st_price, $st_price_vat);
        
        if (mysqli_stmt_execute($stmt)) {
            safe_json_response(true, null, '장바구니에 추가되었습니다.');
        } else {
            mysqli_stmt_close($stmt);
            safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
    } else {
        safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect));
    }
}

mysqli_close($connect);
?>