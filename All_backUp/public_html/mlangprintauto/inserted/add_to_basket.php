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
$PN_type = $_POST['PN_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = intval($_POST['calculated_price'] ?? $_POST['price'] ?? 0);
$vat_price = intval($_POST['calculated_vat_price'] ?? $_POST['vat_price'] ?? 0);
$product_type = $_POST['product_type'] ?? 'leaflet';

// 추가 정보
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 전단지 추가 옵션 데이터
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

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그
error_log("=== 전단지 장바구니 추가 시작 ===");
error_log("받은 POST 데이터: " . print_r($_POST, true));
error_log("세션 ID: " . session_id());
error_log("데이터베이스 연결 상태: " . ($db ? "OK" : "실패"));

if (empty($MY_type) || empty($PN_type) || empty($MY_Fsd) || empty($POtype) || empty($MY_amount) || empty($ordertype)) {
    $missing_fields = [];
    if (empty($MY_type)) $missing_fields[] = 'MY_type';
    if (empty($PN_type)) $missing_fields[] = 'PN_type';
    if (empty($MY_Fsd)) $missing_fields[] = 'MY_Fsd';
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
error_log("PN_type: " . $PN_type);
error_log("MY_Fsd: " . $MY_Fsd);
error_log("POtype: " . $POtype);
error_log("MY_amount: " . $MY_amount);
error_log("Price: " . $price);
error_log("Work memo length: " . strlen($work_memo));

// 파일 업로드 처리
$uploaded_files = [];

// ✅ 구버전 경로 구조: _MlangPrintAuto_inserted_index.php/YYYY/MMDD/IP주소/타임스탬프/
// ✅ UploadPathHelper 사용: 표준화된 경로 생성
require_once __DIR__ . '/../../includes/UploadPathHelper.php';

$paths = UploadPathHelper::generateUploadPath('inserted');
$upload_directory = $paths['full_path'];
$upload_directory_db = $paths['db_path']; // DB 저장용 (ImgFolder 제외)

error_log("업로드 경로: $upload_directory");
error_log("DB 저장 경로: $upload_directory_db");

if (!empty($_FILES['uploaded_files'])) {
    error_log("📤 파일 업로드 시작: " . count($_FILES['uploaded_files']['name']) . "개");
    
    // 디렉토리 생성
    if (!file_exists($upload_directory)) {
        mkdir($upload_directory, 0755, true);
        error_log("✅ 폴더 생성: $upload_directory");
    }
    
    foreach ($_FILES['uploaded_files']['name'] as $key => $filename) {
        if ($_FILES['uploaded_files']['error'][$key] == UPLOAD_ERR_OK) {
            $temp_file = $_FILES['uploaded_files']['tmp_name'][$key];
            // ✅ 구버전: 원본 파일명 그대로 저장
            $target_filename = $filename;
            $target_path = $upload_directory . '/' . $target_filename;
            
            error_log("파일 처리: $filename → $target_path");
            
            if (move_uploaded_file($temp_file, $target_path)) {
                $uploaded_files[] = [
                    'original_name' => $filename,
                    'saved_name' => $target_filename,
                    'path' => $target_path,
                    'size' => $_FILES['uploaded_files']['size'][$key],
                    'web_url' => '/ImgFolder/' . $upload_directory_db . '/' . $target_filename
                ];
                error_log("✅ 파일 업로드 성공: $target_path");
            } else {
                error_log("❌ 파일 이동 실패: $temp_file → $target_path");
            }
        } else {
            error_log("❌ 파일 에러 코드: " . $_FILES['uploaded_files']['error'][$key]);
        }
    }
} else {
    error_log("⚠️ 업로드된 파일 없음");
}

// 장바구니에 추가
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, POtype, ordertype, st_price, st_price_vat, additional_options, additional_options_total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

error_log("SQL 쿼리: " . $insert_query);
$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    error_log("mysqli_prepare 성공");

    $bind_result = mysqli_stmt_bind_param($stmt, "ssssssssiisi",
        $session_id, $product_type, $MY_type, $PN_type, $MY_Fsd, $MY_amount, $POtype, $ordertype,
        $price, $vat_price, $additional_options_json, $additional_options_total);

    if (!$bind_result) {
        error_log("mysqli_stmt_bind_param 실패: " . mysqli_stmt_error($stmt));
        safe_json_response(false, null, 'bind_param 오류가 발생했습니다.');
    }

    error_log("bind_param 성공, execute 시도 중...");
    if (mysqli_stmt_execute($stmt)) {
        $basket_id = mysqli_insert_id($db);
        error_log("✅ INSERT 성공, basket_id: $basket_id");
        
        // 추가 정보는 별도 업데이트로 처리
        mysqli_stmt_close($stmt);
        
        // 추가 정보 업데이트 (기존 시스템 호환)
        $files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);
        $thing_cate = $MY_type . '_' . $PN_type . '_' . $MY_Fsd;
        // ImgFolder는 DB 저장용 상대 경로 사용
        $img_folder_path = $upload_directory_db;
        
        error_log("UPDATE 준비: ThingCate=$thing_cate, ImgFolder=$img_folder_path");
        error_log("파일 JSON: $files_json");
        
        $update_query = "UPDATE shop_temp SET work_memo = ?, upload_method = ?, uploaded_files = ?, ThingCate = ?, ImgFolder = ? WHERE no = ?";
        $update_stmt = mysqli_prepare($db, $update_query);
        
        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "sssssi", $work_memo, $upload_method, $files_json, $thing_cate, $img_folder_path, $basket_id);
            if (mysqli_stmt_execute($update_stmt)) {
                error_log("✅ UPDATE 성공");
            } else {
                error_log("❌ UPDATE 실패: " . mysqli_stmt_error($update_stmt));
            }
            mysqli_stmt_close($update_stmt);
        }
        
        $response_data = [
            'basket_id' => $basket_id,
            'uploaded_files_count' => count($uploaded_files),
            'upload_directory' => $upload_directory_db
        ];
        
        safe_json_response(true, $response_data, '장바구니에 추가되었습니다.');
        
    } else {
        $error_msg = mysqli_stmt_error($stmt);
        error_log("전단지 장바구니 저장 실패: " . $error_msg);
        error_log("SQL: " . $insert_query);
        mysqli_stmt_close($stmt);
        safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . $error_msg);
    }
} else {
    $error_msg = mysqli_error($db);
    error_log("전단지 장바구니 prepare 실패: " . $error_msg);
    error_log("SQL: " . $insert_query);
    safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . $error_msg);
}

mysqli_close($db);

?>
