<?php
// $upload_dir 업로드경로
$MAXFSIZE = 2000; // 이미지의 용량

if (isset($photofile) && is_uploaded_file($photofile['tmp_name'])) {

    $full_filename = pathinfo($photofile['name']);
    $file_extension = strtolower($full_filename['extension']);

    $disallowed_extensions = array("html", "php3", "phtml", "inc", "asp");
    if (in_array($file_extension, $disallowed_extensions)) {
        $msg = "php / asp 관련 파일은 직접 업로드 할 수 없습니다. 파일의 확장자를 변경하여 올려주세요.";
        echo "<script>alert('$msg'); history.go(-1);</script>";
        exit;
    }

    $photofile_size = $photofile['size'];
    if ($photofile_size > $MAXFSIZE * 1024) {
        $photofile_kfsize = intval($photofile_size / 1024);
        $msg = "업로드하신 파일의 크기가 $photofile_kfsize KB입니다. 관리자가 제한한 용량은 $MAXFSIZE KB입니다.";
        echo "<script>alert('$msg'); history.go(-1);</script>";
        exit;
    }

    if (file_exists("$upload_dir/{$photofile['name']}")) {
        $photofile_name = date("is") . "_{$photofile['name']}";
    }

    if ($photofile_size > 0) {
        move_uploaded_file($photofile['tmp_name'], "$upload_dir/$photofile_name");
    }
}

$photofileNAME = $photofile_name;
?>
