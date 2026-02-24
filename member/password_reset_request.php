<?php
// 에러 표시 설정
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../db.php";

$message = '';
$error = '';

include_once __DIR__ . '/../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_verify_or_die();
    } catch (Exception $e) {
        $error = '세션이 만료되었습니다. 페이지를 새로고침 후 다시 시도해주세요.';
    }

    if (empty($error)) {
        $email = mysqli_real_escape_string($db, $_POST['email'] ?? '');
        $username = mysqli_real_escape_string($db, $_POST['username'] ?? '');
    
    if (empty($email) || empty($username)) {
        $error = '이메일과 아이디를 모두 입력해주세요.';
    } else {
        // users 테이블에서 확인
        $query = "SELECT id, username, email, name FROM users WHERE email = ? AND (username = ? OR member_id = ?)";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "sss", $email, $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // 토큰 생성 (PHP 7+ random_bytes or OpenSSL fallback)
            if (function_exists('random_bytes')) {
                $token = bin2hex(random_bytes(32));
            } else {
                $token = bin2hex(openssl_random_pseudo_bytes(32));
            }
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // 토큰 저장
            $insert_query = "INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($db, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "isss", $user['id'], $email, $token, $expires_at);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                // 실제 환경에서는 이메일로 발송
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $reset_link = "$protocol://$host/member/password_reset.php?token=" . $token;

                // 이메일 발송
                $to = $email;
                $subject = "=?UTF-8?B?".base64_encode("두손기획인쇄 비밀번호 재설정")."?=";
                $email_body = "안녕하세요, 두손기획인쇄입니다.\n\n";
                $email_body .= "요청하신 비밀번호 재설정 링크입니다:\n\n";
                $email_body .= $reset_link . "\n\n";
                $email_body .= "이 링크는 1시간 동안 유효합니다.\n";
                $email_body .= "만약 비밀번호 재설정을 요청하지 않으셨다면, 이 이메일을 무시하시면 됩니다.\n\n";
                $email_body .= "감사합니다.\n";
                $email_body .= "두손기획인쇄";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $headers .= "From: 두손기획인쇄 <noreply@dsp114.co.kr>\r\n";

                $mail_sent = mail($to, $subject, $email_body, $headers);

                if ($mail_sent) {
                    $message = "입력하신 이메일로 비밀번호 재설정 링크를 발송했습니다.<br><br>";
                    $message .= "<strong>이메일을 확인해주세요.</strong><br><br>";
                    $message .= "📧 " . htmlspecialchars(substr($email, 0, 3)) . "***@" . htmlspecialchars(substr(strstr($email, '@'), 1));
                } else {
                    $message = "이메일 발송 중 오류가 발생했습니다.<br>관리자에게 문의해주세요.";
                }
            }
            mysqli_stmt_close($insert_stmt);
        } else {
            // member 테이블에서도 확인 (호환성)
            $member_query = "SELECT id, name, email FROM member WHERE email = ? AND id = ?";
            $member_stmt = mysqli_prepare($db, $member_query);
            mysqli_stmt_bind_param($member_stmt, "ss", $email, $username);
            mysqli_stmt_execute($member_stmt);
            $member_result = mysqli_stmt_get_result($member_stmt);
            
            if ($member = mysqli_fetch_assoc($member_result)) {
                // 토큰 생성 (PHP 7+ random_bytes or OpenSSL fallback)
                if (function_exists('random_bytes')) {
                    $token = bin2hex(random_bytes(32));
                } else {
                    $token = bin2hex(openssl_random_pseudo_bytes(32));
                }
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $update_query = "UPDATE member SET reset_token = ?, reset_expires = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($db, $update_query);
                mysqli_stmt_bind_param($update_stmt, "sss", $token, $expires_at, $username);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    // 이메일 발송
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $reset_link = "$protocol://$host/member/password_reset.php?token=" . $token . "&legacy=1";

                    $to = $email;
                    $subject = "=?UTF-8?B?".base64_encode("두손기획인쇄 비밀번호 재설정")."?=";
                    $email_body = "안녕하세요, 두손기획인쇄입니다.\n\n";
                    $email_body .= "요청하신 비밀번호 재설정 링크입니다:\n\n";
                    $email_body .= $reset_link . "\n\n";
                    $email_body .= "이 링크는 1시간 동안 유효합니다.\n";
                    $email_body .= "만약 비밀번호 재설정을 요청하지 않으셨다면, 이 이메일을 무시하시면 됩니다.\n\n";
                    $email_body .= "감사합니다.\n";
                    $email_body .= "두손기획인쇄";

                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                    $headers .= "From: 두손기획인쇄 <noreply@dsp114.co.kr>\r\n";

                    $mail_sent = mail($to, $subject, $email_body, $headers);

                    if ($mail_sent) {
                        $message = "입력하신 이메일로 비밀번호 재설정 링크를 발송했습니다.<br><br>";
                        $message .= "<strong>이메일을 확인해주세요.</strong><br><br>";
                        $message .= "📧 " . htmlspecialchars(substr($email, 0, 3)) . "***@" . htmlspecialchars(substr(strstr($email, '@'), 1));
                    } else {
                        $message = "이메일 발송 중 오류가 발생했습니다.<br>관리자에게 문의해주세요.";
                    }
                }
                mysqli_stmt_close($update_stmt);
            } else {
                $error = '입력하신 정보와 일치하는 계정을 찾을 수 없습니다.';
            }
            mysqli_stmt_close($member_stmt);
        }
        mysqli_stmt_close($stmt);
    }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 재설정 - 두손기획인쇄</title>
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
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 비밀번호 재설정</h1>
            <p>두손기획인쇄 회원 비밀번호 찾기</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!$message): ?>
            <div class="info-box">
                회원가입 시 등록한 이메일과 아이디를 입력하시면<br>
                비밀번호 재설정 링크를 보내드립니다.
            </div>
            
            <form method="POST" action="">
                <?php include_once __DIR__ . '/../includes/csrf.php'; csrf_field(); ?>
                <div class="form-group">
                    <label for="username" class="form-label">아이디</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-input" 
                           placeholder="회원 아이디를 입력하세요"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">이메일</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="example@email.com"
                           required>
                </div>
                
                <button type="submit" class="btn">재설정 링크 받기</button>
            </form>
        <?php endif; ?>
        
        <div class="footer">
            <a href="login.php">← 로그인으로 돌아가기</a>
        </div>
    </div>
</body>
</html>