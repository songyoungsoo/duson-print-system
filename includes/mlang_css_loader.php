<?php
/**
 * 🌟 MlangPrintAuto 스마트 CSS 로딩 시스템
 * ✨ 품목별로 필요한 CSS만 선택적으로 로드하는 지능형 시스템
 * 🔄 하나의 통합 CSS로 모든 품목을 관리하되, 테마는 자동 적용
 * 
 * 사용법:
 * include "../../includes/mlang_css_loader.php";
 * load_mlang_css('namecard'); // 품목명 전달
 */

/**
 * 🎯 메인 CSS 로딩 함수
 * @param string $product_type 품목 타입 (namecard, msticker, cadarok 등)
 * @param array $options 추가 옵션 (debug, performance, custom_css 등)
 */
function load_mlang_css($product_type = 'default', $options = []) {
    // 🔧 옵션 기본값 설정
    $options = array_merge([
        'debug' => false,
        'performance' => true,
        'custom_css' => true,
        'legacy_support' => false
    ], $options);
    
    // 📝 품목 타입 검증 및 표준화
    $product_type = validate_product_type($product_type);
    
    // 🎨 HTML body 태그에 테마 속성 추가 (JavaScript로 처리)
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            document.body.setAttribute("data-theme", "' . $product_type . '");
            document.body.classList.add("mlang-theme-' . $product_type . '");
            document.body.classList.add("mlang-design-system-loaded");
        });
    </script>' . "\n";
    
    // 🚀 성능 최적화: Critical CSS 인라인 삽입
    if ($options['performance']) {
        echo get_critical_css();
    }
    
    // 📦 메인 통합 CSS 로드
    echo '<link rel="stylesheet" href="../../css/mlang-design-system.css">' . "\n";
    
    // 📋 기존 공통 CSS (호환성 유지)
    if ($options['legacy_support']) {
        echo '<link rel="stylesheet" href="../../css/page-title-common.css">' . "\n";
        // standard-gallery.css removed - unified-gallery.css is now the standard
    }
    
    // 🎭 품목별 추가 커스텀 CSS (선택적 로드)
    if ($options['custom_css']) {
        load_custom_css($product_type);
    }
    
    // 🔧 디버그 모드
    if ($options['debug']) {
        echo '<style>
            .mlang-design-system-loaded::after {
                display: block !important;
                content: "🎯 Theme: ' . $product_type . ' | Debug Mode";
            }
        </style>' . "\n";
    }
    
    // 📊 사용 통계 (선택적)
    if (function_exists('track_css_usage')) {
        track_css_usage($product_type);
    }
}

/**
 * 🔍 품목 타입 검증 및 표준화
 * @param string $type 입력된 품목 타입
 * @return string 표준화된 품목 타입
 */
function validate_product_type($type) {
    // 📋 지원되는 품목 타입 목록
    $valid_types = [
        'namecard' => 'namecard',
        'msticker' => 'msticker', 
        'cadarok' => 'cadarok',
        'envelope' => 'envelope',
        'merchandisebond' => 'merchandisebond',
        'ncrflambeau' => 'ncrflambeau',
        'poster' => 'poster',
        'sticker' => 'sticker',
        'leaflet' => 'leaflet',
        'inserted' => 'leaflet',  // 전단지는 leaflet으로 표준화
        'littleprint' => 'poster', // 소량인쇄는 poster와 유사
        
        // 🔄 별칭 지원 (기존 호환성)
        '명함' => 'namecard',
        '자석스티커' => 'msticker',
        '카다록' => 'cadarok',
        '봉투' => 'envelope',
        '상품권' => 'merchandisebond',
        '양식지' => 'ncrflambeau',
        '포스터' => 'poster',
        '스티커' => 'sticker',
        '전단지' => 'leaflet'
    ];
    
    // 🎯 타입 표준화
    $type = strtolower($type);
    
    if (isset($valid_types[$type])) {
        return $valid_types[$type];
    }
    
    // ❓ 알 수 없는 타입의 경우 기본값 반환
    return 'default';
}

/**
 * ⚡ Critical CSS 인라인 삽입 (성능 최적화)
 * @return string Critical CSS 문자열
 */
function get_critical_css() {
    return '
<style>
/* 🚀 Critical CSS - Above the fold 성능 최적화 */
:root {
  --page-title-bg-start: #e0e0e0;
  --page-title-bg-end: #d0d0d0;
  --page-title-text: #333;
  --spacing-md: 1rem;
  --border-radius-xl: 15px;
  --transition-base: 0.3s ease;
  --font-family-primary: "Noto Sans KR", "Malgun Gothic", sans-serif;
}

body {
  font-family: var(--font-family-primary);
  line-height: 1.6;
  color: #333;
  background-color: #f5f5f5;
  margin: 0;
  padding: 0;
}

.mlang-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: var(--spacing-md);
  background: white;
  border-radius: var(--border-radius-xl);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.mlang-grid {
  display: grid;
  gap: 1.5rem;
}

.mlang-grid--two-columns {
  grid-template-columns: 1fr 1fr;
  align-items: start;
}

.page-title {
  background: linear-gradient(135deg, var(--page-title-bg-start) 0%, var(--page-title-bg-end) 100%) !important;
  color: var(--page-title-text) !important;
  padding: 30px 0 !important;
  text-align: center !important;
  margin-bottom: 30px !important;
  border-radius: var(--border-radius-xl) !important;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
}

@media (max-width: 1024px) {
  .mlang-grid--two-columns {
    grid-template-columns: 1fr !important;
  }
}
</style>
';
}

/**
 * 🎨 품목별 커스텀 CSS 로드
 * @param string $product_type 품목 타입
 */
function load_custom_css($product_type) {
    // 🎭 품목별 추가 CSS 파일 경로 정의
    $custom_css_paths = [
        'namecard' => 'css/namecard-addon.css',
        'msticker' => 'css/msticker-addon.css',
        'cadarok' => 'css/cadarok-addon.css',
        'envelope' => 'css/envelope-addon.css',
        'merchandisebond' => 'css/merchandisebond-addon.css',
        'ncrflambeau' => 'css/ncrflambeau-addon.css',
        'poster' => 'css/poster-addon.css',
        'sticker' => 'css/sticker-addon.css',
        'leaflet' => 'css/leaflet-addon.css'
    ];
    
    // 🔍 해당 품목의 커스텀 CSS 파일이 존재하는지 확인
    if (isset($custom_css_paths[$product_type])) {
        $css_path = '../../' . $custom_css_paths[$product_type];
        
        // 📁 파일 존재 여부 확인
        $full_path = $_SERVER['DOCUMENT_ROOT'] . '/css/' . basename($custom_css_paths[$product_type]);
        
        if (file_exists($full_path)) {
            echo '<link rel="stylesheet" href="' . $css_path . '">' . "\n";
        }
    }
}

/**
 * 🎯 품목별 테마 색상 정보 가져오기
 * @param string $product_type 품목 타입
 * @return array 테마 색상 배열
 */
function get_theme_colors($product_type) {
    $themes = [
        'namecard' => [
            'primary' => '#4a5568',
            'secondary' => '#2d3748',
            'accent' => '#667eea'
        ],
        'msticker' => [
            'primary' => '#2c5aa0',
            'secondary' => '#1e3a8a', 
            'accent' => '#3b82f6'
        ],
        'cadarok' => [
            'primary' => '#805ad5',
            'secondary' => '#553c9a',
            'accent' => '#9f7aea'
        ],
        'envelope' => [
            'primary' => '#38a169',
            'secondary' => '#2f855a',
            'accent' => '#48bb78'
        ],
        'merchandisebond' => [
            'primary' => '#e91e63',
            'secondary' => '#ad1457',
            'accent' => '#f06292'
        ],
        'ncrflambeau' => [
            'primary' => '#f56500',
            'secondary' => '#ea580c',
            'accent' => '#fb923c'
        ],
        'poster' => [
            'primary' => '#7c3aed',
            'secondary' => '#5b21b6',
            'accent' => '#8b5cf6'
        ],
        'sticker' => [
            'primary' => '#dc2626',
            'secondary' => '#991b1b',
            'accent' => '#ef4444'
        ],
        'leaflet' => [
            'primary' => '#059669',
            'secondary' => '#047857',
            'accent' => '#10b981'
        ]
    ];
    
    return $themes[$product_type] ?? $themes['namecard'];
}

/**
 * 📊 간단한 HTML 구조 헬퍼 함수들
 */

/**
 * 🏠 메인 컨테이너 시작 태그 생성
 * @param string $product_type 품목 타입
 * @return string HTML 태그
 */
function mlang_container_start($product_type = 'default') {
    return '<div class="mlang-container" data-product="' . $product_type . '">';
}

/**
 * 🏠 메인 컨테이너 종료 태그 생성
 * @return string HTML 태그
 */
function mlang_container_end() {
    return '</div>';
}

/**
 * 📊 그리드 시작 태그 생성
 * @param bool $two_columns 2열 그리드 여부
 * @return string HTML 태그
 */
function mlang_grid_start($two_columns = true) {
    $classes = 'mlang-grid';
    if ($two_columns) {
        $classes .= ' mlang-grid--two-columns mlang-grid--equal-height';
    }
    return '<div class="' . $classes . '">';
}

/**
 * 📊 그리드 종료 태그 생성
 * @return string HTML 태그
 */
function mlang_grid_end() {
    return '</div>';
}

/**
 * 🖼️ 갤러리 컴포넌트 HTML 생성
 * @param string $product_type 품목 타입
 * @param array $options 갤러리 옵션
 * @return string HTML 문자열
 */
function mlang_gallery_html($product_type, $options = []) {
    $options = array_merge([
        'title' => '제품 갤러리',
        'viewer_id' => 'mainViewer',
        'thumbnails_id' => 'thumbnailStrip'
    ], $options);
    
    return '
    <div class="mlang-gallery" data-component="gallery" data-product="' . $product_type . '">
        <h3 class="mlang-gallery__title">' . $options['title'] . '</h3>
        <div class="mlang-gallery__viewer" id="' . $options['viewer_id'] . '"></div>
        <div class="mlang-gallery__thumbnails" id="' . $options['thumbnails_id'] . '">
            <!-- 썸네일 동적 생성 -->
        </div>
    </div>';
}

/**
 * 🧮 계산기 컴포넌트 HTML 생성
 * @param string $product_type 품목 타입  
 * @param array $options 계산기 옵션
 * @return string HTML 문자열
 */
function mlang_calculator_html($product_type, $options = []) {
    $options = array_merge([
        'title' => '실시간 견적 계산',
        'subtitle' => '옵션을 선택하시면 실시간으로 가격이 계산됩니다'
    ], $options);
    
    return '
    <div class="mlang-calculator" data-component="calculator" data-product="' . $product_type . '">
        <div class="mlang-calculator__header">
            <h3>' . $options['title'] . '</h3>
            <p class="calculator-subtitle">' . $options['subtitle'] . '</p>
        </div>
        
        <div class="mlang-calculator__options">
            <!-- 옵션 필드들이 여기에 동적으로 추가됩니다 -->
        </div>
        
        <div class="mlang-price" data-component="price-display">
            <div class="mlang-price__label">예상 금액</div>
            <div class="mlang-price__amount" id="priceAmount">견적을 위해 옵션을 선택해주세요</div>
            <div class="mlang-price__details" id="priceDetails"></div>
        </div>
        
        <button class="mlang-btn mlang-btn--primary" data-action="upload-order">
            📎 파일업로드 주문
        </button>
    </div>';
}

/**
 * 📈 사용 통계 추적 (선택적 기능)
 * @param string $product_type 품목 타입
 */
function track_css_usage($product_type) {
    // 🔧 로그 파일 경로
    $log_file = $_SERVER['DOCUMENT_ROOT'] . '/logs/css-usage.log';
    
    // 📊 사용 정보
    $usage_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'product_type' => $product_type,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
    
    // 📝 로그 기록 (디렉토리가 존재하는 경우만)
    if (is_dir(dirname($log_file)) && is_writable(dirname($log_file))) {
        file_put_contents($log_file, json_encode($usage_data) . "\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * 🔧 유틸리티: 현재 사용 중인 품목 타입 감지
 * @return string 감지된 품목 타입
 */
function detect_current_product_type() {
    // 🔍 URL 경로에서 품목 타입 감지
    $current_path = $_SERVER['REQUEST_URI'] ?? '';
    
    $path_patterns = [
        '/namecard/' => 'namecard',
        '/msticker/' => 'msticker', 
        '/cadarok/' => 'cadarok',
        '/envelope/' => 'envelope',
        '/merchandisebond/' => 'merchandisebond',
        '/ncrflambeau/' => 'ncrflambeau',
        '/poster/' => 'poster',
        '/sticker/' => 'sticker',
        '/inserted/' => 'leaflet',
        '/littleprint/' => 'poster'
    ];
    
    foreach ($path_patterns as $pattern => $type) {
        if (strpos($current_path, $pattern) !== false) {
            return $type;
        }
    }
    
    return 'default';
}

/**
 * 🎉 간편 사용 함수 - 자동 감지
 * 품목 타입을 자동으로 감지하여 CSS를 로드합니다
 * @param array $options 추가 옵션
 */
function load_mlang_css_auto($options = []) {
    $detected_type = detect_current_product_type();
    load_mlang_css($detected_type, $options);
}

/**
 * 🛠️ 개발자 도구: CSS 시스템 정보 출력
 * @return array 시스템 정보 배열
 */
function get_mlang_css_info() {
    return [
        'system_name' => 'MlangPrintAuto Design System',
        'version' => '1.0.0',
        'css_file' => 'mlang-design-system.css',
        'supported_products' => [
            'namecard', 'msticker', 'cadarok', 'envelope', 
            'merchandisebond', 'ncrflambeau', 'poster', 'sticker', 'leaflet'
        ],
        'features' => [
            '통합 CSS 시스템',
            '품목별 테마 지원', 
            '반응형 디자인',
            '성능 최적화',
            '접근성 지원'
        ]
    ];
}

?>

<!-- 🎉 MlangPrintAuto CSS 로딩 시스템 로드 완료 -->
<!-- ✨ 이제 load_mlang_css('품목명')으로 간단하게 사용하세요! -->

<!-- 
📖 사용 예시:

기본 사용법:
<?php load_mlang_css('namecard'); ?>

고급 옵션 사용:
<?php 
load_mlang_css('msticker', [
    'debug' => true,
    'performance' => true,
    'custom_css' => true,
    'legacy_support' => false
]); 
?>

자동 감지 사용:
<?php load_mlang_css_auto(); ?>

HTML 헬퍼 사용:
<?php echo mlang_container_start('namecard'); ?>
<?php echo mlang_grid_start(true); ?>
<?php echo mlang_gallery_html('namecard'); ?>
<?php echo mlang_calculator_html('namecard'); ?>
<?php echo mlang_grid_end(); ?>
<?php echo mlang_container_end(); ?>
-->