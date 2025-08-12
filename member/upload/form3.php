<BR>
<?$ledtColor="width='110' align='center' bgcolor='#EDEFF3'";?>
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

///////////////// id �� �ߺ�Ȯ���Ѵ�,. ////////////////////////////////////////////////////////////////
<?php if
(!$MdoifyMode=="view"){?>
function idcheck()
{
var f=document.JoinInfo;
if (f.id.value == "") {
alert("����Ͻ� ȸ�� ID�� �Է��� �ּ���. ");
f.id.focus();
return false;
}
if (!TypeCheck(f.id.value, ALPHA+NUM)) {
alert("����Ͻ� ȸ�� ID�� ������ �� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.id.focus();
return false;
}
if ((f.id.value.length < 4) || (f.id.value.length > 12)) {
alert("����Ͻ� ȸ�� ID�� 4���� �̻�, 12���� �����̿��� �մϴ�.");
f.id.focus();
return false;
}

winobject = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=100,left=100");
winobject.document.location = "/member/id_check.php?id=" + f.id.value;
winobject.focus();
}
<?php } ?>
///////////////// �ּҰ˻�â�� �ٿ��. ////////////////////////////////////////////////////////////////

function zipcheck()
{
window.open("/member/zip.php?mode=search","zip","scrollbars=yes,resizable=yes,width=550,height=510,top=10,left=50");
}

///////////////////////////////////////////////////////////////////////////////////////////////////////

function JoinCheckField()
{
var f=document.JoinInfo;

<?php if
(!$MdoifyMode=="view"){?>
if (f.id.value == "") {
alert("����Ͻ� ȸ�� ID�� �Է��� �ּ���. ");
f.id.focus();
return false;
}
if (!TypeCheck(f.id.value, ALPHA+NUM)) {
alert("����Ͻ� ȸ�� ID�� ������ �� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.id.focus();
return false;
}
if ((f.id.value.length < 4) || (f.id.value.length > 12)) {
alert("����Ͻ� ȸ�� ID�� 4���� �̻�, 12���� �����̿��� �մϴ�.");
f.id.focus();
return false;
}
<?php } ?>
if (f.pass1.value == "") {
alert("����Ͻ� ��й�ȣ�� �Է��� �ּ���. ");
f.pass1.focus();
return false;
}
if (!TypeCheck(f.pass1.value, ALPHA+NUM)) {
alert("��й�ȣ�� ������ �� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.pass1.focus();
return false;
}
if ((f.pass1.value.length < 4) || (f.pass1.value.length > 12)) {
alert("��й�ȣ�� 4���� �̻�, 12���� �����̿��� �մϴ�.");
f.pass1.focus();
return false;
}
if (f.pass2.value == "") {
alert("����Ͻ� ��й�ȣ��Ȯ�θ� �Է��� �ּ���. ");
f.pass2.focus();
return false;
}
if (!TypeCheck(f.pass2.value, ALPHA+NUM)) {
alert("��й�ȣ��Ȯ���� ������ �� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.pass2.focus();
return false;
}
if ((f.pass2.value.length < 4) || (f.pass2.value.length > 12)) {
alert("��й�ȣ��Ȯ���� 4���� �̻�, 12���� �����̿��� �մϴ�.");
return false;
}
if ((f.pass1.value) != (f.pass2.value)) {
alert("��й�ȣ�� [��й�ȣ��Ȯ��] �� �����Ͽ��� �մϴ�. ");
return false;
}

if (!TypeCheck(f.phone1.value, NUM)) {
alert("��ȭ��ȣ�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.phone1.focus();
return false;
}
if (!TypeCheck(f.phone2.value, NUM)) {
alert("��ȭ��ȣ�� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.phone2.focus();
return false;
}
if (!TypeCheck(f.phone3.value, NUM)) {
alert("��ȭ��ȣ�� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.phone3.focus();
return false;
}

if ((f.headphone1.value.length < 2) || (f.headphone1.value.length > 4)) {
alert("�޴����� ���ڸ���2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.headphone1.focus();
return false;
}
if (!TypeCheck(f.headphone1.value, NUM)) {
alert("�޴����� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.headphone1.focus();
return false;
}
if ((f.headphone2.value.length < 3) || (f.headphone2.value.length > 4)) {
alert("�޴����� �߰��ڸ���3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.headphone2.focus();
return false;
}
if (!TypeCheck(f.headphone2.value, NUM)) {
alert("�޴����� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.headphone2.focus();
return false;
}
if ((f.headphone3.value.length < 4) || (f.headphone3.value.length > 4)) {
alert("�޴����� ���ڸ���4�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.headphone3.focus();
return false;
}
if (!TypeCheck(f.headphone3.value, NUM)) {
alert("�޴����� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.headphone3.focus();
return false;
}

if (f.email.value == "") {
alert("E ���� �ּҸ� �Է��� �ֽñ� �ٶ��ϴ�.");
f.email.focus();
return false;
}
if(f.email.value.lastIndexOf(" ") > -1){
alert("E ���� �ּҿ��� ������ �ü� �����ϴ�.")
f.email.focus();
return false
}
if(f.email.value.lastIndexOf(".")==-1){
alert("E ���� �ּҸ� ���������� �Է��� �ֽñ� �ٶ��ϴ�.")
f.email.focus();
return false
}
if(f.email.value.lastIndexOf("@")==-1){
alert("E ���� �ּҸ� ���������� �Է��� �ֽñ� �ٶ��ϴ�.")
f.email.focus();
return false
}

if (f.zip.value == "") {
alert("������ȣ�� �Է��� �ֽñ� �ٶ��ϴ�....*^^*\n\n�ּ�ã�⸦ Ŭ���Ͻþ� �Է��ϽǼ� �ֽ��ϴ�.");
return false;
}
if (f.zip1.value == "") {
alert("���ּҸ� �Է��� �ֽñ� �ٶ��ϴ�....*^^*\n\n�ּ�ã�⸦ Ŭ���Ͻþ� �Է��ϽǼ� �ֽ��ϴ�.");
return false;
}
if (f.zip2.value == "") {
alert("�������ּҸ� �Է��� �ֽñ� �ٶ��ϴ�....*^^*\n\n����- #### ���� �������� �Է��Ͻø�˴ϴ�.");
return false;
}

if (f.school.value == "0") {
alert("�����з��� ������ �ּ���..");
f.school.focus();
return false;
}

if (f.job.value == "0") {
alert("������ ������ �ּ���..");
f.job.focus();
return false;
}

if (f.yearmonuy.value == "0") {
alert("������ ������ �ּ���..");
f.yearmonuy.focus();
return false;
}

if (f.GirlStyle.value == "0") {
alert("���ϴ� ������ ���� ������ ������ �ּ���..");
f.GirlStyle.focus();
return false;
}

if (f.po1.value == "") {
alert("������ �Է��� �ּ���..");
f.po1.focus();
return false;
}
if (!TypeCheck(f.po1.value, NUM)) {
alert("������ ���ڷθ� �Է��� �ּž� �մϴ�.\n\nŰ�� 1m 80cm �� ���=> 180 ���� �Է��Ͻø� �˴ϴ�.");
f.po1.focus();
return false;
}

if (f.po2.value == "") {
alert("ü���� �Է��� �ּ���..");
f.po2.focus();
return false;
}
if (!TypeCheck(f.po2.value, NUM)) {
alert("ü���� ���ڷθ� �Է��� �ּž� �մϴ�.");
f.po2.focus();
return false;
}


//if (f.po3.value == "0") {
//alert("������ ������ �ּ���..");
//f.po3.focus();
//return false;
//}

//if (f.po4.value == "") {
//alert("������ �Է��� �ּ���..");
//f.po4.focus();
//return false;
//}

if (f.iii_1.value == "#") {
alert("��������-�� [��] �� ������ �ּ���..");
f.iii_1.focus();
return false;
}
if (f.iii_2.value == "#") {
alert("��������-�� [��] �� ������ �ּ���..");
f.iii_2.focus();
return false;
}
if (f.iii_3.value == "#") {
alert("��������-�� [°] ������ �ּ���..");
f.iii_3.focus();
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
</head>

&nbsp;&nbsp;&nbsp;<font style='font:bold; color:red;'>*</font> �� �ʼ� �Է��׸��Դϴ�.

<!---------------------- ȸ������ ����� ------------------>
<table border=0 align=center width=100% cellpadding='0' cellspacing='5'>
<form name='JoinInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return JoinCheckField()' action='<?=$action?>'>
<?php if
($MdoifyMode=="view"){?>
<INPUT TYPE="hidden" name='no' value='<?=$no?>'>
<?php } ?>
<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>���̵�</font>&nbsp;
</td>
<td>
<?php if
($MdoifyMode=="view"){echo("$MlangMember_id");}else{?>
<input type='text' name='id' size='15' maxLength='20' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="20">
<input type='button' style='font-size:9pt; background-color:#376C9D; color:#00315F; border-style:solid; height:22px; border:1 solid #cccccc; filter=progid:DXImageTransform.Microsoft.Gradient(GradientType=0, StartColorStr=#376C9D, EndColorStr=#FFFFFF);' onClick="javascript:idcheck();" value='ID�ߺ�Ȯ��'>
{��/���� 4~20��, ����Ұ�}
<?php } ?>
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'> ��й�ȣ</font>&nbsp;
</td>
<td>
<input type='password' name='pass1' size='10' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="12" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_pass'");}?>>
&nbsp;
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>��й�ȣ���Է�</font>&nbsp;
<input type='password' name='pass2' size='10' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="12" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_pass'");}?>>
 {��,�������� 4~12����} 
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='color:6788A6; font:bold;'>����</font>&nbsp;
</td>
<td>
<?php if
($MdoifyMode=="view"){echo("$MlangMember_name");}else{?>
<input type='hidden' name='name' value='<?=$name?>'>&nbsp;<?=$name?>
<?php } ?>
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='color:6788A6; font:bold;'>�ֹε�Ϲ�ȣ</font>&nbsp;
</td>
<td>
<?php if
($MdoifyMode=="view"){?>
<?=$MlangMember_jumin1?> - <?=$MlangMember_jumin2?>
<?}else{?>
<input type='hidden' name='jumin_1' value='<?=$jumin1?>'>
<input type='hidden' name='jumin_2' value='<?=$jumin2?>'>
<?=$jumin1?> - <?=$jumin2?>
<?php } ?>

</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='color:6788A6; font:bold;'>��ȭ��ȣ</font>&nbsp;
</td>
<td>
<input type='text' name='phone1' size='10' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_phone1'");}?>>
-
<input type='text' name='phone2' size='10' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_phone2'");}?>>
-
<input type='text' name='phone3' size='10' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_phone3'");}?>>
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>�޴���</font>&nbsp;
</td>
<td>
<input type='text' name='headphone1' size='10' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone1'");}?>>
-
<input type='text' name='headphone2' size='10' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone2'");}?>>
-
<input type='text' name='headphone3' size='10' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone3'");}?>>
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>E ����</font>&nbsp;
</td>
<td>
<input type='text' name='email' size='30' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="200" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_email'");}?>><BR>
��й�ȣ �нǽ� ���Ϸ� ������ �߼��ϹǷ� �ڼ��� ������ �Է��ϼž� �մϴ�.
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>�ּ�</font>&nbsp;
</td>
<td>
<table border="0" cellspacing="0" cellpadding="0">
<tr><td align=right>&nbsp;������ȣ&nbsp;</td><td><input  type="text" name="zip" size="10" style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' onClick="javascript:alert('�ּҸ� �Է��ϱ����� �ڵ��ּ�ã��â �� �ٿ�ڽ��ϴ�.'); zipcheck();" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_zip1'");}?>>
<input type='button' onClick="javascript:zipcheck();" value='�ּ��ڵ��Է�' style='font-size:9pt; background-color:#376C9D; color:#00315F; border-style:solid; height:22px; border:1 solid #cccccc; filter=progid:DXImageTransform.Microsoft.Gradient(GradientType=0, StartColorStr=#376C9D, EndColorStr=#FFFFFF);'>
</td></tr>
<tr><td align=right>&nbsp;���ּ�&nbsp;</td><td><input type="text"  name="zip1" size="50"  style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' onClick="javascript:alert('�ּҸ� �Է��ϱ����� �ڵ��ּ�ã��â �� �ٿ�ڽ��ϴ�.'); zipcheck();" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_zip2'");}?>></td></tr>
<tr><td align=right>&nbsp;�������ּ�&nbsp;</td><td><input type="text" name="zip2" size="50"  style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_zip3'");}?>></td></tr>
</table>
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>��ȥ����</font>&nbsp;
</td>
<td>
<?php if
($MlangMember_wedyes=="��ȥ"){?>
<INPUT TYPE="radio" NAME="wedyes" value='��ȥ'> ��ȥ
<INPUT TYPE="radio" NAME="wedyes" value='��ȥ' checked> ��ȥ
<?}else{?>
<INPUT TYPE="radio" NAME="wedyes" value='��ȥ' checked> ��ȥ
<INPUT TYPE="radio" NAME="wedyes" value='��ȥ'> ��ȥ
<?php } ?>
</td>
</tr>


<tr>
<td <?=$ledtColor?>>
<font style='color:6788A6; font:bold;'>������</font>&nbsp;
</td>
<td>
<?php if
($MdoifyMode=="view"){
$regdate_banner = substr($MlangMember_date, 0,10); 
$dir = "/member/upload/$regdate_banner/$MlangMember_id";
if($MlangMember_photofile){
echo("<a href='#' onClick=\"javascript:window.open('/member/PhotoFileView.php?id=$MlangMember_id&file=$dir/$MlangMember_photofile', 'PhotoFileView','width=100,height=100,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');\"><img src='$dir/$MlangMember_photofile' border=0 width=100></a><BR>");
}
echo("<INPUT TYPE='hidden' name='PhotoFileDir' value='upload/$regdate_banner/$MlangMember_id'><INPUT TYPE='hidden' name='PhotoFileDirName' value='$MlangMember_photofile'><INPUT TYPE='checkbox' NAME='PhoFileChick'> ���������� �Է�, ���� �Ͻ÷��� üũ���ּž� �մϴ�.<BR>");
}?>
<INPUT type="file" Size=30 name="photofile" style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' onChange="Mlamg_image(this.value)">
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>�����з�</font>&nbsp;
</td>
<td>
<select name=school style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;'>
<option value="0" selected>�����ϼ���</option>
<option value=1>�ʵ�(����)�б� ����</option>
<option value=2>�ʵ�(����)�б� ����</option>
<option value=3>���б� ����</option>
<option value=4>���б� ����</option>
<option value=5>�����б� ����</option>
<option value=6>�����б� ����</option>
<option value=7>���б� ����</option>
<option value=8>���б� ����</option>
<option value=9>���п� ����</option>
<option value=10>���п� ����</option>
<option value=11>���п� �̻�</option>
<option value=12>�з� ��</option>
</select>
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>����</font>&nbsp;
</td>
<td>
<select name=job style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;'>
				<option value="0" selected>�����ϼ���</option>
				<option value="��ȹ/�繫��">��ȹ/�繫��</option>
				<option value="����/����">����/����</option>
				<option value="�λ�/�ѹ�">�λ�/�ѹ�</option>
				<option value="�����Ͼ�">�����Ͼ�</option>
				<option value="������">������</option>
				<option value="�������">�������</option>
				<option value="��ǻ�Ͱ���">��ǻ�Ͱ���</option>
				<option value="���ͳݰ���">���ͳݰ���</option>
				<option value="�Ǽ�/���">�Ǽ�/���</option>
				<option value="����/����">����/����</option>
				<option value="�¹���/�װ�����">�¹���/�װ�����</option>
				<option value="������">������</option>
				<option value="������">������</option>
				<option value="����">����</option>
				<option value="�п�����">�п�����</option>
				<option value="���">���</option>
				<option value="��������">��������</option>
				<option value="������">������</option>
				<option value="������">������</option>
				<option value="�����̳�">�����̳�</option>
				<option value="�л�">�л�</option>
				<option value="���л�">���л�</option>
				<option value="��/�ڻ����">��/�ڻ����</option>
				<option value="�ǻ�/���ǻ�">�ǻ�/���ǻ�</option>
				<option value="��ȣ��/������">��ȣ��/������</option>
				<option value="�ڿ���">�ڿ���</option>
				<option value="����/���Ӱ���">����/���Ӱ���</option>
				<option value="�����">�����</option>
				<option value="ȸ��/����">ȸ��/����</option>
				<option value="��ġ������">��ġ������</option>
				<option value="��ȣ��/���������">��ȣ��/���������</option>
				<option value="�����">�����</option>
				<option value="����">����</option>
				<option value="��Ÿ">��Ÿ</option>
</select>
&nbsp;&nbsp;
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>����</font>&nbsp;
</td>
<td>
<select name=yearmonuy style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;'>
<option value="0" selected>�����ϼ���</option>
<option value=1>2õ���� ����</option>
<option value=2>2õ~3õ���� ����</option>
<option value=3>3õ~5õ���� ����</option>
<option value=4>5õ~7õ���� ����</option>
<option value=5>7õ~1�� ����</option>
<option value=6>1��~2������</option>
<option value=7>2�� �̻�</option>
</select>
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>&nbsp;<font style='color:6788A6; font:bold;'>�����ǳ���</font>&nbsp;
</td>
<td>
<select name='GirlStyle' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;'>
<option value='0'>�����ϼ���</option>
<option value='�߱�'>�߱�</option>
<option value='�ʸ���'>�ʸ���</option>
<option value='��Ʈ��'>��Ʈ��</option>
<option value='����'>����</option>
<option value='į�����'>į�����</option>
</select>
</td>
</tr>

<?php if
($MdoifyMode=="view"){?>
<script language="JavaScript"> 
var f=document.JoinInfo;
f.school.value="<?=$MlangMember_school?>"; 
f.job.value="<?=$MlangMember_job?>"; 
f.yearmonuy.value="<?=$MlangMember_yearmonuy?>"; 
f.GirlStyle.value="<?=$MlangMember_GirlStyle?>"; 
</script>
<?php } ?>


<tr>
<td <?=$ledtColor?>>
<font style='color:6788A6; font:bold;'>������</font>&nbsp;
</td>
<td>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>����</font>&nbsp;
<input type='text' name='po1' size='5' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="3" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_po1'");}?>>
cm&nbsp;&nbsp;

<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>ü��</font>&nbsp;
<input type='text' name='po2' size='5' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="3" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_po2'");}?>>
kg&nbsp;&nbsp;


<!------
<select name='po3' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;'>
<option value='0'>��������</option>
<option value='�ұ�'>�ұ�</option>
<option value='�⵶��'>�⵶��</option>
<option value='�̽�����'>�̽�����</option>
<option value='����'>����</option>
<option value='��Ÿ'>��Ÿ</option>
</select>
&nbsp;&nbsp;

<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>����</font>&nbsp;
<input type='text' name='po4' size='10' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="10">
--------------------->

</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>��������</font>&nbsp;
</td>
<td>
<select name='iii_1' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;'>
<option value='#'>����</option>
<?$i=0; while( $i < 16) { 
if($MdoifyMode=="view"){if($MlangMember_iii_1=="$i"){$III_1_ok="selected";}else{$III_1_ok="";}}
echo("<option value='$i' $III_1_ok>$i</option>");
$i=$i+1;
}?>
</select>
��
<select name='iii_2' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;'>
<option value='#'>����</option>
<?$i=0; while( $i < 16) {
if($MdoifyMode=="view"){if($MlangMember_iii_2=="$i"){$III_2_ok="selected";}else{$III_2_ok="";}}
echo("<option value='$i' $III_2_ok>$i</option>");
$i=$i+1;
}?>
</select>
��&nbsp;��
<select name='iii_3' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;'>
<option value='#'>����</option>
<?$i=0; while( $i < 16) { 
if($MdoifyMode=="view"){if($MlangMember_iii_3=="$i"){$III_3_ok="selected";}else{$III_3_ok="";}}
echo("<option value='$i' $III_3_ok>$i</option>");
$i=$i+1;
}?>
</select>
°<BR>&nbsp;&nbsp;(���������ϰ��: 0�� 0�� 0° �� ����)
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='color:6788A6; font:bold;'>�ϰ� ������</font>&nbsp;
</td>
<td>
<textarea cols=50 name=connent rows=5 style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;'><?php if
($MdoifyMode=="view"){echo("$MlangMember_connent");}?></textarea>
</td>
</tr>

<tr><td colspan=2 height=10></td></tr>
</table>

<p align=center>
<?php if
($MdoifyMode=="view"){?>
<input type='submit' value=' ���� �մϴ�.'>
<?}else{?>
<input type='submit' value=' ���� �մϴ�.'>
<input type='reset' value=' �ٽ� �ۼ� '>
<?php } ?>
</p>
</form>
<!---------------------- ȸ������ ����� ------------------>
<BR>