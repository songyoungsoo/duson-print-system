<?php 
// 변수 초기화
$M123 = isset($M123) ? $M123 : ".";
include "$M123/../db.php";
include "$M123/config.php";
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

<!-- <script src="<?php echo $M123?>/js/coolbar.js" type="text/javascript"></script>
<script src="<?php echo $M123?>/js/admin_menu.js" type="text/javascript"></script> -->


<!-- <script language="JavaScript">
function MM_jumpMenu(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script> -->

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<?php include "$M123/admin_menu.php";?>

<table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
<tr>
<td valign=top>
