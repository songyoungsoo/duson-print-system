<?php
/**
 * 스티커 포트폴리오 이미지 API - ImgFolder 갤러리 통합 버전
 * ImgFolder/sticker_new/gallery/ 경로에서 이미지를 가져옴
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

include "../../db.php";

if (!$db) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}
mysqli_set_charset($db, "utf8mb4");

try {
    $category = isset($_GET['category']) ? $_GET['category'] : 'sticker_new';
    $showAll = isset($_GET['all']) && $_GET['all'] === 'true';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : ($showAll ? 12 : 4);
    $offset = ($page - 1) * $perPage;
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'thumbnail';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : $perPage;
    
    // sample + samplegallery 합쳐서 filemtime 최신순
    $sampleDir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/sample/sticker_new/';
    $safeDir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/samplegallery/sticker_new/';
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    $allFiles = []; // [absPath => webPath]
    
    foreach ([$sampleDir => '/ImgFolder/sample/sticker_new/', $safeDir => '/ImgFolder/samplegallery/sticker_new/'] as $dir => $webPrefix) {
        if (!is_dir($dir)) continue;
        $found = glob($dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if ($found) {
            foreach ($found as $f) {
                $allFiles[$f] = $webPrefix . basename($f);
            }
        }
    }
    
    // filemtime 최신순 정렬
    uksort($allFiles, function($a, $b) { return filemtime($b) - filemtime($a); });
    
    $totalCount = count($allFiles);
    $totalPages = ceil($totalCount / $perPage);
    $pagedFiles = array_slice($allFiles, $offset, $perPage, true);
    
    $images = [];
    $idx = $offset;
    foreach ($pagedFiles as $absPath => $webPath) {
        $file = basename($absPath);
        $images[] = [
            'id' => 'gallery_' . ($idx + 1),
            'title' => pathinfo($file, PATHINFO_FILENAME),
            'filename' => $file,
            'path' => $webPath,
            'image_path' => $webPath,
            'thumbnail' => $webPath,
            'thumbnail_path' => $webPath,
            'thumb_path' => $webPath,
            'url' => $webPath,
            'thumb' => $webPath,
            'category' => '스티커',
            'type' => '스티커',
            'type_name' => '스티커',
            'order_no' => null,
            'source' => 'gallery',
            'description' => '스티커 갤러리 이미지',
            'date' => date('Y-m-d', filemtime($absPath)),
            'file_exists' => true,
            'customer_masked' => '',
            'is_real_work' => true,
            'work_completed' => true
        ];
        $idx++;
    }
    
    // 이미지가 없으면 기본 샘플 이미지 4개 제공
    if (empty($images)) {
        $totalCount = 4;
        $totalPages = 1;
        
        for ($i = 1; $i <= 4; $i++) {
            $images[] = [
                'id' => 'sample_' . $i,
                'title' => '스티커 샘플 ' . $i,
                'filename' => 'sample_' . $i . '.jpg',
                'path' => '/images/samples/sticker_new_sample_' . $i . '.jpg',
                'image_path' => '/images/samples/sticker_new_sample_' . $i . '.jpg',
                'thumbnail' => '/images/samples/sticker_new_sample_' . $i . '.jpg',
                'thumbnail_path' => '/images/samples/sticker_new_sample_' . $i . '.jpg',
                'thumb_path' => '/images/samples/sticker_new_sample_' . $i . '.jpg',
                'url' => '/images/samples/sticker_new_sample_' . $i . '.jpg',
                'thumb' => '/images/samples/sticker_new_sample_' . $i . '.jpg',
                'category' => '스티커',
                'is_default' => true
            ];
        }
    }
    
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    $response = [
        'success' => true,
        'category' => $category,
        'db_category' => '스티커',
        'mode' => $mode,
        'page' => $page,
        'limit' => $limit,
        'total_items' => $totalCount,
        'total_pages' => $totalPages,
        'has_next' => $hasNext,
        'has_prev' => $hasPrev,
        'images' => $images,
        'data' => $images,
        'count' => count($images),
        'source' => 'gallery',
        'version' => '3.0',
        'description' => '스티커 갤러리 이미지',
        'gallery_path' => $webPath,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_count' => $totalCount,
            'total_pages' => $totalPages,
            'has_next' => $hasNext,
            'has_prev' => $hasPrev,
            'next_page' => $hasNext ? $page + 1 : null,
            'prev_page' => $hasPrev ? $page - 1 : null
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage(),
        'category' => $category ?? 'sticker_new',
        'images' => [],
        'data' => [],
        'source' => 'gallery',
        'version' => '3.0'
    ];
    
    http_response_code(500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} finally {
    if (isset($db) && $db) {
        mysqli_close($db);
    }
}
?>