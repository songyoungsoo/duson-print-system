<?php
/**
 * 네이버 로그인 콜백 처리
 * 네이버 인증 후 돌아왔을 때:
 *   1. CSRF 검증 (state)
 *   2. Authorization Code → Access Token 교환
 *   3. Access Token → 사용자 프로필 조회
 *   4. DB에서 사용자 매칭 또는 자동 회원가입
 *   5. 세션 설정 + 장바구니 마이그레이션
 * 
 * @since 2026-03-10
 */

// 세션 시작 (login_unified.php와 동일 설정)
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

// 환경 설정 및 DB 로드
require_once __DIR__ . '/../config.env.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_functions.php';

// auth.php의 함수 로드 (createRememberToken 등)
$connect = $db;
if (file_exists(__DIR__ . '/../includes/auth.php')) {
    include_once __DIR__ . '/../includes/auth.php';
}

$naverConfig = EnvironmentDetector::getNaverLoginConfig();
$redirect = $_SESSION['naver_redirect'] ?? '/';

// ─── ① CSRF 검증 (state 파라미터) ───
if (empty($_GET['state']) || empty($_SESSION['naver_state']) || $_GET['state'] !== $_SESSION['naver_state']) {
    unset($_SESSION['naver_state']);
    error_log('[NAVER LOGIN] state 불일치 - CSRF 의심');
    echo "<script>alert('잘못된 접근입니다.'); location.href='/member/login.php';</script>";
    exit;
}
unset($_SESSION['naver_state']);

// 사용자가 동의 거부한 경우
if (isset($_GET['error'])) {
    $error_desc = $_GET['error_description'] ?? '알 수 없는 오류';
    error_log('[NAVER LOGIN] 사용자 거부: ' . $error_desc);
    echo "<script>alert('네이버 로그인이 취소되었습니다.'); location.href='/member/login.php';</script>";
    exit;
}

// 인증 코드 확인
$code = $_GET['code'] ?? '';
if (empty($code)) {
    error_log('[NAVER LOGIN] 인증 코드 없음');
    echo "<script>alert('인증 코드가 없습니다.'); location.href='/member/login.php';</script>";
    exit;
}

// ─── ② Access Token 교환 ───
$token_url = 'https://nid.naver.com/oauth2.0/token'
    . '?grant_type=authorization_code'
    . '&client_id=' . urlencode($naverConfig['client_id'])
    . '&client_secret=' . urlencode($naverConfig['client_secret'])
    . '&code=' . urlencode($code)
    . '&state=' . urlencode($_GET['state']);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $token_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true
]);
$token_response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    error_log('[NAVER LOGIN] 토큰 요청 curl 오류: ' . $curl_error);
    echo "<script>alert('네이버 서버 연결에 실패했습니다.'); location.href='/member/login.php';</script>";
    exit;
}

$token_data = json_decode($token_response, true);
if (empty($token_data['access_token'])) {
    $error_msg = $token_data['error_description'] ?? '알 수 없는 오류';
    error_log('[NAVER LOGIN] 토큰 발급 실패: ' . $error_msg);
    echo "<script>alert('토큰 발급에 실패했습니다.'); location.href='/member/login.php';</script>";
    exit;
}

$access_token = $token_data['access_token'];

// ─── ③ 사용자 프로필 조회 ───
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://openapi.naver.com/v1/nid/me',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $access_token
    ]
]);
$profile_response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    error_log('[NAVER LOGIN] 프로필 조회 curl 오류: ' . $curl_error);
    echo "<script>alert('프로필 조회에 실패했습니다.'); location.href='/member/login.php';</script>";
    exit;
}

$profile_data = json_decode($profile_response, true);
if (!isset($profile_data['resultcode']) || $profile_data['resultcode'] !== '00') {
    $error_msg = $profile_data['message'] ?? '알 수 없는 오류';
    error_log('[NAVER LOGIN] 프로필 조회 실패: ' . $error_msg);
    echo "<script>alert('프로필 정보를 가져올 수 없습니다.'); location.href='/member/login.php';</script>";
    exit;
}

$naver_user = $profile_data['response'];
$naver_id    = $naver_user['id'] ?? '';
$naver_name  = $naver_user['name'] ?? '';
$naver_email = $naver_user['email'] ?? '';
$naver_phone = $naver_user['mobile'] ?? '';

if (empty($naver_id)) {
    error_log('[NAVER LOGIN] 네이버 ID 없음');
    echo "<script>alert('네이버 사용자 정보를 확인할 수 없습니다.'); location.href='/member/login.php';</script>";
    exit;
}

// ─── ④ 우리 DB에서 사용자 찾기 / 생성 ───
$user = null;

// 4-1. naver_id로 기존 사용자 찾기
$stmt = mysqli_prepare($db, "SELECT * FROM users WHERE naver_id = ?");
mysqli_stmt_bind_param($stmt, "s", $naver_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($user) {
    // ── 기존 네이버 사용자 → 로그인 통계 업데이트 ──
    $update = mysqli_prepare($db, "UPDATE users SET login_count = login_count + 1, last_login = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($update, "i", $user['id']);
    mysqli_stmt_execute($update);
    mysqli_stmt_close($update);
    
    error_log('[NAVER LOGIN] 기존 사용자 로그인: user_id=' . $user['id']);
} else {
    // 4-2. 이메일로 기존 로컬 계정 찾기 (연동 시도)
    $existing_by_email = null;
    if (!empty($naver_email)) {
        $stmt = mysqli_prepare($db, "SELECT * FROM users WHERE email = ? AND naver_id IS NULL");
        mysqli_stmt_bind_param($stmt, "s", $naver_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existing_by_email = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }

    if ($existing_by_email) {
        // ── 기존 로컬 계정과 네이버 연동 ──
        $update = mysqli_prepare($db, "UPDATE users SET naver_id = ?, login_type = 'naver', login_count = login_count + 1, last_login = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($update, "si", $naver_id, $existing_by_email['id']);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
        
        $user = $existing_by_email;
        $user['naver_id'] = $naver_id;
        
        error_log('[NAVER LOGIN] 기존 계정 네이버 연동: user_id=' . $user['id'] . ', email=' . $naver_email);
    } else {
        // ── 완전 신규 사용자 → 자동 회원가입 ──
        // username 중복 방지: naver_ + 랜덤
        $username = 'naver_' . substr(md5($naver_id), 0, 8);
        
        // username 중복 체크
        $check = mysqli_prepare($db, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($check, "s", $username);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);
        if (mysqli_fetch_assoc($check_result)) {
            $username = 'naver_' . substr(md5($naver_id . time()), 0, 10);
        }
        mysqli_stmt_close($check);
        
        // 랜덤 비밀번호 (네이버 로그인 전용이라 사용 안 함)
        $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        
        // 전화번호 정리 (네이버는 010-1234-5678 형태로 줌)
        $clean_phone = $naver_phone;

        $insert = mysqli_prepare($db,
            "INSERT INTO users (username, password, name, email, phone, naver_id, login_type, login_count, last_login)
             VALUES (?, ?, ?, ?, ?, ?, 'naver', 1, NOW())"
        );
        mysqli_stmt_bind_param($insert, "ssssss",
            $username, $random_password, $naver_name, $naver_email, $clean_phone, $naver_id
        );
        
        if (mysqli_stmt_execute($insert)) {
            $new_user_id = mysqli_insert_id($db);
            $user = [
                'id' => $new_user_id,
                'username' => $username,
                'name' => $naver_name
            ];
            error_log('[NAVER LOGIN] 신규 회원가입: user_id=' . $new_user_id . ', naver_id=' . $naver_id);
        } else {
            error_log('[NAVER LOGIN] 회원가입 실패: ' . mysqli_error($db));
            echo "<script>alert('회원가입 처리 중 오류가 발생했습니다.'); location.href='/member/login.php';</script>";
            exit;
        }
        mysqli_stmt_close($insert);
    }
}

// ─── ⑤ 세션 설정 (login_unified.php와 동일 패턴) ───

// 세션 고정 공격 방지 - 세션 ID 재생성
$old_session_id = session_id();
session_regenerate_id(true);
$new_session_id = session_id();

// 장바구니(shop_temp) 세션 마이그레이션
if ($old_session_id !== $new_session_id && $db) {
    $migrate_stmt = mysqli_prepare($db, "UPDATE shop_temp SET session_id = ? WHERE session_id = ?");
    if ($migrate_stmt) {
        mysqli_stmt_bind_param($migrate_stmt, 'ss', $new_session_id, $old_session_id);
        mysqli_stmt_execute($migrate_stmt);
        $migrated = mysqli_stmt_affected_rows($migrate_stmt);
        mysqli_stmt_close($migrate_stmt);
        if ($migrated > 0) {
            error_log("[NAVER LOGIN] 장바구니 세션 이전: {$old_session_id} → {$new_session_id} ({$migrated}건)");
        }
    }
}

// 세션 변수 설정 (양쪽 시스템 호환)
$_SESSION['user_id']   = $user['id'];
$_SESSION['username']  = $user['username'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['login_type'] = 'naver';

// 기존 시스템 호환
$_SESSION['id_login_ok'] = [
    'id' => $user['username'],
    'pass' => ''
];
setcookie("id_login_ok", $user['username'], 0, "/");

// ─── ⑥ 원래 페이지로 리다이렉트 ───
unset($_SESSION['naver_redirect']);

echo "<script>
    alert('네이버 계정으로 로그인되었습니다.\\n\\n좋은 하루 되시기를 바랍니다.....*^^*');
    location.href = " . json_encode($redirect) . ";
</script>";
