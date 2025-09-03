<?php
declare(strict_types=1);

// 업로드 디렉토리 (절대경로 권장)
$uploadDir   = __DIR__ . '/';  
// 최대 업로드 용량 (KB)
$MAXFSIZEKB  = 2000;

// 업로드된 파일 정보 확인
if (isset($_FILES['BigupfileTwo']) && is_uploaded_file($_FILES['BigupfileTwo']['tmp_name'])) {
    $tmpName    = $_FILES['BigupfileTwo']['tmp_name'];
    $origName   = $_FILES['BigupfileTwo']['name'];
    $fileSize   = $_FILES['BigupfileTwo']['size'];

    // 1) 확장자 검사
    $ext         = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $forbidden   = ['html','php3','phtml','inc','asp'];
    if (in_array($ext, $forbidden, true)) {
        $msg = 'php/asp 관련 파일은 업로드할 수 없습니다. 확장자를 변경해 주세요.';
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    // 2) 용량 체크 (KB → byte)
    if ($fileSize > $MAXFSIZEKB * 1024) {
        $kb  = (int)($fileSize / 1024);
        $msg = "업로드하신 파일 크기: {$kb}KB, 제한 용량: {$MAXFSIZEKB}KB.";
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    // 3) 중복 파일명 처리
    $destName = $origName;
    $destPath = $uploadDir . DIRECTORY_SEPARATOR . $destName;
    if (file_exists($destPath)) {
        $destName = date('His') . "_{$origName}";
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $destName;
    }

    // 4) 파일 이동
    if (! move_uploaded_file($tmpName, $destPath)) {
        echo "<script>alert('파일 업로드에 실패했습니다.'); history.back();</script>";
        exit;
    }

    // 결과 변수 설정
    $BigUPFILENAMETwo = $destName;
    $BigFILESIZE      = $fileSize;
} else {
    // 업로드된 파일이 없을 경우
    $BigUPFILENAMETwo = '';
    $BigFILESIZE      = 0;
}
?>
