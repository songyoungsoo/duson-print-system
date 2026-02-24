<?php
// 에러 표시 설정
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

// 세션 수명 8시간 통일
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 28800);
    session_set_cookie_params([
        'lifetime' => 28800,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
$session_id = session_id();

// 통합 로그인 상태 확인 (신규 시스템 우선)
$is_logged_in = false;

// 1. 신규 시스템 확인 (user_id)
if (isset($_SESSION['user_id'])) {
    $is_logged_in = true;
}
// 2. 구 시스템 확인 (id_login_ok) - 신규 시스템 로그인이 없을 때만
elseif (isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok'])) {
    // 구 시스템만 있고 신규 시스템 없음 = 세션 불일치
    // 구 시스템 쿠키/세션 정리하고 새로 로그인 유도
    unset($_SESSION['id_login_ok']);
    if (isset($_COOKIE['id_login_ok'])) {
        setcookie('id_login_ok', '', time() - 3600, '/');
    }
    // 로그인 페이지 표시 (is_logged_in = false 유지)
}

if ($is_logged_in) {
    echo "<script language='javascript'>
          window.alert('회원님은 이미 로그인되어 있습니다.');
          history.back();
          </script>";
    exit;
}

// 로그인 상태가 아닌 경우에만 로그인 화면을 표시
include __DIR__ . "/../db.php";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400&display=swap">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: rgba(0, 0, 0, 0.8);
            font-size: 9pt;
            font-family: 'Noto Sans', sans-serif;
            margin: 0;
        }
        form {
            width: 420px;
            border: none;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            text-align: center;
            background-color: #ffffff;
            position: relative;
        }
        form table {
            width: 100%;
            background-color: transparent; /* 테이블의 배경색을 투명하게 설정 */
        }
        form table td:first-child {
            text-align: right;
            font-weight: bold;
            color: #696969;
        }
        form table td input[type='text'],
        form table td input[type='password'] {
            width: calc(100% - 12px);
            padding: 5px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        input[type='submit'],
        input[type='button'] {
            padding: 5px 10px;
            background-color: dodgerblue;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            font-size: 9pt;
            font-family: 'Noto Sans', sans-serif;
        }
        input[type='submit']:hover,
        input[type='button']:hover {
            background-color: skyblue;
        }
        p a {
            color: #B3B46A;
            text-decoration: none;
            font-weight: bold;
        }
        h2 {
            font-size: 12pt;
            font-weight: bold;
            font-family: 'Noto Sans', sans-serif;
        }
    </style>
</head>
<body>

<script type="text/javascript">
function MemberCheckField() {
    var form = document.FrmUserInfo;

    if (!form.id.value) {
        alert('아이디를 입력해주세요.');
        form.id.focus();
        return false;
    }

    if (!form.pass.value) {
        alert('비밀번호를 입력해주세요.');
        form.pass.focus();
        return false;
    }

    return true;
}
</script>

<form name='FrmUserInfo' method='post' onsubmit='return MemberCheckField();' action='login_unified.php'>
    <?php include_once __DIR__ . '/../includes/csrf.php'; csrf_field(); ?>
    <input type='hidden' name='mode' value='member_login'>
    <input type='hidden' name='redirect' value='<?php echo htmlspecialchars($_GET['redirect'] ?? '/'); ?>'>

    <table border="0" align="center" cellpadding='3' cellspacing='0'>
        <h2>두손기획인쇄</h2>
        <tr>
            <td>아이디</td>
            <td><input type='text' name='id' size='20' maxlength='20'></td>
        </tr>
        <tr>
            <td>비밀번호</td>
            <td><input type='password' name='pass' size='20' maxlength='12'></td>
        </tr>
    </table>

    <input type='submit' value=' 로 그 인 '>
    <input type='button' value=' 회원가입 ' onclick="window.location.href='join.php';">
    <input type="checkbox" name="remember_me" id="remember_me">
    <label for="remember_me">자동 로그인</label>

    <p>
        <a href='/member/password_reset_simple.php' style="color: #667eea; font-weight: bold;">🔐 비밀번호를 잊으셨나요?</a>
    </p>
</form>
</body>
</html>
