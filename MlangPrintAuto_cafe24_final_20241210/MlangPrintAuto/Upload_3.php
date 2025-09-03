<?php
if(is_uploaded_file($File3)) {

    $full_filename = explode(".", $File3_name);
    $file_extension = end($full_filename);

    if (preg_match("/html|php3|phtml|inc|asp/i", $file_extension)) {
        $msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
        echo ("<script language=javascript>
                 window.alert('$msg');
                    history.go(-1);
              </script>");
            exit;
    }

    if( $MAXFSIZE * 1024 < $File3_size) {
        $File3_kfsize = intval($File3_size/1024);
        $msg = "업로드하신 파일의 크기가 $File3_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
        echo ("<script language=javascript>
                 window.alert('$msg');
                    history.go(-1);
              </script>");
            exit;
    }

    if (file_exists("$upload_dir/$File3_name")) {
        $File3_name = date("is")."_$File3_name";
    }
    if ($File3_size) {
        move_uploaded_file($File3, "$upload_dir/$File3_name");
    }
}

$File3NAME = $File3_name;
?>
