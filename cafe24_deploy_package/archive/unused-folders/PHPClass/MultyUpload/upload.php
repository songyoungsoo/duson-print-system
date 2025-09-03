<?php
$TopDir="../../ImgFolder";

$Turi_dir = "$TopDir/$Turi"; 
$Turi_dirOk = is_dir("$Turi_dir"); 
if(!$Turi_dirOk ){mkdir("$Turi_dir", 0755); chmod("$Turi_dir", 0777);}

$Ty_dir = "$TopDir/$Turi/$Ty";
$Ty_dirOk = is_dir("$Ty_dir"); 
if(!$Ty_dirOk ){mkdir("$Ty_dir", 0755); chmod("$Ty_dir", 0777);}

$Tmd_dir = "$TopDir/$Turi/$Ty/$Tmd"; 
$Tmd_dirOk = is_dir("$Tmd_dir"); 
if(!$Tmd_dirOk){mkdir("$Tmd_dir", 0755); chmod("$Tmd_dir", 0777);}

$Tip_dir = "$TopDir/$Turi/$Ty/$Tmd/$Tip"; 
$Tip_dirOk = is_dir("$Tip_dir"); 
if(!$Tip_dirOk){mkdir("$Tip_dir", 0755); chmod("$Tip_dir", 0777);}

$Ttime_dir = "$TopDir/$Turi/$Ty/$Tmd/$Tip/$Ttime"; 
$Ttime_dirOk = is_dir("$Ttime_dir"); 
if(!$Ttime_dirOk){mkdir("$Ttime_dir", 0755); chmod("$Ttime_dir", 0777);}

$upload_dir="$Ttime_dir";
$MAXFSIZE="200000"; // 이미지의 용량

if(is_uploaded_file($MlangMultyFile)) {

	$full_filename = explode(".", $MlangMultyFile_name);
	$file_extention = strtolower(end($full_filename));

	if (preg_match("/(html|php3|phtml|inc|asp)/i", $file_extention)) {
		$msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

 	if( $MAXFSIZE * 1024 < $MlangMultyFile_size) {
		$MlangMultyFile_kfsize = intval($MlangMultyFile_size/1024);
		$msg = "업로드하신 파일의 크기가 $MlangMultyFile_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

	if (is_file("$upload_dir/$MlangMultyFile_name")) {
		$MlangMultyFile_name = date("is")."_$MlangMultyFile_name";
	}
	if ($MlangMultyFile_size) {
	    move_uploaded_file($MlangMultyFile, "$upload_dir/$MlangMultyFile_name");
	}
}

//  $MlangMultyFile_name
//  $MlangMultyFile_size
?>