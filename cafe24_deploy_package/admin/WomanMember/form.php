<head>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=650,screen.availHeight)

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

///////////////////////////////////////////////////////////////////////////////////////////////////////

function JoinCheckField()
{
var f=document.JoinInfo;
<?php if ($mode=="modify"){}else{?>
if (f.PhotoFileSo.value == "") {
alert("���������� �Է��� �ּ���. ");
f.PhotoFileSo.focus();
return false;
}
if((f.PhotoFileSo.value.lastIndexOf(".jpg")==-1) && (f.PhotoFileSo.value.lastIndexOf(".gif")==-1))
{
alert("�������� �ڷ����� JPG �� GIF ���ϸ� �ϽǼ� �ֽ��ϴ�.");
f.PhotoFileSo.focus();
return false
}

if (f.PhotoFileBig.value == "") {
alert("ū������ �Է��� �ּ���. ");
f.PhotoFileBig.focus();
return false;
}
if((f.PhotoFileBig.value.lastIndexOf(".jpg")==-1) && (f.PhotoFileBig.value.lastIndexOf(".gif")==-1))
{
alert("ū���� �ڷ����� JPG �� GIF ���ϸ� �ϽǼ� �ֽ��ϴ�.");
f.PhotoFileBig.focus();
return false
}
<?php } ?>

if (f.name.value == "") {
alert("�̸��� �Է��� �ּ���. ");
f.name.focus();
return false;
}

if (f.nala.value == "0") {
alert("������ ������ �ּ���. ");
f.nala.focus();
return false;
}

if (f.zip1.value == "") {
alert("����� �� �Է��� �ּ���. ");
f.zip1.focus();
return false;
}

if (f.zip2.value == "") {
alert("���ּ� �� �Է��� �ּ���. ");
f.zip2.focus();
return false;
}

if (f.school.value == "") {
alert("�����б� �� �Է��� �ּ���. ");
f.school.focus();
return false;
}

if (f.job.value == "") {
alert("�������� �� �Է��� �ּ���. ");
f.job.focus();
return false;
}

if (f.cm.value == "") {
alert("Ű �� �Է��� �ּ���. ");
f.cm.focus();
return false;
}
if (!TypeCheck(f.cm.value, NUM)) {
alert("Ű �� ���ڷθ� �����ž� �մϴ�.");
f.cm.focus();
return false;
}

if (f.kg.value == "") {
alert("������ �� �Է��� �ּ���. ");
f.kg.focus();
return false;
}
if (!TypeCheck(f.kg.value, NUM)) {
alert("������ �� ���ڷθ� �����ž� �մϴ�.");
f.kg.focus();
return false;
}

if (f.religion.value == "0") {
alert("���� �� ������ �ּ���. ");
f.religion.focus();
return false;
}


if (f.taste.value == "") {
alert("��� �� �Է��� �ּ���. ");
f.taste.focus();
return false;
}

if (f.special.value == "") {
alert("Ư�� �� �Է��� �ּ���. ");
f.special.focus();
return false;
}

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
function Mlamg_imageSo(image) {

Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
Mlangwindow.document.open();
Mlangwindow.document.write("<html><head><title>�̹��� �̸�����</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><a href=\"#\" onClick=\"javascript:window.close();\"><img src=\"" + image + "\" border=\"0\"></a></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='������ �ݱ�' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}
function Mlamg_imageBig(image) {

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

<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
<tr><td bgcolor='#339999' height=27><font style='color:#FFFFFF; font:bold; font-size:10pt;'>
&nbsp;&nbsp;�� ����ȸ�� ���� <u><?php if ($mode=="modify"){?>����<?}else{?>�Է�<?php } ?></u> �ϱ�
</font></td></tr>
</table>
<BR>

<table border=0 align=center width=550 cellpadding='5' cellspacing='3' class='coolBar'>

<form name='JoinInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return JoinCheckField()' action='<?php echo $action?>'>
<?php if ($mode=="modify"){?><INPUT TYPE="hidden" name='no' value='<?php echo $no?>'><?php } ?>

<tr>
<td width=100 align=right bgcolor='#339999'><font style='color:#FFFFFF;'>������ȣ</font></td>
<td>
<?php if ($mode=="modify"){echo("$WM_Wmember");}else{?>�����Է½� �ڵ�����<?php } ?>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>��������</font></td>
<td>
<?php if ($mode=="modify"){?>
<?php if ($WM_PhotoFileSo){?><a href='/women/upload/<?php echo $no?>/<?php echo $WM_PhotoFileSo?>' target='_blank'><img src='/women/upload/<?php echo $no?>/<?php echo $WM_PhotoFileSo?>' width=100 height=100 border=0></a><BR><?php } ?>
<INPUT TYPE="hidden" name='TTSoFileName' value='<?php echo $WM_PhotoFileSo?>'>
<INPUT TYPE="checkbox" name='PhotoFileSoModify'> ������ �����Ϸ��� üũ���ּ���!!<BR>
<?php } ?>
<input type='file' name='PhotoFileSo' size='50' onChange="Mlamg_imageSo(this.value)" maxLength='100'>
<BR>�̹����� ũ��� 100X130 ���� ���ּ���
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>ū����</font></td>
<td>
<?php if ($mode=="modify"){?>
<?php if ($WM_PhotoFileBig){?><a href='/women/upload/<?php echo $no?>/<?php echo $WM_PhotoFileBig?>' target='_blank'><img src='/women/upload/<?php echo $no?>/<?php echo $WM_PhotoFileBig?>' width=100 height=100 border=0></a><BR><?php } ?>
<INPUT TYPE="hidden" name='TTBigFileName' value='<?php echo $WM_PhotoFileBig?>'>
<INPUT TYPE="checkbox" name='PhotoFileBigModify'> ������ �����Ϸ��� üũ���ּ���!!<BR>
<?php } ?>
<input type='file' name='PhotoFileBig' size='50' onChange="Mlamg_imageBig(this.value)" maxLength='100'>
<BR>�̹����� ũ��� 300X450 ���� ���ּ���
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>�̸�</font></td>
<td><input type='text' name='name' size='20' maxLength='5' <?php if ($mode=="modify"){echo("value='$WM_name'");}?>></td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>����</font></td>
<td>
<select name='nala'>
<option value='0'>�����ϼ���</option>
<option value='�߱�'>�߱�</option>
<!----<option value='�߱�-����'>�߱�-����</option>-->
<option value='�ʸ���'>�ʸ���</option>
<option value='��Ʈ��'>��Ʈ��</option>
<!----<option value='����'>����</option>-->
<!----<option value='į�����'>į�����</option>-->
</select>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>�������</font></td>
<td>
<select name='Byear'>
<?php
$dateyear=date("Y");
$dateformer=$dateyear-60;
$i=$dateformer; while( $i < $dateyear) { 
echo("<option value='$i'>$i</option>");
$i=$i+1;
}?>
</select>��
<select name='Bmonth'>
<?$i=1; while( $i < 13) { 
echo("<option value='$i'>$i</option>");
$i=$i+1;
}?>
</select>��
<select name='Bday'>
<?$i=1; while( $i < 32) { 
echo("<option value='$i'>$i</option>");
$i=$i+1;
}?>
</select>�� ��
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>�����</font></td>
<td><input type='text' name='zip1' size='30' maxLength='50' <?php if ($mode=="modify"){echo("value='$WM_zip1'");}?>></td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>���ּ�</font></td>
<td><input type='text' name='zip2' size='30' maxLength='50' <?php if ($mode=="modify"){echo("value='$WM_zip2'");}?>></td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>�����б�</font></td>
<td><input type='text' name='school' size='30' maxLength='50' <?php if ($mode=="modify"){echo("value='$WM_school'");}?>></td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>��ȥ����</font></td>
<td>
<?php if ($WM_wed=="��ȥ"){?>
<INPUT TYPE="radio" NAME="wed" value='��ȥ'>��ȥ
<INPUT TYPE="radio" NAME="wed" value='��ȥ' checked>��ȥ
<?}else{?>
<INPUT TYPE="radio" NAME="wed" value='��ȥ' checked>��ȥ
<INPUT TYPE="radio" NAME="wed" value='��ȥ'>��ȥ
<?php } ?>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>�ڳ�</font></td>
<td>
<input type='text' name='children' size='20' maxLength='20' <?php if ($mode=="modify"){echo("value='$WM_children'");}?>><BR>
<font style='color:#555555;'>(��ȥ���ο��� ��ȥ�� �����ϸ� ������ �Է� �ʾƵ� "��" �� ó���Ǿ� ���ɴϴ�.)</font>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>��������</font></td>
<td><input type='text' name='job' size='20' maxLength='20' <?php if ($mode=="modify"){echo("value='$WM_job'");}?>></td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>�ǰ�����</font></td>
<td>
<select name='body'>
<option value='�ǰ�'>�ǰ�</option>
<option value='����'>����</option>
</select>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>�� ǰ</font></td>
<td>
<select name='acharacter'>
<option value='ħ���¼�'>ħ���¼�</option>
<option value='������Ȱ'>������Ȱ</option>
<option value='����Ȱ��'>����Ȱ��</option>
</select>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>Ű</font></td>
<td><input type='text' name='cm' size='5' maxLength='3' <?php if ($mode=="modify"){echo("value='$WM_cm'");}?>>cm</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>������</font></td>
<td><input type='text' name='kg' size='5' maxLength='3' <?php if ($mode=="modify"){echo("value='$WM_kg'");}?>>kg</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>������</font></td>
<td>
<select name='blood'>
<option value='A'>A</option>
<option value='B'>B</option>
<option value='AB'>AB</option>
<option value='O'>O</option>
</select>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>�� ��</font></td>
<td>
<select name='religion'>
<option value='0'>��������</option>
<option value='�ұ�'>�ұ�</option>
<option value='ī�縯'>ī�縯</option>
<option value='�⵶��'>�⵶��</option>
<option value='�̽�����'>�̽�����</option>
<option value='����'>����</option>
<option value='��Ÿ'>��Ÿ</option>
</select>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>�� ��</font></td>
<td><input type='text' name='taste' size='20' <?php if ($mode=="modify"){echo("value='$WM_taste'");}?>></td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>Ư��</font></td>
<td><input type='text' name='special' size='20' <?php if ($mode=="modify"){echo("value='$WM_special'");}?>></td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>��������</font></td>
<td>
<select name='family'>
<option value='�Τ��������'>�Τ��������</option>
<option value='�Τ���'>�Τ���</option>
<option value='�Τ�����'>�Τ�����</option>
<option value='�����'>�������</option>
<option value='��'>��</option>
<option value='��'>��</option>
<option value='����'>����</option>
</select>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>��������</font></td>
<td>
<select name='iii_1'>
<option value='#'>����</option>
<?$i=0; while( $i < 16) { 
if($MdoifyMode=="view"){if($MlangMember_iii_1=="$i"){$III_1_ok="selected";}else{$III_1_ok="";}}
echo("<option value='$i' $III_1_ok>$i</option>");
$i=$i+1;
}?>
</select>
��
<select name='iii_2'>
<option value='#'>����</option>
<?$i=0; while( $i < 16) {
if($MdoifyMode=="view"){if($MlangMember_iii_2=="$i"){$III_2_ok="selected";}else{$III_2_ok="";}}
echo("<option value='$i' $III_2_ok>$i</option>");
$i=$i+1;
}?>
</select>
��&nbsp;��
<select name='iii_3'>
<option value='#'>����</option>
<?$i=0; while( $i < 16) { 
if($MdoifyMode=="view"){if($MlangMember_iii_3=="$i"){$III_3_ok="selected";}else{$III_3_ok="";}}
echo("<option value='$i' $III_3_ok>$i</option>");
$i=$i+1;
}?>
</select>
°&nbsp;&nbsp;<font style='color:#555555;'>(���������ϰ��: 0�� 0�� 0° �� ����)</font>
</td>
</tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>���μҰ�</font></td>
<td><TEXTAREA NAME="cont" ROWS="5" COLS="60"><?php if ($mode=="modify"){echo("$WM_cont");}?></TEXTAREA></td>
</tr>

<tr><td align=center colspan=2>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1'>
<tr><td align=center bgcolor='#339999' colspan=4><font style='color:#FFFFFF;'>���� �̻���</font></td></tr>
<tr>
<td bgcolor='#339999' width='20%' align=center><font style='color:#FFFFFF;'>�� ��</font></td>
<td width='30%'>
<select name='I_age1'>
<option value='#'>����</option>
<?$i=20; while( $i < 81) { 
if($MdoifyMode=="view"){if($MlangMember_iii_3=="$i"){$III_3_ok="selected";}else{$III_3_ok="";}}
echo("<option value='$i' $III_3_ok>$i</option>");
$i=$i+1;
}?>
</select>
~
<select name='I_age2'>
<option value='#'>����</option>
<?$i=20; while( $i < 81) { 
if($MdoifyMode=="view"){if($MlangMember_iii_3=="$i"){$III_3_ok="selected";}else{$III_3_ok="";}}
echo("<option value='$i' $III_3_ok>$i</option>");
$i=$i+1;
}?>
</select>
����
</td>
<td bgcolor='#339999' width='20%' align=center><font style='color:#FFFFFF;'>�����з�</font></td>
<td width='30%'>
<select name='I_school'>
<option value='�����б�'>�����б��̻�</option>
<option value='���б�'>���б��̻�</option>
<option value='���б�'>���б��̻�</option>
<option value='�ʵ��б�'>�ʵ��б��̻�</option>
<option value='��'>�����������</option>
</select>
</td>
</tr>
<tr>
<td align=center bgcolor='#339999' colspan=4><TEXTAREA NAME="I_cont" ROWS="5" COLS="60"><?php if ($mode=="modify"){echo("$WM_I_cont ");}?></TEXTAREA></td>
</tr>
</table>

</td></tr>

<tr>
<td align=right bgcolor='#339999'><font style='color:#FFFFFF;'>��¿���</font></td>
<td>
<?php if ($WM_sort=="��õ"){?>
<INPUT TYPE="radio" NAME="sort" value='�Ϲ�'>�Ϲ�
<INPUT TYPE="radio" NAME="sort" value='��õ' checked>��õ
<?}else{?>
<INPUT TYPE="radio" NAME="sort" value='�Ϲ�' checked>�Ϲ�
<INPUT TYPE="radio" NAME="sort" value='��õ'>��õ
<?php } ?>
</td>
</tr>

</table>


<?php if ($mode=="modify"){?>
<script language="JavaScript"> 
var f=document.JoinInfo;
f.nala.value="<?php echo $WM_nala?>"; 
f.Byear.value="<?php echo $WM_Byear?>"; 
f.Bmonth.value="<?php echo $WM_Bmonth?>"; 
f.Bday.value="<?php echo $WM_Bday?>"; 
f.body.value="<?php echo $WM_body?>";
f.acharacter.value="<?php echo $WM_acharacter?>";
f.blood.value="<?php echo $WM_blood?>"; 
f.religion.value="<?php echo $WM_religion?>"; 
f.family.value="<?php echo $WM_family?>"; 
f.iii_1.value="<?php echo $WM_iii_1?>";
f.iii_2.value="<?php echo $WM_iii_2?>"; 
f.iii_3.value="<?php echo $WM_iii_3?>"; 
f.I_age1.value="<?php echo $WM_I_age1?>"; 
f.I_age2.value="<?php echo $WM_I_age2?>"; 
f.I_school.value="<?php echo $WM_I_school?>"; 
//f..value="<?php echo $WM_?>"; 
</script>
<?php } ?>
<p align=center>
<?php if ($mode=="modify"){?>
<input type='submit' value=' ���� �մϴ�.'>
<?}else{?>
<input type='submit' value=' ��� �մϴ�.'>
<?php } ?>
<input type='reset' value=' �ٽ� �ۼ� '>
<input type='button' onClick='javascript:window.self.close();' value=' â �� �� '>
</p>

</form>

<BR><BR>