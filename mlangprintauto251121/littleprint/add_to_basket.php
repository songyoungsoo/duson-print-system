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
$Section = $_POST['Section'] ?? $_POST['TreeSelect'] ?? '';  // 재질 (TreeSelect 호환)
$PN_type = $_POST['PN_type'] ?? '';       // 규격
$MY_amount = $_POST['MY_amount'] ?? '';    // 수량
$POtype = $_POST['POtype'] ?? '';          // 인쇄면
$ordertype = $_POST['ordertype'] ?? '';    // 디자인편집
$price = intval($_POST['calculated_price'] ?? $_POST['price'] ?? 0);
$vat_price = intval($_POST['calculated_vat_price'] ?? $_POST['vat_price'] ?? 0);

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
error_log("=== 포스터 장바구니 디버그 ===");
error_log("전체 POST 데이터: " . print_r($_POST, true));
error_log("전체 FILES 데이터: " . print_r($_FILES, true));
error_log("받은 값: MY_type=$MY_type, Section=$Section, PN_type=$PN_type, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype");
error_log("가격: price=$price, vat_price=$vat_price");
error_log("추가 옵션: $additional_options_json, total=$additional_options_total");

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
    if (empty($MY_type)) $missing_fields[] = 'MY_type (받은값: ' . ($MY_type ?: 'empty') . ')';
    if (empty($Section)) $missing_fields[] = 'Section/TreeSelect (받은값: ' . ($Section ?: 'empty') . ', POST[TreeSelect]: ' . ($_POST['TreeSelect'] ?? 'none') . ')';
    if (empty($MY_amount)) $missing_fields[] = 'MY_amount (받은값: ' . ($MY_amount ?: 'empty') . ')';
    if (empty($POtype)) $missing_fields[] = 'POtype (받은값: ' . ($POtype ?: 'empty') . ')';
    if (empty($ordertype)) $missing_fields[] = 'ordertype (받은값: ' . ($ordertype ?: 'empty') . ')';

    error_log("❌ 입력값 검증 실패: " . implode(', ', $missing_fields));
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

// ✅ 파일 업로드 처리 (통일된 경로)
$upload_count = 0;

// ✅ UploadPathHelper 사용: 표준화된 경로 생성
require_once __DIR__ . '/../../includes/UploadPathHelper.php';

$paths = UploadPathHelper::generateUploadPath('littleprint');
$upload_folder = $paths['full_path'];
$upload_folder_db = $paths['db_path']; // DB 저장용 (ImgFolder 제외)

error_log("포스터 업로드 경로: $upload_folder");
error_log("포스터 DB 경로: $upload_folder_db");

// 폴더 생성 (파일 업로드 여부와 관계없이 항상 생성)
if (!file_exists($upload_folder)) {
    mkdir($upload_folder, 0755, true);
    error_log("✅ 포스터 폴더 생성: $upload_folder");
}

// 파일 업로드 처리
$uploaded_files = [];
if (!empty($_FILES['uploaded_files'])) {
    error_log("📤 포스터 파일 업로드 시작: " . count($_FILES['uploaded_files']['name']) . "개");
    
    foreach ($_FILES['uploaded_files']['name'] as $key => $filename) {
        if ($_FILES['uploaded_files']['error'][$key] == UPLOAD_ERR_OK) {
            $temp_file = $_FILES['uploaded_files']['tmp_name'][$key];
            $target_filename = $filename;
            $target_path = $upload_folder . '/' . $target_filename;

            if (move_uploaded_file($temp_file, $target_path)) {
                $upload_count++;
                $uploaded_files[] = [
                    'original_name' => $filename,
                    'saved_name' => $target_filename,
                    'path' => $target_path,
                    'size' => $_FILES['uploaded_files']['size'][$key],
                    'web_url' => '/ImgFolder/' . $upload_folder_db . '/' . $target_filename
                ];
                error_log("✅ 포스터 파일 업로드 성공: $target_path");
            }
        }
    }
}

// ImgFolder 컬럼 추가
$check_column_query = "SHOW COLUMNS FROM shop_temp LIKE 'ImgFolder'";
$column_result = mysqli_query($db, $check_column_query);
if (mysqli_num_rows($column_result) == 0) {
    $add_column_query = "ALTER TABLE shop_temp ADD COLUMN ImgFolder VARCHAR(255)";
    mysqli_query($db, $add_column_query);
}

// 파일 정보 JSON 변환
$files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

// 장바구니에 추가 (추가 옵션 JSON 방식 + 파일 업로드)
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, PN_type, MY_amount, POtype, ordertype, st_price, st_price_vat,
                additional_options, additional_options_total, ImgFolder, uploaded_files)
                VALUES (?, 'poster', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssssssssisiss",
        $session_id, $MY_type, $Section, $PN_type, $MY_amount, $POtype, $ordertype, $price, $vat_price,
        $additional_options_json, $additional_options_total, $upload_folder_db, $files_json);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = '장바구니에 추가되었습니다.';
        if ($upload_count > 0) {
            $message .= " (파일 {$upload_count}개 업로드 완료)";
        }
        success_response(['upload_count' => $upload_count], $message);
    } else {
        error_response('장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
} else {
    error_response('데이터베이스 오류가 발생했습니다: ' . mysqli_error($db));
}

mysqli_close($db);
?>