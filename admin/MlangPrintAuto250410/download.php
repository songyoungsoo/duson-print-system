<?php
ob_start();

// 파일이 있는 디렉토리
$downfiledir = "../../shop/data/"; 

// 다운로드할 파일명 검증 및 보안 처리
$downfile = basename($_GET['downfile'] ?? ''); // 경로 조작 방지
if (empty($downfile) || !preg_match('/^[a-zA-Z0-9_\-]+\.[a-zA-Z0-9]+$/', $downfile)) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

// 외부 접근 방지
if (!preg_match("/" . preg_quote($_SERVER['HTTP_HOST'], '/') . "/", $_SERVER['HTTP_REFERER'] ?? '')) {
    echo "<script>alert('외부에서는 다운로드를 받을 수 없습니다.'); history.back();</script>";
    exit;
}

// 파일 존재 여부 확인
$filepath = $downfiledir . $downfile;
if (file_exists($filepath)) {
    // 파일 다운로드 헤더 설정
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . urlencode($downfile) . "\"");
    header("Content-Transfer-Encoding: binary"); 
    header("Content-Length: " . filesize($filepath)); 
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: public");
    header("Expires: 0");

    // 출력 버퍼 비우기 후 파일 전송
    ob_end_clean();
    flush();
    readfile($filepath);
} else {
    echo "<script>alert('존재하지 않는 파일입니다.'); history.back();</script>";
}
?>
