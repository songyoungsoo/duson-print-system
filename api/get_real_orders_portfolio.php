<?php
/**
 * 실제 완성된 주문 작업물을 포트폴리오로 활용하는 API
 * 매일 작업해서 완성한 현실감 있는 실제 작업물들
 */

// 오류 출력 차단
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// UTF-8 설정 강화
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// 데이터베이스 연결
include "../db.php";

if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // URL 파라미터 처리
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $showAll = isset($_GET['all']) && $_GET['all'] === 'true';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : ($showAll ? 12 : 4);
    $offset = ($page - 1) * $perPage;

    // 샘플 이미지 로드 함수 (썸네일용) - UTF-8 안전 버전
    // samplegallery 이미지를 우선 표시하고, 부족하면 sample 이미지 추가
    function getSampleImages($category, $limit = 4) {
        // 카테고리 폴더명 매핑 (API 카테고리 -> 실제 폴더명)
        $folderMapping = [
            'sticker' => 'sticker_new',
            'sticker_new' => 'sticker_new',
            'namecard' => 'namecard',
            'inserted' => 'inserted',
            'envelope' => 'envelope',
            'littleprint' => 'littleprint',
            'cadarok' => 'cadarok',
            'merchandisebond' => 'merchandisebond',
            'msticker' => 'msticker',
            'ncrflambeau' => 'ncrflambeau'
        ];

        $folderName = $folderMapping[$category] ?? $category;
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $images = [];

        // 1️⃣ samplegallery 이미지 로드 (최우선 - 큐레이티드 샘플)
        $sampleGalleryDir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/samplegallery/' . $folderName;
        $galleryFiles = [];

        if (is_dir($sampleGalleryDir)) {
            $allGalleryFiles = scandir($sampleGalleryDir);
            if ($allGalleryFiles !== false) {
                foreach ($allGalleryFiles as $filename) {
                    if ($filename === '.' || $filename === '..') {
                        continue;
                    }

                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if (in_array($ext, $imageExtensions)) {
                        $fullPath = $sampleGalleryDir . '/' . $filename;
                        $galleryFiles[] = [
                            'path' => $fullPath,
                            'name' => $filename,
                            'mtime' => filemtime($fullPath),
                            'source_dir' => 'samplegallery'
                        ];
                    }
                }
            }
        }

        // samplegallery가 비어있으면 빈 배열 반환
        if (empty($galleryFiles)) {
            return [];
        }

        // 최신순으로 정렬
        usort($galleryFiles, function($a, $b) {
            return $b['mtime'] - $a['mtime'];
        });

        // 모든 samplegallery 이미지 반환 (limit 제한 제거)
        // $galleryFiles = array_slice($galleryFiles, 0, $limit);

        foreach ($galleryFiles as $fileInfo) {
            $filename = $fileInfo['name'];
            $basePath = '/ImgFolder/samplegallery/' . $folderName . '/';

            $images[] = [
                'id' => 'samplegallery_' . md5($filename),
                'title' => pathinfo($filename, PATHINFO_FILENAME),
                'path' => $basePath . rawurlencode($filename),
                'image_path' => $basePath . rawurlencode($filename),
                'thumbnail' => $basePath . rawurlencode($filename),
                'thumbnail_path' => $basePath . rawurlencode($filename),
                'url' => $basePath . rawurlencode($filename),
                'thumb' => $basePath . rawurlencode($filename),
                'category' => $category,
                'source' => 'samplegallery_images',
                'is_sample' => true,
                'file_exists' => true
            ];
        }

        return $images;
    }

    // 카테고리별 타입 매핑 (실제 데이터베이스의 Type 필드값)
    $typeMapping = [
        'namecard' => ['명함', 'namecard'],  // 명함은 한글로 저장됨
        'sticker' => ['스티커', 'sticker'],  // 한글과 영문, 인코딩 차이 고려
        'sticker_new' => ['스티커', 'sticker'],  // sticker_new도 스티커로 매핑
        'envelope' => ['봉투', 'envelope'],
        'littleprint' => ['포스터', 'littleprint'],
        'cadarok' => ['카탈로그', 'cadarok'],
        'merchandisebond' => ['상품권', 'merchandisebond'],
        'msticker' => ['자석스티커', 'msticker'],
        'ncrflambeau' => ['양식지', 'ncrflambeau'],
        'inserted' => ['전단지']  // 실제 데이터베이스에는 '전단지'로 저장됨
    ];
    
    $dbTypes = $typeMapping[$category] ?? [];
    
    // 이미지가 있는 주문만 선택 (더 넓은 조건)
    // OrderStyle: 모든 상태 포함 (0보다 큰 값)
    // ThingCate: 이미지 파일명이 있어야 함
    // ImgFolder: 업로드 폴더 정보가 있어야 함 (선택적)
    // date: 2022-01-01 ~ 2024-12-31 기간 한정
    $whereClause = "WHERE OrderStyle > '0'
                    AND ThingCate IS NOT NULL
                    AND ThingCate != ''
                    AND LENGTH(ThingCate) > 3
                    AND ThingCate NOT LIKE '%test%'
                    AND ThingCate NOT LIKE '%테스트%'
                    AND date >= '2022-01-01'
                    AND date <= '2024-12-31'";
    
    // 카테고리별 필터링 - 전단지 조건을 기본으로 모든 품목에 동일 적용
    if ($category === 'inserted') {
        $whereClause .= " AND Type = '전단지'";
    } elseif ($category === 'namecard') {
        $whereClause .= " AND (Type = '명함' OR Type = 'namecard')";
    } elseif ($category === 'sticker' || $category === 'sticker_new') {
        $whereClause .= " AND Type = '스티커'";
    } elseif ($category === 'ncrflambeau') {
        $whereClause .= " AND (Type = '양식지' OR Type = 'ncrflambeau')";
    } elseif ($category === 'envelope') {
        $whereClause .= " AND Type = '봉투'";
    } elseif ($category === 'littleprint') {
        $whereClause .= " AND (Type = '포스터' OR Type = 'littleprint')";
    } elseif ($category === 'cadarok') {
        $whereClause .= " AND Type = '카탈로그'";
    } elseif ($category === 'merchandisebond') {
        $whereClause .= " AND Type = '상품권'";
    } elseif ($category === 'msticker') {
        $whereClause .= " AND Type = '자석스티커'";
    } elseif ($category && !empty($dbTypes)) {
        $typeConditions = [];
        foreach ($dbTypes as $type) {
            $typeConditions[] = "Type = '" . mysqli_real_escape_string($db, $type) . "'";
        }
        if (!empty($typeConditions)) {
            $whereClause .= " AND (" . implode(" OR ", $typeConditions) . ")";
        }
    }
    
    // 🔐 개인정보 민감 제품 정의
    $privateCategories = ['namecard', 'envelope', 'ncrflambeau'];
    $isPrivateCategory = in_array($category, $privateCategories);

    // 총 개수 구하기 (개인정보 민감 제품은 실제 주문 제외)
    $totalCount = 0;
    if (!$isPrivateCategory) {
        $countQuery = "SELECT COUNT(*) as total FROM mlangorder_printauto $whereClause";
        $countResult = mysqli_query($db, $countQuery);
        if ($countResult) {
            $countRow = mysqli_fetch_assoc($countResult);
            $totalCount = intval($countRow['total']);
        }
    }
    
    // 실제 데이터 가져오기 (개인정보 민감 제품은 건너뜀)
    $result = null;
    if (!$isPrivateCategory) {
        // 스티커는 랜덤으로, 나머지는 기존 정렬 유지
        if ($category === 'sticker' || $category === 'sticker_new') {
            // 스티커는 랜덤하게 가져오기 (매번 다른 이미지 표시)
            $query = "SELECT no, ThingCate, Type, name, date
                      FROM mlangorder_printauto
                      $whereClause
                      ORDER BY RAND()
                      LIMIT " . intval($perPage) . " OFFSET " . intval($offset);
        } else {
            // 나머지 카테고리는 기존 정렬 유지 (2025년 1월 및 오래된 주문 우선)
            $query = "SELECT no, ThingCate, Type, name, date
                      FROM mlangorder_printauto
                      $whereClause
                      ORDER BY
                        CASE
                            WHEN date >= '2025-01-01' AND date < '2025-02-01' THEN 0
                            WHEN no < 70000 THEN 1
                            WHEN no BETWEEN 70000 AND 75000 THEN 2
                            WHEN no BETWEEN 75000 AND 80000 THEN 3
                            ELSE 4
                        END,
                        no DESC
                      LIMIT " . intval($perPage) . " OFFSET " . intval($offset);
        }

        $result = mysqli_query($db, $query);
    }
    
    // 개인정보 민감 제품이 아닐 때만 에러 체크
    if (!$isPrivateCategory && !$result) {
        throw new Exception("Query failed: " . mysqli_error($db));
    }
    
    $images = [];
    $debugInfo = [];
    $processedCount = 0;

    // 샘플 이미지 먼저 추가 (첫 페이지에서 항상 표시)
    // exclude_samples=true 파라미터가 있으면 샘플 이미지 제외
    $excludeSamples = isset($_GET['exclude_samples']) && $_GET['exclude_samples'] === 'true';

    if ($page === 1 && !$excludeSamples) {
        $sampleImages = getSampleImages($category, $perPage);
        if (!empty($sampleImages)) {
            $images = array_merge($images, $sampleImages);
        }
    }

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // 이미 충분한 이미지를 가져왔으면 중단
            if (count($images) >= $perPage) {
                break;
            }

            $processedCount++;
            $orderNo = $row['no'];
            $imageFile = $row['ThingCate'];

            $debugInfo[] = [
                'order_no' => $orderNo,
                'type' => $row['Type'],
                'image_file' => $imageFile,
                'processed' => $processedCount
            ];

            if (empty($imageFile)) continue;
            
            // 실제 파일 시스템에서 이미지 파일 경로 찾기
            // 여러 가능한 경로 확인
            $uploadBasePath = $_SERVER['DOCUMENT_ROOT'];
            $imagePath = null;
            $fullPath = null;
            $fileExists = false;
            
            // 가능한 경로들 확인
            $possiblePaths = [
                "$uploadBasePath/mlangorder_printauto/upload/$orderNo/$imageFile",
                "$uploadBasePath/upload/$orderNo/$imageFile",
                "$uploadBasePath/uploads/$orderNo/$imageFile",
                "$uploadBasePath/mlangprintauto/upload/$orderNo/$imageFile"
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $imagePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
                    $fullPath = $path;
                    $fileExists = true;
                    break;
                }
            }
            
            // ⚠️ 빈 이미지 필터링: 파일이 존재하지 않거나 크기가 0이면 건너뛰기
            if (!$fileExists) {
                continue; // 파일이 존재하지 않으면 건너뛰기
            }

            // 파일 크기 확인 (0바이트 파일 제외)
            $fileSize = filesize($fullPath);
            if ($fileSize === false || $fileSize === 0) {
                continue; // 파일 크기가 0이거나 읽을 수 없으면 건너뛰기
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
            
            // 제품타입별 한글명 변환 (한글 저장된 데이터도 고려)
            $typeNames = [
                'namecard' => '명함',
                '명함' => '명함',
                'sticker' => '스티커',
                '스티커' => '스티커',
                'envelope' => '봉투',
                '봉투' => '봉투',
                'littleprint' => '포스터',
                '포스터' => '포스터',
                'cadarok' => '카탈로그',
                '카탈로그' => '카탈로그',
                'merchandisebond' => '상품권',
                '상품권' => '상품권',
                'msticker' => '자석스티커',
                '자석스티커' => '자석스티커',
                'ncrflambeau' => '양식지',
                '양식지' => '양식지',
                'inserted' => '전단지',
                '전단지' => '전단지',
                '전단지A5' => '전단지'
            ];
            $typeName = $typeNames[$row['Type']] ?? $row['Type'];
            
            $images[] = [
                'id' => 'real_' . $orderNo,
                'title' => $maskedName . "님의 " . $typeName . " 작품",
                'path' => $imagePath,
                'image_path' => $imagePath,
                'thumbnail' => $imagePath,
                'thumbnail_path' => $imagePath,
                'url' => $imagePath,
                'thumb' => $imagePath,
                'category' => $category,
                'type' => $row['Type'],
                'type_name' => $typeName,
                'order_no' => $orderNo,
                'source' => 'real_orders',
                'description' => '',
                'date' => $row['date'] ?? '',
                'file_exists' => $fileExists,
                'customer_masked' => $maskedName,
                'is_real_work' => true,
                'work_completed' => true
            ];
        }
    }
    
    // 페이지네이션 정보 계산
    $totalPages = $totalCount > 0 ? ceil($totalCount / $perPage) : 1;
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    // JSON 응답 (디버깅 정보 포함)
    echo json_encode([
        'success' => true,
        'data' => $images,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_count' => intval($totalCount),
            'total_pages' => $totalPages,
            'has_next' => $hasNext,
            'has_prev' => $hasPrev,
            'next_page' => $hasNext ? $page + 1 : null,
            'prev_page' => $hasPrev ? $page - 1 : null
        ],
        'count' => count($images),
        'source' => 'real_orders_portfolio',
        'category' => $category,
        'category_type' => $dbTypes,
        'version' => '3.0',
        'description' => '실제 완성된 주문 작업물 포트폴리오',
        'note' => '매일 작업해서 완성한 현실감 있는 실제 작업물들',
        'debug' => [
            'processed_count' => $processedCount,
            'where_clause' => $whereClause,
            'sample_data' => array_slice($debugInfo, 0, 3)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => [],
        'source' => 'real_orders_portfolio',
        'version' => '3.0'
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if ($db) {
        mysqli_close($db);
    }
}
?>