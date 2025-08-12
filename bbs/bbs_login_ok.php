<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

$db = mysqli_connect("host", "user", "password", "dataname");
if (!$db) {
    die("DB 연결 실패: " . mysqli_connect_error());
}
?>

if($bbs_pass){

if(!$DbDir){$DbDir="..";}
include "$DbDir/db.php";

// 최고관리자 패스워드 -----------------------------------------------------------------------------
$admin_result= mysqli_query($db, "select pass from member where no='1'",$db);
$admin_row= mysqli_fetch_array($admin_result);
$PP_BBS_login1="$admin_row[0];

// 보드관리자 패스워드 -----------------------------------------------------------------------------
$bbs_admin_result= mysqli_query($db, "select pass from  Mlang_BBS_Admin where id='$table'",$db);
$bbs_admin_row= mysqli_fetch_array($bbs_admin_result);
$PP_BBS_login2="$bbs_admin_row[0];

 // 보드 글 등록자인지 -----------------------------------------------------------------------------
$bbs_write_result= mysqli_query($db, "select Mlang_bbs_pass from Mlang_{$table}_bbs where Mlang_bbs_no='$no'",$db);
$bbs_write_row= mysqli_fetch_array($bbs_write_result);
$PP_BBS_login3="$bbs_write_row[0];

mysqli_close($db);

// ########################################################################################### //

if($bbs_pass=="$PP_BBS_login1"){ // 최고 관리자
setcookie("bbs_login",$bbs_pass,0,"/","$home_cookie_url");
echo("<html><meta http-equiv='Refresh' content='0; URL=$GH_url?mode=$mode&table=$table&no=$no&GH_url=$GH_url'></html>");

}else if($bbs_pass=="$PP_BBS_login2"){ // 보드 관리자
setcookie("bbs_login",$bbs_pass,0,"/","$home_cookie_url");
echo("<html><meta http-equiv='Refresh' content='0; URL=$GH_url?mode=$mode&table=$table&no=$no&GH_url=$GH_url'></html>");

}else if($bbs_pass=="$PP_BBS_login3"){ // 글쓴사람
setcookie("bbs_login",$bbs_pass,0,"/","$home_cookie_url");
echo("<html><meta http-equiv='Refresh' content='0; URL=$GH_url?mode=$mode&table=$table&no=$no&GH_url=$GH_url'></html>");

}else{

echo ("<script language=javascript>
window.alert('입력하신 비밀번호로는 이용 권환이 없습니다...');
history.go(-1);
</script>
");
exit;

}


// ########################################################################################### //


}else{ // 패스 워드 값이 없음으로 뒤로 빽 //////////////////////////////////////////////////////////////////////////////////////

echo ("<script language=javascript>
window.alert('현 페이지를 이용하시려면 비밀번호를 입력해 주셔야 합니다.');
history.go(-1);
</script>
");
exit;

}


?>