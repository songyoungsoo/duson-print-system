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

if($code=="modify"){include"view.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
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

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.bizname.value == "") {
alert("��ȣ�� �Է��Ͽ��ּ���!!");
f.bizname.focus();
return false;
}

if (f.ceoname.value == "") {
alert("��ǥ�� �Է��Ͽ��ּ���!!");
f.ceoname.focus();
return false;
}

if ((f.ceo_hp_1.value.length < 2) || (f.ceo_hp_1.value.length > 4)) {
alert("��ǥ HP �� ���ڸ��� 2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.ceo_hp_1.focus();
return false;
}
if (!TypeCheck(f.ceo_hp_1.value, NUM)) {
alert("��ǥ HP �� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.ceo_hp_1.focus();
return false;
}
if ((f.ceo_hp_2.value.length < 3) || (f.ceo_hp_2.value.length > 4)) {
alert("��ǥ HP �� �߰��ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.ceo_hp_2.focus();
return false;
}
if (!TypeCheck(f.ceo_hp_2.value, NUM)) {
alert("��ǥ HP �� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.ceo_hp_2.focus();
return false;
}
if ((f.ceo_hp_3.value.length < 3) || (f.ceo_hp_3.value.length > 4)) {
alert("��ǥ HP �� ���ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.ceo_hp_3.focus();
return false;
}
if (!TypeCheck(f.ceo_hp_3.value, NUM)) {
alert("��ǥ HP �� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.ceo_hp_3.focus();
return false;
}

if (f.a_name.value == "") {
alert("���� ����� �Է��Ͽ��ּ���!!");
f.a_name.focus();
return false;
}

if ((f.a_hp_1.value.length < 2) || (f.a_hp_1.value.length > 4)) {
alert("���� ��� HP �� ���ڸ��� 2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.a_hp_1.focus();
return false;
}
if (!TypeCheck(f.a_hp_1.value, NUM)) {
alert("���� ��� HP �� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.a_hp_1.focus();
return false;
}
if ((f.a_hp_2.value.length < 3) || (f.a_hp_2.value.length > 4)) {
alert("���� ��� HP �� �߰��ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.a_hp_2.focus();
return false;
}
if (!TypeCheck(f.a_hp_2.value, NUM)) {
alert("���� ��� HP �� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.a_hp_2.focus();
return false;
}
if ((f.a_hp_3.value.length < 3) || (f.a_hp_3.value.length > 4)) {
alert("���� ��� HP �� ���ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.a_hp_3.focus();
return false;
}
if (!TypeCheck(f.a_hp_3.value, NUM)) {
alert("���� ��� HP �� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.a_hp_3.focus();
return false;
}

if (f.zip.value == "") {
alert("�ּ� �� �Է��Ͽ��ּ���!!\n\n�ּ� �ڵ��˻�â�� ���ڽ��ϴ�.");
f.zip.focus();
zipcheck();
return false;
}
if (f.zip1.value == "") {
alert("���ּ� �� �Է��Ͽ��ּ���!!\n\n�ּ� �ڵ��˻�â�� ���ڽ��ϴ�.");
f.zip1.focus();
zipcheck();
return false;
}
if (f.zip2.value == "") {
alert("�������ּ� �� �Է��Ͽ��ּ���!!");
f.zip2.focus();
return false;
}

if(f.photofile.value){
<?php if ($code=="modify"){}else{?>
if((f.photofile.value.lastIndexOf(".jpg")==-1) && (f.photofile.value.lastIndexOf(".gif")==-1))
{
alert("���� �ڷ����� JPG �� GIF ���ϸ� �ϽǼ� �ֽ��ϴ�.");
f.photofile.focus();
return false
}
<?php } ?>
}

}
//////////////// �̹��� �̸����� //////////////////////////////////
/* �ҽ�����: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {

Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
Mlangwindow.document.open();
Mlangwindow.document.write("<html><head><title>�̹��� �̸�����</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><a href=\"#\" onClick=\"javascript:window.close();\"><img src=\"" + image + "\" border=\"0\"></a></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='������ �ݱ�' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}
///////////////// �ּҰ˻�â�� �ٿ��. ////////////////////////////////////////////////////////////////

function zipcheck()
{
window.open("../../int/zip.php?mode=search&formname=FrmUserXInfo&DbDir=../../","zip","scrollbars=yes,resizable=yes,width=550,height=510,top=10,left=50");
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
<b>&nbsp;&nbsp;�߱� �ŷ�ó���� <?php if ($code=="modify"){?>����<?}else{?>�Է�<?php } ?></b><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>��ȣ&nbsp;&nbsp;</td>
<td colspan=3><INPUT TYPE="text" NAME="bizname" size=50 maxLength='80' value='<?php if ($code=="modify"){echo("$Viewbizname");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>��ǥ&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="ceoname" size=20 maxLength='20' value='<?php if ($code=="modify"){echo("$Viewceoname");}?>'></td>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>��ǥ(HP)&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="ceo_hp_1" size=7 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewceo_hp_1");}?>'>
-
<INPUT TYPE="text" NAME="ceo_hp_2" size=7 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewceo_hp_2");}?>'>
-
<INPUT TYPE="text" NAME="ceo_hp_3" size=7 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewceo_hp_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�������&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="a_name" size=20 maxLength='20' value='<?php if ($code=="modify"){echo("$Viewa_name");}?>'></td>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�������(HP)&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="a_hp_1" size=7 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewa_hp_1");}?>'>
-
<INPUT TYPE="text" NAME="a_hp_2" size=7 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewa_hp_2");}?>'>
-
<INPUT TYPE="text" NAME="a_hp_3" size=7 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewa_hp_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>TEL&nbsp;&nbsp;</td>
<td><TEXTAREA NAME="tel" ROWS="2" COLS="20"><?php if ($code=="modify"){echo("$Viewtel");}?></TEXTAREA></td>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>FAX&nbsp;&nbsp;</td>
<td><TEXTAREA NAME="fax" ROWS="2" COLS="20"><?php if ($code=="modify"){echo("$Viewfax");}?></TEXTAREA></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�ּ�&nbsp;&nbsp;</td>
<td colspan=3>
<table border="0" cellspacing="0" cellpadding="0">
<tr><td align=right>&nbsp;������ȣ&nbsp;</td>
<td><input  type="text" name="zip" size="10" onClick="javascript:alert('�ּҸ� �Է��ϱ����� �ڵ��ּ�ã��â �� �ٿ�ڽ��ϴ�.'); zipcheck();" <?php if ($code=="modify"){echo("value='$Viewzip'");}?>>
<input type='button' onClick="javascript:zipcheck();" value='�ּ��ڵ��Է�'>
</td>
</tr>
<tr><td align=right>&nbsp;���ּ�&nbsp;</td>
<td><input type="text"  name="zip1" size="50" onClick="javascript:alert('�ּҸ� �Է��ϱ����� �ڵ��ּ�ã��â �� �ٿ�ڽ��ϴ�.'); zipcheck();" <?php if ($code=="modify"){echo("value='$Viewzip1'");}?>></td></tr>
<tr><td align=right>&nbsp;�������ּ�&nbsp;</td><td><input type="text" name="zip2" size="50" <?php if ($code=="modify"){echo("value='$Viewzip2'");}?>></td></tr>
</table>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>ã�ư��� ��&nbsp;&nbsp;</td>
<td colspan=3>
<?php if ($code=="modify"){?>
<img src='./upload/<?php echo $Viewoffmap?>' width=50><BR>
<INPUT TYPE="hidden" name='TTFileName' value='<?php echo $Viewoffmap?>'>
<INPUT TYPE="checkbox" name='PhotoFileModify'> ã�ư��� �� ������ �����Ϸ��� üũ���ּ���!!<BR>
<?php } ?>
<INPUT TYPE="file" NAME="photofile" size=30 onChange="Mlamg_image(this.value)"><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�������&nbsp;&nbsp;</td>
<td colspan=3>
<TEXTAREA NAME="cont" ROWS="5" COLS="60"><?php if ($code=="modify"){echo("$Viewcont");}?></TEXTAREA>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�޸��&nbsp;&nbsp;</td>
<td colspan=3>
<TEXTAREA NAME="memo" ROWS="5" COLS="60"><?php if ($code=="modify"){echo("$Viewmemo");}?></TEXTAREA>
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

$resultPHpto= mysql_query("select * from MlangWebOffice_heavy_customer where no='$no'",$db);
$row= mysql_fetch_array($resultPHpto);
$PHOToDir="./upload/$row[offmap]";
$PHOToFile = join ('', file ("$PHOToDir"));
if($PHOToFile){unlink("$PHOToDir");}
mysql_query("DELETE FROM MlangWebOffice_heavy_customer WHERE no='$no'");
mysql_close();

echo ("
<html>
<script language=javascript>
window.alert('���������� �߱�ŷ�ó ��Ȳ $no�� �ڷ��� ���� ó�� �Ͽ����ϴ�.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="form_ok"){

	$result = mysql_query("SELECT max(no) FROM MlangWebOffice_heavy_customer");
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

$upload_dir="./upload";
include"upload.php";

$dbinsert ="insert into MlangWebOffice_heavy_customer values('$new_no',
'$bizname',
'$ceoname',
'$a_name',
'$ceo_hp_1',
'$ceo_hp_2',
'$ceo_hp_3',
'$a_hp_1',
'$a_hp_2 ',
'$a_hp_3',
'$tel',
'$fax',
'$zip',
'$zip1',
'$zip2',
'$PhotofileName',
'$cont',
'$memo'
)";
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('\\n�ڷḦ ���������� ���� �Ͽ����ϴ�.\\n\\n�ڷḦ ���� ����Ͻ÷��� â�� �ٽ� ������\\n');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;


} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="view"){ ///////////////////////////////////////////////////////////////////////////////////////
include"../../title.php";
$Bgcolor1="408080";
include"view.php";
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
.Left2 {font-size:9pt; color:#FFFFFF;}
td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000;}
</style>

<script src="../../js/coolbar.js" type="text/javascript"></script>

<script>
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function WebOffice_customer_staff_Del(no){
	if (confirm(+no+'�� �����ڷḦ ����ó�� �Ͻðڽ��ϱ�..?\n\n�ѹ� ������ �ڷ�� ���� ���� ������ ������ �����ּ���.............!!')) {
		str='<?php echo $PHP_SELF?>?customer_staff_no='+no+'&mode=customer_staff_delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=100,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=1 align=center width=100% cellpadding=5 cellspacing=1>

<tr>
<td class='coolBar' colspan=4 height=25>
&nbsp;&nbsp;�߱� �ŷ�ó����<?php echo ��Ϲ�ȣ: <b><?=$no?></b><BR>
</td>
</tr>

<tr>
<td bgcolor='#408080' width=100 class='Left1' align=right>��ȣ&nbsp;&nbsp;</td>
<td colspan=3>
<?php echo $Viewbizname?>
</td>
</tr>

<tr>
<td bgcolor='#408080' width=100 class='Left1' align=right>��ǥ&nbsp;&nbsp;</td>
<td>
<?php echo $Viewceoname?>
</td>
<td bgcolor='#408080' width=100 class='Left1' align=right>��ǥ(HP)&nbsp;&nbsp;</td>
<td>
<?php echo $Viewceo_hp_1?>
-
<?php echo $Viewceo_hp_2?>
-
<?php echo $Viewceo_hp_3?>
</td>
</tr>

<tr>
<td bgcolor='#408080' width=100 class='Left1' align=right>�������&nbsp;&nbsp;</td>
<td>
<?php echo $Viewa_name?>
</td>
<td bgcolor='#408080' width=100 class='Left1' align=right>�������(HP)&nbsp;&nbsp;</td>
<td>
<?php echo $Viewa_hp_1?>
-
<?php echo $Viewa_hp_2?>
-
<?php echo $Viewa_hp_3?>
</td>
</tr>

<tr>
<td bgcolor='#408080' width=100 class='Left1' align=right>TEL&nbsp;&nbsp;</td>
<td>
<?php
        $CONTENT=$Viewtel;
		$CONTENT = eregi_replace("<", "&lt;", $CONTENT);
		$CONTENT = eregi_replace(">", "&gt;", $CONTENT);
		$CONTENT = eregi_replace("\"", "&quot;", $CONTENT);
		$CONTENT = eregi_replace("\|", "&#124;", $CONTENT);
		$CONTENT = eregi_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = eregi_replace("\r\n", "<BR>", $CONTENT);
		$connent_tel=$CONTENT;
echo("$connent_tel");
?>
</td>
<td bgcolor='#408080' width=100 class='Left1' align=right>FAX&nbsp;&nbsp;</td>
<td>
<?php
        $CONTENT=$Viewfax;
		$CONTENT = eregi_replace("<", "&lt;", $CONTENT);
		$CONTENT = eregi_replace(">", "&gt;", $CONTENT);
		$CONTENT = eregi_replace("\"", "&quot;", $CONTENT);
		$CONTENT = eregi_replace("\|", "&#124;", $CONTENT);
		$CONTENT = eregi_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = eregi_replace("\r\n", "<BR>", $CONTENT);
		$connent_fax=$CONTENT;
echo("$connent_fax");
?>
</td>
</tr>

<tr>
<td bgcolor='#408080' width=100 class='Left1' align=right>�ּ�&nbsp;&nbsp;</td>
<td colspan=3>
<table border="0" cellspacing="0" cellpadding="0">
<tr><td align=right>&nbsp;������ȣ:&nbsp;</td>
<td>
<?php echo $Viewzip?>
</td>
</tr>
<tr><td align=right>&nbsp;���ּ�:&nbsp;</td>
<td>
<?php echo $Viewzip1?>
</td></tr>
<tr><td align=right>&nbsp;�������ּ�:&nbsp;</td>
<td>
<?php echo $Viewzip2?>
</td></tr>
</table>
</td>
</tr>


<tr>
<td bgcolor='#408080' width=100 class='Left1' align=right>ã�ư��� ��&nbsp;&nbsp;</td>
<td colspan=3>
<a href='#' onClick="javascript:window.open('./upload/<?php echo $Viewoffmap?>', 'dasd12d1nt3wa','top=0,left=0,menubar=yes,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='./upload/<?php echo $Viewoffmap?>' onload="if(this.width>500){this.width=500}" border=0></a>
</td>
</tr>

<tr>
<td bgcolor='#408080' width=100 class='Left1' align=right>�������&nbsp;&nbsp;</td>
<td colspan=3>
<?php
        $CONTENT=$Viewcont;
		$CONTENT = eregi_replace("<", "&lt;", $CONTENT);
		$CONTENT = eregi_replace(">", "&gt;", $CONTENT);
		$CONTENT = eregi_replace("\"", "&quot;", $CONTENT);
		$CONTENT = eregi_replace("\|", "&#124;", $CONTENT);
		$CONTENT = eregi_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = eregi_replace("\r\n", "<BR>", $CONTENT);
		$connent_cont=$CONTENT;
echo("$connent_cont");
?>
</td>
</tr>

<tr>
<td bgcolor='#408080' width=100 class='Left1' align=right>�޸��&nbsp;&nbsp;</td>
<td colspan=3>
<?php
        $CONTENT=$Viewmemo;
		$CONTENT = eregi_replace("<", "&lt;", $CONTENT);
		$CONTENT = eregi_replace(">", "&gt;", $CONTENT);
		$CONTENT = eregi_replace("\"", "&quot;", $CONTENT);
		$CONTENT = eregi_replace("\|", "&#124;", $CONTENT);
		$CONTENT = eregi_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = eregi_replace("\r\n", "<BR>", $CONTENT);
		$connent_memo=$CONTENT;
echo("$connent_memo");
?>
</td>
</tr>

<tr>
<td colspan=4 align=center>
<input type='button' value=' â �ݱ� ' onClick="javascript:window.self.close();">
</td>
</tr>

</table>


</body>
</html>

<? exit; }?>


<?php
if($mode=="modify_ok"){

if($PhotoFileModify){
$upload_dir="./upload";
include"upload.php";
$YYPjFile="$PhotofileName";
if($TTFileName){unlink("$upload_dir/$TTFileName");}
}else{
$YYPjFile="$TTFileName";
}

$query ="UPDATE MlangWebOffice_heavy_customer SET 
bizname='$bizname',  
ceoname='$ceoname',
a_name='$a_name',
ceo_hp_1='$ceo_hp_1',
ceo_hp_2='$ceo_hp_2',
ceo_hp_3='$ceo_hp_3',
a_hp_1='$a_hp_1',
a_hp_2='$a_hp_2 ',
a_hp_3='$a_hp_3',
tel='$tel',
fax='$fax',
zip='$zip',
zip1='$zip1',
zip2='$zip2',
offmap='$YYPjFile',
cont='$cont',
memo='$memo'
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