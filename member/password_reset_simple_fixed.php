<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/../db.php';

if (!isset($db) || !$db) {
    die("데이터베이스 연결 실패");
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';

    if ($mode === 'request') {
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');

        if (empty($email) || empty($username)) {
            $msg = '<p style="color:red">이메일과 아이디를 입력해주세요.</p>';
        } else {
            $stmt = mysqli_prepare($db, "SELECT id, username, email FROM users WHERE email = ? AND username = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "ss", $email, $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $check_table = mysqli_query($db, "SHOW TABLES LIKE 'password_resets'");
                if (mysqli_num_rows($check_table) > 0) {
                    $insert_stmt = mysqli_prepare($db, "INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($insert_stmt, "isss", $row['id'], $email, $token, $expires);
                    $insert_success = mysqli_stmt_execute($insert_stmt);
                    mysqli_stmt_close($insert_stmt);
                } else {
                    $insert_success = false;
                    error_log("password_resets table does not exist");
                }
                
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $link = "$protocol://$host/member/password_reset_simple_fixed.php?mode=reset&token=$token";
                
                $subject = "=?UTF-8?B?" . base64_encode("[두손기획인쇄] 비밀번호 재설정") . "?=";
                $body = "안녕하세요, 두손기획인쇄입니다.\n\n";
                $body .= "요청하신 비밀번호 재설정 링크입니다:\n\n";
                $body .= $link . "\n\n";
                $body .= "이 링크는 1시간 동안 유효합니다.\n";
                $body .= "비밀번호 재설정을 요청하지 않으셨다면 이 메일을 무시하세요.\n\n";
                $body .= "감사합니다.\n두손기획인쇄";
                
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $headers .= "From: =?UTF-8?B?" . base64_encode("두손기획인쇄") . "?= <noreply@" . SITE_DOMAIN . ">\r\n";
                
                if ($insert_success && @mail($email, $subject, $body, $headers)) {
                    $msg = '<p style="color:green; padding:15px; background:#d4edda; border-radius:5px">';
                    $msg .= '<strong>이메일로 재설정 링크를 발송했습니다.</strong><br><br>';
                    $msg .= '이메일을 확인해주세요.</p>';
                } else {
                    $msg = '<p style="padding:15px; background:#fff3cd; border-radius:5px">';
                    $msg .= '<strong>처리 중 문제가 발생했습니다.</strong><br><br>';
                    if ($insert_success) {
                        $msg .= '아래 링크로 직접 접속하세요:<br>';
                        $msg .= '<a href="' . htmlspecialchars($link) . '" target="_blank" style="color:#667eea; word-break:break-all;">' . htmlspecialchars($link) . '</a>';
                    } else {
                        $msg .= '데이터베이스 설정이 필요합니다. 관리자에게 문의하세요.';
                    }
                    $msg .= '</p>';
                }
            } else {
                $check_member = mysqli_prepare($db, "SELECT id, name, email FROM member WHERE email = ? AND id = ? LIMIT 1");
                mysqli_stmt_bind_param($check_member, "ss", $email, $username);
                mysqli_stmt_execute($check_member);
                $member_result = mysqli_stmt_get_result($check_member);
                
                if (mysqli_fetch_assoc($member_result)) {
                    $msg = '<p style="color:orange; padding:15px; background:#fff3cd; border-radius:5px">';
                    $msg .= '레거시 회원 계정입니다. 관리자에게 문의하세요.<br>';
                    $msg .= '☎ 02-2632-1830</p>';
                } else {
                    $msg = '<p style="color:red">일치하는 계정을 찾을 수 없습니다.</p>';
                }
                mysqli_stmt_close($check_member);
            }
            mysqli_stmt_close($stmt);
        }
    }
    elseif ($mode === 'reset') {
        $token = $_POST['token'] ?? '';
        $pass1 = $_POST['pass1'] ?? '';
        $pass2 = $_POST['pass2'] ?? '';
        
        if (empty($pass1) || empty($pass2)) {
            $msg = '<p style="color:red">비밀번호를 입력해주세요.</p>';
        } elseif ($pass1 !== $pass2) {
            $msg = '<p style="color:red">비밀번호가 일치하지 않습니다.</p>';
        } elseif (strlen($pass1) < 4) {
            $msg = '<p style="color:red">비밀번호는 4자 이상이어야 합니다.</p>';
        } else {
            $check_table = mysqli_query($db, "SHOW TABLES LIKE 'password_resets'");
            if (mysqli_num_rows($check_table) > 0) {
                $stmt = mysqli_prepare($db, "SELECT pr.user_id, pr.token, u.username 
                                            FROM password_resets pr 
                                            JOIN users u ON pr.user_id = u.id 
                                            WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0 
                                            LIMIT 1");
                mysqli_stmt_bind_param($stmt, "s", $token);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    $new_pass = password_hash($pass1, PASSWORD_DEFAULT);
                    
                    $update_stmt = mysqli_prepare($db, "UPDATE users SET password = ? WHERE id = ?");
                    mysqli_stmt_bind_param($update_stmt, "si", $new_pass, $row['user_id']);
                    
                    if (mysqli_stmt_execute($update_stmt)) {
                        $mark_stmt = mysqli_prepare($db, "UPDATE password_resets SET used = 1 WHERE token = ?");
                        mysqli_stmt_bind_param($mark_stmt, "s", $token);
                        mysqli_stmt_execute($mark_stmt);
                        mysqli_stmt_close($mark_stmt);
                        
                        $member_stmt = mysqli_prepare($db, "UPDATE member SET pass = ? WHERE id = ?");
                        mysqli_stmt_bind_param($member_stmt, "ss", $new_pass, $row['username']);
                        mysqli_stmt_execute($member_stmt);
                        mysqli_stmt_close($member_stmt);
                        
                        echo '<script>alert("비밀번호가 변경되었습니다."); location.href="login.php";</script>';
                        exit;
                    } else {
                        $msg = '<p style="color:red">비밀번호 변경 실패</p>';
                    }
                    mysqli_stmt_close($update_stmt);
                } else {
                    $msg = '<p style="color:red">유효하지 않은 링크입니다.</p>';
                }
                mysqli_stmt_close($stmt);
            } else {
                $msg = '<p style="color:red">시스템 설정이 필요합니다. 관리자에게 문의하세요.</p>';
            }
        }
    }
}

$mode = $_GET['mode'] ?? '';
$token = $_GET['token'] ?? '';

$content = '';
$show_form = true;

if ($mode === 'reset' && !empty($token)) {
    $check_table = mysqli_query($db, "SHOW TABLES LIKE 'password_resets'");
    if (mysqli_num_rows($check_table) > 0) {
        $stmt = mysqli_prepare($db, "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0 LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $content = '
            <div class="header">
                <h1>🔐 새 비밀번호 설정</h1>
                <p>안전한 비밀번호를 입력해주세요</p>
            </div>
            ' . $msg . '
            <form method="post">
                <input type="hidden" name="mode" value="reset">
                <input type="hidden" name="token" value="' . htmlspecialchars($token) . '">
                <div class="form-group">
                    <label>새 비밀번호</label>
                    <input type="password" name="pass1" class="inp" required minlength="4">
                </div>
                <div class="form-group">
                    <label>비밀번호 확인</label>
                    <input type="password" name="pass2" class="inp" required minlength="4">
                </div>
                <button type="submit" class="btn">비밀번호 변경</button>
            </form>
            <p style="text-align:center; margin-top:20px;">
                <a href="login.php">← 로그인으로 돌아가기</a>
            </p>
            ';
        } else {
            $content = '
            <div style="text-align:center; padding:40px;">
                <p style="color:red; font-size:18px;">링크가 만료되었거나 유효하지 않습니다.</p>
                <p><a href="password_reset_simple_fixed.php">다시 시도하기</a></p>
            </div>
            ';
            $show_form = false;
        }
        mysqli_stmt_close($stmt);
    } else {
        $content = '
        <div style="text-align:center; padding:40px;">
            <p style="color:red; font-size:18px;">시스템 설정이 필요합니다.</p>
            <p>관리자에게 문의하세요: 02-2632-1830</p>
        </div>
        ';
        $show_form = false;
    }
} else {
    $content = '
    <div class="header">
        <h1>🔐 비밀번호 찾기</h1>
        <p>두손기획인쇄 회원 비밀번호 재설정</p>
    </div>
    ' . $msg . '
    <form method="post">
        <input type="hidden" name="mode" value="request">
        <div class="form-group">
            <label>아이디</label>
            <input type="text" name="username" class="inp" placeholder="회원 아이디" required>
        </div>
        <div class="form-group">
            <label>이메일</label>
            <input type="email" name="email" class="inp" placeholder="example@email.com" required>
        </div>
        <button type="submit" class="btn">재설정 링크 받기</button>
    </form>
    <p style="text-align:center; margin-top:20px;">
        <a href="login.php">← 로그인으로 돌아가기</a>
    </p>
    ';
}

if (isset($db) && $db) {
    mysqli_close($db);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 찾기 - 두손기획인쇄</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }
        .box {
            background:white;
            border-radius:12px;
            box-shadow:0 10px 40px rgba(0,0,0,0.15);
            width:100%;
            max-width:420px;
            padding:40px;
        }
        .header { text-align:center; margin-bottom:30px; }
        .header h1 { font-size:26px; color:#333; margin-bottom:8px; }
        .header p { color:#888; font-size:14px; }
        .form-group { margin-bottom:18px; }
        .form-group label {
            display:block;
            margin-bottom:8px;
            color:#444;
            font-weight:500;
            font-size:14px;
        }
        .inp {
            width:100%;
            padding:12px 14px;
            border:1px solid #ddd;
            border-radius:6px;
            font-size:14px;
            transition:border-color 0.2s;
        }
        .inp:focus {
            outline:none;
            border-color:#667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            width:100%;
            padding:14px;
            background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color:white;
            border:none;
            border-radius:6px;
            font-size:16px;
            font-weight:600;
            cursor:pointer;
            transition:opacity 0.2s, transform 0.2s;
        }
        .btn:hover { 
            opacity:0.9; 
            transform: translateY(-1px);
        }
        .btn:active { 
            transform: translateY(0);
        }
        a { color:#667eea; text-decoration:none; }
        a:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div class="box">
        <?php echo $content; ?>
    </div>
</body>
</html>