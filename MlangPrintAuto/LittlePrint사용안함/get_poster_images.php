<?php
// 포스터 이미지 가져오기 AJAX 엔드포인트
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";

try {
    // sub/poster.php에서 포스터 샘플 이미지 정보 가져오기 
    // 실제 포스터 샘플 이미지들 (/img/portfolio/ 경로)
    $poster_samples = [
        [
            'id' => 'poster_1',
            'title' => '포스터 샘플 1',
            'path' => '/img/portfolio/poster_001.jpg'
        ],
        [
            'id' => 'poster_2', 
            'title' => '포스터 샘플 2',
            'path' => '/img/portfolio/poster_002.jpg'
        ],
        [
            'id' => 'poster_3',
            'title' => '포스터 샘플 3', 
            'path' => '/img/portfolio/poster_003.jpg'
        ],
        [
            'id' => 'poster_4',
            'title' => '포스터 샘플 4',
            'path' => '/img/portfolio/poster_004.jpg'
        ],
        [
            'id' => 'poster_5',
            'title' => '포스터 샘플 5',
            'path' => '/img/portfolio/poster_005.jpg'
        ],
        [
            'id' => 'poster_6',
            'title' => '포스터 샘플 6',
            'path' => '/img/portfolio/poster_006.jpg'
        ]
    ];

    // 실제로는 데이터베이스에서 가져오는 것 대신 샘플 이미지 사용
    $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
              FROM Mlang_portfolio_bbs 
              WHERE Mlang_bbs_reply='0' AND CATEGORY='포스터'
              ORDER BY Mlang_bbs_no DESC 
              LIMIT 6";
    
    $result = mysqli_query($db, $query);
    
    $images = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        // 데이터베이스에서 이미지가 있으면 사용
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
                    'thumbnail' => $image_path // 썸네일도 같은 이미지 사용 (CSS로 크기 조정)
                ];
            }
        }
    }
    
    // 데이터베이스에 이미지가 없거나 부족하면 샘플 이미지 사용
    if (count($images) < 3) {
        $images = [];
        foreach ($poster_samples as $sample) {
            $images[] = [
                'id' => $sample['id'],
                'title' => $sample['title'],
                'path' => $sample['path'],
                'thumbnail' => $sample['path']
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