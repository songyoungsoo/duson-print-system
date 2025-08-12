<?php
////////////////// 관리자 로그인 ////////////////////
function authenticate()
{
    header("WWW-Authenticate: Basic realm=\"MlangWeb관리프로그램-관리자 인증!.. ♥ WEBSIL.net ♥\"");
    header("HTTP/1.0 401 Unauthorized");
    echo "<html><head><script>
        function pop() {
            alert('관리자 인증 실패');
            history.go(-1);
        }
        </script></head>
        <body onLoad='pop()'></body>
        </html>";
    exit;
}

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    authenticate();
} else {
    // DB 연결이 필요하다면 include 추가
    if (!isset($db)) {
        include_once(dirname(__FILE__) . '/../db.php');
    }

    $result = mysqli_query($db, "SELECT * FROM member WHERE no='1'");
    $row = mysqli_fetch_array($result);

    $adminid = $row['id'];
    $adminpasswd = $row['pass'];

    if (
        strcmp($_SERVER['PHP_AUTH_USER'], $adminid) !== 0 ||
        strcmp($_SERVER['PHP_AUTH_PW'], $adminpasswd) !== 0
    ) {
        authenticate();
    }
}
?>