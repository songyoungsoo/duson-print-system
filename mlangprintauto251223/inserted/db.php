<?php
session_start();
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

// MySQLi로 데이터베이스 연결
$db = new mysqli($host, $user, $password, $dataname);

// 연결 확인
if ($db->connect_error) {
    die("데이터베이스 연결에 실패했습니다: " . $db->connect_error);
}

// UTF-8 인코딩 설정
$db->set_charset("utf8");

$admin_email = "dsp1830@naver.com";
$admin_name = "두손기획";
$MataTitle = "$admin_name - 인쇄, 스티커, 전단지, 리플렛, 포스터, 브로슈어, 카다로그, 패키지, 각종 판촉물,인쇄홍보물, 온라인견적 등 인쇄에서 후가공까지 일괄작업.공장직영으로 신속 제작.";
$SiteTitle = $admin_name;
$admin_Tname = "Mlang";
$admin_url = "http://www.dsp114.com";
$Homedir = $admin_url;
$admin_table = "member"; // 관리자 테이블
$page_big_table = "page_menu_big"; // 주메뉴 테이블
$page_table = "page"; // 페이지 내용 테이블
$home_cookie_url = ".dsp114.com"; // 홈 쿠키 url

$WebSoftCopyright = "
<p align=center>
  Copyright ⓒ 2005 MlangWebProgram - WEBSOFT 제공:
  <a href='http://www.websil.net' target='_blank'>
    <font style='color:#408080; text-decoration:none'><b>WEBSIL</b>.net</font>
  </a> Corp All rights reserved.
</p>";

$WebSoftCopyright2 = "
<p align=center>
  Copyright ⓒ 2005 MlangWebProgram<br>
  WEBSOFT 제공:
  <a href='http://www.websil.net' target='_blank'>
    <font style='color:#408080; text-decoration:none'><b>WEBSIL</b>.net</font>
  </a> Corp All rights reserved.
</p>";

$WebSoftCopyright3 = "
<p align=center>
  <font style='font-family:돋음; color:#B2B2B2; font-size:8pt;'>
    Copyright ⓒ 2005 MlangWebProgram - WEBSOFT 제공:
    <a href='http://www.websil.net' target='_blank'>
      <font style='color:#8C8C8C; text-decoration:none'><u><b>WEBSIL</b>.net</u></font>
    </a> Corp All rights reserved.
  </font>
</p>";
?>
<style>
td, input, li, a { font-size: 9pt; }
th {
  background-color: #CCCCFF;
  font-size: 9pt;
  text-decoration: none;
}
border { border-color: #CCC; }
</style>
