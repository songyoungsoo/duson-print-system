<?php
// 변수 초기화 (Notice 에러 방지)
$BBS_ADMIN_write_select = isset($BBS_ADMIN_write_select) ? $BBS_ADMIN_write_select : '';
$HTTP_COOKIE_VARS = isset($_COOKIE) ? $_COOKIE : array();
$Homedir = isset($Homedir) ? $Homedir : '';
$BbsDir = isset($BbsDir) ? $BbsDir : '.';

if($BBS_ADMIN_write_select=="member"){

$Login_pageselfurl = preg_replace("&", "@", $_SERVER['REQUEST_URI']);
if(!$HTTP_COOKIE_VARS['id_login_ok']){
echo ("<script language=javascript>
window.alert('현 게시판은 회원만 글을 등록할수 있습니다..\\n\\n로그인 후 글을 등록해주시기 바랍니다..');
</script>
<meta http-equiv='Refresh' content='0; URL=$Homedir/member/index.php?LoginChickBoxUrl=$Login_pageselfurl'>
");
exit;
}

}else if($BBS_ADMIN_write_select=="admin"){

include "$BbsDir/write_select_admin.php";

}else{}
?>