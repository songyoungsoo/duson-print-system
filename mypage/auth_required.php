<?php
/**
 * 인증 미들웨어
 * 모든 마이페이지에서 include하여 로그인 체크
 *
 * 지원 세션:
 * - 신규: $_SESSION['user_id'], $_SESSION['username']
 * - 레거시: $_SESSION['id_login_ok']
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

// DB 연결
require_once __DIR__ . '/../db.php';

// 세션 시작 (아직 시작되지 않은 경우)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 레거시 세션 호환성 처리 (id_login_ok → user_id/username 변환)
// auth.php 로드 전에 실행해야 함!
if (!isset($_SESSION['user_id']) && isset($_SESSION['id_login_ok'])) {
    $legacy_user = $_SESSION['id_login_ok'];
    if (is_array($legacy_user) && isset($legacy_user['id'])) {
        $_SESSION['user_id'] = $legacy_user['id'];
        $_SESSION['username'] = $legacy_user['id'];
        $_SESSION['user_name'] = $legacy_user['id'];
    }
}

// 인증 시스템 로드 (이제 user_id가 설정되어 있음)
require_once __DIR__ . '/../includes/auth.php';

// 로그인 체크
if (!$is_logged_in) {
    // 현재 URL 저장 (로그인 후 돌아오기)
    $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];

    // 로그인 페이지로 리다이렉트
    header("Location: /member/login.php");
    exit;
}

// 사용자 정보 전역 변수
$current_user = [
    'id' => $_SESSION['user_id'] ?? '',
    'username' => $_SESSION['username'] ?? '',
    'name' => $_SESSION['user_name'] ?? $_SESSION['username'] ?? '',
    'is_auto_login' => $_SESSION['auto_login'] ?? false,
    'email' => ''
];

// DB에서 email과 name 조회 (mlangorder_printauto에서 주문 조회 시 필요)
$username = $_SESSION['username'] ?? '';
if ($db && $username) {
    $stmt = mysqli_prepare($db, "SELECT email, name FROM member WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $current_user['email'] = $row['email'] ?? '';
            // name이 있으면 실제 이름 사용
            if (!empty($row['name'])) {
                $current_user['name'] = $row['name'];
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// 세션 활동 시간 갱신 (함수가 정의된 경우에만)
if (function_exists('refreshSessionActivity')) {
    refreshSessionActivity();
}
?>
