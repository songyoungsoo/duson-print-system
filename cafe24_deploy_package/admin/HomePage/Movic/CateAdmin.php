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
function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.title.value == "") {
alert("���� �� �Է��Ͽ��ּ���!!");
f.title.focus();
return false;
}

<?php if ($code=="modify"){}else{?>

if(f.photofile.value==""){
alert("������������ ���ε��� �ּ��� *^^*\n\n");
f.photofile.focus();
return false
}

if((f.photofile.value.lastIndexOf(".asf")==-1) && (f.photofile.value.lastIndexOf(".avi")==-1) && (f.photofile.value.lastIndexOf(".wma")==-1)){
alert("������������ asf , avi , wma ���ϸ� ���ε� �ϽǼ� �ֽ��ϴ�.\n\n");
f.photofile.focus();
return false
}

if(f.photofile.value.lastIndexOf("\"") > -1){
alert("���������Ͽ� \" �ֵ���ǥ�� �Է��ϽǼ� �����ϴ�.\n\n");
f.photofile.focus();
return false
}

<?php } ?>

if (f.cont.value.length < 10 ) {
alert("������ �Է����� �ʾҰų� �ʹ� ª���ϴ�.\n\n");
f.cont.focus();
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
<b>&nbsp;&nbsp;�����󺸱� <?php if ($code=="modify"){?>����<?}else{?>�Է�<?php } ?></b><BR>
</td>
</tr>

<!------------ ī�װ��� ��� ���� �߰� -------------
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>ī�װ���&nbsp;&nbsp;</td>
<td colspan=3>
</td>
</tr>----------------->

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����&nbsp;&nbsp;</td>
<td colspan=3><INPUT TYPE="text" NAME="title" size=50 maxLength='80' value='<?php if ($code=="modify"){echo("$View_title");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����������&nbsp;&nbsp;</td>
<td colspan=3>
<?php if ($code=="modify"){?>
<INPUT TYPE="hidden" name='TTFileName' value='<?php echo $View_upfile?>'> 
����<?php echo ϸ�: <?=$View_upfile?><BR>
<INPUT TYPE="checkbox" name='PhotoFileModify'> ������ ������ �����Ϸ��� üũ���ּ���!!<BR>
<?php } ?>
<INPUT TYPE="file" NAME="photofile" size=50 maxLength='80' value='<?php if ($code=="modify"){echo("$View_upfile");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>���빮������&nbsp;&nbsp;</td>
<td colspan=3>
<select name='cont_style'>
<option value='br' <?php if ($code=="modify"){if($View_ContStyle=="br"){?>selected style='background-color:#3399CC; color:#FFFFFF;'<?}}?>>�ڵ� BR</option>
<!---<option value='html' <?php if ($code=="modify"){if($View_ContStyle=="html"){?>selected style='background-color:#3399CC; color:#FFFFFF;'<?}}?>>HTML �����Է�</option>--->
</select>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����&nbsp;&nbsp;</td>
<td colspan=3>
<TEXTAREA NAME="cont" ROWS="15" COLS="70"><?php if ($code=="modify"){echo("$View_cont");}?></TEXTAREA>
</td>
</tr>

<tr>
<td colspan=4 align=center>
<input type='submit' value=' <?php if ($code=="modify"){?>����<?}else{?>����<?php } ?> �մϴ�.'>
</td>
</tr>

</table>

<? } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

include"CateView.php";
$upload_dir="./upload";
if($View_upfile){unlink("$upload_dir/$View_upfile");}

mysql_query("DELETE FROM MlangHomePage_Movic WHERE no='$no'");
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

if($photofile){
	$upload_dir="./upload";
	include"upload.php";
	}

$dbinsert ="insert into MlangHomePage_Movic values('',
'$cate',
'$title',
'$PhotofileName',
'$cont_style',
'$cont'
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

if($PhotoFileModify){ 
       if(!$photofile){
	           echo ("<script language=javascript>
                             window.alert('������ �����Ѵٰ� üũ�ϼ˴µ� ���ε��� ������ ���� �־�� �̤�');
                               history.go(-1);
                             </script>");
                                               exit;
	        }
	$upload_dir="./upload";
	include"upload.php";
$YYPjFile="$PhotofileName";
if($TTFileName){unlink("$upload_dir/$TTFileName");}
}else{
$YYPjFile="$TTFileName";
}

$query ="UPDATE MlangHomePage_Movic SET 
cate='$cate',
title='$title',
upfile='$YYPjFile',
ContStyle='$cont_style',
cont='$cont'
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