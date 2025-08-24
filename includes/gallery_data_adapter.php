<?php
/**
 * 갤러리 데이터 어댑터 v1.0
 * A경로(MlangOrder_PrintAuto) 우선, B경로(레거시) 폴백
 * 
 * 우선순위:
 * 1. MlangOrder_PrintAuto 테이블의 실제 주문 이미지
 * 2. 레거시 폴더 (/ImgFolder/{product}/gallery/)
 * 3. 플레이스홀더 이미지
 */

/**
 * 갤러리 아이템 로드
 * 
 * @param string $product 품목 타입 (inserted, namecard, littleprint 등)
 * @param int|null $orderNo 특정 주문번호 (null이면 전체)
 * @param int $thumbCount 썸네일 개수
 * @param int $modalPerPage 모달 페이지당 아이템 수
 * @return array 이미지 아이템 배열
 */
function load_gallery_items($product, $orderNo = null, $thumbCount = 4, $modalPerPage = 12) {
    global $db;
    $items = [];
    
    // 스티커는 전용 통합 시스템 사용 (A/B/C 경로)
    if ($product === 'sticker') {
        return load_sticker_gallery_unified($thumbCount, $modalPerPage);
    }
    
    // 품목 타입 매핑 (Type 필드 값)
    $productTypeMap = [
        'inserted' => ['전단지', 'inserted'],
        'namecard' => ['명함', 'namecard'],
        'littleprint' => ['포스터', 'littleprint'],
        'merchandisebond' => ['상품권', 'merchandisebond', '쿠폰'],
        'envelope' => ['봉투', 'envelope'],
        'cadarok' => ['카탈로그', 'cadarok', '카다록'],
        'ncrflambeau' => ['양식지', 'ncrflambeau', 'NCR'],
        'msticker' => ['자석스티커', 'msticker', '자석']
    ];
    
    // A경로: MlangOrder_PrintAuto 테이블 조회
    if (isset($productTypeMap[$product])) {
        $typeConditions = array_map(function($type) {
            return "Type LIKE '%" . $type . "%'";
        }, $productTypeMap[$product]);
        $typeWhere = "(" . implode(" OR ", $typeConditions) . ")";
        
        // 특정 주문번호가 있으면 해당 주문만
        if ($orderNo !== null) {
            $query = "SELECT no, ThingCate, ImgFolder, Type, name 
                      FROM MlangOrder_PrintAuto 
                      WHERE no = ? 
                      AND ThingCate IS NOT NULL 
                      AND ThingCate != ''
                      AND date >= '2018-01-01'
                      LIMIT ?";
            
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ii", $orderNo, $modalPerPage);
        } else {
            // 전체 주문에서 랜덤 선택 (2024년 1월 이후만)
            $query = "SELECT no, ThingCate, ImgFolder, Type, name 
                      FROM MlangOrder_PrintAuto 
                      WHERE $typeWhere 
                      AND ThingCate IS NOT NULL 
                      AND ThingCate != ''
                      AND LENGTH(ThingCate) > 3
                      AND date >= '2020-01-01'
                      ORDER BY RAND()
                      LIMIT ?";
            
            // 파일이 없는 경우를 고려해서 10배 많이 가져옴 (스티커 파일 존재율 15% 고려)  
            $fetchLimit = $modalPerPage * 10;
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "i", $fetchLimit);
        }
        
        if ($stmt && mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                // A경로 패턴: /MlangOrder_PrintAuto/upload/{주문번호}/{파일명}
                $imagePath = "/MlangOrder_PrintAuto/upload/{$row['no']}/{$row['ThingCate']}";
                
                // 경로 검증 (보안)
                if (validate_image_path($imagePath)) {
                    // 실제 파일 존재 확인
                    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
                    $fileExists = file_exists($fullPath);
                    
                    // 파일이 실제로 존재하는 것만 추가
                    if ($fileExists) {
                        // 고객명 마스킹
                        $maskedName = !empty($row['name']) ? mb_substr($row['name'], 0, 1) . '***' : '고객';
                        
                        $items[] = [
                            'src' => $imagePath,
                            'alt' => "{$maskedName}님 주문 샘플",
                            'orderNo' => $row['no'],
                            'type' => 'A', // A경로 표시
                            'exists' => true
                        ];
                    }
                }
            }
            mysqli_stmt_close($stmt);
            
            // 썸네일용과 모달용을 모두 포함할 수 있도록 충분히 확보
            $maxNeeded = max($thumbCount, $modalPerPage);
            if (count($items) > $maxNeeded) {
                $items = array_slice($items, 0, $maxNeeded);
            }
        }
    }
    
    // B경로: 레거시 폴백 (A경로에 충분한 데이터 없을 때만)
    // 스티커는 항상 정적 이미지 우선 사용
    if (empty($items) || $product === 'sticker') {
        $legacyPath = "/ImgFolder/{$product}/gallery/";
        
        // 절대 경로 사용 (DOCUMENT_ROOT 문제 해결)
        $possiblePaths = [
            $_SERVER['DOCUMENT_ROOT'] . $legacyPath,
            "C:\\xampp\\htdocs" . $legacyPath,
            realpath(__DIR__ . "/.." . $legacyPath)
        ];
        
        $realPath = null;
        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                $realPath = $path;
                break;
            }
        }
        
        if ($realPath && is_dir($realPath)) {
            $files = glob($realPath . "*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
            
            if (!empty($files)) {
                // 스티커는 파일명 순으로 정렬 (일관성 위해)
                if ($product === 'sticker') {
                    sort($files);
                } else {
                    shuffle($files); // 다른 제품은 랜덤
                }
                
                // 스티커는 모든 파일, 다른 제품은 제한
                $fileLimit = ($product === 'sticker') ? count($files) : $modalPerPage;
                foreach (array_slice($files, 0, $fileLimit) as $file) {
                    $filename = basename($file);
                    if (validate_filename($filename)) {
                        $items[] = [
                            'src' => $legacyPath . $filename,
                            'alt' => pathinfo($filename, PATHINFO_FILENAME),
                            'type' => 'B' // B경로 표시
                        ];
                    }
                }
            }
        }
    }
    
    // 플레이스홀더 (빈 케이스 처리)
    if (empty($items)) {
        for ($i = 1; $i <= 4; $i++) {
            $items[] = [
                'src' => '/assets/images/placeholder.jpg',
                'alt' => '샘플 이미지 준비중',
                'type' => 'placeholder'
            ];
        }
    }
    
    return $items;
}

/**
 * AJAX용 갤러리 아이템 로드 (페이지네이션)
 */
function load_gallery_items_ajax($product, $page = 1, $perPage = 12) {
    global $db;
    $items = [];
    $totalCount = 0;
    
    $offset = ($page - 1) * $perPage;
    
    // 품목 타입 매핑
    $productTypeMap = [
        'inserted' => ['전단지', 'inserted'],
        'namecard' => ['명함', 'namecard'],
        'littleprint' => ['포스터', 'littleprint'],
        'merchandisebond' => ['상품권', 'merchandisebond', '쿠폰'],
        'envelope' => ['봉투', 'envelope'],
        'cadarok' => ['카탈로그', 'cadarok', '카다록'],
        'ncrflambeau' => ['양식지', 'ncrflambeau', 'NCR'],
        'msticker' => ['자석스티커', 'msticker', '자석']
    ];
    
    if (isset($productTypeMap[$product])) {
        $typeConditions = array_map(function($type) {
            return "Type LIKE '%" . $type . "%'";
        }, $productTypeMap[$product]);
        $typeWhere = "(" . implode(" OR ", $typeConditions) . ")";
        
        // 전체 카운트 (2024년 1월 이후만)
        $countQuery = "SELECT COUNT(*) as total 
                       FROM MlangOrder_PrintAuto 
                       WHERE $typeWhere 
                       AND ThingCate IS NOT NULL 
                       AND ThingCate != ''
                       AND date >= '2018-01-01'";
        $countResult = mysqli_query($db, $countQuery);
        if ($countResult) {
            $countRow = mysqli_fetch_assoc($countResult);
            $totalCount = $countRow['total'];
        }
        
        // 페이지 데이터 (2024년 1월 이후만)
        $query = "SELECT no, ThingCate, name 
                  FROM MlangOrder_PrintAuto 
                  WHERE $typeWhere 
                  AND ThingCate IS NOT NULL 
                  AND ThingCate != ''
                  AND date >= '2024-01-01'
                  ORDER BY no DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ii", $perPage, $offset);
        
        if ($stmt && mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $imagePath = "/MlangOrder_PrintAuto/upload/{$row['no']}/{$row['ThingCate']}";
                
                if (validate_image_path($imagePath)) {
                    $maskedName = !empty($row['name']) ? mb_substr($row['name'], 0, 1) . '***' : '고객';
                    
                    $items[] = [
                        'src' => $imagePath,
                        'alt' => "{$maskedName}님 주문 샘플"
                    ];
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // B경로 폴백
    if (empty($items)) {
        $items = load_legacy_items($product, $offset, $perPage);
        $totalCount = count_legacy_items($product);
    }
    
    return [
        'items' => $items,
        'currentPage' => $page,
        'totalPages' => ceil($totalCount / $perPage),
        'totalCount' => $totalCount
    ];
}

/**
 * 레거시 아이템 로드
 */
function load_legacy_items($product, $offset, $limit) {
    $items = [];
    $legacyPath = "/ImgFolder/{$product}/gallery/";
    $realPath = $_SERVER['DOCUMENT_ROOT'] . $legacyPath;
    
    if (is_dir($realPath)) {
        $files = glob($realPath . "*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
        $files = array_slice($files, $offset, $limit);
        
        foreach ($files as $file) {
            $filename = basename($file);
            if (validate_filename($filename)) {
                $items[] = [
                    'src' => $legacyPath . $filename,
                    'alt' => pathinfo($filename, PATHINFO_FILENAME)
                ];
            }
        }
    }
    
    return $items;
}

/**
 * 레거시 아이템 카운트
 */
function count_legacy_items($product) {
    $legacyPath = "/ImgFolder/{$product}/gallery/";
    $realPath = $_SERVER['DOCUMENT_ROOT'] . $legacyPath;
    
    if (is_dir($realPath)) {
        $files = glob($realPath . "*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
        return count($files);
    }
    
    return 0;
}

/**
 * 경로 검증 함수 (보안)
 */
function validate_image_path($path) {
    // 상위 디렉토리 접근 차단
    if (strpos($path, '..') !== false) {
        return false;
    }
    
    // NULL 바이트 차단
    if (strpos($path, "\0") !== false) {
        return false;
    }
    
    // 허용된 경로 패턴
    $allowedPatterns = [
        '/^\/MlangOrder_PrintAuto\/upload\/\d+\/[a-zA-Z0-9_\-\.]+\.(jpg|jpeg|png|gif|webp)$/i',
        '/^\/ImgFolder\/[a-z]+\/gallery\/[a-zA-Z0-9_\-\.]+\.(jpg|jpeg|png|gif|webp)$/i'
    ];
    
    foreach ($allowedPatterns as $pattern) {
        if (preg_match($pattern, $path)) {
            return true;
        }
    }
    
    return false;
}

/**
 * 파일명 검증 (화이트리스트)
 */
function validate_filename($filename) {
    // 확장자 확인 (대소문자 무관)
    if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $filename)) {
        return false;
    }
    
    // 길이 제한 (멀티바이트 문자 고려)
    if (mb_strlen($filename, 'UTF-8') > 255) {
        return false;
    }
    
    // 위험한 문자 차단 (보안용)
    if (preg_match('/[<>:"|?*\\\\\/]/', $filename)) {
        return false;
    }
    
    return true;
}

/**
 * 스티커 전용 통합 갤러리 시스템
 * A/B/C 경로 통합 (데이터베이스 + 정적파일 + 포트폴리오)
 */
function load_sticker_gallery_unified($thumbCount = 4, $modalPerPage = 12) {
    global $db;
    $items = [];
    
    // A경로: 데이터베이스 실제 주문 (동적 개수 제한)
    $max_items = max($thumbCount, $modalPerPage, 12);  // 요청된 개수만큼 로드
    
    $sticker_types = ['스티커', 'sticker', '일반스티커'];
    $typeConditions = array_map(function($type) {
        return "Type LIKE '%$type%'";
    }, $sticker_types);
    $typeWhere = "(" . implode(" OR ", $typeConditions) . ")";
    
    $query = "SELECT no, ThingCate, ImgFolder, Type, name 
              FROM MlangOrder_PrintAuto 
              WHERE $typeWhere 
              AND ThingCate IS NOT NULL 
              AND ThingCate != ''
              AND LENGTH(ThingCate) > 3
              AND date >= '2020-01-01'
              ORDER BY date DESC, no DESC
              LIMIT 200";  // 더 많은 후보 확보
    
    $result = mysqli_query($db, $query);
    if ($result) {
        $found_count = 0;
        while (($row = mysqli_fetch_assoc($result)) && $found_count < $max_items) {
            $imagePath = "/MlangOrder_PrintAuto/upload/{$row['no']}/{$row['ThingCate']}";
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
            
            if (file_exists($fullPath)) {
                $maskedName = !empty($row['name']) ? mb_substr($row['name'], 0, 1) . '***' : '고객';
                $items[] = [
                    'src' => $imagePath,
                    'alt' => "{$maskedName}님 스티커 주문 샘플",
                    'title' => "{$maskedName}님 스티커 주문",
                    'orderNo' => $row['no'],
                    'type' => 'real_orders'
                ];
                $found_count++;
            }
        }
    }
    
    // B경로: 정적 파일 (ImgFolder/sticker/gallery/)
    $possiblePaths = [
        $_SERVER['DOCUMENT_ROOT'] . "/ImgFolder/sticker/gallery/",
        "C:\\xampp\\htdocs\\ImgFolder\\sticker\\gallery\\",
        realpath(__DIR__ . "/../ImgFolder/sticker/gallery/")
    ];
    
    $sticker_gallery_dir = null;
    foreach ($possiblePaths as $path) {
        if (is_dir($path)) {
            $sticker_gallery_dir = $path;
            break;
        }
    }
    
    if ($sticker_gallery_dir) {
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $sticker_files = [];
        
        foreach ($image_extensions as $ext) {
            $files = glob($sticker_gallery_dir . "*." . $ext, GLOB_NOSORT);
            if ($files) {
                $sticker_files = array_merge($sticker_files, $files);
            }
        }
        
        if (!empty($sticker_files)) {
            usort($sticker_files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            foreach ($sticker_files as $file) {
                $filename = basename($file);
                $web_path = "/ImgFolder/sticker/gallery/" . $filename;
                $clean_title = str_replace(['_', '-'], ' ', pathinfo($filename, PATHINFO_FILENAME));
                if (mb_strlen($clean_title) > 30) {
                    $clean_title = mb_substr($clean_title, 0, 30) . '...';
                }
                
                $items[] = [
                    'src' => $web_path,
                    'alt' => $clean_title ?: '스티커 샘플',
                    'title' => $clean_title ?: '스티커 샘플',
                    'orderNo' => null,
                    'type' => 'sticker_gallery'
                ];
            }
        }
    }
    
    // C경로: 포트폴리오 게시판
    $portfolioQuery = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_file, Mlang_date 
                      FROM Mlang_portfolio_bbs 
                      WHERE CATEGORY LIKE '%스티커%' 
                      AND Mlang_bbs_file IS NOT NULL 
                      AND Mlang_bbs_file != '' 
                      AND Mlang_bbs_file NOT LIKE '%.gif'
                      ORDER BY Mlang_bbs_no DESC 
                      LIMIT 50";
    
    $portfolioResult = mysqli_query($db, $portfolioQuery);
    if ($portfolioResult) {
        while ($row = mysqli_fetch_assoc($portfolioResult)) {
            $possiblePortfolioPaths = [
                "/bbs/upload/portfolio/" . $row['Mlang_bbs_file'],
                "/bbs/data/portfolio/" . $row['Mlang_bbs_file'],
                "/bbs/upload/" . $row['Mlang_bbs_file']
            ];
            
            foreach ($possiblePortfolioPaths as $portfolioPath) {
                $serverPath = $_SERVER['DOCUMENT_ROOT'] . $portfolioPath;
                if (file_exists($serverPath)) {
                    $items[] = [
                        'src' => $portfolioPath,
                        'alt' => $row['Mlang_bbs_title'] ?: '포트폴리오 스티커',
                        'title' => $row['Mlang_bbs_title'] ?: '포트폴리오 스티커',
                        'orderNo' => $row['Mlang_bbs_no'],
                        'type' => 'portfolio'
                    ];
                    break;
                }
            }
        }
    }
    
    // 썸네일 개수만큼 반환 (메인갤러리용)
    return array_slice($items, 0, $thumbCount);
}
?>