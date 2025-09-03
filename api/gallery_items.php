<?php
/**
 * 통합 갤러리 API v1.0
 * 모든 제품의 갤러리 이미지를 통합 처리하는 API
 * 각 제품별 API를 호출하여 통합 응답 제공
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// UTF-8 설정 강화
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// 데이터베이스 연결
include "../db.php";

if (!$db) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}
mysqli_set_charset($db, "utf8mb4");

try {
    // URL 파라미터 처리
    $product = isset($_GET['product']) ? $_GET['product'] : 'inserted';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 12;
    
    // 제품별 API 매핑
    $productApiMap = [
        'inserted' => '/mlangprintauto/inserted/get_leaflet_images.php',
        'namecard' => '/mlangprintauto/namecard/get_portfolio_images.php',
        'littleprint' => '/mlangprintauto/littleprint/get_poster_images.php',
        'merchandisebond' => '/mlangprintauto/merchandisebond/get_merchandisebond_images.php',
        'envelope' => '/mlangprintauto/envelope/get_envelope_images.php',
        'cadarok' => '/mlangprintauto/cadarok/get_cadarok_images.php',
        'ncrflambeau' => '/mlangprintauto/ncrflambeau/get_ncrflambeau_images.php',
        'msticker' => '/mlangprintauto/msticker/get_msticker_images.php',
        'sticker' => '/mlangprintauto/sticker_new/get_sticker_images.php'
    ];
    
    // 제품별 API 경로 확인
    if (!isset($productApiMap[$product])) {
        throw new Exception("Unknown product type: $product");
    }
    
    $apiPath = $_SERVER['DOCUMENT_ROOT'] . $productApiMap[$product];
    
    // API 파일 존재 확인
    if (!file_exists($apiPath)) {
        throw new Exception("API file not found for product: $product at path: $apiPath");
    }
    
    // 내부 API 호출을 위한 URL 구성
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $apiUrl = $protocol . '://' . $host . $productApiMap[$product];
    $apiUrl .= '?mode=popup&page=' . $page . '&per_page=' . $perPage . '&all=true';
    
    // cURL을 사용하여 내부 API 호출
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Gallery-API-Internal/1.0');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Cache-Control: no-cache'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false || !empty($error)) {
        throw new Exception("Failed to fetch data from internal API: $error");
    }
    
    if ($httpCode !== 200) {
        throw new Exception("Internal API returned HTTP $httpCode for URL: $apiUrl");
    }
    
    // JSON 응답 파싱
    $apiData = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON response from internal API: " . json_last_error_msg());
    }
    
    // 응답 데이터 표준화
    if (isset($apiData['success']) && $apiData['success']) {
        // 이미지 데이터 추출 (다양한 키 지원)
        $images = [];
        if (isset($apiData['images']) && is_array($apiData['images'])) {
            $images = $apiData['images'];
        } elseif (isset($apiData['data']) && is_array($apiData['data'])) {
            $images = $apiData['data'];
        }
        
        // 통합 갤러리 형식으로 이미지 데이터 변환
        $standardizedImages = [];
        foreach ($images as $img) {
            $standardizedItem = [
                'src' => $img['path'] ?? $img['image_path'] ?? $img['url'] ?? '',
                'alt' => $img['title'] ?? $img['description'] ?? '샘플 이미지',
                'title' => $img['title'] ?? '샘플 이미지',
                'orderNo' => $img['order_no'] ?? null,
                'type' => $img['source'] ?? 'real_orders',
                'exists' => $img['file_exists'] ?? true,
                'category' => $img['category'] ?? $product
            ];
            
            if (!empty($standardizedItem['src'])) {
                $standardizedImages[] = $standardizedItem;
            }
        }
        
        // 페이지네이션 정보 추출
        $currentPage = $apiData['page'] ?? $page;
        $totalPages = $apiData['total_pages'] ?? 1;
        $totalItems = $apiData['total_items'] ?? count($standardizedImages);
        
        // 통합 응답 구성
        $unifiedResponse = [
            'success' => true,
            'product' => $product,
            'debug_info' => [
                'api_url' => $apiUrl,
                'original_count' => count($images),
                'standardized_count' => count($standardizedImages)
            ],
            'data' => [
                'items' => $standardizedImages,
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'perPage' => $perPage
            ],
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'per_page' => $perPage,
                'has_next' => $currentPage < $totalPages,
                'has_prev' => $currentPage > 1
            ]
        ];
        
        echo json_encode($unifiedResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    } else {
        throw new Exception("Internal API returned error: " . ($apiData['message'] ?? 'Unknown error'));
    }
    
} catch (Exception $e) {
    // 에러 응답
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage(),
        'product' => $product ?? 'unknown',
        'debug_info' => [
            'api_path_exists' => isset($apiPath) ? file_exists($apiPath) : false,
            'api_url' => isset($apiUrl) ? $apiUrl : null
        ],
        'data' => [
            'items' => [],
            'currentPage' => 1,
            'totalPages' => 1,
            'totalItems' => 0,
            'perPage' => 12
        ]
    ];
    
    http_response_code(500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} finally {
    // 데이터베이스 연결 종료
    if (isset($db) && $db) {
        mysqli_close($db);
    }
}
?>