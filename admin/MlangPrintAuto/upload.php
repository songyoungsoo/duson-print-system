<?php
// 제작자: http://www.websil.co.kr, http://www.script.ne.kr - Mlang

if (!isset($BBS_ADMIN_MAXFSIZE)) {
    $BBS_ADMIN_MAXFSIZE = 2000000; // 2MB 제한
}

$MlangFF_end = 181;
$MlangFF_num = rand(0, $MlangFF_end);

$upfile_path = $upload_dir; // 업로드 디렉토리 (권한 707 필요)

$tmp_file = $_FILES['photofile']['tmp_name']; // 임시 파일 경로
$filename = $_FILES['photofile']['name'];     // 원본 파일명

// 파일 사이즈 체크
$MlangFile_size = filesize($tmp_file);
if ($MlangFile_size > $BBS_ADMIN_MAXFSIZE) {
    $msg = "\\nERROR: 업로드하신 파일의 크기가 {$MlangFile_size} Bytes입니다.\\n\\n관리자가 제한한 용량은 {$BBS_ADMIN_MAXFSIZE} Bytes입니다.";
    echo "<script>alert('$msg'); history.go(-1);</script>";
    exit;
}

// 확장자 추출 및 보안 검사
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$blocked_extensions = ['php', 'php3', 'phtml', 'inc', 'asp', 'html'];

if (in_array($extension, $blocked_extensions)) {
    $msg = "\\nERROR: 보안상 php / asp / html 관련 확장자는 업로드할 수 없습니다.\\n\\n확장자를 변경하여 업로드해주세요.";
    echo "<script>alert('$msg'); history.go(-1);</script>";
    exit;
}

// 저장될 파일명 생성 (타임스탬프 + 랜덤 + 확장자)
$filepath = $MlangFF_num . date("YmdHis") . "." . $extension;
// 또는 고유 ID 사용 가능: $filepath = uniqid() . "." . $extension;

$dest_file = rtrim($upfile_path, '/') . '/' . $filepath;

// 파일 이동 처리
if (is_uploaded_file($tmp_file)) {
    if (move_uploaded_file($tmp_file, $dest_file)) {
        chmod($dest_file, 0644); // 일반 권장 권한
    } else {
        echo "<script>alert('파일 업로드 실패.'); history.go(-1);</script>";
        exit;
    }
}

$photofileNAME = $filepath;
$photofileSIZE = $MlangFile_size;
?>
