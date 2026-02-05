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
