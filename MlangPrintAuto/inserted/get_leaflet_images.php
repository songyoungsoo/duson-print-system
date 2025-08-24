<?php
/**
 * 전단지 포트폴리오 이미지 API
 * MlangOrder_PrintAuto 테이블에서 실제 완성된 주문 이미지를 가져옴
 * 명함과 동일한 구조로 통합
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
    // URL 파라미터 처리
    $category = isset($_GET['category']) ? $_GET['category'] : 'inserted';
    $showAll = isset($_GET['all']) && $_GET['all'] === 'true';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : ($showAll ? 12 : 4);
    $offset = ($page - 1) * $perPage;
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'thumbnail'; // thumbnail, popup
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : $perPage;
    
    // 전단지 타입 설정
    $productType = '전단지';
    
    // 이미지가 있는 전단지 주문만 선택 (조건 완화)
    // OrderStyle: 모든 상태 포함 (0보다 큰 값)
    // ThingCate: 이미지 파일명이 있어야 함 (길이 3자 이상)
    // Type: 전단지 관련 모든 타입
    // date: 최근 2년 내 데이터
    $whereClause = "WHERE OrderStyle > '0' 
                    AND ThingCate IS NOT NULL 
                    AND ThingCate != '' 
                    AND LENGTH(ThingCate) > 3
                    AND ThingCate NOT LIKE '%test%'
                    AND ThingCate NOT LIKE '%테스트%'
                    AND (Type = '전단지' OR Type LIKE '%전단%' OR Type = 'inserted')
                    AND date >= DATE_SUB(NOW(), INTERVAL 2 YEAR)";
    
    // 총 개수 구하기
    $countQuery = "SELECT COUNT(*) as total FROM MlangOrder_PrintAuto $whereClause";
    $countResult = mysqli_query($db, $countQuery);
    $totalCount = 0;
    
    if ($countResult) {
        $totalRow = mysqli_fetch_assoc($countResult);
        $totalCount = intval($totalRow['total']);
    }
    
    // 페이지네이션 계산
    $totalPages = $totalCount > 0 ? ceil($totalCount / $perPage) : 1;
    
    // 실제 데이터 가져오기 (2025년 1월 및 오래된 주문 우선)
    $query = "SELECT no, ThingCate, Type, name, cont, date, ImgFolder, Type_1
              FROM MlangOrder_PrintAuto 
              $whereClause 
              ORDER BY 
                CASE 
                    WHEN date >= '2025-01-01' AND date < '2025-02-01' THEN 0  -- 2025년 1월 최우선
                    WHEN no < 70000 THEN 1  -- 오래된 주문 (70000번 이하)
                    WHEN no BETWEEN 70000 AND 75000 THEN 2  -- 중간 주문
                    WHEN no BETWEEN 75000 AND 80000 THEN 3  -- 좀 더 최근
                    ELSE 4  -- 최신 주문 (연습용일 가능성)
                END,
                no DESC 
              LIMIT $offset, $perPage";
              
    $result = mysqli_query($db, $query);
    
    $images = [];
    $debugInfo = [];
    $processedCount = 0;
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $processedCount++;
            $orderNo = $row['no'];
            $imageFile = $row['ThingCate'];
            
            if (empty($imageFile)) continue;
            
            // 실제 파일 경로 설정
            $imagePath = "/MlangOrder_PrintAuto/upload/$orderNo/$imageFile";
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
            $fileExists = file_exists($fullPath);
            
            // 파일이 존재하지 않으면 다른 가능한 경로들 확인
            if (!$fileExists) {
                $possiblePaths = [
                    "/upload/$orderNo/$imageFile",
                    "/uploads/$orderNo/$imageFile",
                    "/MlangPrintAuto/upload/$orderNo/$imageFile"
                ];
                
                foreach ($possiblePaths as $path) {
                    $testPath = $_SERVER['DOCUMENT_ROOT'] . $path;
                    if (file_exists($testPath)) {
                        $imagePath = $path;
                        $fullPath = $testPath;
                        $fileExists = true;
                        break;
                    }
                }
            }
            
            // 고객명 마스킹 (개인정보 보호)
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
            
            // 이미지 정보 배열에 추가
            $images[] = [
                'id' => 'real_' . $orderNo,
                'title' => $maskedName . "님의 전단지 작품",
                'filename' => $imageFile,
                'path' => $imagePath,
                'image_path' => $imagePath,
                'thumbnail' => $imagePath,
                'thumbnail_path' => $imagePath,
                'thumb_path' => $imagePath,
                'url' => $imagePath,
                'thumb' => $imagePath,
                'category' => '전단지',
                'type' => $row['Type'],
                'type_name' => '전단지',
                'order_no' => $orderNo,
                'source' => 'real_orders',
                'description' => $row['cont'] ?: '',
                'date' => $row['date'] ?? '',
                'file_exists' => $fileExists,
                'customer_masked' => $maskedName,
                'is_real_work' => true,
                'work_completed' => true
            ];
            
            // 썸네일 모드면 4개만
            if ($mode === 'thumbnail' && count($images) >= 4) {
                break;
            }
        }
    }
    
    // 이미지가 부족한 경우 기본 샘플 이미지 추가 (썸네일 모드일 때만)
    if ($mode === 'thumbnail' && count($images) < 4) {
        $sampleImages = [
            [
                'id' => 'sample_1',
                'title' => '전단지 샘플 1',
                'filename' => 'default-leaflet-1.jpg',
                'path' => '/images/samples/leaflet_sample_1.jpg',
                'image_path' => '/images/samples/leaflet_sample_1.jpg',
                'thumbnail' => '/images/samples/leaflet_sample_1.jpg',
                'thumbnail_path' => '/images/samples/leaflet_sample_1.jpg',
                'thumb_path' => '/images/samples/leaflet_sample_1.jpg',
                'url' => '/images/samples/leaflet_sample_1.jpg',
                'thumb' => '/images/samples/leaflet_sample_1.jpg',
                'category' => '전단지',
                'is_default' => true
            ],
            [
                'id' => 'sample_2',
                'title' => '전단지 샘플 2',
                'filename' => 'default-leaflet-2.jpg',
                'path' => '/images/samples/leaflet_sample_2.jpg',
                'image_path' => '/images/samples/leaflet_sample_2.jpg',
                'thumbnail' => '/images/samples/leaflet_sample_2.jpg',
                'thumbnail_path' => '/images/samples/leaflet_sample_2.jpg',
                'thumb_path' => '/images/samples/leaflet_sample_2.jpg',
                'url' => '/images/samples/leaflet_sample_2.jpg',
                'thumb' => '/images/samples/leaflet_sample_2.jpg',
                'category' => '전단지',
                'is_default' => true
            ],
            [
                'id' => 'sample_3',
                'title' => '전단지 샘플 3',
                'filename' => 'default-leaflet-3.jpg',
                'path' => '/images/samples/leaflet_sample_3.jpg',
                'image_path' => '/images/samples/leaflet_sample_3.jpg',
                'thumbnail' => '/images/samples/leaflet_sample_3.jpg',
                'thumbnail_path' => '/images/samples/leaflet_sample_3.jpg',
                'thumb_path' => '/images/samples/leaflet_sample_3.jpg',
                'url' => '/images/samples/leaflet_sample_3.jpg',
                'thumb' => '/images/samples/leaflet_sample_3.jpg',
                'category' => '전단지',
                'is_default' => true
            ],
            [
                'id' => 'sample_4',
                'title' => '전단지 샘플 4',
                'filename' => 'default-leaflet-4.jpg',
                'path' => '/images/samples/leaflet_sample_4.jpg',
                'image_path' => '/images/samples/leaflet_sample_4.jpg',
                'thumbnail' => '/images/samples/leaflet_sample_4.jpg',
                'thumbnail_path' => '/images/samples/leaflet_sample_4.jpg',
                'thumb_path' => '/images/samples/leaflet_sample_4.jpg',
                'url' => '/images/samples/leaflet_sample_4.jpg',
                'thumb' => '/images/samples/leaflet_sample_4.jpg',
                'category' => '전단지',
                'is_default' => true
            ]
        ];
        
        // 부족한 만큼 샘플 이미지 추가
        $needed = 4 - count($images);
        for ($i = 0; $i < $needed && $i < count($sampleImages); $i++) {
            $images[] = $sampleImages[$i];
        }
    }
    
    // 페이지네이션 정보
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    // 응답 데이터 구성 (다양한 키 지원으로 호환성 확보)
    $response = [
        'success' => true,
        'category' => $category,
        'db_category' => '전단지',
        'mode' => $mode,
        'page' => $page,
        'limit' => $limit,
        'total_items' => $totalCount,
        'total_pages' => $totalPages,
        'has_next' => $hasNext,
        'has_prev' => $hasPrev,
        'images' => $images,
        'data' => $images, // 호환성을 위해 추가
        'count' => count($images),
        'source' => 'real_orders',
        'version' => '2.0',
        'description' => '실제 완성된 전단지 작업물',
        'pagination' => [ // 호환성을 위해 추가
            'current_page' => $page,
            'per_page' => $perPage,
            'total_count' => $totalCount,
            'total_pages' => $totalPages,
            'has_next' => $hasNext,
            'has_prev' => $hasPrev,
            'next_page' => $hasNext ? $page + 1 : null,
            'prev_page' => $hasPrev ? $page - 1 : null
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // 에러 응답
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage(),
        'category' => $category ?? 'inserted',
        'images' => [],
        'data' => [],
        'source' => 'real_orders',
        'version' => '2.0'
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