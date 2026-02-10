<?php
/**
 * CSRF 토큰 보호 헬퍼
 *
 * 사용법:
 *   폼에 삽입: <?php csrf_field(); ?>
 *   검증: csrf_verify() 또는 csrf_verify_or_die()
 */

/**
 * CSRF 토큰 반환 (없으면 생성)
 */
function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * hidden input 출력
 */
function csrf_field() {
    echo '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * CSRF 토큰 검증
 * @return bool
 */
function csrf_verify() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = $_POST['_csrf_token'] ?? '';
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF 검증 실패 시 중단
 */
function csrf_verify_or_die() {
    if (!csrf_verify()) {
        http_response_code(403);
        die('잘못된 요청입니다. 페이지를 새로고침 후 다시 시도해주세요.');
    }
}
