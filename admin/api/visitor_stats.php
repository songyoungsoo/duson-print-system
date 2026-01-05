<?php
/**
 * 방문자 통계 API
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/../../db.php';

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

/**
 * 방문자 요약 통계
 */
function getVisitorSummary($db) {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $thisMonth = date('Y-m');

    // 오늘 통계
    $result = mysqli_query($db,
        "SELECT COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors, COUNT(DISTINCT session_id) as sessions
         FROM visitor_logs WHERE DATE(visit_time) = '$today' AND is_bot = 0");
    $todayData = mysqli_fetch_assoc($result);

    // 어제 통계 (비교용)
    $result = mysqli_query($db,
        "SELECT COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs WHERE DATE(visit_time) = '$yesterday' AND is_bot = 0");
    $yesterdayData = mysqli_fetch_assoc($result);

    // 이번달 통계
    $result = mysqli_query($db,
        "SELECT COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs WHERE DATE_FORMAT(visit_time, '%Y-%m') = '$thisMonth' AND is_bot = 0");
    $monthData = mysqli_fetch_assoc($result);

    // 전체 통계
    $result = mysqli_query($db,
        "SELECT COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs WHERE is_bot = 0");
    $totalData = mysqli_fetch_assoc($result);

    // 현재 접속자 (5분 이내)
    $fiveMinAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $result = mysqli_query($db,
        "SELECT COUNT(DISTINCT ip) as active FROM visitor_logs
         WHERE visit_time >= '$fiveMinAgo' AND is_bot = 0");
    $activeData = mysqli_fetch_assoc($result);

    // 봇 방문 수
    $result = mysqli_query($db,
        "SELECT COUNT(*) as bot_visits FROM visitor_logs
         WHERE DATE(visit_time) = '$today' AND is_bot = 1");
    $botData = mysqli_fetch_assoc($result);

    // 증감률 계산
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

/**
 * 시간대별 방문 통계 (오늘)
 */
function getHourlyStats($db) {
    $today = date('Y-m-d');

    $result = mysqli_query($db,
        "SELECT HOUR(visit_time) as hour, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs
         WHERE DATE(visit_time) = '$today' AND is_bot = 0
         GROUP BY HOUR(visit_time)
         ORDER BY hour");

    $data = array_fill(0, 24, ['visits' => 0, 'unique' => 0]);

    while ($row = mysqli_fetch_assoc($result)) {
        $hour = intval($row['hour']);
        $data[$hour] = [
            'visits' => intval($row['visits']),
            'unique' => intval($row['unique_visitors'])
        ];
    }

    return $data;
}

/**
 * 인기 페이지 TOP 10
 */
function getTopPages($db) {
    $today = date('Y-m-d');

    $result = mysqli_query($db,
        "SELECT page, COUNT(*) as views
         FROM visitor_logs
         WHERE DATE(visit_time) = '$today' AND is_bot = 0
         GROUP BY page
         ORDER BY views DESC
         LIMIT 10");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'page' => $row['page'],
            'views' => intval($row['views'])
        ];
    }

    return $data;
}

/**
 * 유입 경로 TOP 10
 */
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
         GROUP BY source
         ORDER BY visits DESC
         LIMIT 10");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'source' => $row['source'],
            'visits' => intval($row['visits'])
        ];
    }

    return $data;
}

/**
 * 의심스러운 IP 목록
 */
function getSuspiciousIPs($db, $threshold = 100) {
    $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

    $result = mysqli_query($db,
        "SELECT ip, COUNT(*) as hit_count, MAX(visit_time) as last_visit,
                COUNT(DISTINCT page) as unique_pages
         FROM visitor_logs
         WHERE visit_time >= '$oneHourAgo' AND is_bot = 0
         GROUP BY ip
         HAVING hit_count >= $threshold
         ORDER BY hit_count DESC
         LIMIT 20");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $count = intval($row['hit_count']);
        $data[] = [
            'ip' => $row['ip'],
            'count' => $count,
            'unique_pages' => intval($row['unique_pages']),
            'last_visit' => $row['last_visit'],
            'status' => $count >= 500 ? 'critical' : ($count >= 200 ? 'warning' : 'watch')
        ];
    }

    return $data;
}

/**
 * 실시간 방문자 (최근 5분)
 */
function getRealtimeVisitors($db) {
    $fiveMinAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));

    $result = mysqli_query($db,
        "SELECT ip, page, visit_time, user_agent
         FROM visitor_logs
         WHERE visit_time >= '$fiveMinAgo' AND is_bot = 0
         ORDER BY visit_time DESC
         LIMIT 20");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'ip' => $row['ip'],
            'page' => $row['page'],
            'time' => $row['visit_time'],
            'browser' => getBrowserName($row['user_agent'])
        ];
    }

    return $data;
}

/**
 * 일별 방문자 추이
 */
function getDailyVisitors($db, $days = 30) {
    $result = mysqli_query($db,
        "SELECT DATE(visit_time) as day, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_visitors
         FROM visitor_logs
         WHERE visit_time >= DATE_SUB(NOW(), INTERVAL $days DAY) AND is_bot = 0
         GROUP BY DATE(visit_time)
         ORDER BY day ASC");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'day' => $row['day'],
            'visits' => intval($row['visits']),
            'unique' => intval($row['unique_visitors'])
        ];
    }

    return $data;
}

/**
 * 차단된 IP 목록
 */
function getBlockedIPs($db) {
    $result = mysqli_query($db,
        "SELECT * FROM blocked_ips ORDER BY blocked_at DESC LIMIT 50");

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'ip' => $row['ip'],
            'reason' => $row['reason'],
            'blocked_at' => $row['blocked_at'],
            'expires_at' => $row['expires_at'],
            'is_permanent' => $row['is_permanent'] == 1
        ];
    }

    return $data;
}

/**
 * User-Agent에서 브라우저 이름 추출
 */
function getBrowserName($ua) {
    if (preg_match('/MSIE|Trident/i', $ua)) return 'IE';
    if (preg_match('/Edge/i', $ua)) return 'Edge';
    if (preg_match('/Chrome/i', $ua)) return 'Chrome';
    if (preg_match('/Safari/i', $ua)) return 'Safari';
    if (preg_match('/Firefox/i', $ua)) return 'Firefox';
    if (preg_match('/Opera/i', $ua)) return 'Opera';
    return 'Other';
}
