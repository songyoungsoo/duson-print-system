<?php
/**
 * 전단지(Leaflet) 설정 파일
 * 공통 설정 및 상수 정의
 * Created: 2025-09-02
 */

// 페이지 설정
define('LEAFLET_PAGE', 'inserted');
define('LEAFLET_PAGE_TITLE', '📄 두손기획인쇄 - 전단지 컴팩트 견적');
define('LEAFLET_CURRENT_PAGE', 'leaflet');

// 데이터베이스 테이블
define('LEAFLET_TABLE', 'mlangprintauto_transactioncate');
define('LEAFLET_GGTABLE', 'mlangprintauto_transactioncate');

// 기본값 설정
$leaflet_defaults = [
    'ordertype' => 'print',  // 인쇄만 기본
    'POtype' => '1',        // 단면 기본
    'MY_amount' => '',      // 수량 기본값 (빈값)
];

// 파일 업로드 설정
$leaflet_upload_config = [
    'allowed_types' => ['pdf', 'ai', 'psd', 'jpg', 'jpeg', 'png', 'gif'],
    'max_file_size' => 50 * 1024 * 1024, // 50MB
    'upload_path' => '../../uploads/',
];

// 가격 계산 설정
$leaflet_price_config = [
    'vat_rate' => 0.1,  // 부가세 10%
    'design_fee_basic' => 10000,   // 기본 편집비
    'design_fee_premium' => 30000, // 고급 편집비
];

// 제품 설명 데이터
$leaflet_product_info = [
    'hapan' => [
        'title' => '📄 합판 전단지',
        'description' => '일정량의 고객 인쇄물을 한판에 모아서 인쇄 제작하는 상품으로 저렴한 가격과 빠른 제작시간이 특징인 상품입니다. 일반 길거리 대량 배포용 전단지를 제작하실 때 선택하시면 됩니다.',
        'sizes' => ['A4', 'A5', 'A6', 'B5', '4*6', '5*7'],
        'work_size_note' => '재단사이즈에서 사방 1.5mm씩 여분',
        'tip' => '💡 TIP! 작업 템플릿을 다운 받아 사용하시면 더욱 정확하고 편리하게 작업하실 수 있습니다!',
        'color' => 'primary', // 녹색 테마
    ],
    'dokpan' => [
        'title' => '📋 독판 전단지',
        'description' => '나만의 인쇄물을 단독으로 인쇄할 수 있는 상품으로 고급 인쇄물 제작을 원할 때 선택하시면 됩니다. 다양한 용지 선택과 후가공 선택이 가능한 상품입니다.',
        'features' => [
            '다양한 고급용지 선택가능',
            '박, 형압, UV코팅 등 후가공 가능',
            '소량제작 가능',
            '정확한 색상 재현',
            '상담 후 진행',
        ],
        'color' => 'secondary', // 파란색 테마
    ],
];

// AJAX 엔드포인트 설정
$leaflet_ajax_endpoints = [
    'get_paper_types' => 'get_paper_types.php',
    'get_paper_sizes' => 'get_paper_sizes.php',
    'get_quantities' => 'get_quantities.php',
    'calculate_price' => 'calculate_price_ajax.php',
    'add_to_basket' => 'add_to_basket.php',
    'get_images' => 'get_leaflet_images.php',
];

// 에러 메시지
$leaflet_error_messages = [
    'db_connection' => '데이터베이스 연결에 실패했습니다.',
    'invalid_input' => '잘못된 입력값입니다.',
    'file_upload' => '파일 업로드에 실패했습니다.',
    'price_calculation' => '가격 계산 중 오류가 발생했습니다.',
    'add_to_cart' => '장바구니 담기에 실패했습니다.',
];

// 성공 메시지
$leaflet_success_messages = [
    'add_to_cart' => '장바구니에 성공적으로 담았습니다.',
    'file_upload' => '파일이 성공적으로 업로드되었습니다.',
    'order_complete' => '주문이 완료되었습니다.',
];
?>