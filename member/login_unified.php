<?php
/**
 * 통합 로그인 처리 시스템
 * users 테이블 기반 (기존 member 테이블과 호환)
 */

// 세션 수명 8시간 통일 (auth.php, admin_auth.php와 동일)
$session_lifetime = 28800;
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', $session_lifetime);
    session_set_cookie_params([
        'lifetime' => $session_lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// CSRF 검증
include_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify_or_die();

    $mode = $_POST['mode'] ?? '';

    if ($mode == "member_login") {
        include "../db.php";

        // 자동로그인 함수 로드
        include_once __DIR__ . '/../includes/auth_functions.php';
        if (file_exists(__DIR__ . '/../includes/auth.php')) {
            // auth.php의 함수만 로드 (세션은 이미 시작됨)
            $connect = $db;
            include_once __DIR__ . '/../includes/auth.php';
        }

        $id = mysqli_real_escape_string($db, $_POST['id'] ?? '');
        $pass = $_POST['pass'] ?? '';
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '/';
        $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';
        
        if (empty($id) || empty($pass)) {
            echo "<script>
                    alert('아이디와 비밀번호를 입력해주세요.');
                    history.back();
                  </script>";
            exit;
        }
        
        // 1. 신규 users 테이블에서 확인
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            $login_success = false;
            $stored_password = $user['password'];
            $need_hash_upgrade = false;

            // bcrypt 해시인 경우 ($2y$로 시작하고 60자)
            if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
                // 해시된 비밀번호 확인
                if (password_verify($pass, $stored_password)) {
                    $login_success = true;
                }
            } else {
                // 평문 비밀번호인 경우 직접 비교
                if ($pass === $stored_password) {
                    $login_success = true;
                    $need_hash_upgrade = true;
                }
            }

            // 평문 비밀번호 로그인 성공 시 해시로 업그레이드
            if ($login_success && $need_hash_upgrade) {
                $new_hash = password_hash($pass, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($db, $update_query);
                mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $user['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
            
            if ($login_success) {
                // 🔐 세션 고정 공격 방지 - 세션 ID 재생성
                // 장바구니 세션 ID를 먼저 저장 (regenerate 후 shop_temp 업데이트용)
                $old_session_id = session_id();
                session_regenerate_id(true);
                $new_session_id = session_id();
                
                // 🔄 장바구니 데이터를 새 세션 ID로 이전
                if ($old_session_id !== $new_session_id && $db) {
                    $migrate_stmt = mysqli_prepare($db, "UPDATE shop_temp SET session_id = ? WHERE session_id = ?");
                    if ($migrate_stmt) {
                        mysqli_stmt_bind_param($migrate_stmt, 'ss', $new_session_id, $old_session_id);
                        mysqli_stmt_execute($migrate_stmt);
                        mysqli_stmt_close($migrate_stmt);
                    }
                }

                // 로그인 통계 업데이트
                $login_count = ($user['login_count'] ?? 0) + 1;
                $login_time = date("Y-m-d H:i:s");
                
                $update_stats = "UPDATE users SET login_count = ?, last_login = ? WHERE id = ?";
                $stats_stmt = mysqli_prepare($db, $update_stats);
                mysqli_stmt_bind_param($stats_stmt, "isi", $login_count, $login_time, $user['id']);
                mysqli_stmt_execute($stats_stmt);
                mysqli_stmt_close($stats_stmt);
                
                // 세션 설정 (양쪽 시스템 호환)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['name'];
                
                // 기존 시스템 호환
                $_SESSION['id_login_ok'] = array(
                    'id' => $user['username'],
                    'pass' => $pass
                );

                setcookie("id_login_ok", $user['username'], 0, "/");

                // 자동 로그인 처리 (remember_me 체크 시)
                if ($remember_me && function_exists('createRememberToken')) {
                    $token = createRememberToken($db, $user['id']);
                    if ($token) {
                        setRememberMeCookie($token);
                    }
                }

                echo "<script>
                        alert('정상적으로 로그인 되셨습니다.\\n\\n좋은 하루 되시기를 바랍니다.....*^^*');
                        " . (!empty($redirect) ? "location.href = '$redirect';" : "location.href = '../';") . "
                      </script>";
                exit;
            }
        }

        // 2. 로그인 실패 시 기존 member 테이블에서 확인 (fallback)
        $member_query = "SELECT * FROM member WHERE id = ?";
        $member_stmt = mysqli_prepare($db, $member_query);
        mysqli_stmt_bind_param($member_stmt, "s", $id);
        mysqli_stmt_execute($member_stmt);
        $member_result = mysqli_stmt_get_result($member_stmt);

        if ($member = mysqli_fetch_assoc($member_result)) {
            if ($pass === $member['pass']) {
                // 🔧 FIX: users 테이블에 이미 존재하는지 확인
                $check_users = "SELECT id, name FROM users WHERE username = ?";
                $check_stmt = mysqli_prepare($db, $check_users);
                mysqli_stmt_bind_param($check_stmt, "s", $member['id']);
                mysqli_stmt_execute($check_stmt);
                $users_result = mysqli_stmt_get_result($check_stmt);

                if ($existing_user = mysqli_fetch_assoc($users_result)) {
                    // ✅ 이미 마이그레이션됨 - 바로 로그인
                    $login_count = ($member['Logincount'] ?? 0) + 1;
                    $last_login = date("Y-m-d H:i:s");

                    // 🔐 세션 고정 공격 방지 - 세션 ID 재생성
                    // 장바구니 세션 ID를 먼저 저장 (regenerate 후 shop_temp 업데이트용)
                    $old_session_id = session_id();
                    session_regenerate_id(true);
                    $new_session_id = session_id();
                    
                    // 🔄 장바구니 데이터를 새 세션 ID로 이전
                    if ($old_session_id !== $new_session_id && $db) {
                        $cart_migrate_stmt = mysqli_prepare($db, "UPDATE shop_temp SET session_id = ? WHERE session_id = ?");
                        if ($cart_migrate_stmt) {
                            mysqli_stmt_bind_param($cart_migrate_stmt, 'ss', $new_session_id, $old_session_id);
                            mysqli_stmt_execute($cart_migrate_stmt);
                            mysqli_stmt_close($cart_migrate_stmt);
                        }
                    }

                    // 세션 설정
                    $_SESSION['user_id'] = $existing_user['id'];
                    $_SESSION['username'] = $member['id'];
                    $_SESSION['user_name'] = $existing_user['name'];
                    $_SESSION['id_login_ok'] = array('id' => $member['id'], 'pass' => $pass);

                    setcookie("id_login_ok", $member['id'], 0, "/");

                    // 자동 로그인 처리
                    if ($remember_me && function_exists('createRememberToken')) {
                        $token = createRememberToken($db, $existing_user['id']);
                        if ($token) { setRememberMeCookie($token); }
                    }

                    // member 테이블 업데이트
                    $update_member = "UPDATE member SET Logincount = ?, EndLogin = ? WHERE id = ?";
                    $update_stmt = mysqli_prepare($db, $update_member);
                    mysqli_stmt_bind_param($update_stmt, "iss", $login_count, $last_login, $member['id']);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);

                    echo "<script>
                            alert('정상적으로 로그인 되셨습니다.\\n\\n좋은 하루 되시기를 바랍니다.....*^^*');
                            " . (!empty($redirect) ? "location.href = '$redirect';" : "location.href = '../';") . "
                          </script>";
                    exit;
                } else {
                    // ✅ 아직 마이그레이션 안됨 - 새로 등록
                    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

                    $migrate_query = "INSERT INTO users (username, password, name, email, phone, login_count, last_login)
                                     VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $migrate_stmt = mysqli_prepare($db, $migrate_query);
                    $login_count = ($member['Logincount'] ?? 0) + 1;
                    $last_login = date("Y-m-d H:i:s");

                    mysqli_stmt_bind_param($migrate_stmt, "sssssis",
                        $member['id'], $hashed_password, $member['name'],
                        $member['email'], $member['phone'], $login_count, $last_login
                    );

                    if (mysqli_stmt_execute($migrate_stmt)) {
                        // 🔐 세션 고정 공격 방지 - 세션 ID 재생성
                        // 장바구니 세션 ID를 먼저 저장 (regenerate 후 shop_temp 업데이트용)
                        $old_session_id = session_id();
                        session_regenerate_id(true);
                        $new_session_id = session_id();
                        
                        // 🔄 장바구니 데이터를 새 세션 ID로 이전
                        if ($old_session_id !== $new_session_id && $db) {
                            $cart_migrate_stmt = mysqli_prepare($db, "UPDATE shop_temp SET session_id = ? WHERE session_id = ?");
                            if ($cart_migrate_stmt) {
                                mysqli_stmt_bind_param($cart_migrate_stmt, 'ss', $new_session_id, $old_session_id);
                                mysqli_stmt_execute($cart_migrate_stmt);
                                mysqli_stmt_close($cart_migrate_stmt);
                            }
                        }

                        // 세션 설정
                        $_SESSION['user_id'] = mysqli_insert_id($db);
                        $_SESSION['username'] = $member['id'];
                        $_SESSION['user_name'] = $member['name'];
                        $_SESSION['id_login_ok'] = array('id' => $member['id'], 'pass' => $pass);

                        setcookie("id_login_ok", $member['id'], 0, "/");

                        // 자동 로그인 처리
                        if ($remember_me && function_exists('createRememberToken')) {
                            $token = createRememberToken($db, mysqli_insert_id($db));
                            if ($token) { setRememberMeCookie($token); }
                        }

                        // member 테이블 업데이트
                        $update_member = "UPDATE member SET Logincount = ?, EndLogin = ? WHERE id = ?";
                        $update_stmt = mysqli_prepare($db, $update_member);
                        mysqli_stmt_bind_param($update_stmt, "iss", $login_count, $last_login, $member['id']);
                        mysqli_stmt_execute($update_stmt);
                        mysqli_stmt_close($update_stmt);

                        echo "<script>
                                alert('정상적으로 로그인 되셨습니다.\\n\\n(계정이 새 시스템으로 이전되었습니다)');
                                " . (!empty($redirect) ? "location.href = '$redirect';" : "location.href = '../';") . "
                              </script>";
                        exit;
                    }
                    mysqli_stmt_close($migrate_stmt);
                }
                mysqli_stmt_close($check_stmt);
            }
        }
        
        mysqli_stmt_close($member_stmt);
        mysqli_stmt_close($stmt);
        
        // 로그인 실패
        echo "<script>
                alert('아이디 또는 비밀번호가 올바르지 않습니다.');
                history.back();
              </script>";
        exit;
    }
}

// GET 요청이거나 잘못된 접근
echo "<script>
        alert('잘못된 접근입니다.');
        location.href = 'login.php';
      </script>";
?>