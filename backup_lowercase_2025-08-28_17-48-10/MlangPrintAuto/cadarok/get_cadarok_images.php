<?php
// 완전한 에러 출력 방지 및 클린 JSON 응답
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// 출력 버퍼링으로 모든 출력 캐치
ob_start();

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// 데이터베이스 연결 검사
if (!file_exists("../../db.php")) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration file not found',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 데이터베이스 연결 - 에러 억제
@include "../../db.php";

// 데이터베이스 연결 확인
if (!isset($db) || !$db) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 테이블 존재 확인 - 에러 억제
    $table_check = @mysqli_query($db, "SHOW TABLES LIKE "mlang_portfolio_bbs"");
    if (!$table_check || mysqli_num_rows($table_check) == 0) {
        throw new Exception("Portfolio table does not exist");
    }
    
    // URL 파라미터 처리
    $showAll = isset($_GET['all']) && $_GET['all'] === 'true';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(50, intval($_GET['per_page']))) : ($showAll ? 12 : 4);
    $offset = ($page - 1) * $perPage;
    
    // 총 개수 조회
    $countQuery = "SELECT COUNT(*) as total 
                   FROM mlang_portfolio_bbs 
                   WHERE Mlang_bbs_reply='0' AND (CATEGORY='카달로그' OR CATEGORY='리플렛' OR CATEGORY='카다록/리플렛' OR CATEGORY='cadarok')";
    $countResult = @mysqli_query($db, $countQuery);
    $totalCount = 0;
    if ($countResult) {
        $countRow = mysqli_fetch_assoc($countResult);
        $totalCount = $countRow['total'];
    }
    
    // 페이지네이션된 데이터 조회
    $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
              FROM mlang_portfolio_bbs 
              WHERE Mlang_bbs_reply='0' AND (CATEGORY='카달로그' OR CATEGORY='리플렛' OR CATEGORY='카다록/리플렛' OR CATEGORY='cadarok')
              ORDER BY CASE WHEN CATEGORY='카달로그' THEN 1 WHEN CATEGORY='리플렛' THEN 2 WHEN CATEGORY='카다록/리플렛' THEN 3 WHEN CATEGORY='cadarok' THEN 4 ELSE 5 END, Mlang_bbs_no DESC 
              LIMIT $perPage OFFSET $offset";
    
    $result = @mysqli_query($db, $query);
    
    if (!$result) {
        throw new Exception("Query execution error");
    }
    
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $image_path = '';
        $image_title = htmlspecialchars($row['Mlang_bbs_title'], ENT_QUOTES);
        
        // 이미지 경로 결정 (sub/catalog.php와 동일한 방식)
        if (!empty($row['Mlang_bbs_connent'])) {
            $image_path = '/bbs/upload/portfolio/' . $row['Mlang_bbs_connent'];
        } else if (!empty($row['Mlang_bbs_link'])) {
            $image_path = $row['Mlang_bbs_link'];
        }
        
        // 이미지가 있는 경우에만 배열에 추가
        if (!empty($image_path)) {
            $images[] = [
                'id' => $row['Mlang_bbs_no'],
                'title' => $image_title,
                'path' => $image_path,
                'thumbnail' => $image_path, // 썸네일도 같은 이미지 사용 (CSS로 크기 조정)
                'url' => $image_path, // GalleryLightbox 호환성을 위해 추가
                'thumb' => $image_path // GalleryLightbox 호환성을 위해 추가
            ];
        }
    }
    
    // 페이지네이션 정보 계산
    $totalPages = $totalCount > 0 ? ceil($totalCount / $perPage) : 1;
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    // 완전히 깨끗한 출력을 위해 버퍼 정리
    ob_end_clean();
    
    // 최종 JSON 응답
    echo json_encode([
        'success' => true,
        'data' => $images,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_count' => $totalCount,
            'total_pages' => $totalPages,
            'has_next' => $hasNext,
            'has_prev' => $hasPrev,
            'next_page' => $hasNext ? $page + 1 : null,
            'prev_page' => $hasPrev ? $page - 1 : null
        ],
        'count' => count($images)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // 완전히 깨끗한 출력을 위해 버퍼 정리
    ob_end_clean();
    
    // 오류 응답
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
} finally {
    // 데이터베이스 연결 종료
    if (isset($db)) {
        @mysqli_close($db);
    }
}

// 명시적으로 스크립트 종료
exit;
?>