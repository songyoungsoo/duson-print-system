<?php
// 업로드 디렉토리 (절대경로 권장)
$uploadDir = __DIR__ . '/../../results/upload';  
// 최대 업로드 용량 (KB)
$MAXFSIZE = 2000;  

if (isset($_FILES['Bigupfile']) && is_uploaded_file($_FILES['Bigupfile']['tmp_name'])) {
    $tmpName      = $_FILES['Bigupfile']['tmp_name'];
    $originalName = $_FILES['Bigupfile']['name'];
    $fileSize     = $_FILES['Bigupfile']['size'];

    // 1) 확장자 검사
    $ext       = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $blacklist = ['html', 'php3', 'phtml', 'inc', 'asp'];
    if (in_array($ext, $blacklist, true)) {
        $msg = 'php/asp 관련 파일은 업로드할 수 없습니다. 파일의 확장자를 변경하여 올려주세요.';
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    // 2) 용량 체크 (KB → byte)
    if ($fileSize > $MAXFSIZE * 1024) {
        $kb  = (int)($fileSize / 1024);
        $msg = "업로드하신 파일 크기: {$kb}KB, 제한 용량: {$MAXFSIZE}KB.";
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    // 3) 중복 파일명 처리
    $destName = $originalName;
    $destPath = $uploadDir . DIRECTORY_SEPARATOR . $destName;
    if (file_exists($destPath)) {
        $destName = date('His') . "_{$originalName}";
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $destName;
    }

    // 4) 실제 업로드
    if (!move_uploaded_file($tmpName, $destPath)) {
        echo "<script>alert('파일 업로드에 실패했습니다.'); history.back();</script>";
        exit;
    }

    // 업로드 후 사용할 변수
    $BigUPFILENAME = $destName;
    $BigFILESIZE   = $fileSize;
} else {
    // 업로드된 파일이 없을 때의 처리 (필요시)
    $BigUPFILENAME = '';
    $BigFILESIZE   = 0;
}
?>
