<?php
require_once __DIR__ . '/../../includes/safe_json_response.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

include "../../includes/functions.php";
include "../../includes/upload_path_manager.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터
$session_id = session_id();
$product_type = $_POST['product_type'] ?? 'leaflet';
$MY_type = $_POST['MY_type'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = intval($_POST['calculated_price'] ?? $_POST['price'] ?? 0);
$vat_price = intval($_POST['calculated_vat_price'] ?? $_POST['vat_price'] ?? 0);
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';

// 추가 옵션
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

// 필수 필드 검증
if (empty($MY_type) || empty($PN_type) || empty($MY_Fsd) || empty($POtype) || empty($MY_amount) || empty($ordertype)) {
    safe_json_response(false, null, '필수 정보가 누락되었습니다.');
}

// 파일 업로드 처리 (스티커와 동일한 방식)
$uploaded_files = [];
$img_folder = '';
$thing_cate = '';

try {
    // 레거시 경로 생성
    $upload_path_info = generateUploadPath('inserted');
    $img_folder = $upload_path_info['img_folder'];

    // 새 모달 형식 (files[]) 또는 기존 형식 (uploaded_files) 지원
    $files_to_process = null;
    if (!empty($_FILES['files'])) {
        $files_to_process = $_FILES['files'];
    } elseif (!empty($_FILES['uploaded_files'])) {
        $files_to_process = $_FILES['uploaded_files'];
    }

    if ($files_to_process && !empty($files_to_process['name'])) {
        // 레거시 디렉토리 생성
        createUploadDirectory($upload_path_info['physical_path']);

        // 파일이 배열인지 단일 파일인지 확인
        if (is_array($files_to_process['name'])) {
            // 다중 파일 처리
            foreach ($files_to_process['name'] as $key => $filename) {
                if ($files_to_process['error'][$key] == UPLOAD_ERR_OK) {
                    $temp_file = $files_to_process['tmp_name'][$key];

                    // 레거시 파일명 생성
                    $unique_filename = generateUniqueFilename($filename, $upload_path_info['timestamp']);
                    $target_path = $upload_path_info['physical_path'] . $unique_filename;

                    if (move_uploaded_file($temp_file, $target_path)) {
                        $uploaded_files[] = [
                            'original_name' => $filename,
                            'saved_name' => $unique_filename,
                            'path' => $target_path,
                            'size' => $files_to_process['size'][$key]
                        ];

                        // 첫 번째 파일을 대표 파일로 설정
                        if (empty($thing_cate)) {
                            $thing_cate = $unique_filename;
                        }
                    }
                }
            }
        } else {
            // 단일 파일 처리
            if ($files_to_process['error'] == UPLOAD_ERR_OK) {
                $temp_file = $files_to_process['tmp_name'];
                $filename = $files_to_process['name'];

                // 레거시 파일명 생성
                $unique_filename = generateUniqueFilename($filename, $upload_path_info['timestamp']);
                $target_path = $upload_path_info['physical_path'] . $unique_filename;

                if (move_uploaded_file($temp_file, $target_path)) {
                    $uploaded_files[] = [
                        'original_name' => $filename,
                        'saved_name' => $unique_filename,
                        'path' => $target_path,
                        'size' => $files_to_process['size']
                    ];

                    $thing_cate = $unique_filename;
                }
            }
        }
    }

    // ImgFolder 경로 저장 (레거시 형식)
    if (!empty($uploaded_files)) {
        $img_folder = $upload_path_info['img_folder'];
    }

} catch (Exception $e) {
    error_log("Upload error: " . $e->getMessage());
    safe_json_response(false, null, '파일 업로드 중 오류가 발생했습니다: ' . $e->getMessage());
}

$original_filename_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

// INSERT
$sql = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, POtype, ordertype, st_price, st_price_vat, additional_options, additional_options_total, ImgFolder, ThingCate, original_filename)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $sql);
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($db));
    safe_json_response(false, null, 'SQL 준비 실패: ' . mysqli_error($db));
}

mysqli_stmt_bind_param($stmt, "ssssssssiisisss",
    $session_id, $product_type, $MY_type, $PN_type, $MY_Fsd, $MY_amount, $POtype, $ordertype,
    $price, $vat_price, $additional_options_json, $additional_options_total,
    $img_folder, $thing_cate, $original_filename_json);

if (mysqli_stmt_execute($stmt)) {
    $basket_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);

    error_log("Inserted basket success - ID: $basket_id");

    safe_json_response(true, [
        'basket_id' => $basket_id,
        'uploaded_files_count' => count($uploaded_files),
        'upload_path' => $img_folder
    ], '장바구니에 추가되었습니다.');

} else {
    $error = mysqli_stmt_error($stmt);
    error_log("Inserted execute failed: " . $error);
    mysqli_stmt_close($stmt);
    safe_json_response(false, null, '장바구니 추가 실패: ' . $error);
}
?>
