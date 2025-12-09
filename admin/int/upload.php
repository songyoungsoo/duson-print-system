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

// $upload_dir 업로드경로
$MAXFSIZE="2000"; // 이미지의 용량
if(is_uploaded_file($Bigupfile)) {

	$full_filename = split("\.", $Bigupfile_name);
	$file_extention = $full_filename[sizeof($Bigupfile_name)];

	if (preg_match('/^' . $file_extention . '$/i', "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

 	if( $MAXFSIZE * 1024 < $Bigupfile_size) {
		$Bigupfile_kfsize = intval($Bigupfile_size/1024);
		$msg = "업로드하신 파일의 크기가 $Bigupfile_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

	if (is_file("$upload_dir/$Bigupfile_name")) {
		$Bigupfile_name = date("is")."_$Bigupfile_name";
	}
	if ($Bigupfile_size) {
	move_uploaded_file($Bigupfile, "$upload_dir/$Bigupfile_name");
	}
}

$BigUPFILENAME = $Bigupfile_name;
$BigFILESIZE = $Bigupfile_size;
?>