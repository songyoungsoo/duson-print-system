<?php
$MAXFSIZE = 200; // Maximum file size in KB

if (is_uploaded_file($photofile)) {
    $file_extension = strtolower(pathinfo($photofile_name, PATHINFO_EXTENSION));

    // Check for disallowed file extensions
    if (preg_match("/(html|php3|phtml|inc|asp)/i", $file_extension)) {
        $msg = "업로드된 파일이 허용되지 않습니다.\n\n허용된 파일 유형은: jpg, png, gif 등입니다.";
        echo "<script language='javascript'>alert('$msg');</script>";
    }

    // Check file size
    if ($_FILES['photofile']['size'] > $MAXFSIZE * 1024) {
        $photofile_kfsize = intval($_FILES['photofile']['size'] / 1024);
        $msg = "파일 크기가 제한을 초과했습니다.\n\n업로드된 파일 크기: $photofile_kfsize KB\n\n최대 허용 크기: $MAXFSIZE KB";
        echo "<script language='javascript'>alert('$msg');</script>";
    }

    // Handle existing file names
    if (is_file("$upload_dir/$photofile_name")) {
        $photofile_name = date("is") . "_$photofile_name";
    }

    // Move uploaded file to destination directory
    if ($_FILES['photofile']['size']) {
        move_uploaded_file($photofile, "$upload_dir/$photofile_name");
    }
}

$PhotofileName = $photofile_name;
?>