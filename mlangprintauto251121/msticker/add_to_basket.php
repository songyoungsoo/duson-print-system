<?php
// 세션 시작
session_start();

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터 받기
$action = $_POST['action'] ?? '';
$MY_type = $_POST['MY_type'] ?? '';
$Section = $_POST['Section'] ?? ''; // 자석스티커 규격
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'msticker';
$selected_options = $_POST['selected_options'] ?? '';
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? '';

// 입력값 검증
if ($action !== 'add_to_basket') {
    error_response('잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그
error_log("자석스티커 장바구니 추가 - 받은 데이터: " . print_r($_POST, true));

if (empty($MY_type) || empty($Section) || empty($POtype) || empty($MY_amount) || empty($ordertype)) {
    $missing_fields = [];
    if (empty($MY_type)) $missing_fields[] = 'MY_type';
    if (empty($Section)) $missing_fields[] = 'Section';
    if (empty($POtype)) $missing_fields[] = 'POtype';
    if (empty($MY_amount)) $missing_fields[] = 'MY_amount';
    if (empty($ordertype)) $missing_fields[] = 'ordertype';
    
    error_response('필수 정보가 누락되었습니다: ' . implode(', ', $missing_fields));
}

// 세션 ID 가져오기
$session_id = session_id();

// 필요한 컬럼이 있는지 확인하고 없으면 추가
$required_columns = [
    'session_id' => 'VARCHAR(255)',
    'product_type' => 'VARCHAR(50)',
    'MY_type' => 'VARCHAR(50)',
    'Section' => 'VARCHAR(50)',
    'POtype' => 'VARCHAR(50)',
    'MY_amount' => 'VARCHAR(50)',
    'ordertype' => 'VARCHAR(50)',
    'st_price' => 'INT(11)',
    'st_price_vat' => 'INT(11)',
    'selected_options' => 'TEXT',
    'work_memo' => 'TEXT',
    'upload_method' => 'VARCHAR(50)'
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

$paths = UploadPathHelper::generateUploadPath('msticker');
$upload_folder = $paths['full_path'];
$upload_folder_db = $paths['db_path']; // DB 저장용 (ImgFolder 제외)

// 폴더 생성 (파일 업로드 여부와 관계없이 항상 생성)
if (!file_exists($upload_folder)) {
    mkdir($upload_folder, 0755, true);
    error_log("✅ 자석스티커 폴더 생성: $upload_folder");
}

// 파일 업로드 처리
$uploaded_files = [];
if (!empty($_FILES['uploaded_files'])) {
    error_log("📤 자석스티커 파일 업로드 시작: " . count($_FILES['uploaded_files']['name']) . "개");
    
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
                error_log("✅ 자석스티커 파일 업로드 성공: $target_path");
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

// 장바구니에 추가
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype, st_price, st_price_vat, selected_options, work_memo, upload_method, ImgFolder, uploaded_files) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssissssss", $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype, $price, $vat_price, $selected_options, $work_memo, $upload_method, $upload_folder_db, $files_json);
    
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
