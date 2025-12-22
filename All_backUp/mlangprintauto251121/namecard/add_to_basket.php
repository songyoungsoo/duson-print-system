<?php
// 공통 응답 함수 포함 (출력 버퍼링 및 에러 처리 포함)
require_once __DIR__ . '/../../includes/safe_json_response.php';

// JSON 헤더 우선 설정
header('Content-Type: application/json; charset=utf-8');

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
$Section = $_POST['Section'] ?? ''; // 명함 재질
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'namecard';

// 추가 정보
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 🆕 프리미엄 옵션 데이터 받기
$premium_options = [
    'foil_enabled' => $_POST['foil_enabled'] ?? 0,
    'foil_type' => $_POST['foil_type'] ?? '',
    'foil_price' => intval($_POST['foil_price'] ?? 0),
    'numbering_enabled' => $_POST['numbering_enabled'] ?? 0,
    'numbering_type' => $_POST['numbering_type'] ?? '',
    'numbering_price' => intval($_POST['numbering_price'] ?? 0),
    'perforation_enabled' => $_POST['perforation_enabled'] ?? 0,
    'perforation_type' => $_POST['perforation_type'] ?? '',
    'perforation_price' => intval($_POST['perforation_price'] ?? 0),
    'rounding_enabled' => $_POST['rounding_enabled'] ?? 0,
    'rounding_price' => intval($_POST['rounding_price'] ?? 0),
    'creasing_enabled' => $_POST['creasing_enabled'] ?? 0,
    'creasing_type' => $_POST['creasing_type'] ?? '',
    'creasing_price' => intval($_POST['creasing_price'] ?? 0),
    'premium_options_total' => intval($_POST['premium_options_total'] ?? 0)
];
$premium_options_json = json_encode($premium_options, JSON_UNESCAPED_UNICODE);
$premium_total = intval($premium_options['premium_options_total']);

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그
error_log("=== 명함 장바구니 추가 시작 ===");
error_log("받은 POST 데이터: " . print_r($_POST, true));
error_log("세션 ID: " . session_id());
error_log("데이터베이스 연결 상태: " . ($db ? "OK" : "실패"));

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

$paths = UploadPathHelper::generateUploadPath('namecard');
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
                    'size' => $_FILES['uploaded_files']['size'][$key]
                ];
            }
        }
    }
}

// 필요한 컬럼이 있는지 확인하고 없으면 추가
$required_columns = [
    'premium_options' => 'TEXT',
    'premium_options_total' => 'INT(11) DEFAULT 0',
    'MY_type_name' => 'VARCHAR(100) DEFAULT NULL',
    'Section_name' => 'VARCHAR(100) DEFAULT NULL',
    'POtype_name' => 'VARCHAR(100) DEFAULT NULL'
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($db, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        if (!mysqli_query($db, $add_column_query)) {
            error_log("컬럼 $column_name 추가 오류: " . mysqli_error($db));
            safe_json_response(false, null, "데이터베이스 설정 오류가 발생했습니다. 관리자에게 문의하세요.");
        } else {
            error_log("컬럼 $column_name 성공적으로 추가됨");
        }
    }
}

// 명함 옵션명 조회
$MY_type_name = '';
$Section_name = '';
$POtype_name = '';

// MY_type 이름 조회
if (!empty($MY_type)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'NameCard'";
    $name_stmt = mysqli_prepare($db, $name_query);
    if ($name_stmt) {
        mysqli_stmt_bind_param($name_stmt, "s", $MY_type);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        if ($name_row = mysqli_fetch_assoc($name_result)) {
            $MY_type_name = $name_row['title'];
        }
        mysqli_stmt_close($name_stmt);
    }
}

// Section 이름 조회
if (!empty($Section)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'NameCard'";
    $name_stmt = mysqli_prepare($db, $name_query);
    if ($name_stmt) {
        mysqli_stmt_bind_param($name_stmt, "s", $Section);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        if ($name_row = mysqli_fetch_assoc($name_result)) {
            $Section_name = $name_row['title'];
        }
        mysqli_stmt_close($name_stmt);
    }
}

// POtype 이름 설정
switch ($POtype) {
    case '1':
        $POtype_name = '단면칼라';
        break;
    case '2':
        $POtype_name = '양면칼라';
        break;
    default:
        $POtype_name = '';
}

// 장바구니에 추가 - 모든 필드 포함
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype, st_price, st_price_vat, premium_options, premium_options_total, MY_type_name, Section_name, POtype_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

error_log("SQL 쿼리: " . $insert_query);
$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    error_log("mysqli_prepare 성공");

    // 디버그 로깅
    error_log("=== 명함 장바구니 저장 디버그 ===");
    error_log("premium_options_total: " . $premium_total);
    error_log("Basic data: session_id=$session_id, product_type=$product_type, MY_type=$MY_type, Section=$Section, POtype=$POtype, MY_amount=$MY_amount, ordertype=$ordertype, price=$price, vat_price=$vat_price");

    error_log("bind_param 전 - 파라미터 수: 14개");
    error_log("=== 명함 옵션명 디버그 ===");
    error_log("MY_type_name: " . $MY_type_name);
    error_log("Section_name: " . $Section_name);
    error_log("POtype_name: " . $POtype_name);

    $bind_result = mysqli_stmt_bind_param($stmt, "sssssssiisissss",
        $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype,
        $price, $vat_price, $premium_options_json, $premium_total,
        $MY_type_name, $Section_name, $POtype_name);

    if (!$bind_result) {
        error_log("mysqli_stmt_bind_param 실패: " . mysqli_stmt_error($stmt));
        safe_json_response(false, null, 'bind_param 오류가 발생했습니다.');
    }

    error_log("bind_param 성공, execute 시도 중...");
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
        $error_msg = mysqli_stmt_error($stmt);
        error_log("명함 장바구니 저장 실패: " . $error_msg);
        error_log("SQL: " . $insert_query);
        mysqli_stmt_close($stmt);
        safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . $error_msg);
    }
} else {
    $error_msg = mysqli_error($db);
    error_log("명함 장바구니 prepare 실패: " . $error_msg);
    error_log("SQL: " . $insert_query);
    safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . $error_msg);
}

mysqli_close($db);

?>
