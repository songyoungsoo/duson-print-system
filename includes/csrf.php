<?php
/**
 * CSRF 토큰 보호 헬퍼 (세션 + 쿠키 이중 방식)
 *
 * - 세션 기반 검증 (기본)
 * - 쿠키 기반 Double-Submit 패턴 (세션 유실 시 fallback)
 * - Plesk 환경에서 세션 유실 문제 대응 (2026-02-27)
 */

define('CSRF_COOKIE_NAME', '_csrf_cookie');

function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // 쿠키에도 동일 토큰 저장 (Double-Submit 패턴 fallback)
    if (!headers_sent()) {
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie(CSRF_COOKIE_NAME, $_SESSION['csrf_token'], [
            'expires' => time() + 7200,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    return $_SESSION['csrf_token'];
}

function csrf_field() {
    echo '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = $_POST['_csrf_token'] ?? '';

    if (empty($token)) {
        return false;
    }

    // 1차: 세션 기반 검증
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }

    // 2차: 쿠키 기반 검증 (세션 유실 시 fallback)
    $cookie_token = $_COOKIE[CSRF_COOKIE_NAME] ?? '';
    if (!empty($cookie_token) && hash_equals($cookie_token, $token)) {
        error_log('[CSRF] Session fallback used - session token empty, cookie matched. Session ID: ' . session_id());
        return true;
    }

    error_log('[CSRF] Verification failed. POST token: ' . substr($token, 0, 8) . '..., Session token: ' . (!empty($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 8) . '...' : 'EMPTY') . ', Cookie token: ' . (!empty($cookie_token) ? substr($cookie_token, 0, 8) . '...' : 'EMPTY') . ', Session ID: ' . session_id());
    return false;
}

function csrf_verify_or_die() {
    if (!csrf_verify()) {
        http_response_code(403);
        die('잘못된 요청입니다. 페이지를 새로고침 후 다시 시도해주세요.');
    }
}

// 로그인 전용: 403 대신 JavaScript alert로 에러 표시 (Plesk 가로채기 방지)
function csrf_verify_or_alert($redirect_url = 'login.php') {
    if (!csrf_verify()) {
        echo "<script>
            alert('보안 토큰이 만료되었습니다. 페이지를 다시 로드합니다.');
            location.href = '$redirect_url';
        </script>";
        exit;
    }
}
