<?php
// 변수 초기화 (Notice 에러 방지)
$BBS_ADMIN_view_select = isset($BBS_ADMIN_view_select) ? $BBS_ADMIN_view_select : '';
$BbsViewMlang_bbs_secret = isset($BbsViewMlang_bbs_secret) ? $BbsViewMlang_bbs_secret : 'yes';
$HTTP_COOKIE_VARS = isset($_COOKIE) ? $_COOKIE : array();
$Homedir = isset($Homedir) ? $Homedir : '';
$BbsDir = isset($BbsDir) ? $BbsDir : '.';

if($BBS_ADMIN_view_select=="member"){  // 회원만 이용가능 

// 회원 로그인 제어.................
$Login_pageselfurl = preg_replace("&", "@", $_SERVER['REQUEST_URI']);
if(!$HTTP_COOKIE_VARS['id_login_ok']){
echo ("<script language=javascript>
window.alert('현 페이지는 회원 로그인후 이용할수 있습니다.');
</script>
<meta http-equiv='Refresh' content='0; URL=$Homedir/member/login.php?LoginChickBoxUrl=$Login_pageselfurl'>
");
exit;
}

// 비공개 제어 ####################################################################################
if($BbsViewMlang_bbs_secret=="no"){

$TT_msg="현페이지는 비공개 글입니다...*^^*";
include "bbs_chick.php";

} // ###########################################################################################


}else if($BBS_ADMIN_view_select=="admin"){  // 관리자만 이용가능

if(!$BbsDir){$BbsDir=".";}

$TT_msg="현페이지는 관리자만 이용가능하게 설정 되어져 있습니다..*^^*";
include "$BbsDir/bbs_chick.php";


}else{ // 아무나 이용가능 다통과 

// 비공개 제어 ####################################################################################
if($BbsViewMlang_bbs_secret=="no"){

if(!$BbsDir){$BbsDir=".";}
$TT_msg="현페이지는 비공개 글입니다...*^^*";
include "$BbsDir/bbs_chick.php";

} // ###########################################################################################

}
?>