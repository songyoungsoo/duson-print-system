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

if($BBS_ADMIN_write_select=="member"){

// 용도: 게시글을 볼때 포인트를 체크한다. 파일명: PointChick.php
if($Point_TT_mode=="chick"){
if($BBS_ADMIN_PointBoardView){
if($MlangMember_money < $BBS_ADMIN_PointBoardView){ // 회원포인트가 모지라면 에러문 호출
echo ("<script language=javascript>
window.alert('회원님의 Point 점수가 부족합니다.....*^^*\\n\\n본 자료를 보시려면 Point: $BBS_ADMIN_PointBoardView 점이 필요합니다.\\n\\n현재 회원님의 Point 점수는 $MlangMember_money 점 이십니다.');
history.go(-1);
</script>
");
exit;
}

include "$DbDir/db.php";
$PointChickOkf=$MlangMember_money-$BBS_ADMIN_PointBoardView;
$query ="UPDATE member SET money='$PointChickOkf' WHERE id='$WebtingMemberLogin_id'";
$result= mysqli_query($db, $query,$db);
mysqli_close($db); 

}
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($Point_TT_mode=="BoardPointWrite"){
//$BoardPointWriteOkRd=$BBS_ADMIN_PointBoard+$MlangMember_money;
$BoardPointWriteOkRd=500+$MlangMember_money;
include "$DbDir/db.php";
$query ="UPDATE member SET money='$BoardPointWriteOkRd' WHERE id='$WebtingMemberLogin_id'";
$result= mysqli_query($db, $query,$db);
mysqli_close($db); 
}//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($Point_TT_mode=="ComentWrite"){
$ComentWriteOkRd=$BBS_ADMIN_PointComent+$MlangMember_money;
include "$DbDir/db.php";
$query ="UPDATE member SET money='$ComentWriteOkRd' WHERE id='$WebtingMemberLogin_id'";
$result= mysqli_query($db, $query,$db);
mysqli_close($db); 
}//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($Point_TT_mode=="ComentSinGo"){
$ComentWriteOkRd=$MlangMember_money+1000;
include "$DbDir/db.php";
$query ="UPDATE member SET money='$ComentWriteOkRd' WHERE id='$WebtingMemberLogin_id'";
$result= mysqli_query($db, $query,$db);
mysqli_close($db); 
}//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($Point_TT_mode=="ComentRec"){
$ComentWriteOkRd=$MlangMember_money+200;
include "$DbDir/db.php";
$query ="UPDATE member SET money='$ComentWriteOkRd' WHERE id='$TKmember_id'";
$result= mysqli_query($db, $query,$db);
mysqli_close($db); 
}

}
?>