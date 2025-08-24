<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 멀티 DB 서버 연결 시스템
$db_configs = [
    // 설정 1: 로컬 XAMPP (root 계정)
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'db' => 'duson1830', 'port' => 3306],
    
    // 설정 2: 로컬 XAMPP (duson1830 계정)
    ['host' => 'localhost', 'user' => 'duson1830', 'password' => 'du1830', 'db' => 'duson1830', 'port' => 3306],
    
    // 설정 3: 네트워크 서버
    ['host' => '192.168.0.250', 'user' => 'duson1830', 'password' => 'du1830', 'db' => 'duson1830', 'port' => 3306],
    
    // 설정 4: 외부 서버
    ['host' => 'sknas205.ipdisk.co.kr', 'user' => 'duson1830', 'password' => 'du1830', 'db' => 'duson1830', 'port' => 3306],
];

$db = null;
foreach ($db_configs as $config) {
    $host_port = $config['host'] . (($config['port'] != 3306) ? ':' . $config['port'] : '');
    $db = @mysqli_connect($config['host'], $config['user'], $config['password'], $config['db'], $config['port']);
    
    if ($db) {
        mysqli_query($db, "SET NAMES 'utf8'");
        break;
    }
}

if (!$db) {
    die("모든 데이터베이스 서버 연결에 실패했습니다: " . mysqli_connect_error());
}

$admin_email = "dsp1830@naver.com";
$admin_name = "두손기획";
$MataTitle = "$admin_name - 인쇄, 스티커, 전단지, 리플렛, 포스터, 브로슈어, 카다로그, 패키지, 각종 판촉물,인쇄홍보물, 온라인견적 등 인쇄에서 후가공까지 일괄작업.공장직영으로 신속 제작.";
$SiteTitle = $admin_name;
$admin_Tname = "Mlang";
$admin_url = "http://localhost";
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
