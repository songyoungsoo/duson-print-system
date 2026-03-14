<?php
/**
 * 교정 이미지 프록시 - nginx 이미지 가로채기 우회
 * PHP가 직접 이미지를 읽어서 Content-Type과 함께 출력
 * + NAS 아카이브 프록시 (2026-03-13 추가)
 */
$no = isset($_GET['no']) ? intval($_GET['no']) : 0;
$file = isset($_GET['file']) ? basename($_GET['file']) : '';
$src = isset($_GET['src']) ? $_GET['src'] : '';
// ImgFolder는 다단계 경로 (예: _MlangPrintAuto_.../2024/1119/abc)
// basename() 대신 디렉토리 트래버설만 제거
$folder = isset($_GET['folder']) ? str_replace(['..', "\0"], '', $_GET['folder']) : '';

if ($no <= 0 || empty($file)) {
    http_response_code(400);
    exit;
}

// ─── NAS 프록시 소스: NAS에서 직접 스트리밍 (로컬 파일 불필요) ───
if ($src === 'nas') {
    require_once __DIR__ . '/../includes/NasImageProxy.php';
    $ok = NasImageProxy::streamFile($no, $file);
    if (!$ok) {
        http_response_code(404);
    }
    exit;
}

// ─── 로컬 파일 소스: 기존 로직 ───
$path = '';
switch ($src) {
    case 'legacy':
        $path = $_SERVER['DOCUMENT_ROOT'] . '/shop/data/' . $file;
        break;
    case 'uploads':
        $path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/orders/' . $no . '/' . $file;
        break;
    case 'imgfolder':
        if (!empty($folder)) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $folder . '/' . $file;
        }
        break;
    case 'uploaded':
        // uploaded_files JSON 경로 - upload 폴더에서 검색
        $path = __DIR__ . '/upload/' . $no . '/' . $file;
        break;
    default:
        // 기본: 교정 이미지 (upload 폴더)
        $path = __DIR__ . '/upload/' . $no . '/' . $file;
        break;
}

// 경로 검증 (디렉토리 트래버설 방지)
$real_path = realpath($path);
if (!$real_path || !file_exists($real_path)) {
    http_response_code(404);
    exit;
}

// 허용 디렉토리 검증
$allowed_dirs = [
    realpath(__DIR__ . '/upload'),
    realpath($_SERVER['DOCUMENT_ROOT'] . '/shop/data'),
    realpath($_SERVER['DOCUMENT_ROOT'] . '/uploads/orders'),
    realpath($_SERVER['DOCUMENT_ROOT'] . '/ImgFolder'),
];
$allowed = false;
foreach ($allowed_dirs as $dir) {
    if ($dir && strpos($real_path, $dir) === 0) {
        $allowed = true;
        break;
    }
}
if (!$allowed) {
    http_response_code(403);
    exit;
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime_map = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'pdf' => 'application/pdf',
    'ai' => 'application/postscript',
    'psd' => 'application/octet-stream',
    'hwp' => 'application/x-hwp',
    'hwpx' => 'application/x-hwpx',
];

$mime = $mime_map[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($real_path));
header('Cache-Control: public, max-age=86400');
readfile($real_path);
