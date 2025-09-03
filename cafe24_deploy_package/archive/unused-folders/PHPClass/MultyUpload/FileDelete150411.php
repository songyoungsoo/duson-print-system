<?php
<?php
if($FileDelete=="ok"){
unlink("../../ImgFolder/$Turi/$Ty/$Tmd/$Tip/$Ttime/$FileName"); 

echo("
<html>
<script>
window.self.close();
</script>
</html>
");
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($DirDelete=="ok"){

$TTDEL_Dir="../../ImgFolder/$Turi/$Ty/$Tmd/$Tip/$Ttime";
	$Mlang_DIR = opendir("$TTDEL_Dir"); // upload 폴더 OPEN
	while($ufiles = readdir($Mlang_DIR)) {
		if(($ufiles != ".") && ($ufiles != "..")) {
			unlink("$TTDEL_Dir/$ufiles"); // 파일들 삭제
	}
	}
	closedir($Mlang_DIR);

	rmdir("$TTDEL_Dir");  // upload 폴더 삭제

echo("
<html>
<script>
window.self.close();
</script>
</html>
");

}
?>