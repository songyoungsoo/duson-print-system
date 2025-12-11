<?include "../db.php";?>

<html>
<head>
<title><?=$admin_name?>-아이디/비밀번호찾기</title>
<STYLE>

p,br,body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:굴림;}
b {color:black; font-size:10pt; FONT-FAMILY:굴림;}

</STYLE>
<link rel="stylesheet" type="text/css" href="http://www.script.ne.kr/script.css">
</head>

<?php
if(!strcmp($mode,"id")) {
?>

<head>
<script language="JavaScript">
////////////////////////////////////////////////////////////////////////////////
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;


function TypeCheck (s, spc) {
var i;

for(i=0; i< s.length; i++) {
if (spc.indexOf(s.substring(i, i+1)) < 0) {
return false;
}
}        
return true;
}

/////////////////////////////////////////////////////////////////////////////////
function MemberCheckField() {

var f=document.FrmUserInfo;

	if (f.name.value == "") {
		alert("회원이름을 입력해 주세요.");
		return false;
	}

	if (f.hendphone1.value == "" || f.hendphone2.value == "" || f.hendphone3.value == "") {
		alert("전화번호를 모두 입력해 주세요.");
		return false;
	}
// if (f.name.value == "") {
// alert("회원이름을 입력해 주세요. ");
// return false;
// }

// if (f.hendphone1.value == "") {
// alert("전화번호 앞자리를 입력해 주세요. ");
// return false;
// }
// if ((f.hendphone1.value.length < 2) || (f.phone1.value.length > 4)) {
// alert("전화번호 앞자리는2자리 이상 4자리 이하를 입력하셔야 합니다.");
// return false;
// }
// if (!TypeCheck(f.hendphone1.value, NUM)) {
// alert("전화번호 앞자리는 숫자로만 사용할 수 있습니다.");
// return false;
// }

// if (f.hendphone2.value == "") {
// alert("전화번호 앞자리를 입력해 주세요. ");
// return false;
// }
// if ((f.hendphone2.value.length < 4) || (f.hendphone2.value.length > 4)) {
// alert("전화번호는 4글자 이하 이여야 합니다.");
// return false;
// }
// if (!TypeCheck(f.hendphone2.value, NUM)) {
// alert("전화번호는 숫자로만 사용할 수 있습니다.");
// return false;
// }

// if (f.hendphone3.value == "") {
// alert("전화번호 뒤자리를 입력해 주세요. ");
// return false;
// }
// if ((f.hendphone3.value.length < 4) || (f.hendphone3.value.length > 4)) {
// alert("전화번호 뒤자리는 4글자 이하 이여야 합니다.");
// return false;
// }
// if (!TypeCheck(f.hendphone3.value, NUM)) {
// alert("전화번호는 숫자로만 사용할 수 있습니다.");
// return false;
// }
return true;
}

</script>
</head>

<body bgcolor='#FFFFFF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=5 align=left bgcolor='#000000' width=100%></td></tr>
<tr><td  height=30 align=left bgcolor='#43B5C9' width=100%>
&nbsp;&nbsp;<b><font color=#FFFFFF>▶ 아이디 를 찾아드려염... *^^*</font></b>
</td></tr>
<tr><td  height=15 width=100%></td></tr>
<tr><td align=center bgcolor='#FFFFFF'>

<table border=0 align=center cellpadding='0' cellspacing='0' width=395>
<tr><td align=center width=395 height=9><img src='img/member_search_top.gif' width=395 height=9></td></tr>
<tr><td align=center width=100% background='img/member_search_back.gif' height=90>

<b>아래의 정보를 입력하시면 아이디, 비밀번호 를 찾아줘염!!</b>

<table border=0 align=center cellpadding='0' cellspacing='0' width=395>
<form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField()' action='<?php echo"$PHP_SELF";?>?mode=id_ok'>
<tr>
<td width=90 height=35 align=right valign=bottom>회원이름:&nbsp;</td>
<td width=210  valign=bottom>
<input type='text' NAME=name size='15' maxlength="20">
</td>
</tr>
<tr>
<td height=3 colspan=2 width=100%>
</td>
</tr>
<tr>
<td height=1 colspan=2 bgcolor='#5BB4D0' width=100%>
</td>
</tr>
<tr>
<td height=3 colspan=2 width=100%>
</td>
</tr>
<tr>
<td width=90 height=35 align=right  valign=top><p style='text-indent:0; margin-top:5pt;'> 핸드폰:&nbsp;</p></td>
<td width=210     valign=top>
<input type='text' name='hendphone1' size='8' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone1'");}?>>
-
<input type='text' name='hendphone2' size='8' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone2'");}?>>
-
<input type='text' name='hendphone3' size='8' style='background-color:#E8EFF6; color:#000000; border-style:solid; border:1 solid #7D9AB5;' maxlength="5" <?php if
($MdoifyMode=="view"){echo("value='$MlangMember_hendphone3'");}?>>
</td>
</tr>
</table>

</td></tr>
<tr><td align=center width=395 height=8><img src='img/member_search_down.gif' width=395 height=8></td></tr>
</table>

</td></tr>

<tr><td  height=15 width=100%></td></tr>

<tr><td  height=50  valign=middle align=center bgcolor='#E0E7E0' width=100%>
<input type="image" src='img/member_search_1.gif' width=72 height=23 border=0>
<a href="#" onClick="javascript:window.close();"><img src='img/member_search_2.gif' width=72 height=23 border=0></a>
</td></tr>

</form>
</table>

<?php
} elseif(!strcmp($mode,"id_ok")) {
?>

<?php
//에러부분과 현페이지의 url직접 접근을 막는다........
function ERROR($msg)
{
echo "<script language=javascript>
window.alert('$msg');
history.go(-1);
</script>
";
exit;
}

if ( !$name || !$hendphone1  || !$hendphone2 || !$hendphone3) {
	$msg = "정상적으로 처리하지 못하였습니다."; ERROR($msg); 
}


$query = "SELECT * FROM member WHERE name = '$name' AND hendphone1 = '$hendphone1' AND hendphone2 = '$hendphone2' AND hendphone3 = '$hendphone3'";
$result = mysqli_query($db, $query);

if ($result) {
    $rows = mysqli_num_rows($result);

    if ($rows) {
        while ($row = mysqli_fetch_array($result)) {

// $query ="select * from member where name='$name' and hendphone1='$hendphone1' and hendphone2='$hendphone2' and hendphone3='$hendphone3' ";
// $result = mysql_query($query,$db);
// $rows=mysql_num_rows($result);
// if($rows)
//     {

// while($row= mysql_fetch_array($result)) 
echo"
<head>
<script language=\"JavaScript\">
parent.resizeTo(510,340);

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

/////////////////////////////////////////////////////////////////////////////////
function MemberCheckField()
{
var f=document.FrmUserInfo;

if (f.email.value == \"\") {
alert(\"비밀번호를 받을 E메일 주소를 입력해 주세요.\");
return false;
}

}

</script>
</head>

<body bgcolor='#FFFFFF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0' >
<tr><td  height=5 align=left bgcolor='#000000' width=100%></td></tr>
<tr><td  height=30 align=left bgcolor='#43B5C9' width=100%>
&nbsp;&nbsp;<b><font color=#FFFFFF>▶ 회원님의 아이디를 찾았어염... *^^*</font></b>
</td></tr>
<tr><td  height=15 width=100%></td></tr>
<tr><td align=center bgcolor='#FFFFFF'>
<p align=left style='text-indent:0; margin-top:0pt; margin-right:20pt; margin-bottom:10pt; margin-left:45pt;'>
님의 회원 아이디는 <b><font color=green>$row[id]</font></b> 입니다.
</p>
<table border=0 align=center cellpadding='0' cellspacing='0' width=395>
<tr><td align=center width=395 height=9><img src='img/member_search_top.gif' width=395 height=9></td></tr>
<tr><td width=100% align=center background='img/member_search_back.gif' height=110>
<b><font color=#43B5C9> ----- 비밀번호 를 찾아드립니다. -----</font></b>
<BR><BR>
<p align=left style='text-indent:0; margin-top:10pt; margin-right:20pt; margin-bottom:0pt; margin-left:45pt;'> 
<b>아래에 비밀번호를 확인할 E메일 주소를 입력후<BR> 
확인을 누르시면 <u>비밀번호</u> 를 발송해드립니다.</b><BR>
</p>
<table border=0 align=center cellpadding='0' cellspacing='0' width=300>
<form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField()' action='$PHP_SELF?mode=pass_ok'>
<tr>
<td width=90 height=35 align=right valign=bottom>회원아이디:&nbsp;</td>
<td width=210  valign=bottom>
<input type='hidden' NAME=id value='$row[id]'><b><font color=green>$row[id]</font></b>
</td>
</tr>
<tr>
<td height=3 colspan=2 width=100%>
</td>
</tr>
<tr>
<td height=1 colspan=2 bgcolor='#5BB4D0' width=100%>
</td>
</tr>
<tr>
<td height=3 colspan=2 width=100%>
</td>
</tr>
<tr>
<td width=90 height=35 align=right  valign=top><p style='text-indent:0; margin-top:5pt;'> E메일주소:&nbsp;</p></td>
<td width=210  valign=top>
<input type='text' NAME=email size='30'>
<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td height=3></td></tr></table>
</td>
</tr>
<tr>
<td colspan=2 align=center>
<font color=green><b>$name</b> 님께서 확인가능한 메일이여야 합니다.</font>
</td>
</tr>
</table>

</td></tr>
<tr><td align=center width=395 height=8><img src='img/member_search_down.gif' width=395 height=8></td></tr>
</table>

</td></tr>

<tr><td  height=15 width=100%></td></tr>

<tr><td  height=50  valign=middle align=center bgcolor='#E0E7E0' width=100%>
<input type=\"image\" src='img/member_search_1.gif' width=72 height=23 border=0>
<a href=\"#\" onClick=\"javascript:window.close();\"><img src='img/member_search_2.gif' width=72 height=23 border=0></a>
</td></tr>

</form>
</table>
";
exit;

        }
    }
 } else { 

	echo ("
		<script language=javascript>
		alert('\\n$name 님 께서 요청해주신 이름과 핸드폰전화번호로는\\n\\n검색되는 회원 ID 가 없습니다..   ----- $admin_name -----\\n\\n[회원가입을 이용하여 가입하시기 바랍니다.]\\n');
		history.go(-1);
		</script>
		");
		exit;
    echo "Query execution failed: " . mysqli_error($db);
}

// Don't forget to close the connection when done
mysqli_close($db);	

} elseif (!strcmp($mode,"pass_ok")) {
?>

<?php
//에러부분과 현페이지의 url직접 접근을 막는다........
function ERROR($msg)
{
echo "<script language=javascript>
window.alert('$msg');
history.go(-1);
</script>
";
exit;
}

if ( !$id || !$email ) {
		$msg = "정상적으로 처리하지 못하였습니다.\\n\\n처음부터 다시 이용해주세염!!\\n\\n----- $admin_name -----"; ERROR($msg);  
}

{ 
  if (!eregi("^[^@ ]+@[a-zA-Z0-9\-\.]+\.+[a-zA-Z0-9\-\.]", $email)) { 
    	$msg = "\\n메일주소가 부정확하거나 올바른 입력이 아닙니다.\\n\\n재 작성해 주시기 바랍니다. --- $admin_name---\\n"; ERROR($msg); 
  } 
  /* 한글이 포함되었는지 체크 */ 
  for($i = 1; $i <= strlen($email); $i++) { 
    if ((Ord(substr("$email", $i - 1, $i)) & 0x80)) { 
      	$msg = "\\n메일주소에 한글은 포함될수 없습니다.\\n\\n재 작성해 주시기 바랍니다. --- $admin_name---\\n"; ERROR($msg); 
    } 
  } 

}

##메일 주소를 업데이트 시킨다.############################
function generateRandomString($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

$temporaryPassword = generateRandomString(12);

// $temporaryPassword = bin2hex(random_bytes(8)); // 8자리의 랜덤한 문자열
// TODO: 데이터베이스에 임시 비밀번호를 저장하는 코드를 작성
$query ="UPDATE member SET pass = '$temporaryPassword' WHERE id='$id'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}

	echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=pass_mlang&id=$id&email=$email'>";
		exit

?>

<?php
}elseif(!strcmp($mode,"pass_mlang")) {
?>

<html>
<head>
<title><?=$admin_name?></title>
<STYLE>

p,br,body,td,input,select,submit {color:black; font-size:10pt; FONT-FAMILY:굴림;}
b {color:black; font-size:10pt; FONT-FAMILY:굴림;}

</STYLE>
<link rel="stylesheet" type="text/css" href="http://www.script.ne.kr/script.css">
</head>

<body bgcolor='#FFFFFF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=5 align=left bgcolor='#000000' width=100%></td></tr>
<tr><td  height=25 align=left bgcolor='#43B5C9' width=100%>
&nbsp;&nbsp;<b><font color=#FFFFFF>▶ <?=$admin_name?>메일 발송중</font></b>
</td></tr>
<tr><td  height=80 align=center bgcolor='#FFFFFF' width=100%>
<BR><BR>
<img src='img/loding.gif'>
<BR><BR>
&nbsp;&nbsp;
<b>
<?php
echo"
$id 님의 비밀번호를 <BR>$email  주소로 발송하고 있습니다.<BR><BR>
<font color=red>*확인 버튼이 나올동안 현창을 닫지 마시기 바랍니다.*</font>
";
?>
</b>
</td></tr>
</table>

<iframe frameborder="0" height="0" width="0" topmargin="0"  leftmargin="0" marginheight="0" marginwidth="0" scrolling="no" src="<?echo"$PHP_SELF?mode=pass_mlang_ok&id=$id&email=$email";?>"></iframe>
</body>
</html>

<?php
}elseif(!strcmp($mode,"pass_mlang_ok")) {

$query ="select * from member where id='$id'";
$result = mysql_query($query,$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result))
$connent= $row['pass']; 
include_once('../shop/mailer.lib.php');
##메일로 비밀번호를 발송한다.############################
$TO_NAME="$id";
$TO_EMAIL="$email";
$FROM_NAME="$admin_name";
$FROM_EMAIL="$admin_email";
$SUBJECT="$admin_name  에서 발송한 회원정보 메일입니다.";

$SUBJECT = stripslashes($SUBJECT);
$CONTENT = nl2br($CONTENT);

$SEND_CONTENT = "<html>
<head>
    <title>회원정보메일</title>
</head>
<body>

<p>본 메일은 <a href='$admin_url' target='_blank'>$admin_name</a>에서 발송한 회원정보 메일입니다.</p>
<br><br>
<p>$id 회원님의 임시 비밀번호는 $connent 입니다.</p>
<br><br>
<p><a href='http://www.dsp114.com/member/login.php' target='_blank'>로그인</a> 후 비밀번호를 변경하세요.</p>

</body>
</html>";
include_once('../shop/mailer.lib.php');
// $content = $body;
$content = $SEND_CONTENT;
$to = "$email";
$subject = "$TO_NAME 님 비밀번호 요청 안내문입니다.";
//echo $body;
//echo $subject;
//echo $content;
// mailer("보내는 사람 이름", "보내는 사람 메일주소", "받는 사람 메일주소", "제목", "내용", "1");
mailer($fname, $fmail, $to, $subject, $content);
// if($FROM_NAME && $FROM_EMAIL) {
// 	$from = "\"$FROM_NAME\" <$FROM_EMAIL>";
// }
// else {
// 	$from = "$FROM_EMAIL";
// }
// $TO = "\"$TO_NAME\" <$TO_EMAIL>";
// $from = "From:$from\nContent-Type:text/html";

// $content = $SEND_CONTENT;
// $to = "\"$TO_NAME\" <$TO_EMAIL>";
// $subject = "$TO_NAME 님 비밀번호 관련 안내문입니다.";

// // mailer($fname, $fmail, $to, $subject, $content, $type = 1, $file, $cc = "", $bcc = "");
// mailer("$fname", "$fmail", "$to", "$subject", "$content", $type=1);

echo ("
		<script>
		alert('\\n$id 회원님의 임시비밀번호가  $TO_EMAIL 로 발송 되었으니\\n\\n확인해 주시기 바랍니다.... ---- $admin_name ----\\n');
		window.top.close();
		</script>
		");
		exit;

	}
##에러문을 호출해준다.############################
    else
    {

echo ("
		<script>
		alert('\\n정보수행을 정상적으로 처리하지못하였습니다.\\n\\n처음부터 다시 이용바랍니다.. --- $admin_name ---\\n');
		history.go(-1);
		</script>
		");
		exit;

	}

mysql_close($db);

?>

<?php
} else {

echo "
			<script>
				window.alert(\"정보가 이상합니다.\\n\\n----- $admin_name  -----\")
				window.close();
			</script>";
	exit;
}
?>

</body>
</html>