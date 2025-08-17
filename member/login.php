<?php
// 에러 표시 설정
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

session_start();
$session_id = session_id();

// 세션 또는 쿠키에 로그인 정보가 있는지 확인
if (isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok'])) {
    echo "<script language='javascript'>
          window.alert('회원님은 이미 로그인되어 있습니다.');
          history.back();
          </script>";
    exit;
}

// 로그인 상태가 아닌 경우에만 로그인 화면을 표시
include __DIR__ . "/db.php";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/sambo.css" type="text/css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400&display=swap">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('./img/149414.jpg') no-repeat center center fixed;
            background-size: cover;
            font-size: 9pt;
            font-family: 'Noto Sans', sans-serif;
            margin: 0; /* 기본 여백 제거 */
        }
        form {
            width: 420px;
            border: 1px solid gray;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 1px 1px 10px gray;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8); /* 투명한 배경 색상 */
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

<script src="/js/login.js" type="text/javascript"></script>

<form name='FrmUserInfo' method='post' onsubmit='return MemberCheckField();' action='login_unified.php'>
    <input type='hidden' name='selfurl' value='<?php echo htmlspecialchars($pageselfurl ?? $LoginChickBoxUrl); ?>'>
    <input type='hidden' name='mode' value='member_login'>

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
        <a href='#' onclick="window.open('/member/member_search.php?mode=id', 'member_search','width=510,height=220,top=100,left=100,statusbar=no,scrollbars=yes,toolbar=no');">분실한 회원 아이디/비밀번호 찾기</a>
    </p>
</form>
</body>
</html>
