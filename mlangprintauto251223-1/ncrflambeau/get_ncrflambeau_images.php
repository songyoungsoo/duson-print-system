<?php
/**
 * 전표 포트폴리오 이미지 API - ImgFolder 갤러리 통합 버전
 * ImgFolder/ncrflambeau/gallery/ 경로에서 이미지를 가져옴
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
    $category = isset($_GET['category']) ? $_GET['category'] : 'ncrflambeau';
    $showAll = isset($_GET['all']) && $_GET['all'] === 'true';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : ($showAll ? 12 : 4);
    $offset = ($page - 1) * $perPage;
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'thumbnail';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : $perPage;
    
    // ImgFolder 갤러리 경로 (스티커와 동일한 구조)
    $galleryPath = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/ncrflambeau/gallery/';
    $webPath = '/ImgFolder/ncrflambeau/gallery/';
    
    $images = [];
    
    // 갤러리 폴더에서 이미지 파일 검색
    if (is_dir($galleryPath)) {
        $files = scandir($galleryPath);
        $imageFiles = [];
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $imageFiles[] = $file;
                }
            }
        }
        
        // 파일명 기준 정렬
        sort($imageFiles);
        
        // 페이지네이션 적용
        $totalCount = count($imageFiles);
        $totalPages = ceil($totalCount / $perPage);
        $pagedFiles = array_slice($imageFiles, $offset, $perPage);
        
        foreach ($pagedFiles as $index => $file) {
            $images[] = [
                'id' => 'gallery_' . ($offset + $index + 1),
                'title' => pathinfo($file, PATHINFO_FILENAME),
                'filename' => $file,
                'path' => $webPath . $file,
                'image_path' => $webPath . $file,
                'thumbnail' => $webPath . $file,
                'thumbnail_path' => $webPath . $file,
                'thumb_path' => $webPath . $file,
                'url' => $webPath . $file,
                'thumb' => $webPath . $file,
                'category' => '전표',
                'type' => '전표',
                'type_name' => '전표',
                'order_no' => null,
                'source' => 'gallery',
                'description' => '전표 갤러리 이미지',
                'date' => filemtime($galleryPath . $file) ? date('Y-m-d', filemtime($galleryPath . $file)) : '',
                'file_exists' => true,
                'customer_masked' => '',
                'is_real_work' => true,
                'work_completed' => true
            ];
        }
    }
    
    // 이미지가 없으면 기본 샘플 이미지 4개 제공
    if (empty($images)) {
        $totalCount = 4;
        $totalPages = 1;
        
        for ($i = 1; $i <= 4; $i++) {
            $images[] = [
                'id' => 'sample_' . $i,
                'title' => '전표 샘플 ' . $i,
                'filename' => 'sample_' . $i . '.jpg',
                'path' => '/images/samples/ncrflambeau_sample_' . $i . '.jpg',
                'image_path' => '/images/samples/ncrflambeau_sample_' . $i . '.jpg',
                'thumbnail' => '/images/samples/ncrflambeau_sample_' . $i . '.jpg',
                'thumbnail_path' => '/images/samples/ncrflambeau_sample_' . $i . '.jpg',
                'thumb_path' => '/images/samples/ncrflambeau_sample_' . $i . '.jpg',
                'url' => '/images/samples/ncrflambeau_sample_' . $i . '.jpg',
                'thumb' => '/images/samples/ncrflambeau_sample_' . $i . '.jpg',
                'category' => '전표',
                'is_default' => true
            ];
        }
    } else {
        $totalCount = count($imageFiles);
        $totalPages = ceil($totalCount / $perPage);
    }
    
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    $response = [
        'success' => true,
        'category' => $category,
        'db_category' => '전표',
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
        'description' => '전표 갤러리 이미지',
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
        'category' => $category ?? 'ncrflambeau',
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