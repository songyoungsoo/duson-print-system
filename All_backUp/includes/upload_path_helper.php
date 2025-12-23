<?php
/**
 * 업로드 경로 헬퍼 - 품목별 업로드 경로 통일
 * 구버전(dsp1830.shop) 호환을 위한 경로 설정
 * 
 * @param string $product_type 품목 타입 (leaflet, namecard, envelope, sticker, cadarok, merchandisebond 등)
 * @param string $session_id 세션 ID
 * @param int|null $order_no 주문번호 (주문 확정 시)
 * @return array ['temp_path' => 임시경로, 'final_path' => 최종경로, 'db_path' => DB저장경로]
 */
function getUploadPath($product_type, $session_id, $order_no = null) {
    $base_dir = $_SERVER['DOCUMENT_ROOT'];
    
    // 구버전 호환: 모든 품목을 mlangorder_printauto/upload/ 경로로 통일
    $upload_base = '/mlangorder_printauto/upload/';
    
    if ($order_no) {
        // 주문 확정 후: 주문번호 폴더
        $folder_name = $order_no . '/';
        $full_path = $base_dir . $upload_base . $folder_name;
        $db_path = 'mlangorder_printauto/upload/' . $folder_name;
        
        return [
            'full_path' => $full_path,
            'db_path' => $db_path
        ];
    } else {
        // 장바구니 단계: 임시 폴더
        $temp_folder_name = 'temp_' . $session_id . '_' . time() . '/';
        $full_path = $base_dir . $upload_base . $temp_folder_name;
        $db_path = 'mlangorder_printauto/upload/' . $temp_folder_name;
        
        return [
            'full_path' => $full_path,
            'db_path' => $db_path
        ];
    }
}

/**
 * 임시 폴더를 주문번호 폴더로 변경
 * 
 * @param string $temp_folder 임시 폴더 전체 경로
 * @param int $order_no 주문번호
 * @return array ['success' => bool, 'new_path' => 새경로, 'db_path' => DB저장경로]
 */
function moveToOrderFolder($temp_folder, $order_no) {
    $base_dir = $_SERVER['DOCUMENT_ROOT'];
    $upload_base = '/mlangorder_printauto/upload/';
    
    $new_folder = $base_dir . $upload_base . $order_no . '/';
    $db_path = 'mlangorder_printauto/upload/' . $order_no . '/';
    
    if (!is_dir($temp_folder)) {
        return [
            'success' => false,
            'error' => '임시 폴더를 찾을 수 없습니다: ' . $temp_folder
        ];
    }
    
    if (rename($temp_folder, $new_folder)) {
        return [
            'success' => true,
            'new_path' => $new_folder,
            'db_path' => $db_path
        ];
    } else {
        return [
            'success' => false,
            'error' => '폴더 이동 실패: ' . $temp_folder . ' -> ' . $new_folder
        ];
    }
}

/**
 * 폴더의 첫 번째 파일명 가져오기 (ThingCate용)
 * 
 * @param string $folder_path 폴더 경로
 * @return string|null 첫 번째 파일명 또는 null
 */
function getFirstFileName($folder_path) {
    if (!is_dir($folder_path)) {
        return null;
    }
    
    $files = array_diff(scandir($folder_path), ['.', '..']);
    
    foreach ($files as $file) {
        if (is_file($folder_path . '/' . $file)) {
            return $file;
        }
    }
    
    return null;
}
?>
