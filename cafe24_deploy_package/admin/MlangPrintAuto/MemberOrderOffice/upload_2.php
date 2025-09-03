<?php
if (isset($_FILES['photofile_2']) && is_uploaded_file($_FILES['photofile_2']['tmp_name'])) {

    $photofile_2_tmp = $_FILES['photofile_2']['tmp_name'];
    $photofile_2_name = basename($_FILES['photofile_2']['name']);
    $photofile_2_size = $_FILES['photofile_2']['size'];

    // 확장자 검사
    $file_extension = strtolower(pathinfo($photofile_2_name, PATHINFO_EXTENSION));
    $blocked_extensions = ['html', 'php3', 'phtml', 'inc', 'asp'];

    if (in_array($file_extension, $blocked_extensions)) {
        echo "<script>alert('❌ 보안상 문제가 있는 확장자입니다. 다른 파일로 다시 업로드해주세요.');</script>";
        exit;
    }

    // 용량 제한 검사 (KB 단위)
    if (($MAXFSIZE ?? 1024) * 1024 < $photofile_2_size) {
        $photofile_2_kfsize = intval($photofile_2_size / 1024);
        echo "<script>alert('❌ 파일이 너무 큽니다. 현재 크기: {$photofile_2_kfsize}KB, 최대: {$MAXFSIZE}KB');</script>";
        exit;
    }

    // 파일명 한글 처리 + 충돌 방지
    $file_base = pathinfo($photofile_2_name, PATHINFO_FILENAME);
    $safe_name = date("YmdHis") . rand(100, 999) . '.' . $file_extension;

    // 동일 이름 존재시 변경
    if (file_exists("$upload_dir/$safe_name")) {
        $safe_name = date("is") . "_$safe_name";
    }

    // 파일 저장
    if (!move_uploaded_file($photofile_2_tmp, "$upload_dir/$safe_name")) {
        echo "<script>alert('❌ 파일 업로드 실패');</script>";
        exit;
    }

    $photofile_2Name = $safe_name;
}
?>
