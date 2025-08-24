<?php
/**
 * 공통 인증 시스템
 * 모든 MlangPrintAuto 시스템에서 사용하는 통합 로그인 처리
 */

// 데이터베이스 연결이 필요한 경우
if (!isset($connect) && isset($db)) {
    $connect = $db;
}

/**
 * 사용자 로그인 처리
 * @param string $username 사용자명 또는 회원ID
 * @param string $password 비밀번호
 * @param mysqli $db_connection 데이터베이스 연결
 * @return array ['success' => bool, 'message' => string, 'user' => array]
 */
function processLogin($username, $password, $db_connection) {
    $result = [
        'success' => false,
        'message' => '로그인에 실패했습니다.',
        'user' => null
    ];
    
    if (empty($username) || empty($password)) {
        $result['message'] = '아이디와 비밀번호를 입력해주세요.';
        return $result;
    }
    
    try {
        $query = "SELECT * FROM users WHERE username = ? OR member_id = ?";
        $stmt = mysqli_prepare($db_connection, $query);
        
        if (!$stmt) {
            $result['message'] = '데이터베이스 연결 오류: ' . mysqli_error($db_connection);
            return $result;
        }
        
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $query_result = mysqli_stmt_get_result($stmt);
        
        if (!$query_result) {
            $result['message'] = '사용자 조회 중 오류가 발생했습니다.';
            mysqli_stmt_close($stmt);
            return $result;
        }
        
        $user = mysqli_fetch_assoc($query_result);
        mysqli_stmt_close($stmt);
        
        if (!$user) {
            $result['message'] = '존재하지 않는 사용자입니다.';
            return $result;
        }
        
        // 비밀번호 검증
        if (password_verify($password, $user['password'])) {
            // 세션 설정
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['id_login_ok'] = array('id' => $user['username'], 'pass' => $password);
            
            // 쿠키 설정 (보안상 사용자명만)
            setcookie("id_login_ok", $user['username'], 0, "/");
            
            $result['success'] = true;
            $result['message'] = '로그인에 성공했습니다.';
            $result['user'] = $user;
            
        } else {
            $result['message'] = '비밀번호가 올바르지 않습니다.';
        }
        
    } catch (Exception $e) {
        $result['message'] = '로그인 처리 중 오류가 발생했습니다: ' . $e->getMessage();
    }
    
    return $result;
}

/**
 * 로그인 상태 확인
 * @return bool 로그인 여부
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * 현재 사용자 정보 가져오기
 * @return array|null 사용자 정보 배열 또는 null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? '',
        'username' => $_SESSION['username'] ?? '',
        'name' => $_SESSION['user_name'] ?? ''
    ];
}

/**
 * 로그아웃 처리
 */
function logout() {
    // 세션 데이터 제거
    unset($_SESSION['user_id']);
    unset($_SESSION['username']); 
    unset($_SESSION['user_name']);
    unset($_SESSION['id_login_ok']);
    
    // 쿠키 제거
    setcookie("id_login_ok", "", time() - 3600, "/");
}

// POST 요청으로 로그인 처리
$login_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_action'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    $login_result = processLogin($username, $password, $connect);
    $login_message = $login_result['message'];
    
    if ($login_result['success']) {
        // 성공시 페이지 새로고침으로 POST 데이터 제거
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// 로그아웃 요청 처리
if (isset($_GET['logout'])) {
    logout();
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}
?>