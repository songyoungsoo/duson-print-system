<?php
session_start();
include "../db.php";

$message = '';
$error = '';

include_once __DIR__ . '/../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $email = mysqli_real_escape_string($db, $_POST['email'] ?? '');
    $username = mysqli_real_escape_string($db, $_POST['username'] ?? '');
    
    if (empty($email) || empty($username)) {
        $error = '이메일과 아이디를 모두 입력해주세요.';
    } else {
        // users 테이블에서 확인
        $query = "SELECT id, username, email, name FROM users WHERE email = ? AND username = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ss", $email, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // 기존 미사용 토큰 무효화
            $invalidate = "UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0";
            $inv_stmt = mysqli_prepare($db, $invalidate);
            mysqli_stmt_bind_param($inv_stmt, "i", $user['id']);
            mysqli_stmt_execute($inv_stmt);
            mysqli_stmt_close($inv_stmt);
            
            // 토큰 생성
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // 토큰 저장
            $insert_query = "INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($db, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "isss", $user['id'], $email, $token, $expires_at);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                // 환경별 URL 자동 감지
                $is_localhost = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false);
                $protocol = $is_localhost ? 'http://' : 'https://';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $reset_link = $protocol . $host . "/member/password_reset.php?token=" . $token;
                
                // 이메일 발송 시도
                $email_sent = false;
                $mailer_path = __DIR__ . '/../mlangorder_printauto/mailer.lib.php';
                if (file_exists($mailer_path)) {
                    include_once $mailer_path;
                    
                    $user_name = htmlspecialchars($user['name'] ?: $user['username']);
                    $mail_subject = "[두손기획인쇄] 비밀번호 재설정 안내";
                    $mail_body = "
                    <div style='max-width:600px;margin:0 auto;font-family:\"Noto Sans KR\",sans-serif;'>
                        <div style='background:#1E4E79;padding:20px;text-align:center;border-radius:8px 8px 0 0;'>
                            <h2 style='color:#fff;margin:0;font-size:18px;'>두손기획인쇄 비밀번호 재설정</h2>
                        </div>
                        <div style='padding:30px;border:1px solid #ddd;border-top:none;border-radius:0 0 8px 8px;'>
                            <p style='font-size:15px;color:#333;'><strong>{$user_name}</strong>님, 안녕하세요.</p>
                            <p style='font-size:14px;color:#555;line-height:1.8;'>
                                비밀번호 재설정을 요청하셨습니다.<br>
                                아래 버튼을 클릭하여 새 비밀번호를 설정해주세요.
                            </p>
                            <div style='text-align:center;margin:30px 0;'>
                                <a href='{$reset_link}' style='display:inline-block;padding:14px 40px;background:#1E4E79;color:#fff;text-decoration:none;border-radius:5px;font-size:16px;font-weight:500;'>비밀번호 재설정하기</a>
                            </div>
                            <p style='font-size:13px;color:#888;line-height:1.6;'>
                                * 이 링크는 <strong>1시간</strong> 동안만 유효합니다.<br>
                                * 본인이 요청하지 않은 경우 이 메일을 무시하셔도 됩니다.
                            </p>
                            <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
                            <p style='font-size:12px;color:#aaa;text-align:center;'>
                                두손기획인쇄 | Tel. 02-2632-1830
                            </p>
                        </div>
                    </div>";
                    
                    ob_start();
                    $mail_result = mailer(
                        '두손기획인쇄',
                        'dsp1830@naver.com',
                        $email,
                        $mail_subject,
                        $mail_body,
                        1,
                        ""
                    );
                    ob_end_clean();
                    
                    $email_sent = ($mail_result === true || $mail_result == 1);
                }
                
                if ($email_sent) {
                    $message = "비밀번호 재설정 링크를 <strong>" . htmlspecialchars($email) . "</strong> 으로 발송했습니다.<br><br>";
                    $message .= "이메일을 확인하시고, 링크를 클릭하여 비밀번호를 재설정하세요.<br>";
                    $message .= "<span style='color:#888;font-size:13px;'>* 이메일이 오지 않으면 스팸 메일함을 확인해주세요.</span>";
                } else {
                    // 이메일 발송 실패 시 링크를 직접 표시
                    $message = "비밀번호 재설정 링크가 생성되었습니다.<br><br>";
                    $message .= "다음 링크를 클릭하여 비밀번호를 재설정하세요:<br>";
                    $message .= "<a href='{$reset_link}' style='color:#667eea;text-decoration:underline;word-break:break-all;'>{$reset_link}</a><br><br>";
                    $message .= "<span style='color:#888;font-size:13px;'>* 이 링크는 1시간 동안 유효합니다.</span>";
                }
            }
            mysqli_stmt_close($insert_stmt);
        } else {
            $error = '입력하신 정보와 일치하는 계정을 찾을 수 없습니다.';
        }
        mysqli_stmt_close($stmt);
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
            line-height: 1.8;
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
            <h1>비밀번호 재설정</h1>
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
