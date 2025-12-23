<?
// $upload_dir 업로드경로
$MAXFSIZE="2000"; // 이미지의 용량
if(is_uploaded_file($photofile)) {

	$full_filename = split("\.", $photofile_name);
	$file_extention = $full_filename[sizeof($photofile_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

 	if( $MAXFSIZE * 1024 < $photofile_size) {
		$photofile_kfsize = intval($photofile_size/1024);
		$msg = "업로드하신 파일의 크기가 $photofile_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

	if (is_file("$upload_dir/$photofile_name")) {
		$photofile_name = date("is")."_$photofile_name";
	}
	if ($photofile_size) {
	move_uploaded_file($photofile, "$upload_dir/$photofile_name");
	}
}

$photofileNAME = $photofile_name;
?>