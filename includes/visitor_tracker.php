<?php
/**
 * 방문자 추적 시스템
 * 모든 페이지에 include하여 방문 기록
 *
 * 사용법: include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visitor_tracker.php';
 */

// 이미 추적되었으면 중복 실행 방지
if (defined('VISITOR_TRACKED')) {
    return;
}
define('VISITOR_TRACKED', true);

// DB 연결
if (!isset($db)) {
    require_once __DIR__ . '/../db.php';
}

/**
 * 방문자 추적 클래스
 */
class VisitorTracker {
    private $db;
    private $ip;
    private $userAgent;
    private $page;
    private $referer;
    private $sessionId;

    // 봇 패턴 (검색엔진, 크롤러)
    private $botPatterns = [
        'googlebot', 'bingbot', 'yandex', 'baiduspider', 'facebookexternalhit',
        'twitterbot', 'rogerbot', 'linkedinbot', 'embedly', 'slurp',
        'duckduckbot', 'semrushbot', 'ahrefsbot', 'dotbot', 'petalbot',
        'mj12bot', 'seznambot', 'ia_archiver', 'curl', 'wget', 'python',
        'scrapy', 'postman', 'insomnia'
    ];

    // 무시할 경로 (관리자, API 등)
    private $ignorePaths = [
        '/admin/api/',
        '/api/',
        '/includes/',
        '/favicon.ico',
        '/robots.txt'
    ];

    public function __construct($db) {
        $this->db = $db;
        $this->ip = $this->getClientIP();
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->page = $_SERVER['REQUEST_URI'] ?? '';
        $this->referer = $_SERVER['HTTP_REFERER'] ?? '';
        $this->sessionId = session_id() ?: md5($this->ip . date('Y-m-d'));
    }

    /**
     * 실제 클라이언트 IP 가져오기 (프록시 고려)
     */
    private function getClientIP() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // 일반 프록시
            'HTTP_X_REAL_IP',            // Nginx 프록시
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // 쉼표로 구분된 경우 첫 번째 IP
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // 유효한 IP인지 검증
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * 봇인지 확인
     */
    private function isBot() {
        $ua = strtolower($this->userAgent);
        foreach ($this->botPatterns as $bot) {
            if (strpos($ua, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 무시할 경로인지 확인
     */
    private function shouldIgnore() {
        foreach ($this->ignorePaths as $path) {
            if (strpos($this->page, $path) === 0) {
                return true;
            }
        }
        // 정적 파일 무시
        $ext = pathinfo(parse_url($this->page, PHP_URL_PATH), PATHINFO_EXTENSION);
        $ignoreExt = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf'];
        if (in_array(strtolower($ext), $ignoreExt)) {
            return true;
        }
        return false;
    }

    /**
     * IP가 차단되었는지 확인
     */
    public function isBlocked() {
        $ip = mysqli_real_escape_string($this->db, $this->ip);
        $result = mysqli_query($this->db,
            "SELECT id FROM blocked_ips
             WHERE ip = '$ip'
             AND (expires_at IS NULL OR expires_at > NOW() OR is_permanent = 1)"
        );

        // 쿼리 실패 시 (테이블 없음 등) false 반환
        if ($result === false) {
            return false;
        }

        return mysqli_num_rows($result) > 0;
    }

    /**
     * 방문 기록 저장
     */
    public function track() {
        // 무시할 경로면 종료
        if ($this->shouldIgnore()) {
            return false;
        }

        // 차단된 IP면 종료 (선택적으로 차단 페이지 표시 가능)
        if ($this->isBlocked()) {
            // header('HTTP/1.1 403 Forbidden');
            // exit('Access Denied');
            return false;
        }

        $isBot = $this->isBot() ? 1 : 0;

        // 데이터 이스케이프
        $ip = mysqli_real_escape_string($this->db, $this->ip);
        $page = mysqli_real_escape_string($this->db, substr($this->page, 0, 500));
        $userAgent = mysqli_real_escape_string($this->db, substr($this->userAgent, 0, 500));
        $referer = mysqli_real_escape_string($this->db, substr($this->referer, 0, 500));
        $sessionId = mysqli_real_escape_string($this->db, $this->sessionId);
        $now = date('Y-m-d H:i:s');

        $sql = "INSERT INTO visitor_logs (ip, page, user_agent, referer, visit_time, session_id, is_bot)
                VALUES ('$ip', '$page', '$userAgent', '$referer', '$now', '$sessionId', $isBot)";

        return mysqli_query($this->db, $sql);
    }

    /**
     * 부정 클릭 감지 (1시간 내 동일 IP 접속 횟수)
     */
    public static function detectSuspicious($db, $threshold = 100) {
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $sql = "SELECT ip, COUNT(*) as hit_count,
                       MAX(visit_time) as last_visit
                FROM visitor_logs
                WHERE visit_time >= '$oneHourAgo'
                AND is_bot = 0
                GROUP BY ip
                HAVING hit_count >= $threshold
                ORDER BY hit_count DESC
                LIMIT 20";

        $result = mysqli_query($db, $sql);
        $suspicious = [];

        // 쿼리 실패 시 빈 배열 반환
        if ($result === false) {
            return $suspicious;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $suspicious[] = [
                'ip' => $row['ip'],
                'count' => intval($row['hit_count']),
                'last_visit' => $row['last_visit'],
                'status' => $row['hit_count'] >= 500 ? 'critical' : 'warning'
            ];
        }

        return $suspicious;
    }

    /**
     * IP 차단
     */
    public static function blockIP($db, $ip, $reason = '', $hours = 24, $permanent = false) {
        $ip = mysqli_real_escape_string($db, $ip);
        $reason = mysqli_real_escape_string($db, $reason);
        $now = date('Y-m-d H:i:s');
        $expires = $permanent ? 'NULL' : "'" . date('Y-m-d H:i:s', strtotime("+$hours hours")) . "'";
        $isPermanent = $permanent ? 1 : 0;

        $sql = "INSERT INTO blocked_ips (ip, reason, blocked_at, expires_at, is_permanent)
                VALUES ('$ip', '$reason', '$now', $expires, $isPermanent)
                ON DUPLICATE KEY UPDATE
                reason = '$reason', blocked_at = '$now', expires_at = $expires, is_permanent = $isPermanent";

        return mysqli_query($db, $sql);
    }

    /**
     * IP 차단 해제
     */
    public static function unblockIP($db, $ip) {
        $ip = mysqli_real_escape_string($db, $ip);
        return mysqli_query($db, "DELETE FROM blocked_ips WHERE ip = '$ip'");
    }
}

// 자동 추적 실행
if (isset($db)) {
    $tracker = new VisitorTracker($db);
    $tracker->track();
}
