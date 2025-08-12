<?php
include "../db.php";

$action = "register.php";
$MdoifyMode = $_GET['mode'] ?? '';
?>

<?php $ledtColor = "width='110' align='left' bgcolor='#EDEFF3'"; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<style>
    table {
        border: 1px solid gray;
        border-radius: 5px;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
        margin: 0 auto;
        width: 60%;
        padding: 2px;
    }
    table td {
        padding: 2px;
    }
    table input[type="text"],
    table input[type="password"],
    table input[type="button"] {
        width: 100%;
        padding: 8px;
        margin-bottom: 5px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 9pt;
    }
    table button[type="submit"],
    table button[type="reset"] {
        padding: 8px 20px;
        background-color: #4caf50;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        font-weight: bold;
        font-size: 9pt;
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

function TypeCheck (s, spc) {
    for(var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i + 1)) < 0) {
            return false;
        }
    }        
    return true;
}

function idcheck() {
    var f = document.JoinInfo;
    if (f.id.value == "") {
        alert("사용하실 회원 ID를 입력해 주세요.");
        f.id.focus();
        return false;
    } else {
        if (!TypeCheck(f.id.value, ALPHA+NUM)) {
            alert("사용하실 회원 ID는 영문자 및 숫자로만 사용할 수 있습니다.");
            f.id.focus();
            return false;
        }
        if ((f.id.value.length < 4) || (f.id.value.length > 12)) {
            alert("사용하실 회원 ID는 4글자 이상, 12글자 이하이여야 합니다.");
            f.id.focus();
            return false;
        }
        window.open("id_check.php?id=" + f.id.value, "", "scrollbars=no,resizable=yes,width=600,height=200,top=0,left=0");
    }
}

function JoinCheckField() {
    var f = document.JoinInfo;
    if (f.id.value == "") {
        alert("사용하실 회원 ID를 입력해 주세요.");
        f.id.focus();
        return false;
    }
    if (!TypeCheck(f.id.value, ALPHA + NUM)) {
        alert("사용하실 회원 ID는 영문자 및 숫자로만 사용할 수 있습니다.");
        f.id.focus();
        return false;
    }
    if ((f.id.value.length < 4) || (f.id.value.length > 12)) {
        alert("사용하실 회원 ID는 4글자 이상, 12글자 이하이여야 합니다.");
        f.id.focus();
        return false;
    }

    if (f.pass1.value == "") {
        alert("사용하실 비밀번호를 입력해 주세요.");
        f.pass1.focus();
        return false;
    }
    if (!TypeCheck(f.pass1.value, ALPHA + NUM)) {
        alert("비밀번호는 영문자 및 숫자로만 사용할 수 있습니다.");
        f.pass1.focus();
        return false;
    }
    if ((f.pass1.value.length < 4) || (f.pass1.value.length > 12)) {
        alert("비밀번호는 4글자 이상, 12글자 이하이여야 합니다.");
        f.pass1.focus();
        return false;
    }
    if (f.pass2.value == "") {
        alert("사용하실 비밀번호재확인을 입력해 주세요.");
        f.pass2.focus();
        return false;
    }
    if (!TypeCheck(f.pass2.value, ALPHA + NUM)) {
        alert("비밀번호재확인은 영문자 및 숫자로만 사용할 수 있습니다.");
        f.pass2.focus();
        return false;
    }
    if ((f.pass2.value.length < 4) || (f.pass2.value.length > 12)) {
        alert("비밀번호재확인은 4글자 이상, 12글자 이하이여야 합니다.");
        return false;
    }
    if ((f.pass1.value) != (f.pass2.value)) {
        alert("비밀번호와 [비밀번호재확인] 은 동일하여야 합니다.");
        return false;
    }

    if (!TypeCheck(f.phone1.value, NUM)) {
        alert("전화번호의 앞자리는 숫자로만 사용할 수 있습니다.");
        f.phone1.focus();
        return false;
    }
    if (!TypeCheck(f.phone2.value, NUM)) {
        alert("전화번호의 중간자리는 숫자로만 사용할 수 있습니다.");
        f.phone2.focus();
        return false;
    }
    if (!TypeCheck(f.phone3.value, NUM)) {
        alert("전화번호의 끝자리는 숫자로만 사용할 수 있습니다.");
        f.phone3.focus();
        return false;
    }

    if ((f.hendphone1.value.length < 2) || (f.hendphone1.value.length > 4)) {
        alert("휴대폰의 앞자리는 2자리 이상 4자리 이하를 입력하셔야 합니다.");
        f.hendphone1.focus();
        return false;
    }
    if (!TypeCheck(f.hendphone1.value, NUM)) {
        alert("휴대폰의 앞자리는 숫자로만 사용할 수 있습니다.");
        f.hendphone1.focus();
        return false;
    }
    if ((f.hendphone2.value.length < 3) || (f.hendphone2.value.length > 4)) {
        alert("휴대폰의 중간자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
        f.hendphone2.focus();
        return false;
    }
    if (!TypeCheck(f.hendphone2.value, NUM)) {
        alert("휴대폰의 중간자리는 숫자로만 사용할 수 있습니다.");
        f.hendphone2.focus();
        return false;
    }
    if ((f.hendphone3.value.length < 4) || (f.hendphone3.value.length > 4)) {
        alert("휴대폰의 끝자리는 4자리 이상 4자리 이하를 입력하셔야 합니다.");
        f.hendphone3.focus();
        return false;
    }
    if (!TypeCheck(f.hendphone3.value, NUM)) {
        alert("휴대폰의 끝자리는 숫자로만 사용할 수 있습니다.");
        f.hendphone3.focus();
        return false;
    }

    if (f.email.value == "") {
        alert("E 메일 주소를 입력해 주시기 바랍니다.");
        f.email.focus();
        return false;
    }
    if (f.email.value.indexOf(" ") > -1){
        alert("E 메일 주소에는 공백이 올수 없습니다.");
        f.email.focus();
        return false;
    }
    if (f.email.value.indexOf(".") == -1){
        alert("E 메일 주소를 정상적으로 입력해 주시기 바랍니다.");
        f.email.focus();
        return false;
    }
    if (f.email.value.indexOf("@") == -1){
        alert("E 메일 주소를 정상적으로 입력해 주시기 바랍니다.");
        f.email.focus();
        return false;
    }
    if (f.sample6_postcode.value == "") {
        alert("우편번호를 입력해 주시기 바랍니다.");
        return false;
    }
    if (f.sample6_address.value == "") {
        alert("주소를 입력해 주시기 바랍니다.");
        return false;
    }
    if (f.sample6_detailAddress.value == "") {
        alert("나머지 주소를 입력해 주시기 바랍니다.");
        return false;
    }
    if (f.priv.value != "1") {
        alert("개인정보 수집 및 이용에 동의하셔야 회원가입이 가능합니다.");
        return false;
    }
    return true;
}
</script>
</head>
<body>
<h3 align="center">두손기획인쇄 회원가입양식</h3>
<div align="center">
    <font style='font:bold; color:red; font-size: 9pt;'>*는 필수 입력항목입니다.</font>
</div>

<!---------------------- 회원가입 출력폼 ------------------>
<form name='JoinInfo' method='post' enctype='multipart/form-data' onsubmit='return JoinCheckField()' action='<?=$action?>'>
<?php if($MdoifyMode == "view"){ ?>
<input type="hidden" name='no' value='<?=$no?>'>
<?php } ?>

<table>
<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>아이디</font>
  </td>
  <td>
    <div style="display: flex;">
      <input type='text' name='id' size='15' maxLength='20' <?php if($MdoifyMode == "view"){echo("value='$MlangMember_id'");} ?>>
      <input type='button' onClick='javascript:idcheck();' value='ID중복확인'>
    </div>
    <div style="display: flex;">
      <font style='font:bold; color:red;'>*</font>
      <font style='color:6788A6; font:bold; font-size: 9pt;'>{영/숫자 4~20자, 공백불가}</font>
    </div>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'> 비밀번호</font>
  </td>
  <td>
    <div style="display: flex;">
      <input type='password' name='pass1' size='10' maxlength="12" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_pass1'");} ?>>
    </div>
    <div>
      <font style='font:bold; color:red;'>*</font>
      <font style='color:6788A6; font-size: 9pt;'>{영,숫자조합 4~12글자}</font>
    </div>
    <div style="display: flex;">
      <input type='password' name='pass2' size='10' maxlength="12" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_pass1'");} ?>>
    </div>
    <div style="display: flex;">
      <font style='font:bold; color:red;'>*</font>
      <font style='color:6788A6; font-size: 9pt;'>{비빌번호 재입력}</font>
    </div>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>업체명/성명</font>
  </td>
  <td>
    <input type='text' name='name' size='30' maxlength="200" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_name'");} ?>>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>전화번호</font>
  </td>
  <td>
    <div style="display: flex;">
      <input type='text' name='phone1' size='5' maxlength="5" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_phone1'");} ?>>
      -
      <input type='text' name='phone2' size='5' maxlength="5" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_phone2'");} ?>>
      -
      <input type='text' name='phone3' size='5' maxlength="5" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_phone3'");} ?>>
    </div>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>휴대폰</font>
  </td>
  <td>
    <div style="display: flex;">
      <input type='text' name='hendphone1' size='5' maxlength="5" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_hendphone1'");} ?>>
      -
      <input type='text' name='hendphone2' size='5' maxlength="5" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_hendphone2'");} ?>>
      -
      <input type='text' name='hendphone3' size='5' maxlength="5" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_hendphone3'");} ?>>
    </div>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>이메일</font>
  </td>
  <td>
    <input type='text' name='email' size='30' maxlength="200" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_email'");} ?>>
    <br>
    <font style='color:6788A6; font-size: 9pt;'>주문 내역을 메일로 정보를 발송하므로 자세한 정보를 입력하셔야 합니다.</font>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>주소</font>
  </td>
  <td>
    <table border=0 align='left' width='600' cellpadding='0' cellspacing='5'>
      <tr>
        <td>
          <input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="우편번호" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_sample6_postcode'");} ?>>
          <input type="button" onclick="sample6_execDaumPostcode()" value="우편번호 찾기"><br>
          <input type="text" id="sample6_address" name="sample6_address" placeholder="주소" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_sample6_address'");} ?>><br>	
          <input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="상세주소" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_sample6_detailAddress'");} ?>>
          <input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="참고항목" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_sample6_extraAddress'");} ?>>
          <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
          <script>
              function sample6_execDaumPostcode() {
                  new daum.Postcode({
                      oncomplete: function(data) {
                          var addr = ''; 
                          var extraAddr = ''; 

                          if (data.userSelectedType === 'R') { 
                              addr = data.roadAddress;
                          } else { 
                              addr = data.jibunAddress;
                          }

                          if(data.userSelectedType === 'R'){
                              if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                                  extraAddr += data.bname;
                              }
                              if(data.buildingName !== '' && data.apartment === 'Y'){
                                  extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                              }
                              if(extraAddr !== ''){
                                  extraAddr = ' (' + extraAddr + ')';
                              }
                              document.getElementById("sample6_extraAddress").value = extraAddr;
                          
                          } else {
                              document.getElementById("sample6_extraAddress").value = '';
                          }

                          document.getElementById('sample6_postcode').value = data.zonecode;
                          document.getElementById("sample6_address").value = addr;
                          document.getElementById("sample6_detailAddress").focus();
                      }
                  }).open();
              }
          </script>
        </td>
      </tr>
    </table>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>사업자정보</font>
  </td>
  <td>
    <input type="text" name="po1" placeholder="사업자번호" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_po1'");} ?>><br>
    <input type="text" name="po2" placeholder="상호" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_po2'");} ?>><br>
    <input type="text" name="po3" placeholder="성명" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_po3'");} ?>><br>
    <input type="text" name="po4" placeholder="업태" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_po4'");} ?>><br>
    <input type="text" name="po5" placeholder="종목" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_po5'");} ?>><br>
    <input type="text" name="po6" size='50' placeholder="사업장주소" <?php if($MdoifyMode == "view"){echo("value='$MlangMember_po6'");} ?>><br>
  </td>
</tr>

<tr>
  <td <?=$ledtColor?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>
      <a href="http://www.dsp114.com/members/modal2.html" target="_blank">개인정보취급방침및이용약관</a>
    </font>
  </td>
  <td>
    <p>
      <input type="radio" name="priv" value="1" checked="checked">
      <font style='color:6788A6; font:bold; font-size: 9pt;'>동의합니다.</font>
      <input type="radio" name="priv" value="">
      <font style='color:6788A6; font:bold; font-size: 9pt;'>거부합니다. (좌측의 개인정보취급방침 및 이용약관에 관한 내용을 확인하시기 바랍니다.)</font>
    </p>
  </td>
</tr>

<tr>
  <td colspan="2" height="10"></td>
</tr>
</table>

<p align="center">
<?php if($MdoifyMode == "view"){ ?>
<input type="submit" value=" 수정 합니다.">
<?php } else { ?>
<input type="submit" value=" 가입 합니다.">
<input type="reset" value=" 다시 작성 ">
<?php } ?>
</p>
</form>
</body>
</html>
