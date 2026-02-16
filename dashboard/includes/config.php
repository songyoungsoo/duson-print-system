<?php
/**
 * Dashboard Configuration
 * 대시보드 설정 상수
 */

// Dashboard Title
define('DASHBOARD_TITLE', '두손기획 관리자 대시보드');

// Pagination
define('ITEMS_PER_PAGE', 23);

// Date Format
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('DATE_FORMAT_SHORT', 'Y-m-d');

// Module Paths
define('DASHBOARD_ROOT', '/dashboard');
define('DASHBOARD_API', DASHBOARD_ROOT . '/api');

// Sidebar Navigation - Grouped
$DASHBOARD_NAV = [
    'main' => [
        'label' => '',
        'items' => [
            'home' => ['name' => '대시보드', 'icon' => '📊', 'path' => '/dashboard/'],
        ]
    ],
    'order_group' => [
        'label' => '주문·교정',
        'items' => [
            'admin_order' => ['name' => '관리자 주문', 'icon' => '📝', 'path' => '/dashboard/admin-order/'],
            'orders' => ['name' => '주문 관리', 'icon' => '📦', 'path' => '/dashboard/orders/'],
            'proofs' => ['name' => '교정 관리', 'icon' => '🔍', 'path' => '/dashboard/proofs/'],
            'proof_register' => ['name' => '교정 등록', 'icon' => '🖼️', 'path' => '/dashboard/embed.php?url=' . urlencode('/admin/mlangprintauto/admin.php?mode=AdminMlangOrdert'), 'embed' => true],
            'payments' => ['name' => '결제 현황', 'icon' => '💳', 'path' => '/dashboard/payments/'],
            'delivery' => ['name' => '택배 관리', 'icon' => '🚚', 'path' => '/dashboard/embed.php?url=' . urlencode('/shop_admin/delivery_manager.php'), 'embed' => true],
            'post_list' => ['name' => '발송 목록', 'icon' => '📮', 'path' => '/dashboard/embed.php?url=' . urlencode('/shop_admin/post_list74.php'), 'embed' => true],
        ]
    ],
    'comm_group' => [
        'label' => '소통·견적',
        'items' => [
            'email' => ['name' => '이메일 발송', 'icon' => '📧', 'path' => '/dashboard/email/'],
            'chat' => ['name' => '채팅 관리', 'icon' => '💬', 'path' => '/dashboard/chat/'],
            'quotes' => ['name' => '견적 관리', 'icon' => '📋', 'path' => '/dashboard/quotes/'],
            'inquiries' => ['name' => '고객 문의', 'icon' => '✉️', 'path' => '/dashboard/inquiries/'],
        ]
    ],
    'product_group' => [
        'label' => '제품·가격',
        'items' => [
            'products' => ['name' => '제품 관리', 'icon' => '🏷️', 'path' => '/dashboard/products/'],
            'pricing' => ['name' => '가격 관리', 'icon' => '💰', 'path' => '/dashboard/pricing/'],
            'option_prices' => ['name' => '견적옵션', 'icon' => '⚙️', 'path' => '/dashboard/embed.php?url=' . urlencode('/admin/mlangprintauto/quote/option_prices.php'), 'embed' => true],
            'sticker_prices' => ['name' => '스티커수정', 'icon' => '🏷️', 'path' => '/dashboard/pricing/sticker.php'],
            'gallery' => ['name' => '갤러리 관리', 'icon' => '🖼️', 'path' => '/dashboard/gallery/'],
            'premium_options' => ['name' => '품목옵션', 'icon' => '✨', 'path' => '/dashboard/premium-options/'],
        ]
    ],
    'admin_group' => [
        'label' => '관리·통계',
        'items' => [
            'members' => ['name' => '회원 관리', 'icon' => '👥', 'path' => '/dashboard/members/'],
            'stats' => ['name' => '주문 통계', 'icon' => '📈', 'path' => '/dashboard/stats/'],
            'visitors' => ['name' => '방문자분석', 'icon' => '👁️', 'path' => '/dashboard/visitors/'],
            'site_settings' => ['name' => '사이트 설정', 'icon' => '⚙️', 'path' => '/dashboard/settings/'],
        ]
    ],
    'legacy_group' => [
        'label' => '기존 관리자',
        'items' => [
            'admin_legacy' => ['name' => '주문 관리(구)', 'icon' => '🗂️', 'path' => '/dashboard/embed.php?url=' . urlencode('/admin/mlangprintauto/orderlist.php'), 'embed' => true],
            'admin_proof' => ['name' => '교정 관리(구)', 'icon' => '📂', 'path' => '/dashboard/embed.php?url=' . urlencode('/sub/checkboard.php'), 'embed' => true],
            'admin_quote' => ['name' => '견적서(구)', 'icon' => '📝', 'path' => '/dashboard/embed.php?url=' . urlencode('/admin/mlangprintauto/quote/'), 'embed' => true],
        ]
    ],
];

// Flat module list for backward compatibility
$DASHBOARD_MODULES = [];
foreach ($DASHBOARD_NAV as $group) {
    foreach ($group['items'] as $key => $item) {
        $DASHBOARD_MODULES[$key] = $item;
    }
}

// Product Types Configuration
// ttable: mlangprintauto_transactioncate.Ttable 값 (대소문자 주의)
// hasTreeSelect: TreeSelect 컬럼 존재 여부 (종이 종류)
// hasPOtype: POtype 컬럼 존재 여부 (단면/양면, 봉투는 1도/2도/칼라4도)
// 모든 제품은 동일한 BigNo/TreeNo 구조 사용 (2026-02-06 마이그레이션 완료):
//   - Section → BigNo 참조
//   - Tree → TreeNo 참조
$PRODUCT_TYPES = [
    'namecard' => ['name' => '명함', 'table' => 'mlangprintauto_namecard', 'unit' => '매', 'ttable' => 'NameCard', 'hasTreeSelect' => false, 'hasPOtype' => true],
    'sticker' => ['name' => '스티커', 'table' => 'mlangprintauto_sticker', 'unit' => '매', 'ttable' => 'sticker', 'hasTreeSelect' => false, 'hasPOtype' => false],
    'inserted' => ['name' => '전단지', 'table' => 'mlangprintauto_inserted', 'unit' => '연', 'ttable' => 'inserted', 'hasTreeSelect' => true, 'hasPOtype' => true],
    'envelope' => ['name' => '봉투', 'table' => 'mlangprintauto_envelope', 'unit' => '매', 'ttable' => 'envelope', 'hasTreeSelect' => false, 'hasPOtype' => true, 'potypeLabels' => ['1' => '마스터1도', '2' => '마스터2도', '3' => '칼라4도(옵셋)']],
    'littleprint' => ['name' => '포스터', 'table' => 'mlangprintauto_littleprint', 'unit' => '매', 'ttable' => 'LittlePrint', 'hasTreeSelect' => true, 'hasPOtype' => true],
    'merchandisebond' => ['name' => '상품권', 'table' => 'mlangprintauto_merchandisebond', 'unit' => '매', 'ttable' => 'MerchandiseBond', 'hasTreeSelect' => false, 'hasPOtype' => true],
    'cadarok' => ['name' => '카다록', 'table' => 'mlangprintauto_cadarok', 'unit' => '부', 'ttable' => 'cadarok', 'hasTreeSelect' => true, 'hasPOtype' => true],
    'ncrflambeau' => ['name' => 'NCR양식지', 'table' => 'mlangprintauto_ncrflambeau', 'unit' => '권', 'ttable' => 'NcrFlambeau', 'hasTreeSelect' => true, 'hasPOtype' => true],
    'msticker' => ['name' => '자석스티커', 'table' => 'mlangprintauto_msticker', 'unit' => '매', 'ttable' => 'msticker', 'hasTreeSelect' => false, 'hasPOtype' => false],
];
