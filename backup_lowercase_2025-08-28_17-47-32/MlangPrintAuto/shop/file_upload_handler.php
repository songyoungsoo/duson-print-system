<?php
/**
 * 통합 파일 업로드 처리기
 * 경로: mlangprintauto/shop/file_upload_handler.php
 * 
 * AJAX 파일 업로드를 처리하고 shop_temp 테이블과 연동
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";
include "file_management_helper.php";

// 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');

try {
    // 데이터베이스 연결 확인
    check_db_connection($db);
    
    // POST 데이터 검증
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_response('POST 요청만 허용됩니다.');
    }
    
    // 필수 파라미터 확인
    $cart_item_no = $_POST['cart_item_no'] ?? '';
    $product_type = $_POST['product_type'] ?? '';
    
    if (empty($cart_item_no)) {
        error_response('장바구니 아이템 번호가 필요합니다.');
    }
    
    // 파일 업로드 확인
    if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
        error_response('업로드할 파일이 없습니다.');
    }
    
    // 로그 정보 생성
    $log_info = generateFileLogInfo($product_type);
    
    // 업로드 디렉토리 생성
    $upload_dir = createFileUploadDirectory($log_info);
    
    // 업로드된 파일들 처리
    $uploaded_files = [];
    $files = $_FILES['files'];
    $file_count = count($files['name']);
    
    for ($i = 0; $i < $file_count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        
        $original_name = $files['name'][$i];
        $tmp_name = $files['tmp_name'][$i];
        $file_size = $files['size'][$i];
        $file_type = $files['type'][$i];
        
        // 파일 확장자 검증
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'ai', 'psd', 'doc', 'docx'];
        
        if (!in_array($file_ext, $allowed_extensions)) {
            continue; // 허용되지 않은 확장자는 건너뛰기
        }
        
        // 파일 크기 검증 (50MB 제한)
        if ($file_size > 50 * 1024 * 1024) {
            continue; // 크기 초과 파일은 건너뛰기
        }
        
        // 안전한 파일명 생성
        $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
        $saved_name = date('His') . '_' . $safe_name;
        
        // 중복 파일명 처리
        $counter = 1;
        while (file_exists("$upload_dir/$saved_name")) {
            $name_parts = pathinfo($safe_name);
            $saved_name = date('His') . '_' . $name_parts['filename'] . "_{$counter}." . $name_parts['extension'];
            $counter++;
        }
        
        $upload_path = "$upload_dir/$saved_name";
        
        // 파일 이동
        if (move_uploaded_file($tmp_name, $upload_path)) {
            $file_info = [
                'original_name' => $original_name,
                'saved_name' => $saved_name,
                'file_size' => $file_size,
                'file_type' => $file_type,
                'upload_path' => $upload_path
            ];
            
            // 장바구니 아이템에 파일 정보 추가
            if (addFileToCartItem($db, $cart_item_no, $file_info, $log_info)) {
                $uploaded_files[] = [
                    'original_name' => $original_name,
                    'saved_name' => $saved_name,
                    'file_size' => format_file_size($file_size),
                    'file_type' => $file_type,
                    'upload_time' => date('Y-m-d H:i:s')
                ];
            }
        }
    }
    
    if (empty($uploaded_files)) {
        error_response('파일 업로드에 실패했습니다.');
    }
    
    success_response([
        'uploaded_files' => $uploaded_files,
        'total_count' => count($uploaded_files),
        'cart_item_no' => $cart_item_no
    ], '파일이 성공적으로 업로드되었습니다.');
    
} catch (Exception $e) {
    error_response('파일 업로드 중 오류가 발생했습니다: ' . $e->getMessage());
}

// 파일 크기 포맷팅 함수
function format_file_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>