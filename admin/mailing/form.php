<?php
$M123 = "..";
include "../top.php"; 
include "../member/JoinAdminInfo.php";

$table = "member";

include "../../db.php";

// 변수 초기화
$FFF = isset($_GET['FFF']) ? $_GET['FFF'] : '';

// EMailSElect 컬럼이 존재하지 않으므로 모든 회원을 대상으로 쿼리 실행
$Mlang_query_inquiry = "SELECT * FROM $table";

// 나중에 이메일 수신 동의 컬럼이 추가되면 아래 주석을 해제하고 컬럼명을 수정하세요
// if ($FFF == "ok") {
//     $Mlang_query_inquiry = "SELECT * FROM $table WHERE email_agree='yes'"; // 실제 컬럼명으로 변경 필요
// } else {
//     $Mlang_query_inquiry = "SELECT * FROM $table";
// }

$query_inquiry = $db->query($Mlang_query_inquiry);

if (!$query_inquiry) {
    die("쿼리 오류: " . $db->error);
}

$total_inquiry = $query_inquiry->num_rows;
?>

<html>

<head>
<meta http-equiv='content-type' content='text/html; charset=utf-8'>
<title>메일링</title>
<SCRIPT LANGUAGE="JavaScript">
function displayHTML(form) {
    if (document.frm_a.content.value == "") {
        alert("내용을 입력해주세요...");
        return false;
    }

    var inf = form.content.value;
    win = window.open("", 'popup_11', 'toolbar=no, status=no, scrollbars=yes');
    win.document.write("" + inf + "");
}
</script>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table align='center' border='0' cellspacing=0 cellpadding=6>
    <form name="frm_a" method="post" action="mailling.php">
    <tr>
        <td bgcolor="#639a9c">
		<font color='#FFFFFF'>
		<b>전체 회원에게 메일링합니다.</b>
		<BR>
         + 총 <?php echo $total_inquiry; ?>명의 회원이 메일링됩니다.
		 <!-- 이메일 수신 동의 기능이 추가되면 아래 코드를 활성화하세요
		 <?php if ($FFF == "ok") { ?>
		 <b>메일수신 동의 회원에게만 메일링합니다.</b>
		 <?php } else { ?>
		 <b>전체 회원에게 메일링합니다.</b>
		 <?php } ?>
		 -->
		 </font>
        </td>
        <td bgcolor="#efa610">
            <p>내용 (HTML 형식)</p>
        </td>
    </tr>
    <tr>
        <td valign=top>
<textarea name='email_list' rows='18' cols='45'>
<?php
$query = $db->query($Mlang_query_inquiry);
if ($query) {
    while ($row = $query->fetch_assoc()) {
        echo $row['email'] . "\n";
    }
} else {
    echo "메일 리스트를 가져오는 동안 오류가 발생했습니다.";
}
?>
</textarea>			

<?php
$bgcolor_1 = "50A0FF";
$bgcolor_2 = "B0D7FF";
$bgcolor_3 = "EEF6FF";
?>

        </td>
        <td align=center>
<textarea name='content' rows='22' cols='88'></textarea><BR>
<input type="button" value=" 미리보기 " onclick="displayHTML(this.form)">
        </td>
    </tr>
    <tr>
        <td colspan='2'>
     <table border=0 align=center cellpadding=0 cellspacing=3>
	 <tr>
	 <td>제목</td>
	 <td><input type='text' name='subject' size='62' value='<?php echo $admin_name?> 님이 보내는 회원 전체메일입니다.'></td>
    </tr>
    <tr>
       <td>보내는 사람 이름</td> 
	   <td><input type='text' name='send_name' size='62' value='<?php echo $admin_name?>'></td>
    </tr>
    <tr>
        <td>보내는 사람 E-mail</td>
		<td><input type='text' name='send_email' size='62' value='<?php echo $admin_email?>'></td>
    </tr>
    <tr>
        <td>테스트 E-Mail</td>
		<td><input type='text' name='test_email' size='62'></td>
     </tr>
     </table>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <p align='center'><input type='submit' value=' 보내기 '></p>
        </td>
    </tr>
    </form>
</table>
</body>
</html>

<?php
$db->close();
include "../down.php";
?>
