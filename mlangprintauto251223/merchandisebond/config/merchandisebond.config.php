<?php
/**
 * 상품권/쿠폰(MerchandiseBond) 설정 파일
 * 공통 설정 및 상수 정의
 * Created: 2025-09-03
 */

// 페이지 설정
define('MERCHANDISEBOND_PAGE', 'MerchandiseBond');
define('MERCHANDISEBOND_PAGE_TITLE', '🎁 상품권/쿠폰 견적안내');
define('MERCHANDISEBOND_CURRENT_PAGE', 'merchandisebond');

// 데이터베이스 테이블
define('MERCHANDISEBOND_CATEGORY_TABLE', 'mlangprintauto_transactioncate');
define('MERCHANDISEBOND_PRICE_TABLE', 'mlangprintauto_merchandisebond');

// 기본값 설정
$merchandisebond_defaults = [
    'MY_type' => '',        // 상품권/쿠폰 종류 (동적으로 설정됨)
    'Section' => '',        // 재질 (동적으로 설정됨)
    'POtype' => '1',        // 인쇄면 (단면 기본)
    'MY_amount' => '',      // 수량 (동적으로 설정됨)
    'ordertype' => 'print'  // 인쇄만 기본
];

// 파일 업로드 설정
$merchandisebond_upload_config = [
    'allowed_types' => ['pdf', 'ai', 'psd', 'jpg', 'jpeg', 'png', 'gif', 'eps'],
    'max_file_size' => 100 * 1024 * 1024, // 100MB
    'upload_path' => '../../uploads/',
];

// 가격 계산 설정
$merchandisebond_price_config = [
    'vat_rate' => 0.1,  // 부가세 10%
    'design_fee_basic' => 10000,   // 기본 편집비
    'design_fee_premium' => 30000, // 고급 편집비
];

// 제품 설명 데이터
$merchandisebond_product_info = [
    'gift_certificate' => [
        'title' => '🎁 상품권',
        'description' => '다양한 업종에서 사용할 수 있는 맞춤형 상품권을 제작합니다.',
        'features' => [
            '다양한 재질 선택 가능',
            '맞춤형 디자인 제작',
            '위조 방지 기능',
            '다양한 크기 옵션',
            '대량 주문 할인'
        ],
        'color' => 'success', // 녹색 테마
    ],
    'coupon' => [
        'title' => '🎫 쿠폰',
        'description' => '매장 프로모션과 마케팅에 효과적인 쿠폰을 제작합니다.',
        'features' => [
            '할인쿠폰 제작',
            '이벤트용 쿠폰',
            '멤버십 쿠폰',
            '미싱선 가공',
            '연번 인쇄 옵션'
        ],
        'color' => 'warning', // 노란색 테마
    ]
];

// AJAX 엔드포인트 설정
$merchandisebond_ajax_endpoints = [
    'get_sections' => 'get_paper_types.php',
    'get_quantities' => 'get_quantities.php',
    'calculate_price' => 'calculate_price_ajax.php',
    'add_to_basket' => 'add_to_basket.php',
    'get_images' => 'get_merchandisebond_images.php',
];

// 에러 메시지
$merchandisebond_error_messages = [
    'db_connection' => '데이터베이스 연결에 실패했습니다.',
    'invalid_input' => '잘못된 입력값입니다.',
    'file_upload' => '파일 업로드에 실패했습니다.',
    'price_calculation' => '가격 계산 중 오류가 발생했습니다.',
    'add_to_cart' => '장바구니 담기에 실패했습니다.',
    'no_category' => '상품권/쿠폰 종류를 찾을 수 없습니다.',
    'no_section' => '재질 정보를 찾을 수 없습니다.',
    'no_quantity' => '수량 정보를 찾을 수 없습니다.',
];

// 성공 메시지
$merchandisebond_success_messages = [
    'add_to_cart' => '장바구니에 성공적으로 담았습니다.',
    'file_upload' => '파일이 성공적으로 업로드되었습니다.',
    'order_complete' => '주문이 완료되었습니다.',
    'price_calculated' => '견적이 계산되었습니다.',
];

// 브랜드 색상 설정 (핑크색 계열 - 상품권/쿠폰 테마)
$merchandisebond_brand_colors = [
    'primary' => '#e91e63',    // 핑크색 (메인)
    'secondary' => '#ad1457',  // 짙은 핑크 (서브)
    'accent' => '#ff6b9d',     // 밝은 핑크 (강조)
    'light' => '#ffc1e3',      // 연한 핑크 (배경)
    'dark' => '#8e0038',       // 어두운 핑크 (텍스트)
];

// 폼 스타일 설정
$merchandisebond_form_config = [
    'grid_style' => true,      // 그리드 기반 폼 사용
    'icon_style' => true,      // 아이콘 라벨 사용
    'help_text' => true,       // 도움말 텍스트 표시
    'animation' => true,       // 애니메이션 효과
];

// 메타 태그 설정
$merchandisebond_meta = [
    'description' => '두손기획인쇄 상품권/쿠폰 견적 시스템 - 실시간 가격계산, 다양한 재질 옵션, 빠른 제작',
    'keywords' => '상품권, 쿠폰, 기프트카드, 할인쿠폰, 상품권제작, 쿠폰제작, 두손기획인쇄',
    'author' => '두손기획인쇄',
];

// 갤러리 설정
$merchandisebond_gallery_config = [
    'images_per_page' => 8,
    'thumbnail_size' => '300x200',
    'lightbox_enabled' => true,
    'lazy_loading' => true,
    'show_gallery' => true,
];

// JavaScript 설정
$merchandisebond_js_config = [
    'auto_calculation' => true,
    'dynamic_updates' => true,
    'file_validation' => true,
    'ajax_timeout' => 30000, // 30초
    'animation_duration' => 300, // 0.3초
];

// 개인정보 보호 설정
$merchandisebond_privacy_settings = [
    'mask_personal_info' => true,         // 개인정보 마스킹 활성화
    'mask_area_width' => '40%',           // 마스킹 영역 너비
    'mask_area_height' => '35%',          // 마스킹 영역 높이
    'blur_intensity' => '6px',            // 블러 강도
];

// 드롭다운 설정
$merchandisebond_dropdown_config = [
    'auto_select_first' => true,          // 첫 번째 옵션 자동 선택
    'cascade_update' => true,             // 연쇄 업데이트
    'loading_text' => '로딩 중...',        // 로딩 텍스트
    'empty_text' => '선택 불가',          // 빈 목록 텍스트
];

// 상품권/쿠폰 특화 설정
$merchandisebond_specific_config = [
    'certificate_types' => [
        '기프트카드' => '플라스틱 카드형 상품권',
        '종이상품권' => '일반 종이형 상품권',
        '북클릿형' => '책자형 상품권',
        '롤형' => '롤 형태 쿠폰'
    ],
    'security_features' => [
        '홀로그램' => '위조 방지 홀로그램',
        '워터마크' => '투명 워터마크',
        '미싱선' => '절취선 가공',
        '연번인쇄' => '일련번호 인쇄'
    ],
    'common_sizes' => [
        '신용카드' => '86×54mm (신용카드 사이즈)',
        '명함' => '90×50mm (명함 사이즈)',
        'A6' => '148×105mm (A6 사이즈)',
        'A5' => '210×148mm (A5 사이즈)'
    ]
];

// 품질 관리 설정
$merchandisebond_quality_settings = [
    'min_quantity' => 100,                 // 최소 주문 수량
    'max_quantity' => 50000,              // 최대 주문 수량
    'standard_size' => '86×54mm',         // 표준 크기 (신용카드)
    'quality_check' => true,              // 품질 검사 활성화
];

// 인쇄면 옵션
$merchandisebond_print_sides = [
    '1' => '단면',
    '2' => '양면'
];

// 편집 타입 옵션
$merchandisebond_edit_options = [
    'print' => '인쇄만',
    'total' => '디자인+인쇄'
];

// 업로드 알림 메시지
$merchandisebond_upload_notices = [
    'free_shipping' => '택배 무료배송은 결제금액 총 3만원 이상시에 한함',
    'same_day_limit' => '당일출고(당일)는 전날 주문 제품과 목업 불가',
    'file_naming' => '파일첨부 특수기호(#,&,\',&\',*,%, 등) 사용은 불가능하며 파일명이 길면 오류가 발생하니 되도록 짧고 간단하게 작성해 주세요!'
];

// 상품권/쿠폰별 추천 용도
$merchandisebond_use_cases = [
    '상품권' => [
        '매장 상품권',
        '온라인몰 기프트카드',
        '이벤트 경품',
        '직원 복리후생',
        '고객 감사 선물'
    ],
    '쿠폰' => [
        '할인쿠폰',
        '신제품 체험쿠폰',
        '멤버십 혜택쿠폰',
        '이벤트 참가쿠폰',
        '재방문 유도쿠폰'
    ]
];

// 로그 설정
$merchandisebond_log_settings = [
    'enable_logging' => true,
    'log_price_calculations' => true,
    'log_file_uploads' => true,
    'log_cart_additions' => true,
    'log_retention_days' => 30,
];
?>