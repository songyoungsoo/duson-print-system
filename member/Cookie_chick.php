<?php
if (isset($_COOKIE['id_login_ok'])) {
    $WebtingMemberLogin_id = $_COOKIE['id_login_ok'];
} else {
    echo ("<script language='javascript'>
    window.alert('쿠키로 인해 로그인 처리에 실패했습니다.\\n\\n해결1) 브라우저의 쿠키 설정을 확인해주세요.\\n\\n해결2) 서버의 데이터베이스를 확인해주세요.\\n\\n해결1이 실패하면 해결2를 진행해주세요.');
    history.go(-1);
    </script>");
    exit;
}
?>
