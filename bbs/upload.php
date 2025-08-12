<?php
// 변수 초기화 (Notice 에러 방지)
$upfile = isset($_FILES['upfile']['tmp_name']) ? $_FILES['upfile']['tmp_name'] : '';
$upfile_name = isset($_FILES['upfile']['name']) ? $_FILES['upfile']['name'] : '';
$upfile_size = isset($_FILES['upfile']['size']) ? $_FILES['upfile']['size'] : 0;
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');

// BBS 관련 변수들 초기화
$BBS_ADMIN_MAXFSIZE = isset($BBS_ADMIN_MAXFSIZE) ? $BBS_ADMIN_MAXFSIZE : "2000";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

if(!$BBS_ADMIN_MAXFSIZE){$BBS_ADMIN_MAXFSIZE="2000";}

if(is_uploaded_file($upfile)) {

	$full_filename = preg_split("\.", $upfile_name);
	$file_extention = $full_filename[sizeof($upfile_name)];

	if (preg_match($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		echo("<SCRIPT LNAGUAGE=JAVASCRIPT>
                      window.alert('msg');
                       history.go(-1);
                   </SCRIPT>");
		exit;
	}

 	if( $BBS_ADMIN_MAXFSIZE * 1024 < $upfile_size) {
		$upfile_kfsize = intval($upfile_size/1024);
		$msg = "업로드하신 파일의 크기가 $BBS_ADMIN_MAXFSIZE KB입니다. 관리자가 제한한 용량은 $BBS_ADMIN_MAXFSIZE KB입니다.";
		echo("<SCRIPT LNAGUAGE=JAVASCRIPT>
                      window.alert('$msg');
                       history.go(-1);
                   </SCRIPT>");
		exit;
	}


//---------------------------------------------------------------------------------------------------------------//
$MlangHanGulChange = "$upfile_name";
if(preg_match("(([^/a-zA-Z]){1,})(\.jpg|\.jpeg|\.bmp|\.png|\.gif)", $MlangHanGulChange ,$MlangHanGulregs)){
$MlangHanGulCode_date=date("YmdHis");  $MlangHanGulCode_end=1014; $MlangHanGulCode_num=rand(0,$MlangHanGulCode_end);  
$MlangHanGulCodeOK="{$MlangHanGulCode_date}{$MlangHanGulCode_num}";
$upfile_name = preg_replace($MlangHanGulregs[1], "$MlangHanGulCodeOK",$MlangHanGulChange);
}else{
$upfile_name = $upfile_name;
}
//---------------------------------------------------------------------------------------------------------------//


	if (is_file("$BbsDir/upload/$table/$upfile_name")) {
		$upfile_name = date("is")."_$upfile_name";
	}
	if ($upfile_size) {
	move_uploaded_file($upfile, "$BbsDir/upload/$table/$upfile_name");
	}
}

$UPFILENAME = $upfile_name;
$FILESIZE = $upfile_size;
?>