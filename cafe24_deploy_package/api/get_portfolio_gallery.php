<?php
/**
 * 포트폴리오 갤러리 API - 팝업 갤러리용
 * 품목별 카테고리에 따른 포트폴리오 이미지 조회
 * Created: 2025년 8월 (AI Assistant)
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 데이터베이스 연결
include "../db.php";

try {
    // 파라미터 받기
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = isset($_GET['per_page']) ? max(1, min(50, intval($_GET['per_page']))) : 24;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // 제품별 카테고리 매핑 (파일명 패턴 기반)
    $category_mapping = [
        'sticker' => [
            'keywords' => ['sticker', '스티커', 'label', '라벨', 'stic'],
            'name' => '스티커'
        ],
        'namecard' => [
            'keywords' => ['명함', 'namecard', 'card', 'business'],
            'name' => '명함'
        ],
        'leaflet' => [
            'keywords' => ['leaflet', '전단지', 'flyer', 'leaf'],
            'name' => '전단지'
        ],
        'cadarok' => [
            'keywords' => ['catalog', '카달로그', '카다록', 'cata'],
            'name' => '카달로그'
        ],
        'envelope' => [
            'keywords' => ['envelope', '봉투', 'envel'],
            'name' => '봉투'
        ],
        'littleprint' => [
            'keywords' => ['poster', '포스터', 'print', 'little'],
            'name' => '포스터'
        ],
        'msticker' => [
            'keywords' => ['magnetic', 'magnet', '자석', 'mstick'],
            'name' => '자석스티커'
        ],
        'merchandisebond' => [
            'keywords' => ['coupon', '쿠폰', 'voucher', 'bond', '상품권'],
            'name' => '쿠폰/상품권'
        ],
        'ncrflambeau' => [
            'keywords' => ['form', '서식', 'ncr', '양식', '상장'],
            'name' => '서식/양식'
        ]
    ];
    
    // 카테고리별 이미지 분류 함수
    function categorizeImageByFilename($filename, $category_mapping) {
        $filename_lower = strtolower($filename);
        
        foreach ($category_mapping as $cat_key => $cat_data) {
            foreach ($cat_data['keywords'] as $keyword) {
                if (strpos($filename_lower, strtolower($keyword)) !== false) {
                    return [
                        'category' => $cat_key,
                        'name' => $cat_data['name'],
                        'keyword_matched' => $keyword
                    ];
                }
            }
        }
        
        return [
            'category' => 'other',
            'name' => '기타',
            'keyword_matched' => null
        ];
    }
    
    // 중복 제거 함수 (파일명 기반)
    function removeDuplicateImages($files) {
        $unique_files = [];
        $seen_names = [];
        
        foreach ($files as $file) {
            // 파일명에서 숫자와 특수문자 제거하여 기본 패턴 추출
            $base_name = preg_replace('/[0-9_\-\.\s]+/', '', strtolower($file['filename']));
            
            if (!in_array($base_name, $seen_names)) {
                $seen_names[] = $base_name;
                $unique_files[] = $file;
            }
        }
        
        return $unique_files;
    }
    
    $offset = ($page - 1) * $per_page;
    $images = [];
    $total_count = 0;
    
    // 카테고리 아이콘 함수
    function getCategoryIcon($category) {
        $icons = [
            'sticker' => '🏷️',
            'namecard' => '💳',
            'leaflet' => '📄',
            'cadarok' => '📖',
            'envelope' => '✉️',
            'littleprint' => '🖼️',
            'msticker' => '🧲',
            'merchandisebond' => '🎫',
            'ncrflambeau' => '📋',
            'other' => '📁'
        ];
        return $icons[$category] ?? '📁';
    }
    
    // 실제 포트폴리오 폴더에서 이미지 가져오기
    $portfolio_dir = "../bbs/upload/portfolio/";
    $web_portfolio_dir = "/bbs/upload/portfolio/";
    
    if (is_dir($portfolio_dir)) {
        // 포트폴리오 디렉토리의 모든 이미지 파일들 찾기
        $all_files = scandir($portfolio_dir);
        $categorized_files = [];
        
        foreach ($all_files as $file) {
            // 모든 이미지 파일 포함 (jpg, jpeg, png, gif, bmp)
            if (preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $file)) {
                $file_path = $portfolio_dir . $file;
                if (file_exists($file_path)) {
                    // 파일 카테고리 분류
                    $file_category = categorizeImageByFilename($file, $category_mapping);
                    
                    // 요청된 카테고리와 일치하는지 확인
                    if ($category === 'all' || $file_category['category'] === $category) {
                        $categorized_files[] = [
                            'filename' => $file,
                            'path' => $web_portfolio_dir . $file,
                            'mtime' => filemtime($file_path),
                            'filesize' => filesize($file_path),
                            'detected_category' => $file_category['category'],
                            'category_name' => $file_category['name'],
                            'keyword_matched' => $file_category['keyword_matched']
                        ];
                    }
                }
            }
        }
        
        // 중복 제거 적용
        $categorized_files = removeDuplicateImages($categorized_files);
        
        // 파일 수정시간 기준으로 최신순 정렬
        usort($categorized_files, function($a, $b) {
            return $b['mtime'] - $a['mtime'];
        });
        
        // 검색 필터링
        if (!empty($search)) {
            $categorized_files = array_filter($categorized_files, function($file) use ($search) {
                return stripos($file['filename'], $search) !== false || 
                       stripos($file['category_name'], $search) !== false ||
                       stripos($file['keyword_matched'] ?? '', $search) !== false;
            });
        }
        
        $total_count = count($categorized_files);
        
        // 페이지네이션 적용
        $paginated_files = array_slice($categorized_files, $offset, $per_page);
        
        foreach ($paginated_files as $index => $file_info) {
            $category_name = $file_info['category_name'] ?? '기타';
            $category_icon = getCategoryIcon($file_info['detected_category']);
            
            $images[] = [
                'id' => $file_info['detected_category'] . '_' . ($index + 1),
                'title' => $category_icon . ' ' . $category_name . ' 샘플 ' . ($index + 1),
                'category' => $category_name,
                'detected_category' => $file_info['detected_category'],
                'keyword_matched' => $file_info['keyword_matched'],
                'thumbnail' => $file_info['path'],
                'full_image' => $file_info['path'],
                'src' => $file_info['path'], // EnhancedImageLightbox 호환성
                'url' => $file_info['path'], // 호환성
                'path' => $file_info['path'], // 호환성
                'description' => $category_icon . ' ' . $category_name . ' 샘플 - ' . $file_info['filename'],
                'tags' => [$category_name, $file_info['detected_category'], $file_info['keyword_matched']],
                'upload_date' => date('Y-m-d H:i:s', $file_info['mtime']),
                'file_size' => $file_info['filesize'],
                'filename' => $file_info['filename']
            ];
        }
        
    }
    
    // 페이지네이션 정보 계산
    $total_pages = ceil($total_count / $per_page);
    $has_next = $page < $total_pages;
    $has_prev = $page > 1;
    
    // 응답 데이터
    echo json_encode([
        'success' => true,
        'data' => $images,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_count' => $total_count,
            'total_pages' => $total_pages,
            'has_next' => $has_next,
            'has_prev' => $has_prev
        ],
        'category' => $category,
        'search' => $search,
        'available_categories' => array_keys($category_mapping)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // 오류 응답
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => [],
        'pagination' => [
            'current_page' => 1,
            'per_page' => 24,
            'total_count' => 0,
            'total_pages' => 0,
            'has_next' => false,
            'has_prev' => false
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} finally {
    // 데이터베이스 연결 종료
    if (isset($db)) {
        mysqli_close($db);
    }
}
?>