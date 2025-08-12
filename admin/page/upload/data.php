<?php
$MAXFSIZE = 2000; // 최대 파일 크기 (KB)

// 파일이 업로드되었는지 확인
if (isset($_FILES['FILELINK']) && is_uploaded_file($_FILES['FILELINK']['tmp_name'])) {

    // 업로드된 파일의 정보
    $FILELINK_name = $_FILES['FILELINK']['name']; // 파일명
    $FILELINK_size = $_FILES['FILELINK']['size']; // 파일 크기
    $FILELINK_tmp = $_FILES['FILELINK']['tmp_name']; // 임시 파일 경로
    $upload_dir = "$tty/upload/data/"; // 파일 저장 경로

    // 파일 확장자 추출
    $file_info = pathinfo($FILELINK_name);
    $file_extension = strtolower($file_info['extension']); // 확장자를 소문자로 변환

    // 허용하지 않는 파일 확장자 검사 (보안 강화)
    $blocked_extensions = ['php', 'html', 'php3', 'phtml', 'inc', 'asp'];
    if (in_array($file_extension, $blocked_extensions)) {
        $msg = "업로드할 수 없는 파일 형식입니다.";
        echo "<script>
                alert('$msg');
                history.go(-1);
              </script>";
        exit;
    }

    // 파일 크기 검사
    if ($MAXFSIZE * 1024 < $FILELINK_size) {
        $FILELINK_kfsize = intval($FILELINK_size / 1024);
        $msg = "업로드된 파일의 크기가 $FILELINK_kfsize KB입니다. 허용된 최대 크기는 $MAXFSIZE KB입니다.";

        echo "<script>
                alert('$msg');
                history.go(-1);
              </script>";
        exit;
    }

    // 동일한 파일명이 있을 경우 파일 이름을 시간 기반으로 변경
    if (file_exists($upload_dir . $FILELINK_name)) {
        $FILELINK_name = time() . "_" . $FILELINK_name; // 중복 방지를 위해 파일 이름에 시간을 추가
    }

    // 파일 업로드 처리
    if (move_uploaded_file($FILELINK_tmp, $upload_dir . $FILELINK_name)) {
        $FILELINK_ok = $FILELINK_name;
        $FILESIZE_ok = $FILELINK_size;
        // 업로드 성공 처리
    } else {
        // 업로드 실패 처리
        $msg = "파일 업로드에 실패했습니다.";
        echo "<script>
                alert('$msg');
                history.go(-1);
              </script>";
        exit;
    }
}
?>
