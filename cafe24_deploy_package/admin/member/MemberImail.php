<?php
// member 데이터 호출
include "../../db.php";

$no = $_GET['no'] ?? '';
$code = $_GET['code'] ?? '';
$mode = $_POST['mode'] ?? '';

$stmt = $db->prepare("SELECT * FROM member WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $AD_no = $row['no'];
    $AD_name = $row['name'];
    $AD_email = $row['email'];
}

$stmt->close();
$db->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>
<?php 
if ($code == "1") { 
    echo "[$AD_name]님께 - 관리자 메일 발송"; 
} 
if ($code == "2") { 
    echo "[$id]님께 - 관리자 메일 발송"; 
} 
?>
</title>

<style>
body, td {
    font-family: '굴림', sans-serif;
    font-size: 10pt;
    color: #D4E7FD;
    line-height: 140%;
}
b {
    font-family: '굴림', sans-serif;
    font-size: 10pt;
    color: #FFFFFF;
}
</style>

<script language="javascript">
function MemberCheckField() {
    var f = document.FrmUserInfo;

    if (f.title.value == "") {
        alert("제목을 입력하세요.");
        return false;
    }
    if (f.connent.value == "") {
        alert("내용을 입력하세요.");
        return false;
    }
    return true;
}
</script>

</head>
<body bgcolor='#000000' LEFTMARGIN='10' TOPMARGIN='10' MARGINWIDTH='10' MARGINHEIGHT='10'>
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='#CCCCCC'>
<form name='FrmUserInfo' method='post' onsubmit='return MemberCheckField()' action='<?php echo $_SERVER['PHP_SELF']; ?>'>

<?php if ($code == "1") { ?>
<input type='hidden' name='mode' value='ok'>
<input type='hidden' name='member_name' value='<?php echo $AD_name; ?>'>
<input type='hidden' name='member_email' value='<?php echo $AD_email; ?>'>
<?php } ?>

<?php if ($code == "2") { ?>
<input type='hidden' name='mode' value='ok'>
<input type='hidden' name='member_name' value='<?php echo $id; ?>'>
<input type='hidden' name='member_email' value='<?php echo $email; ?>'>
<?php } ?>

<tr>
<td valign=top bgcolor='#000000' width=30%><b>제목</b></td>
<td valign=top bgcolor='#606060' width=70%>
<input type='text' name='title' size='61'>
</td>
</tr>

<tr>
<td valign=top bgcolor='#000000' width=30%><b>내용형식</b></td>
<td valign=top bgcolor='#606060' width=70%>
<select name='style'>
<option value='text'>텍스트</option>
<option value='html'>HTML</option>
</select>
</td>
</tr>

<tr>
<td bgcolor='#000000' width=30%><b>내용</b></td>
<td valign=top bgcolor='#606060' width=70%>
<textarea cols=60 name=connent rows=20></textarea>
</td>
</tr>

</table>

<p align=center>
<input type='submit' value=' 메일 발송 ' style="background-color:#37383A; color:#FFFFFF; border-width:1; border-style:solid; height:21px; border:1 solid #FFFFFF;">
&nbsp;&nbsp;
<input type='button' onClick='window.close();' value='창닫기 - CLOSE' style="background-color:#37383A; color:#FFFFFF; border-width:1; border-style:solid; height:21px; border:1 solid #FFFFFF;">
</p>

</form>
</body>
</html>

<?php
if ($mode == "ok") {
    include "../../db.php";
    include "./MaillingJoinAdminInfo.php";

    $TO_NAME = $_POST['member_name'];
    $TO_EMAIL = $_POST['member_email'];
    $FROM_NAME = $admin_name;
    $FROM_EMAIL = $admin_email;
    $SUBJECT = "$admin_name 님의 메일입니다.";

    if ($_POST['style'] == "text") {
        $CONTENT = nl2br(htmlspecialchars($_POST['connent'], ENT_QUOTES));
    } else {
        $CONTENT = $_POST['connent'];
    }

    $SEND_CONTENT = "
    <html>
    <table border=0 align=center width=100% cellpadding='7' cellspacing='1' bgcolor='#000000'>
    <tr>
    <td bgcolor='#393839'>
    &nbsp;&nbsp;<font style='font-size:10pt; color:#FFFFFF;'>{$_POST['title']}</font>
    </td>
    </tr>
    </table>
    <br>
    $CONTENT
    <p align=center>
    Copyright &copy; 2004 <a href='$admin_url' target='_blank'>$admin_name</a> Corp. All rights reserved. 문의: $admin_email
    </p>
    </html>
    ";

    $headers = "From: $FROM_NAME <$FROM_EMAIL>\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    mail($TO_EMAIL, $SUBJECT, $SEND_CONTENT, $headers);

    echo ("<script language='javascript'>
    alert('$TO_NAME 님께 메일이 발송되었습니다.');
    window.close();
    </script>
    ");
    exit;
}
?>