<?php
/**
 * 실제 파일이 존재하는 포트폴리오만 반환하는 API
 * 파일 시스템 체크까지 포함
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// UTF-8 설정 강화
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// 데이터베이스 연결
$host = "localhost";
$user = "duson1830"; 
$password = "du1830";
$dataname = "duson1830";

$db = mysqli_connect($host, $user, $password, $dataname);
if (!$db) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}
mysqli_set_charset($db, "utf8mb4");

try {
    // URL 파라미터 처리
    $category = isset($_GET['category']) ? $_GET['category'] : 'inserted';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 4;
    $offset = ($page - 1) * $perPage;
    
    // 매우 넓은 조건으로 데이터 검색
    $whereClause = "WHERE ThingCate IS NOT NULL 
                    AND ThingCate != '' 
                    AND LENGTH(ThingCate) > 3
                    AND ThingCate NOT LIKE '%test%'
                    AND ThingCate NOT LIKE '%테스트%'
                    AND ThingCate NOT LIKE '%.tmp'
                    AND date >= '2020-01-01'";
    
    // 카테고리별 필터링
    if ($category === 'inserted') {
        $whereClause .= " AND (Type = '전단지' OR Type LIKE '%전단%' OR Type = 'inserted' OR Type LIKE '%leaflet%')";
    } elseif ($category === 'namecard') {
        $whereClause .= " AND (Type = '명함' OR Type = 'NameCard' OR Type LIKE '%명함%' OR Type LIKE '%card%')";
    }
    
    // 데이터 조회 (많은 양을 가져와서 파일 체크)
    $query = "SELECT no, ThingCate, Type, name, cont, date, ImgFolder, Type_1
              FROM MlangOrder_PrintAuto 
              $whereClause 
              ORDER BY date DESC, no DESC 
              LIMIT " . ($perPage * 10); // 10배 많이 가져와서 필터링
              
    $result = mysqli_query($db, $query);
    
    $validImages = [];
    $checkedCount = 0;
    $foundCount = 0;
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result) && $foundCount < $perPage) {
            $checkedCount++;
            $orderNo = $row['no'];
            $imageFile = $row['ThingCate'];
            
            if (empty($imageFile)) continue;
            
            // 여러 가능한 경로들 확인
            $possiblePaths = [
                "/MlangOrder_PrintAuto/upload/$orderNo/$imageFile",
                "/upload/$orderNo/$imageFile",
                "/uploads/$orderNo/$imageFile",
                "/MlangPrintAuto/upload/$orderNo/$imageFile"
            ];
            
            $foundPath = null;
            foreach ($possiblePaths as $path) {
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
                if (file_exists($fullPath)) {
                    $foundPath = $path;
                    break;
                }
            }
            
            // 실제 파일이 존재하는 경우만 추가
            if ($foundPath) {
                $foundCount++;
                
                // 고객명 마스킹
                $customerName = $row['name'] ?? '';
                $maskedName = '';
                if (!empty($customerName)) {
                    if (mb_strlen($customerName) > 1) {
                        $maskedName = mb_substr($customerName, 0, 1) . str_repeat('*', mb_strlen($customerName) - 1);
                    } else {
                        $maskedName = '*';
                    }
                } else {
                    $maskedName = "고객" . substr($orderNo, -3);
                }
                
                $validImages[] = [
                    'id' => 'real_' . $orderNo,
                    'title' => $maskedName . "님의 " . ($category === 'inserted' ? '전단지' : $row['Type']) . " 작품",
                    'filename' => $imageFile,
                    'path' => $foundPath,
                    'image_path' => $foundPath,
                    'thumbnail' => $foundPath,
                    'thumbnail_path' => $foundPath,
                    'thumb_path' => $foundPath,
                    'url' => $foundPath,
                    'thumb' => $foundPath,
                    'full' => $foundPath,
                    'category' => $category === 'inserted' ? '전단지' : $row['Type'],
                    'type' => $row['Type'],
                    'order_no' => $orderNo,
                    'source' => 'real_orders_verified',
                    'description' => $row['cont'] ?: '',
                    'date' => $row['date'] ?? '',
                    'file_exists' => true,
                    'customer_masked' => $maskedName,
                    'is_real_work' => true,
                    'work_completed' => true,
                    'verified_path' => $foundPath
                ];
            }
        }
    }
    
    // 총 개수는 실제 검증된 이미지 개수로 설정
    $totalCount = count($validImages);
    $totalPages = 1; // 단일 페이지로 처리
    
    // 응답 데이터
    $response = [
        'success' => true,
        'category' => $category,
        'db_category' => $category === 'inserted' ? '전단지' : $category,
        'page' => $page,
        'limit' => $perPage,
        'total_items' => $totalCount,
        'total_pages' => $totalPages,
        'has_next' => false,
        'has_prev' => false,
        'data' => $validImages,
        'images' => $validImages,
        'count' => count($validImages),
        'source' => 'real_orders_file_verified',
        'version' => '3.0',
        'description' => '실제 파일이 존재하는 작업물만 선별',
        'debug' => [
            'checked_records' => $checkedCount,
            'found_files' => $foundCount,
            'where_clause' => $whereClause
        ],
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_count' => $totalCount,
            'total_pages' => $totalPages,
            'has_next' => false,
            'has_prev' => false,
            'next_page' => null,
            'prev_page' => null
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // 에러 응답
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage(),
        'category' => $category ?? 'unknown',
        'data' => [],
        'images' => [],
        'source' => 'real_orders_file_verified',
        'version' => '3.0'
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