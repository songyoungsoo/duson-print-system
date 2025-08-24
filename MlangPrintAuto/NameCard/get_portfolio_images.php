<?php
/**
 * 통합 갤러리 시스템 - BBS 포트폴리오 이미지 API
 * 명함 기준 개발 후 다른 제품에 적용
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 데이터베이스 연결
include "../../db.php";

// 입력 매개변수
$category = isset($_GET['category']) ? $_GET['category'] : 'namecard';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'thumbnail'; // thumbnail, popup

// 카테고리 매핑
$categoryMapping = [
    'namecard' => '명함',
    'envelope' => '봉투', 
    'sticker' => '스티커',
    'leaflet' => '전단지',
    'poster' => '포스터',
    'cadarok' => '카다록',
    'merchandisebond' => '상품권',
    'msticker' => '자석스티커',
    'ncrflambeau' => '양식지'
];

$dbCategory = isset($categoryMapping[$category]) ? $categoryMapping[$category] : '명함';

try {
    // UTF-8 설정
    mysqli_set_charset($db, "utf8");
    
    // 전체 개수 조회
    $countQuery = "SELECT COUNT(*) as total 
                   FROM mlang_portfolio_bbs 
                   WHERE CATEGORY LIKE '%$dbCategory%' 
                   AND Mlang_bbs_file IS NOT NULL 
                   AND Mlang_bbs_file != ''";
    
    $countResult = mysqli_query($db, $countQuery);
    $totalItems = 0;
    
    if ($countResult && $countRow = mysqli_fetch_assoc($countResult)) {
        $totalItems = (int)$countRow['total'];
    }
    
    // 페이지네이션 계산
    $offset = ($page - 1) * $limit;
    $totalPages = ceil($totalItems / $limit);
    
    // 이미지 데이터 조회
    $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_file, CATEGORY, Mlang_date
              FROM mlang_portfolio_bbs 
              WHERE CATEGORY LIKE '%$dbCategory%' 
              AND Mlang_bbs_file IS NOT NULL 
              AND Mlang_bbs_file != ''
              ORDER BY Mlang_bbs_no DESC 
              LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($db, $query);
    $images = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // 파일 정보 파싱 (여러 파일이 | 로 구분되어 있을 수 있음)
            $files = explode('|', $row['Mlang_bbs_file']);
            
            foreach ($files as $file) {
                $file = trim($file);
                if (!empty($file)) {
                    // 파일 확장자 체크
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                        $images[] = [
                            'id' => $row['Mlang_bbs_no'],
                            'title' => $row['Mlang_bbs_title'],
                            'filename' => $file,
                            'path' => "/bbs/upload/portfolio/" . $file,
                            'thumb_path' => "/bbs/upload/portfolio/" . $file, // 썸네일은 원본 사용
                            'category' => $row['CATEGORY'],
                            'date' => $row['Mlang_date']
                        ];
                        
                        // 썸네일 모드면 4개만, 팝업 모드면 limit만큼
                        if ($mode === 'thumbnail' && count($images) >= 4) {
                            break 2; // 이중 루프 탈출
                        }
                    }
                }
            }
        }
    }
    
    // 이미지가 부족한 경우 기본 이미지 추가
    if ($mode === 'thumbnail') {
        while (count($images) < 4) {
            $images[] = [
                'id' => 'default',
                'title' => '샘플 이미지',
                'filename' => 'default-' . $category . '.jpg',
                'path' => "/images/samples/default-" . $category . ".jpg",
                'thumb_path' => "/images/samples/default-" . $category . ".jpg",
                'category' => $dbCategory,
                'date' => date('Y-m-d H:i:s'),
                'is_default' => true
            ];
        }
        
        // 썸네일 모드는 정확히 4개만
        $images = array_slice($images, 0, 4);
    }
    
    // 응답 데이터 구성
    $response = [
        'success' => true,
        'category' => $category,
        'db_category' => $dbCategory,
        'mode' => $mode,
        'page' => $page,
        'limit' => $limit,
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'has_next' => $page < $totalPages,
        'has_prev' => $page > 1,
        'images' => $images,
        'count' => count($images)
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // 에러 응답
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'category' => $category,
        'images' => []
    ];
    
    http_response_code(500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// 데이터베이스 연결 종료
if ($db) {
    mysqli_close($db);
}
?>