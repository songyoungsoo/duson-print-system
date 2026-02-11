<?php
session_start();
include "../db.php";

$token = $_GET['token'] ?? '';
$legacy = $_GET['legacy'] ?? 0;
$message = '';
$error = '';
$valid_token = false;
$user_info = null;

if (empty($token)) {
    $error = '유효하지 않은 토큰입니다.';
} else {
    if (!$legacy) {
        // users 테이블 토큰 확인
        $query = "SELECT pr.*, u.username, u.name, u.email 
                  FROM password_resets pr 
                  JOIN users u ON pr.user_id = u.id 
                  WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($reset = mysqli_fetch_assoc($result)) {
            $valid_token = true;
            $user_info = $reset;
        }
        mysqli_stmt_close($stmt);
    } else {
        // member 테이블 토큰 확인
        $query = "SELECT * FROM member WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($member = mysqli_fetch_assoc($result)) {
            $valid_token = true;
            $user_info = $member;
            $user_info['username'] = $member['id'];
            $user_info['is_legacy'] = true;
        }
        mysqli_stmt_close($stmt);
    }
    
    if (!$valid_token) {
        $error = '토큰이 만료되었거나 유효하지 않습니다.';
    }
}

// 비밀번호 재설정 처리
include_once __DIR__ . '/../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    csrf_verify_or_die();
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = '비밀번호를 입력해주세요.';
    } elseif ($new_password !== $confirm_password) {
        $error = '비밀번호가 일치하지 않습니다.';
    } elseif (strlen($new_password) < 6) {
        $error = '비밀번호는 최소 6자 이상이어야 합니다.';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        if (isset($user_info['is_legacy'])) {
            // member 테이블 업데이트
            $update_query = "UPDATE member SET pass = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
            $update_stmt = mysqli_prepare($db, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $user_info['id']);
            $success = mysqli_stmt_execute($update_stmt);
            
            // users 테이블로 마이그레이션 시도
            if ($success) {
                $migrate_query = "INSERT INTO users (username, password, name, email, phone, member_id, login_count, last_login) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                 ON DUPLICATE KEY UPDATE password = ?";
                $migrate_stmt = mysqli_prepare($db, $migrate_query);
                $phone = ($user_info['hendphone1'] ?? '') . '-' . ($user_info['hendphone2'] ?? '') . '-' . ($user_info['hendphone3'] ?? '');
                $login_count = $user_info['Logincount'] ?? 0;
                $last_login = $user_info['EndLogin'] ?? null;
                
                mysqli_stmt_bind_param($migrate_stmt, "ssssssiis", 
                    $user_info['id'], $hashed_password, $user_info['name'], 
                    $user_info['email'], $phone, $user_info['id'], 
                    $login_count, $last_login, $hashed_password
                );
                mysqli_stmt_execute($migrate_stmt);
                mysqli_stmt_close($migrate_stmt);
            }
            mysqli_stmt_close($update_stmt);
        } else {
            // users 테이블 업데이트
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($db, $update_query);
            mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user_info['user_id']);
            $success = mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
            
            // 토큰 사용 처리
            if ($success) {
                $mark_used = "UPDATE password_resets SET used = 1 WHERE token = ?";
                $mark_stmt = mysqli_prepare($db, $mark_used);
                mysqli_stmt_bind_param($mark_stmt, "s", $token);
                mysqli_stmt_execute($mark_stmt);
                mysqli_stmt_close($mark_stmt);
            }
        }
        
        if ($success) {
            $message = '비밀번호가 성공적으로 변경되었습니다.';
            $valid_token = false; // 폼 숨기기
        } else {
            $error = '비밀번호 변경 중 오류가 발생했습니다.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>새 비밀번호 설정 - 두손기획인쇄</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .user-info strong {
            color: #667eea;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        
        .weak { color: #dc3545; }
        .medium { color: #ffc107; }
        .strong { color: #28a745; }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            text-align: center;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin: 5px;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .requirements {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }
        
        .requirements ul {
            margin: 5px 0 0 20px;
        }
    </style>
    <script>
        function checkPasswordStrength(password) {
            const strengthIndicator = document.getElementById('strength-indicator');
            if (password.length < 6) {
                strengthIndicator.textContent = '약함';
                strengthIndicator.className = 'password-strength weak';
            } else if (password.length < 10) {
                strengthIndicator.textContent = '보통';
                strengthIndicator.className = 'password-strength medium';
            } else {
                strengthIndicator.textContent = '강함';
                strengthIndicator.className = 'password-strength strong';
            }
        }
        
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 6) {
                alert('비밀번호는 최소 6자 이상이어야 합니다.');
                return false;
            }
            
            if (password !== confirmPassword) {
                alert('비밀번호가 일치하지 않습니다.');
                return false;
            }
            
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 새 비밀번호 설정</h1>
            <p>안전한 비밀번호를 설정해주세요</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message success">
                <?php echo $message; ?>
            </div>
            <div class="footer">
                <a href="login.php">로그인하기 →</a>
            </div>
        <?php elseif ($error && !$valid_token): ?>
            <div class="message error">
                <?php echo $error; ?>
            </div>
            <div class="footer">
                <a href="password_reset_request.php">← 비밀번호 재설정 다시 요청</a>
                <a href="login.php">로그인으로 돌아가기</a>
            </div>
        <?php elseif ($valid_token): ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="user-info">
                <strong><?php echo htmlspecialchars($user_info['username']); ?></strong>님의 비밀번호를 재설정합니다
            </div>
            
            <form method="POST" action="" onsubmit="return validateForm()">
                <?php include_once __DIR__ . '/../includes/csrf.php'; csrf_field(); ?>
                <div class="form-group">
                    <label for="password" class="form-label">새 비밀번호</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="새 비밀번호를 입력하세요"
                           onkeyup="checkPasswordStrength(this.value)"
                           required>
                    <div id="strength-indicator" class="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">비밀번호 확인</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="form-input" 
                           placeholder="비밀번호를 다시 입력하세요"
                           required>
                </div>
                
                <div class="requirements">
                    <strong>비밀번호 요구사항:</strong>
                    <ul>
                        <li>최소 6자 이상</li>
                        <li>영문, 숫자, 특수문자 조합 권장</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn" style="margin-top: 20px;">비밀번호 변경</button>
            </form>
            
            <div class="footer">
                <a href="login.php">← 로그인으로 돌아가기</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>