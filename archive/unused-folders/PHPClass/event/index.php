<?php
if(!$EventDir){$EventDir=".";}
include "$EventDir/config.php";
?>

<html>
<head>
<title><?php echo $title?></title>
<meta http-equiv='Content-type' content='text/html; charset=UTF-8'>
<META NAME='KEYWORDS' CONTENT='MlangWeb관리프로그램-홈페이지팝업창프로그램'>
<meta name='author' content='Mlang'>
<meta name='classification' content='MlangWeb관리프로그램-홈페이지팝업창프로그램'>
<meta name='description' content='MlangWeb관리프로그램-홈페이지팝업창프로그램'>
<!--------------------------------------------------------------------------------
     디자인 편집툴-포토샵7.0, 플래쉬MX
     프로그램 제작툴-에디터플러스2
     프로그램언어: PHP, javascript, DHTML, html
     제작자: Mlang - 메일: webmaster@script.ne.kr
     URL: http://www.websil.net , http://www.script.ne.kr

* 현 사이트는 MYSQLDB(MySql데이터베이스) 화 작업되어져 있는 홈페이지 입니다.
* 홈페이지의 해킹, 사고등으로 자료가 없어질시 5분안에 복구가 가능합니다.
* 현사이트는 PHP프로그램화 되어져 있음으로 웹초보자가 자료를 수정/삭제 가능합니다.
* 페이지 수정시 의뢰자가 HTML에디터 추가를 원하면 프로그램을 지원합니다.
* 모든 페이지는 웹상에서 관리할수 있습니다.
----------------------------------------------------------------------------------->

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<?php

if($style=="br"){
        $CONTENT=$txt;
		$CONTENT = preg_replace("<", "&lt;", $CONTENT);
		$CONTENT = preg_replace(">", "&gt;", $CONTENT);
		$CONTENT = preg_replace("\"", "&quot;", $CONTENT);
		$CONTENT = preg_replace("\|", "&#124;", $CONTENT);
		$CONTENT = preg_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = preg_replace("\r\n", "<BR>", $CONTENT);
		$connent_text=$CONTENT;
}
if($style=="html"){$connent_text="$txt";}

echo("$connent_text");
?>

</body>

</html>
?>