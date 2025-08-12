<?php
// 변수 초기화
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$name = isset($_POST['name']) ? $_POST['name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$title = isset($_POST['title']) ? $_POST['title'] : '';
$style = isset($_POST['style']) ? $_POST['style'] : '';
$cont = isset($_POST['cont']) ? $_POST['cont'] : '';

if($mode == "modify") {
    $fp = fopen("./JoinAdminInfo.php", "w");
    if ($fp) {
        fwrite($fp, "<?php\n");
        fwrite($fp, "\$AdminName = \"" . addslashes($name) . "\";\n");
        fwrite($fp, "\$AdminMail = \"" . addslashes($email) . "\";\n");
        fwrite($fp, "\$MailTitle = \"" . addslashes($title) . "\";\n");
        fwrite($fp, "\$MailStyle = \"" . addslashes($style) . "\";\n");
        fwrite($fp, "\$MailCont = \"" . addslashes($cont) . "\";\n");
        fwrite($fp, "?>");
        fclose($fp);

        echo ("<script language='javascript'>
        alert('정보가 성공적으로 수정되었습니다.');
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF'>
        ");
        exit;
    } else {
        echo ("<script language='javascript'>
        alert('파일 쓰기 실패!');
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF'>
        ");
        exit;
    }
}

$M123 = "..";
include "../top.php"; 
include "./JoinAdminInfo.php";
?>

<head>
<meta charset="UTF-8">
<script language="javascript">
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

////////////////////////////////////////////////////////////////////////////////
function TypeCheck(s, spc) {
    var i;
    for (i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i + 1)) < 0) {
            return false;
        }
    }        
    return true;
}

/////////////////////////////////////////////////////////////////////////////////

function MemberMailoCheckField() {
    var f = document.MemberMailoInfo;

    if (f.name.value == "") {
        alert("관리자 이름을 입력하세요.");
        return false;
    }

    if (f.email.value == "") {
        alert("이메일 주소를 입력해 주세요.");
        f.email.focus();
        return false;
    }
    if (f.email.value.lastIndexOf(" ") > -1) {
        alert("이메일 주소에 공백이 있습니다.");
        f.email.focus();
        return false;
    }
    if (f.email.value.lastIndexOf(".") == -1) {
        alert("유효한 이메일 주소를 입력해 주세요.");
        f.email.focus();
        return false;
    }
    if (f.email.value.lastIndexOf("@") == -1) {
        alert("유효한 이메일 주소를 입력해 주세요.");
        f.email.focus();
        return false;
    }

    if (f.title.value == "") {
        alert("메일 제목을 입력하세요.");
        return false;
    }

    if (f.cont.value.length < 20) {
        alert("메일 내용을 최소 20자 이상 입력하세요.");
        return false;
    }
}
</script>
</head>

<br>
<p align="center">
- 아래의 정보는 관리자 메일링 설정 정보입니다. -<br>
변경하신 후 수정 버튼을 클릭해 주세요.
</p>

<table border="0" align="center" width="600" cellpadding="5" cellspacing="1" class="coolBar">
<form name="MemberMailoInfo" method="post" onsubmit="return MemberMailoCheckField()" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>">
<input type="hidden" name="mode" value="modify">
<tr><td colspan="2" width="100%" height="10">&nbsp;</td></tr>

<tr>
<td width="150" align="right">관리자 이름</td>
<td width="450"><input type="text" name="name" size="30" value="<?php echo  htmlspecialchars($AdminName) ?>" maxlength="50"></td>
</tr>

<tr>
<td width="150" align="right">관리자 이메일</td>
<td width="450"><input type="text" name="email" size="60" value="<?php echo  htmlspecialchars($AdminMail) ?>" maxlength="200"></td>
</tr>

<tr>
<td width="150" align="right">메일 제목</td>
<td width="450"><input type="text" name="title" size="60" value="<?php echo  htmlspecialchars($MailTitle) ?>" maxlength="50"></td>
</tr>

<tr>
<td width="150" align="right">메일 형식</td>
<td width="450">
    <input type="radio" name="style" value="br" <?php if ($MailStyle == "br") echo "checked"; ?>> 자동 BR(개행)
    <input type="radio" name="style" value="html" <?php if ($MailStyle == "html") echo "checked"; ?>> HTML형식
</td>
</tr>

<tr>
<td width="150" align="right">메일 내용</td>
<td width="450">
    <textarea name="cont" rows="10" cols="50"><?php echo  htmlspecialchars($MailCont) ?></textarea>
</td>
</tr>

<tr><td colspan="2" width="100%" height="10">&nbsp;</td></tr>
</table>

<p align="center">
<input type="submit" value=" 수정합니다.">
</p>
</form>
<br>

<?php include "../down.php"; ?>
