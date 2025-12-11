<?php
/**
 * 양식지(NcrFlambeau) 설정 파일
 * 공통 설정 및 상수 정의
 * Created: 2025-09-03
 */

// 페이지 설정
define('NCRFLAMBEAU_PAGE', 'NcrFlambeau');
define('NCRFLAMBEAU_PAGE_TITLE', '📋 양식지(NCR) 견적안내');
define('NCRFLAMBEAU_CURRENT_PAGE', 'ncrflambeau');

// 데이터베이스 테이블
define('NCRFLAMBEAU_CATEGORY_TABLE', 'mlangprintauto_transactioncate');
define('NCRFLAMBEAU_PRICE_TABLE', 'mlangprintauto_ncrflambeau');

// 기본값 설정
$ncrflambeau_defaults = [
    'MY_type' => '475',     // 기본 양식지 종류 (양식 100매철)
    'MY_Fsd' => '',         // 규격 (동적으로 설정됨)
    'PN_type' => '',        // 색상 (동적으로 설정됨)
    'MY_amount' => '',      // 수량 (동적으로 설정됨)
    'ordertype' => 'print'  // 인쇄만 기본
];

// 파일 업로드 설정
$ncrflambeau_upload_config = [
    'allowed_types' => ['pdf', 'ai', 'psd', 'jpg', 'jpeg', 'png', 'gif', 'eps', 'doc', 'docx'],
    'max_file_size' => 100 * 1024 * 1024, // 100MB
    'upload_path' => '../../uploads/',
];

// 가격 계산 설정
$ncrflambeau_price_config = [
    'vat_rate' => 0.1,  // 부가세 10%
    'design_fee_basic' => 15000,   // 기본 편집비
    'design_fee_premium' => 40000, // 고급 편집비
];

// 제품 설명 데이터
$ncrflambeau_product_info = [
    'ncr_form' => [
        'title' => '📋 NCR 양식지',
        'description' => '복사본이 자동으로 만들어지는 무탄소 양식지입니다. 영수증, 주문서, 전표 등에 널리 사용됩니다.',
        'features' => [
            '2매철~5매철 선택 가능',
            '다양한 크기 및 색상',
            '고품질 무탄소 용지',
            '번호 매기기 옵션',
            '맞춤 디자인 제작'
        ],
        'color' => 'info', // 파란색 테마
    ],
    'business_form' => [
        'title' => '🏢 사업용 양식지',
        'description' => '업무용 각종 양식지 제작 서비스입니다. 견적서, 거래명세서, 세금계산서 등 비즈니스에 필요한 모든 양식을 제작합니다.',
        'features' => [
            '업무용 표준 양식',
            '회사 로고 삽입',
            '연번 인쇄 가능',
            '다양한 후가공 옵션',
            '대량 주문 할인'
        ],
        'color' => 'success', // 녹색 테마
    ]
];

// AJAX 엔드포인트 설정
$ncrflambeau_ajax_endpoints = [
    'get_sizes' => 'get_sizes.php',
    'get_colors' => 'get_colors.php',
    'get_quantities' => 'get_quantities.php',
    'calculate_price' => 'calculate_price_ajax.php',
    'add_to_basket' => 'add_to_basket.php',
    'get_images' => 'get_ncrflambeau_images.php',
    'get_options' => 'get_ncrflambeau_options.php',
];

// 에러 메시지
$ncrflambeau_error_messages = [
    'db_connection' => '데이터베이스 연결에 실패했습니다.',
    'invalid_input' => '잘못된 입력값입니다.',
    'file_upload' => '파일 업로드에 실패했습니다.',
    'price_calculation' => '가격 계산 중 오류가 발생했습니다.',
    'add_to_cart' => '장바구니 담기에 실패했습니다.',
    'no_category' => '양식지 종류를 찾을 수 없습니다.',
    'no_size' => '규격 정보를 찾을 수 없습니다.',
    'no_color' => '색상 정보를 찾을 수 없습니다.',
    'no_quantity' => '수량 정보를 찾을 수 없습니다.',
];

// 성공 메시지
$ncrflambeau_success_messages = [
    'add_to_cart' => '장바구니에 성공적으로 담았습니다.',
    'file_upload' => '파일이 성공적으로 업로드되었습니다.',
    'order_complete' => '주문이 완료되었습니다.',
    'price_calculated' => '견적이 계산되었습니다.',
];

// 브랜드 색상 설정 (파란색 계열 - NCR 양식지 테마)
$ncrflambeau_brand_colors = [
    'primary' => '#007bff',    // 파란색 (메인)
    'secondary' => '#6c757d',  // 회색 (서브)
    'info' => '#17a2b8',       // 청록색 (정보)
    'warning' => '#ffc107',    // 노란색 (경고)
    'danger' => '#dc3545',     // 빨간색 (오류)
];

// 폼 스타일 설정
$ncrflambeau_form_config = [
    'grid_style' => true,      // 그리드 기반 폼 사용
    'icon_style' => true,      // 아이콘 라벨 사용
    'help_text' => true,       // 도움말 텍스트 표시
    'animation' => true,       // 애니메이션 효과
];

// 메타 태그 설정
$ncrflambeau_meta = [
    'description' => '두손기획인쇄 양식지(NCR) 견적 시스템 - 실시간 가격계산, 다양한 규격 옵션, 빠른 제작',
    'keywords' => '양식지, NCR, 무탄소복사지, 전표제작, 영수증인쇄, 양식지제작, 두손기획인쇄',
    'author' => '두손기획인쇄',
];

// 갤러리 설정
$ncrflambeau_gallery_config = [
    'images_per_page' => 8,
    'thumbnail_size' => '300x200',
    'lightbox_enabled' => true,
    'lazy_loading' => true,
    'show_gallery' => true,
];

// JavaScript 설정
$ncrflambeau_js_config = [
    'auto_calculation' => true,
    'dynamic_updates' => true,
    'file_validation' => true,
    'ajax_timeout' => 30000, // 30초
    'animation_duration' => 300, // 0.3초
];

// 개인정보 보호 설정
$ncrflambeau_privacy_settings = [
    'mask_personal_info' => true,         // 개인정보 마스킹 활성화
    'mask_area_width' => '40%',           // 마스킹 영역 너비
    'mask_area_height' => '35%',          // 마스킹 영역 높이
    'blur_intensity' => '6px',            // 블러 강도
];

// 드롭다운 설정
$ncrflambeau_dropdown_config = [
    'auto_select_first' => true,          // 첫 번째 옵션 자동 선택
    'cascade_update' => true,             // 연쇄 업데이트
    'loading_text' => '로딩 중...',        // 로딩 텍스트
    'empty_text' => '선택 불가',          // 빈 목록 텍스트
];

// 양식지 특화 설정
$ncrflambeau_specific_config = [
    'ncr_layers' => [
        '2매철' => '상용지 + 중용지',
        '3매철' => '상용지 + 중용지 + 하용지',
        '4매철' => '상용지 + 중용지1 + 중용지2 + 하용지',
        '5매철' => '상용지 + 중용지1 + 중용지2 + 중용지3 + 하용지'
    ],
    'standard_colors' => [
        'white' => '백색 (상용지)',
        'pink' => '핑크색 (중용지)',
        'blue' => '청색 (중용지)',
        'green' => '녹색 (중용지)',
        'yellow' => '노란색 (하용지)'
    ],
    'numbering_options' => [
        'none' => '번호 없음',
        'simple' => '일련번호',
        'duplicate' => '중복번호',
        'custom' => '맞춤번호'
    ]
];

// 품질 관리 설정
$ncrflambeau_quality_settings = [
    'min_quantity' => 100,                 // 최소 주문 수량
    'max_quantity' => 50000,              // 최대 주문 수량
    'standard_size' => 'A4',              // 표준 크기
    'quality_check' => true,              // 품질 검사 활성화
];
?>