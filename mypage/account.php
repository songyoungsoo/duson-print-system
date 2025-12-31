<?php
require_once __DIR__ . '/auth_required.php';

$user_id = $current_user['id'];
$message = '';
$error = '';

// 비밀번호 변경 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_pw = $_POST['current_password'] ?? '';
    $new_pw = $_POST['new_password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    
    if (empty($current_pw) || empty($new_pw) || empty($confirm_pw)) {
        $error = '모든 항목을 입력해주세요.';
    } elseif ($new_pw !== $confirm_pw) {
        $error = '새 비밀번호가 일치하지 않습니다.';
    } elseif (strlen($new_pw) < 6) {
        $error = '비밀번호는 6자 이상이어야 합니다.';
    } else {
        // 현재 비밀번호 확인
        $check_query = "SELECT password FROM users WHERE id = ?";
        $stmt = mysqli_prepare($db, $check_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        // 비밀번호 검증 (bcrypt 해시 또는 평문 모두 지원)
        $stored_password = $result['password'] ?? '';
        $password_valid = false;

        if ($result) {
            // bcrypt 해시인 경우 ($2y$로 시작하고 60자)
            if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
                $password_valid = password_verify($current_pw, $stored_password);
            } else {
                // 평문 비밀번호인 경우 직접 비교
                $password_valid = ($current_pw === $stored_password);
            }
        }

        if ($password_valid) {
            // 새 비밀번호로 변경
            $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($db, $update_query);
            mysqli_stmt_bind_param($stmt, "si", $new_hash, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = '비밀번호가 성공적으로 변경되었습니다.';
            } else {
                $error = '비밀번호 변경 중 오류가 발생했습니다.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = '현재 비밀번호가 올바르지 않습니다.';
        }
    }
}

// 사용자 정보 조회
$user_query = "SELECT username, name, email, phone, created_at FROM users WHERE id = ?";
$stmt = mysqli_prepare($db, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>계정 정보 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/mlangprintauto/css/common-styles.css">
    <style>
        body { background: #f5f5f5; padding: 20px; font-family: 'Malgun Gothic', sans-serif; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .nav-link { margin: 20px 0; }
        .nav-link a { color: #667eea; text-decoration: none; }
        .content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .content h2 { color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        .info-row { display: flex; padding: 15px; border-bottom: 1px solid #f0f0f0; }
        .info-row label { width: 150px; font-weight: 600; color: #666; }
        .info-row span { color: #333; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #666; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: 600; }
        .form-group button:hover { background: #5568d3; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .logout-button { background: #dc3545; }
        .logout-button:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ 계정 정보</h1>
        </div>
        <div class="nav-link">
            <a href="index.php">← 마이페이지로 돌아가기</a>
        </div>
        
        <div class="content">
            <h2>기본 정보</h2>
            <div class="info-row">
                <label>아이디</label>
                <span><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
            <div class="info-row">
                <label>이름</label>
                <span><?php echo htmlspecialchars($user['name']); ?></span>
            </div>
            <div class="info-row">
                <label>이메일</label>
                <span><?php echo htmlspecialchars($user['email'] ?? '-'); ?></span>
            </div>
            <div class="info-row">
                <label>전화번호</label>
                <span><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></span>
            </div>
            <div class="info-row">
                <label>가입일</label>
                <span><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></span>
            </div>
        </div>
        
        <div class="content">
            <h2>비밀번호 변경</h2>
            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>현재 비밀번호</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>새 비밀번호 (6자 이상)</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>새 비밀번호 확인</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="change_password">비밀번호 변경</button>
                </div>
            </form>
        </div>
        
        <div class="content">
            <h2>로그아웃</h2>
            <form method="POST" action="/includes/auth.php">
                <div class="form-group">
                    <button type="submit" name="logout_action" class="logout-button">로그아웃</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
