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
    // 간단한 하드코딩 방식으로 복원 (기존 작동 방식)
    $adminid = 'admin';
    $adminpasswd = 'admin123';

    if (
        strcmp($_SERVER['PHP_AUTH_USER'], $adminid) !== 0 ||
        strcmp($_SERVER['PHP_AUTH_PW'], $adminpasswd) !== 0
    ) {
        authenticate();
    }
}
?>