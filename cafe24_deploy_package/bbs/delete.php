<?php
// 변수 초기화 (Notice 에러 방지)
$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');
$page = isset($_GET['page']) ? $_GET['page'] : (isset($_POST['page']) ? $_POST['page'] : '');
$GH_url = isset($_GET['GH_url']) ? $_GET['GH_url'] : '';
$PHP_SELF = $_SERVER['PHP_SELF'];

// BBS 관련 변수들 초기화
$BbsViewMlang_bbs_file = isset($BbsViewMlang_bbs_file) ? $BbsViewMlang_bbs_file : '';
$DelMoney = isset($DelMoney) ? $DelMoney : "500";

if(!$DbDir){$DbDir="..";}
if(!$BbsDir){$BbsDir=".";}

include "$DbDir/db.php";

// 자료를 삭제하면 포인트를 감소시킨다..
$DelMoney="500";
$Point_TT_mode="ComentBBSDelete";
include "$BbsDir/PointChick.php";

include "$DbDir/db.php";
$result = mysqli_query($db, "DELETE FROM Mlang_{$table}_bbs WHERE Mlang_bbs_no='$no'");

if($BbsViewMlang_bbs_file){

if (is_file("./upload/$table/$BbsViewMlang_bbs_file")) { 
unlink("./upload/$table/$BbsViewMlang_bbs_file");
}else{}

}


///////////////////////////////////////////////////////////////////////////////////////////////////////////

//if($GH_url){$GH_urlOk="$GH_url";}else{$GH_urlOk="./bbs.php?table=$table&mode=list&page=$page";}

////////////// 새로 추가한 기능 BBSTOP /////////////////////////
$BBS_TOPresult= mysqli_query($db, "select * from BBS_TOP where BBS_Table='$table' and BBS_No='$no'");
   $BBS_TOProw= mysqli_fetch_array($BBS_TOPresult);
     if($BBS_TOProw){
$result = mysqli_query($db, "DELETE FROM BBS_TOP WHERE no='{$BBS_TOProw['no']}'");
         }
mysqli_close($db);
////////////// 새로 추가한 기능 BBSTOP /////////////////////////

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 자료를 삭제 하였습니다.');
window.top.location.href='$PHP_SELF?table=$table&mode=list&page=$page';
</script>
</html>
");
exit;

?>