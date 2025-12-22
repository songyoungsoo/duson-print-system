<?php
// 공통 응답 함수 포함 (출력 버퍼링 및 에러 처리 포함)
require_once __DIR__ . '/../../includes/safe_json_response.php';

// JSON 헤더 우선 설정
header('Content-Type: application/json; charset=utf-8');

// 전체 에러 처리
try {
    error_log("=== 상품권 장바구니 처리 시작 ===");

    // 세션 시작
    session_start();
    error_log("세션 시작 완료");

    // 공통 함수 포함
    error_log("functions.php 포함 시작");
    include "../../includes/functions.php";
    error_log("functions.php 포함 완료");

    error_log("db.php 포함 시작");
    include "../../db.php";
    error_log("db.php 포함 완료");

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터 받기
$action = $_POST['action'] ?? '';
$MY_type = $_POST['MY_type'] ?? '';
$Section = $_POST['Section'] ?? ''; // 상품권 재질
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'merchandisebond';

// 추가 정보
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 프리미엄 옵션 데이터 수집 및 검증
$premium_options = [];
$premium_total = 0;

// 프리미엄 옵션 데이터 추출
if (isset($_POST['premium_options_total'])) {
    $premium_total = intval($_POST['premium_options_total']);

    // 상품권 프리미엄 옵션들 (명함과 동일)
    $option_names = ['foil', 'numbering', 'perforation', 'rounding', 'creasing'];

    foreach ($option_names as $option) {
        if (isset($_POST["{$option}_enabled"]) && $_POST["{$option}_enabled"] == '1') {
            $premium_options["{$option}_enabled"] = true;

            // 타입이 있는 옵션들 (코팅, 엠보싱, 금박)
            if (isset($_POST["{$option}_type"]) && !empty($_POST["{$option}_type"])) {
                $premium_options["{$option}_type"] = $_POST["{$option}_type"];
            }

            // 개별 가격 저장
            if (isset($_POST["{$option}_price"])) {
                $premium_options["{$option}_price"] = intval($_POST["{$option}_price"]);
            }
        }
    }

    $premium_options['premium_options_total'] = $premium_total;
}

// JSON 형태로 변환
$premium_options_json = json_encode($premium_options, JSON_UNESCAPED_UNICODE);

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그 (안전하게)
try {
    error_log("상품권 장바구니 추가 시작");
    error_log("POST 데이터 키들: " . implode(', ', array_keys($_POST)));
    error_log("파일 데이터: " . (isset($_FILES['uploaded_files']) ? "있음" : "없음"));
} catch (Exception $e) {
    error_log("디버그 로깅 오류: " . $e->getMessage());
}

if (empty($MY_type) || empty($Section) || empty($POtype) || empty($MY_amount) || empty($ordertype)) {
    $missing_fields = [];
    if (empty($MY_type)) $missing_fields[] = 'MY_type';
    if (empty($Section)) $missing_fields[] = 'Section';
    if (empty($POtype)) $missing_fields[] = 'POtype';
    if (empty($MY_amount)) $missing_fields[] = 'MY_amount';
    if (empty($ordertype)) $missing_fields[] = 'ordertype';
    
    safe_json_response(false, null, '필수 정보가 누락되었습니다: ' . implode(', ', $missing_fields));
}

// 세션 ID 가져오기
$session_id = session_id();

// 디버그 로깅 강화
error_log("=== Cart Debug Info ===");
error_log("Session ID: " . $session_id);
error_log("Action: " . $action);
error_log("MY_type: " . $MY_type);
error_log("Section: " . $Section);
error_log("POtype: " . $POtype);
error_log("MY_amount: " . $MY_amount);
error_log("Price: " . $price);
error_log("Work memo length: " . strlen($work_memo));

// 파일 업로드 처리 (기존 PHPClass/MultyUpload 방식과 호환)
$uploaded_files = [];
$log_url = $_POST['log_url'] ?? '';
$log_y = $_POST['log_y'] ?? '';
$log_md = $_POST['log_md'] ?? '';
$log_ip = $_POST['log_ip'] ?? '';
$log_time = $_POST['log_time'] ?? '';

// ✅ UploadPathHelper 사용: 표준화된 경로 생성
require_once __DIR__ . '/../../includes/UploadPathHelper.php';

$paths = UploadPathHelper::generateUploadPath('merchandisebond');
$upload_directory = $paths['full_path'];
$upload_directory_db = $paths['db_path']; // DB 저장용 (ImgFolder 제외)

if (!empty($_FILES['uploaded_files'])) {
    // 디렉토리 생성
    if (!file_exists($upload_directory)) {
        mkdir($upload_directory, 0755, true);
    }
    
    foreach ($_FILES['uploaded_files']['name'] as $key => $filename) {
        if ($_FILES['uploaded_files']['error'][$key] == UPLOAD_ERR_OK) {
            $temp_file = $_FILES['uploaded_files']['tmp_name'][$key];
            // ✅ 구버전: 원본 파일명 그대로 저장
            $target_filename = $filename;
            $target_path = $upload_directory . '/' . $target_filename;
            
            if (move_uploaded_file($temp_file, $target_path)) {
                $uploaded_files[] = [
                    'original_name' => $filename,
                    'saved_name' => $target_filename,
                    'path' => $target_path,
                    'size' => $_FILES['uploaded_files']['size'][$key],
                    'web_url' => '/ImgFolder/' . $upload_directory_db . '/' . $target_filename
                ];
            }
        }
    }
}

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
            safe_json_response(false, null, "컬럼 $column_name 추가 오류: " . mysqli_error($db));
        }
    }
}

// 장바구니에 추가 - 기본 필드만으로 단순화
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype, st_price, st_price_vat, premium_options, premium_options_total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssiisi",
        $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype, $price, $vat_price, $premium_options_json, $premium_total);
    
    if (mysqli_stmt_execute($stmt)) {
        $basket_id = mysqli_insert_id($db);
        
        // 추가 정보는 별도 업데이트로 처리
        mysqli_stmt_close($stmt);
        
        // 추가 정보 업데이트 (기존 시스템 호환)
        $files_json = json_encode($uploaded_files);
        $thing_cate = $MY_type . '_' . $Section;
        // ImgFolder는 DB 저장용 상대 경로 사용
        $img_folder_path = $upload_directory_db;
        
        $update_query = "UPDATE shop_temp SET work_memo = ?, upload_method = ?, uploaded_files = ?, ThingCate = ?, ImgFolder = ? WHERE no = ?";
        $update_stmt = mysqli_prepare($db, $update_query);
        
        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "sssssi", $work_memo, $upload_method, $files_json, $thing_cate, $img_folder_path, $basket_id);
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
    safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db));
}

mysqli_close($db);

} catch (Exception $e) {
    // 치명적인 오류 처리
    error_log("상품권 장바구니 치명적 오류: " . $e->getMessage());
    error_log("오류 스택: " . $e->getTraceAsString());
    safe_json_response(false, null, '시스템 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
} catch (Error $e) {
    // PHP 7+ Fatal Error 처리
    error_log("상품권 장바구니 Fatal Error: " . $e->getMessage());
    error_log("오류 스택: " . $e->getTraceAsString());
    safe_json_response(false, null, '치명적인 오류가 발생했습니다. 관리자에게 문의해주세요.');
}

?>
