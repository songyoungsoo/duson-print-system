<?php 
// 변수 초기화 (Notice 에러 방지)
$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');
$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
$GH_url = isset($_GET['GH_url']) ? $_GET['GH_url'] : '';
$HTTP_REFERER = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// BBS 관련 변수들 초기화
$BBS_ADMIN_td_color1 = isset($BBS_ADMIN_td_color1) ? $BBS_ADMIN_td_color1 : '#000000';
$BBS_ADMIN_td_color2 = isset($BBS_ADMIN_td_color2) ? $BBS_ADMIN_td_color2 : '#FFFFFF';
$BbsDir = isset($BbsDir) ? $BbsDir : '.';

if(!$BbsDir){$BbsDir=".";}
?>

<head>
<style>
.write {color:<?php echo $BBS_ADMIN_td_color1?>; font:bold;}
input,select,submit,TEXTAREA {background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>;}
</style>

<script language=javascript>
function BBSLoginCheckField()
{
var f=document.BBSLogin;
if (f.bbs_pass.value == "") {
alert("비밀번호를 입력하세요..");
return false;
}
}
</script>

</head>

<table border=0 align=center bgcolor='<?php echo $BBS_ADMIN_td_color1?>' cellpadding='15' cellspacing='1' style='word-break:break-all;'>

<form name='BBSLogin' method='post' OnSubmit='javascript:return BBSLoginCheckField()' action='<?php echo $BbsDir?>/bbs_login_ok.php'>
<input type='hidden' name='no' value='<?php echo $no?>'>
<input type='hidden' name='table' value='<?php echo $table?>'>
<input type='hidden' name='mode' value='<?php echo $mode?>'>
<?php if($GH_url){ ?>
    <input type='hidden' name='GH_url' value='<?php echo $GH_url; ?>'>
<?php } else { ?>
    <input type='hidden' name='GH_url' value='<?php echo $HTTP_REFERER; ?>'>
<?php } ?>
<tr bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<td>
<font style='font:bold; color:<?php echo $BBS_ADMIN_td_color1?>;'>&nbsp;&nbsp;비밀번호 입력&nbsp;&nbsp;
<input type='password' name='bbs_pass' size='15'>
<input type='submit' value=' 확 인 '>
<input type='button' value=' 취 소 ' onClick='javascript:history.go(-1);'>
</td>
</tr>
</form>
</table>
