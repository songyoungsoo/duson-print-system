<?php
/**
 * 프로덕션 ImgFolder 정리 스크립트
 * 1개월(30일) 이전의 고객 원고 파일을 삭제하여 서버 용량 확보
 *
 * 배포 위치: https://dsp114.com/api/cleanup_uploads.php
 * 호출: GET ?key=duson_cleanup_2026
 * cron에서 호출: curl -s "https://dsp114.com/api/cleanup_uploads.php?key=duson_cleanup_2026"
 *
 * PHP 8.2 호환 (프로덕션 환경)
 */

// ─── 보안 ───
$SECRET_KEY = 'duson_cleanup_2026';
$key = $_GET['key'] ?? '';
if ($key !== $SECRET_KEY) {
    http_response_code(403);
    die('Unauthorized');
}

// ─── 설정 ───
$IMG_DIR = dirname(__DIR__) . '/ImgFolder';  // /httpdocs/ImgFolder
$KEEP_DAYS = 30;
$DRY_RUN = isset($_GET['dry_run']);  // ?dry_run 파라미터로 미리보기
$cutoff = time() - ($KEEP_DAYS * 86400);

if (!is_dir($IMG_DIR)) {
    die("ERROR: ImgFolder not found: $IMG_DIR");
}

// ─── 보호 목록 (삭제하면 안 되는 폴더/파일) ───
$PROTECTED = [
    'detail_page',
    'detail_page_backup',
    'detail_page_backup_1024x1024',
    'detail_page_staging',
    'cadarok',
    '.htaccess',
];

// ─── 정리 실행 ───
$deleted_count = 0;
$deleted_size = 0;
$skipped_count = 0;
$errors = [];

$items = scandir($IMG_DIR);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;

    // 보호 목록 체크
    if (in_array($item, $PROTECTED, true)) {
        $skipped_count++;
        continue;
    }

    // 제품 페이지 스크린샷 패턴 보호 (_MlangPrintAuto_*)
    if (strpos($item, '_MlangPrintAuto_') === 0 || strpos($item, '_mlangprintauto_') === 0) {
        $skipped_count++;
        continue;
    }

    $path = $IMG_DIR . '/' . $item;
    $mtime = filemtime($path);

    // 30일 이내면 보존
    if ($mtime >= $cutoff) continue;

    if ($DRY_RUN) {
        $size = is_dir($path) ? dirSize($path) : filesize($path);
        $deleted_count++;
        $deleted_size += $size;
        continue;
    }

    // 삭제 실행
    if (is_dir($path)) {
        $size = dirSize($path);
        if (deleteDir($path)) {
            $deleted_count++;
            $deleted_size += $size;
        } else {
            $errors[] = "Failed to delete dir: $item";
        }
    } else {
        $size = filesize($path);
        if (unlink($path)) {
            $deleted_count++;
            $deleted_size += $size;
        } else {
            $errors[] = "Failed to delete file: $item";
        }
    }
}

// ─── 결과 출력 ───
$mode = $DRY_RUN ? 'DRY-RUN' : 'EXECUTED';
$size_mb = round($deleted_size / 1024 / 1024, 1);
$err_str = count($errors) > 0 ? ' | ERRORS: ' . implode('; ', array_slice($errors, 0, 3)) : '';

echo "OK | {$mode} | deleted: {$deleted_count} | freed: {$size_mb}MB | skipped: {$skipped_count} | cutoff: " . date('Y-m-d', $cutoff) . $err_str;

// ─── 유틸리티 함수 ───

function dirSize(string $dir): int {
    $size = 0;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $file) {
        $size += $file->getSize();
    }
    return $size;
}

function deleteDir(string $dir): bool {
    if (!is_dir($dir)) return false;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    return rmdir($dir);
}
