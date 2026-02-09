<?php
/**
 * Front Controller - 보안을 위한 단일 진입점
 *
 * 웹 루트: /public/ (이 폴더만 외부 접근 가능)
 * 앱 루트: /        (상위 디렉토리, 외부 접근 차단)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);

// ★ 핵심: DOCUMENT_ROOT를 앱 루트로 덮어쓰기
// 기존 코드들이 $_SERVER['DOCUMENT_ROOT']를 사용하므로 호환성 유지
$_SERVER['DOCUMENT_ROOT'] = APP_ROOT;
$_SERVER['REAL_DOCUMENT_ROOT'] = PUBLIC_ROOT;  // 원래 값 백업

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = strtok($requestUri, '?');
$requestUri = $requestUri === '/' ? '/index.php' : $requestUri;

// 정적 파일 처리
$staticExtensions = ['css','js','png','jpg','jpeg','gif','svg','ico','woff','woff2','ttf','eot','pdf','zip','mp4','webp','bmp'];
$extension = strtolower(pathinfo($requestUri, PATHINFO_EXTENSION));

if (in_array($extension, $staticExtensions)) {
    // 1차: 상위 디렉토리(APP_ROOT)에서 찾기
    $staticFile = APP_ROOT . $requestUri;

    // 2차: public 디렉토리에서 찾기 (fallback)
    if (!file_exists($staticFile)) {
        $staticFile = PUBLIC_ROOT . $requestUri;
    }

    if (file_exists($staticFile) && is_file($staticFile)) {
        $mimeTypes = [
            'css'=>'text/css','js'=>'application/javascript','png'=>'image/png',
            'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif',
            'svg'=>'image/svg+xml','ico'=>'image/x-icon','pdf'=>'application/pdf',
            'woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf','webp'=>'image/webp',
            'bmp'=>'image/bmp','mp4'=>'video/mp4','zip'=>'application/zip',
        ];
        header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($staticFile));
        header('Cache-Control: public, max-age=86400'); // 1일 캐시
        readfile($staticFile);
        exit;
    }

    // 디버깅: 파일을 찾지 못한 경우
    http_response_code(404);
    exit('File not found: ' . htmlspecialchars($requestUri));
}

// PHP 파일 라우팅
$targetFile = APP_ROOT . $requestUri;

// public 내의 PHP 파일도 확인 (fallback)
if (!file_exists($targetFile) && !is_dir($targetFile)) {
    $publicTarget = PUBLIC_ROOT . $requestUri;
    if (file_exists($publicTarget)) {
        $targetFile = $publicTarget;
    }
}

if (is_dir($targetFile)) {
    $targetFile = rtrim($targetFile, '/') . '/index.php';
}

if (!file_exists($targetFile) && !str_ends_with($targetFile, '.php')) {
    $targetFile .= '.php';
}

if (file_exists($targetFile) && is_file($targetFile)) {
    chdir(dirname($targetFile));
    require $targetFile;
    exit;
}

http_response_code(404);
echo "<h1>404 - Page Not Found</h1>";
echo "<p>요청: " . htmlspecialchars($requestUri) . "</p>";
