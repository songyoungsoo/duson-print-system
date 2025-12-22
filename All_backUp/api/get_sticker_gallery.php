<?php
/**
 * 스티커 통합 갤러리 API
 * gallery_data_adapter.php를 사용하여 통합 갤러리 시스템 호출
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// UTF-8 설정 강화
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

try {
    // URL 파라미터 처리
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 12;
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'popup';
    
    // 데이터베이스 연결
    include "../db.php";
    if (!$db) {
        throw new Exception('데이터베이스 연결 실패');
    }
    mysqli_set_charset($db, "utf8mb4");
    
    // 통합 갤러리 시스템 사용
    include_once "../includes/gallery_data_adapter.php";
    
    // 스티커 갤러리 데이터 가져오기
    $images = load_gallery_items('sticker', $perPage);
    
    // 통합 API 형식으로 변환
    $standardizedImages = [];
    foreach ($images as $img) {
        // src 키가 있는지 확인
        $srcPath = $img['src'] ?? '';
        if (empty($srcPath)) {
            continue; // src가 없으면 건너뛰기
        }
        
        $standardizedItem = [
            'src' => $srcPath,
            'alt' => $img['alt'] ?? '스티커 샘플',
            'title' => $img['alt'] ?? '스티커 샘플',
            'orderNo' => null,
            'type' => $img['type'] ?? 'static_files',
            'exists' => true,
            'category' => 'sticker'
        ];
        
        $standardizedImages[] = $standardizedItem;
    }
    
    // 페이지네이션 정보
    $totalItems = count($standardizedImages);
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = min($page, max(1, $totalPages));
    
    // 통합 응답 구성
    $response = [
        'success' => true,
        'images' => $standardizedImages,
        'data' => $standardizedImages,
        'page' => $currentPage,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'product' => 'sticker',
        'source' => 'unified_gallery',
        'debug_info' => [
            'mode' => $mode,
            'original_count' => count($images),
            'standardized_count' => count($standardizedImages)
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // 에러 응답
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage(),
        'images' => [],
        'data' => [],
        'page' => 1,
        'total_pages' => 1,
        'total_items' => 0
    ];
    
    http_response_code(500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>