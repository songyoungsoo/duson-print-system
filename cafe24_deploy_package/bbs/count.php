<?php
// 변수 초기화 (Notice 에러 방지)
$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');
$REMOTE_ADDR = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$HTTP_COOKIE_VARS = isset($_COOKIE) ? $_COOKIE : array();
$home_cookie_url = isset($home_cookie_url) ? $home_cookie_url : '';

// BBS 관련 변수들 초기화
$BbsViewMlang_bbs_rec = isset($BbsViewMlang_bbs_rec) ? $BbsViewMlang_bbs_rec : 0;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

if(!strcmp($mode,"req")) {  // 추천

include "view_fild.php";
include "../db.php";
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$ip_win_top="$REMOTE_ADDR";
$ip_gogo="req_$table$no";
///////////// 첨에 ip를 쿠키로굽는다.... /////////////////////////////////////
setcookie("$ip_gogo",$ip_win_top,0,"/","$home_cookie_url");
////////////////////////////////////////////////////////////////////////////////

$ip_is="$HTTP_COOKIE_VARS[$ip_gogo];

if($ip_is=="$REMOTE_ADDR"){  // ip값이 동일하면 걍 닫아버린다...

	echo ("
		<script language=javascript>
         window.alert('한 자료에 두번의 추천을 하실수 없습니다..');
		 window.self.close();
		</script>
	");
		exit;

}else{
$req_ok=$BbsViewMlang_bbs_rec+1;
// 카운터를 증가 시킨다....
$query ="UPDATE Mlang_{$table}_bbs SET Mlang_bbs_rec='$req_ok' WHERE Mlang_bbs_no='$no'";
$result= mysqli_query($db, $query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
		      window.alert(\"추천 에러(87654): DB 접속 에러입니다!\")
              window.self.close();
			</script>";
		exit;

} else {

//$BbsViewMlang_bbs_member 에게 포인트를 적립시킨다(100으로 설정햇음)
if($BbsViewMlang_bbs_member){
$DbDir="..";
$db_dir="..";
include "./admin_fild.php";
$TKmember_id="$BbsViewMlang_bbs_member";
include "../member/member_fild_member.php";
$Point_TT_mode="ComentRec";
include "PointChick.php"; 
}	
	echo ("
		<script language=javascript>
        window.alert('추천을 해주심에 감사 드립니다..\\n\\n글등록인 에게 Point200점을 충전하였습니다.\\n\\n$TKmember_id 님도 기뻐하실겁니다.*^^*');
         window.self.close();
		</script>
	");
		exit;

}

}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


}elseif(!strcmp($mode,"count")) {  //  조회
 
include "view_fild.php";
include "../db.php";
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$ip_win_top="$REMOTE_ADDR";
$ip_gogo2="count_$table$no";
///////////// 첨에 ip를 쿠키로굽는다.... /////////////////////////////////////
setcookie("$ip_gogo2",$ip_win_top,0,"/","$home_cookie_url");
////////////////////////////////////////////////////////////////////////////////

$ip_is2="$HTTP_COOKIE_VARS[$ip_gogo2];

if($ip_is2=="$REMOTE_ADDR"){  // ip값이 동일하면 걍 닫아버린다...
}else{

$count_ok=$BbsViewMlang_bbs_count+1;

// 카운터를 증가 시킨다....
$query ="UPDATE Mlang_{$table}_bbs SET Mlang_bbs_count='$count_ok' WHERE Mlang_bbs_no='$no'";
$result= mysqli_query($db, $query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
		      window.alert(\"카운터에러(7282): DB 접속 에러입니다!\")
			</script>";
		exit;

} else {}

mysqli_close($db);

}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


} else { 

echo" 
<script language=javascript> 
alert('정상적인 접근이 아닙니다... 이상한 장난 치지마잉..*^^*\\n\\n프로그램의 절대강자 ㅡ websil.net') 
</script> 
"; 
exit; 

} 
?>
