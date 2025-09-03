<?php
/**
 * 장바구니 아이템의 파일 목록 조회
 * 경로: mlangprintauto/shop/get_cart_files.php
 * 
 * 특정 장바구니 아이템의 파일 목록을 반환하는 AJAX 처리기
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
    
    // GET 파라미터 확인
    $cart_item_no = $_GET['cart_item_no'] ?? '';
    
    if (empty($cart_item_no)) {
        error_response('장바구니 아이템 번호가 필요합니다.');
    }
    
    // 세션 검증 (보안)
    $session_id = session_id();
    $verify_query = "SELECT no FROM shop_temp WHERE no = ? AND session_id = ?";
    $stmt = mysqli_prepare($db, $verify_query);
    
    if (!$stmt) {
        error_response('데이터베이스 오류가 발생했습니다.');
    }
    
    mysqli_stmt_bind_param($stmt, 'is', $cart_item_no, $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        error_response('해당 장바구니 아이템을 찾을 수 없습니다.');
    }
    
    mysqli_stmt_close($stmt);
    
    // 파일 목록 조회
    $files = getCartItemFiles($db, $cart_item_no);
    
    // 파일 정보 포맷팅
    $formatted_files = [];
    foreach ($files as $file) {
        $formatted_files[] = [
            'original_name' => $file['original_name'],
            'saved_name' => $file['saved_name'],
            'file_size' => format_file_size($file['file_size']),
            'file_type' => $file['file_type'],
            'upload_time' => date('Y-m-d H:i:s', $file['upload_time']),
            'file_icon' => getFileIcon($file['original_name'])
        ];
    }
    
    success_response([
        'files' => $formatted_files,
        'total_count' => count($formatted_files),
        'cart_item_no' => $cart_item_no
    ]);
    
} catch (Exception $e) {
    error_response('파일 목록 조회 중 오류가 발생했습니다: ' . $e->getMessage());
}

/**
 * 파일 크기 포맷팅
 */
function format_file_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * 파일 아이콘 반환
 */
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icon_map = [
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️',
        'pdf' => '📄', 'ai' => '🎨', 'psd' => '🎨',
        'doc' => '📝', 'docx' => '📝'
    ];
    return $icon_map[$ext] ?? '📎';
}
?>