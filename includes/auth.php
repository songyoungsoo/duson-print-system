<?php
/**
 * 공통 인증 처리 파일
 * 경로: includes/auth.php
 *
 * 기능:
 * - 세션 유효 시간: 8시간 (28800초)
 * - 자동 로그인 (Remember Me): 30일
 * - 활동 시 세션 자동 갱신
 */

// ============================================
// 1. 세션 설정 (세션 시작 전에 설정해야 함)
// ============================================
$session_lifetime = 28800; // 8시간 = 28800초

if (session_status() == PHP_SESSION_NONE) {
    // 세션 유효 시간 설정
    ini_set('session.gc_maxlifetime', $session_lifetime);

    // 세션 쿠키 설정 (브라우저 닫아도 8시간 유지)
    session_set_cookie_params([
        'lifetime' => $session_lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => false,  // HTTPS 사용 시 true로 변경
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // 세션 저장 경로 설정 (권한 문제 해결)
    $session_path = dirname(__DIR__) . '/sessions';
    if (!is_dir($session_path)) {
        mkdir($session_path, 0777, true);
    }
    ini_set('session.save_path', $session_path);

    session_start();
}

// ============================================
// 2. 자동 로그인 설정
// ============================================
define('REMEMBER_ME_DAYS', 30);  // 자동 로그인 유지 기간 (일)
define('REMEMBER_ME_COOKIE', 'remember_token');

// 공통 인증 함수 로드 (존재할 때만)
if (file_exists(__DIR__ . '/auth_functions.php')) {
    include_once __DIR__ . '/auth_functions.php';
}

// 데이터베이스 연결 (각 페이지에서 이미 연결되어 있다고 가정)
if (isset($db) && $db) {
    $connect = $db;
} else {
    $connect = null;
}

// ============================================
// 3. 자동 로그인 토큰 테이블 확인/생성
// ============================================
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

// ============================================
// 4. 자동 로그인 토큰 생성
// ============================================
function createRememberToken($connect, $user_id) {
    if (!$connect) return null;

    ensureRememberTokenTable($connect);

    // 안전한 랜덤 토큰 생성
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + (REMEMBER_ME_DAYS * 24 * 60 * 60));

    // 기존 토큰 삭제 (한 사용자당 하나의 토큰만)
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

// ============================================
// 5. 자동 로그인 토큰 검증
// ============================================
function validateRememberToken($connect, $token) {
    if (!$connect || empty($token)) return null;

    ensureRememberTokenTable($connect);

    // 만료된 토큰 정리
    mysqli_query($connect, "DELETE FROM remember_tokens WHERE expires_at < NOW()");

    // 토큰 검증
    $stmt = mysqli_prepare($connect, "
        SELECT rt.user_id, u.id, u.username, u.name
        FROM remember_tokens rt
        JOIN users u ON rt.user_id = u.id
        WHERE rt.token = ? AND rt.expires_at > NOW()
    ");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $user;
    }

    return null;
}

// ============================================
// 6. 자동 로그인 토큰 삭제
// ============================================
function deleteRememberToken($connect, $user_id) {
    if (!$connect) return;

    $stmt = mysqli_prepare($connect, "DELETE FROM remember_tokens WHERE user_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// ============================================
// 7. Remember Me 쿠키 설정
// ============================================
function setRememberMeCookie($token) {
    $expires = time() + (REMEMBER_ME_DAYS * 24 * 60 * 60);
    setcookie(REMEMBER_ME_COOKIE, $token, [
        'expires' => $expires,
        'path' => '/',
        'domain' => '',
        'secure' => false,  // HTTPS 사용 시 true로 변경
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// ============================================
// 8. Remember Me 쿠키 삭제
// ============================================
function clearRememberMeCookie() {
    setcookie(REMEMBER_ME_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// ============================================
// 9. 세션 활동 시간 갱신 (자동 연장)
// ============================================
function refreshSessionActivity() {
    $_SESSION['last_activity'] = time();
}

// ============================================
// 10. 자동 로그인 시도 (세션 없을 때)
// ============================================
$login_message = '';
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? ($_SESSION['user_name'] ?? '') : '';

// 세션에 로그인 정보가 없지만 Remember Me 쿠키가 있는 경우
if (!$is_logged_in && isset($_COOKIE[REMEMBER_ME_COOKIE]) && $connect) {
    $user = validateRememberToken($connect, $_COOKIE[REMEMBER_ME_COOKIE]);

    if ($user) {
        // 자동 로그인 성공
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['auto_login'] = true;  // 자동 로그인 표시

        $is_logged_in = true;
        $user_name = $user['name'];

        // 토큰 갱신 (보안 강화)
        $new_token = createRememberToken($connect, $user['id']);
        if ($new_token) {
            setRememberMeCookie($new_token);
        }

        refreshSessionActivity();
        error_log("자동 로그인 성공: user_id=" . $user['id']);
    } else {
        // 유효하지 않은 토큰 - 쿠키 삭제
        clearRememberMeCookie();
    }
}

// 로그인 상태이면 활동 시간 갱신
if ($is_logged_in) {
    refreshSessionActivity();
}

// ============================================
// 11. POST 요청 처리
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login_action'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';

        if (empty($username) || empty($password)) {
            $login_message = '아이디와 비밀번호를 입력해주세요.';
        } else {
            // 데이터베이스 연결 확인
            if (!$connect) {
                $login_message = '데이터베이스 연결에 실패했습니다.';
            } else {
                // 테이블 존재 및 상태 확인
                $table_check = mysqli_query($connect, "SELECT 1 FROM users LIMIT 1");

                if (!$table_check) {
                    $error = mysqli_error($connect);

                    // 테이블스페이스 관련 오류인 경우 강제 정리
                    if (strpos($error, 'Tablespace') !== false || strpos($error, 'exists') !== false) {
                        mysqli_query($connect, "DROP TABLE IF EXISTS users");
                        mysqli_query($connect, "RESET QUERY CACHE");
                        mysqli_query($connect, "FLUSH TABLES");
                        usleep(100000);
                    }

                    // 새 테이블 생성
                    $create_table_query = "CREATE TABLE users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(50) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(100) DEFAULT NULL,
                        phone VARCHAR(20) DEFAULT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

                    if (!mysqli_query($connect, $create_table_query)) {
                        $alt_table = "user_auth_" . date('YmdHis');
                        $create_alt_query = "CREATE TABLE $alt_table (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            username VARCHAR(50) UNIQUE NOT NULL,
                            password VARCHAR(255) NOT NULL,
                            name VARCHAR(100) NOT NULL,
                            email VARCHAR(100) DEFAULT NULL,
                            phone VARCHAR(20) DEFAULT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

                        if (mysqli_query($connect, $create_alt_query)) {
                            mysqli_query($connect, "RENAME TABLE $alt_table TO users");
                        } else {
                            $login_message = '테이블 생성 중 오류: ' . mysqli_error($connect);
                        }
                    }
                }

                // 관리자 계정 확인/생성
                if (empty($login_message)) {
                    $admin_check = mysqli_query($connect, "SELECT id FROM users WHERE username = 'admin'");
                    if ($admin_check && mysqli_num_rows($admin_check) == 0) {
                        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
                        $admin_insert = mysqli_query($connect, "INSERT INTO users (username, password, name, email) VALUES ('admin', '$admin_password', '관리자', 'admin@duson.co.kr')");
                        if (!$admin_insert) {
                            $login_message = '관리자 계정 생성 중 오류: ' . mysqli_error($connect);
                        }
                    }
                }
            }

            // 로그인 확인
            if (empty($login_message)) {
                $query = "SELECT id, username, password, name FROM users WHERE username = ?";
                $stmt = mysqli_prepare($connect, $query);

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $username);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    if ($user = mysqli_fetch_assoc($result)) {
                        if (password_verify($password, $user['password'])) {
                            // 로그인 성공
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['auto_login'] = false;

                            $is_logged_in = true;
                            $user_name = $user['name'];

                            refreshSessionActivity();

                            // 자동 로그인 체크 시 토큰 생성
                            if ($remember_me) {
                                $token = createRememberToken($connect, $user['id']);
                                if ($token) {
                                    setRememberMeCookie($token);
                                    error_log("자동 로그인 토큰 생성: user_id=" . $user['id']);
                                }
                            }

                            // 리다이렉트
                            header("Location: " . $_SERVER['PHP_SELF']);
                            exit;
                        } else {
                            $login_message = '비밀번호가 올바르지 않습니다.';
                        }
                    } else {
                        $login_message = '존재하지 않는 사용자입니다.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect);
                }
            }
        }
    } elseif (isset($_POST['register_action'])) {
        $username = trim($_POST['reg_username']);
        $password = trim($_POST['reg_password']);
        $confirm_password = trim($_POST['reg_confirm_password']);
        $name = trim($_POST['reg_name']);
        $email = trim($_POST['reg_email']);
        $phone = trim($_POST['reg_phone']);

        if (empty($username) || empty($password) || empty($name)) {
            $login_message = '필수 항목을 모두 입력해주세요.';
        } elseif ($password !== $confirm_password) {
            $login_message = '비밀번호가 일치하지 않습니다.';
        } elseif (strlen($password) < 6) {
            $login_message = '비밀번호는 6자 이상이어야 합니다.';
        } elseif (!$connect) {
            $login_message = '데이터베이스 연결에 실패했습니다.';
        } else {
            // 중복 확인
            $check_query = "SELECT id FROM users WHERE username = ?";
            $stmt = mysqli_prepare($connect, $check_query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    $login_message = '이미 존재하는 아이디입니다.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_query = "INSERT INTO users (username, password, name, email, phone) VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = mysqli_prepare($connect, $insert_query);

                    if ($insert_stmt) {
                        mysqli_stmt_bind_param($insert_stmt, "sssss", $username, $hashed_password, $name, $email, $phone);

                        if (mysqli_stmt_execute($insert_stmt)) {
                            $login_message = '회원가입이 완료되었습니다. 로그인해주세요.';
                        } else {
                            $login_message = '회원가입 중 오류가 발생했습니다: ' . mysqli_stmt_error($insert_stmt);
                        }
                        mysqli_stmt_close($insert_stmt);
                    } else {
                        $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect);
                    }
                }
                mysqli_stmt_close($stmt);
            } else {
                $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect);
            }
        }
    } elseif (isset($_POST['logout_action'])) {
        // 자동 로그인 토큰 삭제
        if (isset($_SESSION['user_id']) && $connect) {
            deleteRememberToken($connect, $_SESSION['user_id']);
        }

        // Remember Me 쿠키 삭제
        clearRememberMeCookie();

        // 세션 변수 모두 제거
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

        // 새 세션 시작
        session_start();

        // 리다이렉트
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
