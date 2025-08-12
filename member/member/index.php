<?php
$Color1="1466BA";
$Color2="4C90D6";
$Color3="BBD5F0";
$PageCode="member";
include"../db.php";
include"../top.php";
?><head>
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
alert("��� ��ȭ��ȣ�� �Է��� �ּ���. ");
return false;
}
if ((f.phone2.value.length < 3) || (f.phone2.value.length > 4)) {
alert("��ȭ��ȣ�� 3�����̻� 4�������� �̿��� �մϴ�.");
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
if ((f.phone3.value.length < 3) || (f.phone3.value.length > 4)) {
alert("��ȭ��ȣ ���ڸ��� 4���� ���� �̿��� �մϴ�.");
return false;
}
if (!TypeCheck(f.phone3.value, NUM)) {
alert("��ȭ��ȣ�� ���ڷθ� ����� �� �ֽ��ϴ�.");
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
$result= mysql_query("select * from member where name='$name'",$db);
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
�� ȸ�������� �Ͻ÷��� ������ ����/��ü���� ��ȭ��ȣ�� �Է����ּ���
<BR>
<table border=0  cellpadding='5' cellspacing='0' align=center>
<tr>
<td align=center style='background-color:#<?=$Color2?>; font-size:9pt; color:#FFFFFF; border-width:1; border-style:solid; border:1 solid #<?=$Color1?>;'>����/��ü��<b></b>&nbsp;
  <input type='text' name='name' size='15' <?=$LoginBoxStyle?>>
&nbsp;&nbsp;&nbsp;<b>��ȭ��ȣ</b>&nbsp;
<input type='text' name='phone1' size='7' maxLength='3' <?=$LoginBoxStyle?>>-
<input type='text' name='phone2' size='7' maxLength='4' <?=$LoginBoxStyle?>>-
<input type='text' name='phone3' size='7' maxLength='4' <?=$LoginBoxStyle?>>		
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