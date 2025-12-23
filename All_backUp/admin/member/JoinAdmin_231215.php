<!-- <?php // if($mode=="modify"){

// 	$fp = fopen("./JoinAdminInfo.php", "w+");
// 	fwrite($fp, "<?\n");
// 	fwrite($fp, "\$AdminName = \"$name\";\n");
// 	fwrite($fp, "\$AdminMail = \"$email\";\n");
// 	fwrite($fp, "\$MailTitle = \"$title\";\n");
// 	fwrite($fp, "\$MailStyle = \"$style\";\n");
// 	fwrite($fp, "\$MailCont = \"$cont\";\n");
// 	fwrite($fp, "?>");
// 	fclose($fp);
// echo "$fP";
// exit; -->


<?php
if ($mode == "modify") {
    $file = 'JoinAdminInfo.php';
// echo "$file";
// exit;
    // Open the file with 'w+' mode, which creates the file if it doesn't exist and truncates it to zero length otherwise
    $fp = fopen($file, "w+");
    
    // Check if the file was opened successfully
    if ($fp === false) {
        die("Unable to open or create file ($file)");
    }

    // Write the content to the file
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$AdminName = \"$name\";\n");
    fwrite($fp, "\$AdminMail = \"$email\";\n");
    fwrite($fp, "\$MailTitle = \"$title\";\n");
    fwrite($fp, "\$MailStyle = \"$style\";\n");
    fwrite($fp, "\$MailCont = \"$cont\";\n");
    fwrite($fp, "?>");

    // Close the file handle
    fclose($fp);

	echo ("<script language=javascript>
	window.alert('정보를 정상적으로 수정하였습니다..');
	</script>
	<meta http-equiv='Refresh' content='0; URL=$PHP_SELF'>
	");
	exit;
	 // echo "File ($file) has been modified successfully.";
} else {
    echo "Invalid mode specified.";
}
?>





 <!-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<?php

$M123="..";
include"../top.php"; 

include"./JoinAdminInfo.php";
?><head>
<script language=javascript>

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

function MemberMailoCheckField()
{
var f=document.MemberMailoInfo;

if (f.name.value == "") {
alert("보내는 회사이름을 입력하여주세요?");
return false;
}

if (f.email.value == "") {
alert("E 메일 주소를 입력해 주시기 바랍니다.");
f.email.focus();
return false;
}
if(f.email.value.lastIndexOf(" ") > -1){
alert("E 메일 주소에는 공백이 올수 없습니다.")
f.email.focus();
return false
}
if(f.email.value.lastIndexOf(".")==-1){
alert("E 메일 주소를 정상적으로 입력해 주시기 바랍니다.")
f.email.focus();
return false
}
if(f.email.value.lastIndexOf("@")==-1){
alert("E 메일 주소를 정상적으로 입력해 주시기 바랍니다.")
f.email.focus();
return false
}

if (f.title.value == "") {
alert("메일의 제목을 입력하여주세요?");
return false;
}

if (f.cont.value.length < 20 ) {
alert("메일의 내용을 입력하지 않았거나 너무 짧습니다.");
return false;
}

}
</script>
</head>




<BR>
<p align=center>
- 아래의 정보는 회원가입후 가입환영메세지를 가입회원의 메일로 자동발송하는 내용입니다. -<BR>
정보를 수정하시면 수정된 정보로 가입회원에게 보여집니다.
</p>


<table border=0 align=center width=600 cellpadding='5' cellspacing='1' class='coolBar'>
<form name='MemberMailoInfo' method='post' OnSubmit='javascript:return MemberMailoCheckField()' action='<?=$PHP_SELF?>'>
<input type='hidden' name='mode' value='modify'>
<tr><td colspan=10 width=100% height=10>&nbsp;</td></tr>

<tr>
<td width=150 align=right>보내는 회사이름</td>
<td width=450><input type='text' name='name' size='30' value='<?=$AdminName?>' maxLength='50'></td>
</tr>

<tr>
<td width=150 align=right>보내는 메일주소</td>
<td width=450><input type='text' name='email' size='60' value='<?=$AdminMail?>' maxLength='200'></td>
</tr>

<tr>
<td width=150 align=right>메일의 제목</td>
<td width=450><input type='text' name='title' size='60' value='<?=$MailTitle?>' maxLength='50'></td>
</tr>

<tr>
<td width=150 align=right>내용의 문서형식</td>
<td width=450>
<?if($MailStyle=="html"){?>
<INPUT TYPE="radio" name="style" value="br"> 자동 BR(건너뛰기)
<INPUT TYPE="radio" name="style" value="html" checked> HTML허용
<?}else{?>
<INPUT TYPE="radio" name="style" value="br" checked> 자동 BR(건너뛰기)
<INPUT TYPE="radio" name="style" value="html"> HTML허용
<?}?>
</td>
</tr>

<tr>
<td width=150 align=right>메일의 내용</td>
<td width=450>
<TEXTAREA NAME="cont" ROWS="10" COLS="50"><?=$MailCont?></TEXTAREA>
</td>
</tr>

<tr><td colspan=10 width=100% height=10>&nbsp;</td></tr>
</table>

<p align=center>
<input type='submit' value=' 수정 합니다.'>
</p>
</form>
<BR>

<?php include"../down.php"; 
?>