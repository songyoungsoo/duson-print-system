<?php
// 업로드 경로
$upload_dir = './upload';
$MAXFSIZE = 2000; // 이미지의 용량 (KB)

if (isset($_FILES['photofile']) && is_uploaded_file($_FILES['photofile']['tmp_name'])) {
    $photofile = $_FILES['photofile']['tmp_name'];
    $photofile_name = basename($_FILES['photofile']['name']);
    $photofile_size = $_FILES['photofile']['size'];

    $full_filename = pathinfo($photofile_name);
    $file_extension = strtolower($full_filename['extension']);

    if (preg_match('/html|php|php3|phtml|inc|asp/', $file_extension)) {
        $msg = "\\nphp / asp 관련파일은 직접 업로드 하실 수 없습니다.\\n\\n파일의 확장자를 변경하여 올려 주세요.\\n";
        echo ("<script language='javascript'>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
        exit;
    }

    if ($MAXFSIZE * 1024 < $photofile_size) {
        $photofile_kfsize = intval($photofile_size / 1024);
        $msg = "업로드하신 파일의 크기가 $photofile_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
        echo ("<script language='javascript'>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
        exit;
    }

    if (is_file("$upload_dir/$photofile_name")) {
        $photofile_name = date("YmdHis") . "_" . $photofile_name;
    }

    if ($photofile_size) {
        if (!move_uploaded_file($photofile, "$upload_dir/$photofile_name")) {
            echo ("<script language='javascript'>
                         window.alert('파일 업로드에 실패했습니다.');
                            history.go(-1);
                      </script>");
            exit;
        }
    }

    $photofileNAME = $photofile_name;
    $photofileSIZE = $photofile_size;
} else {
    $photofileNAME = '';
    $photofileSIZE = 0;
}
?>
