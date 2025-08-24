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

$time=date("H, i, s, d, m, Y"); 
$today=mktime($time);  
if($WriteTime > $today){
echo ("<script language=javascript>
window.alert('현 게시판의 불법 스팸 게시판 등록프로그램의 불법 게시글을 방지하기 위하여\\n\\n글의 입력시간이 10초이상이어야 글을 등록하실수 있습니다... ');
history.go(-1);
</script>
");
exit;
}
?> 