<?php
if(is_uploaded_file($File4)) {

    $full_filename = explode(".", $File4_name);
    $file_extension = end($full_filename);

    if (preg_match("/html|php3|phtml|inc|asp/i", $file_extension)) {
        $msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
        echo ("<script language=javascript>
                 window.alert('$msg');
                    history.go(-1);
              </script>");
            exit;
    }

    if( $MAXFSIZE * 1024 < $File4_size) {
        $File4_kfsize = intval($File4_size/1024);
        $msg = "업로드하신 파일의 크기가 $File4_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
        echo ("<script language=javascript>
                 window.alert('$msg');
                    history.go(-1);
              </script>");
            exit;
    }

    if (file_exists("$upload_dir/$File4_name")) {
        $File4_name = date("is")."_$File4_name";
    }
    if ($File4_size) {
        move_uploaded_file($File4, "$upload_dir/$File4_name");
    }
}

$File4NAME = $File4_name;
?>
