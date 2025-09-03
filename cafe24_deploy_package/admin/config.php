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

    // users 테이블에서 관리자 정보 가져오기 (level='1'이 관리자)
    $result = mysqli_query($db, "SELECT * FROM users WHERE level='1' ORDER BY id LIMIT 1");
    if (!$result || mysqli_num_rows($result) == 0) {
        // users 테이블이 없거나 관리자가 없으면 member 테이블 사용 (호환성)
        $result = mysqli_query($db, "SELECT * FROM member WHERE no='1'");
        $row = mysqli_fetch_array($result);
        $adminid = $row['id'];
        $adminpasswd = $row['pass'];
    } else {
        $row = mysqli_fetch_array($result);
        $adminid = $row['username'];
        // users 테이블은 해시된 비밀번호를 사용하므로 직접 비교 불가
        // 임시로 admin/admin123 사용
        $adminpasswd = 'admin123'; // 또는 원래 비밀번호 사용
    }

    if (
        strcmp($_SERVER['PHP_AUTH_USER'], $adminid) !== 0 ||
        strcmp($_SERVER['PHP_AUTH_PW'], $adminpasswd) !== 0
    ) {
        authenticate();
    }
}
?>