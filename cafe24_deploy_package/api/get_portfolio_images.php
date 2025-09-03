<?php
/**
 * 포트폴리오 이미지 API
 * 카테고리별 이미지 조회 및 페이지네이션 지원
 * Created: 2025년 12월
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

include "../db.php";

try {
    // 파라미터 받기
    $category = $_GET['category'] ?? $_GET['product'] ?? 'envelope';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? $_GET['per_page'] ?? 24))); // 최대 100개 제한
    $offset = ($page - 1) * $limit;
    
    // 카테고리 매핑 (영어 -> 한글)
    $categoryMap = [
        'envelope' => '봉투',
        'namecard' => '명함',
        'sticker' => '스티커',
        'littleprint' => '포스터',
        'cadarok' => '카탈로그',
        'merchandisebond' => '상품권',
        'msticker' => '자석스티커',
        'ncrflambeau' => '서식/양식/상장',
        'inserted' => '전단지'
    ];
    
    $categoryName = $categoryMap[$category] ?? $category;
    
    // 스티커의 경우 통합갤러리 시스템 사용
    if ($category === 'sticker') {
        include_once "../includes/gallery_data_adapter.php";
        
        $images = load_gallery_items('sticker', null, 4, $limit);
        
        // 페이지네이션 적용
        $totalItems = count($images);
        $totalPages = ceil($totalItems / $limit);
        $currentPage = min($page, max(1, $totalPages));
        $paginatedOffset = ($currentPage - 1) * $limit;
        $paginatedImages = array_slice($images, $paginatedOffset, $limit);
        
        // 응답 형식 변환
        $response = [
            'success' => true,
            'category' => $category,
            'categoryName' => $categoryName,
            'images' => array_map(function($img) {
                return [
                    'id' => null,
                    'title' => $img['alt'] ?? '스티커 샘플',
                    'thumb' => $img['src'],
                    'full' => $img['src'],
                    'date' => date('Y-m-d'),
                    'description' => '',
                    'category' => 'sticker'
                ];
            }, $paginatedImages),
            'pagination' => [
                'current' => $currentPage,
                'total' => $totalItems,
                'pages' => $totalPages,
                'perPage' => $limit,
                'hasNext' => $currentPage < $totalPages,
                'hasPrev' => $currentPage > 1
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return;
    }
    
    // 데이터베이스 연결 확인
    if (!$db) {
        throw new Exception('데이터베이스 연결 실패');
    }
    
    mysqli_set_charset($db, "utf8");
    
    // 이미지 조회 쿼리 (실제 컬럼명 사용 - CATEGORY 필드 사용)
    $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_file, Mlang_bbs_connent, Mlang_date, CATEGORY 
              FROM Mlang_portfolio_bbs 
              WHERE CATEGORY = ? AND Mlang_bbs_file IS NOT NULL AND Mlang_bbs_file != ''
              ORDER BY Mlang_bbs_no DESC 
              LIMIT ? OFFSET ?";
    
    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        throw new Exception('쿼리 준비 실패: ' . mysqli_error($db));
    }
    
    mysqli_stmt_bind_param($stmt, "sii", $categoryName, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // 이미지 파일명 
        $fileName = $row['Mlang_bbs_file'];
        
        // 이미지 경로 처리 (실제 포트폴리오 경로)
        $possiblePaths = [
            '/bbs/upload/portfolio/' . $fileName,
            '/uploads/portfolio/' . $fileName,
            '/uploads/' . $fileName,
            '/bbs/data/portfolio/' . $fileName
        ];
        
        $thumbPath = '/images/no-image.jpg'; // 기본값
        $fullPath = '/images/no-image.jpg';
        
        // 실제 파일이 있는 경로 찾기
        foreach ($possiblePaths as $path) {
            $serverPath = $_SERVER['DOCUMENT_ROOT'] . $path;
            if (file_exists($serverPath)) {
                $thumbPath = $path;
                $fullPath = $path;
                break;
            }
        }
        
        $images[] = [
            'id' => (int)$row['Mlang_bbs_no'],
            'title' => trim($row['Mlang_bbs_title']) ?: '샘플 ' . $row['Mlang_bbs_no'],
            'thumb' => $thumbPath,
            'full' => $fullPath,
            'date' => $row['Mlang_date'],
            'description' => trim($row['Mlang_bbs_connent']) ?: '',
            'category' => $row['CATEGORY']
        ];
    }
    
    mysqli_stmt_close($stmt);
    
    // 전체 개수 조회
    $countQuery = "SELECT COUNT(*) as total FROM Mlang_portfolio_bbs WHERE CATEGORY = ? AND Mlang_bbs_file IS NOT NULL AND Mlang_bbs_file != ''";
    $countStmt = mysqli_prepare($db, $countQuery);
    
    if (!$countStmt) {
        throw new Exception('카운트 쿼리 준비 실패: ' . mysqli_error($db));
    }
    
    mysqli_stmt_bind_param($countStmt, "s", $categoryName);
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $totalRow = mysqli_fetch_assoc($countResult);
    $total = (int)$totalRow['total'];
    
    mysqli_stmt_close($countStmt);
    
    // 응답 데이터
    $response = [
        'success' => true,
        'category' => $category,
        'categoryName' => $categoryName,
        'images' => $images,
        'pagination' => [
            'current' => $page,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'perPage' => $limit,
            'hasNext' => $page < ceil($total / $limit),
            'hasPrev' => $page > 1
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // 에러 응답
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'category' => $_GET['category'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    
    // 로그 기록 (옵션)
    error_log("Portfolio API Error: " . $e->getMessage());
    
} finally {
    // 데이터베이스 연결 정리
    if (isset($db) && $db) {
        mysqli_close($db);
    }
}
?>