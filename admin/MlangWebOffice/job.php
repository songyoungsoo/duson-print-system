<?php
include"../../db.php";
include"../config.php";
include"../title.php";

if($mode=="modify"){

$result= mysql_query("select * from MlangWebOffice_job_Admin where no='$no'",$db);
$row= mysql_fetch_array($result);
if($row){
$JobAdminView_recnum=htmlspecialchars($row[recnum]);  // �ڷ�ȣ���
$JobAdminView_lnum=htmlspecialchars($row[lnum]);   // �ڷ��ϼ�
$JobAdminView_New_Article=htmlspecialchars($row[New_Article]);  // ���� ǥ��
$JobAdminView_cutlen=htmlspecialchars($row[cutlen]);  // ���� ���Ѽ�
}else{
echo ("<script language=javascript>
window.alert('DataBase ���� �Դϴ�.');
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
alert("�ڷ�ȣ��� �� �Է��Ͽ��ּ���...?");
f.recnum.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("�ڷ�ȣ��� �� ���ڷθ� �����ž� �մϴ�.");
f.email.focus();
return false;
}

if (f.lnum.value == "") {
alert("�ڷ��ϼ� �� �Է��Ͽ��ּ���...?");
f.lnum.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("�ڷ��ϼ� �� ���ڷθ� �����ž� �մϴ�.");
f.email.focus();
return false;
}

if (f.New_Article.value == "") {
alert("���� ǥ�ñⰣ �� �Է��Ͽ��ּ���...?");
f.email.focus();
return false;
}
if (!TypeCheck(f.New_Article.value, NUM)) {
alert("���� ǥ�ñⰣ �� ���ڷθ� �����ž� �մϴ�.");
f.email.focus();
return false;
}

if (f.title.value == "") {
alert("���� ���Ѽ� �� �Է��Ͽ��ּ���...?");
f.email.focus();
return false;
}
if (!TypeCheck(f.cutlen.value, NUM)) {
alert("���� ���Ѽ� �� ���ڷθ� �����ž� �մϴ�.");
f.cutlen.focus();
return false;
}

}

</script>
</head>

<body class='coolBar' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<p><BR>
* ���������� ����/������ ȯ�� ������ ���� �ϴ� �� �Դϴ�.
</p>

<table border=0 align=center width=100% cellpadding=5 cellspacing=1>

<form name='JobAdmin' method='post' OnSubmit='javascript:return JobAdminCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='AdminOk'>
<INPUT TYPE="hidden" name='no' value='<?php echo $no?>'>

<tr>
<td width=100 class='coolBar' align='right'>
<font style='color:#000000;'>�ڷ�ȣ���&nbsp;</font>
</td>
<td bgcolor='#575757'>
<INPUT TYPE="text" NAME="recnum" value='<?php echo $JobAdminView_recnum?>' size=7>
</td>
<td width=100 class='coolBar' align='right'>
<font style='color:#000000;'>�ڷ��ϼ�&nbsp;</font>
</td>
<td bgcolor='#575757'>
<INPUT TYPE="text" NAME="lnum" value='<?php echo $JobAdminView_lnum?>' size=7>
</td>
</tr>

<tr>
<td class='coolBar' align='right'>
<font style='color:#000000;'>���� ǥ�ñⰣ&nbsp;</font>
</td>
<td bgcolor='#575757'>
<INPUT TYPE="text" NAME="New_Article" value='<?php echo $JobAdminView_New_Article?>' size=7>
</td>
<td class='coolBar' align='right'>
<font style='color:#000000;'>���� ���Ѽ�&nbsp;</font>
</td>
<td bgcolor='#575757'>
<INPUT TYPE="text" NAME="cutlen" value='<?php echo $JobAdminView_cutlen?>' size=7>
</td>
</tr>
	
</table>

<p align=center>
<input type='submit' value=' ���� �մϴ�. '>
</p>

</form>

</body>
</html>

<?php
}

if($mode=="AdminOk"){

$query ="UPDATE MlangWebOffice_job_Admin SET recnum='$recnum', lnum='$lnum', New_Article='$New_Article', cutlen='$cutlen' WHERE no='$no'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n������ ���������� ���� �Ͽ����ϴ�.');
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=modify&no=$no'>
	");
		exit;

}

mysql_close($db);


}
?>