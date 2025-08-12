<?php
$Color1="1466BA";
$Color2="4C90D6";
$Color3="BBD5F0";
$PageCode="member";
include"../db.php";
include"../top.php";
?>

<head>
<SCRIPT language=JavaScript>
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

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

function checkCheckBox(f){

var f=document.MlangUserInfo;

if (f.name.value == "") {
alert("������ �Է��� �ּ���. ");
return false;
}

if (f.jumin1.value == "") {
alert("�ֹε�Ϲ�ȣ ���ڸ��� �Է��� �ּ���. ");
return false;
}
if ((f.jumin1.value.length < 6) || (f.jumin1.value.length > 6)) {
alert("�ֹε�Ϲ�ȣ ���ڸ��� 6���� ���� �̿��� �մϴ�.");
return false;
}
if (!TypeCheck(f.jumin1.value, NUM)) {
alert("�ֹε�Ϲ�ȣ ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
return false;
}

if (f.jumin2.value == "") {
alert("�ֹε�Ϲ�ȣ ���ڸ��� �Է��� �ּ���. ");
return false;
}
if ((f.jumin2.value.length < 7) || (f.jumin2.value.length > 7)) {
alert("�ֹε�Ϲ�ȣ ���ڸ��� 7���� ���� �̿��� �մϴ�.");
return false;
}
if (!TypeCheck(f.jumin2.value, NUM)) {
alert("�ֹε�Ϲ�ȣ ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
return false;
}

var i;
chk = 0;
for (i=0; i<6; i++) {
chk += ( (i+2) * parseInt( f.jumin1.value.substring( i, i+1) ));
}

for (i=6; i<12; i++) {
chk += ( (i%8+2) * parseInt( f.jumin2.value.substring( i-6, i-5) ));             
}

chk = 11 - (chk%11);
chk %= 10;

if (chk != parseInt( f.jumin2.value.substring(6,7))) {
alert ("��Ȯ���� ���� �ֹε�� ��ȣ�Դϴ�.");
return false;
}       

if ((f.jumin1.value.length < 6) || (f.jumin2.value.length < 7)) {
alert("�Է��Ͻ� �ֹε�� ��ȣ�� ��Ȯ���� �ʽ��ϴ�. ");
return false;
}

if (f.agree.checked == false )
{
alert('���Ǹ� üũ�� Ȯ���� �����ֽñ� �ٶ��ϴ�.');
return false;
}else{
return true;
}

}
</SCRIPT>
</head>

<BR>


<table border=0 width=90% align=center cellpadding='0' cellspacing='0'>
<form name='MlangUserInfo' action="join.php" method="post" onsubmit="return checkCheckBox(this)">
<INPUT TYPE="hidden" name='PageCode' value='ok'>
<tr><td width=100% valign=top align=left>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<font style='font-size:11pt; color:<?=$Color2?>;'>�� �¶���ȸ�����</font>
</td></tr>
<tr><td valign=top align=center>
<TEXTAREA ROWS="17" COLS="80" style='font-size:9pt; background-color:#<?=$Color3?>; color:#<?=$Color1?>; border-width:1; border-style:solid; border:1 solid #<?=$Color2?>;'>
<?php
$result= mysql_query("select * from page where no='3'",$db);
$row= mysql_fetch_array($result);
?>
<?=htmlspecialchars($row[connent]);?>
<?php
mysql_close($db); 
?>
</TEXTAREA>
<BR>
<input type="checkbox" value="0" name="agree">���� ����� ���ǿ� ���� �Ͻʴϱ�!!
<BR><BR>

</td></tr>
<tr><td align=center>
<BR>
�� ȸ�������� �Ͻ÷��� ������ ������ �ֹε�Ϲ�ȣ�� �Է����ּ���
<BR>
<table border=0  cellpadding='5' cellspacing='0' align=center>
<tr>
<td align=center style='background-color:#<?=$Color2?>; font-size:9pt; color:#FFFFFF; border-width:1; border-style:solid; border:1 solid #<?=$Color1?>;'>&nbsp;&nbsp;&nbsp;<b>����</b>&nbsp;<input type='text' name='name' size='15' <?=$LoginBoxStyle?>>
&nbsp;&nbsp;&nbsp;<b>�ֹε�Ϲ�ȣ</b>&nbsp;
<input type='text' name='jumin1' size='10' maxLength='6' <?=$LoginBoxStyle?>>
��
<input type='text' name='jumin2' size='10' maxLength='7' <?=$LoginBoxStyle?>>
&nbsp;&nbsp;&nbsp;
</td>
</tr>
<tr>
<td align=center>
<input type="submit" value=" Ȯ �� ">
<input type="button" value="���ư���" onclick="history.go(-1);">
</td>
</tr>
</table>

</td></tr>
</table>

<BR><BR>

<?include"../down.php";?>