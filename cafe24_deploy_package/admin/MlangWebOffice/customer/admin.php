<?php
////////////////// ������ �α��� ////////////////////
include"../../../db.php";
include"../../config.php";
////////////////////////////////////////////////////
?>

<?php
if($mode=="staffForm"){ ///////////////////////////////////////////////////////////////////////////////////////
include"../../title.php";
$Bgcolor1="408080";
if($code=="modify"){include"view_staff.php";}
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

if (f.name.value == "") {
alert("�̸��� �Է��Ͽ��ּ���!!");
f.name.focus();
return false;
}

if ((f.tel_1.value.length < 2) || (f.tel_1.value.length > 4)) {
alert("�޴����� ���ڸ��� 2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.tel_1.focus();
return false;
}
if (!TypeCheck(f.tel_1.value, NUM)) {
alert("�޴����� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.tel_1.focus();
return false;
}
if ((f.tel_2.value.length < 3) || (f.tel_2.value.length > 4)) {
alert("�޴����� �߰��ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.tel_2.focus();
return false;
}
if (!TypeCheck(f.tel_2.value, NUM)) {
alert("�޴����� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.tel_2.focus();
return false;
}
if ((f.tel_3.value.length < 3) || (f.tel_3.value.length > 4)) {
alert("�޴����� ���ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.tel_3.focus();
return false;
}
if (!TypeCheck(f.tel_3.value, NUM)) {
alert("�޴����� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.tel_3.focus();
return false;
}

if (f.work.value == "") {
alert("��å�� �Է��Ͽ��ּ���!!");
f.work.focus();
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
Mlangwindow.document.write("<html><head><title>�̹������� �̸�����</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><a href=\"#\" onClick=\"javascript:window.close();\"><img src=\"" + image + "\" border=\"0\"></a></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='â �ݱ�' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}
</script>
<script src="../../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=5 cellspacing=1>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?php if ($code=="modify"){?>staffModify_ok<?}else{?>staffForm_ok<?php } ?>'>
<INPUT TYPE="hidden" name='customer_no' value='<?php echo $customer_no?>'>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' class='Left1' align=left colspan=2>&nbsp;&nbsp;ä�����������Է¶�</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�̸�&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="name" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$staffViewname");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�޴���&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="tel_1" size=9 maxLength='5' value='<?php if ($code=="modify"){echo("$staffViewtel_1");}?>'>
-
<INPUT TYPE="text" NAME="tel_2" size=9 maxLength='5' value='<?php if ($code=="modify"){echo("$staffViewtel_2");}?>'>
-
<INPUT TYPE="text" NAME="tel_3" size=9 maxLength='5' value='<?php if ($code=="modify"){echo("$staffViewtel_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>��å&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="work" size=18 maxLength='20' value='<?php if ($code=="modify"){echo("$staffViewwork");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����&nbsp;&nbsp;</td>
<td>
<?php if ($code=="modify"){?>
<img src='./upload/staff/<?php echo $staffViewcustomer_no?>/<?php echo $staffViewphoto?>' width=30>
<INPUT TYPE="hidden" name='TTFileName' value='<?php echo $staffViewphoto?>'>
<INPUT TYPE="hidden" name='PHONO' value='<?php echo $staffViewcustomer_no?>'>
<INPUT TYPE="checkbox" name='PhotoFileModify'> ������ �����Ϸ��� üũ���ּ���!!<BR>
<?php } ?>
<INPUT TYPE="file" NAME="photofile" size=30 onChange="Mlamg_image(this.value)"><BR>
</td>
</tr>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' <?php if ($code=="modify"){?>����<?}else{?>����<?php } ?> �մϴ�.'>
</td>
</tr>

</table>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="staffForm_ok"){

	$result = mysql_query("SELECT max(no) FROM WebOffice_customer_staff");
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

// �ڷḦ ���ε��� ������ ���� �����ش�.. /////////////////////////////////////////////////////////////////////////////////
$dir = "./upload/staff/$customer_no";
$dir_handle = is_dir("$dir"); 
if(!$dir_handle){mkdir("$dir", 0755); exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$upload_dir="$dir";
include"upload.php";

$dbinsert ="insert into WebOffice_customer_staff values('$new_no',
'$customer_no',
'$name',  
'$tel_1',   
'$tel_2',
'$tel_3',
'$work',
'$PhotofileName'
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


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

	$result = mysql_query("SELECT max(no) FROM WebOffice_customer");
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

$dbinsert ="insert into WebOffice_customer values('$new_no',
'$bizname',  
'$ceoname',
'$tel_1',   
'$tel_2',
'$tel_3',
'$fax_1',
'$fax_2',  
'$fax_3',
'$zip',   
'$offtel_1',
'$offtel_2', 
'$offtel_3',   
'$offfax_1',    
'$offfax_2', 
'$offfax_3', 
'$PhotofileName',
'$cont '
)";
$result_insert= mysql_query($dbinsert,$db);

// �ڷḦ ���ε��� ������ ���� �����ش�.. /////////////////////////////////////////////////////////////////////////////////
$dir = "./upload/staff/$new_no"; mkdir("$dir", 0755);  exec("chmod 777 $dir"); 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>��ȣ&nbsp;&nbsp;</td>
<td><?php echo $Viewbizname?></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>��ǥ&nbsp;&nbsp;</td>
<td><?php echo $Viewceoname?></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����TEL&nbsp;&nbsp;</td>
<td>
<?php echo $Viewtel_1?>
-
<?php echo $Viewtel_2?>
-
<?php echo $Viewtel_3?>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����FAX&nbsp;&nbsp;</td>
<td>
<?php echo $Viewfax_1?>
-
<?php echo $Viewfax_2?>
-
<?php echo $Viewfax_3?>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�ּ�(����)&nbsp;&nbsp;</td>
<td><?php echo $Viewzip?></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>������ȭ&nbsp;&nbsp;</td>
<td>
<?php echo $Viewofftel_1?>
-
<?php echo $Viewofftel_2?>
-
<?php echo $Viewofftel_3?>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����FAX&nbsp;&nbsp;</td>
<td>
<?php echo $Viewofffax_1?>
-
<?php echo $Viewofffax_2?>
-
<?php echo $Viewofffax_3?>
</td>
</tr>

<tr>
<td align=right>
<font style='font:bold; font-size:10pt;'>����ä����Ȳ</font>&nbsp;&nbsp;
</td>
<td>
<input type='button' onClick="javascript:popup=window.open('<?php echo $PHP_SELF?>?mode=staffForm&customer_no=<?php echo $row[no]?>', 'WebOffice_customerstaffForm','width=450,height=200,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='ä������ ���� �Է��ϱ�'>
</td>
</tr>

<tr><td colspan=2 align=center>
   <table border=1 align=center width=100% cellpadding='5' cellspacing='0'>
     <tr>
	 <td align=center bgcolor='#<?php echo $Bgcolor1?>' class='Left2'>�̸�</td>
	 <td align=center bgcolor='#<?php echo $Bgcolor1?>' class='Left2'>�޴���</td>
	 <td align=center bgcolor='#<?php echo $Bgcolor1?>' class='Left2'>��å</td>
	 <td align=center bgcolor='#<?php echo $Bgcolor1?>' class='Left2'>����</td>
	 <td align=center bgcolor='#<?php echo $Bgcolor1?>' class='Left2'>����</td>
	 </tr>
<?php
include"../../../db.php";
$result_customer_staff= mysql_query("select * from WebOffice_customer_staff where customer_no=$no",$db);
$rows=mysql_num_rows($result_customer_staff);
if($rows){
while($row= mysql_fetch_array($result_customer_staff)) 
{ 
?>
     <tr>
	 <td align=center><?php echo $row[name]?></td>
	 <td align=center><?php echo $row[tel_1]?> - <?php echo $row[tel_2]?> - <?php echo $row[tel_3]?></td>
	 <td align=center><?php echo $row[work]?></td>
	 <td align=center><?php if (!$row[photo]){?>����NO<?}else{?><a href='#' onClick="javascript:window.open('./uploa<?php echo taff/<<?php echo no?>/<?=$row[photo]?>', 'dasd12d1nt3wa','top=0,left=0,menubar=yes,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='./uploa<?php echo taff/<<?php echo no?>/<?=$row[photo]?>' onload="if(this.width>50){this.width=50}" border=0></a><?php } ?>
	 </td>
     <td align=center>
	 <input type='button' onClick="javascript:popup=window.open('<?php echo $PHP_SELF?>?mode=staffForm&customer_no=<?php echo $row[no]?>&code=modify', 'WebOffice_customerstaffModify','width=450,height=200,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='����' style='height:18; width:30;'>
     <input type='button' onClick="javascript:WebOffice_customer_staff_Del('<?php echo $row[no]?>');" value='����' style='height:18; width:30;'>
	 </td>
	 </tr>
<?php
}
}else{echo("<tr><td colspan=4><p align=center><b>��� �ڷᰡ ����.</b></p></td></tr>");}
mysql_close($db); 
?>
   </table>
</td></tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����൵&nbsp;&nbsp;</td>
<td><a href='#' onClick="javascript:window.open('./upload/<?php echo $Viewoffmap?>', 'dasd12d1nt3wa','top=0,left=0,menubar=yes,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='./upload/<?php echo $Viewoffmap?>' onload="if(this.width>500){this.width=500}" border=0></a></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�޸��&nbsp;&nbsp;</td>
<td>
<TEXTAREA NAME="cont" ROWS="5" COLS="60"><?php echo $Viewcont?></TEXTAREA>
</td>
</tr>

</table>

</body>
</html>

<? exit; }?>


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

if ((f.tel_1.value.length < 2) || (f.tel_1.value.length > 4)) {
alert("����TEL�� ���ڸ��� 2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.tel_1.focus();
return false;
}
if (!TypeCheck(f.tel_1.value, NUM)) {
alert("����TEL�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.tel_1.focus();
return false;
}
if ((f.tel_2.value.length < 3) || (f.tel_2.value.length > 4)) {
alert("����TEL�� �߰��ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.tel_2.focus();
return false;
}
if (!TypeCheck(f.tel_2.value, NUM)) {
alert("����TEL�� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.tel_2.focus();
return false;
}
if ((f.tel_3.value.length < 3) || (f.tel_3.value.length > 4)) {
alert("����TEL�� ���ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.tel_3.focus();
return false;
}
if (!TypeCheck(f.tel_3.value, NUM)) {
alert("����TEL�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.tel_3.focus();
return false;
}

if ((f.fax_1.value.length < 2) || (f.fax_1.value.length > 4)) {
alert("����FAX�� ���ڸ��� 2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.fax_1.focus();
return false;
}
if (!TypeCheck(f.fax_1.value, NUM)) {
alert("����FAX�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.fax_1.focus();
return false;
}
if ((f.fax_2.value.length < 3) || (f.fax_2.value.length > 4)) {
alert("����FAX�� �߰��ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.fax_2.focus();
return false;
}
if (!TypeCheck(f.fax_2.value, NUM)) {
alert("����FAX�� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.fax_2.focus();
return false;
}
if ((f.fax_3.value.length < 3) || (f.fax_3.value.length > 4)) {
alert("����FAX�� ���ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.fax_3.focus();
return false;
}
if (!TypeCheck(f.fax_3.value, NUM)) {
alert("����FAX�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.fax_3.focus();
return false;
}

if (f.zip.value == "") {
alert("�ּ�[����]�� �Է��Ͽ��ּ���!!");
f.zip.focus();
return false;
}

if ((f.offtel_1.value.length < 2) || (f.offtel_1.value.length > 4)) {
alert("������ȭ�� ���ڸ��� 2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.offtel_1.focus();
return false;
}
if (!TypeCheck(f.offtel_1.value, NUM)) {
alert("������ȭ�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.offtel_1.focus();
return false;
}
if ((f.offtel_2.value.length < 3) || (f.offtel_2.value.length > 4)) {
alert("������ȭ�� �߰��ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.offtel_2.focus();
return false;
}
if (!TypeCheck(f.offtel_2.value, NUM)) {
alert("������ȭ�� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.offtel_2.focus();
return false;
}
if ((f.offtel_3.value.length < 3) || (f.offtel_3.value.length > 4)) {
alert("������ȭ�� ���ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.offtel_3.focus();
return false;
}
if (!TypeCheck(f.offtel_3.value, NUM)) {
alert("������ȭ�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.offtel_3.focus();
return false;
}

if ((f.offfax_1.value.length < 2) || (f.offfax_1.value.length > 4)) {
alert("����FAX�� ���ڸ��� 2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.offfax_1.focus();
return false;
}
if (!TypeCheck(f.offfax_1.value, NUM)) {
alert("����FAX�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.offfax_1.focus();
return false;
}
if ((f.offfax_2.value.length < 3) || (f.offfax_2.value.length > 4)) {
alert("����FAX�� �߰��ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.offfax_2.focus();
return false;
}
if (!TypeCheck(f.offfax_2.value, NUM)) {
alert("����FAX�� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.offfax_2.focus();
return false;
}
if ((f.offfax_3.value.length < 3) || (f.offfax_3.value.length > 4)) {
alert("����FAX�� ���ڸ��� 3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.offfax_3.focus();
return false;
}
if (!TypeCheck(f.offfax_3.value, NUM)) {
alert("����FAX�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.offfax_3.focus();
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
</script>
<script src="../../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=0 cellspacing=5>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?php if ($code=="modify"){?>modify_ok<?}else{?>form_ok<?php } ?>'>
<?php if ($code=="modify"){?><INPUT TYPE="hidden" name='no' value='<?php echo $no?>'><?php } ?>

<tr>
<td class='coolBar' colspan=2 height=25>
<b>&nbsp;&nbsp;���� �ŷ�ó���� <?php if ($code=="modify"){?>����<?}else{?>�Է�<?php } ?></b><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>��ȣ&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="bizname" size=50 maxLength='80' value='<?php if ($code=="modify"){echo("$Viewbizname");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>��ǥ&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="ceoname" size=30 maxLength='20' value='<?php if ($code=="modify"){echo("$Viewceoname");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����TEL&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="tel_1" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewtel_1");}?>'>
-
<INPUT TYPE="text" NAME="tel_2" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewtel_2");}?>'>
-
<INPUT TYPE="text" NAME="tel_3" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewtel_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����FAX&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="fax_1" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewfax_1");}?>'>
-
<INPUT TYPE="text" NAME="fax_2" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewfax_2");}?>'>
-
<INPUT TYPE="text" NAME="fax_3" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewfax_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�ּ�(����)&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="zip" size=70 maxLength='200' value='<?php if ($code=="modify"){echo("$Viewzip");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>������ȭ&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="offtel_1" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewofftel_1");}?>'>
-
<INPUT TYPE="text" NAME="offtel_2" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewofftel_2");}?>'>
-
<INPUT TYPE="text" NAME="offtel_3" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewofftel_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����FAX&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="offfax_1" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewofffax_1");}?>'>
-
<INPUT TYPE="text" NAME="offfax_2" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewofffax_2");}?>'>
-
<INPUT TYPE="text" NAME="offfax_3" size=12 maxLength='5' value='<?php if ($code=="modify"){echo("$Viewofffax_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����൵&nbsp;&nbsp;</td>
<td>
<?php if ($code=="modify"){?>
<img src='./upload/<?php echo $Viewoffmap?>' width=50><BR>
<INPUT TYPE="hidden" name='TTFileName' value='<?php echo $Viewoffmap?>'>
<INPUT TYPE="checkbox" name='PhotoFileModify'> ����൵ ������ �����Ϸ��� üũ���ּ���!!<BR>
<?php } ?>
<INPUT TYPE="file" NAME="photofile" size=30 onChange="Mlamg_image(this.value)"><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�޸��&nbsp;&nbsp;</td>
<td>
<TEXTAREA NAME="cont" ROWS="5" COLS="60"><?php if ($code=="modify"){echo("$Viewcont");}?></TEXTAREA>
</td>
</tr>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' <?php if ($code=="modify"){?>����<?}else{?>����<?php } ?> �մϴ�.'>
</td>
</tr>

</table>

<?php
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

// �� ������ ���� ��ü ���� ������ �� ���� ���� //////////////////////////////////
	$Mlang_DIR = opendir("./upload/staff/$no"); // upload ���� OPEN
	while($ufiles = readdir($Mlang_DIR)) {
		if(($ufiles != ".") && ($ufiles != "..")) {
			unlink("./upload/staff/$no/$ufiles"); // ���ϵ� ����
		}
	}
	closedir($Mlang_DIR);

	rmdir("./upload/staff/$no");  // upload ���� ����

////////////////////////////////////////////////////////////////////////////////////
$resultPHpto= mysql_query("select * from WebOffice_customer where no='$no'",$db);
$row= mysql_fetch_array($resultPHpto);
$PHOToDir="./upload/$row[offmap]";
$PHOToFile = join ('', file ("$PHOToDir"));
if($PHOToFile){unlink("$PHOToDir");}
mysql_query("DELETE FROM WebOffice_customer WHERE no='$no'");
mysql_query("DELETE FROM WebOffice_customer_staff WHERE customer_no='$no'");
mysql_close();

echo ("
<html>
<script language=javascript>
window.alert('���������� �ŷ�ó��Ȳ $no�� �ڷ��� ���� ó�� �Ͽ����ϴ�.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="customer_staff_delete"){
//�����ڷ����
$resultPHpto= mysql_query("select * from WebOffice_customer_staff where no='$customer_staff_no'",$db);
$row= mysql_fetch_array($resultPHpto);
$PHOToDir="./upload/staff/$row[customer_no]/$row[photo]";
$PHOToFile = join ('', file ("$PHOToDir"));
if($PHOToFile){unlink("$PHOToDir");}

mysql_query("DELETE FROM WebOffice_customer_staff WHERE no='$customer_staff_no'");
mysql_close();

echo ("
<html>
<script language=javascript>
window.alert('���������� ����ä����Ȳ $no�� �ڷ��� ���� ó�� �Ͽ����ϴ�.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="staffModify_ok"){

if($PhotoFileModify){
$upload_dir="./upload/staff/$PHONO";
include"upload.php";
$YYPjFile="$PhotofileName";
if($TTFileName){unlink("$upload_dir/$TTFileName");}
}else{
$YYPjFile="$TTFileName";
}

$query ="UPDATE WebOffice_customer_staff SET 
 name='$name',
 tel_1='$tel_1',
 tel_2='$tel_2',
 tel_3='$tel_3',
 work='$work',
 photo='$YYPjFile'
WHERE no='$customer_no'";
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


} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modify_ok"){

if($PhotoFileModify){
$upload_dir="./upload";
include"upload.php";
$YYPjFile="$PhotofileName";
if($TTFileName){unlink("$upload_dir/$TTFileName");}
}else{
$YYPjFile="$TTFileName";
}

$query ="UPDATE WebOffice_customer SET 
bizname='$bizname',  
ceoname='$ceoname',
tel_1='$tel_1',   
tel_2='$tel_2',
tel_3='$tel_3',
fax_1='$fax_1',
fax_2='$fax_2',  
fax_3='$fax_3',
zip='$zip',   
offtel_1='$offtel_1',
offtel_2='$offtel_2', 
offtel_3='$offtel_3',   
offfax_1='$offfax_1',    
offfax_2='$offfax_2', 
offfax_3='$offfax_3', 
offmap='$YYPjFile',
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
        window.self.close();
		</script>
	");
		exit;

}
mysql_close($db);


}
?>