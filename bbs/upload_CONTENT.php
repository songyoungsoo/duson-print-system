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

if(!$BBS_ADMIN_MAXFSIZE){$BBS_ADMIN_MAXFSIZE="2000";}

if(is_uploaded_file($CONTENT)) {

	$CONTENT_full_filename = preg_split("\.", $CONTENT_name);
	$CONTENT_file_extention = $CONTENT_full_filename[sizeof($CONTENT_name)];

	if (preg_match($CONTENT_file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		echo("<SCRIPT LNAGUAGE=JAVASCRIPT>
                      window.alert('msg');
                       history.go(-1);
                   </SCRIPT>");
		exit;
	}

 	if( $BBS_ADMIN_MAXFSIZE * 1024 < $CONTENT_size) {
		$CONTENT_kfsize = intval($CONTENT_size/1024);
		$msg = "업로드하신 파일의 크기가 $BBS_ADMIN_MAXFSIZE KB입니다. 관리자가 제한한 용량은 $BBS_ADMIN_MAXFSIZE KB입니다.";
		echo("<SCRIPT LNAGUAGE=JAVASCRIPT>
                      window.alert('$msg');
                       history.go(-1);
                   </SCRIPT>");
		exit;
	}


//---------------------------------------------------------------------------------------------------------------//
$MlangHanGulChange = "$CONTENT_name";
if(preg_match("(([^/a-zA-Z]){1,})(\.jpg|\.jpeg|\.bmp|\.png|\.gif)", $MlangHanGulChange ,$MlangHanGulregs)){
$MlangHanGulCode_date=date("YmdHis");  $MlangHanGulCode_end=1014; $MlangHanGulCode_num=rand(0,$MlangHanGulCode_end);  
$MlangHanGulCodeOK="{$MlangHanGulCode_date}{$MlangHanGulCode_num}";
$CONTENT_name = preg_replace($MlangHanGulregs[1], "$MlangHanGulCodeOK",$MlangHanGulChange);
}else{
$CONTENT_name = $CONTENT_name;
}
//---------------------------------------------------------------------------------------------------------------//


	if (is_file("$BbsDir/upload/$table/$CONTENT_name")) {
		$CONTENT_name = date("is")."_$CONTENT_name";
	}
	if ($CONTENT_size) {
	move_uploaded_file($CONTENT, "$BbsDir/upload/$table/$CONTENT_name");
	}
}

$CONTENTNAME = $CONTENT_name;
$FILE_CONTENTSIZE = $CONTENT_size;
?>