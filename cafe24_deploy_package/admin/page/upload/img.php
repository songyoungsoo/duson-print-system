<?php
$MAXFSIZE = 200; // 최대 파일 크기 (KB)

// 파일이 업로드되었는지 확인
if (isset($_FILES['upfile']) && is_uploaded_file($_FILES['upfile']['tmp_name'])) {

    // 업로드된 파일의 정보
    $upfile_name = $_FILES['upfile']['name'];
    $upfile_size = $_FILES['upfile']['size'];
    $upfile_tmp = $_FILES['upfile']['tmp_name'];

    // 파일 확장자 추출
    $file_info = pathinfo($upfile_name);
    $file_extension = strtolower($file_info['extension']);

    // 허용하지 않는 파일 확장자 검사
    $blocked_extensions = ['html', 'php', 'php3', 'phtml', 'inc', 'asp'];
    if (in_array($file_extension, $blocked_extensions)) {
        $msg = "\\nphp / asp 확장자의 파일은 업로드할 수 없습니다.\\n\\n다른 파일을 선택해주세요.\\n";
        include("./ERROR.php3");
        exit;
    }

    // 파일 크기 검사
    if ($upfile_size > $MAXFSIZE * 1024) {
        $upfile_kfsize = intval($upfile_size / 1024);
        $msg = "업로드된 파일의 크기가 $upfile_kfsize KB입니다. 허용된 최대 크기는 $MAXFSIZE KB입니다.";
        include("./ERROR.php3");
        exit;
    }

    // 동일한 파일명이 있을 경우 이름 변경
    $upload_dir = "../table/$table/upload/";
    $new_filename = $upfile_name;
    if (file_exists($upload_dir . $new_filename)) {
        $new_filename = time() . "_" . $upfile_name; // 현재 시간으로 파일 이름 변경
    }

    // 파일 업로드 처리
    if (move_uploaded_file($upfile_tmp, $upload_dir . $new_filename)) {
        $UPFILENAME = $new_filename;
        $FILESIZE = $upfile_size;
        // 파일이 성공적으로 업로드되었습니다.
    } else {
        $msg = "파일 업로드에 실패했습니다.";
        include("./ERROR.php3");
        exit;
    }
}
?>
