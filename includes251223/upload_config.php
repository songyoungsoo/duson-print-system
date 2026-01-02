<?php
/**
 * 통합 업로드 설정 파일
 * 모든 파일 업로드 경로를 중앙에서 관리
 */

// 기본 업로드 루트 디렉토리
// 절대 경로 사용으로 변경 (상대 경로 문제 해결)
define('UPLOAD_ROOT', __DIR__ . '/../uploads/');

// 업로드 디렉토리 구조
$UPLOAD_PATHS = [
    // 임시 파일 (모든 시스템 공용)
    'temp' => UPLOAD_ROOT . 'temp/',
    
    // 고객 주문 파일
    'orders' => UPLOAD_ROOT . 'orders/',
    
    // 관리자 파일
    'admin' => UPLOAD_ROOT . 'admin/',
    'admin_templates' => UPLOAD_ROOT . 'admin/templates/',
    'admin_samples' => UPLOAD_ROOT . 'admin/samples/',
    
    // 보관소
    'archive' => UPLOAD_ROOT . 'archive/',
];

// 제품별 관리자 경로
$PRODUCT_ADMIN_PATHS = [
    'namecard' => UPLOAD_ROOT . 'admin/namecard/',
    'inserted' => UPLOAD_ROOT . 'admin/inserted/',
    'cadarok' => UPLOAD_ROOT . 'admin/cadarok/',
    'msticker' => UPLOAD_ROOT . 'admin/msticker/',
    'envelope' => UPLOAD_ROOT . 'admin/envelope/',
    'merchandisebond' => UPLOAD_ROOT . 'admin/merchandisebond/',
    'ncrflambeau' => UPLOAD_ROOT . 'admin/ncrflambeau/',
    'littleprint' => UPLOAD_ROOT . 'admin/littleprint/',
];

// 임시 파일 경로 생성 함수
function getTempUploadPath($session_id) {
    global $UPLOAD_PATHS;
    return $UPLOAD_PATHS['temp'] . $session_id . '/';
}

// 주문 완료 파일 경로 생성 함수
function getOrderUploadPath($order_id) {
    global $UPLOAD_PATHS;
    return $UPLOAD_PATHS['orders'] . $order_id . '/';
}

// 관리자 제품별 경로 생성 함수
function getAdminProductPath($product_type) {
    global $PRODUCT_ADMIN_PATHS;
    return $PRODUCT_ADMIN_PATHS[$product_type] ?? $UPLOAD_PATHS['admin'] . $product_type . '/';
}

// 디렉토리 생성 함수
function createUploadDirectory($path) {
    // 이미 존재하면 성공
    if (file_exists($path)) {
        // 쓰기 권한 확인
        if (!is_writable($path)) {
            error_log("Directory exists but not writable: {$path}");
            @chmod($path, 0777); // 권한 수정 시도
        }
        return true;
    }

    // 디렉토리 생성 시도
    if (!@mkdir($path, 0755, true)) {
        $error = error_get_last();
        error_log("Failed to create directory: {$path}");
        error_log("Error: " . ($error['message'] ?? 'Unknown error'));
        error_log("Parent directory: " . dirname($path));
        error_log("Parent writable: " . (is_writable(dirname($path)) ? 'YES' : 'NO'));
        return false;
    }

    // 권한 설정
    @chmod($path, 0777);
    error_log("Successfully created directory: {$path}");
    return true;
}

// 파일 이동 함수 (임시 → 최종)
function moveFromTempToFinal($session_id, $order_id) {
    $temp_path = getTempUploadPath($session_id);
    $final_path = getOrderUploadPath($order_id);
    
    if (!createUploadDirectory($final_path)) {
        return false;
    }
    
    if (is_dir($temp_path)) {
        $files = scandir($temp_path);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $source = $temp_path . $file;
                $destination = $final_path . $file;
                if (!copy($source, $destination)) {
                    return false;
                }
            }
        }
        
        // 임시 폴더 정리
        array_map('unlink', glob($temp_path . '*'));
        rmdir($temp_path);
    }
    
    return true;
}

// 구버전 호환성을 위한 경로 매핑
$LEGACY_PATH_MAP = [
    // 구버전 → 새버전 경로 매핑
    '../mlangorder_printauto/upload/temp/' => UPLOAD_ROOT . 'temp/',
    '../mlangorder_printauto/upload/' => UPLOAD_ROOT . 'orders/',
    '../../mlangprintauto/namecard/upload' => UPLOAD_ROOT . 'admin/namecard/',
    '../../mlangprintauto/inserted/upload' => UPLOAD_ROOT . 'admin/inserted/',
];

// 구버전 경로를 새버전으로 변환
function convertLegacyPath($old_path) {
    global $LEGACY_PATH_MAP;
    foreach ($LEGACY_PATH_MAP as $old => $new) {
        if (strpos($old_path, $old) !== false) {
            return str_replace($old, $new, $old_path);
        }
    }
    return $old_path;
}

/**
 * 레거시 형식 업로드 경로 생성
 * Format: ImgFolder/_MlangPrintAuto_{product}_index.php/YYYY/MMDD/IP/timestamp/
 *
 * @param string $product_type 제품 타입 (inserted, namecard, sticker 등)
 * @return array ['img_folder' => 경로, 'physical_path' => 실제 파일시스템 경로]
 */
function generateLegacyUploadPath($product_type) {
    // URL 정보 가져오기
    $url_path = $_SERVER['REQUEST_URI'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $timestamp = time();

    // 날짜 정보
    $year = date('Y');
    $month_day = date('md'); // MMDD 형식

    // IP 주소 정리 (IPv6 localhost -> 127.0.0.1)
    if ($ip === '::1') {
        $ip = '127.0.0.1';
    }

    // 경로 구조: ImgFolder/_MlangPrintAuto_{product}_index.php/YYYY/MMDD/IP/timestamp/
    $folder_name = "_MlangPrintAuto_{$product_type}_index.php";
    $img_folder = "ImgFolder/{$folder_name}/{$year}/{$month_day}/{$ip}/{$timestamp}/";

    // 실제 파일시스템 경로 (루트 기준)
    $physical_path = __DIR__ . '/../' . $img_folder;

    return [
        'img_folder' => $img_folder,
        'physical_path' => $physical_path
    ];
}

/**
 * 레거시 업로드 디렉토리 생성
 *
 * @param string $physical_path 실제 파일시스템 경로
 * @return bool 성공 여부
 */
function createLegacyUploadDirectory($physical_path) {
    if (!file_exists($physical_path)) {
        if (!mkdir($physical_path, 0755, true)) {
            error_log("Failed to create directory: {$physical_path}");
            return false;
        }
        chmod($physical_path, 0777);
    }
    return true;
}
?>