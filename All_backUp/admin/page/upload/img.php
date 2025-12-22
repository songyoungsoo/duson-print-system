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

$MAXFSIZE="200"; // 이미지의 용량
if(is_uploaded_file($upfile)) {

	$full_filename = split("\.", $upfile_name);
	$file_extention = $full_filename[sizeof($upfile_name)];

	if (preg_match('/^' . $file_extention . '$/i', "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		include ("./ERROR.php3");
		exit;
	}

 	if( $MAXFSIZE * 1024 < $upfile_size) {
		$upfile_kfsize = intval($upfile_size/1024);
		$msg = "업로드하신 파일의 크기가 $upfile_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
		include ("./ERROR.php3");
		exit;
	}

	if (is_file("../table/$table/upload/$upfile_name")) {
		$upfile_name = date("is")."_$upfile_name";
	}
	if ($upfile_size) {
	move_uploaded_file($upfile, "../table/$table/upload/$upfile_name");
	}
}

$UPFILENAME = $upfile_name;
$FILESIZE = $upfile_size;
?>
