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
    case 'pending':
        echo json_encode(getPendingStats($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'action_items':
        echo json_encode(getActionItems($db), JSON_UNESCAPED_UNICODE);
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

/**
 * 처리 대기 현황
 */
function getPendingStats($db) {
    // 결제 대기
    $paymentPending = 0;
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE payment_status = 'pending' OR payment_status IS NULL");
    if ($row = mysqli_fetch_assoc($result)) {
        $paymentPending = intval($row['cnt']);
    }

    // 입금 확인 대기 (무통장입금)
    $depositPending = 0;
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM payments WHERE payment_method = 'bank_transfer' AND status = 'pending'");
    if ($row = mysqli_fetch_assoc($result)) {
        $depositPending = intval($row['cnt']);
    }

    // 교정 승인 대기
    $proofPending = 0;
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE OrderStyle = 'proof_ready'");
    if ($row = mysqli_fetch_assoc($result)) {
        $proofPending = intval($row['cnt']);
    }

    // 제작 대기
    $productionPending = 0;
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE OrderStyle IN ('proof_approved', 'payment_confirmed')");
    if ($row = mysqli_fetch_assoc($result)) {
        $productionPending = intval($row['cnt']);
    }

    // 배송 대기
    $shippingPending = 0;
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE OrderStyle = 'shipping_ready' OR (ship_status = 'pending' AND OrderStyle NOT IN ('pending', 'cancelled'))");
    if ($row = mysqli_fetch_assoc($result)) {
        $shippingPending = intval($row['cnt']);
    }

    // 배송 중
    $inShipping = 0;
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM shipping_info WHERE status IN ('picked_up', 'in_transit', 'out_for_delivery')");
    if ($row = mysqli_fetch_assoc($result)) {
        $inShipping = intval($row['cnt']);
    }

    return [
        'payment_pending' => $paymentPending,
        'deposit_pending' => $depositPending,
        'proof_pending' => $proofPending,
        'production_pending' => $productionPending,
        'shipping_pending' => $shippingPending,
        'in_shipping' => $inShipping,
        'total_pending' => $paymentPending + $depositPending + $proofPending + $productionPending + $shippingPending
    ];
}

/**
 * 조치 필요 항목
 */
function getActionItems($db) {
    $items = [];

    // 3일 이상 입금 대기 중인 주문
    $result = mysqli_query($db, "
        SELECT o.no, o.name, o.money_5, o.date
        FROM mlangorder_printauto o
        LEFT JOIN payments p ON o.no = p.order_id
        WHERE (o.payment_status = 'pending' OR o.payment_status IS NULL)
        AND o.date < DATE_SUB(NOW(), INTERVAL 3 DAY)
        ORDER BY o.date ASC
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'type' => 'payment_overdue',
            'priority' => 'high',
            'title' => "입금 대기 3일 초과",
            'description' => "주문 #{$row['no']} - {$row['name']} (" . number_format($row['money_5']) . "원)",
            'order_no' => $row['no'],
            'date' => $row['date']
        ];
    }

    // 48시간 이상 교정 응답 대기
    $result = mysqli_query($db, "
        SELECT no, name, date
        FROM mlangorder_printauto
        WHERE OrderStyle = 'proof_ready'
        AND date < DATE_SUB(NOW(), INTERVAL 48 HOUR)
        ORDER BY date ASC
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'type' => 'proof_overdue',
            'priority' => 'medium',
            'title' => "교정 응답 48시간 초과",
            'description' => "주문 #{$row['no']} - {$row['name']}",
            'order_no' => $row['no'],
            'date' => $row['date']
        ];
    }

    // 배송 중 5일 이상 경과
    $result = mysqli_query($db, "
        SELECT s.order_id, o.name, s.created_at, s.courier_code, s.tracking_number
        FROM shipping_info s
        JOIN mlangorder_printauto o ON s.order_id = o.no
        WHERE s.status IN ('picked_up', 'in_transit')
        AND s.created_at < DATE_SUB(NOW(), INTERVAL 5 DAY)
        ORDER BY s.created_at ASC
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'type' => 'shipping_delayed',
            'priority' => 'medium',
            'title' => "배송 5일 이상 경과",
            'description' => "주문 #{$row['order_id']} - {$row['name']} ({$row['tracking_number']})",
            'order_no' => $row['order_id'],
            'date' => $row['created_at']
        ];
    }

    // 알림 발송 실패
    $result = mysqli_query($db, "
        SELECT order_id, notification_type, recipient, created_at
        FROM notification_logs
        WHERE status = 'failed'
        AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY created_at DESC
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'type' => 'notification_failed',
            'priority' => 'low',
            'title' => "알림 발송 실패",
            'description' => "주문 #{$row['order_id']} - {$row['notification_type']} ({$row['recipient']})",
            'order_no' => $row['order_id'],
            'date' => $row['created_at']
        ];
    }

    // 우선순위별 정렬
    usort($items, function($a, $b) {
        $priority = ['high' => 0, 'medium' => 1, 'low' => 2];
        return $priority[$a['priority']] <=> $priority[$b['priority']];
    });

    return $items;
}
