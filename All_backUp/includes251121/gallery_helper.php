<?php
/**
 * 갤러리 헬퍼 함수 v1.0
 * 갤러리 시스템 사용을 더욱 간단하게 만드는 유틸리티 함수들
 */

/**
 * 간편한 갤러리 렌더링 함수
 * 
 * @param string $product 제품 타입 (inserted, namecard, littleprint 등)
 * @param array $options 추가 옵션 (선택사항)
 * @return string 갤러리 HTML + 모달 HTML
 */
function render_product_gallery($product, $options = []) {
    // 기본 설정
    $defaults = [
        'thumbCount' => 4,
        'modalPerPage' => 12,
        'enableModal' => true,
        'randomize' => true,
        'mainSize' => [500, 400],  // 500×400으로 설정
        'thumbSize' => [80, 80],
        'thumbCols' => 4
    ];
    
    $config = array_merge($defaults, $options);
    
    // 갤러리 데이터 로드
    $galleryItems = load_gallery_items(
        $product, 
        null, 
        $config['thumbCount'], 
        $config['modalPerPage']
    );
    
    // 고유 ID 생성
    $galleryId = $product . '-gallery';
    
    // 갤러리 렌더링 옵션
    $renderOptions = [
        'product' => $product,
        'thumbCols' => $config['thumbCols'],
        'mainSize' => $config['mainSize'],
        'thumbSize' => $config['thumbSize'],
        'enableModal' => $config['enableModal'],
        'randomize' => $config['randomize']
    ];
    
    // HTML 출력
    $output = render_gallery($galleryId, $galleryItems, $renderOptions);
    
    // 모달도 함께 포함 (한 번만 포함되도록 체크)
    if ($config['enableModal'] && !defined('GALLERY_MODAL_INCLUDED')) {
        $output .= render_gallery_modal();
        define('GALLERY_MODAL_INCLUDED', true);
    }
    
    return $output;
}

/**
 * 제품별 갤러리 설정 가져오기
 * 
 * @param string $product 제품 타입
 * @return array 제품별 맞춤 설정
 */
function get_product_gallery_config($product) {
    $configs = [
        'inserted' => [
            'thumbCount' => 4,
            'modalPerPage' => 12,
            'mainSize' => [500, 400],  // 500×400으로 변경
            'thumbSize' => [80, 80]
        ],
        'namecard' => [
            'thumbCount' => 4,
            'modalPerPage' => 12,
            'mainSize' => [500, 400],  // 500×400으로 변경
            'thumbSize' => [80, 80]
        ],
        'littleprint' => [
            'thumbCount' => 4,
            'modalPerPage' => 16,  // 포스터는 더 많이 표시
            'mainSize' => [500, 400],  // 500×400으로 변경
            'thumbSize' => [80, 80]
        ],
        'merchandisebond' => [
            'thumbCount' => 4,
            'modalPerPage' => 12,
            'mainSize' => [500, 400],  // 500×400으로 변경
            'thumbSize' => [80, 80]
        ],
        'envelope' => [
            'thumbCount' => 4,
            'modalPerPage' => 12,
            'mainSize' => [500, 400],  // 500×400으로 변경
            'thumbSize' => [80, 80]
        ],
        'cadarok' => [
            'thumbCount' => 4,
            'modalPerPage' => 12,
            'mainSize' => [500, 400],  // 500×400으로 변경
            'thumbSize' => [80, 80]
        ],
        'ncrflambeau' => [
            'thumbCount' => 4,
            'modalPerPage' => 12,
            'mainSize' => [500, 400],  // 500×400으로 변경
            'thumbSize' => [80, 80]
        ],
        'msticker' => [
            'thumbCount' => 4,
            'modalPerPage' => 12,
            'mainSize' => [500, 400],  // 500×400으로 변경
            'thumbSize' => [80, 80]
        ],
        'sticker' => [
            'thumbCount' => 4,
            'modalPerPage' => 12,  // 팝업에서 4×3 그리드
            'mainSize' => [500, 400],  // 500×400으로 변경
            'thumbSize' => [80, 80]
        ]
    ];
    
    return isset($configs[$product]) ? $configs[$product] : $configs['inserted'];
}

/**
 * 원클릭 갤러리 포함 함수
 * 단 한 줄로 제품 갤러리를 완전히 설정
 * 
 * @param string $product 제품 타입
 * @param array $customOptions 커스텀 옵션 (선택사항)
 */
function include_product_gallery($product, $customOptions = []) {
    // 공통 설정 확인
    if (!defined('GALLERY_UNIFIED')) {
        define('GALLERY_UNIFIED', true);
    }
    
    // 갤러리 에셋 자동 포함
    include_gallery_assets();
    
    // 필수 파일 포함 확인
    if (!function_exists('load_gallery_items')) {
        include_once __DIR__ . '/gallery_data_adapter.php';
    }
    
    if (!function_exists('render_gallery')) {
        include_once __DIR__ . '/gallery_component.php';
    }
    
    // 제품별 기본 설정 로드
    $productConfig = get_product_gallery_config($product);
    
    // 커스텀 옵션 병합
    $finalConfig = array_merge($productConfig, $customOptions);
    
    // 갤러리 렌더링 및 출력
    echo render_product_gallery($product, $finalConfig);
}

/**
 * 갤러리 CSS/JS 자동 포함 함수
 */
function include_gallery_assets() {
    static $included = false;
    
    if ($included) return;
    
    // 현재 위치를 기준으로 상대 경로 계산
    $assetsPath = '../../assets';
    if (strpos($_SERVER['REQUEST_URI'], '/mlangprintauto/') !== false) {
        $assetsPath = '../../assets';
    } elseif (strpos($_SERVER['REQUEST_URI'], '/includes/') !== false) {
        $assetsPath = '../assets';
    }
    
    echo '<link rel="stylesheet" href="' . $assetsPath . '/css/gallery.css">' . "\n";
    echo '<script src="' . $assetsPath . '/js/gallery.js" defer></script>' . "\n";
    
    $included = true;
}

/**
 * 갤러리 초기화 헬퍼 (페이지 헤더용)
 * 
 * @param string $product 제품 타입
 */
function init_gallery_system($product) {
    // 갤러리 활성화
    if (!defined('GALLERY_UNIFIED')) {
        define('GALLERY_UNIFIED', true);
    }
    
    // 필수 파일들 포함
    include_once __DIR__ . '/gallery_data_adapter.php';
    include_once __DIR__ . '/gallery_component.php';
    
    // 에셋 자동 포함을 위한 플래그 설정
    if (!defined('GALLERY_ASSETS_NEEDED')) {
        define('GALLERY_ASSETS_NEEDED', $product);
    }
}

/**
 * 제품명을 한글로 변환
 */
function get_product_korean_name($product) {
    $names = [
        'inserted' => '전단지',
        'namecard' => '명함',  
        'littleprint' => '포스터',
        'merchandisebond' => '상품권',
        'envelope' => '봉투',
        'cadarok' => '카탈로그',
        'ncrflambeau' => '양식지',
        'msticker' => '자석스티커'
    ];
    
    return isset($names[$product]) ? $names[$product] : $product;
}

/**
 * 갤러리 상태 확인 함수 (디버깅용)
 */
function debug_gallery_status($product) {
    if (!defined('GALLERY_DEBUG') || !GALLERY_DEBUG) return;
    
    echo "<!-- 갤러리 디버그 정보 -->\n";
    echo "<!-- 제품: {$product} (" . get_product_korean_name($product) . ") -->\n";
    echo "<!-- GALLERY_UNIFIED: " . (defined('GALLERY_UNIFIED') ? 'true' : 'false') . " -->\n";
    echo "<!-- 데이터 어댑터: " . (function_exists('load_gallery_items') ? 'loaded' : 'missing') . " -->\n";
    echo "<!-- 렌더링 컴포넌트: " . (function_exists('render_gallery') ? 'loaded' : 'missing') . " -->\n";
    echo "<!-- 모달 포함됨: " . (defined('GALLERY_MODAL_INCLUDED') ? 'true' : 'false') . " -->\n";
}

/**
 * 갤러리 통계 정보 (관리자용)
 */
function get_gallery_stats($product) {
    global $db;
    
    if (!$db) return null;
    
    // 데이터 어댑터 로드 확인
    if (!function_exists('load_gallery_items')) {
        include_once __DIR__ . '/gallery_data_adapter.php';
    }
    
    // 통계 수집
    $items = load_gallery_items($product, null, 100, 100); // 최대 100개 체크
    
    $stats = [
        'product' => $product,
        'korean_name' => get_product_korean_name($product),
        'total_items' => count($items),
        'a_path_count' => 0,
        'b_path_count' => 0,
        'placeholder_count' => 0,
        'working_images' => 0
    ];
    
    foreach ($items as $item) {
        if (isset($item['type'])) {
            switch ($item['type']) {
                case 'A':
                    $stats['a_path_count']++;
                    break;
                case 'B':
                    $stats['b_path_count']++;
                    break;
                case 'placeholder':
                    $stats['placeholder_count']++;
                    break;
            }
        }
        
        // 실제 파일 존재 여부 체크
        if (isset($item['exists']) && $item['exists']) {
            $stats['working_images']++;
        }
    }
    
    return $stats;
}
?>