<?php
<?php
//$upload_dir="./upload";
//$MAXFSIZE="2000000"; // 이미지의 용량

if(is_uploaded_file($user_file)) {

	$full_filename = explode("\.", $user_file_name);
	$file_extention = $full_filename[sizeof($user_file_name)];

	if (preg_match($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

 	if( $MAXFSIZE * 1024 < $user_file_size) {
		$user_file_kfsize = intval($user_file_size/1024);
		$msg = "업로드하신 파일의 크기가 $user_file_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

	if (is_file("$upload_dir/$user_file_name")) {
		$user_file_name = date("is")."_$user_file_name";
	}
	if ($user_file_size) {
	move_uploaded_file($user_file, "$upload_dir/$user_file_name");
	}
}

$BigUPFILENAME = $user_file_name;
$BigFILESIZE = $user_file_size;
?>