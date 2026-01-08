<?php
/**
 * 카다록/리플렛 설정 파일
 * 공통 설정 및 상수 정의
 * Created: 2025-09-03
 */

// 페이지 설정
define('CADAROK_PAGE', 'cadarok');
define('CADAROK_PAGE_TITLE', '📝 카다록/리플렛 견적안내 - 프리미엄');
define('CADAROK_CURRENT_PAGE', 'cadarok');

// 데이터베이스 테이블
define('CADAROK_CATEGORY_TABLE', 'mlangprintauto_transactioncate');
define('CADAROK_PRICE_TABLE', 'mlangprintauto_cadarok');

// 기본값 설정
$cadarok_defaults = [
    'MY_type' => '',        // 카다록 종류 (동적으로 설정됨)
    'Section' => '',        // 재질 (동적으로 설정됨)
    'POtype' => '1',        // 단면 기본
    'MY_amount' => '',      // 수량 기본값 (동적으로 설정됨)
    'ordertype' => 'print'  // 인쇄만 기본
];

// 파일 업로드 설정
$cadarok_upload_config = [
    'allowed_types' => ['pdf', 'ai', 'psd', 'jpg', 'jpeg', 'png', 'gif', 'eps'],
    'max_file_size' => 50 * 1024 * 1024, // 50MB
    'upload_path' => '../../uploads/',
];

// 가격 계산 설정
$cadarok_price_config = [
    'vat_rate' => 0.1,  // 부가세 10%
    'design_fee_basic' => 10000,   // 기본 편집비
    'design_fee_premium' => 30000, // 고급 편집비
];

// 제품 설명 데이터
$cadarok_product_info = [
    'catalog' => [
        'title' => '📝 카다록',
        'description' => '회사 또는 제품을 소개하는 전문적인 카다록입니다. 고급스러운 인쇄품질과 다양한 후가공 옵션으로 브랜드 이미지를 향상시킵니다.',
        'features' => [
            '고급 용지 옵션',
            '무료 편집디자인 지원',
            '다양한 사이즈 선택',
            '빠른 제작 및 배송',
            '대량 주문 할인'
        ],
        'color' => 'primary', // 보라색 테마
    ],
    'leaflet' => [
        'title' => '📄 리플렛',
        'description' => '정보 전달에 최적화된 리플렛으로 이벤트, 제품홍보, 안내 등 다양한 용도로 활용 가능합니다. 경제적인 가격으로 효과적인 홍보가 가능합니다.',
        'features' => [
            '경제적인 가격',
            '빠른 제작 시간',
            '다양한 접지 방식',
            '고품질 인쇄',
            '맞춤 디자인'
        ],
        'color' => 'info', // 파란색 테마
    ]
];

// AJAX 엔드포인트 설정
$cadarok_ajax_endpoints = [
    'get_sections' => 'get_paper_types.php',
    'get_quantities' => 'get_quantities.php', 
    'calculate_price' => 'calculate_price.php',
    'add_to_basket' => 'add_to_basket.php',
    'get_images' => 'get_cadarok_images.php',
];

// 에러 메시지
$cadarok_error_messages = [
    'db_connection' => '데이터베이스 연결에 실패했습니다.',
    'invalid_input' => '잘못된 입력값입니다.',
    'file_upload' => '파일 업로드에 실패했습니다.',
    'price_calculation' => '가격 계산 중 오류가 발생했습니다.',
    'add_to_cart' => '장바구니 담기에 실패했습니다.',
    'no_category' => '카다록 종류를 찾을 수 없습니다.',
    'no_section' => '재질 정보를 찾을 수 없습니다.',
    'no_quantity' => '수량 정보를 찾을 수 없습니다.',
];

// 성공 메시지
$cadarok_success_messages = [
    'add_to_cart' => '장바구니에 성공적으로 담았습니다.',
    'file_upload' => '파일이 성공적으로 업로드되었습니다.',
    'order_complete' => '주문이 완료되었습니다.',
    'price_calculated' => '견적이 계산되었습니다.',
];

// 브랜드 색상 설정 (보라색 계열)
$cadarok_brand_colors = [
    'primary' => '#6f42c1',    // 보라색 (메인)
    'secondary' => '#5a3a9a',  // 진한 보라색 (서브)
    'success' => '#28a745',    // 녹색 (성공)
    'warning' => '#ffc107',    // 노란색 (경고)
    'danger' => '#dc3545',     // 빨간색 (오류)
];

// 개인정보 보호 설정
$cadarok_privacy_settings = [
    'mask_personal_info' => true,         // 개인정보 마스킹 활성화
    'mask_area_width' => '40%',           // 마스킹 영역 너비
    'mask_area_height' => '35%',          // 마스킹 영역 높이
    'blur_intensity' => '6px',            // 블러 강도
];

// 메타 태그 설정
$cadarok_meta = [
    'description' => '두손기획인쇄 카다록/리플렛 견적 시스템 - 실시간 가격계산, 다양한 재질 옵션, 빠른 제작',
    'keywords' => '카다록, 리플렛, 카다록제작, 리플렛인쇄, 회사소개서, 두손기획인쇄',
    'author' => '두손기획인쇄',
];

// 갤러리 설정
$cadarok_gallery_config = [
    'images_per_page' => 12,
    'thumbnail_size' => '250x200',
    'lightbox_enabled' => true,
    'lazy_loading' => true,
];

// JavaScript 설정
$cadarok_js_config = [
    'auto_calculation' => true,
    'file_validation' => true,
    'ajax_timeout' => 30000, // 30초
    'animation_duration' => 300, // 0.3초
];
?>