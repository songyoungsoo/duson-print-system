<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';
// ✅ $M123이 미리 설정되어 있지 않은 경우만 GET/POST에서 가져오기
if (!isset($M123) || empty($M123)) {
    $M123 = $_GET['M123'] ?? $_POST['M123'] ?? '';
}

if(!$M123){$M123=".";}

// ✅ 상대 경로 문제 해결 - 절대 경로 기준으로 수정
$admin_dir = dirname(__FILE__); // admin 디렉토리
include $admin_dir . "/../db.php";
include $admin_dir . "/config.php";

// ✅ CSP 보안 정책 설정 - eval() 허용 (인증 후 설정)
// Google Fonts 허용 추가
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-eval' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;");
?>

<html>
<head>
<title>MlangWeb관리프로그램(3.2)</title>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<!--------------------------------------------------------------------------------

     프로그램명: MlangWeb관리프로그램 버젼3.0
     프로그램 제작툴-에디터플러스2
     프로그램언어: PHP, javascript, DHTML, html
     제작자: Mlang 

// 3.2 에 추가된  기능 --------------------------------------------------------------//

(1) 게시판 PHOTO SKIN  기능 수정
(1) 게시판상위 공지 삽입/출력 기능 추가

//-------------------------------------------------------------------------------//


* 현 사이트는 MYSQLDB(MySql데이터베이스) 화 작업되어져 있는 홈페이지 입니다.
* 홈페이지의 해킹, 사고등으로 자료가 없어질시 5분안에 복구가 가능합니다.
* 현사이트는 PHP프로그램화 되어져 있음으로 웹초보자가 자료를 수정/삭제 가능합니다.
* 페이지 수정시 의뢰자가 HTML에디터 추가를 원하면 프로그램을 지원합니다.
* 모든 페이지는 웹상에서 관리할수 있습니다.

   프로그램 에러 있을시 : ☏ 011-548-7038, 임태희 (전화안받을시 문자를주셔염*^^*)
----------------------------------------------------------------------------------->
<STYLE>
body,td,input,select{color:#000000; font-size:9pt; FONT-FAMILY:굴림; line-height:130%; word-break:break-all;}
a:link    {font-size:9pt; font-family:굴림,Tahoma; text-decoration:none;}
a:visited {font-size:9pt; font-family:굴림,Tahoma; text-decoration:none;}
a:hover   {font-size:9pt; font-family:굴림,Tahoma; text-decoration:underline;}

a.menu:link, a.menu:visited	{color:#FFFFFF; font-size:9pt; FONT-FAMILY:굴림; text-decoration:none; font-weight:none;}
a.menu:hover, a.menu:active{color:#000000; font-size:9pt; FONT-FAMILY:굴림; text-decoration:none; font-weight:bold;}


.admin_menu{color:#000000; font:bold; font-size:10pt; FONT-FAMILY:굴림; cursor : default;}
.down { font:bold; font-size:10pt; border-width:1; border-style:solid; border-color:#000000 #FFFFFF #FFFFFF #000000; color:white; text-align:center; padding:2 0 0 2}

a.mune123:link, a.mune123:visited	{color:#000000; font-size:9pt; FONT-FAMILY:굴림; text-decoration:none; font-weight:none;}
a.mune123:hover, a.mune123:active{color:#FFFFFF; font-size:9pt; FONT-FAMILY:굴림; text-decoration:none; font-weight:none;}

td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000; font-size:10pt; FONT-FAMILY:굴림; line-height:130%; word-break:break-all;}
</STYLE>

<script src="<?=($M123 === '..') ? '../js/coolbar.js' : $M123 . '/js/coolbar.js'?>" type="text/javascript"></script>
<script src="<?=($M123 === '..') ? '../js/admin_menu.js' : $M123 . '/js/admin_menu.js'?>" type="text/javascript"></script>


<script language="JavaScript">
function MM_jumpMenu(targ,selObj,restore){
  // ✅ PHP 7.4 호환: eval() 사용 제거 - CSP 보안 강화
  if (targ === 'parent') {
    parent.location = selObj.options[selObj.selectedIndex].value;
  } else if (targ === 'self') {
    self.location = selObj.options[selObj.selectedIndex].value;
  } else if (targ === 'top') {
    top.location = selObj.options[selObj.selectedIndex].value;
  } else {
    // 기본값: 현재 창
    window.location = selObj.options[selObj.selectedIndex].value;
  }
  if (restore) selObj.selectedIndex=0;
}
</script>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<?php include $admin_dir . "/admin_menu.php";?>

<table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
<tr>
<td valign=top>
