<?php
// 변수 초기화 (Notice 에러 방지)
$DbDir = isset($DbDir) ? $DbDir : '..';
$WebtingMemberLogin_id = isset($WebtingMemberLogin_id) ? $WebtingMemberLogin_id : '';

include "$DbDir/db.php";
$result= mysqli_query($db, "select * from member where no='1'");
$row= mysqli_fetch_array($result);
$BBSAdminloginKK=$row['id'];

if($WebtingMemberLogin_id=="$BBSAdminloginKK"){
}else{
echo ("<script language=javascript>
window.alert('현페이지는 관리자만 이용할수 있게금 설정되어져 있습니다.');
history.go(-1);
</script>
");
exit;
}
?>