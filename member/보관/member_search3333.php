<?include "../db.php";?>

<html>
<head>
<title><?=$admin_name?>-���̵�/��й�ȣã��</title>
<STYLE>

p,br,body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:����;}
b {color:black; font-size:10pt; FONT-FAMILY:����;}

</STYLE>
<link rel="stylesheet" type="text/css" href="http://www.script.ne.kr/script.css">
</head>

<?php
if(!strcmp($mode,"id")) {

?>

<head>
<script language="JavaScript">
////////////////////////////////////////////////////////////////////////////////
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;


function TypeCheck (s, spc) {
var i;

for(i=0; i< s.length; i++) {
if (spc.indexOf(s.substring(i, i+1)) < 0) {
return false;
}
}        
return true;
}

/////////////////////////////////////////////////////////////////////////////////
function MemberCheckField() {

var f=document.FrmUserInfo;

if (f.name.value == "") {
alert("������ �Է��� �ּ���. ");
return false;
}

if (f.phone1.value == "") {
alert("��ȭ��ȣ ���ڸ��� �Է��� �ּ���. ");
return false;
}
if ((f.phone1.value.length < 2) || (f.phone1.value.length > 4)) {
alert("��ȭ��ȣ ���ڸ���2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
return false;
}
if (!TypeCheck(f.phone1.value, NUM)) {
alert("��ȭ��ȣ ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
return false;
}

if (f.phone2.value == "") {
alert("��ȭ��ȣ�� �Է��� �ּ���. ");
return false;
}
if ((f.phone2.value.length < 4) || (f.phone2.value.length > 4)) {
alert("��ȭ��ȣ�� 4���� ���� �̿��� �մϴ�.");
return false;
}
if (!TypeCheck(f.phone2.value, NUM)) {
alert("��ȭ��ȣ�� ���ڷθ� ����� �� �ֽ��ϴ�.");
return false;
}

if (f.phone3.value == "") {
alert("��ȭ��ȣ ���ڸ��� �Է��� �ּ���. ");
return false;
}
if ((f.phone3.value.length < 4) || (f.phone3.value.length > 4)) {
alert("��ȭ��ȣ ���ڸ��� 4���� ���� �̿��� �մϴ�.");
return false;
}
if (!TypeCheck(f.phone3.value, NUM)) {
alert("��ȭ��ȣ�� ���ڷθ� ����� �� �ֽ��ϴ�.");
return false;
}

}

</script>
</head>

<body bgcolor='#FFFFFF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=5 align=left bgcolor='#000000' width=100%></td></tr>
<tr><td  height=30 align=left bgcolor='#43B5C9' width=100%>
&nbsp;&nbsp;<b><font color=#FFFFFF>�� ���̵� �� ã�Ƶ����... *^^*</font></b>
</td></tr>
<tr><td  height=15 width=100%></td></tr>
<tr><td align=center bgcolor='#FFFFFF'>

<table border=0 align=center cellpadding='0' cellspacing='0' width=395>
<tr><td align=center width=395 height=9><img src='img/member_search_top.gif' width=395 height=9></td></tr>
<tr><td align=center width=100% background='img/member_search_back.gif' height=90>

<b>�Ʒ��� ������ �Է��Ͻø� ���̵�, ��й�ȣ �� ã���࿰!!</b>

<table border=0 align=center cellpadding='0' cellspacing='0' width=300>
<form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField()' action='<?echo"$PHP_SELF";?>?mode=id_ok'>
<tr>
<td width=90 height=35 align=right valign=bottom>ȸ���̸�:&nbsp;</td>
<td width=210  valign=bottom>
<input type='text' NAME=name size='15' maxlength="20">
</td>
</tr>
<tr>
<td height=3 colspan=2 width=100%>
</td>
</tr>
<tr>
<td height=1 colspan=2 bgcolor='#5BB4D0' width=100%>
</td>
</tr>
<tr>
<td height=3 colspan=2 width=100%>
</td>
</tr>
<tr>
<td width=90 height=35 align=right  valign=top><p style='text-indent:0; margin-top:5pt;'> �޴�����ȭ��ȣ:&nbsp;</p></td>
<td width=330     valign=top>
<input type='text' name='hendphone1' size='8' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone1'");}?>>
-
<input type='text' name='hendphone2' size='8' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone2'");}?>>
-
<input type='text' name='hendphone3' size='8' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone3'");}?>>
</td>
</tr>
</table>

</td></tr>
<tr><td align=center width=395 height=8><img src='img/member_search_down.gif' width=395 height=8></td></tr>
</table>

</td></tr>

<tr><td  height=15 width=100%></td></tr>

<tr><td  height=50  valign=middle align=center bgcolor='#E0E7E0' width=100%>
<input type="image" src='img/member_search_1.gif' width=72 height=23 border=0>
<a href="#" onClick="javascript:window.close();"><img src='img/member_search_2.gif' width=72 height=23 border=0></a>
</td></tr>

</form>
</table>

<?php
}elseif(!strcmp($mode,"id_ok")) {
?>

<?php
//�����κа� ���������� url���� ������ ���´�........
function ERROR($msg)
{
echo "<script language=javascript>
window.alert('$msg');
history.go(-1);
</script>
";
exit;
}

if ( !$name || !$hendphone1  || !$hendphone2 || !$hendphone3) {
	$msg = "���������� ó������ ���Ͽ����ϴ�."; ERROR($msg); 
}

$query ="select * from member where name='$name' and hendphone1='$hendphone1' and hendphone2='$hendphone2' and hendphone3='$hendphone3' ";
$result = mysql_query($query,$db);
$rows=mysql_num_rows($result);
if($rows)
    {

while($row= mysql_fetch_array($result)) 
echo"
<head>
<script language=\"JavaScript\">
parent.resizeTo(510,340);

////////////////////////////////////////////////////////////////////////////////
function TypeCheck (s, spc) {
var i;

for(i=0; i< s.length; i++) {
if (spc.indexOf(s.substring(i, i+1)) < 0) {
return false;
}
}        
return true;
}

/////////////////////////////////////////////////////////////////////////////////
function MemberCheckField()
{
var f=document.FrmUserInfo;

if (f.email.value == \"\") {
alert(\"��й�ȣ�� ���� E���� �ּҸ� �Է��� �ּ���.\");
return false;
}

}

</script>
</head>

<body bgcolor='#FFFFFF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=5 align=left bgcolor='#000000' width=100%></td></tr>
<tr><td  height=30 align=left bgcolor='#43B5C9' width=100%>
&nbsp;&nbsp;<b><font color=#FFFFFF>�� ȸ������ ���̵� ã�Ҿ... *^^*</font></b>
</td></tr>
<tr><td  height=15 width=100%></td></tr>
<tr><td align=center bgcolor='#FFFFFF'>
<p align=left style='text-indent:0; margin-top:0pt; margin-right:20pt; margin-bottom:10pt; margin-left:45pt;'>
���� ȸ�� ���̵�� <b><font color=green>$row[id]</font></b> �Դϴ�.
</p>
<table border=0 align=center cellpadding='0' cellspacing='0' width=395>
<tr><td align=center width=395 height=9><img src='img/member_search_top.gif' width=395 height=9></td></tr>
<tr><td width=100% align=center background='img/member_search_back.gif' height=110>
<b><font color=#43B5C9> ----- ��й�ȣ �� ã�Ƶ帳�ϴ�. -----</font></b>
<BR><BR>
<p align=left style='text-indent:0; margin-top:10pt; margin-right:20pt; margin-bottom:0pt; margin-left:45pt;'> 
<b>�Ʒ��� ��й�ȣ�� Ȯ���� E���� �ּҸ� �Է���<BR> 
Ȯ���� �����ø� <u>��й�ȣ</u> �� �߼��ص帳�ϴ�.</b><BR>
</p>
<table border=0 align=center cellpadding='0' cellspacing='0' width=300>
<form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField()' action='$PHP_SELF?mode=pass_ok'>
<tr>
<td width=90 height=35 align=right valign=bottom>ȸ�����̵�:&nbsp;</td>
<td width=210  valign=bottom>
<input type='hidden' NAME=id value='$row[id]'><b><font color=green>$row[id]</font></b>
</td>
</tr>
<tr>
<td height=3 colspan=2 width=100%>
</td>
</tr>
<tr>
<td height=1 colspan=2 bgcolor='#5BB4D0' width=100%>
</td>
</tr>
<tr>
<td height=3 colspan=2 width=100%>
</td>
</tr>
<tr>
<td width=90 height=35 align=right  valign=top><p style='text-indent:0; margin-top:5pt;'> E�����ּ�:&nbsp;</p></td>
<td width=210  valign=top>
<input type='text' NAME=email size='30'>
<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td height=3></td></tr></table>
</td>
</tr>
<tr>
<td colspan=2 align=center>
<font color=green><b>$name</b> �Բ��� Ȯ�ΰ����� �����̿��� �մϴ�.</font>
</td>
</tr>
</table>

</td></tr>
<tr><td align=center width=395 height=8><img src='img/member_search_down.gif' width=395 height=8></td></tr>
</table>

</td></tr>

<tr><td  height=15 width=100%></td></tr>

<tr><td  height=50  valign=middle align=center bgcolor='#E0E7E0' width=100%>
<input type=\"image\" src='img/member_search_1.gif' width=72 height=23 border=0>
<a href=\"#\" onClick=\"javascript:window.close();\"><img src='img/member_search_2.gif' width=72 height=23 border=0></a>
</td></tr>

</form>
</table>
";
exit;
} else {

echo ("
		<script language=javascript>
		alert('\\n$name �� ���� ��û���ֽ� �̸��� �ֹε�Ϲ�ȣ�δ�\\n\\n�˻��Ǵ� ȸ�� ID �� �����ϴ�..   ----- $admin_name -----\\n\\n[ȸ�������� �̿��Ͽ� �����Ͻñ� �ٶ��ϴ�.]\\n');
		history.go(-1);
		</script>
		");
		exit;
	}

?>

<?php
}elseif(!strcmp($mode,"pass_ok")) {
?>

<?php
//�����κа� ���������� url���� ������ ���´�........
function ERROR($msg)
{
echo "<script language=javascript>
window.alert('$msg');
history.go(-1);
</script>
";
exit;
}

if ( !$id || !$email ) {
		$msg = "���������� ó������ ���Ͽ����ϴ�.\\n\\nó������ �ٽ� �̿����ּ���!!\\n\\n----- $admin_name -----"; ERROR($msg);  
}

{ 
  if (!eregi("^[^@ ]+@[a-zA-Z0-9\-\.]+\.+[a-zA-Z0-9\-\.]", $email)) { 
    	$msg = "\\n�����ּҰ� ����Ȯ�ϰų� �ùٸ� �Է��� �ƴմϴ�.\\n\\n�� �ۼ��� �ֽñ� �ٶ��ϴ�. --- $admin_name---\\n"; ERROR($msg); 
  } 
  /* �ѱ��� ���ԵǾ����� üũ */ 
  for($i = 1; $i <= strlen($email); $i++) { 
    if ((Ord(substr("$email", $i - 1, $i)) & 0x80)) { 
      	$msg = "\\n�����ּҿ� �ѱ��� ���Եɼ� �����ϴ�.\\n\\n�� �ۼ��� �ֽñ� �ٶ��ϴ�. --- $admin_name---\\n"; ERROR($msg); 
    } 
  } 

}

##���� �ּҸ� ������Ʈ ��Ų��.############################
$query ="UPDATE member SET id='$id', email='$email' WHERE id='$id'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1)
			</script>";
		exit;
	}

	echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=pass_mlang&id=$id&email=$email'>";
		exit

?>

<?php
}elseif(!strcmp($mode,"pass_mlang")) {
?>

<html>
<head>
<title><?=$admin_name?></title>
<STYLE>

p,br,body,td,input,select,submit {color:black; font-size:10pt; FONT-FAMILY:����;}
b {color:black; font-size:10pt; FONT-FAMILY:����;}

</STYLE>
<link rel="stylesheet" type="text/css" href="http://www.script.ne.kr/script.css">
</head>

<body bgcolor='#FFFFFF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=5 align=left bgcolor='#000000' width=100%></td></tr>
<tr><td  height=25 align=left bgcolor='#43B5C9' width=100%>
&nbsp;&nbsp;<b><font color=#FFFFFF>�� <?=$admin_name?>���� �߼���</font></b>
</td></tr>
<tr><td  height=80 align=center bgcolor='#FFFFFF' width=100%>
<BR><BR>
<img src='img/loding.gif'>
<BR><BR>
&nbsp;&nbsp;
<b>
<?php
echo"
$id ���� ��й�ȣ�� <BR>$email  �ּҷ� �߼��ϰ� �ֽ��ϴ�.<BR><BR>
<font color=red>*Ȯ�� ��ư�� ���õ��� ��â�� ���� ���ñ� �ٶ��ϴ�.*</font>
";
?>
</b>
</td></tr>
</table>

<iframe frameborder="0" height="0" width="0" topmargin="0"  leftmargin="0" marginheight="0" marginwidth="0" scrolling="no" src="<?echo"$PHP_SELF?mode=pass_mlang_ok&id=$id&email=$email";?>"></iframe>
</body>
</html>

<?php
}elseif(!strcmp($mode,"pass_mlang_ok")) {

$query ="select * from member where id='$id'";
$result = mysql_query($query,$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) 
include_once('../shop/mailer.lib.php');
##���Ϸ� ��й�ȣ�� �߼��Ѵ�.############################
$connent="$row[pass]";
$name = "$row[name]";
$TO_NAME="$id";
$TO_EMAIL="$email";
$FROM_NAME="$admin_name";
$FROM_EMAIL="$admin_email";
$SUBJECT="$admin_name  ���� �߼��� ȸ������ �����Դϴ�.";

$SUBJECT = stripslashes($SUBJECT);
$CONTENT = nl2br($CONTENT);

$SEND_CONTENT = "<HTML>

�������� <a href='$admin_url' rarget='_blank'>$admin_name</a> ���� �߼��� ȸ���������� �Դϴ�.
<BR><BR><BR>
$id ȸ������ ��й�ȣ�� $connent �Դϴ�.

</HTML>";

if($FROM_NAME && $FROM_EMAIL) {
	$from = "\"$FROM_NAME\" <$FROM_EMAIL>";
}
else {
	$from = "$FROM_EMAIL";
}
$TO = "\"$TO_NAME\" <$TO_EMAIL>";
$from = "From:$from\nContent-Type:text/html";

$content = $SEND_CONTENT;
$to = "\"$TO_NAME\" <$TO_EMAIL>";
$subject = "$TO_NAME �� ��й�ȣ ���� �ȳ����Դϴ�.";

// mailer($fname, $fmail, $to, $subject, $content, $type = 1, $file, $cc = "", $bcc = "");
mailer("$fname", "$fmail", "$to", "$subject", "$content", $type=1);

echo ("
		<script>
		alert('\\n$id ȸ������ ��й�ȣ��  $TO_EMAIL �� �߼� �Ǿ�����\\n\\nȮ���� �ֽñ� �ٶ��ϴ�.... ---- $admin_name ----\\n');
		window.top.close();
		</script>
		");
		exit;

	}
##�������� ȣ�����ش�.############################
    else
    {

echo ("
		<script>
		alert('\\n���������� ���������� ó���������Ͽ����ϴ�.\\n\\nó������ �ٽ� �̿�ٶ��ϴ�.. --- $admin_name ---\\n');
		history.go(-1);
		</script>
		");
		exit;

	}

mysql_close($db);

?>

<?php
} else {

echo "
			<script>
				window.alert(\"������ �̻��մϴ�.\\n\\n----- $admin_name  -----\")
				window.close();
			</script>";
	exit;
}
?>

</body>
</html>