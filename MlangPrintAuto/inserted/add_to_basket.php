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
$MY_type = isset($_POST['MY_type']) ? $_POST['MY_type'] : '';
$PN_type = isset($_POST['PN_type']) ? $_POST['PN_type'] : '';
$MY_Fsd = isset($_POST['MY_Fsd']) ? $_POST['MY_Fsd'] : '';
$MY_amount = isset($_POST['MY_amount']) ? $_POST['MY_amount'] : '';
$POtype = isset($_POST['POtype']) ? $_POST['POtype'] : '';
$ordertype = isset($_POST['ordertype']) ? $_POST['ordertype'] : '';
$price = intval(isset($_POST['price']) ? $_POST['price'] : 0);
$vat_price = intval(isset($_POST['vat_price']) ? $_POST['vat_price'] : 0);

// 파일 업로드 관련 데이터
$work_memo = isset($_POST['work_memo']) ? $_POST['work_memo'] : '';
$upload_method = isset($_POST['upload_method']) ? $_POST['upload_method'] : 'upload';
$uploaded_files_info = isset($_POST['uploaded_files_info']) ? $_POST['uploaded_files_info'] : '';

// 디버깅을 위한 로그
error_log("=== 전단지 장바구니 추가 시작 ===");
error_log("Session ID: $session_id");
error_log("POST 데이터: " . print_r($_POST, true));
error_log("파일 데이터: " . print_r($_FILES, true));

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
    echo json_encode(['success' => false, 'message' => '테이블 생성 오류: ' . $error_msg]);
    exit;
}
error_log("shop_temp 테이블 확인/생성 완료");

// 전단지용 필드들이 없으면 추가 (파일 업로드 필드 포함)
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
    'upload_folder' => "VARCHAR(255)"
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
    // 절대 경로로 업로드 폴더 설정
    $base_upload_dir = 'C:/xampp/htdocs/uploads/leaflet/';
    $date_folder = date('Y/m/d/');
    
    // IP 주소 안전하게 처리 (IPv6 ::1을 localhost로)
    $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    if ($remote_addr === '::1') {
        $remote_addr = 'localhost';
    }
    $ip_folder = str_replace([':', '.'], '_', $remote_addr) . '/';
    $time_folder = date('His') . '_' . $session_id . '/';
    
    $upload_folder = $base_upload_dir . $date_folder . $ip_folder . $time_folder;
    
    error_log("파일 업로드 시작: " . count($_FILES['uploaded_files']['name']) . "개 파일");
    error_log("업로드 폴더 경로: $upload_folder");
    
    // 폴더 생성 (재귀적으로 한번에)
    if (!file_exists($upload_folder)) {
        if (!mkdir($upload_folder, 0755, true)) {
            $error_msg = "업로드 폴더 생성 실패: $upload_folder";
            error_log($error_msg);
            error_log("현재 디렉토리: " . getcwd());
            error_log("베이스 경로: " . $base_upload_dir);
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

// 장바구니에 추가 (파일 업로드 정보 포함)
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, POtype, ordertype, st_price, st_price_vat, work_memo, upload_method, uploaded_files_info, upload_folder) 
                VALUES (?, 'leaflet', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

error_log("INSERT 쿼리: $insert_query");
error_log("바인드 파라미터: session_id=$session_id, MY_type=$MY_type, PN_type=$PN_type, MY_Fsd=$MY_Fsd, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype, price=$price, vat_price=$vat_price");

$stmt = mysqli_prepare($connect, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssiiisss", $session_id, $MY_type, $PN_type, $MY_Fsd, $MY_amount, $POtype, $ordertype, $price, $vat_price, $work_memo, $upload_method, $uploaded_files_info, $upload_folder);
    
    if (mysqli_stmt_execute($stmt)) {
        $inserted_id = mysqli_insert_id($connect);
        $message = '장바구니에 추가되었습니다.';
        if ($upload_count > 0) {
            $message .= " (파일 {$upload_count}개 업로드 완료)";
        }
        error_log("장바구니 추가 성공: ID=$inserted_id, 업로드 파일 수=$upload_count");
        echo json_encode(['success' => true, 'message' => $message, 'cart_id' => $inserted_id]);
    } else {
        $error_msg = mysqli_stmt_error($stmt);
        error_log("INSERT 실행 오류: $error_msg");
        echo json_encode(['success' => false, 'message' => '장바구니 추가 중 오류가 발생했습니다: ' . $error_msg]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    $error_msg = mysqli_error($connect);
    error_log("준비된 문장 생성 오류: $error_msg");
    echo json_encode(['success' => false, 'message' => '데이터베이스 준비 오류: ' . $error_msg]);
}

mysqli_close($connect);
?>