<?php
/**
 * 로그아웃 처리 API
 * 경로: /auth/logout.php
 */

session_start();

$is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

// 기존 시스템 쿠키 삭제 (모든 경로 + Secure/SameSite 일치)
setcookie('id_login_ok', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => $is_https, 'httponly' => true, 'samesite' => 'Lax']);
setcookie('id_login_ok', '', ['expires' => time() - 3600, 'path' => '/member/', 'secure' => $is_https, 'httponly' => true, 'samesite' => 'Lax']);
// 속성 없이도 삭제 (레거시 쿠키 대응)
setcookie('id_login_ok', '', time() - 3600, '/');
unset($_COOKIE['id_login_ok']);

// 세션 데이터 완전 삭제
$_SESSION = array();

    // 세션 쿠키 삭제
    if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}

// 세션 파괴
session_destroy();

// 새로운 세션 시작
session_start();
$_SESSION['logout_message'] = '성공적으로 로그아웃되었습니다.';

// 리다이렉트
    $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '/';
    if (filter_var($redirect, FILTER_VALIDATE_URL)) {
    $parsed = parse_url($redirect);
    if (($parsed['host'] ?? '') !== $_SERVER['HTTP_HOST']) {
        $redirect = '/';
    }
}

header('Location: ' . $redirect);
exit;