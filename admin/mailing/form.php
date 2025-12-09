<?php
declare(strict_types=1);


// ⚠️  XSS 보호 권장: echo 시 htmlspecialchars() 사용을 고려하세요
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

$M123="..";
include"../top.php"; 
include"../member/MaillingJoinAdminInfo.php";

$table="member";

if($FFF=="ok"){
$Mlang_query_inquiry="select * from $table where EMailSElect='yes'";
}else{
$Mlang_query_inquiry="select * from $table";
}

$query_inquiry= mysqli_query($db, "$Mlang_query_inquiry");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$total_inquiry = mysqli_affected_rows($db);
?>

<html>

<head>
<meta http-equiv='content-type' content='text/html; charset=euc-kr'>
<title>메일링</title>
<SCRIPT LANGUAGE="JavaScript">
function displayHTML(form) {
if(document.frm_a.content.value=="")
{
alert("내용을입력하세요....")
return false
}

var inf = form.content.value;
win = window.open(", ", 'popup_11', 'toolbar=no, status=no, scrollbars=yes');
win.document.write("" + inf + "");
}
</script>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table align='center' border='0' cellspacing=0 cellpadding=6>
    <form name=frm_a method=post action='mailling.php'>
    <tr>
        <td bgcolor=#639a9c>
		<font color='#FFFFFF'>
		<?if($FFF=="ok"){?>
		<b>발송유무 YES 회원님만 메일발송 페이지 입니다.</b>
		<?}else{?>
		<b>전체회원 에게 메일발송 페이지 입니다.</b>
		<?}?>
		<BR>
         + 총 <?echo("$total_inquiry");?> 명의 회원에게 메일을 발송합니다.
		 </font>
        </td>
        <td bgcolor=#efa610>
            <p>내용 (HTML도 가능함)</p>
        </td>
    </tr>
    <tr>
        <td valign=top>
<textarea name='email_list' rows='18' cols='45'>
<?php    $query= mysqli_query($db, "$Mlang_query_inquiry");
			while($row=mysqli_fetch_array($query))
			{
               
				echo "$row[email]";
				echo "\n";
			};
?>
</textarea>			


<?php $bgcolor_1="50A0FF";
$bgcolor_2="B0D7FF";
$bgcolor_3="EEF6FF";
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
	 <td><input type='text' name='subject' size='62' value='<?=$admin_name?> 에서 발송하는 회원 전체메일 입니다.'></td>
    </tr>
    <tr>
       <td>보내는 사람 이름</td> 
	   <td><input type='text' name='send_name' size='62' value='<?=$admin_name?>'></td>
    </tr>
    <tr>
        <td>보내는 사람 E-mail </td>
		<td><input type='text' name='send_email' size='62' value='<?=$admin_email?>'></td>
    </tr>
    <tr>
        <td>테스트 E-Mail </td>
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
<?php mysqli_close($db);
?>


<?php include"../down.php";
?>