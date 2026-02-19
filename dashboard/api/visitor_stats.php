<?php
/**
 * 방문자 통계 API (Dashboard)
 */
date_default_timezone_set('Asia/Seoul');
require_once __DIR__ . '/base.php';

$type = $_GET['type'] ?? 'summary';

switch ($type) {
    case 'summary':
        echo json_encode(getVisitorSummary($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'hourly':
        echo json_encode(getHourlyStats($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'pages':
        echo json_encode(getTopPages($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'referers':
        echo json_encode(getTopReferers($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'suspicious':
        $threshold = intval($_GET['threshold'] ?? 100);
        echo json_encode(getSuspiciousIPs($db, $threshold), JSON_UNESCAPED_UNICODE);
        break;
    case 'realtime':
        echo json_encode(getRealtimeVisitors($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'daily':
        $days = intval($_GET['days'] ?? 30);
        echo json_encode(getDailyVisitors($db, $days), JSON_UNESCAPED_UNICODE);
        break;
    case 'blocked':
        echo json_encode(getBlockedIPs($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'sessions':
        echo json_encode(getSessionStats($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'devices':
        echo json_encode(getDeviceStats($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'new_returning':
        echo json_encode(getNewReturning($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'conversion':
        echo json_encode(getConversionStats($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'entry_exit':
        echo json_encode(getEntryExitPages($db), JSON_UNESCAPED_UNICODE);
        break;
    case 'block':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = $_POST['ip'] ?? '';
            $reason = $_POST['reason'] ?? '수동 차단';
            $hours = intval($_POST['hours'] ?? 24);
            require_once __DIR__ . '/../../includes/visitor_tracker.php';
            $result = VisitorTracker::blockIP($db, $ip, $reason, $hours);
            echo json_encode(['success' => $result], JSON_UNESCAPED_UNICODE);
        }
        break;
    case 'unblock':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = $_POST['ip'] ?? '';
            require_once __DIR__ . '/../../includes/visitor_tracker.php';
            $result = VisitorTracker::unblockIP($db, $ip);
            echo json_encode(['success' => $result], JSON_UNESCAPED_UNICODE);
        }
        break;
    default:
        echo json_encode(['error' => 'Invalid type'], JSON_UNESCAPED_UNICODE);
}

// ─── 기존 함수 ───

function getVisitorSummary($db) {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $thisMonth = date('Y-m');

    $result = mysqli_query($db,
        "SELECT COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors, COUNT(DISTINCT session_id) as sessions
         FROM visitor_logs WHERE DATE(visit_time) = '$today' AND is_bot = 0");
    $todayData = mysqli_fetch_assoc($result);

    $result = mysqli_query($db,
        "SELECT COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs WHERE DATE(visit_time) = '$yesterday' AND is_bot = 0");
    $yesterdayData = mysqli_fetch_assoc($result);

    $result = mysqli_query($db,
        "SELECT COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs WHERE DATE_FORMAT(visit_time, '%Y-%m') = '$thisMonth' AND is_bot = 0");
    $monthData = mysqli_fetch_assoc($result);

    $result = mysqli_query($db,
        "SELECT COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs WHERE is_bot = 0");
    $totalData = mysqli_fetch_assoc($result);

    $fiveMinAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $result = mysqli_query($db,
        "SELECT COUNT(DISTINCT ip) as active FROM visitor_logs
         WHERE visit_time >= '$fiveMinAgo' AND is_bot = 0");
    $activeData = mysqli_fetch_assoc($result);

    $result = mysqli_query($db,
        "SELECT COUNT(*) as bot_visits FROM visitor_logs
         WHERE DATE(visit_time) = '$today' AND is_bot = 1");
    $botData = mysqli_fetch_assoc($result);

    $visitorGrowth = 0;
    if ($yesterdayData['unique_visitors'] > 0) {
        $visitorGrowth = round((($todayData['unique_visitors'] - $yesterdayData['unique_visitors']) / $yesterdayData['unique_visitors']) * 100, 1);
    }

    return [
        'today' => [
            'visits' => intval($todayData['visits']),
            'unique_visitors' => intval($todayData['unique_visitors']),
            'sessions' => intval($todayData['sessions']),
            'growth' => $visitorGrowth
        ],
        'month' => [
            'visits' => intval($monthData['visits']),
            'unique_visitors' => intval($monthData['unique_visitors'])
        ],
        'total' => [
            'visits' => intval($totalData['visits']),
            'unique_visitors' => intval($totalData['unique_visitors'])
        ],
        'realtime' => [
            'active' => intval($activeData['active']),
            'bots_today' => intval($botData['bot_visits'])
        ]
    ];
}

function getHourlyStats($db) {
    $today = date('Y-m-d');

    // 방문 시간을 한국 시간(UTC+9)으로 변환하여 집계
    // MySQL HOUR() 함수는 서버 시간대를 따르므로, DATE_ADD로 9시간 추가 후 시간 추출
    $result = mysqli_query($db,
        "SELECT HOUR(DATE_ADD(visit_time, INTERVAL 9 HOUR)) as hour, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs WHERE DATE(visit_time) = '$today' AND is_bot = 0
         GROUP BY HOUR(DATE_ADD(visit_time, INTERVAL 9 HOUR)) ORDER BY hour");

    $data = array_fill(0, 24, ['visits' => 0, 'unique' => 0]);
    while ($row = mysqli_fetch_assoc($result)) {
        $hour = intval($row['hour']);
        $data[$hour] = ['visits' => intval($row['visits']), 'unique' => intval($row['unique_visitors'])];
    }
    return $data;
}

function getTopPages($db) {
    $today = date('Y-m-d');
    $result = mysqli_query($db,
        "SELECT page, COUNT(*) as views FROM visitor_logs
         WHERE DATE(visit_time) = '$today' AND is_bot = 0
         GROUP BY page ORDER BY views DESC LIMIT 10");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = ['page' => $row['page'], 'views' => intval($row['views'])];
    }
    return $data;
}

function getTopReferers($db) {
    $today = date('Y-m-d');
    $result = mysqli_query($db,
        "SELECT
            CASE
                WHEN referer = '' OR referer IS NULL THEN '직접 방문'
                WHEN referer LIKE '%google%' THEN 'Google'
                WHEN referer LIKE '%naver%' THEN 'Naver'
                WHEN referer LIKE '%daum%' THEN 'Daum'
                WHEN referer LIKE '%bing%' THEN 'Bing'
                WHEN referer LIKE '%facebook%' THEN 'Facebook'
                WHEN referer LIKE '%instagram%' THEN 'Instagram'
                ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(referer, '/', 3), '//', -1)
            END as source,
            COUNT(*) as visits
         FROM visitor_logs
         WHERE DATE(visit_time) = '$today' AND is_bot = 0
         GROUP BY source ORDER BY visits DESC LIMIT 10");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = ['source' => $row['source'], 'visits' => intval($row['visits'])];
    }
    return $data;
}

function getSuspiciousIPs($db, $threshold = 100) {
    $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $result = mysqli_query($db,
        "SELECT ip, COUNT(*) as hit_count, MAX(visit_time) as last_visit,
                COUNT(DISTINCT page) as unique_pages
         FROM visitor_logs WHERE visit_time >= '$oneHourAgo' AND is_bot = 0
         GROUP BY ip HAVING hit_count >= $threshold
         ORDER BY hit_count DESC LIMIT 20");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $count = intval($row['hit_count']);
        $data[] = [
            'ip' => $row['ip'], 'count' => $count,
            'unique_pages' => intval($row['unique_pages']),
            'last_visit' => $row['last_visit'],
            'status' => $count >= 500 ? 'critical' : ($count >= 200 ? 'warning' : 'watch')
        ];
    }
    return $data;
}

function getRealtimeVisitors($db) {
    $fiveMinAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $result = mysqli_query($db,
        "SELECT ip, page, visit_time, user_agent FROM visitor_logs
         WHERE visit_time >= '$fiveMinAgo' AND is_bot = 0
         ORDER BY visit_time DESC LIMIT 20");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'ip' => $row['ip'], 'page' => $row['page'],
            'time' => $row['visit_time'], 'browser' => getBrowserName($row['user_agent'])
        ];
    }
    return $data;
}

function getDailyVisitors($db, $days = 30) {
    $result = mysqli_query($db,
        "SELECT DATE(visit_time) as day, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs WHERE visit_time >= DATE_SUB(NOW(), INTERVAL $days DAY) AND is_bot = 0
         GROUP BY DATE(visit_time) ORDER BY day ASC");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = ['day' => $row['day'], 'visits' => intval($row['visits']), 'unique' => intval($row['unique_visitors'])];
    }
    return $data;
}

function getBlockedIPs($db) {
    $result = mysqli_query($db, "SELECT * FROM blocked_ips ORDER BY blocked_at DESC LIMIT 50");
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'ip' => $row['ip'], 'reason' => $row['reason'],
            'blocked_at' => $row['blocked_at'], 'expires_at' => $row['expires_at'],
            'is_permanent' => $row['is_permanent'] == 1
        ];
    }
    return $data;
}

// ─── 새 함수: 체류시간 / 이탈률 ───

function getSessionStats($db) {
    $today = date('Y-m-d');

    // 세션별 체류시간, 페이지뷰
    $result = mysqli_query($db,
        "SELECT session_id,
                TIMESTAMPDIFF(SECOND, MIN(visit_time), MAX(visit_time)) as duration,
                COUNT(*) as page_views
         FROM visitor_logs
         WHERE DATE(visit_time) = '$today' AND is_bot = 0
           AND session_id IS NOT NULL AND session_id != ''
         GROUP BY session_id");

    $totalSessions = 0;
    $totalDuration = 0;
    $bouncedSessions = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $totalSessions++;
        $totalDuration += intval($row['duration']);
        if (intval($row['page_views']) <= 1) {
            $bouncedSessions++;
        }
    }

    $avgDuration = $totalSessions > 0 ? round($totalDuration / $totalSessions) : 0;
    $bounceRate = $totalSessions > 0 ? round(($bouncedSessions / $totalSessions) * 100, 1) : 0;
    $avgPageViews = $totalSessions > 0 ? round(($result ? mysqli_data_seek($result, 0) : 0) ?: 0) : 0;

    // 평균 페이지뷰 다시 계산
    $result2 = mysqli_query($db,
        "SELECT AVG(pv) as avg_pv FROM (
            SELECT session_id, COUNT(*) as pv
            FROM visitor_logs
            WHERE DATE(visit_time) = '$today' AND is_bot = 0
              AND session_id IS NOT NULL AND session_id != ''
            GROUP BY session_id
        ) t");
    $avgPv = mysqli_fetch_assoc($result2);

    return [
        'total_sessions' => $totalSessions,
        'avg_duration' => $avgDuration,
        'bounce_rate' => $bounceRate,
        'bounced_sessions' => $bouncedSessions,
        'avg_page_views' => round(floatval($avgPv['avg_pv'] ?? 0), 1)
    ];
}

// ─── 새 함수: 기기/브라우저/OS 분석 ───

function getDeviceStats($db) {
    $today = date('Y-m-d');

    $result = mysqli_query($db,
        "SELECT user_agent FROM visitor_logs
         WHERE DATE(visit_time) = '$today' AND is_bot = 0
           AND user_agent IS NOT NULL AND user_agent != ''");

    $devices = ['Desktop' => 0, 'Mobile' => 0, 'Tablet' => 0];
    $browsers = [];
    $os = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $ua = $row['user_agent'];

        // 기기 분류
        $deviceType = getDeviceType($ua);
        $devices[$deviceType]++;

        // 브라우저
        $browser = getBrowserName($ua);
        $browsers[$browser] = ($browsers[$browser] ?? 0) + 1;

        // OS
        $osName = getOSName($ua);
        $os[$osName] = ($os[$osName] ?? 0) + 1;
    }

    arsort($browsers);
    arsort($os);

    // 배열을 [{name, count}] 형태로 변환
    $browserList = [];
    foreach ($browsers as $name => $count) {
        $browserList[] = ['name' => $name, 'count' => $count];
    }
    $osList = [];
    foreach ($os as $name => $count) {
        $osList[] = ['name' => $name, 'count' => $count];
    }
    $deviceList = [];
    foreach ($devices as $name => $count) {
        $deviceList[] = ['name' => $name, 'count' => $count];
    }

    return [
        'devices' => $deviceList,
        'browsers' => array_slice($browserList, 0, 8),
        'os' => array_slice($osList, 0, 8)
    ];
}

// ─── 새 함수: 신규 vs 재방문 ───

function getNewReturning($db) {
    $today = date('Y-m-d');

    // 오늘 방문한 IP 중 이전에 방문한 적 있는 IP = 재방문
    $result = mysqli_query($db,
        "SELECT
            COUNT(DISTINCT CASE WHEN first_day = '$today' THEN ip END) as new_visitors,
            COUNT(DISTINCT CASE WHEN first_day < '$today' THEN ip END) as returning_visitors
         FROM (
            SELECT ip, DATE(MIN(visit_time)) as first_day
            FROM visitor_logs
            WHERE is_bot = 0
            GROUP BY ip
            HAVING ip IN (
                SELECT DISTINCT ip FROM visitor_logs
                WHERE DATE(visit_time) = '$today' AND is_bot = 0
            )
         ) t");

    $row = mysqli_fetch_assoc($result);

    // 최근 7일 추이
    $result2 = mysqli_query($db,
        "SELECT d.day,
                COUNT(DISTINCT CASE WHEN f.first_day = d.day THEN f.ip END) as new_v,
                COUNT(DISTINCT CASE WHEN f.first_day < d.day THEN f.ip END) as return_v
         FROM (
            SELECT DISTINCT DATE(visit_time) as day FROM visitor_logs
            WHERE visit_time >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND is_bot = 0
         ) d
         JOIN (
            SELECT ip, DATE(MIN(visit_time)) as first_day
            FROM visitor_logs WHERE is_bot = 0 GROUP BY ip
         ) f ON f.ip IN (
            SELECT DISTINCT ip FROM visitor_logs
            WHERE DATE(visit_time) = d.day AND is_bot = 0
         )
         GROUP BY d.day ORDER BY d.day ASC");

    $trend = [];
    while ($r = mysqli_fetch_assoc($result2)) {
        $trend[] = [
            'day' => $r['day'],
            'new' => intval($r['new_v']),
            'returning' => intval($r['return_v'])
        ];
    }

    return [
        'today' => [
            'new' => intval($row['new_visitors'] ?? 0),
            'returning' => intval($row['returning_visitors'] ?? 0)
        ],
        'trend' => $trend
    ];
}

// ─── 새 함수: 전환 추적 ───

function getConversionStats($db) {
    $today = date('Y-m-d');
    $thisMonth = date('Y-m');

    // 오늘 방문자 수
    $result = mysqli_query($db,
        "SELECT COUNT(DISTINCT ip) as visitors FROM visitor_logs
         WHERE DATE(visit_time) = '$today' AND is_bot = 0");
    $todayVisitors = intval(mysqli_fetch_assoc($result)['visitors']);

    // 오늘 주문 수
    $result = mysqli_query($db,
        "SELECT COUNT(*) as orders FROM mlangorder_printauto
         WHERE DATE(date) = '$today'");
    $todayOrders = intval(mysqli_fetch_assoc($result)['orders']);

    // 이번달 방문자 수
    $result = mysqli_query($db,
        "SELECT COUNT(DISTINCT ip) as visitors FROM visitor_logs
         WHERE DATE_FORMAT(visit_time, '%Y-%m') = '$thisMonth' AND is_bot = 0");
    $monthVisitors = intval(mysqli_fetch_assoc($result)['visitors']);

    // 이번달 주문 수
    $result = mysqli_query($db,
        "SELECT COUNT(*) as orders FROM mlangorder_printauto
         WHERE DATE_FORMAT(date, '%Y-%m') = '$thisMonth'");
    $monthOrders = intval(mysqli_fetch_assoc($result)['orders']);

    // 최근 30일 일별 전환
    $result = mysqli_query($db,
        "SELECT v.day, v.visitors, IFNULL(o.orders, 0) as orders
         FROM (
            SELECT DATE(visit_time) as day, COUNT(DISTINCT ip) as visitors
            FROM visitor_logs
            WHERE visit_time >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_bot = 0
            GROUP BY DATE(visit_time)
         ) v
         LEFT JOIN (
            SELECT DATE(date) as day, COUNT(*) as orders
            FROM mlangorder_printauto
            WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(date)
         ) o ON v.day = o.day
         ORDER BY v.day ASC");

    $trend = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $visitors = intval($row['visitors']);
        $orders = intval($row['orders']);
        $trend[] = [
            'day' => $row['day'],
            'visitors' => $visitors,
            'orders' => $orders,
            'rate' => $visitors > 0 ? round(($orders / $visitors) * 100, 1) : 0
        ];
    }

    return [
        'today' => [
            'visitors' => $todayVisitors,
            'orders' => $todayOrders,
            'rate' => $todayVisitors > 0 ? round(($todayOrders / $todayVisitors) * 100, 1) : 0
        ],
        'month' => [
            'visitors' => $monthVisitors,
            'orders' => $monthOrders,
            'rate' => $monthVisitors > 0 ? round(($monthOrders / $monthVisitors) * 100, 1) : 0
        ],
        'trend' => $trend
    ];
}

// ─── 새 함수: 진입/이탈 페이지 ───

function getEntryExitPages($db) {
    $today = date('Y-m-d');

    // 진입 페이지: 각 세션의 첫 번째 페이지
    $result = mysqli_query($db,
        "SELECT page, COUNT(*) as cnt FROM (
            SELECT session_id, page FROM visitor_logs v
            INNER JOIN (
                SELECT session_id as sid, MIN(visit_time) as first_time
                FROM visitor_logs
                WHERE DATE(visit_time) = '$today' AND is_bot = 0
                  AND session_id IS NOT NULL AND session_id != ''
                GROUP BY session_id
            ) t ON v.session_id = t.sid AND v.visit_time = t.first_time
        ) entry
        GROUP BY page ORDER BY cnt DESC LIMIT 10");

    $entryPages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $entryPages[] = ['page' => $row['page'], 'count' => intval($row['cnt'])];
    }

    // 이탈 페이지: 각 세션의 마지막 페이지
    $result = mysqli_query($db,
        "SELECT page, COUNT(*) as cnt FROM (
            SELECT session_id, page FROM visitor_logs v
            INNER JOIN (
                SELECT session_id as sid, MAX(visit_time) as last_time
                FROM visitor_logs
                WHERE DATE(visit_time) = '$today' AND is_bot = 0
                  AND session_id IS NOT NULL AND session_id != ''
                GROUP BY session_id
            ) t ON v.session_id = t.sid AND v.visit_time = t.last_time
        ) exit_p
        GROUP BY page ORDER BY cnt DESC LIMIT 10");

    $exitPages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $exitPages[] = ['page' => $row['page'], 'count' => intval($row['cnt'])];
    }

    return ['entry' => $entryPages, 'exit' => $exitPages];
}

// ─── 유틸 함수 ───

function getBrowserName($ua) {
    if (preg_match('/MSIE|Trident/i', $ua)) return 'IE';
    if (preg_match('/Edg/i', $ua)) return 'Edge';
    if (preg_match('/OPR|Opera/i', $ua)) return 'Opera';
    if (preg_match('/Chrome/i', $ua)) return 'Chrome';
    if (preg_match('/Safari/i', $ua)) return 'Safari';
    if (preg_match('/Firefox/i', $ua)) return 'Firefox';
    return 'Other';
}

function getDeviceType($ua) {
    if (preg_match('/Mobile|Android.*Mobile|iPhone|iPod|Windows Phone/i', $ua)) return 'Mobile';
    if (preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $ua)) return 'Tablet';
    return 'Desktop';
}

function getOSName($ua) {
    if (preg_match('/Windows NT 10/i', $ua)) return 'Windows 10+';
    if (preg_match('/Windows/i', $ua)) return 'Windows';
    if (preg_match('/iPhone|iPad|iPod/i', $ua)) return 'iOS';
    if (preg_match('/Android/i', $ua)) return 'Android';
    if (preg_match('/Mac OS X/i', $ua)) return 'macOS';
    if (preg_match('/Linux/i', $ua)) return 'Linux';
    return 'Other';
}
