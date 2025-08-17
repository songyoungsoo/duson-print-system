<?php
// 전단지 이미지 가져오기 AJAX 엔드포인트
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";

try {
    // 전단지/리플렛 카테고리 포트폴리오에서 최신 이미지들 가져오기
    $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
              FROM Mlang_portfolio_bbs 
              WHERE Mlang_bbs_reply='0' 
              AND (CATEGORY='전단지' OR CATEGORY='리플렛' OR CATEGORY='leaflet' OR CATEGORY='flyer')
              ORDER BY Mlang_bbs_no DESC 
              LIMIT 8";
    
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
                'image_path' => $image_path, // NcrFlambeau 패턴 호환성
                'thumbnail' => $image_path, // 썸네일도 같은 이미지 사용 (CSS로 크기 조정)
                'thumbnail_path' => $image_path, // NcrFlambeau 패턴 호환성
                'url' => $image_path, // GalleryLightbox 호환성을 위해 추가
                'thumb' => $image_path // GalleryLightbox 호환성을 위해 추가
            ];
        }
    }
    
    // 포트폴리오 이미지가 부족한 경우 기본 샘플 이미지 추가
    if (count($images) < 4) {
        $sample_images = [
            [
                'id' => 'sample_1',
                'title' => '전단지 샘플 1 - A4 4도 인쇄',
                'path' => '/images/samples/leaflet_sample_1.jpg',
                'image_path' => '/images/samples/leaflet_sample_1.jpg',
                'thumbnail' => '/images/samples/leaflet_sample_1.jpg',
                'thumbnail_path' => '/images/samples/leaflet_sample_1.jpg',
                'url' => '/images/samples/leaflet_sample_1.jpg',
                'thumb' => '/images/samples/leaflet_sample_1.jpg'
            ],
            [
                'id' => 'sample_2',
                'title' => '전단지 샘플 2 - 양면 인쇄',
                'path' => '/images/samples/leaflet_sample_2.jpg',
                'image_path' => '/images/samples/leaflet_sample_2.jpg',
                'thumbnail' => '/images/samples/leaflet_sample_2.jpg',
                'thumbnail_path' => '/images/samples/leaflet_sample_2.jpg',
                'url' => '/images/samples/leaflet_sample_2.jpg',
                'thumb' => '/images/samples/leaflet_sample_2.jpg'
            ],
            [
                'id' => 'sample_3',
                'title' => '전단지 샘플 3 - 고급 용지',
                'path' => '/images/samples/leaflet_sample_3.jpg',
                'image_path' => '/images/samples/leaflet_sample_3.jpg',
                'thumbnail' => '/images/samples/leaflet_sample_3.jpg',
                'thumbnail_path' => '/images/samples/leaflet_sample_3.jpg',
                'url' => '/images/samples/leaflet_sample_3.jpg',
                'thumb' => '/images/samples/leaflet_sample_3.jpg'
            ],
            [
                'id' => 'sample_4',
                'title' => '전단지 샘플 4 - 디자인+인쇄',
                'path' => '/images/samples/leaflet_sample_4.jpg',
                'image_path' => '/images/samples/leaflet_sample_4.jpg',
                'thumbnail' => '/images/samples/leaflet_sample_4.jpg',
                'thumbnail_path' => '/images/samples/leaflet_sample_4.jpg',
                'url' => '/images/samples/leaflet_sample_4.jpg',
                'thumb' => '/images/samples/leaflet_sample_4.jpg'
            ]
        ];
        
        // 부족한 만큼 샘플 이미지 추가
        $needed = 4 - count($images);
        for ($i = 0; $i < $needed && $i < count($sample_images); $i++) {
            $images[] = $sample_images[$i];
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