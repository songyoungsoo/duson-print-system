<?php
/**
 * 파일 다운로드 시스템 (개선 버전)
 * 다중 디렉토리 지원 및 보안 강화
 */

ob_start();

// 기본 디렉토리 설정
$base_dir = "../../";

// 파라미터 받기
$downfile = $_GET['downfile'] ?? '';
$path = $_GET['path'] ?? 'shop/data';
$no = $_GET['no'] ?? '';

// 1. 파일명 검증
if (empty($downfile)) {
    die("<script>alert('파일명이 지정되지 않았습니다.'); history.back();</script>");
}

// 2. 보안: 경로 조작 방지 (Path Traversal Attack)
$downfile = basename($downfile); // 파일명만 추출
$path = str_replace(['../', '..\\', './'], '', $path); // 상위 디렉토리 접근 차단

// 3. Referer 체크 (기본 보안)
if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_HOST'])) {
    if (!preg_match('/' . preg_quote($_SERVER['HTTP_HOST'], '/') . '/i', $_SERVER['HTTP_REFERER'])) {
        die("<script>alert('외부에서는 다운로드 받으실 수 없습니다.'); history.back();</script>");
    }
}

// 4. 허용된 경로 목록 (화이트리스트 방식)
$allowed_paths = [
    'shop/data',
    'mlangorder_printauto/upload',
    'uploads/sticker_new',
    'uploads/orders',
    'uploads/namecard',
    'uploads/envelope',
    'uploads/cadarok',
    'uploads/leaflet',
    'uploads/littleprint',
    'uploads/merchandisebond',
    'uploads/msticker',
    'uploads',  // 날짜별 업로드 폴더 (uploads/2025/10/09/IP/) 허용
    'ImgFolder'  // 레거시 경로 형식 지원 (_MlangPrintAuto_*_index.php/...)
];

// 경로 검증
$path_allowed = false;
foreach ($allowed_paths as $allowed) {
    if (strpos($path, $allowed) === 0) {
        $path_allowed = true;
        break;
    }
}

if (!$path_allowed) {
    die("<script>alert('허용되지 않은 경로입니다.'); history.back();</script>");
}

// 5. 파일 경로 생성
$downfiledir = $base_dir . $path . '/';
$full_path = $downfiledir . $downfile;

// 6. 파일 존재 확인
if (!file_exists($full_path)) {
    // 대체 경로 시도 (주문번호 기반)
    if (!empty($no)) {
        $alternative_paths = [
            $base_dir . "mlangorder_printauto/upload/$no/",
            $base_dir . "uploads/orders/$no/"
        ];

        foreach ($alternative_paths as $alt_dir) {
            if (file_exists($alt_dir . $downfile)) {
                $full_path = $alt_dir . $downfile;
                break;
            }
        }
    }

    if (!file_exists($full_path)) {
        die("<script>alert('존재하지 않는 파일입니다.'); history.back();</script>");
    }
}

// 7. 파일 타입 검증 (추가 보안)
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'ai', 'psd', 'zip', 'doc', 'docx', 'xls', 'xlsx'];
$file_ext = strtolower(pathinfo($downfile, PATHINFO_EXTENSION));
if (!in_array($file_ext, $allowed_extensions)) {
    die("<script>alert('허용되지 않은 파일 형식입니다.'); history.back();</script>");
}

// 8. 파일 다운로드 처리
$file_size = filesize($full_path);

// UTF-8 파일명 처리 (한글 파일명 지원)
$encoded_filename = urlencode($downfile);
if (preg_match('/MSIE|Trident/i', $_SERVER['HTTP_USER_AGENT'])) {
    // IE 브라우저
    $encoded_filename = str_replace('+', '%20', $encoded_filename);
} else {
    // 기타 브라우저
    $encoded_filename = $downfile;
}

// HTTP 헤더 설정
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$encoded_filename\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . $file_size);
header("Cache-Control: cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 파일 전송
$fp = fopen($full_path, "rb");
if ($fp) {
    while (!feof($fp)) {
        echo fread($fp, 100 * 1024); // 100KB씩 전송
    }
    fclose($fp);
    flush();
} else {
    die("<script>alert('파일을 열 수 없습니다.'); history.back();</script>");
}

ob_end_flush();
?>
