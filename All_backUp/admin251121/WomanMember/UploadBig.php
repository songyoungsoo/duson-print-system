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

$MAXFSIZE="500"; // 이미지의 용량
if(is_uploaded_file($PhotoFileBig)) {

	$full_filename = split("\.", $PhotoFileBig_name);
	$file_extention = $full_filename[sizeof($PhotoFileBig_name)];

	if (preg_match('/^' . $file_extention . '$/i', "html|php3|phtml|inc|asp")) {
		$msg = "\\n사진자료를 업로드중에 문제가 발생하였습니다.\\n\\n회원정보수정에서 사진자료를 재입력하시면 됩니다.\\n\\n문제요인: php / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

 	if( $MAXFSIZE * 1024 < $PhotoFileBig_size) {
		$PhotoFileBig_kfsize = intval($PhotoFileBig_size/1024);
		$msg = "\\n사진자료를 업로드중에 문제가 발생하였습니다.\\n\\n회원정보수정에서 사진자료를 재입력하시면 됩니다.\\n\\n문제요인: 업로드하신 파일의 크기가 $PhotoFileBig_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

	if (is_file("$upload_dir/$PhotoFileBig_name")) {
		$PhotoFileBig_name = date("is")."_$PhotoFileBig_name";
	}
	if ($PhotoFileBig_size) {
	move_uploaded_file($PhotoFileBig, "$upload_dir/$PhotoFileBig_name");
	}
}

$PhotoFileBigName = $PhotoFileBig_name;
?>