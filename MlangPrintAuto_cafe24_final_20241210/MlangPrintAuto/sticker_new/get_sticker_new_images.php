<?php
// 새버전 스티커 이미지 가져오기 AJAX 엔드포인트
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";

try {
    // URL 파라미터 처리
    $showAll = isset($_GET['all']) && $_GET['all'] === 'true';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(50, intval($_GET['per_page']))) : ($showAll ? 12 : 4);
    $offset = ($page - 1) * $perPage;
    
    // 포트폴리오에서 sticker 카테고리 이미지를 직접 가져오기
    $images = [];
    $totalCount = 0;
    
    // 1차: sticker_new 카테고리에서 스티커 이미지 우선 가져오기
    
    // 총 개수 조회
    $countQuery = "SELECT COUNT(*) as total 
                   FROM Mlang_portfolio_bbs 
                   WHERE Mlang_bbs_reply='0' AND CATEGORY='sticker_new'";
    $countResult = mysqli_query($db, $countQuery);
    if ($countResult) {
        $countRow = mysqli_fetch_assoc($countResult);
        $totalCount = $countRow['total'];
    }
    
    // 페이지네이션된 데이터 조회
    $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
              FROM Mlang_portfolio_bbs 
              WHERE Mlang_bbs_reply='0' AND CATEGORY='sticker_new'
              ORDER BY Mlang_bbs_no DESC 
              LIMIT $perPage OFFSET $offset";
    
    $result = mysqli_query($db, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $image_path = '';
            $image_title = htmlspecialchars($row['Mlang_bbs_title'], ENT_QUOTES);
            
            if (!empty($row['Mlang_bbs_connent'])) {
                $image_path = '/bbs/upload/portfolio/' . $row['Mlang_bbs_connent'];
            } else if (!empty($row['Mlang_bbs_link'])) {
                $image_path = $row['Mlang_bbs_link'];
            }
            
            if (!empty($image_path)) {
                $images[] = [
                    'id' => $row['Mlang_bbs_no'],
                    'title' => $image_title,
                    'path' => $image_path,
                    'thumbnail' => $image_path,
                    'url' => $image_path,
                    'thumb' => $image_path
                ];
            }
        }
    }
    
    // 2차: sticker_new가 없다면 스티커 관련 키워드로 검색
    if (empty($images) && $totalCount == 0) {
        // 총 개수 조회
        $countQuery = "SELECT COUNT(*) as total 
                       FROM Mlang_portfolio_bbs 
                       WHERE Mlang_bbs_reply='0' AND (
                           CATEGORY='sticker' OR 
                           CATEGORY LIKE '%스티커%' OR 
                           Mlang_bbs_title LIKE '%스티커%' OR 
                           Mlang_bbs_title LIKE '%sticker%' OR
                           CATEGORY='namecard'
                       )";
        $countResult = mysqli_query($db, $countQuery);
        if ($countResult) {
            $countRow = mysqli_fetch_assoc($countResult);
            $totalCount = $countRow['total'];
        }
        
        $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
                  FROM Mlang_portfolio_bbs 
                  WHERE Mlang_bbs_reply='0' AND (
                      CATEGORY='sticker' OR 
                      CATEGORY LIKE '%스티커%' OR 
                      Mlang_bbs_title LIKE '%스티커%' OR 
                      Mlang_bbs_title LIKE '%sticker%' OR
                      CATEGORY='namecard'
                  )
                  ORDER BY Mlang_bbs_no DESC 
                  LIMIT $perPage OFFSET $offset";
        
        $result = mysqli_query($db, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $image_path = '';
                $image_title = htmlspecialchars($row['Mlang_bbs_title'], ENT_QUOTES);
                
                if (!empty($row['Mlang_bbs_connent'])) {
                    $image_path = '/bbs/upload/portfolio/' . $row['Mlang_bbs_connent'];
                } else if (!empty($row['Mlang_bbs_link'])) {
                    $image_path = $row['Mlang_bbs_link'];
                }
                
                if (!empty($image_path)) {
                    $images[] = [
                        'id' => $row['Mlang_bbs_no'],
                        'title' => $image_title,
                        'path' => $image_path,
                        'thumbnail' => $image_path,
                        'url' => $image_path,
                        'thumb' => $image_path
                    ];
                }
            }
        }
    }
    
    // 3차: 그래도 없다면 모든 포트폴리오에서 최신순으로 가져오기
    if (empty($images) && $totalCount == 0) {
        // 총 개수 조회
        $countQuery = "SELECT COUNT(*) as total 
                       FROM Mlang_portfolio_bbs 
                       WHERE Mlang_bbs_reply='0' AND (Mlang_bbs_connent IS NOT NULL AND Mlang_bbs_connent != '' OR Mlang_bbs_link IS NOT NULL AND Mlang_bbs_link != '')";
        $countResult = mysqli_query($db, $countQuery);
        if ($countResult) {
            $countRow = mysqli_fetch_assoc($countResult);
            $totalCount = $countRow['total'];
        }
        
        $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
                  FROM Mlang_portfolio_bbs 
                  WHERE Mlang_bbs_reply='0' AND (Mlang_bbs_connent IS NOT NULL AND Mlang_bbs_connent != '' OR Mlang_bbs_link IS NOT NULL AND Mlang_bbs_link != '')
                  ORDER BY Mlang_bbs_no DESC 
                  LIMIT $perPage OFFSET $offset";
        
        $result = mysqli_query($db, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $image_path = '';
                $image_title = htmlspecialchars($row['Mlang_bbs_title'], ENT_QUOTES);
                
                if (!empty($row['Mlang_bbs_connent'])) {
                    $image_path = '/bbs/upload/portfolio/' . $row['Mlang_bbs_connent'];
                } else if (!empty($row['Mlang_bbs_link'])) {
                    $image_path = $row['Mlang_bbs_link'];
                }
                
                if (!empty($image_path)) {
                    $images[] = [
                        'id' => $row['Mlang_bbs_no'],
                        'title' => $image_title . ' (스티커 참고)',
                        'path' => $image_path,
                        'thumbnail' => $image_path,
                        'url' => $image_path,
                        'thumb' => $image_path
                    ];
                }
            }
        }
    }
    
    // 페이지네이션 정보 계산
    $totalPages = $totalCount > 0 ? ceil($totalCount / $perPage) : 1;
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    // JSON 응답
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
    // 오류 응답
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
} finally {
    // 데이터베이스 연결 종료
    if (isset($db)) {
        mysqli_close($db);
    }
}
?>