<?php
/**
 * 통합 로그인 처리 시스템
 * users 테이블 기반 (기존 member 테이블과 호환)
 *
 * 기능:
 * - 세션 유효 시간: 8시간 (28800초)
 * - 자동 로그인 (Remember Me): 30일
 */

// 세션 설정 및 시작
$session_lifetime = 28800; // 8시간 = 28800초
ini_set('session.gc_maxlifetime', $session_lifetime);
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// 자동 로그인 상수 및 함수
define('REMEMBER_ME_DAYS', 30);
define('REMEMBER_ME_COOKIE', 'remember_token');

/**
 * 자동 로그인 토큰 테이블 확인/생성
 */
function ensureRememberTokenTable($connect) {
    if (!$connect) return false;
    $check = mysqli_query($connect, "SHOW TABLES LIKE 'remember_tokens'");
    if (mysqli_num_rows($check) == 0) {
        $create_sql = "CREATE TABLE remember_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_user_id (user_id),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        mysqli_query($connect, $create_sql);
    }
    return true;
}

/**
 * 자동 로그인 토큰 생성
 */
function createRememberToken($connect, $user_id) {
    if (!$connect) return null;
    ensureRememberTokenTable($connect);

    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + (REMEMBER_ME_DAYS * 24 * 60 * 60));

    // 기존 토큰 삭제
    $delete_stmt = mysqli_prepare($connect, "DELETE FROM remember_tokens WHERE user_id = ?");
    if ($delete_stmt) {
        mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
        mysqli_stmt_execute($delete_stmt);
        mysqli_stmt_close($delete_stmt);
    }

    // 새 토큰 저장
    $insert_stmt = mysqli_prepare($connect, "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    if ($insert_stmt) {
        mysqli_stmt_bind_param($insert_stmt, "iss", $user_id, $token, $expires_at);
        if (mysqli_stmt_execute($insert_stmt)) {
            mysqli_stmt_close($insert_stmt);
            return $token;
        }
        mysqli_stmt_close($insert_stmt);
    }
    return null;
}

/**
 * Remember Me 쿠키 설정
 */
function setRememberMeCookie($token) {
    $expires = time() + (REMEMBER_ME_DAYS * 24 * 60 * 60);
    setcookie(REMEMBER_ME_COOKIE, $token, [
        'expires' => $expires,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mode = $_POST['mode'] ?? '';

    if ($mode == "member_login") {
        include "../db.php";

        $id = mysqli_real_escape_string($db, $_POST['id'] ?? '');
        $pass = $_POST['pass'] ?? '';
        $remember_me = isset($_POST['remember_me']) && ($_POST['remember_me'] == '1' || $_POST['remember_me'] == 'on');
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '/';
        
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
            
            // 해시된 비밀번호 확인
            if (password_verify($pass, $user['password'])) {
                $login_success = true;
            }
            // 기존 평문 비밀번호 확인 (호환성)
            elseif (!empty($user['old_password']) && $pass === $user['old_password']) {
                $login_success = true;
                
                // 비밀번호를 해시로 업데이트
                $new_hash = password_hash($pass, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($db, $update_query);
                mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $user['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
            
            if ($login_success) {
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

                // 자동 로그인 체크 시 토큰 생성
                if ($remember_me) {
                    $token = createRememberToken($db, $user['id']);
                    if ($token) {
                        setRememberMeCookie($token);
                        error_log("자동 로그인 토큰 생성: user_id=" . $user['id']);
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
                // member 테이블 데이터를 users로 마이그레이션
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
                    // 세션 설정
                    $_SESSION['user_id'] = mysqli_insert_id($db);
                    $_SESSION['username'] = $member['id'];
                    $_SESSION['user_name'] = $member['name'];
                    $_SESSION['id_login_ok'] = array('id' => $member['id'], 'pass' => $pass);
                    
                    setcookie("id_login_ok", $member['id'], 0, "/");

                    // 자동 로그인 체크 시 토큰 생성
                    if ($remember_me) {
                        $new_user_id = mysqli_insert_id($db);
                        $token = createRememberToken($db, $new_user_id);
                        if ($token) {
                            setRememberMeCookie($token);
                            error_log("자동 로그인 토큰 생성 (마이그레이션): user_id=" . $new_user_id);
                        }
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