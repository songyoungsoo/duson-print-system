<?php
if(is_uploaded_file($File1)) {

    $full_filename = explode(".", $File1_name);
    $file_extension = end($full_filename);

    if (preg_match("/html|php3|phtml|inc|asp/i", $file_extension)) {
        $msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
        echo ("<script language=javascript>
                 window.alert('$msg');
                    history.go(-1);
              </script>");
            exit;
    }

    if( $MAXFSIZE * 1024 < $File1_size) {
        $File1_kfsize = intval($File1_size/1024);
        $msg = "업로드하신 파일의 크기가 $File1_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
        echo ("<script language=javascript>
                 window.alert('$msg');
                    history.go(-1);
              </script>");
            exit;
    }

    if (file_exists("$upload_dir/$File1_name")) {
        $File1_name = date("is")."_$File1_name";
    }
    if ($File1_size) {
        move_uploaded_file($File1, "$upload_dir/$File1_name");
    }
}

$File1NAME = $File1_name;
?>
