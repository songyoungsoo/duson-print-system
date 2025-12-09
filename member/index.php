<?php
$Color1="1466BA";
$Color2="4C90D6";
$Color3="BBD5F0";
$PageCode="member";
include"../db.php";
include"../includes/auth.php";
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
alert("성명를 입력해 주세요. ");
return false;
}

if (f.phone1.value == "") {
alert("전화번호 앞자리를 입력해 주세요. ");
return false;
}
if ((f.phone1.value.length < 2) || (f.phone1.value.length > 4)) {
alert("전화번호 앞자리는2자리 이상 4자리 이하를 입력하셔야 합니다.");
return false;
}
if (!TypeCheck(f.phone1.value, NUM)) {
alert("전화번호 앞자리는 숫자로만 사용할 수 있습니다.");
return false;
}

if (f.phone2.value == "") {
alert("가운데 전화번호를 입력해 주세요. ");
return false;
}
if ((f.phone2.value.length < 3) || (f.phone2.value.length > 4)) {
alert("전화번호는 3글자이상 4글자이하 이여야 합니다.");
return false;
}
if (!TypeCheck(f.phone2.value, NUM)) {
alert("전화번호는 숫자로만 사용할 수 있습니다.");
return false;
}

if (f.phone3.value == "") {
alert("전화번호 뒤자리를 입력해 주세요. ");
return false;
}
if ((f.phone3.value.length < 3) || (f.phone3.value.length > 4)) {
alert("전화번호 뒤자리는 4글자 이하 이여야 합니다.");
return false;
}
if (!TypeCheck(f.phone3.value, NUM)) {
alert("전화번호는 숫자로만 사용할 수 있습니다.");
return false;
}	


if (f.agree.checked == false )
{
alert('동의를 체크후 확인을 눌러주시기 바랍니다.');
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
<font style='font-size:11pt; color:<?=$Color2?>;'>※ 온라인회원약관</font>
</td></tr>
<tr><td valign=top align=center>
<TEXTAREA ROWS="17" COLS="80" style='font-size:9pt; background-color:#<?=$Color3?>; color:#<?=$Color1?>; border-width:1; border-style:solid; border:1 solid #<?=$Color2?>;'>
<?php
$name = $_POST['name'];
$result = mysqli_query($db, "SELECT * FROM member WHERE name='$name'");
$row = mysqli_fetch_array($result);
?>
<?=htmlspecialchars($row['connent']);?>
<?php
mysqli_close($db); 
?>
</TEXTAREA>
<BR>
<input type="checkbox" value="0" name="agree">위의 약관에 동의에 동의 하십니까!!
<BR><BR>

</td></tr>
<tr><td align=center>
<BR>
※ 회원가입을 하시려면 본인의 성명/업체명과 전화번호를 입력해주세요
<BR>
<table border=0  cellpadding='5' cellspacing='0' align=center>
<tr>
<td align=center style='background-color:#<?=$Color2?>; font-size:9pt; color:#FFFFFF; border-width:1; border-style:solid; border:1 solid #<?=$Color1?>;'>성명/업체명<b></b>&nbsp;
  <input type='text' name='name' size='15' <?=$LoginBoxStyle?>>
&nbsp;&nbsp;&nbsp;<b>전화번호</b>&nbsp;
<input type='text' name='phone1' size='7' maxLength='3' <?=$LoginBoxStyle?>>-
<input type='text' name='phone2' size='7' maxLength='4' <?=$LoginBoxStyle?>>-
<input type='text' name='phone3' size='7' maxLength='4' <?=$LoginBoxStyle?>>		
&nbsp;&nbsp;&nbsp;
</td>
</tr>
<tr>
<td align=center>
<input type="submit" value=" 확 인 ">
<input type="button" value="돌아가기" onclick="history.go(-1);">
</td>
</tr>
</table>

</td></tr>
</table>

<BR><BR>

<?include"../down.php";?>