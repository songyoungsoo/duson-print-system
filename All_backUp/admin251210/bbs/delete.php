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

include"../../db.php";
include"../config.php"; // 관리자 로그인

mysqli_query($db, "DROP TABLE Mlang_${id}_bbs");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요
  // Board테이블 삭제
mysqli_query($db, "DROP TABLE Mlang_${id}_bbs_coment");  // Coment테이블 삭제
$result = mysqli_query($db, "DELETE FROM mlang_bbs_admin WHERE id='$id'"); // 게시판관리자료 삭제
mysqli_close($db);



	$Mlang_DIR = opendir("../../bbs/upload/$id"); // upload 폴더 OPEN
	while($ufiles = readdir($Mlang_DIR)) {
		if(($ufiles != ".") && ($ufiles != "..")) {
			unlink("../../bbs/upload/$id/$ufiles"); // 파일들 삭제
		}
	}
	closedir($Mlang_DIR);

	rmdir("../../bbs/upload/$id");  // upload 폴더 삭제


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 게시판의 모든 자료를 삭제 하였습니다.');
</script>
<meta http-equiv='Refresh' content='0; URL=../bbs_admin.php?mode=list'>
</html>
");
exit;

?>