<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB 연결 정보
$host = "localhost";
$user = "duson1830";
$password = "du1830";
$dataname = "duson1830";

// 커넥션 객체 생성
$db = new mysqli($host, $user, $password, $dataname);
if ($db->connect_error) {
    die("❌ DB 연결 실패(db.php): " . $db->connect_error);
}
$db->set_charset('utf8');

// echo "<!-- ✅ DB 연결 성공 in db.php -->";
// ✅ 함수 선언 (다른 곳에서 안전하게 호출 가능)
// function getSafeDB() {
//   static $dbInstance;

//   if ($dbInstance instanceof mysqli && !$dbInstance->connect_error) {
//       return $dbInstance;
//   }

//   // 안전하게 환경 가져오기
//   $host = "localhost";
//   $user = "duson1830";
//   $password = "du1830";
//   $dataname = "duson1830";

//   $dbInstance = new mysqli($host, $user, $password, $dataname);
//   if ($dbInstance->connect_error) {
//       die("❌ DB 재연결 실패: " . $dbInstance->connect_error);
//   }

//   $dbInstance->set_charset("utf8");
//   return $dbInstance;
// }


// 사이트/관리자 기본정보
$admin_email = "dsp1830@naver.com";
$admin_name = "두손기획";
$MataTitle = "$admin_name - 인쇄, 스티커, 전단지, 리플렛, 포스터, 브로슈어, 카다로그, 패키지, 각종 판촉물,인쇄홍보물, 온라인견적 등 인쇄에서 후가공까지 일괄작업.공장직영으로 신속 제작.";
$SiteTitle = $admin_name;
$admin_Tname = "Mlang";
$admin_url = "http://localhost";
$Homedir = $admin_url;

// 테이블 명시
$admin_table = "member";
$page_big_table = "page_menu_big";
$page_table = "page";
$home_cookie_url = ".dsp1830.shop";


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
