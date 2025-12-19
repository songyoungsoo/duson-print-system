<?php
declare(strict_types=1);


// ⚠️  XSS 보호 권장: echo 시 htmlspecialchars() 사용을 고려하세요
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

$M123="..";
include"../top.php"; 

if($mode=="go"){
include"../../db.php";
include"./MaillingJoinAdminInfo.php";
?>

<BR><BR>

<FORM ACTION='<?echo("$PHP_SELF");?>' METHOD='POST'>
<INPUT TYPE=HIDDEN NAME=mode VALUE='sendmail_ok'>
<INPUT TYPE=HIDDEN NAME=ADMIN_TITLE VALUE='<?echo("$admin_name");?>'>
<INPUT TYPE=HIDDEN NAME=ADMIN_URL VALUE='<?echo("$admin_url");?>'>
<DIV ALIGN=LEFT>
<TABLE WIDTH=700 BORDER=0 CELLPADDING=5 CELLSPACING=5 align=center class='coolBar'>
<TR>
<TD COLSPAN=2>
<?php 
// 자료의 합계 값을 호출.....
$table="member";

$Mlang_query_inquiry="select * from $table";
$query_inquiry= mysqli_query($db, "$Mlang_query_inquiry");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$total_inquiry = mysqli_affected_rows($db);
mysqli_close($db);
?>

+ 총 <?echo("$total_inquiry");?> 명의 회원에게 공지메일을 발송합니다.
<INPUT TYPE=HIDDEN NAME=MEMBER_DATA VALUE='<?echo("$total_inquiry");?>'>
</TD>
</TR>

<TR>
<TD WIDTH=15%>&nbsp;&nbsp; 제목</TD><TD WIDTH=85%><INPUT TYPE=TEXT NAME='SUBJECT' SIZE=50 VALUE='<?echo("$admin_name");?>에서 발송한 회원전체메일입니다.'></TD>
</TR>

<TR>
<TD WIDTH=15%>&nbsp;&nbsp; 이메일/이름</TD><TD WIDTH=85%><INPUT TYPE=TEXT NAME='ADMIN_EMAIL' SIZE=50 VALUE='<?echo("$admin_email");?>'> <INPUT TYPE=TEXT NAME='ADMIN_NAME' SIZE=30 VALUE='<?echo("$admin_name");?>'></TD>
</TR>

<TR>
<TD COLSPAN=2>&nbsp;

글자체 : <SELECT NAME='FONT_FAMILY' size='1'>
<option selected value='굴림'>굴림</OPTION>
<OPTION VALUE='굴림체'>굴림체</OPTION>
<OPTION VALUE='돋움'>돋움</OPTION>
<OPTION VALUE='돋움체'>돋움체</OPTION>
<OPTION VALUE='바탕'>바탕</OPTION>
<OPTION VALUE='바탕체'>바탕체</OPTION>
<OPTION VALUE='궁서'>궁서</OPTION>
<OPTION VALUE='궁서체'>궁서체</OPTION>
</SELECT>

글자색 : <SELECT NAME='FONT_COLOR' size='1'>
<option selected value='black'>검정</OPTION>
<OPTION VALUE='white'>흰색</OPTION>
<OPTION VALUE='navy'>군청</OPTION>
<OPTION VALUE='blue'>파랑</OPTION>
<OPTION VALUE='red'>빨강</OPTION>
<OPTION VALUE='purple'>보라</OPTION>
<OPTION VALUE='EEEEEE'>회색</OPTION>
<OPTION VALUE='yellow'>노랑</OPTION>
<OPTION VALUE='teal'>청녹</OPTION>
</SELECT>

배경색 : <SELECT NAME='BGCOLOR' size='1'>
<option selected value='FFFFFF'>흰색</OPTION>
<OPTION VALUE='navy'>군청</OPTION>
<OPTION VALUE='blue'>파랑</OPTION>
<OPTION VALUE='red'>빨강</OPTION>
<OPTION VALUE='purple'>보라</OPTION>
<OPTION VALUE='#EEEEEE'>회색</OPTION>
<OPTION VALUE='yellow'>노랑</OPTION>
<OPTION VALUE='teal'>청녹</OPTION>
</SELECT>

사이즈 : <SELECT NAME='FONT_SIZE' size='1'>
<OPTION VALUE='1'>1</OPTION>
<OPTION VALUE='2'>2</OPTION>
<OPTION VALUE='3' selected>3</OPTION>
<OPTION VALUE='4'>4</OPTION>
<OPTION VALUE='5'>5</OPTION>
<OPTION VALUE='6'>6</OPTION>
<OPTION VALUE='7'>7</OPTION>
</SELECT>

HTML : <SELECT NAME='HTML' size='1'>
<OPTION VALUE='Y'>ON</OPTION>
<OPTION VALUE='N'selected>OFF</OPTION>
</SELECT>


</TD>
</TR>

<TR>
<TD COLSPAN=2 ALIGN=CENTER><TEXTAREA NAME=CONTENT ROWS=18 COLS=94 STYLE='width:99%;'></TEXTAREA></TD>
</TR>

<TR>
<TD COLSPAN=2> + 이메일 하단에 첨가될 풋터코드를 입력해 주세요.</TD>
</TR>

<TR>
<TD COLSPAN=2>
<TEXTAREA NAME=CONTENT1 ROWS=5 COLS=94 STYLE='width:99%;'>
홈페이지 : <?echo("$admin_url");?>

전자우편 : <?echo("$admin_email");?>


CopyRight (c) <?echo("$admin_name");?> All Rights Reserved.</TEXTAREA></TD>
</TR>

</TABLE>

<p align=center>
<INPUT TYPE=SUBMIT VALUE=' 발송하기 '>
</p>

</DIV>
</FORM>

</BODY>
</HTML>


<?php 
}
?>

<?php 
if($mode=="sendmail_ok"){

function ERROR($msg)
{
echo "<script language=javascript>
window.alert('$msg');
history.go(-1);
</script>";
exit;
}

if ( !$ADMIN_NAME ) {
$msg = "관리자 이름을 입력해 주세요."; ERROR($msg);
}
if ( !$ADMIN_EMAIL ) {
$msg = "관리자 이메일 주소를 입력해 주세요."; ERROR($msg);
}
if ( !$SUBJECT ) {
$msg = "제목을 입력해 주세요."; ERROR($msg);
}
if ( !$CONTENT ) {
$msg = "내용을 입력해 주세요."; ERROR($msg);
}

$MEMBER_NUM = $MEMBER_DATA;

if( $HTML != "Y" ) {    
	$CONTENT = nl2br(stripslashes($CONTENT));
}
else {
	$CONTENT = stripslashes($CONTENT);
}
$CONTENT1 = nl2br(stripslashes($CONTENT1));

$i = 0;
$xx = 0;

include"../../db.php";
$result= mysqli_query($db, "select * from member");
$rows=mysqli_num_rows($result);
if($rows){

while( $i < $MEMBER_NUM) 
{ 

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$MEMBER_ID = mysql_result($result,$i,id);
$MEMBER_NAME = mysql_result($result,$i,name);
$MEMBER_EMAIL = mysql_result($result,$i,email);
$REMAIL = Y;
$REGIS_OK = checked;

	if ( $REGIS_OK == "checked" && $REMAIL == "Y" ) {

		$SEND_CONTENT = "<HTML>
		<HEAD>
		<STYLE>
		<!--
		A:link {text-decoration:none;color:black;}
		A:visited {text-decoration:none;color:black;}
		A:hover {  text-decoration:underline;  color:#081E8A;}
		p,br,body,td {color:black; font-size:9pt; line-height:140%;}
		-->
		</STYLE>
		</HEAD>
		<BODY BGCOLOR='#FFFFFF'>

		<table border=0 align=center width=100% cellpadding='5' cellspacing='1' BGCOLOR='#339999'>

		<TR>
		<TD><font style='color:#FFFFFF'>$SUBJECT</font></TD>
		</TR>

		<TR>
		<TD BGCOLOR='$BGCOLOR' HEIGHT='400' VALIGN='TOP'>
		<BR>
		<FONT COLOR='$FONT_COLOR' FACE='$FONT_FAMILY' SIZE='$FONT_SIZE'>$CONTENT</FONT>
		</TD>
		</TR>

		</TABLE>


		<BR><p align=center><font style='color:#939393; font-size:9pt;'>$CONTENT1</font></p>
		<BR><BR><BR>
		</BODY>
		</HTML>";

		if($ADMIN_NAME && $ADMIN_EMAIL) {
			$from = "\"$ADMIN_NAME\" <$ADMIN_EMAIL>";
		}
		else {
			$from = "$ADMIN_EMAIL";
		}
		$TO = "\"$MEMBER_NAME\" <$MEMBER_EMAIL>";
		$from = "From:$from\nContent-Type:text/html";
		mail($TO, $SUBJECT , $SEND_CONTENT , $from);
		
		$xx++;
		echo "<TABLE WIDTH=700 align=center><TR><TD>[$xx] $MEMBER_NAME ($MEMBER_EMAIL) 발송완료.</TD></TR></TABLE>";
	}
	$i++;
}
echo "<SCRIPT LANGUAGE=JAVASCRIPT>
window.alert('총 $i 분중 이메일수신을 원하시는 $xx 분께 이메일 발송을 마쳤습니다.');
</SCRIPT>";

echo "<P><TABLE WIDTH=700 ALIGN=CENTER><TR><TD ALIGN=CENTER><input type='button' onClick='javascript:history.go(-1)' value='전체메일보내기로 돌아가기'></TD></TR></TABLE><BR><BR><BR>";
exit;
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

}else{
echo ("<script language=javascript>
window.alert('가입 되어 있는 회원이 없음으로 메일을 보낼수 없습니다.');
history.go(-1);
</script>
");
exit;
}

mysqli_close($db);
}
?>

<?php 
include"../down.php";
?>