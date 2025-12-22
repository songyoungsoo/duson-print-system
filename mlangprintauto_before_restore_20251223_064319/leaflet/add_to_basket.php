<?php
// 🔧 출력 버퍼링 시작 (JSON 응답 전에 불필요한 출력 방지)
ob_start();

session_start();
$session_id = session_id();

// 🔧 JSON 응답 헤더 설정 (가장 먼저 설정)
header('Content-Type: application/json; charset=utf-8');

// 🔧 에러를 JSON으로 캡처하기 위한 핸들러
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: [$errno] $errstr in $errfile on line $errline");
});

// 공통 데이터베이스 연결 사용
include "../../db.php";
$connect = $db;
if (!$connect) {
    ob_end_clean(); // 버퍼 내용 제거
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결에 실패했습니다.']);
    exit;
}

// 🔧 FIX: utf8mb4 사용 (이모지 및 확장 유니코드 지원)
mysqli_set_charset($connect, "utf8mb4");

// POST 데이터 받기
$product_type = isset($_POST['product_type']) ? $_POST['product_type'] : 'leaflet'; // 기본값 leaflet
$MY_type = isset($_POST['MY_type']) ? $_POST['MY_type'] : '';
$PN_type = isset($_POST['PN_type']) ? $_POST['PN_type'] : '';
$MY_Fsd = isset($_POST['MY_Fsd']) ? $_POST['MY_Fsd'] : '';
$MY_amount = isset($_POST['MY_amount']) ? $_POST['MY_amount'] : '';
$POtype = isset($_POST['POtype']) ? $_POST['POtype'] : '';
$ordertype = isset($_POST['ordertype']) ? $_POST['ordertype'] : '';
// 가격 데이터: calculated_price와 calculated_vat_price로 전달됨
$price = intval(isset($_POST['calculated_price']) ? $_POST['calculated_price'] : (isset($_POST['price']) ? $_POST['price'] : 0));
$vat_price = intval(isset($_POST['calculated_vat_price']) ? $_POST['calculated_vat_price'] : (isset($_POST['vat_price']) ? $_POST['vat_price'] : 0));

// 파일 업로드 관련 데이터
$work_memo = isset($_POST['work_memo']) ? $_POST['work_memo'] : '';
$upload_method = isset($_POST['upload_method']) ? $_POST['upload_method'] : 'upload';
$uploaded_files_info = isset($_POST['uploaded_files_info']) ? $_POST['uploaded_files_info'] : '';

// 🆕 추가 옵션 데이터 받기 (JSON 방식 - 명함 스타일)
$additional_options = [
    'coating_enabled' => intval($_POST['coating_enabled'] ?? 0),
    'coating_type' => $_POST['coating_type'] ?? '',
    'coating_price' => intval($_POST['coating_price'] ?? 0),
    'folding_enabled' => intval($_POST['folding_enabled'] ?? 0),
    'folding_type' => $_POST['folding_type'] ?? '',
    'folding_price' => intval($_POST['folding_price'] ?? 0),
    'creasing_enabled' => intval($_POST['creasing_enabled'] ?? 0),
    'creasing_lines' => intval($_POST['creasing_lines'] ?? 0),
    'creasing_price' => intval($_POST['creasing_price'] ?? 0)
];
$additional_options_json = json_encode($additional_options, JSON_UNESCAPED_UNICODE);
$additional_options_total = intval($_POST['additional_options_total'] ?? 0);

// 디버깅을 위한 로그 (개선된 버전)
$debug_log_file = __DIR__ . '/debug_cart.log';
$log_message = date('[Y-m-d H:i:s]') . " === 전단지 장바구니 추가 시작 ===\n";
$log_message .= "Session ID: $session_id\n";
$log_message .= "POST 데이터: " . print_r($_POST, true);
$log_message .= "파일 데이터: " . print_r($_FILES, true);
$log_message .= "추가 옵션 (JSON): " . $additional_options_json . "\n";
$log_message .= "추가 옵션 총액: {$additional_options_total}\n";
$log_message .= str_repeat('-', 80) . "\n";

@file_put_contents($debug_log_file, $log_message, FILE_APPEND); // @ 오류 억제
error_log("=== 전단지 장바구니 추가 시작 ===");
error_log("Session ID: $session_id");
error_log("추가 옵션 (JSON): $additional_options_json");
error_log("추가 옵션 총액: $additional_options_total");

// 입력값 검증 (더 자세한 오류 메시지)
$missing_fields = [];
if (empty($MY_type)) $missing_fields[] = 'MY_type (종류)';
if (empty($PN_type)) $missing_fields[] = 'PN_type (규격)';  
if (empty($MY_Fsd)) $missing_fields[] = 'MY_Fsd (용지)';
if (empty($MY_amount)) $missing_fields[] = 'MY_amount (수량)';
if (empty($POtype)) $missing_fields[] = 'POtype (인쇄면)';
if (empty($ordertype)) $missing_fields[] = 'ordertype (주문타입)';

if (!empty($missing_fields)) {
    $error_msg = '필수 입력값이 누락되었습니다: ' . implode(', ', $missing_fields);
    error_log("입력값 검증 실패: $error_msg");
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $error_msg]);
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
    $error_msg = mysqli_error($connect);
    error_log("테이블 생성 오류: $error_msg");
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => '테이블 생성 오류: ' . $error_msg]);
    exit;
}
error_log("shop_temp 테이블 확인/생성 완료");

// 전단지용 필드들이 없으면 추가 (파일 업로드 필드 + 추가 옵션 필드 포함)
$required_columns = [
    'product_type' => "VARCHAR(50) NOT NULL DEFAULT 'leaflet'",
    'MY_type' => "VARCHAR(50)",
    'PN_type' => "VARCHAR(50)",
    'MY_Fsd' => "VARCHAR(50)",
    'MY_amount' => "VARCHAR(50)",
    'POtype' => "VARCHAR(10)",
    'ordertype' => "VARCHAR(50)",
    'work_memo' => "TEXT",
    'upload_method' => "VARCHAR(20) DEFAULT 'upload'",
    'uploaded_files_info' => "TEXT",
    'upload_folder' => "VARCHAR(255)",
    // 🆕 추가 옵션 컬럼들 (JSON 방식 - 명함 스타일)
    'additional_options' => "TEXT",
    'additional_options_total' => "INT DEFAULT 0"
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($connect, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        error_log("컬럼 추가 쿼리: $add_column_query");
        if (!mysqli_query($connect, $add_column_query)) {
            $error_msg = mysqli_error($connect);
            error_log("컬럼 $column_name 추가 오류: $error_msg");
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => "컬럼 $column_name 추가 오류: " . $error_msg]);
            exit;
        }
        error_log("컬럼 $column_name 추가 성공");
    }
}
error_log("필요한 컬럼들 확인/추가 완료");

// 파일 업로드 처리
$upload_folder = '';
$upload_count = 0;

if (!empty($_FILES['uploaded_files']['name'][0])) {
    // ✅ 통일된 업로드 경로
    $base_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload/';
    $temp_folder_name = 'temp_' . $session_id . '_' . time() . '/';
    
    $upload_folder = $base_upload_dir . $temp_folder_name;
    
    error_log("파일 업로드 시작: " . count($_FILES['uploaded_files']['name']) . "개 파일");
    error_log("업로드 폴더 경로: $upload_folder");
    
    // 폴더 생성 (재귀적으로 한번에)
    if (!file_exists($upload_folder)) {
        if (!mkdir($upload_folder, 0755, true)) {
            $error_msg = "업로드 폴더 생성 실패: $upload_folder";
            error_log($error_msg);
            error_log("현재 디렉토리: " . getcwd());
            error_log("베이스 경로: " . $base_upload_dir);
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => $error_msg]);
            exit;
        }
        error_log("업로드 폴더 생성 성공: $upload_folder");
    }
    
    // 파일 업로드 처리
    error_log("파일 업로드 배열 정보: " . print_r($_FILES['uploaded_files'], true));
    
    if (isset($_FILES['uploaded_files']['name']) && is_array($_FILES['uploaded_files']['name'])) {
        for ($i = 0; $i < count($_FILES['uploaded_files']['name']); $i++) {
            error_log("파일 $i 처리 중: " . $_FILES['uploaded_files']['name'][$i]);
            
            if ($_FILES['uploaded_files']['error'][$i] === UPLOAD_ERR_OK) {
                $temp_name = $_FILES['uploaded_files']['tmp_name'][$i];
                $original_name = $_FILES['uploaded_files']['name'][$i];
                $file_size = $_FILES['uploaded_files']['size'][$i];
                
                error_log("파일 상세: name=$original_name, size=$file_size, temp=$temp_name");
                
                // 파일명 안전하게 처리
                $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
                $target_file = $upload_folder . $safe_filename;
                
                // 파일 크기 체크 (15MB)
                if ($file_size > 15 * 1024 * 1024) {
                    error_log("파일 크기 초과: $file_size bytes");
                    continue;
                }
                
                // 파일 이동
                if (move_uploaded_file($temp_name, $target_file)) {
                    $upload_count++;
                    error_log("파일 업로드 성공: $target_file");
                } else {
                    error_log("파일 이동 실패: $temp_name -> $target_file");
                }
            } else {
                error_log("파일 업로드 오류: " . $_FILES['uploaded_files']['error'][$i]);
            }
        }
    } else {
        error_log("uploaded_files 배열이 없거나 잘못된 형식입니다.");
    }
    
    error_log("총 업로드된 파일 수: $upload_count");
}

// 장바구니에 추가 (파일 업로드 정보 및 추가 옵션 포함)
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, POtype, ordertype, st_price, st_price_vat, work_memo, upload_method, uploaded_files_info, upload_folder,
                additional_options, additional_options_total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

error_log("INSERT 쿼리 (JSON 방식 추가옵션 포함): $insert_query");
error_log("바인드 파라미터: session_id=$session_id, additional_options_total=$additional_options_total");
error_log("추가 옵션 (JSON): $additional_options_json");

$stmt = mysqli_prepare($connect, $insert_query);
if ($stmt) {
    // 총 16개 파라미터: s=string, i=integer, d=decimal
    // decimal로 변환
    $price_decimal = (float)$price;
    $vat_price_decimal = (float)$vat_price;
    // $product_type은 이미 위에서 정의됨 (POST에서 받은 값 또는 기본값 'leaflet')

    // 타입 문자열: s=string, i=integer, d=decimal
    // 총 16개: session_id(s), product_type(s), MY_type(s), PN_type(s), MY_Fsd(s), MY_amount(s), POtype(s), ordertype(s),
    //          price(d), vat_price(d), work_memo(s), upload_method(s), uploaded_files_info(s), upload_folder(s),
    //          additional_options(s), additional_options_total(i)
    mysqli_stmt_bind_param($stmt, "ssssssssddsssssi",
        $session_id, $product_type, $MY_type, $PN_type, $MY_Fsd, $MY_amount, $POtype, $ordertype,
        $price_decimal, $vat_price_decimal, $work_memo, $upload_method, $uploaded_files_info, $upload_folder,
        $additional_options_json, $additional_options_total);
    
    if (mysqli_stmt_execute($stmt)) {
        $inserted_id = mysqli_insert_id($connect);
        $message = '장바구니에 추가되었습니다.';
        if ($upload_count > 0) {
            $message .= " (파일 {$upload_count}개 업로드 완료)";
        }
        error_log("장바구니 추가 성공: ID=$inserted_id, 업로드 파일 수=$upload_count, 추가옵션총액=$additional_options_total");

        // 디버그 로그 파일에도 성공 기록
        $success_log = date('[Y-m-d H:i:s]') . " ✅ 성공 - ID: $inserted_id, 추가옵션: $additional_options_total 원\n";
        file_put_contents($debug_log_file, $success_log, FILE_APPEND);

        ob_end_clean(); // 성공 시에도 버퍼 정리
        echo json_encode([
            'success' => true,
            'message' => $message,
            'cart_id' => $inserted_id,
            'additional_options_total' => $additional_options_total
        ]);
    } else {
        $error_msg = mysqli_stmt_error($stmt);
        error_log("INSERT 실행 오류: $error_msg");
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => '장바구니 추가 중 오류가 발생했습니다: ' . $error_msg]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    $error_msg = mysqli_error($connect);
    error_log("준비된 문장 생성 오류: $error_msg");
    error_log("쿼리: $insert_query");

    // 디버깅을 위한 파라미터 로그
    error_log("=== 파라미터 값 확인 ===");
    error_log("session_id: $session_id");
    error_log("MY_type: $MY_type, PN_type: $PN_type, MY_Fsd: $MY_Fsd");
    error_log("MY_amount: $MY_amount, POtype: $POtype, ordertype: $ordertype");
    error_log("price: $price, vat_price: $vat_price");
    error_log("additional_options_total: $additional_options_total");

    ob_end_clean();
    echo json_encode(['success' => false, 'message' => '데이터베이스 준비 오류: ' . $error_msg]);
}

mysqli_close($connect);
?>