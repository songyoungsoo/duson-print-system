<?php
/**
 * 자석스티커(MSticker) 설정 파일
 * 공통 설정 및 상수 정의
 * Created: 2025-09-03
 */

// 페이지 설정
define('MSTICKER_PAGE', 'msticker');
define('MSTICKER_PAGE_TITLE', '🧲 자석스티커 견적안내');
define('MSTICKER_CURRENT_PAGE', 'msticker');

// 데이터베이스 테이블
define('MSTICKER_CATEGORY_TABLE', 'mlangprintauto_transactioncate');
define('MSTICKER_PRICE_TABLE', 'mlangprintauto_msticker');

// 기본값 설정
$msticker_defaults = [
    'MY_type' => '',        // 자석스티커 종류 (동적으로 설정됨)
    'Section' => '',        // 재질 (동적으로 설정됨)
    'POtype' => '1',        // 인쇄면 (단면 기본)
    'MY_amount' => '',      // 수량 (동적으로 설정됨)
    'ordertype' => 'print'  // 인쇄만 기본
];

// 파일 업로드 설정
$msticker_upload_config = [
    'allowed_types' => ['pdf', 'ai', 'psd', 'jpg', 'jpeg', 'png', 'gif', 'eps'],
    'max_file_size' => 100 * 1024 * 1024, // 100MB
    'upload_path' => '../../uploads/',
];

// 가격 계산 설정
$msticker_price_config = [
    'vat_rate' => 0.1,  // 부가세 10%
    'design_fee_basic' => 10000,   // 기본 편집비
    'design_fee_premium' => 30000, // 고급 편집비
];

// 제품 설명 데이터
$msticker_product_info = [
    'magnetic_sticker' => [
        'title' => '🧲 자석스티커',
        'description' => '강력한 자력으로 부착이 가능한 맞춤형 자석스티커를 제작합니다.',
        'features' => [
            '다양한 크기 옵션',
            '맞춤형 디자인 제작',
            '강력한 자력',
            '내구성 우수',
            '대량 주문 할인',
            '방수 처리 가능'
        ],
        'color' => 'info', // 청색 테마
    ],
    'namecard_magnet' => [
        'title' => '🧲 명함형 자석스티커',
        'description' => '명함 크기로 제작하는 실용적인 자석스티커입니다.',
        'features' => [
            '명함 표준 사이즈',
            '비즈니스용 디자인',
            '강력한 자력',
            '연락처 정보 표시',
            '프로모션 효과'
        ],
        'color' => 'success', // 녹색 테마
    ]
];

// AJAX 엔드포인트 설정
$msticker_ajax_endpoints = [
    'get_sections' => 'get_paper_types.php',
    'get_quantities' => 'get_quantities.php',
    'calculate_price' => 'calculate_price_ajax.php',
    'add_to_basket' => 'add_to_basket.php',
    'get_images' => 'get_msticker_images.php',
    'get_namecard_images' => 'get_namecard_images.php',
];

// 에러 메시지
$msticker_error_messages = [
    'db_connection' => '데이터베이스 연결에 실패했습니다.',
    'invalid_input' => '잘못된 입력값입니다.',
    'file_upload' => '파일 업로드에 실패했습니다.',
    'price_calculation' => '가격 계산 중 오류가 발생했습니다.',
    'add_to_cart' => '장바구니 담기에 실패했습니다.',
    'no_category' => '자석스티커 종류를 찾을 수 없습니다.',
    'no_section' => '재질 정보를 찾을 수 없습니다.',
    'no_quantity' => '수량 정보를 찾을 수 없습니다.',
];

// 성공 메시지
$msticker_success_messages = [
    'add_to_cart' => '장바구니에 성공적으로 담았습니다.',
    'file_upload' => '파일이 성공적으로 업로드되었습니다.',
    'order_complete' => '주문이 완료되었습니다.',
    'price_calculated' => '견적이 계산되었습니다.',
];

// 브랜드 색상 설정 (회색 계열 - 자석스티커 테마)
$msticker_brand_colors = [
    'primary' => '#6c757d',    // 회색 (메인)
    'secondary' => '#495057',  // 짙은 회색 (서브)
    'accent' => '#adb5bd',     // 밝은 회색 (강조)
    'light' => '#f8f9fa',      // 연한 회색 (배경)
    'dark' => '#343a40',       // 어두운 회색 (텍스트)
];

// 폼 스타일 설정
$msticker_form_config = [
    'grid_style' => true,      // 그리드 기반 폼 사용
    'icon_style' => true,      // 아이콘 라벨 사용
    'help_text' => true,       // 도움말 텍스트 표시
    'animation' => true,       // 애니메이션 효과
];

// 메타 태그 설정
$msticker_meta = [
    'description' => '두손기획인쇄 자석스티커 견적 시스템 - 실시간 가격계산, 다양한 크기 옵션, 빠른 제작',
    'keywords' => '자석스티커, 마그넷스티커, 자석명함, 차량용스티커, 냉장고스티커, 두손기획인쇄',
    'author' => '두손기획인쇄',
];

// 갤러리 설정
$msticker_gallery_config = [
    'images_per_page' => 8,
    'thumbnail_size' => '300x200',
    'lightbox_enabled' => true,
    'lazy_loading' => true,
    'show_gallery' => true,
    'pagination_enabled' => true,
];

// JavaScript 설정
$msticker_js_config = [
    'auto_calculation' => true,
    'dynamic_updates' => true,
    'file_validation' => true,
    'ajax_timeout' => 30000, // 30초
    'animation_duration' => 300, // 0.3초
    'gallery_pagination' => true,
];

// 개인정보 보호 설정
$msticker_privacy_settings = [
    'mask_personal_info' => true,         // 개인정보 마스킹 활성화
    'mask_area_width' => '40%',           // 마스킹 영역 너비
    'mask_area_height' => '35%',          // 마스킹 영역 높이
    'blur_intensity' => '6px',            // 블러 강도
];

// 드롭다운 설정
$msticker_dropdown_config = [
    'auto_select_first' => true,          // 첫 번째 옵션 자동 선택
    'cascade_update' => true,             // 연쇄 업데이트
    'loading_text' => '로딩 중...',        // 로딩 텍스트
    'empty_text' => '선택 불가',          // 빈 목록 텍스트
];

// 자석스티커 특화 설정
$msticker_specific_config = [
    'magnet_types' => [
        '일반자석' => '표준 자력 자석스티커',
        '강력자석' => '고급 강력 자석스티커',
        '유연자석' => '구부러지는 유연 자석',
        '하드자석' => '단단한 하드 자석'
    ],
    'common_sizes' => [
        '명함형' => '90×50mm (명함 사이즈)',
        '정사각' => '100×100mm (정사각형)',
        '직사각' => '150×100mm (직사각형)',
        '원형' => '직경 100mm (원형)'
    ],
    'applications' => [
        '차량용' => '자동차 외관 부착용',
        '냉장고용' => '가정용 냉장고 부착',
        '사무용' => '사무실 화이트보드용',
        '광고용' => '홍보 및 마케팅용',
        '기념품' => '기념품 및 선물용'
    ]
];

// 품질 관리 설정
$msticker_quality_settings = [
    'min_quantity' => 50,                 // 최소 주문 수량
    'max_quantity' => 20000,              // 최대 주문 수량
    'standard_size' => '90×50mm',         // 표준 크기 (명함)
    'quality_check' => true,              // 품질 검사 활성화
    'magnet_strength' => 'standard',      // 기본 자력 강도
];

// 인쇄면 옵션
$msticker_print_sides = [
    '1' => '단면',
    '2' => '양면'
];

// 편집 타입 옵션
$msticker_edit_options = [
    'print' => '인쇄만',
    'total' => '디자인+인쇄'
];

// 업로드 알림 메시지
$msticker_upload_notices = [
    'free_shipping' => '택배 무료배송은 결제금액 총 3만원 이상시에 한함',
    'same_day_limit' => '당일출고(당일)는 전날 주문 제품과 목업 불가',
    'file_naming' => '파일첨부 특수기호(#,&,\',&\',*,%, 등) 사용은 불가능하며 파일명이 길면 오류가 발생하니 되도록 짧고 간단하게 작성해 주세요!',
    'magnet_notice' => '자석스티커는 금속 표면에만 부착 가능하며, 곡면에는 부착이 어려울 수 있습니다.'
];

// 자석스티커별 추천 용도
$msticker_use_cases = [
    '차량용' => [
        '차량 광고',
        '업체 홍보',
        '연락처 표시',
        '로고 부착',
        '초보운전 표시'
    ],
    '사무용' => [
        '화이트보드 마커',
        '일정표 자석',
        '메모 고정용',
        '명함 자석',
        '사원증 자석'
    ],
    '가정용' => [
        '냉장고 메모',
        '사진 고정',
        '아이 작품 전시',
        '할일 목록',
        '연락처 메모'
    ]
];

// 로그 설정
$msticker_log_settings = [
    'enable_logging' => true,
    'log_price_calculations' => true,
    'log_file_uploads' => true,
    'log_cart_additions' => true,
    'log_retention_days' => 30,
];

// 갤러리 페이지네이션 설정
$msticker_pagination_config = [
    'items_per_page' => 8,
    'max_page_links' => 5,
    'show_first_last' => true,
    'show_prev_next' => true,
    'ellipsis_threshold' => 7,
];

// 자석 강도 분류
$msticker_magnet_strength = [
    'light' => [
        'name' => '약자력',
        'description' => '종이나 얇은 재료 고정용',
        'applications' => ['메모', '사진', '가벼운 물건']
    ],
    'standard' => [
        'name' => '표준자력',
        'description' => '일반적인 용도에 적합',
        'applications' => ['명함', '광고물', '안내판']
    ],
    'strong' => [
        'name' => '강자력',
        'description' => '무거운 재료나 야외 사용',
        'applications' => ['차량용', '야외광고', '산업용']
    ]
];

// 제작 공정 정보
$msticker_production_process = [
    '디자인확인' => '고객 디자인 검토 및 수정',
    '인쇄준비' => '인쇄용 파일 준비',
    '디지털인쇄' => '고해상도 디지털 인쇄',
    '자석부착' => '자석 시트 부착 작업',
    '재단가공' => '정확한 크기로 재단',
    '품질검사' => '최종 품질 확인',
    '포장발송' => '안전 포장 후 배송'
];

// 자석스티커 주의사항
$msticker_cautions = [
    'attachment' => '자석스티커는 철, 스테인리스 등 자성체에만 부착됩니다.',
    'temperature' => '고온(60℃ 이상)에서는 자력이 약해질 수 있습니다.',
    'surface' => '거친 표면이나 곡면에는 부착력이 약할 수 있습니다.',
    'storage' => '자석끼리 붙여서 보관하면 자력이 약해질 수 있습니다.',
    'cleaning' => '물에 젖지 않도록 주의하고, 부드러운 천으로 청소하세요.'
];
?>