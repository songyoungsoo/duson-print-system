<?php
/**
 * 비밀번호 재설정 - 최대한 호환성 높은 버전
 */

// 에러 끄기
error_reporting(0);
ini_set('display_errors', 0);

// 세션
if (session_id() == '') session_start();

// DB 연결 (직접)
$db_host = 'localhost';
$db_user = 'dsp1830';
$db_pass = 't3zn?5R56';
$db_name = 'dsp1830';

$connect = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$connect) {
    die("데이터베이스 연결 실패");
}

@mysqli_query($connect, "SET NAMES utf8");

// POST 처리
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

    if ($mode == 'request') {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';

        if (empty($email) || empty($username)) {
            $msg = '<p style="color:red">이메일과 아이디를 입력해주세요.</p>';
        } else {
            $email = mysqli_real_escape_string($connect, $email);
            $username = mysqli_real_escape_string($connect, $username);

            // users 테이블 확인
            $q = "SELECT * FROM users WHERE email='$email' AND username='$username' LIMIT 1";
            $r = mysqli_query($connect, $q);

            if ($row = mysqli_fetch_assoc($r)) {
                // 간단한 토큰 생성 (시간 + 랜덤)
                $token = md5(time() . rand(1000, 9999) . $username);
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // password_resets 테이블에 저장
                $iq = "INSERT INTO password_resets (user_id, email, token, expires_at) VALUES ('{$row['id']}', '$email', '$token', '$expires')";
                @mysqli_query($connect, $iq);

                // 이메일 발송
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
                $link = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/member/password_reset_simple.php?mode=reset&token=' . $token;

                $subject = "[두손기획인쇄] 비밀번호 재설정";
                $body = "비밀번호 재설정 링크:\n\n" . $link . "\n\n(1시간 유효)";

                $headers = "From: noreply@dsp114.co.kr\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                if (@mail($email, $subject, $body, $headers)) {
                    $msg = '<p style="color:green; padding:15px; background:#d4edda; border-radius:5px">';
                    $msg .= '<strong>이메일로 재설정 링크를 발송했습니다.</strong><br><br>';
                    $msg .= '이메일을 확인해주세요.</p>';
                } else {
                    $msg = '<p style="padding:15px; background:#fff3cd; border-radius:5px">';
                    $msg .= '<strong>이메일 발송 실패</strong><br><br>';
                    $msg .= '아래 링크로 직접 접속하세요:<br>';
                    $msg .= '<a href="' . $link . '" target="_blank" style="color:#667eea">' . $link . '</a></p>';
                }
            } else {
                $msg = '<p style="color:red">일치하는 계정을 찾을 수 없습니다.</p>';
            }
        }
    }
    elseif ($mode == 'reset') {
        $token = isset($_POST['token']) ? trim($_POST['token']) : '';
        $pass1 = isset($_POST['pass1']) ? trim($_POST['pass1']) : '';
        $pass2 = isset($_POST['pass2']) ? trim($_POST['pass2']) : '';

        if (empty($pass1) || empty($pass2)) {
            $msg = '<p style="color:red">비밀번호를 입력해주세요.</p>';
        } elseif ($pass1 != $pass2) {
            $msg = '<p style="color:red">비밀번호가 일치하지 않습니다.</p>';
        } elseif (strlen($pass1) < 4) {
            $msg = '<p style="color:red">비밀번호는 4자 이상이어야 합니다.</p>';
        } else {
            // 토큰 확인
            $token = mysqli_real_escape_string($connect, $token);
            $q = "SELECT pr.*, u.username FROM password_resets pr
                  JOIN users u ON pr.user_id = u.id
                  WHERE pr.token='$token' AND pr.expires_at > NOW() AND pr.used=0 LIMIT 1";
            $r = mysqli_query($connect, $q);

            if ($row = mysqli_fetch_assoc($r)) {
                // 비밀번호 해시 (PHP 5.3+)
                if (function_exists('password_hash')) {
                    $new_pass = password_hash($pass1, PASSWORD_DEFAULT);
                } else {
                    // 레거시: 평문 (기존 방식)
                    $new_pass = $pass1;
                }

                // users 테이블 업데이트
                $uq = "UPDATE users SET password='$new_pass' WHERE id='{$row['user_id']}'";
                if (mysqli_query($connect, $uq)) {
                    // 토큰 사용 표시
                    @mysqli_query($connect, "UPDATE password_resets SET used=1 WHERE token='$token'");

                    // member 테이블도 업데이트
                    @mysqli_query($connect, "UPDATE member SET pass='$new_pass' WHERE id='{$row['username']}'");

                    echo '<script>alert("비밀번호가 변경되었습니다."); location.href="login.php";</script>';
                    exit;
                } else {
                    $msg = '<p style="color:red">비밀번호 변경 실패</p>';
                }
            } else {
                $msg = '<p style="color:red">유효하지 않은 링크입니다.</p>';
            }
        }
    }
}

// GET 처리 (비밀번호 재설정 링크 접속)
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

$content = '';
$show_form = true;

if ($mode == 'reset' && !empty($token)) {
    // 토큰 유효성 확인
    $token = mysqli_real_escape_string($connect, $token);
    $q = "SELECT * FROM password_resets WHERE token='$token' AND expires_at > NOW() AND used=0 LIMIT 1";
    $r = mysqli_query($connect, $q);

    if (mysqli_num_rows($r) > 0) {
        // 비밀번호 재설정 폼
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
                <input type="password" name="pass1" class="inp" required>
            </div>
            <div class="form-group">
                <label>비밀번호 확인</label>
                <input type="password" name="pass2" class="inp" required>
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
            <p><a href="password_reset_simple.php">다시 시도하기</a></p>
        </div>
        ';
        $show_form = false;
    }
} else {
    // 기본: 아이디/이메일 입력 폼
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

mysqli_close($connect);
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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
            transition:opacity 0.2s;
        }
        .btn:hover { opacity:0.9; }
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
