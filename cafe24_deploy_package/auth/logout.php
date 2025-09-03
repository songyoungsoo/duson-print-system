<?php
/**
 * 로그아웃 처리 API
 * 경로: /auth/logout.php
 */

session_start();

// 기존 시스템 쿠키 삭제 (호환성)
setcookie("id_login_ok", '', time() - 3600, "/");

// 세션 데이터 완전 삭제
$_SESSION = array();

// 세션 쿠키 삭제
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 세션 파괴
session_destroy();

// 새로운 세션 시작 (깔끔한 상태)
session_start();

// 성공 메시지 설정
$_SESSION['logout_message'] = '성공적으로 로그아웃되었습니다.';

// 리다이렉트 URL 처리
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '/';

// 보안: 외부 URL로의 리다이렉트 방지
if (filter_var($redirect, FILTER_VALIDATE_URL)) {
    $parsed = parse_url($redirect);
    if ($parsed['host'] !== $_SERVER['HTTP_HOST']) {
        $redirect = '/';
    }
}

// 메인 페이지로 리다이렉트
header('Location: ' . $redirect);
exit;
?>