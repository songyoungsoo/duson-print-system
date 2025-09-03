<?php
/**
 * 스티커 갤러리 이미지 API (통합 버전)
 * 메인갤러리와 동일한 데이터베이스 소스 사용
 * Created: 2025년 8월 (AI Assistant - 통합버전)
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// UTF-8 설정 강화
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// 데이터베이스 연결
include "../../db.php";

if (!$db) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}
mysqli_set_charset($db, "utf8mb4");

try {
    // URL 파라미터 처리 및 디버깅 로그
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'popup';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 12;
    $showAll = isset($_GET['all']) && $_GET['all'] === 'true';
    
    // 디버깅: 요청 파라미터 로그
    error_log("Sticker API called with: " . http_build_query($_GET));
    
    // 메인갤러리와 동일한 로직 사용
    $items = [];
    
    // 스티커 타입 매핑 (메인갤러리와 동일)
    $sticker_types = ['스티커', 'sticker', '일반스티커'];
    $typeConditions = array_map(function($type) {
        return "Type LIKE '%" . $type . "%'";
    }, $sticker_types);
    $typeWhere = "(" . implode(" OR ", $typeConditions) . ")";
    
    // 파일이 없는 경우를 고려해서 10배 많이 가져옴 (15% 존재율 고려)
    $fetchLimit = $perPage * 10;
    
    $query = "SELECT no, ThingCate, ImgFolder, Type, name 
              FROM mlangorder_printauto 
              WHERE $typeWhere 
              AND ThingCate IS NOT NULL 
              AND ThingCate != ''
              AND LENGTH(ThingCate) > 3
              AND date >= '2020-01-01'
              ORDER BY date DESC, no DESC
              LIMIT ?";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $fetchLimit);
    
    if ($stmt && mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            // A경로 패턴: /MlangOrder_PrintAuto/upload/{주문번호}/{파일명}
            $imagePath = "/MlangOrder_PrintAuto/upload/{$row['no']}/{$row['ThingCate']}";
            
            // 실제 파일 존재 확인
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
            $fileExists = file_exists($fullPath);
            
            // 파일이 실제로 존재하는 것만 추가
            if ($fileExists) {
                // 고객명 마스킹
                $maskedName = !empty($row['name']) ? mb_substr($row['name'], 0, 1) . '***' : '고객';
                
                $items[] = [
                    'path' => $imagePath,
                    'image_path' => $imagePath,
                    'url' => $imagePath,
                    'src' => $imagePath,
                    'title' => "{$maskedName}님 스티커 주문",
                    'description' => "{$maskedName}님 스티커 주문 샘플",
                    'alt' => "{$maskedName}님 스티커 주문 샘플",
                    'order_no' => $row['no'],
                    'source' => 'real_orders',
                    'file_exists' => true,
                    'category' => 'sticker'
                ];
            }
            
            // 요청한 개수만큼 모이면 중단
            if (count($items) >= $perPage) {
                break;
            }
        }
        mysqli_stmt_close($stmt);
        
        // 실제 주문 데이터 제한 (포트폴리오 공간 확보)
        if (count($items) > 10) {
            $items = array_slice($items, 0, 10);
        }
    }
    
    // B경로: 스티커 전용 샘플 이미지 (항상 추가)
    $shouldAddCustomImages = true; // 항상 126개 이미지 추가
    
    if ($shouldAddCustomImages) {
        // 절대 경로 사용 (DOCUMENT_ROOT 문제 해결)
        $possiblePaths = [
            $_SERVER['DOCUMENT_ROOT'] . "/ImgFolder/sticker/gallery/",
            "C:\\xampp\\htdocs\\ImgFolder\\sticker\\gallery\\",
            realpath(__DIR__ . "/../../ImgFolder/sticker/gallery/")
        ];
        
        $sticker_gallery_dir = null;
        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                $sticker_gallery_dir = $path;
                break;
            }
        }
        
        if ($sticker_gallery_dir && is_dir($sticker_gallery_dir)) {
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $sticker_files = [];
            
            foreach ($image_extensions as $ext) {
                $pattern = $sticker_gallery_dir . "*." . $ext;
                $files = glob($pattern, GLOB_NOSORT);
                if ($files) {
                    $sticker_files = array_merge($sticker_files, $files);
                }
            }
            
            if (!empty($sticker_files)) {
                // 최신 파일부터 정렬 (날짜순)
                usort($sticker_files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                if ($mode === 'popup') {
                    // 팝업모드에서는 모든 커스텀 이미지 표시 (126개)
                    $needed = count($sticker_files); // 모든 파일
                } else {
                    // 메인갤러리에서는 부족한 만큼 추가
                    $needed = $perPage - count($items);
                }
                
                foreach (array_slice($sticker_files, 0, $needed) as $file) {
                    $filename = basename($file);
                    $web_path = "/ImgFolder/sticker/gallery/" . $filename;
                    
                    // 파일명에서 제품명 추출 (간단한 정리)
                    $clean_title = str_replace(['_', '-'], ' ', pathinfo($filename, PATHINFO_FILENAME));
                    if (mb_strlen($clean_title) > 30) {
                        $clean_title = mb_substr($clean_title, 0, 30) . '...';
                    }
                    
                    $items[] = [
                        'path' => $web_path,
                        'image_path' => $web_path,
                        'url' => $web_path,
                        'src' => $web_path,
                        'title' => $clean_title ?: '스티커 샘플',
                        'description' => '스티커 샘플 이미지',
                        'alt' => $clean_title ?: '스티커 샘플',
                        'order_no' => null,
                        'source' => 'sticker_gallery',
                        'file_exists' => true,
                        'category' => 'sticker'
                    ];
                }
            }
        }
    }
    
    // C경로: 포트폴리오 게시판에서 스티커 이미지 가져오기 (항상 추가)
    if (true) { // 항상 포트폴리오 이미지 추가
        $portfolioQuery = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_file, Mlang_date 
                          FROM mlang_portfolio_bbs 
                          WHERE CATEGORY LIKE '%스티커%' 
                          AND Mlang_bbs_file IS NOT NULL 
                          AND Mlang_bbs_file != '' 
                          AND Mlang_bbs_file NOT LIKE '%.gif'
                          ORDER BY Mlang_bbs_no DESC 
                          LIMIT 50";
        
        $portfolioResult = mysqli_query($db, $portfolioQuery);
        if ($portfolioResult) {
            while ($row = mysqli_fetch_assoc($portfolioResult)) {
                // 다양한 포트폴리오 경로 시도
                $possiblePortfolioPaths = [
                    "/bbs/upload/portfolio/" . $row['Mlang_bbs_file'],
                    "/bbs/data/portfolio/" . $row['Mlang_bbs_file'],
                    "/bbs/upload/" . $row['Mlang_bbs_file']
                ];
                
                foreach ($possiblePortfolioPaths as $portfolioPath) {
                    $serverPath = $_SERVER['DOCUMENT_ROOT'] . $portfolioPath;
                    
                    // 파일 존재 확인
                    if (file_exists($serverPath)) {
                        $items[] = [
                            'path' => $portfolioPath,
                            'image_path' => $portfolioPath,
                            'url' => $portfolioPath,
                            'src' => $portfolioPath,
                            'title' => $row['Mlang_bbs_title'] ?: '포트폴리오 스티커',
                            'description' => '포트폴리오 스티커 샘플',
                            'alt' => $row['Mlang_bbs_title'] ?: '포트폴리오 스티커',
                            'order_no' => $row['Mlang_bbs_no'],
                            'source' => 'portfolio',
                            'file_exists' => true,
                            'category' => 'sticker'
                        ];
                        break; // 첫 번째로 찾은 경로 사용
                    }
                }
            }
        }
    }
    
    // 플레이스홀더 (최후 수단)
    if (empty($items)) {
        for ($i = 1; $i <= 4; $i++) {
            $items[] = [
                'path' => "/images/placeholder-sticker.jpg",
                'image_path' => "/images/placeholder-sticker.jpg", 
                'url' => "/images/placeholder-sticker.jpg",
                'src' => "/images/placeholder-sticker.jpg",
                'title' => "스티커 샘플 $i",
                'description' => "스티커 샘플 이미지 $i",
                'alt' => "스티커 샘플 $i",
                'order_no' => null,
                'source' => 'placeholder',
                'file_exists' => false,
                'category' => 'sticker'
            ];
        }
    }
    
    // 페이지네이션 적용
    $totalItems = count($items);
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = min($page, max(1, $totalPages));
    
    // 현재 페이지에 해당하는 이미지만 추출
    $offset = ($currentPage - 1) * $perPage;
    $paginatedItems = array_slice($items, $offset, $perPage);
    
    // API 응답 (다양한 키 형식 지원)
    $response = [
        'success' => true,
        'images' => $paginatedItems,        // 기존 API 호환
        'data' => $paginatedItems,          // 통합 API 호환  
        'items' => $paginatedItems,         // 새로운 API 호환
        'page' => $currentPage,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'product' => 'sticker',
        'source' => 'database',
        'debug_info' => [
            'mode' => $mode,
            'fetch_limit' => $fetchLimit,
            'total_count' => count($items),
            'paginated_count' => count($paginatedItems),
            'current_page' => $currentPage,
            'offset' => $offset,
            'all_param' => $showAll
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
        'items' => []
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