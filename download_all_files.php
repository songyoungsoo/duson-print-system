<?php
/**
 * 전체 웹사이트 파일 다운로드 스크립트
 * 실행: http://dsp1830.shop/download_all_files.php
 */

set_time_limit(0);
ini_set('memory_limit', '1G');

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<title>전체 파일 다운로드</title>";
echo "<style>
body { font-family: 'Noto Sans KR', sans-serif; margin: 20px; background: #f5f5f5; }
h2 { color: #333; }
.progress { background: #e0e0e0; height: 30px; border-radius: 5px; margin: 20px 0; }
.progress-bar { background: #4caf50; height: 100%; border-radius: 5px; transition: width 0.3s; }
.status { padding: 10px; background: white; border-left: 4px solid #2196f3; margin: 10px 0; }
.success { border-left-color: #4caf50; }
.error { border-left-color: #f44336; }
</style></head><body>";

echo "<h2>📦 전체 웹사이트 파일 다운로드</h2>";
echo "<hr>";

$base_dir = __DIR__;
$zip_filename = 'dsp1830_backup_' . date('Ymd_His') . '.zip';
$zip_path = $base_dir . '/' . $zip_filename;

// 제외할 패턴
$exclude_patterns = [
    '/backup_',
    '/dsp1830_backup_',
    '/.git/',
    '/node_modules/',
    '/vendor/'
];

echo "<div class='status'>📁 파일 목록 수집 중...</div>";
flush();

// 파일 수집
$files = [];
$total_size = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $path = $item->getPathname();
    $relative = str_replace($base_dir . '/', '', $path);

    // 제외 패턴 확인
    $skip = false;
    foreach ($exclude_patterns as $pattern) {
        if (strpos($relative, $pattern) !== false) {
            $skip = true;
            break;
        }
    }

    if (!$skip && $item->isFile()) {
        $files[] = $path;
        $total_size += $item->getSize();
    }
}

$file_count = count($files);
$total_size_mb = round($total_size / 1024 / 1024, 2);

echo "<div class='status success'>✓ 총 {$file_count}개 파일 발견 (약 {$total_size_mb} MB)</div>";
echo "<div id='progress-container'>";
echo "<div class='progress'><div class='progress-bar' id='progress-bar' style='width: 0%'></div></div>";
echo "<div id='status-text'>압축 준비 중...</div>";
echo "</div>";
flush();

// ZIP 생성
$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    echo "<div class='status error'>✗ ZIP 파일 생성 실패</div>";
    exit;
}

$compressed = 0;
foreach ($files as $file) {
    $relative_path = str_replace($base_dir . '/', '', $file);
    $zip->addFile($file, $relative_path);
    $compressed++;

    if ($compressed % 50 == 0) {
        $percent = round(($compressed / $file_count) * 100, 1);
        echo "<script>
        document.getElementById('progress-bar').style.width = '{$percent}%';
        document.getElementById('status-text').innerHTML = '압축 중... {$compressed}/{$file_count} ({$percent}%)';
        </script>";
        flush();
    }
}

$zip->close();

$zip_size = filesize($zip_path);
$zip_size_mb = round($zip_size / 1024 / 1024, 2);

echo "<script>
document.getElementById('progress-bar').style.width = '100%';
document.getElementById('status-text').innerHTML = '✓ 압축 완료!';
</script>";

echo "<hr>";
echo "<div class='status success'>";
echo "<h3>✅ 백업 파일 생성 완료!</h3>";
echo "<p><strong>파일명:</strong> {$zip_filename}</p>";
echo "<p><strong>크기:</strong> {$zip_size_mb} MB</p>";
echo "<p><strong>파일 수:</strong> {$file_count}개</p>";
echo "</div>";

echo "<hr>";
echo "<h3>📥 다운로드</h3>";
echo "<p><a href='{$zip_filename}' download style='background: #4caf50; color: white; padding: 20px 40px; text-decoration: none; font-size: 20px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.2);'>💾 백업 파일 다운로드 ({$zip_size_mb} MB)</a></p>";

echo "<hr>";
echo "<h3>⚠️ 중요 안내</h3>";
echo "<ul>";
echo "<li>다운로드 완료 후 <strong>서버에서 백업 파일을 반드시 삭제</strong>하세요</li>";
echo "<li>데이터베이스는 별도로 백업해야 합니다 (phpMyAdmin 사용)</li>";
echo "<li>다운로드가 완료되면 아래 삭제 버튼을 클릭하세요</li>";
echo "</ul>";

echo "<form method='POST' action='delete_backup.php' style='margin-top: 20px;'>";
echo "<input type='hidden' name='filename' value='{$zip_filename}'>";
echo "<button type='submit' onclick='return confirm(\"정말 삭제하시겠습니까?\")' style='background: #f44336; color: white; padding: 15px 30px; border: none; cursor: pointer; border-radius: 5px; font-size: 16px;'>🗑️ 백업 파일 삭제</button>";
echo "</form>";

echo "</body></html>";
?>
