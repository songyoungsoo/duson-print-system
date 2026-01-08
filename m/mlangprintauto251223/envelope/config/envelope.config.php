<?php
/**
 * 봉투(Envelope) 설정 파일
 * 공통 설정 및 상수 정의
 * Created: 2025-09-03
 */

// 페이지 설정
define('ENVELOPE_PAGE', 'Envelope');
define('ENVELOPE_PAGE_TITLE', '✉️ 두손기획인쇄 - 봉투 컴팩트 견적');
define('ENVELOPE_CURRENT_PAGE', 'envelope');

// 데이터베이스 테이블
define('ENVELOPE_CATEGORY_TABLE', 'mlangprintauto_transactioncate');
define('ENVELOPE_PRICE_TABLE', 'mlangprintauto_envelope');

// 기본값 설정
$envelope_defaults = [
    'MY_type' => '',        // 봉투 종류 (동적으로 설정됨)
    'Section' => '',        // 재질 (동적으로 설정됨)
    'POtype' => '1',        // 단면 기본
    'MY_amount' => '',      // 수량 기본값 (동적으로 설정됨)
    'ordertype' => 'print'  // 인쇄만 기본
];

// 파일 업로드 설정
$envelope_upload_config = [
    'allowed_types' => ['pdf', 'ai', 'psd', 'jpg', 'jpeg', 'png', 'gif'],
    'max_file_size' => 50 * 1024 * 1024, // 50MB
    'upload_path' => '../../uploads/',
];

// 가격 계산 설정
$envelope_price_config = [
    'vat_rate' => 0.1,  // 부가세 10%
    'design_fee_basic' => 10000,   // 기본 편집비
    'design_fee_premium' => 30000, // 고급 편집비
];

// 제품 설명 데이터
$envelope_product_info = [
    'standard' => [
        'title' => '✉️ 표준 봉투',
        'description' => '일반적인 사무용 봉투로 다양한 크기와 용지 옵션을 선택할 수 있습니다. 비즈니스 우편발송용으로 널리 사용되며 경제적인 가격이 장점입니다.',
        'features' => [
            '다양한 크기 선택가능',
            '단면/양면 인쇄 지원',
            '대량 주문 할인',
            '빠른 제작 시간',
            '표준 봉투 규격'
        ],
        'color' => 'warning', // 오렌지 테마
    ],
    'window' => [
        'title' => '📄 창봉투',
        'description' => '투명 창이 있는 봉투로 내용물의 주소를 직접 볼 수 있어 편리합니다. 청구서, 안내문, 공문 발송에 주로 사용됩니다.',
        'features' => [
            '투명 창 적용',
            '주소 확인 편의성',
            '공식 문서용',
            '고품질 인쇄',
            '다양한 창 위치'
        ],
        'color' => 'info', // 파란색 테마
    ]
];

// AJAX 엔드포인트 설정
$envelope_ajax_endpoints = [
    'get_sections' => 'get_sections.php',
    'get_quantities' => 'get_quantities.php',
    'calculate_price' => 'calculate_price.php',
    'add_to_basket' => 'add_to_basket.php',
    'get_images' => 'get_envelope_images.php',
];

// 에러 메시지
$envelope_error_messages = [
    'db_connection' => '데이터베이스 연결에 실패했습니다.',
    'invalid_input' => '잘못된 입력값입니다.',
    'file_upload' => '파일 업로드에 실패했습니다.',
    'price_calculation' => '가격 계산 중 오류가 발생했습니다.',
    'add_to_cart' => '장바구니 담기에 실패했습니다.',
    'no_category' => '봉투 종류를 찾을 수 없습니다.',
    'no_section' => '재질 정보를 찾을 수 없습니다.',
    'no_quantity' => '수량 정보를 찾을 수 없습니다.',
];

// 성공 메시지
$envelope_success_messages = [
    'add_to_cart' => '장바구니에 성공적으로 담았습니다.',
    'file_upload' => '파일이 성공적으로 업로드되었습니다.',
    'order_complete' => '주문이 완료되었습니다.',
    'price_calculated' => '견적이 계산되었습니다.',
];

// 브랜드 색상 설정 (오렌지 계열)
$envelope_brand_colors = [
    'primary' => '#ff9800',    // 오렌지 (메인)
    'secondary' => '#f57c00',  // 진한 오렌지 (서브)
    'success' => '#28a745',    // 녹색 (성공)
    'warning' => '#ffc107',    // 노란색 (경고)
    'danger' => '#dc3545',     // 빨간색 (오류)
];

// 개인정보 보호 설정
$envelope_privacy_settings = [
    'mask_personal_info' => true,         // 개인정보 마스킹 활성화
    'mask_area_width' => '40%',           // 마스킹 영역 너비
    'mask_area_height' => '35%',          // 마스킹 영역 높이
    'blur_intensity' => '6px',            // 블러 강도
];

// 메타 태그 설정
$envelope_meta = [
    'description' => '두손기획인쇄 봉투 견적 시스템 - 실시간 가격계산, 다양한 크기 옵션, 빠른 제작',
    'keywords' => '봉투, 봉투제작, 봉투인쇄, 창봉투, 두손기획인쇄',
    'author' => '두손기획인쇄',
];
?>