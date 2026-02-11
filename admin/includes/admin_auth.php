<?php
/**
 * 관리자 인증 미들웨어
 * 경로: /admin/includes/admin_auth.php
 *
 * 모든 관리자 페이지에서 require_once로 포함
 *
 * 기능:
 * - 세션 기반 인증
 * - CSRF 토큰 보호
 * - 로그인 시도 제한 (브루트포스 방지)
 * - 보안 로깅
 */

// 세션 시작 (아직 시작되지 않은 경우)
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 28800); // 8시간
    session_set_cookie_params([
        'lifetime' => 28800,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 설정 상수
define('ADMIN_SESSION_TIMEOUT', 28800);      // 8시간
define('ADMIN_MAX_LOGIN_ATTEMPTS', 5);        // 최대 로그인 시도
define('ADMIN_LOCKOUT_TIME', 900);            // 잠금 시간 (15분)
define('ADMIN_CREDENTIALS_FILE', __DIR__ . '/../../.admin_credentials.php');

/**
 * 관리자 자격증명 로드
 * 환경변수 또는 설정 파일에서 로드
 */
function getAdminCredentials() {
    // 1. 환경변수에서 먼저 확인
    $env_user = getenv('ADMIN_USERNAME');
    $env_pass = getenv('ADMIN_PASSWORD_HASH');

    if ($env_user && $env_pass) {
        return [
            'username' => $env_user,
            'password_hash' => $env_pass
        ];
    }

    // 2. 설정 파일에서 로드
    if (file_exists(ADMIN_CREDENTIALS_FILE)) {
        $credentials = include ADMIN_CREDENTIALS_FILE;
        if (is_array($credentials) && isset($credentials['username'], $credentials['password_hash'])) {
            return $credentials;
        }
    }

    // 3. 기본값 (로컬/리모트 동일) — 반드시 비밀번호 변경 필요
    return [
        'username' => 'admin',
        'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        'needs_change' => true
    ];
}

/**
 * 로그인 시도 횟수 확인 (브루트포스 방지)
 */
function checkLoginAttempts($ip) {
    $attempts_file = sys_get_temp_dir() . '/admin_login_attempts_' . md5($ip) . '.json';

    if (!file_exists($attempts_file)) {
        return ['count' => 0, 'locked_until' => 0];
    }

    $data = json_decode(file_get_contents($attempts_file), true);

    // 잠금 시간이 지났으면 초기화
    if (isset($data['locked_until']) && $data['locked_until'] < time()) {
        unlink($attempts_file);
        return ['count' => 0, 'locked_until' => 0];
    }

    return $data;
}

/**
 * 로그인 시도 기록
 */
function recordLoginAttempt($ip, $success) {
    $attempts_file = sys_get_temp_dir() . '/admin_login_attempts_' . md5($ip) . '.json';

    if ($success) {
        // 성공 시 초기화
        if (file_exists($attempts_file)) {
            unlink($attempts_file);
        }
        return;
    }

    // 실패 시 기록
    $data = checkLoginAttempts($ip);
    $data['count'] = ($data['count'] ?? 0) + 1;
    $data['last_attempt'] = time();

    if ($data['count'] >= ADMIN_MAX_LOGIN_ATTEMPTS) {
        $data['locked_until'] = time() + ADMIN_LOCKOUT_TIME;
    }

    file_put_contents($attempts_file, json_encode($data));
}

/**
 * CSRF 토큰 생성
 */
function generateCsrfToken() {
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['admin_csrf_token'];
}

/**
 * CSRF 토큰 검증
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['admin_csrf_token']) &&
           hash_equals($_SESSION['admin_csrf_token'], $token);
}

/**
 * 새 CSRF 토큰 생성 (폼 제출 후)
 */
function regenerateCsrfToken() {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['admin_csrf_token'];
}

/**
 * 관리자 로그인 상태 확인
 */
function isAdminLoggedIn() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }

    // 세션 타임아웃 확인
    if (isset($_SESSION['admin_last_activity'])) {
        if (time() - $_SESSION['admin_last_activity'] > ADMIN_SESSION_TIMEOUT) {
            adminLogout();
            return false;
        }
    }

    // 활동 시간 갱신
    $_SESSION['admin_last_activity'] = time();

    return true;
}

/**
 * 관리자 로그인 처리
 */
function adminLogin($username, $password) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // 브루트포스 체크
    $attempts = checkLoginAttempts($ip);
    if (isset($attempts['locked_until']) && $attempts['locked_until'] > time()) {
        $remaining = ceil(($attempts['locked_until'] - time()) / 60);
        return [
            'success' => false,
            'message' => "너무 많은 로그인 시도로 인해 {$remaining}분간 잠금되었습니다."
        ];
    }

    // 자격증명 확인
    $credentials = getAdminCredentials();

    if ($username === $credentials['username'] &&
        password_verify($password, $credentials['password_hash'])) {

        // 로그인 성공
        session_regenerate_id(true); // 세션 고정 공격 방지

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_id'] = 1; // 단일 관리자 시스템 (향후 DB 기반으로 전환)
        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['admin_ip'] = $ip;

        recordLoginAttempt($ip, true);
        adminLog('LOGIN', '관리자 로그인 성공');

        $result = ['success' => true, 'message' => '로그인 성공'];

        // 비밀번호 변경 필요 여부
        if (isset($credentials['needs_change']) && $credentials['needs_change']) {
            $result['needs_password_change'] = true;
            $result['message'] = '초기 비밀번호입니다. 보안을 위해 비밀번호를 변경해주세요.';
        }

        return $result;
    }

    // 로그인 실패
    recordLoginAttempt($ip, false);
    adminLog('LOGIN_FAILED', "로그인 실패: username=$username");

    $attempts = checkLoginAttempts($ip);
    $remaining = ADMIN_MAX_LOGIN_ATTEMPTS - $attempts['count'];

    return [
        'success' => false,
        'message' => "아이디 또는 비밀번호가 올바르지 않습니다. (남은 시도: {$remaining}회)"
    ];
}

/**
 * 관리자 로그아웃
 */
function adminLogout() {
    adminLog('LOGOUT', '관리자 로그아웃');

    // 세션 변수 제거
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_login_time']);
    unset($_SESSION['admin_last_activity']);
    unset($_SESSION['admin_ip']);
    unset($_SESSION['admin_csrf_token']);

    // 전체 세션 파괴가 필요한 경우
    // session_destroy();
}

/**
 * 관리자 비밀번호 변경
 */
function changeAdminPassword($current_password, $new_password) {
    $credentials = getAdminCredentials();

    // 현재 비밀번호 확인
    if (!password_verify($current_password, $credentials['password_hash'])) {
        return ['success' => false, 'message' => '현재 비밀번호가 올바르지 않습니다.'];
    }

    // 비밀번호 강도 검사
    if (strlen($new_password) < 8) {
        return ['success' => false, 'message' => '비밀번호는 8자 이상이어야 합니다.'];
    }

    if (!preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        return ['success' => false, 'message' => '비밀번호는 영문과 숫자를 포함해야 합니다.'];
    }

    // 새 비밀번호 저장
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $new_credentials = [
        'username' => $credentials['username'],
        'password_hash' => $new_hash,
        'needs_change' => false,  // 비밀번호 변경 완료 표시
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $content = "<?php\n// 관리자 자격증명 - 자동 생성됨\n// 이 파일을 직접 수정하지 마세요\nreturn " . var_export($new_credentials, true) . ";\n";

    if (file_put_contents(ADMIN_CREDENTIALS_FILE, $content)) {
        adminLog('PASSWORD_CHANGED', '관리자 비밀번호 변경됨');
        return ['success' => true, 'message' => '비밀번호가 변경되었습니다.'];
    }

    return ['success' => false, 'message' => '비밀번호 저장에 실패했습니다.'];
}

/**
 * 관리자 활동 로깅
 */
function adminLog($action, $message) {
    $log_dir = dirname(__DIR__) . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/admin_' . date('Y-m') . '.log';
    $log_entry = sprintf(
        "[%s] %s | IP: %s | User: %s | %s\n",
        date('Y-m-d H:i:s'),
        $action,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SESSION['admin_username'] ?? 'anonymous',
        $message
    );

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * 인증 필수 페이지에서 호출
 * 로그인되지 않은 경우 로그인 페이지로 리다이렉트
 */
function requireAdminAuth($redirect_url = null) {
    if (!isAdminLoggedIn()) {
        $current_url = $_SERVER['REQUEST_URI'] ?? '/admin/';
        $login_url = '/admin/mlangprintauto/login.php';

        if ($redirect_url) {
            $login_url .= '?redirect=' . urlencode($redirect_url);
        } else {
            $login_url .= '?redirect=' . urlencode($current_url);
        }

        header('Location: ' . $login_url);
        exit;
    }

    // IP 변경 감지 (세션 하이재킹 방지)
    if (isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
        adminLog('SECURITY', 'IP 변경 감지 - 세션 무효화');
        adminLogout();
        header('Location: /admin/mlangprintauto/login.php?error=session_invalid');
        exit;
    }
}

/**
 * 안전한 에러 메시지 반환 (SQL 에러 숨김)
 */
function safeErrorMessage($technical_error, $user_message = '처리 중 오류가 발생했습니다.') {
    // 기술적 에러는 로그에만 기록
    error_log('[ADMIN ERROR] ' . $technical_error);
    adminLog('ERROR', $technical_error);

    // 사용자에게는 일반적인 메시지만 표시
    return $user_message;
}

/**
 * CSRF 토큰 HTML 히든 필드 생성
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

/**
 * POST 요청 시 CSRF 검증
 */
function validateCsrfOnPost() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($token)) {
            adminLog('SECURITY', 'CSRF 토큰 검증 실패');
            http_response_code(403);
            die('보안 토큰이 유효하지 않습니다. 페이지를 새로고침 후 다시 시도해주세요.');
        }
        regenerateCsrfToken();
    }
}
