<?php
/**
 * 네이버 로그인 리다이렉트
 * 네이버 인증 페이지로 사용자를 보내는 역할
 * 
 * @since 2026-03-10
 */

// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 28800);
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

// 이미 로그인 상태면 메인으로
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

// 환경 설정 로드
require_once __DIR__ . '/../config.env.php';
$naverConfig = EnvironmentDetector::getNaverLoginConfig();

// CSRF 방지용 state 토큰 생성
$state = bin2hex(random_bytes(16));
$_SESSION['naver_state'] = $state;

// 로그인 후 돌아갈 페이지 저장 (login.php로 되돌아가는 루프 방지)
$redirect = $_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? '/';
if (strpos($redirect, 'login') !== false || strpos($redirect, 'register') !== false) {
    $redirect = '/';
}
$_SESSION['naver_redirect'] = $redirect;

// 네이버 인증 페이지로 리다이렉트
$auth_url = 'https://nid.naver.com/oauth2.0/authorize'
    . '?response_type=code'
    . '&client_id=' . urlencode($naverConfig['client_id'])
    . '&redirect_uri=' . urlencode($naverConfig['callback_url'])
    . '&state=' . $state;

header('Location: ' . $auth_url);
exit;
