<?php
include"../../db.php";
include"../config.php";

$T_DirUrl="../../MlangPrintAuto";
include"$T_DirUrl/ConDb.php";

$T_DirFole="./int/info.php";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="ModifyOk"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

$query ="UPDATE MlangOrder_PrintAuto SET Type_1='$TypeOne', name='$name', email='$email', zip='$zip', zip1='$zip1', zip2='$zip2', phone='$phone', Hendphone='$Hendphone', delivery='$delivery' bizname='$bizname', bank='$bank', bankname='$bankname', cont='$cont',Gensu='$Gensu' WHERE no='$no'";
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
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$no'>
	");
		exit;

}

mysql_close($db);

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="SubmitOk"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

$Table_result = mysql_query("SELECT max(no) FROM MlangOrder_PrintAuto");
	if (!$Table_result) {
		echo "
			<script>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($Table_result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   

// �ڷḦ ���ε��� ������ ���� �����ش�.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../MlangOrder_PrintAuto/upload/$new_no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$date=date("Y-m-d H:i;s");
$dbinsert ="insert into MlangOrder_PrintAuto values('$new_no',
'$Type', 
'$ImgFolder', 
'$TypeOne',
'$money_1',
'$money_2',	
'$money_3',	
'$money_4',	
'$money_5',	
'$name',   
'$email',
'$zip', 
'$zip1',
'$zip2',
'$phone',   
'$Hendphone',
'$delivery', 
'$bizname',
'$bank',
'$bankname',
'$cont', 
'$date',
'3',
'',
'$phone',
'$Gensu'
)";
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('\\n������ ���������� [����] �Ͽ����ϴ�.\\n');
		opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$new_no'>
	");
		exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="BankForm"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

include"../title.php";
include"$T_DirFole";
$Bgcolor1="408080";
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script>
self.moveTo(0,0);
self.resizeTo(availWidth=680,availHeight=500);
</script>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=600,screen.availHeight)

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
var f=document.myForm;

if (f.BankName.value == "") {
alert("������� �Է��Ͽ��ּ���!!");
f.BankName.focus();
return false;
}

if (f.TName.value == "") {
alert("�������� �Է��Ͽ��ּ���!!");
f.TName.focus();
return false;
}

if (f.BankNo.value == "") {
alert("���¹�ȣ �Է��Ͽ��ּ���!!");
f.BankNo.focus();
return false;
}

}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=5 cellspacing=5>

<form name='myForm' method='post' <?php if ($code=="Text"){}else{?>OnSubmit='javascript:return MemberXCheckField()'<?php } ?> action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='BankModifyOk'>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;�� �����þ� ��й�ȣ ��� ���� �ƢƢƢƢ�</b></font>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>��뿩��&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="radio" NAME="SignMMk" <?php if ($View_SignMMk=="yes"){?>checked<?php } ?> value='yes'>YES
<INPUT TYPE="radio" NAME="SignMMk" <?php if ($View_SignMMk=="no"){?>checked<?php } ?> value='no'>NO
</td>
</tr>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;�� �Ա����� ���� �ƢƢƢƢ�</b></font>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�����&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="BankName" size=20 maxLength='200' value='<?php echo $View_BankName?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>������&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="TName" size=20 maxLength='200' value='<?php echo $View_TName?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>���¹�ȣ&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="BankNo" size=40 maxLength='200' value='<?php echo $View_BankNo?>'></td>
</tr>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;�� �ڵ����� �ϴ� TEXT ���� ���� �ƢƢƢƢ�</b><BR>
&nbsp;&nbsp;&nbsp;&nbsp;*���ǻ��� <big><b>'</b></big> �� ����ǥ ��  <big><b>"</b></big> �� ����ǥ �Է� �Ұ�</font>
</td>
</tr>

<?php
if ($ConDb_A) {
	$Si_LIST_script = split(":", $ConDb_A);
	$k = 0; $kt = 0;
	while($k < sizeof($Si_LIST_script)) {
?>
 <tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right><?echo("$Si_LIST_script[$k]");?>&nbsp;&nbsp;</td>
<td><TEXTAREA NAME="ContText_<?php echo $kt?>" ROWS="4" COLS="58"><?$temp = "View_ContText_".$kt; $get_temp=$$temp; echo("$get_temp");?></TEXTAREA></td>
</tr>
 <?php
		$k=$k+1; $kt=$kt+1;
	} 
} 
?>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' ���� �մϴ�.'>
</td>
</tr>
</FORM>
</table>
<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="BankModifyOk"){

	$fp = fopen("$T_DirFole", "w");
	fwrite($fp, "<?\n");
	fwrite($fp, "\$View_SignMMk=\"$SignMMk\";\n");
	fwrite($fp, "\$View_BankName=\"$BankName\";\n");
	fwrite($fp, "\$View_TName=\"$TName\";\n");
	fwrite($fp, "\$View_BankNo=\"$BankNo\";\n");

if ($ConDb_A) {
	$Si_LIST_script = split(":", $ConDb_A);
	$k = 0; $kt = 0;
	while($k < sizeof($Si_LIST_script)) {
		$tempTwo = "ContText_".$kt; $get_tempTwo=$$tempTwo;
     fwrite($fp, "\$View_ContText_${kt}=\"$get_tempTwo\";\n");
		$k=$k+1; $kt=$kt+1;
	} 
} 

	fwrite($fp, "?>");
	fclose($fp);


echo ("<script language=javascript>
window.alert('���� �Ϸ�....*^^*');
</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=BankForm'>
");
exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php
if($mode=="OrderView"){

 include"../title.php";
 
 if($no){
   $result= mysql_query("select * from MlangOrder_PrintAuto where no='$no'",$db);
   $row= mysql_fetch_array($result);
     if($row){

if($row[OrderStyle]=="2"){
$query ="UPDATE MlangOrder_PrintAuto SET OrderStyle='3' WHERE no='$no'";
$result= mysql_query($query,$db);

	echo ("
		<script language=javascript>
        opener.parent.location.reload();
		</script>
	");

} 
	 }}
?>

<style>
a.file:link,  a.file:visited{font-family:����; font-size: 10pt; color:#336699; line-height:130%; text-decoration:underline}
a.file:hover, a.file:active{font-family:����; font-size: 10pt; color:#333333; line-height:130%; text-decoration:underline}
</style>

<? 
	 $ViewDiwr="../../MlangOrder_PrintAuto";
     include"$ViewDiwr/OrderFormOrderTree.php";
?>
----------------------------------------------------------------------------------------<BR>
<? 
	 $ViewDiwr="../../MlangOrder_PrintAuto";
     include"$ViewDiwr/OrderFormOrderTree.php";
?>
<BR>
<?php if ($no){?>
 
 <font style='font:bold; color:#336699;'>* ÷�� ���� *</font> ���ϸ��� Ŭ���Ͻø� ����/���⸦ �ϽǼ� �ֽ��ϴ�.  =============================<BR>
<table border=0 align=center width=100% cellpadding=5 cellspacing=0>
       <tr>
         <td height="20">
         
<a href='download.php?downfile=<?php echo $row[ThingCate]?>'><?php echo $row[ThingCate]?></a>
<? 
if(is_dir("../../ImgFolder/$View_ImgFolder")){
	
$dir_path = "../../ImgFolder/$View_ImgFolder"; 

if($View_ImgFolder){
$dir_handle = opendir($dir_path);

// ��ü ���丮 ������ ����Ѵ�.
$i=1;
while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")) {
echo (is_file($dir_path.$tmp) ? "" : "[$i] ����: <a href='$dir_path/$tmp' target='_blank' class='file'>$tmp</a><br>");

$i++;
}

}

closedir($dir_handle);	
}
}
?>
		 </td>
       </tr>
</table>
========
<?php } ?>

 <?php if ($no){?>
 <input type='submit' value=' �� �� �� �� '>
<?}else{?>
 <input type='submit' value=' �� �� �� �� '>
<?php } ?>
<input type='button' onClick='javascript:window.close();' value=' â�ݱ�-CLOSE '>


        </td>
       </tr>
     </table>
</form>
 <BR>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php
if($mode=="SinForm"){
 include"../title.php";
?>

<head>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=600,availHeight=200)

function MlangFriendSiteCheckField()
{
var f=document.MlangFriendSiteInfo;

if (f.photofile.value == "") {
alert("���ε��� �̹����� �÷��ֽñ� �ٶ��ϴ�.");
f.photofile.focus();
return false;
}

<?php
include"$T_DirFole";
if($View_SignMMk=="yes"){  // �߰��� �����þ� ��� �Է� ���
?>

if (f.pass.value == "") {
alert("����� ��й�ȣ�� �Է��� �ֽñ� �ٶ��ϴ�.");
f.pass.focus();
return false;
}

<?php
}
?>

}

//////////////// �̹��� �̸����� //////////////////////////////////
/* �ҽ�����: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {

Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
Mlangwindow.document.open();
Mlangwindow.document.write("<html><head><title>�̹��� �̸�����</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='������ �ݱ�' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='<?php echo $Bgcolor_1?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MlangFriendSiteCheckField()' action='<?php echo $PHP_SELF?>'>

<INPUT TYPE="hidden" name='mode' value='SinFormModifyOk'>
<INPUT TYPE="hidden" name='no' value='<?php echo $no?>'>
<?php if ($ModifyCode){?><INPUT TYPE="hidden" name='ModifyCode' value='ok'><?php } ?>

<tr>
<td bgcolor='#6699CC' colspan=2><font style='color:#FFFFFF; font:bold;'>����/�þ� - ���/����</font></td>
</td>
</tr>

<tr>
<td align=right>�̹��� �ڷ�:&nbsp;</td>
<td>
<INPUT TYPE="hidden" NAME="photofileModify" value='ok'>
<INPUT type="file" Size=45 name="photofile" onChange="Mlamg_image(this.value)">
</td>
</tr>

<?php
if($View_SignMMk=="yes"){  // �߰��� �����þ� ��� �Է� ���
        $result_SignTy= mysql_query("select * from  MlangOrder_PrintAuto where no='$no'",$db);
        $row_SignTy= mysql_fetch_array($result_SignTy);
		$ViewSignTy_pass="$row_SignTy[pass]"; 
?>
<tr>
<td align=right>��� ��й�ȣ:&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="pass" size=20 value='<?php echo $ViewSignTy_pass?>'>
</td>
</tr>
<?php } ?>

<tr>
<td>&nbsp;</td>
<td>
<?php if ($ModifyCode){?>
<input type='submit' value='���� �մϴ�.'>
<?}else{?>
<input type='submit' value='��� �մϴ�.'>
<?php } ?>
</td>
</tr>

</table>


</form>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if($mode=="SinFormModifyOk"){

if($ModifyCode=="ok"){$TOrderStyle="7";}else{$TOrderStyle="6";}
$ModifyCode="$no";

$result= mysql_query("select * from MlangOrder_PrintAuto where no='$ModifyCode'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) 
{   
$GF_upfile="$row[ThingCate]";  
}

}else{echo("<p align=center><b>DB �� $ModifyCode �� ��� �ڷᰡ ����.</b></p>"); exit;}

// �ڷḦ ���ε��� ������ ���� �����ش�.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../MlangOrder_PrintAuto/upload/$no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($GF_upfile){if($photofileModify){if($photofile){
$upload_dir="../../MlangOrder_PrintAuto/upload/$no"; include"upload.php";
unlink("../../MlangOrder_PrintAuto/upload/$no/$GF_upfile");
}}else{$photofileNAME="$GF_upfile";}
}else{if($photofile){$upload_dir="../../MlangOrder_PrintAuto/upload/$no"; include"upload.php";}}

$query ="UPDATE MlangOrder_PrintAuto SET OrderStyle='$TOrderStyle', ThingCate='$photofileNAME', pass='$pass' WHERE no='$no'";
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

}
mysql_close($db);

		exit;
}
?>


<?php
if($mode=="AdminMlangOrdert"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////
 include"../title.php";
?>

<head>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=680,availHeight=400);
</script>

<script language=javascript>
function MlangFriendSiteCheckField()
{
var f=document.MlangFriendSiteInfo;

if((f.MlangFriendSiteInfo[0].checked==false) && (f.MlangFriendSiteInfo[1].checked==false)){
   alert('������ �������ּ���');
   return false;
 }

if (f.OrderName.value == "") {
alert("�ֹ��ڼ����� �Է����ּ���");
f.OrderName.focus();
return false;
}

if (f.Designer.value == "") {
alert("��� �����̳ʸ� �Է����ּ���");
f.Designer.focus();
return false;
}

if (f.OrderStyle.value == "0") {
alert("���ó���� �������ּ���");
f.OrderStyle.focus();
return false;
}

if (f.date.value == "") {
alert("�ֹ���¥�� �Է����ּ���\n\n���콺�� �� ������ �ڵ��Է�â�� ���ɴϴ�.");
f.date.focus();
return false;
}

if (f.photofile.value == "") {
alert("���ε��� �̹����� �÷��ֽñ� �ٶ��ϴ�.");
f.photofile.focus();
return false;
}

<?php
include"$T_DirFole";
if($View_SignMMk=="yes"){  // �߰��� �����þ� ��� �Է� ���
?>

if (f.pass.value == "") {
alert("����� ��й�ȣ�� �Է��� �ֽñ� �ٶ��ϴ�.");
f.pass.focus();
return false;
}

<?php
}
?>

}

//////////////// �̹��� �̸����� //////////////////////////////////
/* �ҽ�����: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {

Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
Mlangwindow.document.open();
Mlangwindow.document.write("<html><head><title>�̹��� �̸�����</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='������ �ݱ�' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}

// �ҽ�����: http://www.script.ne.kr - Mlang
// ���� �����Ͻ������ſ�.........*^^*
// HONG : ��ũ��Ʈ ���� ǥ��ȭ��Ű�� �����ϰ�� �������� ���� �ִ� inThing()�Լ��� �ϳ��� ���.
function MlangFriendSiteInfocheck()
{
	f=document.MlangFriendSiteInfo;
	if (f.MlangFriendSiteInfoS[0].checked==true){
		ThingNoVal="<select name='Thing' OnChange='inThing(this.value)'><?php
		include"../../mlangprintauto/ConDb.php";
		if ( $ConDb_A) {
			$OrderCate_LIST_script = split(":", $ConDb_A);
			$k = 0;
			while($k < sizeof($OrderCate_LIST_script)) {

							  if($OrderCate=="$OrderCate_LIST_script[$k]"){
									echo "<OPTION VALUE='$OrderCate_LIST_script[$k]' selected style='background-color:#000000; color:#FFFFFF;'>$OrderCate_LIST_script[$k]</OPTION>";
								   }else{
									   echo "<OPTION VALUE='$OrderCate_LIST_script[$k]'>$OrderCate_LIST_script[$k]</OPTION>";
												   }

				$k++;
			} 
		} 
		?></select>"
		document.getElementById('Mlang_go').innerHTML = ThingNoVal;

	}
	if (f.MlangFriendSiteInfoS[1].checked==true){
		ThingNoVal="<INPUT TYPE='text' NAME='Thing' size='30' OnBlur='inThing(this.value)'>";
		document.getElementById('Mlang_go').innerHTML = ThingNoVal;
	}
}


function inThing(HYO){
	f=document.MlangFriendSiteInfo;
	f.ThingNo.value=HYO;
}


</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
<SCRIPT LANGUAGE=JAVASCRIPT src='../js/exchange.js'></SCRIPT>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='<?php echo $Bgcolor_1?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MlangFriendSiteCheckField()' action='<?php echo $PHP_SELF?>'>

<INPUT TYPE="hidden" name='mode' value='AdminMlangOrdertOk'>
<INPUT TYPE="hidden" name='no' value='<?php echo $no?>'>
<?php if ($ModifyCode){?><INPUT TYPE="hidden" name='ModifyCode' value='ok'><?php } ?>

<tr>
<td bgcolor='#6699CC' colspan=2><font style='color:#FFFFFF; font:bold;'>����/�þ� - ���/����</font></td>
</td>

<tr>
<td bgcolor='#6699CC' align=right>����&nbsp;</td>
<td>
<input type="radio" name="MlangFriendSiteInfoS" onClick='MlangFriendSiteInfocheck()'>���ùڽ�
<input type="radio" name="MlangFriendSiteInfoS" onClick='MlangFriendSiteInfocheck()'>�����Է�
<input type='hidden' name='ThingNo'>
<BR>
     <table border=0 align=center width=100% cellpadding=5 cellspacing=0>
       <tr>
         <td id='Mlang_go'></td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>�ֹ��μ���&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="OrderName" size=20> 
<font style='color:#363636; font-size:8pt;'>(�ֹ��ڼ����� ����ڰ� �˻��ϴ� �ڵ� ������ �Ǽ� ���� �Է��ϼ���)</font>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>��� �����̳�&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="Designer" size=20> 
</td>
</tr>


<tr>
<td bgcolor='#6699CC' align=right>���ó��&nbsp;</td>
<td>
<select name='OrderStyle'>
<option value='0'>:::����:::</option>
<option value='6'>�þ�</option>
<option value='7'>����</option>
</select>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>�ֹ���¥&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="date" size=20 onClick="Calendar(this);">
<font style='color:#363636; font-size:8pt;'>(�Է¿�:2005-08-10 * ���콺�� �������� �ڵ��Է�â ���� * )</font>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>�̹��� �ڷ�&nbsp;</td>
<td>
<INPUT TYPE="hidden" NAME="photofileModify" value='ok'>
<INPUT type="file" Size=45 name="photofile" onChange="Mlamg_image(this.value)">
</td>
</tr>

<?php
if($View_SignMMk=="yes"){  // �߰��� �����þ� ��� �Է� ���
?>
<tr>
<td bgcolor='#6699CC' align=right>��й�ȣ&nbsp;</td>
<td>
<INPUT type="text" Size=25 name="pass">
</td>
</tr>
<?php } ?>

<tr>
<td align=center colspan=2>
<?php if ($ModifyCode){?>
<input type='submit' value='���� �մϴ�.'>
<?}else{?>
<input type='submit' value='��� �մϴ�.'>
<?php } ?>
</td>

</table>


</form>


<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if($mode=="AdminMlangOrdertOk"){

$ToTitle="$ThingNo";
include"../../mlangprintauto/ConDb.php";

if(!$ThingNoOkp){$ThingNoOkp="$ThingNo";}else{$ThingNoOkp="$View_TtableB";}

$Table_result = mysql_query("SELECT max(no) FROM MlangOrder_PrintAuto");
	if (!$Table_result) {
		echo "
			<script>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($Table_result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   

// �ڷḦ ���ε��� ������ ���� �����ش�.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../MlangOrder_PrintAuto/upload/$new_no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($photofile){$upload_dir="$dir"; include"upload.php";}

// ��� ���� �ڷ� ����
$dbinsert ="insert into MlangOrder_PrintAuto values('$new_no',
'$ThingNoOkp', 
'$ImgFolder', 
'$Type_1
$Type_2
$Type_3
$Type_4
$Type_5
$Type_6',
'$money_1',
'$money_2',	
'$money_3',	
'$money_4',	
'$money_5',	
'$OrderName',   
'$email',
'$zip', 
'$zip1',
'$zip2',
'$phone',   
'$Hendphone',
'$delivery', 
'$bizname',
'$bank',
'$bankname',
'$cont', 
'$date',
'$OrderStyle',
'$photofileNAME',
'$pass',
'',
'$Designer'
)";

//echo $dbinsert; exit;
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('\\n������ ���������� ���� �Ͽ����ϴ�.\\n');
		 opener.parent.location.reload();
		 window.self.close();
		</script>
	");

mysql_close($db);
		exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>



