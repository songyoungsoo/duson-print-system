<?
if(is_uploaded_file($File2)) {

	$full_filename = split("\.", $File2_name);
	$file_extention = $full_filename[sizeof($File2_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

 	if( $MAXFSIZE * 1024 < $File2_size) {
		$File2_kfsize = intval($File2_size/1024);
		$msg = "업로드하신 파일의 크기가 $File2_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

	if (is_file("$upload_dir/$File2_name")) {
		$File2_name = date("is")."_$File2_name";
	}
	if ($File2_size) {
	move_uploaded_file($File2, "$upload_dir/$File2_name");
	}
}

$File2NAME = $File2_name;
?>