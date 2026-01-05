<?php
/**
 * 통계 API
 * 주문 통계 데이터를 JSON으로 반환
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/../../db.php';

// 요청 타입
$type = $_GET['type'] ?? 'summary';

switch ($type) {
    case 'summary':
        echo json_encode(getSummary($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'monthly':
        echo json_encode(getMonthlyOrders($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'products':
        echo json_encode(getProductStats($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'daily':
        $days = intval($_GET['days'] ?? 30);
        echo json_encode(getDailyOrders($db, $days), JSON_UNESCAPED_UNICODE);
        break;
    case 'recent':
        echo json_encode(getRecentOrders($db), JSON_UNESCAPED_UNICODE);
        break;
    default:
        echo json_encode(['error' => 'Invalid type'], JSON_UNESCAPED_UNICODE);
}

/**
 * 요약 통계 (오늘, 이번달, 총계)
 */
function getSummary($db) {
    $today = date('Y-m-d');
    $thisMonth = date('Y-m');
    $lastMonth = date('Y-m', strtotime('-1 month'));

    // 오늘 주문
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt, SUM(money_5) as total FROM mlangorder_printauto WHERE DATE(date) = '$today'");
    $todayData = mysqli_fetch_assoc($result);

    // 이번달 주문
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt, SUM(money_5) as total FROM mlangorder_printauto WHERE DATE_FORMAT(date, '%Y-%m') = '$thisMonth'");
    $thisMonthData = mysqli_fetch_assoc($result);

    // 지난달 주문 (비교용)
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt, SUM(money_5) as total FROM mlangorder_printauto WHERE DATE_FORMAT(date, '%Y-%m') = '$lastMonth'");
    $lastMonthData = mysqli_fetch_assoc($result);

    // 전체 주문
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt, SUM(money_5) as total FROM mlangorder_printauto");
    $totalData = mysqli_fetch_assoc($result);

    // 이번달 vs 지난달 증감률
    $orderGrowth = 0;
    $revenueGrowth = 0;
    if ($lastMonthData['cnt'] > 0) {
        $orderGrowth = round((($thisMonthData['cnt'] - $lastMonthData['cnt']) / $lastMonthData['cnt']) * 100, 1);
    }
    if ($lastMonthData['total'] > 0) {
        $revenueGrowth = round((($thisMonthData['total'] - $lastMonthData['total']) / $lastMonthData['total']) * 100, 1);
    }

    return [
        'today' => [
            'orders' => intval($todayData['cnt']),
            'revenue' => intval($todayData['total'] ?? 0)
        ],
        'thisMonth' => [
            'orders' => intval($thisMonthData['cnt']),
            'revenue' => intval($thisMonthData['total'] ?? 0),
            'orderGrowth' => $orderGrowth,
            'revenueGrowth' => $revenueGrowth
        ],
        'total' => [
            'orders' => intval($totalData['cnt']),
            'revenue' => intval($totalData['total'] ?? 0)
        ]
    ];
}

/**
 * 월별 주문 추이 (최근 12개월)
 */
function getMonthlyOrders($db) {
    $result = mysqli_query($db, "
        SELECT
            DATE_FORMAT(date, '%Y-%m') as month,
            COUNT(*) as orders,
            SUM(money_5) as revenue
        FROM mlangorder_printauto
        WHERE date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(date, '%Y-%m')
        ORDER BY month ASC
    ");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'month' => $row['month'],
            'orders' => intval($row['orders']),
            'revenue' => intval($row['revenue'] ?? 0)
        ];
    }

    return $data;
}

/**
 * 품목별 주문 통계
 */
function getProductStats($db) {
    // 품목명 정규화 (스티커/스티카, 명함/NameCard 등 통합)
    $result = mysqli_query($db, "
        SELECT
            CASE
                WHEN Type LIKE '%스티커%' OR Type LIKE '%스티카%' THEN '스티커'
                WHEN Type IN ('명함', 'NameCard', 'namecard') THEN '명함'
                WHEN Type IN ('전단지', 'inserted', '리플렛', 'leaflet') THEN '전단지/리플렛'
                WHEN Type LIKE '%봉투%' THEN '봉투'
                WHEN Type LIKE '%포스터%' OR Type = 'littleprint' THEN '포스터'
                WHEN Type LIKE '%상품권%' OR Type = 'merchandisebond' THEN '상품권'
                WHEN Type LIKE '%양식%' OR Type = 'ncrflambeau' THEN '양식지'
                WHEN Type LIKE '%카다%' OR Type = 'cadarok' THEN '카다록'
                ELSE '기타'
            END as category,
            COUNT(*) as orders,
            SUM(money_5) as revenue
        FROM mlangorder_printauto
        GROUP BY category
        ORDER BY orders DESC
    ");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['category'] != '' && $row['orders'] > 0) {
            $data[] = [
                'category' => $row['category'],
                'orders' => intval($row['orders']),
                'revenue' => intval($row['revenue'] ?? 0)
            ];
        }
    }

    return $data;
}

/**
 * 일별 주문 추이
 */
function getDailyOrders($db, $days = 30) {
    $result = mysqli_query($db, "
        SELECT
            DATE(date) as day,
            COUNT(*) as orders,
            SUM(money_5) as revenue
        FROM mlangorder_printauto
        WHERE date >= DATE_SUB(NOW(), INTERVAL $days DAY)
        GROUP BY DATE(date)
        ORDER BY day ASC
    ");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'day' => $row['day'],
            'orders' => intval($row['orders']),
            'revenue' => intval($row['revenue'] ?? 0)
        ];
    }

    return $data;
}

/**
 * 최근 주문 10건
 */
function getRecentOrders($db) {
    $result = mysqli_query($db, "
        SELECT no, Type, name, email, money_5, date
        FROM mlangorder_printauto
        ORDER BY no DESC
        LIMIT 10
    ");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'no' => $row['no'],
            'type' => $row['Type'],
            'name' => $row['name'] ?: explode('@', $row['email'])[0],
            'amount' => intval($row['money_5']),
            'date' => $row['date']
        ];
    }

    return $data;
}
