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
if (f.pass.value == "") {
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

if (f.sample4_postcode.value == "") {
alert("������ȣ�� �Է��� �ֽñ� �ٶ��ϴ�....*^^*\n\n�ּ�ã�⸦ Ŭ���Ͻþ� �Է��ϽǼ� �ֽ��ϴ�.");
return false;
}
if (f.sample4_roadAddress.value == "") {
alert("�ּҸ� �Է��� �ֽñ� �ٶ��ϴ�....*^^*\n\n�ּ�ã�⸦ Ŭ���Ͻþ� �Է��ϽǼ� �ֽ��ϴ�.");
return false;
}
if (f.sample4_detailAddress.value == "") {
alert("�������ּҸ� �Է��� �ֽñ� �ٶ��ϴ�....*^^*\n\n����- #### ���� �������� �Է��Ͻø�˴ϴ�.");
return false;
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
<tr><td>
<input type="text" name="sample4_postcode" placeholder="������ȣ"
<?php if
($MdoifyMode=="view"){echo("value='$MlangMember_sample4_postcode'");}?>>
<input type="button" onclick="sample4_execDaumPostcode()" value="������ȣ ã��"><br>
<input type="text" name="sample4_roadAddress" placeholder="���θ��ּ�">
<?php if
($MdoifyMode=="view"){echo("value='$MlangMember_sample4_roadAddress'");}?>
<input type="text" name="sample4_jibunAddress" placeholder="�����ּ�">
<?php if
($MdoifyMode=="view"){echo("value='$MlangMember_sample4_jibunAddress'");}?>
<span id="guide" style="color:#999;display:none"></span>
<input type="text" name="sample4_detailAddress" placeholder="���ּ�">
<?php if
($MdoifyMode=="view"){echo("value='$MlangMember_sample4_detailAddress'");}?>
<input type="text" name="sample4_extraAddress" placeholder="�����׸�">
<?php if
($MdoifyMode=="view"){echo("value='$MlangMember_sample4_extraAddress'");}?>

<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
    //�� ���������� ���θ� �ּ� ǥ�� ��Ŀ� ���� ���ɿ� ����, �������� �����͸� �����Ͽ� �ùٸ� �ּҸ� �����ϴ� ����� �����մϴ�.
    function sample4_execDaumPostcode() {
        new daum.Postcode({
            oncomplete: function(data) {
                // �˾����� �˻���� �׸��� Ŭ�������� ������ �ڵ带 �ۼ��ϴ� �κ�.

                // ���θ� �ּ��� ���� ��Ģ�� ���� �ּҸ� ǥ���Ѵ�.
                // �������� ������ ���� ���� ��쿣 ����('')���� �����Ƿ�, �̸� �����Ͽ� �б� �Ѵ�.
                var roadAddr = data.roadAddress; // ���θ� �ּ� ����
                var extraRoadAddr = ''; // ���� �׸� ����

                // ���������� ���� ��� �߰��Ѵ�. (�������� ����)
                // �������� ��� ������ ���ڰ� "��/��/��"�� ������.
                if(data.bname !== '' && /[��|��|��]$/g.test(data.bname)){
                    extraRoadAddr += data.bname;
                }
                // �ǹ����� �ְ�, ���������� ��� �߰��Ѵ�.
                if(data.buildingName !== '' && data.apartment === 'Y'){
                   extraRoadAddr += (extraRoadAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                // ǥ���� �����׸��� ���� ���, ��ȣ���� �߰��� ���� ���ڿ��� �����.
                if(extraRoadAddr !== ''){
                    extraRoadAddr = ' (' + extraRoadAddr + ')';
                }

                // ������ȣ�� �ּ� ������ �ش� �ʵ忡 �ִ´�.
                document.getElementById('sample4_postcode').value = data.zonecode;
                document.getElementById("sample4_roadAddress").value = roadAddr;
                document.getElementById("sample4_jibunAddress").value = data.jibunAddress;
                
                // �����׸� ���ڿ��� ���� ��� �ش� �ʵ忡 �ִ´�.
                if(roadAddr !== ''){
                    document.getElementById("sample4_extraAddress").value = extraRoadAddr;
                } else {
                    document.getElementById("sample4_extraAddress").value = '';
                }

                var guideTextBox = document.getElementById("guide");
                // ����ڰ� '���� ����'�� Ŭ���� ���, ���� �ּҶ�� ǥ�ø� ���ش�.
                if(data.autoRoadAddress) {
                    var expRoadAddr = data.autoRoadAddress + extraRoadAddr;
                    guideTextBox.innerHTML = '(���� ���θ� �ּ� : ' + expRoadAddr + ')';
                    guideTextBox.style.display = 'block';

                } else if(data.autoJibunAddress) {
                    var expJibunAddr = data.autoJibunAddress;
                    guideTextBox.innerHTML = '(���� ���� �ּ� : ' + expJibunAddr + ')';
                    guideTextBox.style.display = 'block';
                } else {
                    guideTextBox.innerHTML = '';
                    guideTextBox.style.display = 'none';
                }
            }
        }).open();
    }
</script>
	</td></tr>
</table>
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