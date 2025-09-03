<?php
////////////////// ������ �α��� ////////////////////
include"../../../db.php";
include"../../config.php";
////////////////////////////////////////////////////
?>

<?php
if($mode=="SoForm"){ ///////////////////////////////////////////////////////////////////////////////////////
include"../../title.php";
$Bgcolor1="408080";
if($no){include"view_admin.php";}else{
	echo ("
		<script language=javascript>
		alert('�ŷ��������� �Է��Ϸ��� ����ó, ��������� ���NO ������ �ʿ��ѵ�\\n\\n�� �ڷᰡ �����ϴ�. â�� �ٽ� ������');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;
}
?>

<?include"SoList.php";?>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="SoForm_ok"){

	$result = mysql_query("SELECT max(no) FROM MlangWebOffice_Biz_particulars");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################

$dbinsert ="insert into MlangWebOffice_Biz_particulars values('$new_no',
'$admin_no',
'$biz_date',
'$kinds',
'$fitting_no',
'$engineer_name',
'$money',
'$remark'
)";
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('�ڷ� ���� OK');
		</script>
	<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=SoForm&no=$admin_no'>
	");
		exit;


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../../title.php";
$Bgcolor1="408080";

if($code=="modify"){include"view_admin.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
self.moveTo(0,0)
<?php if ($code=="modify"){?>
self.resizeTo(availWidth=780,screen.availHeight)	
<?}else{?>
self.resizeTo(availWidth=630,availHeight=190)
<?php } ?> 

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

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.biz_name.value == "") {
alert("����ó �� �Է��Ͽ��ּ���!!");
f.biz_name.focus();
return false;
}

if (f.a_name.value == "") {
alert("����� �� �Է��Ͽ��ּ���!!");
f.a_name.focus();
return false;
}

if (f.b_name.value == "") {
alert("����� �� �Է��Ͽ��ּ���!!");
f.b_name.focus();
return false;
}

if ((f.tel_1.value.length < 2) || (f.tel_1.value.length > 4)) {
alert("����ó �� ���ڸ��� 2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.tel_1.focus();
return false;
}
if (!TypeCheck(f.tel_1.value, NUM)) {
alert("����ó �� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.tel_1.focus();
return false;
}
if ((f.tel_2.value.length < 3) || (f.tel_2.value.length > 4)) {
alert("����ó �� �߰��ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.tel_2.focus();
return false;
}
if (!TypeCheck(f.tel_2.value, NUM)) {
alert("����ó �� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.tel_2.focus();
return false;
}
if ((f.tel_3.value.length < 3) || (f.tel_3.value.length > 4)) {
alert("����ó �� ���ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.tel_3.focus();
return false;
}
if (!TypeCheck(f.tel_3.value, NUM)) {
alert("����ó �� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.tel_3.focus();
return false;
}

}
</script>
<script src="../../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=0 cellspacing=5>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?php if ($code=="modify"){?>modify_ok<?}else{?>form_ok<?php } ?>'>
<?php if ($code=="modify"){?><INPUT TYPE="hidden" name='no' value='<?php echo $no?>'><?php } ?>

<tr>
<td class='coolBar' colspan=3 height=25>
<b>&nbsp;&nbsp;�ŷ������� <?php if ($code=="modify"){?>����<?}else{?>�Է�<?php } ?></b><BR>
</td>
<td align=right><font color='#336666'>���NO: <b><?php echo g><?=$no?></big></b>&nbsp;&nbsp;</font></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����ó&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="biz_name" size=25 maxLength='80' value='<?php if ($code=="modify"){echo("$Viewbiz_name");}?>'></td>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�����&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="a_name" size=25 maxLength='20' value='<?php if ($code=="modify"){echo("$Viewa_name");}?>'></td>
</tr>


<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�����&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="b_name" size=25 maxLength='20' value='<?php if ($code=="modify"){echo("$Viewb_name");}?>'></td>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����TEL&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="tel_1" size=7 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewtel_1");}?>'>
-
<INPUT TYPE="text" NAME="tel_2" size=7 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewtel_2");}?>'>
-
<INPUT TYPE="text" NAME="tel_3" size=7 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewtel_3");}?>'>
</td>
</tr>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' <?php if ($code=="modify"){?>����<?}else{?>�Է�<?php } ?> �մϴ�.'>
<BR><BR>
</td>
</tr>
</form>
</table>

<?php if ($code=="modify"){ include"SoList.php"; }?> 

<?php
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

	$result = mysql_query("SELECT max(no) FROM MlangWebOffice_Biz_particulars_admin");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################


$dbinsert ="insert into MlangWebOffice_Biz_particulars_admin values('$new_no',
'$id',
'$biz_name',  
'$a_name',
'$b_name',
'$tel_1',   
'$tel_2',
'$tel_3'
)";
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('\\n�ڷḦ ���������� ���� �Ͽ����ϴ�.\\n\\n�ŷ����� ������ �Է��� �������� �ٷ� �̵��ϰڽ��ϴ�.\\n');
        opener.parent.location.reload();
		</script>
	<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=SoForm&no=$new_no'>
	");
		exit;


} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="SoDelete"){

mysql_query("DELETE FROM MlangWebOffice_Biz_particulars WHERE no='$no'");
mysql_close();

echo ("
<html>
<script language=javascript>
window.alert('���������� �ڷ��� ���� �Ͽ����ϴ�.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="BigDelete"){

mysql_query("DELETE FROM MlangWebOffice_Biz_particulars_admin WHERE no='$no'");
/////////////////////////////////////
$result_SO= mysql_query("select * from MlangWebOffice_Biz_particulars where admin_no='$no'",$db);
$rows_SO=mysql_num_rows($result_SO);
if($rows_SO){
while($row_SO= mysql_fetch_array($result_SO)) 
{ mysql_query("DELETE FROM MlangWebOffice_Biz_particulars WHERE no='$row_SO[no]'"); }
}
/////////////////////////////////////
mysql_close();

echo ("
<html>
<script language=javascript>
window.alert('���������� ����ó ������ �ŷ������� �ڷ� $no�� �� ���� ó�� �Ͽ����ϴ�.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="SoModifypart99"){

$query ="UPDATE MlangWebOffice_Biz_particulars SET 
biz_date='$biz_date',
kinds='$kinds',
fitting_no='$fitting_no',
engineer_name='$engineer_name',
money='$money',
remark='$remark'
WHERE no='$SoTyuno'";
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
		alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
		</script>");

if($offset){$INH="&offset=$offset";}

if($TDsearch){
	echo("<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=SoForm&no=$Big_no&TDsearch=$TDsearch&TDsearchValue=$TDsearchValue$INH'>");
}else{
	echo("<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=SoForm&no=$Big_no$INH'>");
}

		exit;

}
mysql_close($db);


} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modify_ok"){

$query ="UPDATE MlangWebOffice_Biz_particulars_admin SET 
id='$id',
biz_name='$biz_name',
a_name='$a_name',
b_name='$b_name',
tel_1='$tel_1',
tel_2='$tel_2',
tel_3='$tel_3'
WHERE no='$no'";
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
		alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
		opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;

}
mysql_close($db);


}
?>