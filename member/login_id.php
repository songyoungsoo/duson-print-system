<?php
session_start();
if (isset($_SESSION['id_login_ok'])) {
    $WebtingMemberLogin_id = $_SESSION['id_login_ok'];
} elseif (isset($_COOKIE['id_login_ok'])) {
    $WebtingMemberLogin_id = $_COOKIE['id_login_ok'];
} else {
    echo ("<script>
    window.alert('로그인 상태가 아닙니다.\\n\\n1) 인터넷 쿠키를 활성화 해주세요.\\n2) 데이터베이스 관리자에게 문의하세요.\\n\\n1번을 해결하신 후에도 문제가 지속되면 2번을 해결해주세요.');
    history.go(-1);
    </script>");
    exit;
}
?>
