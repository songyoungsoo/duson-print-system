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

// member 상세 자료 호출
include"../../db.php";
$result= mysqli_query($db, "select * from member where no='$no'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$rows=mysqli_num_rows($result);
if($rows){

while($row= mysqli_fetch_array($result)) 
{ 
$AD_no="$row[no]"; 
$AD_name="$row[name]";   
$AD_email="$row[email]";  
}

}else{}

mysqli_close($db); 
?>

<html>
<head>

<?php if($code=="1"){ ?>

<title>[<?echo("$AD_name");?>]님에게-개인 메일보내기</title>

<?php } ?>

<?php if($code=="2"){ ?>

<title>[<?echo("$id");?>]님에게-개인 메일보내기</title>

<?php } ?>


<style>
body,td {font-family:굴림; font-size: 10pt; color:#D4E7FD; line-height:140%;}
b {font-family:굴림; font-size: 10pt; color:#FFFFFF;}
</style>

<script language=javascript>

function MemberCheckField()
{
var f=document.FrmUserInfo;

if (f.title.value == "") {
alert("제목을 입력하여주세요?");
return false;
}
if (f.connent.value == "") {
alert("내용을 입력하여주세요?");
return false;
}

}

</script>

</head>



<body bgcolor='#000000' LEFTMARGIN='10' TOPMARGIN='10' MARGINWIDTH='10' MARGINHEIGHT='10'> 

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='#CCCCCC'>

<form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField()' action='<?echo("$PHP_SELF");?>'>

<?php if($code=="1"){ ?>

<input type='hidden' name='mode' value='ok'>
<input type='hidden' name='member_name' value='<?echo("$AD_name");?>'>
<input type='hidden' name='member_email' value='<?echo("$AD_email");?>'>

<?php } ?>

<?php if($code=="2"){ ?>

<input type='hidden' name='mode' value='ok'>
<input type='hidden' name='member_name' value='<?echo("$id");?>'>
<input type='hidden' name='member_email' value='<?echo("$email");?>'>

<?php } ?>

<tr>
<td valign=top bgcolor='#000000' width=30%>
<b>제목</b>
</td>
<td valign=top bgcolor='#606060' width=70%>
<input type='text' name='title' size='61'>
</td>
</tr>

<tr>
<td valign=top bgcolor='#000000' width=30%>
<b>문서의형식</b>
</td>
<td valign=top bgcolor='#606060' width=70%>
<select name='style'>
<option value='text'>텍스트</option>
<option value='html'>HTML</option>
</select>
</td>
</tr>

<tr>
<td bgcolor='#000000' width=30%>
<b>내용</b>
</td>
<td valign=top bgcolor='#606060' width=70%>
<textarea cols=60 name=connent rows=20></textarea>
</td>
</tr>

</table>

<p align=center>
<input type='submit' value=' 메일 보내기 ' style="background-color:#37383A; color:#FFFFFF; border-width:1; border-style:solid; height:21px; border:1 solid #FFFFFF;">
&nbsp;&nbsp;
<input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE' style="background-color:#37383A; color:#FFFFFF; border-width:1; border-style:solid; height:21px; border:1 solid #FFFFFF;">
</p>

</form>

</body>

</html>

<?php 

if($mode=="ok"){

include"../../db.php";

$TO_NAME="$member_name"; 
$TO_EMAIL="$member_email"; 
$FROM_NAME="$admin_name"; 
$FROM_EMAIL="$admin_email"; 
$SUBJECT="$admin_name 에서 발송한 메일입니다."; 

if($style=="text"){
        $CONTENT=$connent;
		$CONTENT = eregi_replace("<", "&lt;", $CONTENT);
		$CONTENT = eregi_replace(">", "&gt;", $CONTENT);
		$CONTENT = eregi_replace("\"", "&quot;", $CONTENT);
		$CONTENT = eregi_replace("\|", "&#124;", $CONTENT);
		$CONTENT = eregi_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = eregi_replace("\r\n", "<BR>", $CONTENT);
		$connent_text=$CONTENT;
}
if($style=="html"){$connent_text="$connent";}

$SEND_CONTENT = "
<html>
<table border=0 align=center width=100% cellpadding='7' cellspacing='1' bgcolor='#000000'>
<tr>
<td bgcolor='#393839'>
&nbsp;&nbsp;<font style='font-size:10pt; color:#FFFFFF;'>$title</font>
</td>
</tr>
</table>
<BR>
$connent_text
<p align=center>
Copyright ⓒ 2004 <a href='$admin_url' target='_blank'>$admin_name</a> Corp. All rights reserved. 메일: $admin_email
</p>
</html>
"; 


if($FROM_NAME && $FROM_EMAIL) {
	$from = "\"$FROM_NAME\" <$FROM_EMAIL>";
}
else {
	$from = "$FROM_EMAIL";
}
$TO = "\"$TO_NAME\" <$TO_EMAIL>";
$from = "From:$from\nContent-Type:text/html";
mail($TO, $SUBJECT , $SEND_CONTENT , $from);

echo ("<script language=javascript>
window.alert('$member_name 회원님에게 정상적으로 메일을 발송하였습니다.');
window.self.close();
</script>
");
exit;


}

?>
