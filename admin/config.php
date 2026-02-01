<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

////////////////// 관리자 로그인 ////////////////////
function authenticate()
{
  HEADER("WWW-authenticate: basic realm=\"MlangWeb관리프로그램-관리자 전용!.. ♥ WEBSIL.net ♥\" ");
  HEADER("HTTP/1.0 401 Unauthorized");
  echo("<html><head><script>
       <!--
        function pop()
        { alert('관리자 인증 실패');
             history.go(-1);}
       //--->
        </script>
        </head>
        <body onLoad='pop()'></body>
        </html>
       ");
exit;
}

// ✅ 임시로 인증 비활성화 (개발/디버깅용)
// TODO: 실제 운영시에는 인증을 다시 활성화하세요

/*
// ✅ PHP 7.4 호환: 변수 초기화
$auth_user = $_SERVER['PHP_AUTH_USER'] ?? '';
$auth_pw = $_SERVER['PHP_AUTH_PW'] ?? '';

if (empty($auth_user) || empty($auth_pw))
{
 authenticate();
}

else
{
    include"../db.php";

    // ✅ users 테이블에서 관리자 조회 (member → users 마이그레이션)
    $stmt = mysqli_prepare($db, "SELECT username AS id, password AS pass FROM users WHERE is_admin = 1 LIMIT 1");
    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && $row = mysqli_fetch_assoc($result)) {
            $adminid = $row['id'] ?? '';
            $adminpasswd = $row['pass'] ?? '';

            // ✅ 아이디 비교 (타이밍 공격 방지)
            if (!hash_equals($auth_user, $adminid)) {
                authenticate();
            } else {
                // ✅ bcrypt 또는 평문 비밀번호 비교
                if (strlen($adminpasswd) === 60 && strpos($adminpasswd, '$2y$') === 0) {
                    if (!password_verify($auth_pw, $adminpasswd)) {
                        authenticate();
                    }
                } else {
                    if (!hash_equals($auth_pw, $adminpasswd)) {
                        authenticate();
                    }
                }
            }
        } else {
            authenticate();
        }

        mysqli_stmt_close($stmt);
    } else {
        error_log("Database prepare statement failed: " . mysqli_error($db));
        authenticate();
    }
}
*/
?>