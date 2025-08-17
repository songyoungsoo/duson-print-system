<?php
// 자석스티커 이미지 가져오기 AJAX 엔드포인트
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";

try {
    // 포트폴리오에서 sticker 카테고리 이미지를 직접 가져오기
    $images = [];
    
    // 1차: msticker 카테고리에서 자석스티커 이미지 우선 가져오기
    $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
              FROM Mlang_portfolio_bbs 
              WHERE Mlang_bbs_reply='0' AND CATEGORY='msticker'
              ORDER BY Mlang_bbs_no DESC 
              LIMIT 8";
    
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
    
    // 2차: msticker가 없다면 sticker 카테고리와 자석스티커 관련 키워드로 검색
    if (empty($images)) {
        $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
                  FROM Mlang_portfolio_bbs 
                  WHERE Mlang_bbs_reply='0' AND (
                      CATEGORY='sticker' OR 
                      CATEGORY LIKE '%자석%' OR 
                      Mlang_bbs_title LIKE '%자석%' OR 
                      Mlang_bbs_title LIKE '%자석스티커%' OR 
                      Mlang_bbs_title LIKE '%msticker%' OR
                      Mlang_bbs_title LIKE '%스티커%'
                  )
                  ORDER BY Mlang_bbs_no DESC 
                  LIMIT 8";
        
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
    if (empty($images)) {
        $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
                  FROM Mlang_portfolio_bbs 
                  WHERE Mlang_bbs_reply='0' AND (Mlang_bbs_connent IS NOT NULL AND Mlang_bbs_connent != '' OR Mlang_bbs_link IS NOT NULL AND Mlang_bbs_link != '')
                  ORDER BY Mlang_bbs_no DESC 
                  LIMIT 8";
        
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
                        'title' => $image_title . ' (자석스티커 참고)',
                        'path' => $image_path,
                        'thumbnail' => $image_path,
                        'url' => $image_path,
                        'thumb' => $image_path
                    ];
                }
            }
        }
    }
    
    // JSON 응답
    echo json_encode([
        'success' => true,
        'data' => $images,
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