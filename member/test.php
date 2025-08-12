<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>테스트</title>
<script language=javascript>
	function idcheck()
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
	}else{
 //v2.0
  window.open("id_check.php?id="+ f.id.value,"","scrollbars=no,resizable=yes,width=600,height=200,top=0,left=0");
}
}

//window.open("http://www.dsp114.com/member/id_check.php?id="+ f.id.value,"","scrollbars=no,resizable=yes,width=400,height=50,top=100,left=100");
//winobject.document.location = "./id_check.php?id=" + f.id.value;
//winobject.focus();
	</script>
</head>

<body>
<table border=0 align=center width=100% cellpadding='0' cellspacing='5'>
<form name='JoinInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return JoinCheckField()' action='<?=$action?>'>
<? if($MdoifyMode=="view"){?>
<INPUT TYPE="hidden" name='no' value='<?=$no?>'>
<?php } ?>
<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>아이디</font>&nbsp;
</td>
<td>

<input type='text' name='id' size='15' style='background-color:#E8EFF6;' maxLength='20' <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_id'");}?>>
<input type='button' onClick='javascript:idcheck();' value='ID중복확인'>
{영/숫자 4~20자, 공백불가}
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'> 비밀번호</font>&nbsp;
</td>
<td>
<input type='password' name='pass1' size='10' style='background-color:#E8EFF6; ' maxlength="12" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_pass1'");}?>>
&nbsp;
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>비밀번호재입력</font>&nbsp;
<input type='password' name='pass2' size='10' style='background-color:#E8EFF6;' maxlength="12" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_pass2'");}?>>
 {영,숫자조합 4~12글자} 
</td>
</tr>
<tr>
<td <?=$ledtColor?>>
<font style='color:6788A6; font:bold;'>성명</font>&nbsp;
</td>
<td>
<? if($MdoifyMode=="view"){echo("$MlangMember_name");}else{?>
<input type='hidden' name='name' value='<?=$name?>'>&nbsp;<?=$name?>
<?php } ?>
</td>
</tr>

<tr>
  <td <?=$ledtColor?>>
  <font style='color:6788A6; font:bold;'>전화번호</font>&nbsp;
  </td>
  <td>
  <input type='text' name='phone1' size='10' style='background-color:#E8EFF6; ' maxlength="3" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_phone1'");}?>>
    -
  <input type='text' name='phone2' size='10' style='background-color:#E8EFF6; ' maxlength="4" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_phone2'");}?>>
    -
  <input type='text' name='phone3' size='10' style='background-color:#E8EFF6; ' maxlength="4" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_phone3'");}?>>
  </td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>휴대폰</font>&nbsp;
</td>
<td>
<input type='text' name='hendphone1' size='10' style='background-color:#E8EFF6; ' maxlength="3" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone1'");}?>>
-
<input type='text' name='hendphone2' size='10' style='background-color:#E8EFF6; ' maxlength="4" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone2'");}?>>
-
<input type='text' name='hendphone3' size='10' style='background-color:#E8EFF6; ' maxlength="4" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone3'");}?>>
</td>
</tr>

<tr>
<td <?=$ledtColor?>>
<font style='font:bold; color:red;'>*</font>
<font style='color:6788A6; font:bold;'>E 메일</font>&nbsp;
</td>
<td>
<input type='text' name='email' size='30' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="200" 
	<? if($MdoifyMode=="view"){echo("value='$MlangMember_email'");}?>><BR>
비밀번호 분실시 메일로 정보를 발송하므로 자세한 정보를 입력하셔야 합니다.
</td>
</tr>	
	
</table>

<p align=center>
<? if($MdoifyMode=="view"){?>
<input type='submit' value=' 수정 합니다.'>
<?}else{?>
<input type='submit' value=' 가입 합니다.'>
<input type='reset' value=' 다시 작성 '>
<?php } ?>
</p>
</form>
</body>
</html>
