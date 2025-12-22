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

include"../../db.php";
include"../config.php";

$T_DirUrl="../../mlangprintauto";
include"$T_DirUrl/ConDb.php";

$T_DirFole="./int/info.php";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="ModifyOk"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

$query ="UPDATE mlangorder_printauto SET Type_1='$TypeOne', name='$name', email='$email', zip='$zip', zip1='$zip1', zip2='$zip2', phone='$phone', Hendphone='$Hendphone', delivery='$delivery', bizname='$bizname', bank='$bank', bankname='$bankname', cont='$cont', Gensu='$Gensu' WHERE no='$no'";
$result= mysqli_query($db, $query);
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

	if(!$result) {
		echo "
			<script language=javascript>
			<meta charset='euc-kr'>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		<meta charset='euc-kr'>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$no'>
<meta charset='euc-kr'>
	");
		exit;

}

mysqli_close($db);

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="SubmitOk"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

?>
