<?php
ob_start(); // 출력 버퍼링 시작

session_start();
$_SESSION['id_login_ok'] = "";
setcookie("id_login_ok", "", time() - 3600, "/");
session_unset();
session_destroy();

// 로그아웃 후 리다이렉트할 페이지로 이동
header("Location:../shop/view.php");
exit();

ob_end_flush(); // 출력 버퍼 내용을 전송하고 버퍼링 종료
?>
