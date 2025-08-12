<?php
if (isset($_POST['mode']) && $_POST['mode'] == "modify") {
    $name = addslashes($_POST['name']);
    $email = addslashes($_POST['email']);
    $url = addslashes($_POST['url']);
    
    $fp = fopen("./MaillingJoinAdminInfo.php", "w");
    if ($fp) {
        fwrite($fp, "<?php\n");
        fwrite($fp, "\$admin_name = \"$name\";\n");
        fwrite($fp, "\$admin_email = \"$email\";\n");
        fwrite($fp, "\$admin_url = \"$url\";\n");
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
include "./MaillingJoinAdminInfo.php";
?>

<head>
<meta charset="UTF-8">
<script language="javascript">
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
    if (f.email.value.indexOf(" ") > -1) {
        alert("이메일 주소에 공백이 있습니다.");
        f.email.focus();
        return false;
    }
    if (f.email.value.indexOf(".") == -1) {
        alert("유효한 이메일 주소를 입력해 주세요.");
        f.email.focus();
        return false;
    }
    if (f.email.value.indexOf("@") == -1) {
        alert("유효한 이메일 주소를 입력해 주세요.");
        f.email.focus();
        return false;
    }

    if (f.url.value == "") {
        alert("홈페이지 URL을 입력하세요.");
        return false;
    }
    return true;
}
</script>
</head>

<br>
<p align="center">
- 회원 전체메일링 관리자 설정 정보입니다. -<br>
변경 후 수정 버튼을 클릭해 주세요.
</p>

<table border=0 align=center width=600 cellpadding='5' cellspacing='1' class='coolBar'>
<form name='MemberMailoInfo' method='post' onsubmit='return MemberMailoCheckField()' action='<?php echo $PHP_SELF?>'>
<input type='hidden' name='mode' value='modify'>
<tr><td colspan=2 width=100% height=10>&nbsp;</td></tr>

<tr>
<td width=150 align=right>관리자 이름</td>
<td width=450><input type='text' name='name' size='30' value='<?php echo $admin_name?>' maxlength='50'></td>
</tr>

<tr>
<td width=150 align=right>이메일 주소</td>
<td width=450><input type='text' name='email' size='60' value='<?php echo $admin_email?>' maxlength='200'></td>
</tr>

<tr>
<td width=150 align=right>홈페이지 URL</td>
<td width=450><input type='text' name='url' size='60' value='<?php echo $admin_url?>' maxlength='50'></td>
</tr>

<tr><td colspan=2 width=100% height=10>&nbsp;</td></tr>
</table>

<p align="center">
<input type='submit' value=' 수정합니다.'>
</p>
</form>
<br>

<?php include "../down.php"; ?>