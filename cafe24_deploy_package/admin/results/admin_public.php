<?php
if($mode=="delete"){

include"../config.php";

mysql_query("DROP TABLE Mlang_${id}_Results");  // 테이블 삭제
$result = mysql_query("DELETE FROM Mlnag_Results_Admin WHERE id='$id'"); // 게시판관리자료 삭제
mysql_close();


// 전체 디렉토리를 연다. //////////////////////////////////////////////////////////
$dir_path = "../../results/upload/$id";
$dir_handle = opendir($dir_path);

// 전체 디렉토리 내용을 출력한다.
while($tmp = readdir($dir_handle))
{

// 한 폴더에 대한 전체 파일 삭제후 그 폴더 삭제 --------------------//

	$Mlang_DIR = opendir("$dir_path/$tmp"); // upload 폴더 OPEN
	while($ufiles = readdir($Mlang_DIR)) {
              if(($ufiles != ".") && ($ufiles != "..")) {
			  unlink("$dir_path/$tmp/$ufiles"); // 파일들 삭제
		}
	}
	closedir($Mlang_DIR);

	rmdir("$dir_path/$tmp");  // upload 폴더 삭제

//-----------------------------------------------------------//


}

closedir($dir_handle);

rmdir("../../results/upload/$id");  // upload 폴더 삭제

////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 실적물 시스템 의 모든 자료를 삭제 하였습니다.');
</script>
<meta http-equiv='Refresh' content='0; URL=../results/admin.php?mode=list'>
</html>
");

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="admin_modify"){

include"../config.php";

if ( !$title ) {
echo ("<script language=javascript>
window.alert('타이틀(제목명)이 입력되지 않았습니다..');
history.go(-1);
</script>
");
exit;
}


$query ="UPDATE Mlnag_Results_Admin SET item='$item', title='$title', celect='$celect' WHERE no='$no'";
$result= mysql_query($query,$db);
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
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list'>
	");
		exit;

}

mysql_close($db);


}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>