<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

include"../db.php";
include"./config.php";
include"./title.php";

if($mode=="modify"){
?>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=380,availHeight=200);
</script>

<script src="./js/coolbar.js" type="text/javascript"></script>
<script src="./js/login.js" type="text/javascript"></script>

<body class='coolBar'>

<p align=center><font style='color:#000000; line-height:150%; font-size:10pt;'>
변경할 관리자의 <u>아이디와 비밀번호를 입력</u>하여 주십시요...
</font></p>

   <table border=0 align=center cellpadding=5 cellspacing=0>
   <form name='AdminKingInfo' method='post' OnSubmit='javascript:return AdminKingCheckField()' action='<?=$PHP_SELF?>'>
   <INPUT TYPE="hidden" name='mode' value='AdminOk'>
      <tr>
         <td align=center>아이디</td>
		 <td align=center><input type=text name=id maxlength=12 value="<?=$adminid?>" readonly style='background-color:#429EB2; color:#FFFFFF;'></td>
	  </tr>
      <tr>
         <td align=center>비밀번호</td>
		 <td align=center><input type=password name=pass maxlength=20 value="<?=$adminpasswd?>"></td>
	  </tr>
      <tr>
         <td align=center colspan=2>&nbsp;&nbsp;&nbsp;<input type=submit value=' 변경하기 '></td>
	  </tr>
	</form>
	</table>



</body>
</html>

<?php }

if($mode=="AdminOk"){

// ✅ users 테이블 업데이트 (member → users 마이그레이션, prepared statement)
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
$query = "UPDATE users SET username = ?, password = ? WHERE is_admin = 1";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "ss", $id, $hashed_pass);
$result = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// ✅ member 테이블 동시 쓰기 (하위 호환성)
$member_query = "UPDATE member SET id = ?, pass = ? WHERE no = '1'";
$member_stmt = mysqli_prepare($db, $member_query);
if ($member_stmt) {
    mysqli_stmt_bind_param($member_stmt, "ss", $id, $hashed_pass);
    mysqli_stmt_execute($member_stmt);
    mysqli_stmt_close($member_stmt);
}

	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n\\n\\n정보를 변경하였음으로 재 로그인 하셔야 합니다.');
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=modify'>
	");
		exit;

}

mysqli_close($db);


}
?>