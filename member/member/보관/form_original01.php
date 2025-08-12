<BR>
<? $ledtColor="width='110' align='left' bgcolor='#EDEFF3'";?>
<head>
<!-- <style>
p,br,body,td,input,select,submit {color:black; font-size:10pt; FONT-FAMILY:����;}
b {color:black; font-size:9pt; FONT-FAMILY:����;}
</style> -->
<style>
  /* ȸ������ �� ��Ÿ�� */
  table {
    border: 1px solid gray;
    border-radius: 5px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
    margin: 0 auto;
    width: 66.67%; /* ���� 2/3�� ���� */
    padding: 10px; /* ���Ʒ� ���� ���� */
  }

  table td {
    padding: 10px;
  }

  table input[type="text"],
  table input[type="password"],
  table input[type="button"] {
    width: 100%;
    padding: 8px; /* ���� ���� ���� */
    margin-bottom: 5px; /* �Ʒ��� ���� ���� */
    border: 1px solid #ccc;
    border-radius: 3px;
  }

  table button[type="submit"],
  table button[type="reset"] {
    padding: 8px 20px; /* ���Ʒ� ���� ���� */
    background-color: #4caf50;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-weight: bold;
  }

  table button[type="submit"]:hover,
  table button[type="reset"]:hover {
    background-color: #45a049;
  }
</style>

<script>

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
<?php if(!$MdoifyMode=="view"){?>
function idcheck() {
  var f = document.JoinInfo;
  if (f.id.value == "") {
    alert("����Ͻ� ȸ�� ID�� �Է��� �ּ���. ");
    f.id.focus();
    return false;
  } else {
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
    window.open("id_check.php?id="+f.id.value, "", "scrollbars=no,resizable=yes,width=600,height=200,top=0,left=0");
  }
}
<?php } ?>

<? if(!$MdoifyMode=="view"){?>

function JoinCheckField()
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

if ((f.hendphone1.value.length < 2) || (f.hendphone1.value.length > 4)) {
alert("�޴����� ���ڸ���2�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.hendphone1.focus();
return false;
}
if (!TypeCheck(f.hendphone1.value, NUM)) {
alert("�޴����� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.hendphone1.focus();
return false;
}
if ((f.hendphone2.value.length < 3) || (f.hendphone2.value.length > 4)) {
alert("�޴����� �߰��ڸ���3�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.hendphone2.focus();
return false;
}
if (!TypeCheck(f.hendphone2.value, NUM)) {
alert("�޴����� �߰��ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.hendphone2.focus();
return false;
}
if ((f.hendphone3.value.length < 4) || (f.hendphone3.value.length > 4)) {
alert("�޴����� ���ڸ���4�ڸ� �̻� 4�ڸ� ���ϸ� �Է��ϼž� �մϴ�.");
f.hendphone3.focus();
return false;
}
if (!TypeCheck(f.hendphone3.value, NUM)) {
alert("�޴����� ���ڸ��� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.hendphone3.focus();
return false;
}

if (f.email.value == "") {
alert("E ���� �ּҸ� �Է��� �ֽñ� �ٶ��ϴ�.");
f.email.focus();
return false;
}
if (f.email.value.indexOf(" ") > -1){
alert("E ���� �ּҿ��� ������ �ü� �����ϴ�.")
f.email.focus();
return false
}
if (f.email.value.indexOf(".") == -1){
alert("E ���� �ּҸ� ���������� �Է��� �ֽñ� �ٶ��ϴ�.")
f.email.focus();
return false
}
if (f.email.value.indexOf("@") == -1){
alert("E ���� �ּҸ� ���������� �Է��� �ֽñ� �ٶ��ϴ�.")
f.email.focus();
return false
}
if (f.sample6_postcode.value == "") {
alert("������ȣ�� �Է��� �ֽñ� �ٶ��ϴ�....*^^*\n\n�ּ�ã�⸦ Ŭ���Ͻþ� �Է��ϽǼ� �ֽ��ϴ�.");
return false;
}
if (f.sample6_address.value == "") {
alert("�ּҸ� �Է��� �ֽñ� �ٶ��ϴ�....*^^*\n\n�ּ�ã�⸦ Ŭ���Ͻþ� �Է��ϽǼ� �ֽ��ϴ�.");
return false;
}
if (f.sample6_detailAddress.value == "") {
alert("�������ּҸ� �Է��� �ֽñ� �ٶ��ϴ�....*^^*\n\n����- #### ���� �������� �Է��Ͻø�˴ϴ�.");
return false;
}
return true;
}
<?php } ?>
</script>
</head>



<!---------------------- ȸ������ ����� ------------------>
<table border=0 align='center' width=100% cellpadding='0' cellspacing='5'>

<form name='JoinInfo' method='post' enctype='multipart/form-data' onsubmit='return JoinCheckField()' action='<?=$action?>'>
<? if($MdoifyMode=="view"){?>
<INPUT TYPE="hidden" name='no' value='<?=$no?>'>
<?php } ?>
&nbsp;&nbsp;&nbsp;<font style='font:bold; color:red;'>*</font> �� �ʼ� �Է��׸��Դϴ�.
<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold;'>���̵�</font>&nbsp;
  </td>
  <td>

    <div style="display: flex;">
      <input type='text' name='id' size='15' maxLength='20'>
      <input type='button' onClick='javascript:idcheck();' value='ID�ߺ�Ȯ��'>
    </div>
    <div style="display: flex;">
      <font style='font:bold; color:red;'>*</font>
      <font style='color:6788A6; font:bold;'>{��/���� 4~20��, ����Ұ�}</font>
    </div>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold;'> ��й�ȣ</font>&nbsp;
  </td>
  <td>
    <div style="display: flex;">
      <input type='password' name='pass1' size='10' maxlength="12">
    </div>
    <div>
      <font style='font:bold; color:red;'>*</font>
      <font style='color:6788A6;'>{��,�������� 4~12����}</font>
    </div>
    <div style="display: flex;">
      <input type='password' name='pass2' size='10' maxlength="12">
    </div>
    <div style="display: flex;">
      <font style='font:bold; color:red;'>*</font>
      <font style='color:6788A6;'>{�����ȣ ���Է�}</font>
    </div>
  </td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font>��ü��/����</font>&nbsp;
</td>
<td>
<input type='text' name='name' size='30' maxlength="200" 
	<? if($MdoifyMode=="view"){echo("value='$MlangMember_name'");}?>>
</td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold;'>��ȭ��ȣ</font>&nbsp;
  </td>
  <td>
    <div style="display: flex;">
      <input type='text' name='phone1' size='5' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_phone1'");}?>>
      -
      <input type='text' name='phone2' size='5' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_phone2'");}?>>
      -
      <input type='text' name='phone3' size='5' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_phone3'");}?>>
    </div>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold;'>�޴���</font>&nbsp;
  </td>
  <td>
    <div style="display: flex;">
      <input type='text' name='hendphone1' size='5' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone1'");}?>>
      -
      <input type='text' name='hendphone2' size='5' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone2'");}?>>
      -
      <input type='text' name='hendphone3' size='5' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone3'");}?>>
    </div>
  </td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
E ����&nbsp;
</td>
<td>
<input type='text' name='email' size='30' maxlength="200" 
	<? if($MdoifyMode=="view"){echo("value='$MlangMember_email'");}?>><BR>
�ֹ� ������ ���Ϸ� ������ �߼��ϹǷ� �ڼ��� ������ �Է��ϼž� �մϴ�.
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
�ּ�&nbsp;
</td>
<td>
<table border=0 align='left' width=100% cellpadding='0' cellspacing='5'>
<tr><td>

<input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="������ȣ"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_sample6_postcode'");}?>>
<input type="button" onclick="sample6_execDaumPostcode()" value="������ȣ ã��"><br>
<input type="text" id="sample6_address" name="sample6_address" placeholder="�ּ�"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_sample6_address'");}?>><br>	
<input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="���ּ�"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_sample6_detailAddress'");}?>>
<input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="�����׸�"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_sample6_extraAddress'");}?>>

<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
    function sample6_execDaumPostcode() {
        new daum.Postcode({
            oncomplete: function(data) {
                // �˾����� �˻���� �׸��� Ŭ�������� ������ �ڵ带 �ۼ��ϴ� �κ�.

                // �� �ּ��� ���� ��Ģ�� ���� �ּҸ� �����Ѵ�.
                // �������� ������ ���� ���� ��쿣 ����('')���� �����Ƿ�, �̸� �����Ͽ� �б� �Ѵ�.
                var addr = ''; // �ּ� ����
                var extraAddr = ''; // �����׸� ����

                //����ڰ� ������ �ּ� Ÿ�Կ� ���� �ش� �ּ� ���� �����´�.
                if (data.userSelectedType === 'R') { // ����ڰ� ���θ� �ּҸ� �������� ���
                    addr = data.roadAddress;
                } else { // ����ڰ� ���� �ּҸ� �������� ���(J)
                    addr = data.jibunAddress;
                }

                // ����ڰ� ������ �ּҰ� ���θ� Ÿ���϶� �����׸��� �����Ѵ�.
                if(data.userSelectedType === 'R'){
                    // ���������� ���� ��� �߰��Ѵ�. (�������� ����)
                    // �������� ��� ������ ���ڰ� "��/��/��"�� ������.
                    if(data.bname !== '' && /[��|��|��]$/g.test(data.bname)){
                        extraAddr += data.bname;
                    }
                    // �ǹ����� �ְ�, ���������� ��� �߰��Ѵ�.
                    if(data.buildingName !== '' && data.apartment === 'Y'){
                        extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                    }
                    // ǥ���� �����׸��� ���� ���, ��ȣ���� �߰��� ���� ���ڿ��� �����.
                    if(extraAddr !== ''){
                        extraAddr = ' (' + extraAddr + ')';
                    }
                    // ���յ� �����׸��� �ش� �ʵ忡 �ִ´�.
                    document.getElementById("sample6_extraAddress").value = extraAddr;
                
                } else {
                    document.getElementById("sample6_extraAddress").value = '';
                }

                // ������ȣ�� �ּ� ������ �ش� �ʵ忡 �ִ´�.
                document.getElementById('sample6_postcode').value = data.zonecode;
                document.getElementById("sample6_address").value = addr;
                // Ŀ���� ���ּ� �ʵ�� �̵��Ѵ�.
                document.getElementById("sample6_detailAddress").focus();
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
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>���������</font>&nbsp;
</td>
<td>
<input type="text" name="po1" placeholder="����ڹ�ȣ"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_po1'");}?>><BR>
	<input type="text" name="po2" placeholder="��ȣ"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_po2'");}?>><BR>
	<input type="text" name="po3" placeholder="����"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_po3'");}?>><BR>
	<input type="text" name="po4" placeholder="����"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_po4'");}?>><BR>
	<input type="text" name="po5" placeholder="����"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_po5'");}?>><BR>
	<input type="text" name="po6" size='50' placeholder="������ּ�"
<? if($MdoifyMode=="view"){echo("value='$MlangMember_po6'");}?>><BR>
</td>
</tr>

<tr><td colspan=2 height=10></td></tr>
</table>

<p align=center>
<? if($MdoifyMode=="view"){?>
<input type='submit' value=' ���� �մϴ�.'>
<?}else{?>
<input type='submit' value=' ���� �մϴ�.'>
<input type='reset' value=' �ٽ� �ۼ� '>
<?php } ?>
</p>
</form>
<!---------------------- ȸ������ ����� ------------------>