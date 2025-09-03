<?php
// 로그인 처리
$login_message = '';
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login_action'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        if (empty($username) || empty($password)) {
            $login_message = '아이디와 비밀번호를 입력해주세요.';
        } else {
            if (!$db) {
                $login_message = '데이터베이스 연결에 실패했습니다.';
            } else {
                $query = "SELECT id, username, password, name FROM users WHERE username = ?";
                $stmt = mysqli_prepare($db, $query);  
              
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
                            $login_message = '로그인 성공!';
                        } else {
                            $login_message = '비밀번호가 올바르지 않습니다.';
                        }
                    } else {
                        $login_message = '존재하지 않는 사용자입니다.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db);
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
        } elseif (!$db) {
            $login_message = '데이터베이스 연결에 실패했습니다.';
        } else {
            $check_query = "SELECT id FROM users WHERE username = ?";
            $stmt = mysqli_prepare($db, $check_query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $login_message = '이미 존재하는 아이디입니다.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_query = "INSERT INTO users (username, password, name, email, phone) VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = mysqli_prepare($db, $insert_query);
                    
                    if ($insert_stmt) {
                        mysqli_stmt_bind_param($insert_stmt, "sssss", $username, $hashed_password, $name, $email, $phone);
                        
                        if (mysqli_stmt_execute($insert_stmt)) {
                            $login_message = '회원가입이 완료되었습니다. 로그인해주세요.';
                        } else {
                            $login_message = '회원가입 중 오류가 발생했습니다: ' . mysqli_stmt_error($insert_stmt);
                        }
                        mysqli_stmt_close($insert_stmt);
                    } else {
                        $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db);
                    }
                }
                mysqli_stmt_close($stmt);
            } else {
                $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db);
            }
        }
    } elseif (isset($_POST['logout_action'])) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!-- 상단 헤더 -->
<div class="top-header">
    <div class="header-content">
        <div class="logo-section">
            <div class="logo-icon"><?php echo $page_icon ?? '🖨️'; ?></div>
            <div class="company-info">
                <h1>두손기획인쇄</h1>
                <p><?php echo $page_subtitle ?? '프리미엄 인쇄 서비스'; ?></p>
            </div>
        </div>
        <div class="contact-info">
            <div class="contact-card">
                <div class="label">📞 고객센터</div>
                <div class="value">1688-2384</div>
            </div>
            <div class="contact-card cart-card">
                <a href="/MlangPrintAuto/shop/cart.php" class="cart-btn">🛒 장바구니</a>
            </div>
            <?php if ($is_logged_in): ?>
            <div class="contact-card user-info">
                <div class="label">👤 환영합니다</div>
                <div class="value"><?php echo htmlspecialchars($user_name); ?>님</div>
                <form method="post" style="margin-top: 10px;">
                    <button type="submit" name="logout_action" class="logout-btn">로그아웃</button>
                </form>
            </div>
            <?php else: ?>
            <div class="contact-card login-card">
                <button onclick="showLoginModal()" class="login-btn">🔐 로그인</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>