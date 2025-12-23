<?
//소스제작자 http://www.websil.co.kr , http://www.script.ne.kr - Mlang
if(!$BBS_ADMIN_MAXFSIZE){ $BBS_ADMIN_MAXFSIZE="2000000"; }   
$MlangFF_end=181; $MlangFF_num=rand(0,$MlangFF_end);

$upfile_path = "$upload_dir"; // 화일업로드 디렉토리 권한은 707 이여야 합니다.
$tmp_file =  $_FILES['photofile']['tmp_name']; // 업로드된 화일의 임시이름
$filename  = $_FILES['photofile']['name']; // 업로드 하려한 화일명(원래 이름)

   $MlangFile_size = filesize($tmp_file);  // 파일 사이즈
   if($BBS_ADMIN_MAXFSIZE < $MlangFile_size) {
		$msg = "\\nERROR: 업로드하신 파일의 크기가 $MlangFile_size KB입니다\\n\\n관리자가 제한한 용량은 $BBS_ADMIN_MAXFSIZE KB입니다.";
		echo ("<script language=javascript>
                   window.alert('$msg');
				   history.go(-1);
                </script>");
				exit;
	}

//인코딩은 일본 서버일경우 읽지를 못함으로 숫자로 변환 시킨다.
// 또한 파일명 처리시 . 는 파일명중에 . 가 있을경우 재대로 읽지를 못하는 에러가 있다.
$strExt_ok=strrchr($filename,".");
$strExt_ok_two = explode(".",$strExt_ok); #파일명 분리
$MlangFile_File = $strExt_ok_two[1]; #파일확장자

  if (eregi($MlangFile_File, "html|php3|phtml|inc|asp")) {
	$msg = "\\nERROR: php / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
		echo ("<script language=javascript>
                    window.alert('$msg');
			         history.go(-1);
                 </script>");
		    	  exit;
	}

$filepath = date("YmdHis")."$filepath";
$filepath = $MlangFF_num."$filepath";
$filepath = $filepath.".$MlangFile_File";
$dest_file = $upfile_path ."/". $filepath; // 화일 위치와 화일명을 지정

 if(is_uploaded_file($tmp_file)) { // 업로드 화일이 존재하는지 체크
       $error_code = move_uploaded_file($tmp_file, $dest_file); // 임시화일을 실제 업로드 디렉토리로 옮긴다.
       chmod($dest_file, 0606); // 권한 606 을 준다.
    }

 $photofileNAME = $filepath;
$photofileSIZE = $MlangFile_size;
?>