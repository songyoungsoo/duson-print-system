<?php
////////////////// ������ �α��� ////////////////////
include"../../../db.php";
include"../../config.php";
////////////////////////////////////////////////////
?>

<?php
if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../../title.php";
$Bgcolor1="408080";

if($code=="modify"){include"CateView.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
window.moveTo(screen.width/5, screen.height/5); 

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.title.value == "") {
alert("ȸ��� �� �Է��Ͽ��ּ���!!");
f.title.focus();
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
<td class='coolBar' colspan=4 height=25>
<b>&nbsp;&nbsp;�ֿ����ȸ�� <?php if ($code=="modify"){?>����<?}else{?>�Է�<?php } ?></b><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����&nbsp;&nbsp;</td>
<td colspan=3>
<?$YMode="input"; include"Year.php";?>
<?php if ($View_newy=="yes"){?>
<INPUT TYPE="radio" NAME="newy" value='no'>NO
<INPUT TYPE="radio" NAME="newy" value='yes' checked>YES
<?}else{?>
<INPUT TYPE="radio" NAME="newy" value='no' checked>NO
<INPUT TYPE="radio" NAME="newy" value='yes'>YES
<?php } ?>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>ȸ���&nbsp;&nbsp;</td>
<td colspan=3><INPUT TYPE="text" NAME="title" size=50 maxLength='80' value='<?php if ($code=="modify"){echo("$View_title");}?>'></td>
</tr>

<tr>
<td colspan=4 align=center>
<input type='submit' value=' <?php if ($code=="modify"){?>����<?}else{?>����<?php } ?> �մϴ�.'>
</td>
</tr>

</table>

<? } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

mysql_query("DELETE FROM MlangHomePage_Customer WHERE no='$no'");
mysql_close();

echo ("
<html>
<script language=javascript>
window.alert('$no�� �ڷ��� ���� ó�� �Ͽ����ϴ�.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="form_ok"){

$dbinsert ="insert into MlangHomePage_Customer values('',
'$Y8y_year',
'$title',
'$newy'
)";
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('\\n�ڷḦ ���������� ���� �Ͽ����ϴ�.\\n');
        opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form'>
	");
		exit;


} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php
if($mode=="modify_ok"){

$query ="UPDATE MlangHomePage_Customer SET 
BigNo='$Y8y_year',  
title='$title',
newy='$newy'
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
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no'>
	");
		exit;

}
mysql_close($db);


}
?>