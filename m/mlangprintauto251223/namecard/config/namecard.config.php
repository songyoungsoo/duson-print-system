<?php
/**
 * 명함(NameCard) 설정 파일
 * 공통 설정 및 상수 정의
 * Created: 2025-09-03
 */

// 페이지 설정
define('NAMECARD_PAGE', 'NameCard');
define('NAMECARD_PAGE_TITLE', '💳 두손기획인쇄 - 명함 컴팩트 견적');
define('NAMECARD_CURRENT_PAGE', 'namecard');

// 데이터베이스 테이블
define('NAMECARD_CATEGORY_TABLE', 'mlangprintauto_transactioncate');
define('NAMECARD_PRICE_TABLE', 'mlangprintauto_namecard');

// 기본값 설정
$namecard_defaults = [
    'MY_type' => '',        // 명함 종류 (동적으로 설정됨)
    'Section' => '',        // 재질 (동적으로 설정됨)
    'POtype' => '1',        // 단면 기본
    'MY_amount' => '',      // 수량 기본값 (동적으로 설정됨)
    'ordertype' => 'print'  // 인쇄만 기본
];

// 파일 업로드 설정
$namecard_upload_config = [
    'allowed_types' => ['pdf', 'ai', 'psd', 'jpg', 'jpeg', 'png', 'gif'],
    'max_file_size' => 50 * 1024 * 1024, // 50MB
    'upload_path' => '../../uploads/',
];

// 가격 계산 설정
$namecard_price_config = [
    'vat_rate' => 0.1,  // 부가세 10%
    'design_fee_basic' => 10000,   // 기본 편집비
    'design_fee_premium' => 30000, // 고급 편집비
];

// 제품 설명 데이터
$namecard_product_info = [
    'general' => [
        'title' => '💳 일반 명함',
        'description' => '가장 기본적인 명함으로 다양한 종이와 인쇄 옵션을 선택할 수 있습니다. 비즈니스용으로 널리 사용되며 경제적인 가격이 장점입니다.',
        'features' => [
            '다양한 용지 선택가능',
            '단면/양면 인쇄 지원',
            '대량 주문 할인',
            '빠른 제작 시간',
            '표준 명함 규격'
        ],
        'color' => 'primary', // 파란색 테마
    ],
    'premium' => [
        'title' => '💎 프리미엄 명함',
        'description' => '고급 용지와 특수 인쇄 기법을 사용한 프리미엄 명함입니다. 품격있는 비즈니스 이미지를 위한 최고급 명함을 원하실 때 선택하세요.',
        'features' => [
            '고급 특수 용지',
            'UV코팅, 박 처리 가능',
            '프리미엄 색상 구현',
            '고품질 인쇄',
            '개인 맞춤 제작'
        ],
        'color' => 'secondary', // 보라색 테마
    ]
];

// AJAX 엔드포인트 설정
$namecard_ajax_endpoints = [
    'get_sections' => 'get_sections.php',
    'get_quantities' => 'get_quantities.php',
    'calculate_price' => 'calculate_price.php',
    'add_to_basket' => 'add_to_basket.php',
    'get_images' => 'get_namecard_images.php',
];

// 에러 메시지
$namecard_error_messages = [
    'db_connection' => '데이터베이스 연결에 실패했습니다.',
    'invalid_input' => '잘못된 입력값입니다.',
    'file_upload' => '파일 업로드에 실패했습니다.',
    'price_calculation' => '가격 계산 중 오류가 발생했습니다.',
    'add_to_cart' => '장바구니 담기에 실패했습니다.',
    'no_category' => '명함 종류를 찾을 수 없습니다.',
    'no_section' => '재질 정보를 찾을 수 없습니다.',
    'no_quantity' => '수량 정보를 찾을 수 없습니다.',
];

// 성공 메시지
$namecard_success_messages = [
    'add_to_cart' => '장바구니에 성공적으로 담았습니다.',
    'file_upload' => '파일이 성공적으로 업로드되었습니다.',
    'order_complete' => '주문이 완료되었습니다.',
    'price_calculated' => '견적이 계산되었습니다.',
];

// 브랜드 색상 설정
$namecard_brand_colors = [
    'primary' => '#17a2b8',    // 파란색 (메인)
    'secondary' => '#667eea',  // 보라색 (서브)
    'success' => '#28a745',    // 녹색 (성공)
    'warning' => '#ffc107',    // 노란색 (경고)
    'danger' => '#dc3545',     // 빨간색 (오류)
];

// 개인정보 보호 설정
$namecard_privacy_settings = [
    'mask_personal_info' => true,         // 개인정보 마스킹 활성화
    'mask_area_width' => '40%',           // 마스킹 영역 너비
    'mask_area_height' => '35%',          // 마스킹 영역 높이
    'blur_intensity' => '6px',            // 블러 강도
];

// 메타 태그 설정
$namecard_meta = [
    'description' => '두손기획인쇄 명함 견적 시스템 - 실시간 가격계산, 다양한 용지 옵션, 빠른 제작',
    'keywords' => '명함, 명함제작, 명함인쇄, 비즈니스카드, 두손기획인쇄',
    'author' => '두손기획인쇄',
];
?>