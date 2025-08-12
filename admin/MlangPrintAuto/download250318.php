<?php
// ✅ 출력 버퍼 방지
if (ob_get_level()) {
    ob_end_clean();
}

// ✅ GET 요청 데이터 확인
$no = $_GET['no'] ?? '';
$downfile = $_GET['downfile'] ?? '';

// ✅ 파일명 검증 (보안 강화)
if (empty($downfile)) {
    die("<script>alert('파일명이 전달되지 않았습니다. 파일명을 확인하세요.'); history.go(-1);</script>");
}

$downfile = basename($downfile); // ✅ 보안 강화 (디렉토리 트래버설 방지)

// ✅ 다운로드 가능한 폴더 목록
$directories = [
    "../../MlangOrder_PrintAuto/upload/" . $no . "/",  // ✅ 주문번호 폴더
    "../../MlangOrder_PrintAuto/upload/",              // ✅ 기본 업로드 폴더
    "../../shop/data/"                                 // ✅ 보조 폴더
];

$filepath = null;

foreach ($directories as $dir) {
    $test_path = realpath($dir . DIRECTORY_SEPARATOR . $downfile);

    // ✅ realpath()가 false인 경우 무시하고 다음 디렉토리 확인
    if ($test_path === false) {
        continue;
    }

    // ✅ 파일 존재 여부 확인 및 허용된 디렉토리 내 파일인지 체크
    if (file_exists($test_path) && strpos($test_path, realpath($dir)) === 0) {
        $filepath = $test_path;
        break;
    }
}

// ✅ 파일이 존재하지 않을 경우 오류 메시지
if (!$filepath || !file_exists($filepath)) {
    die("<script>alert('파일을 찾을 수 없습니다. 올바른 파일인지 확인하세요.'); history.go(-1);</script>");
}

// ✅ 파일 크기 및 MIME 타입 가져오기
$filesize = filesize($filepath);
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $filepath);
finfo_close($finfo);

// ✅ 파일명 인코딩 (한글 깨짐 방지)
$encoded_filename = rawurlencode($downfile);

// ✅ 다운로드 헤더 설정
header("Content-Type: $mime_type");
header("Content-Disposition: attachment; filename*=UTF-8''" . $encoded_filename);
header("Content-Transfer-Encoding: binary");
header("Content-Length: $filesize");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");
header("Expires: 0");

// ✅ 파일 읽기 및 전송
readfile($filepath);
exit;
?>
