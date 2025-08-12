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

$BbsDir=".";
$DbDir="..";
include "$BbsDir/view_fild.php";
include "$BbsDir/admin_fild.php";
include "./view_select.php";
?>

<title><?php echo $BbsViewMlang_bbs_title?>- ???</title>
<meta http-equiv='Content-type' content='text/html; charset=UTF-8'>

<?php echo $BbsViewMlang_bbs_connent?>