<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 인코딩 설정
header('Content-Type: text/html; charset=utf-8');

include "../db.php";

// 데이터베이스 연결 (mysqli 사용 예시)
$mysqli = new mysqli($host, $user, $password, $dataname);

// 연결 확인
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// 데이터베이스 인코딩 설정
$mysqli->set_charset("utf8mb4");

// POST와 GET 데이터를 필터링
$mode = $_GET['mode'] ?? '';

if ($mode === "id") {
?>

<html>
<head>
<title><?=$admin_name?> - 아이디/비밀번호 찾기</title>
<style>
p, br, body, td, input, select, submit {
    color: black; 
    font-size: 9pt; 
    font-family: 굴림;
}
b {
    color: black; 
    font-size: 9pt; 
    font-family: 굴림;
}
</style>
<link rel="stylesheet" type="text/css" href="http://www.script.ne.kr/script.css">
<script>
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i + 1)) < 0) {
            return false;
        }
    }
    return true;
}

function MemberCheckField() {
    var f = document.FrmUserInfo;

    if (f.name.value === "") {
        alert("회원이름을 입력해 주세요.");
        return false;
    }

    if (f.hendphone1.value === "" || f.hendphone2.value === "" || f.hendphone3.value === "") {
        alert("전화번호를 모두 입력해 주세요.");
        return false;
    }

    return true;
}
</script>
</head>

<body bgcolor='#FFFFFF' leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td height=5 align=left bgcolor='#000000' width=100%></td></tr>
<tr><td height=30 align=left bgcolor='#43B5C9' width=100%>
&nbsp;&nbsp;<b><font color=#FFFFFF>▶ 아이디 및 비밀번호 찾기</font></b>
</td></tr>
<tr><td height=15 width=100%></td></tr>
<tr><td align=center bgcolor='#FFFFFF'>

<table border=0 align=center cellpadding='0' cellspacing='0' width=395>
<tr><td align=center width=395 height=9><img src='img/member_search_top.gif' width=395 height=9'></td></tr>
<tr><td align=center width=100% background='img/member_search_back.gif' height=90>

<b>아래 정보를 입력하시면 아이디와 비밀번호를 찾을 수 있습니다.</b>

<table border=0 align=center cellpadding='0' cellspacing='0' width=395>
<form name='FrmUserInfo' method='post' onsubmit='return MemberCheckField()' action='<?=htmlspecialchars($_SERVER["PHP_SELF"])?>?mode=id_ok'>
<tr>
<td width=90 height=35 align=right valign=bottom>회원이름:&nbsp;</td>
<td width=210 valign=bottom>
<input type='text' name='name' size='15' maxlength="20">
</td>
</tr>
<tr><td height=3 colspan=2 width=100%></td></tr>
<tr><td height=1 colspan=2 bgcolor='#5BB4D0' width=100%></td></tr>
<tr><td height=3 colspan=2 width=100%></td></tr>
<tr>
<td width=90 height=35 align=right valign=top><p style='text-indent:0; margin-top:5pt;'> 전화번호:&nbsp;</p></td>
<td width=210 valign=top>
<input type='text' name='hendphone1' size='4' maxlength="4">-
<input type='text' name='hendphone2' size='4' maxlength="4">-
<input type='text' name='hendphone3' size='4' maxlength="4">
</td>
</tr>
</table>

</td></tr>
<tr><td align=center width=395 height=8'><img src='img/member_search_down.gif' width=395 height=8'></td></tr>
</table>

</td></tr>
<tr><td height=15 width=100%></td></tr>
<tr><td height=50 valign=middle align=center bgcolor='#E0E7E0' width=100%>
<input type="image" src='img/member_search_1.gif' width=72 height=23 border=0>
<a href="#" onclick="javascript:window.close();"><img src='img/member_search_2.gif' width=72 height=23 border=0'></a>
</td></tr>

</form>
</table>

</body>
</html>

<?php
} elseif ($mode === "id_ok") {
    function ERROR($msg) {
        echo "<script>
        window.alert('$msg');
        history.go(-1);
        </script>";
        exit;
    }

    $name = $_POST['name'] ?? '';
    $hendphone1 = $_POST['hendphone1'] ?? '';
    $hendphone2 = $_POST['hendphone2'] ?? '';
    $hendphone3 = $_POST['hendphone3'] ?? '';

    if (!$name || !$hendphone1 || !$hendphone2 || !$hendphone3) {
        ERROR("입력된 정보가 올바르지 않습니다. 다시 시도해 주세요.");
    }

    $query = "SELECT * FROM member WHERE name = ? AND hendphone1 = ? AND hendphone2 = ? AND hendphone3 = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssss", $name, $hendphone1, $hendphone2, $hendphone3);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row['id']; // 여기서 $id 변수 정의
            echo "
<html>
<head>
<script>
parent.resizeTo(510,440);

function MemberCheckField() {
    var f = document.FrmUserInfo;
    if (f.email.value === '') {
        alert('비밀번호를 받을 이메일 주소를 입력해 주세요.');
        return false;
    }
    return true;
}
</script>
<style>
p, br, body, td, input, select, submit {
    color: black; 
    font-size: 9pt; 
    font-family: 굴림;
}
b {
    color: black; 
    font-size: 9pt; 
    font-family: 굴림;
}
</style>
</head>

<body bgcolor='#FFFFFF' leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td height=5 align=left bgcolor='#000000' width=100%></td></tr>
<tr><td height=30 align=left bgcolor='#43B5C9' width=100%>
&nbsp;&nbsp;<b><font color=#FFFFFF>▶ 회원님의 아이디를 찾았습니다.</font></b>
</td></tr>
<tr><td height=15 width=100%></td></tr>
<tr><td align=center bgcolor='#FFFFFF'>
<p align=left style='text-indent:0; margin-top:0pt; margin-right:20pt; margin-bottom:10pt; margin-left:45pt;'>
회원님의 아이디는 <b><font color='blue'>{$row['id']}</font></b> 입니다.
</p>
<table border=0 align=center cellpadding='0' cellspacing='0' width=395>
<tr><td align=center width=395 height=9'><img src='img/member_search_top.gif' width=395 height=9'></td></tr>
<tr><td width=100% align=center background='img/member_search_back.gif' height=110>
<b><font color=#43B5C9> ----- 비밀번호를 찾아드립니다. -----</font></b>
<br><br>
<p align=left style='text-indent:0; margin-top:10pt; margin-right:20pt; margin-bottom:0pt; margin-left:45pt;'> 
<b>아래에 비밀번호를 확인할 E메일 주소를 입력후<br> 
확인을 누르시면 <u>비밀번호</u> 를 발송해드립니다.</b><br>
</p>
<table border=0 align=center cellpadding='0' cellspacing='0' width=300>
<form name='FrmUserInfo' method='post' onsubmit='return MemberCheckField()' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?mode=pass_ok'>
<tr>
<td width=90 height=35 align=right valign=bottom>회원아이디:&nbsp;</td>
<td width=210 valign=bottom>
<input type='hidden' name='id' value='{$row['id']}'><b><font color='blue'>{$row['id']}</font></b>
</td>
</tr>
<tr><td height=3 colspan=2 width=100%></td></tr>
<tr><td height=1 colspan=2 bgcolor='#5BB4D0' width=100%></td></tr>
<tr><td height=3 colspan=2 width=100%></td></tr>
<tr>
<td width=90 height=35 align=right valign=top><p style='text-indent:0; margin-top:5pt;'> E메일주소:&nbsp;</p></td>
<td width=210 valign=top><input type='text' name='email' size='30'></td>
</tr>
<tr>
<td colspan=2 align=center>
<font color='blue'><b>$name</b> 님께서 확인가능한 메일이어야 합니다.</font>
</td>
</tr>
</table>

</td></tr>
<tr><td align=center width=395 height=8'><img src='img/member_search_down.gif' width=395 height=8'></td></tr>
</table>

</td></tr>
<tr><td height=15 width=100%></td></tr>
<tr><td height=50 valign=middle align=center bgcolor='#E0E7E0' width=100%>
<input type='image' src='img/member_search_1.gif' width=72 height=23 border=0>
<a href='#' onclick='javascript:window.close();'><img src='img/member_search_2.gif' width=72 height=23 border=0'></a>
</td></tr>

</form>
</table>
</body>
</html>
            ";
            exit;
        }
    } else {
        echo ("
            <script>
            alert('$name 님께서 요청해주신 이름과 전화번호로 검색되는 회원 ID가 없습니다.   ----- $admin_name ----- [회원가입을 이용하여 가입하시기 바랍니다.]');
            history.go(-1);
            </script>
        ");
        exit;
    }
} elseif ($mode === "pass_ok") {
?>

<?php
// 에러 메시지 출력 함수
function ERROR($msg) {
    echo "<script>
    window.alert('$msg');
    history.go(-1);
    </script>";
    exit;
}

// 임의의 비밀번호 생성 함수
function generateRandomPassword($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}

$id = $_POST['id'] ?? '';
$email = $_POST['email'] ?? '';
$newPassword = generateRandomPassword();

if (!$id || !$email) {
    $msg = "입력된 정보가 올바르지 않습니다.\\n\\n처음부터 다시 시도해 주세요.\\n\\n----- $admin_name -----";
    ERROR($msg);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = "메일 주소가 부정확하거나 올바르지 않습니다.\\n\\n다시 작성해 주시기 바랍니다. --- $admin_name---";
    ERROR($msg);
}

// 이메일에 한글이 포함되었는지 확인
if (preg_match('/[\xA1-\xFE\xA1-\xFE]/', $email)) {
    $msg = "메일 주소에 한글이 포함되어 있습니다.\\n\\n다시 작성해 주시기 바랍니다. --- $admin_name---";
    ERROR($msg);
}

// 데이터베이스 업데이트
$query = "UPDATE member SET pass = ? WHERE id = ?";
$stmt = $mysqli->prepare($query);
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // 비밀번호 해싱
$stmt->bind_param("ss", $hashedPassword, $id);

if (!$stmt->execute()) {
    echo "
    <script>
    window.alert('DB 접속 에러입니다!');
    history.go(-1);
    </script>";
    exit;
}

echo "<meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?mode=pass_mlang&id=$id&email=$email&newPassword=$newPassword'>";
exit;
?>

<?php
} elseif ($mode === "pass_mlang") {
    $id = $_GET['id'] ?? '';
    $email = $_GET['email'] ?? '';
    $newPassword = $_GET['newPassword'] ?? '';
?>

<html>
<head>
<title><?=$admin_name?></title>
<style>
p, br, body, td, input, select, submit {
    color: black; 
    font-size: 9pt; 
    font-family: 굴림;
}
b {
    color: black; 
    font-size: 9pt; 
    font-family: 굴림;
}
</style>
<link rel="stylesheet" type="text/css" href="http://www.script.ne.kr/script.css">
</head>

<body bgcolor='#FFFFFF' leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td height=5 align=left bgcolor='#000000' width=100%></td></tr>
<tr><td height=25 align=left bgcolor='#43B5C9' width=100%>
&nbsp;&nbsp;<b><font color=#FFFFFF>▶ <?=$admin_name?>에서 발송한 메일</font></b>
</td></tr>
<tr><td height=80 align=center bgcolor='#FFFFFF' width=100%>
<BR><BR>
<img src='img/loding.gif'>
<BR><BR>
&nbsp;&nbsp;
<b>
<?php
echo "
$id 님의 비밀번호를 <BR>$email 주소로 발송하고 있습니다.<BR><BR>
<font color=red>*확인 버튼이 나올 동안 창을 닫지 마시기 바랍니다.*</font>
";
?>
</b>
</td></tr>
</table>

<iframe frameborder="0" height="0" width="0" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" scrolling="no" src="<?=htmlspecialchars($_SERVER["PHP_SELF"])?>?mode=pass_mlang_ok&id=<?=$id?>&email=<?=$email?>&newPassword=<?=$newPassword?>"></iframe>
</body>
</html>

<?php
} elseif ($mode === "pass_mlang_ok") {
    $id = $_GET['id'] ?? '';
    $email = $_GET['email'] ?? '';
    $newPassword = $_GET['newPassword'] ?? '';

$query = "SELECT * FROM member WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $connent = $newPassword;
    }

    include_once('../shop/mailer.lib.php');

    ## 메일로 비밀번호를 발송한다. ############################
    $TO_NAME = $id;
    $TO_EMAIL = $email;
    $FROM_NAME = $admin_name;
    $FROM_EMAIL = $admin_email;
    $SUBJECT = "$admin_name 에서 발송한 회원정보 메일입니다.";

    $SEND_CONTENT = "<HTML>
    본 메일은 <a href='$admin_url' target='_blank'>$admin_name</a> 에서 발송한 회원정보 메일입니다.
    <BR><BR>
    $id 회원님의 임시 비밀번호는 $connent 입니다. 
    <BR><BR>
    홈페이지 로그인하시고 편하신대로 수정 바랍니다
    </HTML>";

    // 메일 발송
    mailer($FROM_NAME, $FROM_EMAIL, $TO_EMAIL, $SUBJECT, $SEND_CONTENT);

    echo ("
        <script>
        alert('\\n$id 회원님의 임시 비밀번호가 $TO_EMAIL 로 발송되었습니다.\\n\\n확인해 주시기 바랍니다.... ---- $admin_name ----\\n');
        window.top.close();
        </script>
    ");
    exit;

} else {
    echo ("
        <script>
        alert('\\n정보를 정상적으로 처리하지 못하였습니다.\\n\\n처음부터 다시 시도해 주세요.. --- $admin_name ---\\n');
        history.go(-1);
        </script>
    ");
    exit;
}

$stmt->close();
$mysqli->close();
?>

<?php
} else {
    echo "
    <script>
    window.alert(\"정보가 이상합니다.\\n\\n----- $admin_name -----\");
    window.close();
    </script>";
    exit;
}
?>
</body>
</html>