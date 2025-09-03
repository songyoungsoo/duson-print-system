<script language='javascript'>
self.moveTo(0,0)
self.resizeTo(availWidth=650,screen.availHeight)
</script>

<?php
// 2006��3��18�� ������Ʈ (�����α׷���  ��ȹ���� ������� ���̺����� �����մ�)
//�׸��Ͽ� ������ ���´� �������� Mlang_bbs_link�� ���װ������� ���� ������ 
// Mlang_bbs_secret �� �װ��� �����Ų��...


// ���� ȭ���� ��û���� ������ �������� ���׷��̵� �Ѵ�.


if($mode=="modify_ok"){

include"../../db.php";

if($HH_code=="text"){ // -------------------------------------------------------------------------------------//
$query ="UPDATE Mlang_${id}_Results SET Mlang_bbs_member='$main', Mlang_bbs_link='$Mlang_bbs_link', Mlang_bbs_title='$title', Mlang_bbs_connent='$connent', Mlang_bbs_secret='$Mlang_bbs_cateTr', Mlang_bbs_reply='$Y8y_year' WHERE Mlang_bbs_no='$no'";
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

}else{ // -------------------------------------------------------------------------------------//


// ���� ���� ���� ó���Ѵ�....////////////////////////////////////////
if($Sofileset=="yes"){
// �űԷ� ���� �ڷ�� ������ ���� ���ѹ�����.
include "../int/upload.inc";                 // ���ε� �Լ� include

$forbid_ext = array("php","asp","jsp","inc","c","cpp","sh");

// �װ����������� �������Ѽ� �װ� ȣ���ع����� �� ����Ÿ���̽����� ������ �ʿ䰡 ����.
$result=func_multi_upload($upfile, $upfile_name, $upfile_size, $upfile_type, "../../results/upload/$id/$no/", $forbid_ext);
}else{
$result="��";
}


if ($result) {


if($checkbox_bigfile){ //^^---------------------^^
 
// ���� �ڷ��� �Է� ó���Ѵ�....
if($MlangFriendSiteInfo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/upload.php";  
}else{$BigUPFILENAME="$BigupfileLink";}

}else{$BigUPFILENAME="$bigfile_name";} //^^---------------------------------------------------------^^

if($checkbox_bigfileTwo){ //^^---------------------^^
// ���� �ڷ��� �Է� ó���Ѵ�....
if($MlangFriendSiteInfoTwo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/uploadTwo.php";  
}else{$BigUPFILENAMETwo="$BigupfileTwoLink";}

}else{$BigUPFILENAMETwo="$bigfile_nameTwo";} //^^---------------------------------------------------------^^
	
// ���� ���� �ΰ��� ���� �ִ� �̰� �� ó�� ���־�� �Ѵ� ������ �浹�� �Ͼ��.
$query ="UPDATE Mlang_${id}_Results SET Mlang_bbs_member='$main', Mlang_bbs_link='$BigUPFILENAMETwo', Mlang_bbs_file='$BigUPFILENAME', Mlang_bbs_title='$title', Mlang_bbs_connent='$connent', Mlang_bbs_secret='$Mlang_bbs_cateTr', Mlang_bbs_reply='$Y8y_year' WHERE Mlang_bbs_no='$no'";


$result_date= mysql_query($query,$db);
	if(!$result_date) {
		echo "
			<script language=javascript>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1);
			</script>";
		exit;

} else {}

mysql_close($db);


// üũ�� �ڷ�� ���� ó�� �ع�����  ���� �ڷᰡ 100���� �ȳѰ��� ��������
$i=0;
while( $i < 100) 
{ 
$temp = "checkbox_".$i; $get_temp=$$temp;
$Filetemp = "file_".$i; $Fileget_temp=$$Filetemp;
if($get_temp){ unlink("../../results/upload/$id/$no/$Fileget_temp"); }
$i=$i+1;
}

// bigfile_name �� ����ó���Ѵ�...
if($MlangFriendSiteInfo=="file"){
if($checkbox_bigfile){ unlink("../../results/upload/$id/$bigfile_name"); }
}

//�Ϸ� �޼����� ������ �������� �̵� ��Ų��
echo ("
		<script language=javascript>
		alert('\\n$result ���� ������ ���� ���ε� �ϰ� �ڷḦ ���������� ���� �Ͽ����ϴ�.\\n\\n')
        opener.parent.location.reload();
		</script>
		<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=modify&id=$id&no=$no'>
	");
		exit;


} else {
     echo "
     <script language='javascript'>
     alert('���������� ������ ���ε���� �ʾҽ��ϴ�.\\n\\n�� �����Ͽ� �ֽñ� �ٶ��ϴ�..');
     history.go(-1)
     </script>";
exit;
}

// ���� ���ε� ��//////////////////////////////////////////////////////





} // -------------------------------------------------------------------------------------//

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="submit_ok"){

include"../../db.php";


if($HH_code=="text"){ // -------------------------------------------------------------------------------------//
	$result = mysql_query("SELECT max(Mlang_bbs_no) FROM Mlang_${id}_Results");
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
//���� �Է�
$date=date("Y-m-d H:i;s");
$dbinsert ="insert into Mlang_${id}_Results values('$new_no',
'$main',
'$title',
'',
'$connent',
'$Mlang_bbs_link',
'',
'',
'0',
'0',
'$Mlang_bbs_cateTr',
'$Y8y_year', 
'$date'
)";
$result_insert= mysql_query($dbinsert,$db);

//�Ϸ� �޼����� ������ �������� �̵� ��Ų��
echo ("
		<script language=javascript>
		alert('\\n���������� ������ ���� �Ǿ����ϴ�.\\n\\n')
        opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=submit&id=$id'>
		");
		exit;

}else{ // -------------------------------------------------------------------------------------//

// ����Ÿ ���̽��� �ڷḦ ���� �Ѵ�...........................
$result = mysql_query("SELECT max(Mlang_bbs_no) FROM Mlang_${id}_Results");
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

// �ڷḦ ���ε��� ������ ���� �����ش�.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../results/upload/$id/$new_no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($Sofileset1=="yes"){

include "../int/upload.inc";                 // ���ε� �Լ� include

$forbid_ext = array("php","asp","jsp","inc","c","cpp","sh");

// �װ����������� �������Ѽ� �װ� ȣ���ع����� �� ����Ÿ���̽����� ������ �ʿ䰡 ����.
$result=func_multi_upload($upfile, $upfile_name, $upfile_size, $upfile_type, "$dir/", $forbid_ext);

if ($result) {


// ���� �ڷ��� �Է� ó���Ѵ�....
if($MlangFriendSiteInfo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/upload.php";  
}else{$BigUPFILENAME="$BigupfileLink";}

// ���� �ڷ��� �Է� ó���Ѵ�....MlangFriendSiteInfoTwo
if($MlangFriendSiteInfoTwo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/uploadTwo.php";  
}else{$BigUPFILENAMETwo="$BigupfileTwoLink";}

############################################
$date=date("Y-m-d H:i;s");
$dbinsert ="insert into Mlang_${id}_Results values('$new_no',
'$main',
'$title',
'',
'$connent',
'$BigUPFILENAMETwo',
'$BigUPFILENAME',
'',
'0',
'0',
'$Mlang_bbs_cateTr',
'$Y8y_year',
'$date'
)";
$result_insert= mysql_query($dbinsert,$db);
############################################

//�Ϸ� �޼����� ������ �������� �̵� ��Ų��
echo ("
		<script language=javascript>
		alert('\\n$result ���� ������ ���ε� �ϰ� �ڷḦ ���������� ���� �Ͽ����ϴ�.\\n\\n')
        opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=submit&id=$id'>
		");
		exit;

} else {
     echo "
     <script language='javascript'>
     alert('���������� ������ ���ε���� �ʾҽ��ϴ�.\\n\\n�� �����Ͽ� �ֽñ� �ٶ��ϴ�..');
     history.go(-1)
     </script>";
exit;
}

}else{ 

// ���� ������ �Է� ó���Ѵ�....
$upload_dir="../../results/upload/$id";
include "../int/upload.php";  

if($MlangFriendSiteInfo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/upload.php";  
}else{$BigUPFILENAME="$Bigupfile";}

// ���� �ڷ��� �Է� ó���Ѵ�....
if($MlangFriendSiteInfoTwo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/uploadTwo.php";  
}else{$BigUPFILENAMETwo="$BigupfileTwoLink";}

############################################
$date=date("Y-m-d H:i;s");
$dbinsert ="insert into Mlang_${id}_Results values('$new_no',
'$main',
'$title',
'',
'$connent',
'$BigUPFILENAMETwo',
'$BigUPFILENAME',
'',
'0',
'0',
'$Mlang_bbs_cateTr',
'$Y8y_year',
'$date'
)";
$result_insert= mysql_query($dbinsert,$db);
############################################

//�Ϸ� �޼����� ������ �������� �̵� ��Ų��
echo ("
		<script language=javascript>
		alert('\\n���� ������ ���ε� �ϰ� �ڷḦ ���������� ���� �Ͽ����ϴ�.\\n\\n')
        opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=submit&id=$id'>
		");
		exit;

}

} // -------------------------------------------------------------------------------------//


}


//////////////////////////////////// �Է°� ó�� ��--------�� ////////////////////////////////////////////////////////////////////////////////////
?>


<?php
include"data_admin_fild.php";

if($mode=="modify"){$DbDir="../.."; include"../../results/view_fild.php";}

include"../title.php";
?>

<script src="../js/coolbar.js" type="text/javascript"></script>

<head>
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

function MemberCheckField()
{
var f=document.FrmUserInfo;

<?php
if ( $DataAdminFild_celect ) {echo("
if (f.Mlang_bbs_cateTr.value == \"0\") {
alert(\"�׸��� ���� ���ּ���..\");
return false;
}

");}
?>


<? 
if($DataAdminFild_item=="text"){
?>

if (f.title.value == "") {
alert("���� �� �Է��Ͽ��ּ���?");
return false;
}
if (f.connent.value == "") {
alert("���� �� �Է��Ͽ��ּ���?");
return false;
}

<? }?>


}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function MlangShowLayerOne(Code) {

      if(Code=="1"){
         document.all.MlangLayerOne_1.style.visibility = "visible";
		 document.all.MlangLayerOne_2.style.visibility = "hidden";
         }
	  if(Code=="2"){
         document.all.MlangLayerOne_2.style.visibility = "visible";
		 document.all.MlangLayerOne_1.style.visibility = "hidden";
         }
}

function MlangShowLayerTwo(Code) {

      if(Code=="1"){
         document.all.MlangLayerTwo_1.style.visibility = "visible";
		 document.all.MlangLayerTwo_2.style.visibility = "hidden";
         }
	  if(Code=="2"){
         document.all.MlangLayerTwo_2.style.visibility = "visible";
		 document.all.MlangLayerTwo_1.style.visibility = "hidden";
         }
}

 
function calc_re(){   // �׸�����
asd=document.forms["FrmUserInfo"];
asd.VateIIIO.value = asd.Mlang_bbs_cateTr.options[asd.Mlang_bbs_cateTr.selectedIndex].value;
} 

function VateIIIO_Submit(){   // �Է�
asd=document.forms["FrmUserInfo"];

      if (asd.VateIIIO.value == "") {
      alert("���Ӱ� �Է��� �׸��� ���� ���ּ���..");
	  asd.VateIIIO.focus();
      return false;
        }
		 if(asd.VateIIIO.value.lastIndexOf(" ") > -1) {
      alert("���Ӱ� �Է��� �׸� ������ ������ �ʽ��ϴ�.");
	  asd.VateIIIO.focus();
      return false;
        }
		if(asd.VateIIIO.value.lastIndexOf("\"") > -1) {
      alert("���Ӱ� �Է��� �׸� �ֵ���ǥ�� ������ �ʽ��ϴ�.");
	  asd.VateIIIO.focus();
      return false;
        }
		if(asd.VateIIIO.value.lastIndexOf("'") > -1) {
      alert("���Ӱ� �Է��� �׸� �ܵ���ǥ�� ������ �ʽ��ϴ�.");
	  asd.VateIIIO.focus();
      return false;
        }
		if(asd.VateIIIO.value.lastIndexOf(":") > -1) {
      alert("���Ӱ� �Է��� �׸� : �� ������ �ʽ��ϴ�.");
	  asd.VateIIIO.focus();
      return false;
        }

var str;
		str='Mlang_bbs_cateTr.php?id=<?php echo $id?>&mode=Submit&F='+asd.VateIIIO.value;
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
}  /////////////////////////////////////////////////////////////////////////////

function VateIIIO_Delete(){   // ����
asd=document.forms["FrmUserInfo"];

      if (asd.VateIIIO.value == "") {
      alert("������ �׸��� ���� ���ּ���..");
      return false;
        }

var str;
		if (confirm("�׸��� �����Ͻø� �׸���� ��� �ڷᰡ ���ÿ� �������ϴ�.\n\n������ �ڷ�� �ι� �ٽ� ���� ���� �ʽ��ϴ�.\n\n������ �ڷᰡ Ȯ���Ͻø� �����Ͻʽÿ�!!")) {
		str='Mlang_bbs_cateTr.php?id=<?php echo $id?>&mode=Delete&F='+asd.VateIIIO.value;
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}

} 

</script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>
<iframe name=Tcal frameborder=0 width=0 height=0></iframe>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0' bgcolor='#999933'>

<form name='FrmUserInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MemberCheckField()' action='<?php echo $PHP_SELF?>'>

<tr>
<td colspan=2>&nbsp;&nbsp;
<font color=white><b>
<?php echo $DataAdminFild_title?> -
<big>
<?php
if($mode=="submit"){echo("�ڷ� ��� �ϱ�");}
if($mode=="modify"){echo("�ڷ� ���� �ϱ�");}
?>
</font></b></big>
</td>
</tr>

<?php
if ( $DataAdminFild_celect ) {

echo("
<tr>
<td class='coolBar' height=30 width=20% align=right><b>�׸�</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>
&nbsp;<select name='Mlang_bbs_cateTr' onChange='calc_re();'>
<OPTION VALUE='0' selected>�Ƽ��� �ϵ�����</OPTION>
");

	$CATEGORY_LIST_script = split(":", $DataAdminFild_celect );
	$k = 0;
	while($k < sizeof($CATEGORY_LIST_script)) {

if($BbsViewMlang_bbs_secret=="$CATEGORY_LIST_script[$k]"){
		echo "<OPTION VALUE='$CATEGORY_LIST_script[$k]' selected style='background-color:#3399CC; color:#FFFFFF;'>$CATEGORY_LIST_script[$k]</OPTION>";	
}else{		echo "<OPTION VALUE='$CATEGORY_LIST_script[$k]'>$CATEGORY_LIST_script[$k]</OPTION>";}

		$k++;
	} 
	echo("</select>");

} 
?>

<INPUT TYPE="text" NAME="VateIIIO" name='40'>
<input type='button' onClick='VateIIIO_Submit();' value='�Է�'><input type='button' onClick='VateIIIO_Delete();' value='����'>
</td></tr>

<? ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($DataAdminFild_item=="text"){
?>

<tr>
<td class='coolBar' height=30 width=20% align=right><b>���ۻ�</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>&nbsp;<Input type='text' name='title' size='50' <?php if ($mode=="modify"){echo("value='$BbsViewMlang_bbs_title'");}?>></td>
</tr>

<tr>
<td class='coolBar' height=30 width=20% align=right><b>���۹�</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>&nbsp;<Input type='text' name='connent' size='70' <?php if ($mode=="modify"){echo("value='$BbsViewMlang_bbs_connent'");}?>></td>
</tr>

<? }else{?>

<?php
echo("
<tr>
<td class='coolBar' height=60 width=20% align=right><b>����Ʈ�ڽ�</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>");

if($mode=="modify"){
	echo("<INPUT TYPE='checkbox' NAME='checkbox_bigfile'><INPUT TYPE='hidden' NAME='bigfile_name' value='$BbsViewMlang_bbs_file'> ����: $BbsViewMlang_bbs_file &nbsp;<BR>&nbsp;&nbsp;&nbsp;<font style='color:#6600CC;'>(�ڷ��������Ͻ÷���üũ�����Է�/üũ�� �ϰ� �ڷ� ���Է��ϸ� �����ڷ� �����˴ϴ�.)</font><BR>");	
	}
?>

<INPUT TYPE="radio" NAME="MlangFriendSiteInfo" value='file' onClick="javascript:MlangShowLayerOne('1');">���� ���ε�
<INPUT TYPE="radio" NAME="MlangFriendSiteInfo" value='link' onClick="javascript:MlangShowLayerOne('2');">���� ��ũ
<BR>
<?$DivS_Width=""; $DivS_height="22";?>
<div id='MlangLayerOne_1' class='coolBar' style="position:absolute; width:<?php echo $DivS_Width?>; height:<?php echo $DivS_height?>; visibility:hidden;">
<input type='file' name='Bigupfile' size=60>
</div>
<div id='MlangLayerOne_2' class='coolBar' style="position:absolute; width:<?php echo $DivS_Width?>; height:<?php echo $DivS_height?>; visibility:hidden;">
<input type='text' name='BigupfileLink' size=75>
</div>

<font style='color:#828282; font-size:8pt; FONT-FAMILY:����; line-height:180%;'>
&nbsp;&nbsp;&nbsp;* ���ε峪 ���ϸ�ũ �� �ϳ��� �Է��ϼž� ���������� ȣ��˴ϴ�..!!
</td>
</tr>

<?php
echo("
<tr>
<td class='coolBar' height=60 width=20% align=right><b>â�ٿ�����</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>");

if($mode=="modify"){
	echo("<INPUT TYPE='checkbox' NAME='checkbox_bigfileTwo'><INPUT TYPE='hidden' NAME='bigfile_nameTwo' value='$BbsViewMlang_bbs_link'> ����: $BbsViewMlang_bbs_link &nbsp;<BR>&nbsp;&nbsp;&nbsp;<font style='color:#6600CC;'>(�ڷ��������Ͻ÷���üũ�����Է�/üũ�� �ϰ� �ڷ� ���Է��ϸ� �����ڷ� �����˴ϴ�.)</font><BR>");	
	}
?>

<INPUT TYPE="radio" NAME="MlangFriendSiteInfoTwo" value='file' onClick="javascript:MlangShowLayerTwo('1');">���� ���ε�
<INPUT TYPE="radio" NAME="MlangFriendSiteInfoTwo" value='link' onClick="javascript:MlangShowLayerTwo('2');">���� ��ũ
<BR>
<div id='MlangLayerTwo_1' class='coolBar' style="position:absolute; width:<?php echo $DivS_Width?>; height:<?php echo $DivS_height?>; visibility:hidden;">
<input type='file' name='BigupfileTwo' size=60>
</div>
<div id='MlangLayerTwo_2' class='coolBar' style="position:absolute; width:<?php echo $DivS_Width?>; height:<?php echo $DivS_height?>; visibility:hidden;">
<input type='text' name='BigupfileTwoLink' size=75>
</div>
<font style='color:#828282; font-size:8pt; FONT-FAMILY:����; line-height:180%;'>
&nbsp;&nbsp;&nbsp;* ���ε峪 ���ϸ�ũ �� �ϳ��� �Է��ϼž� ���������� ȣ��˴ϴ�..!!
</td>
</tr>

<?php
echo("
<tr>
<td class='coolBar' height=30 width=20% align=right><b>���콺�Ǿ�����</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%><input type='hidden' name='Sofileset1' value='yes'>
");

if($mode=="modify"){ // ������ ���� �ڷḦ ��� �Ѵ�............................................................

echo("<input type='hidden' name='Sofileset1' value='yes'>");

// ��ü ���� �� ����. //////////////////////////////////////////////////////////
$dir_path = "../../results/upload/$id/$no";
$dir_handle = opendir($dir_path);

// ��ü ���丮 ������ ����Ѵ�.
$WQ=0;
while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")) {
echo (is_file($dir_path.$tmp) ? "" : "
<table border=1 align=center width=90% cellpadding='5' cellspacing='0'><tr>
<td width=50%><INPUT TYPE='checkbox' NAME='checkbox_$WQ'><INPUT TYPE='hidden' NAME='file_$WQ' value='$tmp'>&nbsp;&nbsp;���ϸ�: $tmp</td>
<td width=50%><img src='$dir_path/$tmp' width=100></td>
</tr></table>
");
}

	$WQ=$WQ+1;
}

closedir($dir_handle);

////////////////////////////////////////////////////////////////////////////////////

echo("<BR>");

} //..............................................................................................................................

?>


<script language="javascript">
function AddFile()
{
var objTbl = document.all["tblAttFiles"];
var objRow = objTbl.insertRow();
var objCell = objRow.insertCell();
objCell.innerHTML =
  "<img src=/img/12345.gif align=absbottom>\n" +
  "<input type=file onChange='CkImageVal()' name=upfile[] size=40>";
document.recalc();
}

function CkImageVal() {
var oInput = event.srcElement;
var fname = oInput.value;
if((/(.jpg|.jpeg|.gif|.png)$/i).test(fname))
  oInput.parentElement.children[0].src = fname;
else
  alert('�̹����� gif, jpg, png ���ϸ� �����մϴ�.');
}
</script>

<table id=tblAttFiles cellspacing=0 border=0>
<tr><td>
   <img src=/img/12345.gif align=absbottom>
   <input type=file name=upfile[] size=40  onChange="CkImageVal()">
</td></tr>
</table>
<input type=hidden value='�����̹��� �Է� �߰�' onclick="AddFile()">

</td></tr>

<tr>
<td class='coolBar' height=30 width=20% align=right><b>����</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>&nbsp;<Input type='text' name='title' size='50' <?php if ($mode=="modify"){echo("value='$BbsViewMlang_bbs_title'");}?>></td>
</tr>

<tr>
<td class='coolBar' height=30 width=20% align=right><b>����</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>&nbsp;<TEXTAREA NAME="connent" ROWS="5" COLS="50"><?php if ($mode=="modify"){echo("$BbsViewMlang_bbs_connent");}?></TEXTAREA>
</td>
</tr>


<? } ?>

</table>

<INPUT TYPE='hidden' name='HH_code' value='<?php echo $DataAdminFild_item?>'>
<?php
if($mode=="submit"){echo("
<INPUT TYPE='hidden' name='mode' value='submit_ok'>
<INPUT TYPE='hidden' name='id' value='$id'>
");
}
if($mode=="modify"){echo("
<INPUT TYPE='hidden' name='mode' value='modify_ok'>
<INPUT TYPE='hidden' name='id' value='$id'>
<INPUT TYPE='hidden' name='no' value='$no'>
");
}
?>

<p align=center>
<input type='submit' value='<?php if ($mode=="submit"){echo("�Է��մϴ�..");}if($mode=="modify"){echo("�����մϴ�..");}?>'>
<input type='button' onClick='javascript:window.close();' value='â�ݱ�-CLOSE'>
<BR><BR>
</p>

</body>
</html>