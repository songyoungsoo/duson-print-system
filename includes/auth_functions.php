<?php
/**
 * 공통 인증 관련 함수들
 * 모든 시스템에서 공통으로 사용하는 로그인/세션 관련 함수
 * 경로: includes/auth_functions.php
 */

/**
 * 로그인 상태 확인 함수
 */
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);
    }
}

/**
 * 사용자 정보 반환 함수
 */
if (!function_exists('getUserInfo')) {
    function getUserInfo() {
        if (!isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? $_SESSION['id_login_ok'] ?? '',
            'username' => $_SESSION['username'] ?? '',
            'name' => $_SESSION['user_name'] ?? $_SESSION['name_login_ok'] ?? ''
        ];
    }
}

/**
 * 사용자 이름 반환 함수
 */
if (!function_exists('getUserName')) {
    function getUserName() {
        $userInfo = getUserInfo();
        return $userInfo ? $userInfo['name'] : '';
    }
}

/**
 * 사용자 ID 반환 함수
 */
if (!function_exists('getUserId')) {
    function getUserId() {
        $userInfo = getUserInfo();
        return $userInfo ? $userInfo['id'] : '';
    }
}

/**
 * 관리자 권한 확인 함수
 */
if (!function_exists('isAdmin')) {
    function isAdmin() {
        $userInfo = getUserInfo();
        return $userInfo && ($userInfo['username'] === 'admin' || $userInfo['id'] === '1');
    }
}

/**
 * 로그인 필수 페이지 체크
 * (auth.php에 동일 함수가 있으면 그것을 사용)
 */
if (!function_exists('requireLogin')) {
    function requireLogin($redirect_url = '/') {
        if (!isLoggedIn()) {
            header("Location: $redirect_url");
            exit;
        }
    }
}
?>