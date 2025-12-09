<?php
// 명함 성공 패턴 적용 - 안전한 JSON 응답 처리
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

session_start();
$session_id = session_id();

include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 안전한 JSON 응답 함수 (명함 패턴)
function safe_json_response($success = true, $data = null, $message = '') {
    ob_clean(); // 이전 출력 완전 정리
    
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 데이터 받기
$product_type = $_POST['product_type'] ?? 'ncrflambeau';
$MY_type = $_POST['MY_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$calculated_price = $_POST['calculated_price'] ?? 0;
$calculated_vat_price = $_POST['calculated_vat_price'] ?? 0;

// 추가 옵션 데이터 수집 (넘버링 + 미싱만)
$additional_options = [];
$additional_total = intval($_POST['additional_options_total'] ?? 0);

if ($additional_total > 0) {
    // 넘버링 (folding_enabled로 전송됨)
    if (isset($_POST['folding_enabled']) && $_POST['folding_enabled'] == '1') {
        $additional_options['folding_enabled'] = true;
        $additional_options['folding_type'] = $_POST['folding_type'] ?? '';
        $additional_options['folding_price'] = intval($_POST['folding_price'] ?? 0);
    }

    // 미싱 (creasing_enabled로 전송됨)
    if (isset($_POST['creasing_enabled']) && $_POST['creasing_enabled'] == '1') {
        $additional_options['creasing_enabled'] = true;
        $additional_options['creasing_lines'] = $_POST['creasing_lines'] ?? '';
        $additional_options['creasing_price'] = intval($_POST['creasing_price'] ?? 0);
    }

    $additional_options['additional_options_total'] = $additional_total;
}

$additional_options_json = json_encode($additional_options, JSON_UNESCAPED_UNICODE);

// 필수 필드 검증
if (empty($MY_type) || empty($MY_Fsd) || empty($PN_type) || empty($MY_amount) || empty($ordertype)) {
    safe_json_response(false, null, '모든 옵션을 선택해주세요.');
}

if (empty($calculated_price) || empty($calculated_vat_price)) {
    safe_json_response(false, null, '가격 정보가 없습니다. 다시 계산해주세요.');
}

try {
    // shop_temp 테이블에 필요한 컬럼이 있는지 확인하고 없으면 추가
    $required_columns = [
        'session_id' => 'VARCHAR(255)',
        'product_type' => 'VARCHAR(50)',
        'MY_type' => 'VARCHAR(50)',
        'MY_Fsd' => 'VARCHAR(50)',
        'PN_type' => 'VARCHAR(50)',
        'MY_amount' => 'VARCHAR(50)',
        'ordertype' => 'VARCHAR(50)',
        'st_price' => 'INT(11)',
        'st_price_vat' => 'INT(11)',
        'premium_options' => 'TEXT',
        'premium_options_total' => 'INT(11)',
        'work_memo' => 'TEXT',
        'upload_method' => 'VARCHAR(50)',
        'uploaded_files' => 'TEXT',
        'ThingCate' => 'VARCHAR(255)',
        'ImgFolder' => 'VARCHAR(255)'
    ];

    foreach ($required_columns as $column_name => $column_definition) {
        $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
        $column_result = mysqli_query($db, $check_column_query);
        if (mysqli_num_rows($column_result) == 0) {
            $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
            if (!mysqli_query($db, $add_column_query)) {
                error_log("컬럼 추가 실패: $column_name - " . mysqli_error($db));
            }
        }
    }

    // ✅ 파일 업로드 처리 먼저 (경로 생성)
    $upload_count = 0;
    // ✅ UploadPathHelper 사용: 표준화된 경로 생성
    require_once __DIR__ . '/../../includes/UploadPathHelper.php';

    $paths = UploadPathHelper::generateUploadPath('ncrflambeau');
    $upload_folder = $paths['full_path'];
    $upload_folder_db = $paths['db_path']; // DB 저장용 (ImgFolder 제외)
    
    // 폴더 생성 (파일 업로드 여부와 관계없이 항상 생성)
    if (!file_exists($upload_folder)) {
        mkdir($upload_folder, 0755, true);
        error_log("✅ 양식지 폴더 생성: $upload_folder");
    }
    
    // 파일 업로드 처리
    $uploaded_files = [];
    if (!empty($_FILES['uploaded_files'])) {
        error_log("📤 양식지 파일 업로드 시작: " . count($_FILES['uploaded_files']['name']) . "개");
        
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
                    error_log("✅ 양식지 파일 업로드 성공: $target_path");
                }
            }
        }
    }
    
    // ✅ INSERT 방식으로 통일 - 모든 데이터를 한 번에 저장
    $work_memo = $_POST['work_memo'] ?? '';
    $upload_method = $_POST['upload_method'] ?? 'upload';
    $price = intval($calculated_price);
    $vat_price = intval($calculated_vat_price);
    
    // 파일 정보 JSON 변환
    $files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

    $insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, ordertype, st_price, st_price_vat, 
                     premium_options, premium_options_total, work_memo, upload_method, ImgFolder, uploaded_files)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $insert_query);
    if (!$stmt) {
        throw new Exception('쿼리 준비 실패: ' . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt, "sssssssisisssss",
        $session_id, $product_type, $MY_type, $MY_Fsd, $PN_type, $MY_amount, $ordertype, 
        $price, $vat_price, $additional_options_json, $additional_total,
        $work_memo, $upload_method, $upload_folder_db, $files_json);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('INSERT 실패: ' . mysqli_stmt_error($stmt));
    }
    
    $basket_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);
    
    // 성공 로그
    error_log("NcrFlambeau 장바구니 추가 성공: basket_id=$basket_id, session_id=$session_id, upload_count=$upload_count");
    
    $message = '장바구니에 추가되었습니다.';
    if ($upload_count > 0) {
        $message .= " (파일 {$upload_count}개 업로드 완료)";
    }
    
    safe_json_response(true, ['basket_id' => $basket_id, 'upload_count' => $upload_count], $message);
    
} catch (Exception $e) {
    error_log("NcrFlambeau 장바구니 추가 오류: " . $e->getMessage());
    safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . $e->getMessage());
}

mysqli_close($db);
?>