<?php
/**
 * 모든 품목의 이미지 API를 ImgFolder 갤러리 경로로 통일하는 스크립트
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🖼️ 갤러리 이미지 API 통합 스크립트\n";
echo "===================================\n\n";

// 품목별 설정
$products = [
    'inserted' => ['name' => '전단지', 'folder' => 'inserted'],
    'namecard' => ['name' => '명함', 'folder' => 'namecard'],
    'littleprint' => ['name' => '포스터', 'folder' => 'littleprint'],
    'poster' => ['name' => '포스터', 'folder' => 'poster'],
    'envelope' => ['name' => '봉투', 'folder' => 'envelope'],
    'cadarok' => ['name' => '카다록', 'folder' => 'cadarok'],
    'merchandisebond' => ['name' => '상품권', 'folder' => 'merchandisebond'],
    'ncrflambeau' => ['name' => '전표', 'folder' => 'ncrflambeau'],
    'msticker' => ['name' => '자석스티커', 'folder' => 'msticker'],
    'sticker' => ['name' => '스티커', 'folder' => 'sticker'],
    'sticker_new' => ['name' => '스티커', 'folder' => 'sticker_new']
];

// 통합 갤러리 API 템플릿
$apiTemplate = '<?php
/**
 * %PRODUCT_NAME% 포트폴리오 이미지 API - ImgFolder 갤러리 통합 버전
 * ImgFolder/%FOLDER_NAME%/gallery/ 경로에서 이미지를 가져옴
 */

header(\'Content-Type: application/json; charset=utf-8\');
header(\'Cache-Control: no-cache, must-revalidate\');

mb_internal_encoding(\'UTF-8\');
mb_http_output(\'UTF-8\');

include "../../db.php";

if (!$db) {
    die(json_encode([\'success\' => false, \'message\' => \'Database connection failed\']));
}
mysqli_set_charset($db, "utf8mb4");

try {
    $category = isset($_GET[\'category\']) ? $_GET[\'category\'] : \'%CATEGORY%\';
    $showAll = isset($_GET[\'all\']) && $_GET[\'all\'] === \'true\';
    $page = isset($_GET[\'page\']) ? max(1, intval($_GET[\'page\'])) : 1;
    $perPage = isset($_GET[\'per_page\']) ? max(1, min(100, intval($_GET[\'per_page\']))) : ($showAll ? 12 : 4);
    $offset = ($page - 1) * $perPage;
    $mode = isset($_GET[\'mode\']) ? $_GET[\'mode\'] : \'thumbnail\';
    $limit = isset($_GET[\'limit\']) ? (int)$_GET[\'limit\'] : $perPage;
    
    // ImgFolder 갤러리 경로 (스티커와 동일한 구조)
    $galleryPath = $_SERVER[\'DOCUMENT_ROOT\'] . \'/ImgFolder/%FOLDER_NAME%/gallery/\';
    $webPath = \'/ImgFolder/%FOLDER_NAME%/gallery/\';
    
    $images = [];
    
    // 갤러리 폴더에서 이미지 파일 검색
    if (is_dir($galleryPath)) {
        $files = scandir($galleryPath);
        $imageFiles = [];
        
        foreach ($files as $file) {
            if ($file !== \'.\' && $file !== \'..\') {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, [\'jpg\', \'jpeg\', \'png\', \'gif\', \'webp\'])) {
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
                \'id\' => \'gallery_\' . ($offset + $index + 1),
                \'title\' => pathinfo($file, PATHINFO_FILENAME),
                \'filename\' => $file,
                \'path\' => $webPath . $file,
                \'image_path\' => $webPath . $file,
                \'thumbnail\' => $webPath . $file,
                \'thumbnail_path\' => $webPath . $file,
                \'thumb_path\' => $webPath . $file,
                \'url\' => $webPath . $file,
                \'thumb\' => $webPath . $file,
                \'category\' => \'%PRODUCT_NAME%\',
                \'type\' => \'%PRODUCT_NAME%\',
                \'type_name\' => \'%PRODUCT_NAME%\',
                \'order_no\' => null,
                \'source\' => \'gallery\',
                \'description\' => \'%PRODUCT_NAME% 갤러리 이미지\',
                \'date\' => filemtime($galleryPath . $file) ? date(\'Y-m-d\', filemtime($galleryPath . $file)) : \'\',
                \'file_exists\' => true,
                \'customer_masked\' => \'\',
                \'is_real_work\' => true,
                \'work_completed\' => true
            ];
        }
    }
    
    // 이미지가 없으면 기본 샘플 이미지 4개 제공
    if (empty($images)) {
        $totalCount = 4;
        $totalPages = 1;
        
        for ($i = 1; $i <= 4; $i++) {
            $images[] = [
                \'id\' => \'sample_\' . $i,
                \'title\' => \'%PRODUCT_NAME% 샘플 \' . $i,
                \'filename\' => \'sample_\' . $i . \'.jpg\',
                \'path\' => \'/images/samples/%CATEGORY%_sample_\' . $i . \'.jpg\',
                \'image_path\' => \'/images/samples/%CATEGORY%_sample_\' . $i . \'.jpg\',
                \'thumbnail\' => \'/images/samples/%CATEGORY%_sample_\' . $i . \'.jpg\',
                \'thumbnail_path\' => \'/images/samples/%CATEGORY%_sample_\' . $i . \'.jpg\',
                \'thumb_path\' => \'/images/samples/%CATEGORY%_sample_\' . $i . \'.jpg\',
                \'url\' => \'/images/samples/%CATEGORY%_sample_\' . $i . \'.jpg\',
                \'thumb\' => \'/images/samples/%CATEGORY%_sample_\' . $i . \'.jpg\',
                \'category\' => \'%PRODUCT_NAME%\',
                \'is_default\' => true
            ];
        }
    } else {
        $totalCount = count($imageFiles);
        $totalPages = ceil($totalCount / $perPage);
    }
    
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    $response = [
        \'success\' => true,
        \'category\' => $category,
        \'db_category\' => \'%PRODUCT_NAME%\',
        \'mode\' => $mode,
        \'page\' => $page,
        \'limit\' => $limit,
        \'total_items\' => $totalCount,
        \'total_pages\' => $totalPages,
        \'has_next\' => $hasNext,
        \'has_prev\' => $hasPrev,
        \'images\' => $images,
        \'data\' => $images,
        \'count\' => count($images),
        \'source\' => \'gallery\',
        \'version\' => \'3.0\',
        \'description\' => \'%PRODUCT_NAME% 갤러리 이미지\',
        \'gallery_path\' => $webPath,
        \'pagination\' => [
            \'current_page\' => $page,
            \'per_page\' => $perPage,
            \'total_count\' => $totalCount,
            \'total_pages\' => $totalPages,
            \'has_next\' => $hasNext,
            \'has_prev\' => $hasPrev,
            \'next_page\' => $hasNext ? $page + 1 : null,
            \'prev_page\' => $hasPrev ? $page - 1 : null
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    $response = [
        \'success\' => false,
        \'error\' => $e->getMessage(),
        \'message\' => $e->getMessage(),
        \'category\' => $category ?? \'%CATEGORY%\',
        \'images\' => [],
        \'data\' => [],
        \'source\' => \'gallery\',
        \'version\' => \'3.0\'
    ];
    
    http_response_code(500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} finally {
    if (isset($db) && $db) {
        mysqli_close($db);
    }
}
?>';

// API 파일 업데이트 매핑
$apiFiles = [
    'mlangprintauto/inserted/get_leaflet_images.php' => ['category' => 'inserted', 'name' => '전단지', 'folder' => 'inserted'],
    'mlangprintauto/namecard/get_portfolio_images.php' => ['category' => 'namecard', 'name' => '명함', 'folder' => 'namecard'],
    'mlangprintauto/namecard/get_namecard_images.php' => ['category' => 'namecard', 'name' => '명함', 'folder' => 'namecard'],
    'mlangprintauto/littleprint/get_poster_images.php' => ['category' => 'littleprint', 'name' => '포스터', 'folder' => 'littleprint'],
    'mlangprintauto/poster/get_poster_images.php' => ['category' => 'poster', 'name' => '포스터', 'folder' => 'poster'],
    'mlangprintauto/envelope/get_envelope_images.php' => ['category' => 'envelope', 'name' => '봉투', 'folder' => 'envelope'],
    'mlangprintauto/cadarok/get_cadarok_images.php' => ['category' => 'cadarok', 'name' => '카다록', 'folder' => 'cadarok'],
    'mlangprintauto/merchandisebond/get_merchandisebond_images.php' => ['category' => 'merchandisebond', 'name' => '상품권', 'folder' => 'merchandisebond'],
    'mlangprintauto/ncrflambeau/get_ncrflambeau_images.php' => ['category' => 'ncrflambeau', 'name' => '전표', 'folder' => 'ncrflambeau'],
    'mlangprintauto/msticker/get_msticker_images.php' => ['category' => 'msticker', 'name' => '자석스티커', 'folder' => 'msticker'],
    'mlangprintauto/sticker/get_namecard_images.php' => ['category' => 'sticker', 'name' => '스티커', 'folder' => 'sticker'],
    'mlangprintauto/sticker_new/get_sticker_images.php' => ['category' => 'sticker_new', 'name' => '스티커', 'folder' => 'sticker_new'],
    'mlangprintauto/sticker_new/get_namecard_images.php' => ['category' => 'sticker_new', 'name' => '스티커', 'folder' => 'sticker_new']
];

$updatedCount = 0;

foreach ($apiFiles as $filePath => $config) {
    $fullPath = __DIR__ . '/' . $filePath;
    
    // 템플릿에 값 치환
    $content = str_replace('%CATEGORY%', $config['category'], $apiTemplate);
    $content = str_replace('%PRODUCT_NAME%', $config['name'], $content);
    $content = str_replace('%FOLDER_NAME%', $config['folder'], $content);
    
    // 파일 쓰기
    if (file_put_contents($fullPath, $content)) {
        echo "✅ 업데이트 완료: {$filePath}\n";
        $updatedCount++;
    } else {
        echo "❌ 업데이트 실패: {$filePath}\n";
    }
}

echo "\n📊 업데이트 결과:\n";
echo "==================\n";
echo "총 {$updatedCount}개 API 파일 업데이트 완료\n\n";

echo "📁 생성된 갤러리 폴더 구조:\n";
echo "===========================\n";
echo "ImgFolder/\n";

foreach ($products as $key => $product) {
    $galleryPath = __DIR__ . '/ImgFolder/' . $product['folder'] . '/gallery/';
    $exists = is_dir($galleryPath);
    $status = $exists ? '✅' : '❌';
    echo "├── {$product['folder']}/\n";
    echo "│   └── gallery/ {$status}\n";
}

echo "\n🎯 다음 단계:\n";
echo "============\n";
echo "1. 각 폴더에 샘플 이미지 추가\n";
echo "   예시: ImgFolder/inserted/gallery/샘플전단지01.jpg\n";
echo "2. 웹호스팅에 ImgFolder 전체 업로드\n";
echo "3. 모든 품목에서 동일한 갤러리 구조로 이미지 표시됨\n";

?>