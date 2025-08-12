<?php
// 초기 변수 설정
$BBS_ADMIN_MAXFSIZE = $BBS_ADMIN_MAXFSIZE ?? 20000; // 기본 최대 파일 크기 2MB
$MlangFF_end = 181;
$MlangFF_num = rand(0, $MlangFF_end);

$upfile_path = $upload_dir; // 업로드 디렉토리
$tmp_file = $_FILES['photofile']['tmp_name'] ?? ''; // 업로드된 파일의 임시 경로
$filename = $_FILES['photofile']['name'] ?? ''; // 원본 파일 이름
$MlangFile_size = $_FILES['photofile']['size'] ?? 0; // 파일 크기

// 파일 크기 제한 검사
if ($MlangFile_size > $BBS_ADMIN_MAXFSIZE) {
    $msg = "\\nERROR: 업로드한 파일 크기가 $MlangFile_size KB입니다.\\n관리자가 제한한 용량은 $BBS_ADMIN_MAXFSIZE KB입니다.";
    echo "<script>
              alert('$msg');
              history.go(-1);
          </script>";
    exit;
}

// 파일 확장자 검사 (업로드 가능한 확장자만 허용)
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
$file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    $msg = "\\nERROR: 허용되지 않은 파일 형식입니다. 업로드 가능한 확장자는 " . implode(", ", $allowed_extensions) . "입니다.";
    echo "<script>
              alert('$msg');
              history.go(-1);
          </script>";
    exit;
}

// 파일 이름 생성 (현재 시간과 랜덤 번호를 결합)
$filepath = $MlangFF_num . date("YmdHis") . ".$file_extension";
$dest_file = $upfile_path . "/" . $filepath;

// 파일 업로드 처리
if (is_uploaded_file($tmp_file)) {
    if (move_uploaded_file($tmp_file, $dest_file)) {
        chmod($dest_file, 0777); // 파일 권한 설정
    } else {
        echo "<script>
                  alert('파일 업로드에 실패했습니다.');
                  history.go(-1);
              </script>";
        exit;
    }
}

$photofileNAME = $filepath;
$photofileSIZE = $MlangFile_size;
?>
