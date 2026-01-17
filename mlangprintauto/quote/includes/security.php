<?php
/**
 * 견적서 시스템 보안 함수
 * - 인증 체크
 * - CSRF 토큰 관리
 *
 * @package DusonPrint
 * @since 2026-01-17
 */

/**
 * 관리자 인증 확인
 *
 * @param bool $jsonResponse JSON 응답 여부 (API용)
 * @return bool 인증 성공 여부
 */
function requireAdminAuth($jsonResponse = true) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 세션에 관리자 정보가 있는지 확인
    if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
        if ($jsonResponse) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => '인증이 필요합니다. 로그인 후 다시 시도해주세요.',
                'error_code' => 'AUTH_REQUIRED'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        return false;
    }

    return true;
}

/**
 * CSRF 토큰 생성
 *
 * @return string CSRF 토큰
 */
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * CSRF 토큰 검증
 *
 * @param string|null $token 검증할 토큰 (null이면 POST/Header에서 자동 추출)
 * @param bool $jsonResponse JSON 응답 여부
 * @return bool 검증 성공 여부
 */
function verifyCsrfToken($token = null, $jsonResponse = true) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 토큰 추출 (우선순위: 파라미터 > POST > Header)
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }

    // 세션 토큰과 비교
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        if ($jsonResponse) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => '보안 토큰이 유효하지 않습니다. 페이지를 새로고침 후 다시 시도해주세요.',
                'error_code' => 'CSRF_INVALID'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        return false;
    }

    return true;
}

/**
 * CSRF 토큰 재생성 (로그인/중요 작업 후)
 *
 * @return string 새 CSRF 토큰
 */
function regenerateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * API 보안 체크 (인증 + CSRF)
 *
 * @param bool $requireCsrf CSRF 검증 필요 여부
 * @return void
 */
function apiSecurityCheck($requireCsrf = true) {
    requireAdminAuth(true);

    if ($requireCsrf) {
        verifyCsrfToken(null, true);
    }
}

/**
 * 안전한 JSON 응답 (DB 오류 노출 방지)
 *
 * @param bool $success 성공 여부
 * @param string $message 사용자 메시지
 * @param array $data 추가 데이터
 * @param string|null $internalError 내부 오류 (로그만 기록)
 */
function safeJsonResponse($success, $message, $data = [], $internalError = null) {
    if ($internalError) {
        error_log("[Quote API Error] " . $internalError);
    }

    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * CSRF 토큰 HTML input 생성
 *
 * @return string HTML hidden input
 */
function csrfTokenInput() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * CSRF 토큰 메타 태그 생성 (AJAX용)
 *
 * @return string HTML meta tag
 */
function csrfTokenMeta() {
    $token = generateCsrfToken();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
}
