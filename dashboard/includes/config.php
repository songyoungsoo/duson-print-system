<?php
/**
 * Dashboard Configuration
 * 대시보드 설정 상수
 */

// Dashboard Title
define('DASHBOARD_TITLE', '두손기획 관리자 대시보드');

// Pagination
define('ITEMS_PER_PAGE', 30);

// Date Format
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('DATE_FORMAT_SHORT', 'Y-m-d');

// Module Paths
define('DASHBOARD_ROOT', '/dashboard');
define('DASHBOARD_API', DASHBOARD_ROOT . '/api');

// Module Names
$DASHBOARD_MODULES = [
    'home' => ['name' => '대시보드 홈', 'icon' => '📊', 'path' => '/dashboard/'],
    'orders' => ['name' => '주문 관리', 'icon' => '📦', 'path' => '/dashboard/orders/'],
    'members' => ['name' => '회원 관리', 'icon' => '👥', 'path' => '/dashboard/members/'],
    'products' => ['name' => '제품 관리', 'icon' => '🏷️', 'path' => '/dashboard/products/'],
    'stats' => ['name' => '주문 통계', 'icon' => '📈', 'path' => '/dashboard/stats/'],
    'payments' => ['name' => '결제 현황', 'icon' => '💳', 'path' => '/dashboard/payments/'],
    'inquiries' => ['name' => '고객 문의', 'icon' => '💬', 'path' => '/dashboard/inquiries/'],
    'pricing' => ['name' => '가격 관리', 'icon' => '💰', 'path' => '/dashboard/pricing/'],
];

// Product Types Configuration
// ttable: mlangprintauto_transactioncate.Ttable 값 (대소문자 주의)
// hasTreeSelect: TreeSelect 컬럼 존재 여부 (종이 종류)
// hasPOtype: POtype 컬럼 존재 여부 (단면/양면)
// 모든 제품은 동일한 BigNo/TreeNo 구조 사용 (2026-02-06 마이그레이션 완료):
//   - Section → BigNo 참조
//   - Tree → TreeNo 참조
$PRODUCT_TYPES = [
    'namecard' => ['name' => '명함', 'table' => 'mlangprintauto_namecard', 'unit' => '매', 'ttable' => 'NameCard', 'hasTreeSelect' => false, 'hasPOtype' => true],
    'sticker' => ['name' => '스티커', 'table' => 'mlangprintauto_sticker', 'unit' => '매', 'ttable' => 'sticker', 'hasTreeSelect' => false, 'hasPOtype' => false],
    'inserted' => ['name' => '전단지', 'table' => 'mlangprintauto_inserted', 'unit' => '연', 'ttable' => 'inserted', 'hasTreeSelect' => true, 'hasPOtype' => true],
    'envelope' => ['name' => '봉투', 'table' => 'mlangprintauto_envelope', 'unit' => '매', 'ttable' => 'envelope', 'hasTreeSelect' => false, 'hasPOtype' => true],
    'littleprint' => ['name' => '포스터', 'table' => 'mlangprintauto_littleprint', 'unit' => '매', 'ttable' => 'LittlePrint', 'hasTreeSelect' => true, 'hasPOtype' => true],
    'merchandisebond' => ['name' => '상품권', 'table' => 'mlangprintauto_merchandisebond', 'unit' => '매', 'ttable' => 'MerchandiseBond', 'hasTreeSelect' => false, 'hasPOtype' => true],
    'cadarok' => ['name' => '카다록', 'table' => 'mlangprintauto_cadarok', 'unit' => '부', 'ttable' => 'cadarok', 'hasTreeSelect' => true, 'hasPOtype' => true],
    'ncrflambeau' => ['name' => 'NCR양식지', 'table' => 'mlangprintauto_ncrflambeau', 'unit' => '권', 'ttable' => 'NcrFlambeau', 'hasTreeSelect' => true, 'hasPOtype' => true],
    'msticker' => ['name' => '자석스티커', 'table' => 'mlangprintauto_msticker', 'unit' => '매', 'ttable' => 'msticker', 'hasTreeSelect' => false, 'hasPOtype' => false],
];
