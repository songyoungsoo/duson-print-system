<?
include"../../db.php";
include"../config.php";
include"../title.php";

if($mode=="modify"){

$result= mysql_query("select * from MlangWebOffice_job_Admin where no='$no'",$db);
$row= mysql_fetch_array($result);
if($row){
$JobAdminView_recnum=htmlspecialchars($row[recnum]);  // 자료호출수
$JobAdminView_lnum=htmlspecialchars($row[lnum]);   // 자료목록수
$JobAdminView_New_Article=htmlspecialchars($row[New_Article]);  // 새글 표시
$JobAdminView_cutlen=htmlspecialchars($row[cutlen]);  // 글자 제한수
}else{
echo ("<script language=javascript>
window.alert('DataBase 에러 입니다.');
window.self.close();
</script>
");
exit;
}
mysql_close($db); 
?>

<head>
<script src="../js/coolbar.js" type="text/javascript"></script>

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

function JobAdminCheckField()
{
var f=document.JobAdmin;

if (f.recnum.value == "") {
alert("자료호출수 을 입력하여주세요...?");
f.recnum.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("자료호출수 은 숫자로만 적으셔야 합니다.");
f.email.focus();
return false;
}

if (f.lnum.value == "") {
alert("자료목록수 을 입력하여주세요...?");
f.lnum.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("자료목록수 은 숫자로만 적으셔야 합니다.");
f.email.focus();
return false;
}

if (f.New_Article.value == "") {
alert("새글 표시기간 을 입력하여주세요...?");
f.email.focus();
return false;
}
if (!TypeCheck(f.New_Article.value, NUM)) {
alert("새글 표시기간 은 숫자로만 적으셔야 합니다.");
f.email.focus();
return false;
}

if (f.title.value == "") {
alert("글자 제한수 을 입력하여주세요...?");
f.email.focus();
return false;
}
if (!TypeCheck(f.cutlen.value, NUM)) {
alert("글자 제한수 은 숫자로만 적으셔야 합니다.");
f.cutlen.focus();
return false;
}

}

</script>
</head>

<body class='coolBar' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<p><BR>
* 현페이지는 구인/구직의 환경 설정을 설정 하는 곳 입니다.
</p>

<table border=0 align=center width=100% cellpadding=5 cellspacing=1>

<form name='JobAdmin' method='post' OnSubmit='javascript:return JobAdminCheckField()' action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='AdminOk'>
<INPUT TYPE="hidden" name='no' value='<?=$no?>'>

<tr>
<td width=100 class='coolBar' align='right'>
<font style='color:#000000;'>자료호출수&nbsp;</font>
</td>
<td bgcolor='#575757'>
<INPUT TYPE="text" NAME="recnum" value='<?=$JobAdminView_recnum?>' size=7>
</td>
<td width=100 class='coolBar' align='right'>
<font style='color:#000000;'>자료목록수&nbsp;</font>
</td>
<td bgcolor='#575757'>
<INPUT TYPE="text" NAME="lnum" value='<?=$JobAdminView_lnum?>' size=7>
</td>
</tr>

<tr>
<td class='coolBar' align='right'>
<font style='color:#000000;'>새글 표시기간&nbsp;</font>
</td>
<td bgcolor='#575757'>
<INPUT TYPE="text" NAME="New_Article" value='<?=$JobAdminView_New_Article?>' size=7>
</td>
<td class='coolBar' align='right'>
<font style='color:#000000;'>글자 제한수&nbsp;</font>
</td>
<td bgcolor='#575757'>
<INPUT TYPE="text" NAME="cutlen" value='<?=$JobAdminView_cutlen?>' size=7>
</td>
</tr>
	
</table>

<p align=center>
<input type='submit' value=' 변경 합니다. '>
</p>

</form>

</body>
</html>

<?
}

if($mode=="AdminOk"){

$query ="UPDATE MlangWebOffice_job_Admin SET recnum='$recnum', lnum='$lnum', New_Article='$New_Article', cutlen='$cutlen' WHERE no='$no'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 변경 하였습니다.');
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=modify&no=$no'>
	");
		exit;

}

mysql_close($db);


}
?>