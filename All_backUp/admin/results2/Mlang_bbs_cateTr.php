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

include"data_admin_fild.php";

if($mode=="Submit"){

	$CATEGORY_LIST_script = split(":", $DataAdminFild_celect);
	$k = 0;
	while($k < sizeof($CATEGORY_LIST_script)) {
		if($F=="$CATEGORY_LIST_script[$k]"){
		echo ("<script language=javascript>
		          alert('\\n$F 자료는 이미 등록되어있습니다. 다른것으로 입력해주세요\\n');
                 opener.parent.location.reload();
                  window.self.close();
		       </script>");
		     exit;}
		$k++;
	} 



$Ok_celect="${DataAdminFild_celect}:${F}";

include"../../db.php";
$query ="UPDATE Mlnag_Results_Admin SET celect='$Ok_celect' WHERE id='$id'";
$result= mysqli_query($db, $query);
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				window.self.close();
			</script>";
		exit;

} else {
	
	echo ("<script language=javascript>
		          alert('\\n$F 자료을 추가로 입력하였습니다.\\n');
                 opener.parent.location.reload();
                 window.self.close();
		       </script>");
		     exit;

}

mysqli_close($db);

} /////////////////////////////////////////////////////////////////////////////////////

if($mode=="Modify"){

	$CATEGORY_LIST_script = split(":", $DataAdminFild_celect);
	$k = 0;
	while($k < sizeof($CATEGORY_LIST_script)) {
		if($F=="$CATEGORY_LIST_script[$k]"){
		echo ("<script language=javascript>
		          alert('\\n$F 자료는 이미 등록되어있습니다. 다른것으로 입력해주세요\\n');
                 opener.parent.location.reload();
                  window.self.close();
		       </script>");
		     exit;}
		$k++;
	} 


} /////////////////////////////////////////////////////////////////////////////////////

if($mode=="Delete"){
include"../../db.php";

// id 에 있는 모든 자료 다 삭제 처리 해야줘야 함///////////

// 1차 F관련된 모든자료 검색 하여 no 의폴더들삭제처리
$result= mysqli_query($db, "select * from Mlang_${id}_Results where Mlang_bbs_secret='$F'");
$rows=mysqli_num_rows($result);
if($rows){

while($row= mysqli_fetch_array($result)) { 
 $dir_path = "$DOCUMENT_ROOT/results/upload/$id/$row[Mlang_bbs_no]";
	$Mlang_DIR = opendir("$dir_path"); // upload 폴더 OPEN
	while($ufiles = readdir($Mlang_DIR)) {
		if(($ufiles != ".") && ($ufiles != "..")) {
			unlink("$dir_path/$ufiles"); // 파일들 삭제
		}
	}
	closedir($Mlang_DIR);

rmdir("$dir_path");  // upload 폴더 삭제

mysqli_query($db, "DELETE FROM Mlang_${id}_Results WHERE Mlang_bbs_no='$row[Mlang_bbs_no]'");
                                                          }

}else{}

///////////////////////////////////////////////////////

	$ListOne= eregi_replace("$F", "", $DataAdminFild_celect);
    $ListTwo= eregi_replace("::", ":", $ListOne); 
	$ListTree="@${ListTwo}@";
	$ListFour= eregi_replace("@:", "", $ListTree); 
	$ListFive= eregi_replace(":@", "", $ListFour);
    $ListSix= eregi_replace("@", "", $ListFive);

$query ="UPDATE Mlnag_Results_Admin SET celect='$ListSix' WHERE id='$id'";
$result= mysqli_query($db, $query);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				window.self.close();
			</script>";
		exit;

} else {
	
	echo ("<script language=javascript>
		          alert('\\n정상적으로 자료을 삭제 하였습니다.\\n');
                 opener.parent.location.reload();
                 window.self.close();
		       </script>");
		     exit;

}

mysqli_close($db);

} /////////////////////////////////////////////////////////////////////////////////////
?>