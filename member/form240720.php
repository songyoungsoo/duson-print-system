<?php $ledtColor = "width='110' align='left' bgcolor='#EDEFF3'"; ?>
<!DOCTYPE html>
<html lang="ko">

<head>
  <meta charset="UTF-8">
  <title>두손기획인쇄 회원가입양식</title>
  <style>
    /* 회원가입 폼 스타일 */
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

///////////////// id 를 중복확인한다,. ////////////////////////////////////////////////////////////////

function idcheck() {
  var f = document.JoinInfo;
  if (f.id.value == "") {
    alert("사용하실 회원 ID를 입력해 주세요. ");
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
    window.open("id_check.php?id="+f.id.value, "", "scrollbars=no,resizable=yes,width=600,height=200,top=0,left=0");
  }
}




function JoinCheckField()
{
    var f=document.JoinInfo;
if (f.id.value == "") {
alert("사용하실 회원 ID를 입력해 주세요. ");
f.id.focus();
return false;
}
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

if (f.pass1.value == "") {
alert("사용하실 비밀번호를 입력해 주세요. ");
f.pass1.focus();
return false;
}
if (!TypeCheck(f.pass1.value, ALPHA+NUM)) {
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
alert("사용하실 비밀번호재확인를 입력해 주세요. ");
f.pass2.focus();
return false;
}
if (!TypeCheck(f.pass2.value, ALPHA+NUM)) {
alert("비밀번호재확인은 영문자 및 숫자로만 사용할 수 있습니다.");
f.pass2.focus();
return false;
}
if ((f.pass2.value.length < 4) || (f.pass2.value.length > 12)) {
alert("비밀번호재확인은 4글자 이상, 12글자 이하이여야 합니다.");
return false;
}
if ((f.pass1.value) != (f.pass2.value)) {
alert("비밀번호와 [비밀번호재확인] 은 동일하여야 합니다. ");
f.pass1.focus();
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
alert("휴대폰의 앞자리는2자리 이상 4자리 이하를 입력하셔야 합니다.");
f.hendphone1.focus();
return false;
}
if (!TypeCheck(f.hendphone1.value, NUM)) {
alert("휴대폰의 앞자리는 숫자로만 사용할 수 있습니다.");
f.hendphone1.focus();
return false;
}
if ((f.hendphone2.value.length < 3) || (f.hendphone2.value.length > 4)) {
alert("휴대폰의 중간자리는3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.hendphone2.focus();
return false;
}
if (!TypeCheck(f.hendphone2.value, NUM)) {
alert("휴대폰의 중간자리는 숫자로만 사용할 수 있습니다.");
f.hendphone2.focus();
return false;
}
if ((f.hendphone3.value.length < 4) || (f.hendphone3.value.length > 4)) {
alert("휴대폰의 끝자리는4자리 이상 4자리 이하를 입력하셔야 합니다.");
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
alert("E 메일 주소에는 공백이 올수 없습니다.")
f.email.focus();
return false
}
if (f.email.value.indexOf(".") == -1){
alert("E 메일 주소를 정상적으로 입력해 주시기 바랍니다.")
f.email.focus();
return false
}
if (f.email.value.indexOf("@") == -1){
alert("E 메일 주소를 정상적으로 입력해 주시기 바랍니다.")
f.email.focus();
return false
}
if (f.sample6_postcode.value == "") {
alert("우편번호를 입력해 주시기 바랍니다....*^^*\n\n주소찾기를 클릭하시어 입력하실수 있습니다.");
return false;
}
if (f.sample6_address.value == "") {
alert("주소를 입력해 주시기 바랍니다....*^^*\n\n주소찾기를 클릭하시어 입력하실수 있습니다.");
return false;
}
if (f.sample6_detailAddress.value == "") {
alert("나머지주소를 입력해 주시기 바랍니다....*^^*\n\n직접- #### 번지 형식으로 입력하시면됩니다.");
return false;
}
if (!f.priv.value == "1") {
alert("좌측의 개인정보 수집 및 이용에관한 내용을 확인하시기 바랍니다*^^*\n\n 동의하셔야 회원가입이 가능합니다.");
return false;
}
return true;
}

</script>
</head>
<body>
  <h3 align=center>두손기획인쇄 회원가입양식</h3>
  <div align=center>&nbsp;&nbsp;&nbsp;<font style='font:bold; color:red; font-size: 9pt; align: center;'>*는 필수 입력항목입니다.</font></div>

  <!-- 회원가입 출력폼 -->
  <table border=0 align='center' width=100% cellpadding='0' cellspacing='5'>
    <form name='JoinInfo' method='post' enctype='multipart/form-data' onsubmit='return JoinCheckField()' action='<?php echo $action ?>'>

        <input type="hidden" name='no' value='<?php echo $no ?>'>

<tr>
  <td <?php echo $ledtColor ?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>아이디</font>&nbsp;
  </td>
  <td>
    <div style="display: flex;">
      <input type='text' name='id' size='15' maxLength='20'
       value='<?php $MlangMember_id ?>'>
      <input type='button' onClick='javascript:idcheck();' value='ID중복확인'>
    </div>
    <div style="display: flex;">
      <font style='font:bold; color:red;'>*</font>
      <font style='color:6788A6; font:bold; font-size: 9pt;'>{영/숫자 4~20자, 공백불가}</font>
    </div>
  </td>
</tr>

<tr>
  <td <?php echo $ledtColor ?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'> 비밀번호</font>&nbsp;
  </td>
  <td>
    <div style="display: flex;">
      <input type='password' name='pass1' size='10' maxlength="12"
value='<?php $MlangMember_pass1 ?>'>
    </div>
    <div>
      <font style='font:bold; color:red;'>*</font>
      <font style='color:6788A6; font-size: 9pt;'>{영,숫자조합 4~12글자}</font>
    </div>
    <div style="display: flex;">
      <input type='password' name='pass2' size='10' maxlength="12"
value= '<?php $MlangMember_pass2 ?>'>
    </div>
    <div style="display: flex;">
      <font style='font:bold; color:red;'>*</font>
      <font style='color:6788A6; font-size: 9pt;'>{비빌번호 재입력}</font>
    </div>
  </td>
</tr>

<tr>
<td <?php echo $ledtColor ?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold; font-size: 9pt;'>업체명/성명</font>&nbsp;
</td>
<td>
<input type='text' name='name' size='30' maxlength="200" 
value='<?php $MlangMember_name ?>'>
</td>
</tr>

<tr>
  <td <?php echo $ledtColor ?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>전화번호</font>&nbsp;
  </td>
  <td>
    <div style="display: flex;">
      <input type='text' name='phone1' size='5' maxlength="5" value='<?php $MlangMember_phone1 ?>'>
      -
      <input type='text' name='phone2' size='5' maxlength="5" value= '<?php $MlangMember_phone2 ?>'>
      -
      <input type='text' name='phone3' size='5' maxlength="5" value='<?php $MlangMember_phone3 ?>'>
    </div>
  </td>
</tr>

<tr>
  <td <?php echo $ledtColor ?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'>휴대폰</font>&nbsp;
  </td>
  <td>
    <div style="display: flex;">
      <input type='text' name='hendphone1' size='5' maxlength="5" value='<?php $MlangMember_hendphone1 ?>'>
      -
      <input type='text' name='hendphone2' size='5' maxlength="5" value='<?php $MlangMember_hendphone2 ?>'>
      -
      <input type='text' name='hendphone3' size='5' maxlength="5" value='<?php $MlangMember_hendphone3 ?>'>
    </div>
  </td>
</tr>

<tr>
<td <?php echo $ledtColor ?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold; font-size: 9pt;'>이메일</font>&nbsp;&nbsp;
</td>
<td>
<input type='text' name='email' size='30' maxlength="200" 
value='<?php $MlangMember_email ?>'><BR>
  <font style='color:6788A6; font-size: 9pt;'>주문 내역을 메일로 정보를 발송하므로 자세한 정보를 입력하셔야 합니다.</font>
</td>
</tr>
<tr>
<td <?php echo $ledtColor ?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold; font-size: 9pt;'>주소</font>&nbsp;
</td>
<td>
<table border=0 align='left' width='600' cellpadding='0' cellspacing='5'>
<tr>
<td>
<input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="우편번호"
 value='<?php $MlangMember_sample6_postcode ?>' >
<input type="button" onclick="sample6_execDaumPostcode()" value="우편번호 찾기"><br>
<input type="text" id="sample6_address" name="sample6_address" placeholder="주소"
 value='<?php $MlangMember_sample6_address ?>' ><br>	
<input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="상세주소"
 value='<?php $MlangMember_sample6_detailAddress ?>' >
<input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="참고항목"
 value='<?php $MlangMember_sample6_extraAddress ?>' >

<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
    function sample6_execDaumPostcode() {
        new daum.Postcode({
            oncomplete: function(data) {
                // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

                // 각 주소의 노출 규칙에 따라 주소를 조합한다.
                // 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
                var addr = ''; // 주소 변수
                var extraAddr = ''; // 참고항목 변수

                //사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
                if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
                    addr = data.roadAddress;
                } else { // 사용자가 지번 주소를 선택했을 경우(J)
                    addr = data.jibunAddress;
                }

                // 사용자가 선택한 주소가 도로명 타입일때 참고항목을 조합한다.
                if(data.userSelectedType === 'R'){
                    // 법정동명이 있을 경우 추가한다. (법정리는 제외)
                    // 법정동의 경우 마지막 문자가 "동/로/가"로 끝난다.
                    if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                        extraAddr += data.bname;
                    }
                    // 건물명이 있고, 공동주택일 경우 추가한다.
                    if(data.buildingName !== '' && data.apartment === 'Y'){
                        extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                    }
                    // 표시할 참고항목이 있을 경우, 괄호까지 추가한 최종 문자열을 만든다.
                    if(extraAddr !== ''){
                        extraAddr = ' (' + extraAddr + ')';
                    }
                    // 조합된 참고항목을 해당 필드에 넣는다.
                    document.getElementById("sample6_extraAddress").value = extraAddr;
                
                } else {
                    document.getElementById("sample6_extraAddress").value = '';
                }

                // 우편번호와 주소 정보를 해당 필드에 넣는다.
                document.getElementById('sample6_postcode').value = data.zonecode;
                document.getElementById("sample6_address").value = addr;
                // 커서를 상세주소 필드로 이동한다.
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
 <td <?php echo $ledtColor ?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold; font-size: 9pt;'>사업자정보</font>&nbsp;
</td>
<td>
<input type="text" name="po1" placeholder="사업자번호"
 value='<?php $MlangMember_po1 ?>' ><BR>
	<input type="text" name="po2" placeholder="상호"
 value='<?php $MlangMember_po2 ?>' ><BR>
	<input type="text" name="po3" placeholder="성명"
 value='<?php $MlangMember_po3 ?>'><BR>
	<input type="text" name="po4" placeholder="업태"
 value='<?php $MlangMember_po4 ?>'><BR>
	<input type="text" name="po5" placeholder="종목"
 value='<?php $MlangMember_po5 ?>'><BR>
	<input type="text" name="po6" size='50' placeholder="사업장주소"
 value='<?php $MlangMember_po6 ?>'><BR>
</td>
</tr>
<tr> 
<td <?php echo $ledtColor ?>>
    <font style='font:bold; color:red;'>*</font>
    <font style='color:6788A6; font:bold; font-size: 9pt;'><a href="http://www.dsp114.com/members/modal2.html" target="_blank">개인정보취급방침및이용약관</a></font>
    <!-- //http://www.dsp114.com/sub/pri_info.html -->
  </td>
    <td ><p><input type=radio name=priv value='1' checked="checked"> 
    <font style='color:6788A6; font:bold; font-size: 9pt;'>동의합니다.</font>
          <input type=radio name=priv value='' >
    <font style='color:6788A6; font:bold; font-size: 9pt;'>거부합니다.(좌측의 개인정보취급방침 및 이용약관에 관한 내용을 확인하시기 바랍니다,)</p></font>
    </td>
      </tr>  
<tr><td colspan=2 height=10></td></tr>
</table>

<p align=center>

<input type='submit' value=' 수정 합니다.'>

<input type='submit' value=' 가입 합니다.'>
<input type='reset' value=' 다시 작성 '>

</p>
</form>
  </table>
  <!-- 회원가입 출력폼 -->

  <!-- JavaScript 코드 (샘플 코드에서 뒷 부분 생략) -->

</body>

</html>