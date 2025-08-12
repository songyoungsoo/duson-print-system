<html>
<head>
<title>MlangBizMap���α׷�</title>
<meta http-equiv='Content-type' content='text/html; charset=euc-kr'>
<META NAME='KEYWORDS' CONTENT='MlangBizMap���α׷�'>
<meta name='author' content='Mlang'>
<meta name='classification' content='MlangBizMap���α׷�'>
<meta name='description' content='MlangBizMap���α׷�'>
<!--------------------------------------------------------------------------------
     ���α׷� ������-�������÷���2
     ���α׷����: PHP, javascript, DHTML, html
     ������: Mlang - ����: webmaster@script.ne.kr
     URL: http://www.websil.net , http://www.script.ne.kr
----------------------------------------------------------------------------------->

<style>
body,td,input,select,submit {font-family:����; font-size: 9pt;; color:#000000; font-weight:none; line-height: normal;}
.td11 {font-family:����; font-size: 9pt;; color:#FFFFFF; font-weight:bold; line-height: normal;}
.td1 {font-family:����; font-size: 9pt;; color:#FFFFFF; font-weight:bold; line-height: normal;}
.td2 {font-family:����; font-size: 9pt;; color:#008080; font-weight:none; line-height:130%;}
</style>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<?php
if($mode=="view"){

$result= mysql_query("select * from MlangOrder where no='$no'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) 
{
$BBAdminSelect="$row[AdminSelect]";	
?>


<BR>
<table border=0 align=center width=90% cellpadding='0' cellspacing='1' bgcolor='#65B1B1'>
<tr><td valign=top>

<table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='#FFFFFF'>
<tr>
<td bgcolor='#65B1B1' width=100 class='td1' align='left'>&nbsp;��  ��&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?php echo $row[name]?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;��  ��&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[nai]?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;��������&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[house]?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;��ȭ��ȣ&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[phone]?>
</td>
</tr>


<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;��㰡�ɽð�&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[si]?>
</td>
</tr>


<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;��� �з�&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[cont_1]?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;��� ����&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php
        $CONTENT=$row[cont_2];
		$CONTENT = eregi_replace("<", "&lt;", $CONTENT);
		$CONTENT = eregi_replace(">", "&gt;", $CONTENT);
		$CONTENT = eregi_replace("\"", "&quot;", $CONTENT);
		$CONTENT = eregi_replace("\|", "&#124;", $CONTENT);
		$CONTENT = eregi_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = eregi_replace("\r\n", "<BR>", $CONTENT);
		$connent_text=$CONTENT;	
echo("$connent_text");
?>
</tr>

</table>

</td></tr></table>

<p align=center>
<input type='button' onClick='javascript:window.close();' value='â�ݱ�-CLOSE' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
</p>

<?php
}

}else{echo("<p align=center><b>��� �ڷᰡ ����.</b></p>");}

mysql_close($db); 


///////^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^/////////

if($BBAdminSelect=="no"){

include"db.php";
$query ="UPDATE MlangOrder SET AdminSelect='yes' WHERE no='$no'";
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
         opener.parent.location.reload();
		</script>
	");
		exit;

}

mysql_close($db);
}



exit;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

include"db.php";
$result = mysql_query("DELETE FROM $table WHERE no='$no'");
mysql_close();

	echo ("
		<script language=javascript>
		alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($mode=="AdminSiteSubmit"){
$Bgcolor_1="#FFFFFF";
$Bgcolor_2="#65B1B1";
$Bgcolor_3="#FFFFFF";
$align_td1="left";
$InputStyle="style='font-size:10pt; background-color:#DAF8F4; color:#000000; border-style:solid; border:1 solid $Bgcolor_2'";
include"db.php";

if($ModifyCode){include"ViewFild.php";}
?>

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

function MlangFriendSiteCheckField()
{
var f=document.MlangFriendSiteInfo;

<?php if  ( $AdCate ) {?>
if (f.cate.value == "0") {
alert("����Ʈ�� ���ݿ� �´� ī�װ����� ���� �Ͽ� �ּ���.. *^^*");
f.cate.focus();
return false;
}
<?php } ?>

if (f.bizname.value == "") {
alert("����� �� �Է��Ͽ� �ּ��� *^^*");
f.bizname.focus();
return false;
}

if (f.name.value == "") {
alert("��ǥ�� ���� �� �Է��Ͽ� �ּ��� *^^*");
f.name.focus();
return false;
}

if (f.tel.value == "") {
alert("TEL �� �Է� �Ͽ� �ּ���.. *^^*");
f.tel.focus();
return false;
}

if (f.fax.value == "") {
alert("FAX �� �Է� �Ͽ� �ּ���.. *^^*");
f.fax.focus();
return false;
}

if (f.zip.value == "") {
alert("�����ּ� �� �Է� �Ͽ� �ּ���.. *^^*");
f.zip.focus();
return false;
}


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

<style>
.td1 {font-family:����; font-size: 9pt;; color:#FFFFFF; font-weight:bold; line-height: normal;}
.td2 {font-family:����; font-size: 9pt;; color:#008080; font-weight:none; line-height:130%;}
</style>

</head>

<BR>
<table border=0 align=center width=90% cellpadding='0' cellspacing='1' bgcolor='<?php echo $Bgcolor_2?>'>
<tr><td valign=top>

<table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='<?php echo $Bgcolor_1?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MlangFriendSiteCheckField()' action='<?php echo $PHP_SELF?>'>
<?php if ($ModifyCode){?>
<INPUT TYPE="hidden" name='mode' value='FormModifyOk'>
<INPUT TYPE="hidden" name='no' value='<?php echo $ModifyCode?>'>
<?}else{?>
<INPUT TYPE="hidden" name='mode' value='FormSubmitOk'>
<?php } ?>

<?php if  ( $AdCate ) {?>
<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;���װ���&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<?php
	echo "<select name='cate' $InputStyle><OPTION VALUE='0' selected>�Ƽ����ϼ����</OPTION>";
	$CATEGORY_LIST_script = split(":", $AdCate);
	$k = 0;
	while($k < sizeof($CATEGORY_LIST_script)) {

if($GF_cate=="$CATEGORY_LIST_script[$k]"){
echo "<OPTION VALUE='$CATEGORY_LIST_script[$k]' selected style='background-color:#000000; color:#FFFFFF;'>$CATEGORY_LIST_script[$k]</OPTION>";}else{
		echo "<OPTION VALUE='$CATEGORY_LIST_script[$k]'>$CATEGORY_LIST_script[$k]</OPTION>";
} 

		$k++;
	} 
	echo"</select>\n";
?>
</td>
</tr>
<?php } ?>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;�����&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<input type='text' name='bizname' maxLength='50' size='50' <?php echo $InputStyle?> <?php if ($ModifyCode){echo("value='$GF_bizname'");}?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;��ǥ�� ����&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<input type='text' name='name' maxLength='100' size='20' <?php echo $InputStyle?> <?php if ($ModifyCode){echo("value='$GF_name'");}?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;TEL&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<input type='text' name='tel' maxLength='100' size='20' <?php echo $InputStyle?> <?php if ($ModifyCode){echo("value='$GF_tel'");}?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;FAX&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<input type='text' name='fax' maxLength='100' size='20' <?php echo $InputStyle?> <?php if ($ModifyCode){echo("value='$GF_fax'");}?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;�����ּ�&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<input type='text' name='zip' maxLength='200' size='50' <?php echo $InputStyle?> <?php if ($ModifyCode){echo("value='$GF_zip'");}?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;���� MAP&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<?php if ($ModifyCode){if($GF_upfile){?>
<INPUT TYPE="checkbox" NAME="photofileModify">������ �����Ͻ÷��� üũ�� ���ּ���<BR>
<img src='../../BizMap/upload/<?php echo $GF_upfile?>' width=100 height=40>
<?} }?>
<INPUT type="file" Size=35 name="photofile" onChange="Mlamg_image(this.value)" <?php echo $InputStyle?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;���ʻ���-1&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<?php if ($ModifyCode){if($GF_upfile1){?>
<INPUT TYPE="checkbox" NAME="photofileModify1">������ �����Ͻ÷��� üũ�� ���ּ���<BR>
<img src='../../BizMap/upload/<?php echo $GF_upfile1?>' width=100 height=40>
<?} }?>
<INPUT type="file" Size=35 name="photofile1" onChange="Mlamg_image(this.value)" <?php echo $InputStyle?>>
<BR>�̹�����ũ��� width=230, height=130 �� �����Ͽ� ���߾��ּ���
<BR>(ū�����ϰ�� �ڵ����� width=230, height=130 �� ������ - ���ʻ��� 1,2,3 ����)
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;���ʻ���-2&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<?php if ($ModifyCode){if($GF_upfile2){?>
<INPUT TYPE="checkbox" NAME="photofileModify2">������ �����Ͻ÷��� üũ�� ���ּ���<BR>
<img src='../../BizMap/upload/<?php echo $GF_upfile2?>' width=100 height=40>
<?} }?>
<INPUT type="file" Size=35 name="photofile2" onChange="Mlamg_image(this.value)" <?php echo $InputStyle?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;���ʻ���-3&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<?php if ($ModifyCode){if($GF_upfile3){?>
<INPUT TYPE="checkbox" NAME="photofileModify3">������ �����Ͻ÷��� üũ�� ���ּ���<BR>
<img src='../../BizMap/upload/<?php echo $GF_upfile3?>' width=100 height=40>
<?} }?>
<INPUT type="file" Size=35 name="photofile3" onChange="Mlamg_image(this.value)" <?php echo $InputStyle?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;���빮������&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<INPUT TYPE="radio" NAME="Mstyle" value='br' <?php if ($GF_style=="br"){echo("checked");}?>>�ڵ�BR
<INPUT TYPE="radio" NAME="Mstyle" value='html' <?php if ($GF_style=="html"){echo("checked");}?>>HTML�����Է�
</td>
</tr>

<tr>
<td bgcolor='<?php echo $Bgcolor_2?>' width=100 class='td1' align='<?php echo $align_td1?>'>&nbsp;����Ʈ ����&nbsp;</td>
<td bgcolor='<?php echo $Bgcolor_3?>'>
<TEXTAREA NAME="cont" ROWS="13" COLS="60" <?php echo $InputStyle?>><?php if ($ModifyCode){echo("$GF_cont");}?></TEXTAREA>
</td>
</tr>

</table>

</td></tr></table>


<p align=center>
<?php if ($ModifyCode){?>
<input type='submit' value='���� �մϴ�.' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
<?}else{?>
<input type='submit' value='�Է� �մϴ�.' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
<?php } ?>
</p>

</form>

<?php
		exit;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="FormSubmitOk"){

if($photofile){$upload_dir="../../BizMap/upload"; include"upload.php";}
if($photofile1){$upload_dir="../../BizMap/upload"; include"upload_1.php";}
if($photofile2){$upload_dir="../../BizMap/upload"; include"upload_2.php";}
if($photofile3){$upload_dir="../../BizMap/upload"; include"upload_3.php";}

include"db.php";
	$result = mysql_query("SELECT max(no) FROM $table");
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
$dbinsert ="insert into $table values('$new_no',
'$cate',
'$bizname',
'$name',
'$tel',
'$fax',
'$zip',
'$photofileNAME',
'$photofile1NAME',
'$photofile2NAME',
'$photofile3NAME',
'$Mstyle',
'$cont'
)";
$result_insert= mysql_query($dbinsert,$db);

//�Ϸ� �޼����� ������ �������� �̵� ��Ų��
echo ("
		<script language=javascript>
		alert('\\n���������� ������ ���� �Ǿ����ϴ�.\\n\\n')
		opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=AdminSiteSubmit'>
		");

		exit;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="FormModifyOk"){

include"db.php";
$ModifyCode="$no";
include"ViewFild.php";

if($GF_upfile){if($photofileModify){if($photofile){
$upload_dir="../../BizMap/upload"; include"upload.php";
unlink("../../BizMap/upload/$GF_upfile");
}}else{$photofileNAME="$GF_upfile";}
}else{if($photofile){$upload_dir="../../BizMap/upload"; include"upload.php";}}
/////////////////////////////////////////////////////////////////////////////////////
if($GF_upfile1){if($photofileModify1){if($photofile1){
$upload_dir="../../BizMap/upload"; include"upload_1.php";
unlink("../../BizMap/upload/$GF_upfile1");
}}else{$photofile1NAME="$GF_upfile1";}
}else{if($photofile1){$upload_dir="../../BizMap/upload"; include"upload_1.php";}}
/////////////////////////////////////////////////////////////////////////////////////
if($GF_upfile2){if($photofileModify2){if($photofile2){
$upload_dir="../../BizMap/upload"; include"upload_2.php";
unlink("../../BizMap/upload/$GF_upfile2");
}}else{$photofile2NAME="$GF_upfile2";}
}else{if($photofile2){$upload_dir="../../BizMap/upload"; include"upload_2.php";}}
/////////////////////////////////////////////////////////////////////////////////////
if($GF_upfile3){if($photofileModify3){if($photofile3){
$upload_dir="../../BizMap/upload"; include"upload_3.php";
unlink("../../BizMap/upload/$GF_upfile3");
}}else{$photofile3NAME="$GF_upfile3";}
}else{if($photofile3){$upload_dir="../../BizMap/upload"; include"upload_3.php";}}
/////////////////////////////////////////////////////////////////////////////////////

$query ="UPDATE $table SET cate='$cate',  bizname='$bizname',  name='$name',  tel='$tel',  fax='$fax', zip='$zip',  photo='$photofileNAME',  photo1='$photofile1NAME',  photo2='$photofile2NAME',  photo3='$photofile3NAME',  cont_style='$Mstyle',  cont='$cont'  WHERE no='$no'";
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
$M123="..";
include"../top.php"; 
?>

<?include"db.php";?>

<head>
<script>
function Member_Admin_Del(no){
	if (confirm(+no+'�� �� ��� �ڷḦ ���� �Ͻðڽ��ϱ�..?\n\n�ѹ� ������ �ڷ�� ���� ���� ������ ������ �����ּ���.............!!')) {
		str='<?php echo $PHP_SELF?>?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}

function MM_jumpMenu(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>

</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='0' class='coolBar'>
<tr>
<td align=left>
<?php
if ( $AdCate ) {
	echo "<select onChange=\"MM_jumpMenu('parent',this,0)\" style='background-color:#D8EBFC;'><OPTION selected>��ī�װ������κ����</OPTION>";
	$CATEGORY_LIST_script = split(":", $AdCate);
	$k = 0;
	while($k < sizeof($CATEGORY_LIST_script)) {

if($cate=="$CATEGORY_LIST_script[$k]"){
			echo "<OPTION VALUE='$PHP_SELF?cate=$CATEGORY_LIST_script[$k]' selected style='background-color:#000000; color:#FFFFFF;'>$CATEGORY_LIST_script[$k]</OPTION>";
}else{
		echo "<OPTION VALUE='$PHP_SELF?cate=$CATEGORY_LIST_script[$k]'>$CATEGORY_LIST_script[$k]</OPTION>";
}

		$k++;
	} 

if($cate){echo"<option value='$PHP_SELF'>�� ��ü��Ϻ���</option></select>\n";}else{echo"</select>\n";}
} 
?>
������� Ŭ���Ͻø� �ö��� �ִ� �������� �ٷ� ���Ǽ� �ֽ��ϴ�..
</td>
<td align=right>
<input type='button'  onClick="javascript:popup=window.open('<?php echo $PHP_SELF?>?mode=AdminSiteSubmit', 'MlangFriendSiteSubmit','width=680,height=680,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='�ű����� �ڷ����ϱ�'>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='#6699CC'>
<tr bgcolor='#6699CC'>
<td align=center class='td11'>��Ϲ�ȣ</font></td>
<td align=center class='td11'>ī�װ���</font></td>
<td align=center class='td11'>�����</font></td>
<td align=center class='td11'>��ǥ�� ����</td>
<td align=center class='td11'>TEL</td>
<td align=center class='td11'>����</td>
<tr>

<?php
if($cate){$Mlang_query="select * from $table  where cate='$cate'";}else{$Mlang_query="select * from $table";}

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 15;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 

$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);
$rows=mysql_num_rows($result);
if($rows){


while($row= mysql_fetch_array($result)) 
{ 
?>

<tr bgcolor='#FFFFFF'>
<td align=center><?php echo $row[no]?></td>
<td align=center><?php echo $row[cate]?></td>
<td align=center><a href='/new/BizMap/index.php?cate=<?php echo $row[cate]?>&NoCode=<?php echo $row[no]?>' target='_blank'><?php echo $row[bizname]?></a></td>
<td align=center><?php echo $row[name]?></td>
<td align=center><?php echo $row[tel]?></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?php echo $PHP_SELF?>?mode=AdminSiteSubmit&ModifyCode=<?php echo $row[no]?>', 'MlangFFFiteModify','width=680,height=680,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' ���� '>
<input type='button' onClick="javascript:Member_Admin_Del('<?php echo $row[no]?>');" value=' ���� '>
</td>
<tr>

<?php
		$i=$i+1;
} 


}else{

if($search){
echo"<tr><td colspan=10 bgcolor='#FFFFFF'><p align=center><BR><BR>���� �˻� �ڷ����</p></td></tr>";
}else{
echo"<tr><td colspan=10 bgcolor='#FFFFFF'><p align=center><BR><BR>��� �ڷ����</p></td></tr>";
}

}

?>


</table>



<p align='center'>

<?php
if($rows){

if($cate){$mlang_pagego="cate=$cate";}else{$mlang_pagego="";}

$pagecut= 7; 
$one_bbs= $listcut*$pagecut; 
$start_offset= intval($offset/$one_bbs)*$one_bbs; 
$end_offset= intval($recordsu/$one_bbs)*$one_bbs; 
$start_page= intval($start_offset/$listcut)+1; 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 
if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[����]</a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset){
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;"; 
}else{echo("&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;"); } 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[����]...</a>"; 
} 
echo "�Ѹ�ϰ���: $end_page ��"; 


}

mysql_close($db); 
?> 

</p>
<!------------------------------------------- ����Ʈ ��----------------------------------------->

<?php
include"../down.php";
?>