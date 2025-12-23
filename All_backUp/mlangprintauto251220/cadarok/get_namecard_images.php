<?php
// 명함 이미지 가져오기 AJAX 엔드포인트
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";

try {
    // 명함 카테고리 포트폴리오에서 최신 이미지 4개 가져오기
    $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
              FROM mlang_portfolio_bbs 
              WHERE Mlang_bbs_reply='0' AND CATEGORY='명함'
              ORDER BY Mlang_bbs_no DESC 
              LIMIT 4";
    
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        throw new Exception("쿼리 실행 오류: " . mysqli_error($db));
    }
    
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $image_path = '';
        $image_title = htmlspecialchars($row['Mlang_bbs_title'], ENT_QUOTES);
        
        // 이미지 경로 결정 (connent 우선, 없으면 link 사용)
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