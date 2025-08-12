<?php
ob_start();

// 다운로드 파일 디렉토리
$downfiledir = "../../shop/data/";

// 파일명 가져오기 및 필터링
$downfile = $_GET['downfile'] ?? '';

if (!$downfile || preg_match('/[\\/:*?"<>|]/', $downfile)) {
    echo "<script>alert('잘못된 파일 요청입니다.'); history.back();</script>";
    exit;
}

// 외부에서 접근 방지 (HTTP_REFERER 체크)
if (!isset($_SERVER['HTTP_REFERER']) || !str_contains($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) {
    echo "<script>alert('외부에서는 다운로드 받으실 수 없습니다.'); history.back();</script>";
    exit;
}

// 파일 전체 경로
$filepath = $downfiledir . basename($downfile);

// 파일 존재 확인
if (file_exists($filepath)) {
    // 파일명 안전 처리
    $safe_filename = urlencode(basename($downfile));

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$safe_filename\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . filesize($filepath));
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: public");
    header("Expires: 0");

    // 파일 출력
    readfile($filepath);
    flush();
    exit;
} else {
    echo "<script>alert('존재하지 않는 파일입니다.'); history.back();</script>";
    exit;
}
?>

