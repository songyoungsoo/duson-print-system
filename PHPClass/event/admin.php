<?php
include "../../db.php";
include "../../admin/config.php";

if($mode=="formOk"){

	$fp = fopen("config.php", "w");
	fwrite($fp, "<?\n");
	fwrite($fp, "\$title=\"$Mtitle\";\n");
	fwrite($fp, "\$pop=\"$Mpop\";\n");
	fwrite($fp, "\$TopLeft=\"$MTopLeft\";\n");
	fwrite($fp, "\$TopTop=\"$MTopTop\";\n");
	fwrite($fp, "\$popWidth=\"$MpopWidth\";\n");
	fwrite($fp, "\$popheight=\"$Mpopheight\";\n");
	fwrite($fp, "\$style=\"$Mstyle\";\n");
	fwrite($fp, "\$txt=\"$Mtxt\";\n");
	fwrite($fp, "\$Copyright=\"$MCopyright\";\n");
	fwrite($fp, "?>");
	fclose($fp);

echo ("<script language=javascript>
window.alert('수정 완료....*^^*');
</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF'>
");
exit;

}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include "config.php";
?>

<html>
<head>
<title>홈페이지팝업창프로그램</title>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=680,availHeight=680);
</script>

<meta http-equiv='Content-type' content='text/html; charset=UTF-8'>

<script language=javascript>
var NUM = "0123456789"; 
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
function EvevtCheckField()
{
var f=document.EvevtInfo;

if (f.Mtitle.value == "") {
alert("타을틀바 제목 을 입력하여주세요?");
f.Mtitle.focus();
return false;
}

if (f.MpopWidth.value == "") {
alert("WIDTH 을 입력하여주세요?");
f.MpopWidth.focus();
return false;
}
if (f.Mpopheight.value == "") {
alert("HEIGHT 을 입력하여주세요?");
f.Mpopheight.focus();
return false;
}

if (f.Mtxt.value.length < 30 ) {
alert("수정할 내용을 입력하지 않았거나 너무 짧습니다.");
f.Mtxt.focus();
return false;
}

}
</script>

<style>
body,td,input,select,submit {color:black; font-size:9pt;}
</style>

</head>


<?php
$CONTENT = preg_replace("\\\\", "", $txt);
?>


<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 align=center width=100% cellpadding='5' cellspacing='0' bgcolor='#408080'>
<form name='EvevtInfo' method='post' OnSubmit='javascript:return EvevtCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='formOk'>
<INPUT TYPE="hidden" name='MCopyright' value='<?php echo $Copyright?>'>

<tr>
<td align=center><font style='font:bold; color:#FFFFFF;'>타을틀바 제목</font><td>
<td valign=top bgcolor='#FFFFFF'>
<input type='text' name='Mtitle' value='<?php echo $title?>' size='75'>
</td>
</tr>
<tr><td colspan=2 bgcolor='#FFFFFF' height=1 width=100%></td></tr>

<tr>
<td align=center><font style='font:bold; color:#FFFFFF;'>팝업여부</font><td>
<td valign=top bgcolor='#FFFFFF'>

<SELECT NAME="Mpop">
<option value='Div2' <?php if($pop=="Div2"){echo("selected style='background-color:#429EB2; color:#FFFFFF;'");}?>>DHTML창-떨어지게</option>
<option value='Div1' <?php if($pop=="Div1"){echo("selected style='background-color:#429EB2; color:#FFFFFF;'");}?>>DHTML창-스무스하게</option>
<option value='1' <?php if($pop=="1"){echo("selected style='background-color:#429EB2; color:#FFFFFF;'");}?>>윈도우팝창-계속호출</option>
<option value='2' <?php if($pop=="2"){echo("selected style='background-color:#429EB2; color:#FFFFFF;'");}?>>윈도우팝창-하루에한번만</option>
<option value='no' <?php if($pop=="no"){echo("selected style='background-color:#429EB2; color:#FFFFFF;'");}?>>호출안함</option>
</SELECT>

</td>
</tr>
<tr><td colspan=2 bgcolor='#FFFFFF' height=1 width=100%></td></tr>

<tr>
<td align=center><font style='font:bold; color:#FFFFFF;'>팝업창의크기</font><td>
<td valign=top bgcolor='#FFFFFF'>
WIDTH&nbsp;<input type='text' name='MpopWidth' value='<?php echo $popWidth?>' size='15'>
HEIGHT&nbsp;<input type='text' name='Mpopheight' value='<?php echo $popheight?>' size='15'>
</td>
</tr>
<tr><td colspan=2 bgcolor='#FFFFFF' height=1 width=100%></td></tr>

<tr>
<td align=center><font style='font:bold; color:#FFFFFF;'>창이 뜰위치</font><td>
<td valign=top bgcolor='#FFFFFF'>
LEFT&nbsp;<input type='text' name='MTopLeft' value='<?php echo $TopLeft?>' size='15'>
TOP&nbsp;<input type='text' name='MTopTop' value='<?php echo $TopTop?>' size='15'>
</td>
</tr>
<tr><td colspan=2 bgcolor='#FFFFFF' height=1 width=100%></td></tr>


<tr>
<td align=center><font style='font:bold; color:#FFFFFF;'>문서형식</font><td>
<td valign=top bgcolor='#FFFFFF'>
<INPUT TYPE="radio" NAME="Mstyle" value='br' <?php if($style=="br"){echo("checked");}?>>자동BR
<INPUT TYPE="radio" NAME="Mstyle" value='html' <?php if($style=="html"){echo("checked");}?>>HTML직접입력
</td>
</tr>
<tr><td colspan=2 bgcolor='#FFFFFF' height=1 width=100%></td></tr>

<tr>
<td align=center><font style='font:bold; color:#FFFFFF;'>내용</font><td>
<td valign=top bgcolor='#FFFFFF'>
<TEXTAREA NAME="Mtxt" ROWS="25" COLS="70"><?php echo $CONTENT?></TEXTAREA>
</td>
</tr>

</table>


<p align=center>
<input type='submit' value=' 수정 합니다.'>
</p>
</form>

<?php echo $WebSoftCopyright?>

</body>

</html>
?>