<?php
include "../db.php";

// 에러 부분과 현 페이지의 URL 직접 접근을 막는다........
function ERROR($msg) {
    echo "<script language=javascript>
    window.alert('$msg');
    history.go(-1);
    </script>";
    exit;
}

// 세션 시작
session_start();

// 필요한 변수가 설정되었는지 확인
$id = isset($_POST['id']) ? mysqli_real_escape_string($db, $_POST['id']) : null;
$pass1 = isset($_POST['pass1']) ? mysqli_real_escape_string($db, $_POST['pass1']) : null;
$name = isset($_POST['name']) ? mysqli_real_escape_string($db, $_POST['name']) : null;
$phone1 = isset($_POST['phone1']) ? mysqli_real_escape_string($db, $_POST['phone1']) : null;
$phone2 = isset($_POST['phone2']) ? mysqli_real_escape_string($db, $_POST['phone2']) : null;
$phone3 = isset($_POST['phone3']) ? mysqli_real_escape_string($db, $_POST['phone3']) : null;
$hendphone1 = isset($_POST['hendphone1']) ? mysqli_real_escape_string($db, $_POST['hendphone1']) : null;
$hendphone2 = isset($_POST['hendphone2']) ? mysqli_real_escape_string($db, $_POST['hendphone2']) : null;
$hendphone3 = isset($_POST['hendphone3']) ? mysqli_real_escape_string($db, $_POST['hendphone3']) : null;
$email = isset($_POST['email']) ? mysqli_real_escape_string($db, $_POST['email']) : null;
$sample6_postcode = isset($_POST['sample6_postcode']) ? mysqli_real_escape_string($db, $_POST['sample6_postcode']) : null;
$sample6_address = isset($_POST['sample6_address']) ? mysqli_real_escape_string($db, $_POST['sample6_address']) : null;
$sample6_detailAddress = isset($_POST['sample6_detailAddress']) ? mysqli_real_escape_string($db, $_POST['sample6_detailAddress']) : null;
$sample6_extraAddress = isset($_POST['sample6_extraAddress']) ? mysqli_real_escape_string($db, $_POST['sample6_extraAddress']) : null;
$po1 = isset($_POST['po1']) ? mysqli_real_escape_string($db, $_POST['po1']) : null;
$po2 = isset($_POST['po2']) ? mysqli_real_escape_string($db, $_POST['po2']) : null;
$po3 = isset($_POST['po3']) ? mysqli_real_escape_string($db, $_POST['po3']) : null;
$po4 = isset($_POST['po4']) ? mysqli_real_escape_string($db, $_POST['po4']) : null;
$po5 = isset($_POST['po5']) ? mysqli_real_escape_string($db, $_POST['po5']) : null;
$po6 = isset($_POST['po6']) ? mysqli_real_escape_string($db, $_POST['po6']) : null;
$po7 = isset($_POST['po7']) ? mysqli_real_escape_string($db, $_POST['po7']) : null;
$connent = isset($_POST['connent']) ? mysqli_real_escape_string($db, $_POST['connent']) : null;

if (!$id) {
    $msg = "정상적인 접근방법이 아닙니다.";
    ERROR($msg);
}

// DB에 있는 아이디를 중복 체크한다...
$query = "SELECT * FROM member WHERE id='$id'";
$result = mysqli_query($db, $query);
$rows = mysqli_num_rows($result);
if ($rows) {
    echo "<script language=javascript>
    window.alert('\\n$id 는 이미등록되어있는\\아이디이므로 신청하실수 없습니다.\\n');
    history.go(-1);
    </script>";
    exit;
}

###################################################################
$result = mysqli_query($db, "SELECT MAX(no) FROM member");
if (!$result) {
    echo "<script>
        window.alert(\"DB 접속 에러입니다!\")
        history.go(-1)
        </script>";
    exit;
}
$row = mysqli_fetch_row($result);

$new_no = ($row[0]) ? $row[0] + 1 : 1;

// 회원 정보 입력
$date = date("Y-m-d H:i:s");
$dbinsert = "INSERT INTO member (no, id, pass, name, phone1, phone2, phone3, hendphone1, hendphone2, hendphone3, email, sample6_postcode, sample6_address, sample6_detailAddress, sample6_extraAddress, po1, po2, po3, po4, po5, po6, po7, connent, date, level, status, extra) 
VALUES ('$new_no', '$id', '$pass1', '$name', '$phone1', '$phone2', '$phone3', '$hendphone1', '$hendphone2', '$hendphone3', '$email', '$sample6_postcode', '$sample6_address', '$sample6_detailAddress', '$sample6_extraAddress', '$po1', '$po2', '$po3', '$po4', '$po5', '$po6', '$po7', '$connent', '$date', '5', '0', '')";

$result_insert = mysqli_query($db, $dbinsert);

// 회원가입하면 그날의 폴더 생성시키기////////////////////////////////////////
/* $regdate_banner = substr($date, 0, 10);
$dir = "./upload/$regdate_banner";
$dir_handle = is_dir("$dir");
if (!$dir_handle) {
    mkdir("$dir", 0755);
    exec("chmod 777 $dir");
}
$dir_id = "./upload/$regdate_banner/$id";
$dir_handle_id = is_dir("$dir_id");
if (!$dir_handle_id) {
    mkdir("$dir_id", 0755);
    exec("chmod 777 $dir_id");
} */
////////////////////////////////////////////////////////////////////////////////////

// 회원에게 가입 인사메일을 보낸다...
include "../admin/member/JoinAdminInfo.php";
$TO_NAME = "$name";
$TO_EMAIL = "$email";
$FROM_NAME = "$AdminName";
$FROM_EMAIL = "$AdminMail";
$SUBJECT = "$MailTitle";

if ($MailStyle == "html") {
    $connent_text = $MailCont;
} else {
    $CONTENT = $MailCont;
    $CONTENT = preg_replace("/</", "&lt;", $CONTENT);
    $CONTENT = preg_replace("/>/", "&gt;", $CONTENT);
    $CONTENT = preg_replace("/\"/", "&quot;", $CONTENT);
    $CONTENT = preg_replace("/\|/", "&#124;", $CONTENT);
    $CONTENT = preg_replace("/\r\n\r\n/", "<P>", $CONTENT);
    $CONTENT = preg_replace("/\r\n/", "<BR>", $CONTENT);
    $connent_text = $CONTENT;
}

$SEND_CONTENT = "
<html>
<body bgcolor='#FFFFFF'>
$connent_text
</body>
</html>
";
include_once('../shop/mailer.lib.php');
// $content = $body;
$content = $SEND_CONTENT;
$to = "$email";
$subject = "$TO_NAME 님 회원가입을 환영합니다.";

mailer($fname, $fmail, $to, $subject, $content, $type = 1, $file, $cc = "", $bcc = ""); //("$fname", "$fmail", "$to", "$subject", "$content", $type=1)

// 회원가입 완료 메세지를 보인후 페이지를 이동 시킨다
echo "<meta charset='utf-8'>
<script language='javascript'>
alert('$name 님 께서는 신청해주신 ID - $id  비밀번호 - $pass1 로 회원가입이 완료 되었습니다. 가입하신 정보로 로그인 을 하시면 정상적인 서비스를 이용하실 수 있습니다...');
</script>
<meta http-equiv='Refresh' content='0; URL=/member/login.php'>";
exit;
?>
