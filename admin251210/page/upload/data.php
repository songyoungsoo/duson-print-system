<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

$MAXFSIZE="2000";
if(is_uploaded_file($FILELINK)) {

	$full_filename = split("\.", $FILELINK_name);
	$file_extention = $full_filename[sizeof($FILELINK_name)];

 	if( $MAXFSIZE * 1024 < $FILELINK_size) {
		$FILELINK_kfsize = intval($FILELINK_size/1024);
		$msg = "업로드하신 파일의 크기가 $FILELINK_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";

echo("<SCRIPT LNAGUAGE=JAVASCRIPT>
window.alert('$msg');
history.go(-1);
</SCRIPT>");
exit;

	}

	if (is_file("$tty/upload/data/$FILELINK_name")) {
		$FILELINK_name = date("is")."_$FILELINK_name";
	}
	if ($FILELINK_size){
	move_uploaded_file($FILELINK, "$tty/upload/data/$FILELINK_name");
	}
}
$FILELINK_ok = $FILELINK_name;
$FILESIZE_ok = $FILELINK_size;
?>