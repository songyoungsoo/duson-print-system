<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

$db = mysqli_connect("host", "user", "password", "dataname");
if (!$db) {
    die("DB 연결 실패: " . mysqli_connect_error());
}
?>

//include "../member/login_chick.php";
include "../top.php";
?>


<table border=0 align=center width=100% cellpadding='0' cellspacing='0' background='/img/line1_back.gif'>
<tr><td align=right><img src='../img/line_bbs.gif'  width=550 height=25></td></tr>
</table>

<table border=0 width=75% align=center cellpadding='0' cellspacing='0'>
<tr><td><img src='/img/2345.gif' width=1 height=20></td></tr>
<tr><td><font style='line-height:130%;'>
* 배너 등록*관리는 로그인후 이용하실수 있는 서비스 입니다.<BR>
* 로그인은 회원가입후 신청하신 아이디와 비밀번호를 입력후 인증받으시면 됩니다.<BR>
* <b>배너등록과 회원가입은 100% 무료</b> 입니다.
</font></td></tr>
<tr><td><img src='/img/2345.gif' width=1 height=20></td></tr>
</table>


☞ 배너 등록하기
☞ 




<?php include "../down.php";?>