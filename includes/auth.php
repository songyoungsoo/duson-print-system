<?php
/**
 * 공통 인증 처리 파일
 * 경로: includes/auth.php
 */

// 세션이 시작되지 않았다면 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 공통 인증 함수 로드 (존재할 때만)
if (file_exists(__DIR__ . '/auth_functions.php')) {
    include_once __DIR__ . '/auth_functions.php';
}

// 데이터베이스 연결 (각 페이지에서 이미 연결되어 있다고 가정)
// $db 변수가 있다면 $connect로 할당 (새로운 연결 생성하지 않음)
if (isset($db) && $db) {
    $connect = $db;
} else {
    // 기본값 설정 (연결 시도하지 않음)
    $connect = null;
}


$login_message = '';
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login_action'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
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
                        // 강제로 테이블 정리
                        mysqli_query($connect, "DROP TABLE IF EXISTS users");
                        
                        // 캐시 정리
                        mysqli_query($connect, "RESET QUERY CACHE");
                        mysqli_query($connect, "FLUSH TABLES");
                        
                        // 잠시 대기 후 테이블 재생성
                        usleep(100000); // 0.1초 대기
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
                        // 여전히 실패하면 다른 테이블명 사용
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
                            // 대체 테이블 생성 성공, users로 리네임
                            mysqli_query($connect, "RENAME TABLE $alt_table TO users");
                        } else {
                            $login_message = '테이블 생성 중 오류: ' . mysqli_error($connect);
                        }
                    }
                }
                
                // 테이블이 정상적으로 존재하는 경우에만 관리자 계정 처리
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
            
            // 로그인 확인 (테이블 구조가 올바른 경우에만)
            if (empty($login_message)) {
                $query = "SELECT id, username, password, name FROM users WHERE username = ?";
                $stmt = mysqli_prepare($connect, $query);
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $username);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($user = mysqli_fetch_assoc($result)) {
                        if (password_verify($password, $user['password'])) {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['user_name'] = $user['name'];
                            $is_logged_in = true;
                            $user_name = $user['name'];
                            
                            // 로그인 성공 시 POST 상태 정리를 위해 리다이렉트
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
                    // 회원가입
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
        
        // 새 세션 시작 (깨끗한 상태로)
        session_start();
        
        // 리다이렉트
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>