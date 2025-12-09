<?php
/**
 * 웹사이트 전체 백업 스크립트
 * 실행: http://dsp1830.shop/backup_website.php
 */

set_time_limit(0); // 시간 제한 없음
ini_set('memory_limit', '512M');

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔄 웹사이트 백업 시작</h2>";
echo "<hr>";

$backup_dir = __DIR__ . '/backup_' . date('Ymd_His');
$zip_file = $backup_dir . '.zip';

// 제외할 디렉토리/파일
$exclude_dirs = [
    'backup_*',
    'node_modules',
    '.git',
    'vendor'
];

// 백업 대상 파일 목록
$files_to_backup = [];

echo "<p>📁 파일 목록 수집 중...</p>";
flush();

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$total_files = 0;
foreach ($iterator as $item) {
    $path = $item->getPathname();
    $relative_path = str_replace(__DIR__ . '/', '', $path);

    // 제외 디렉토리 확인
    $skip = false;
    foreach ($exclude_dirs as $exclude) {
        if (fnmatch($exclude, $relative_path) ||
            strpos($relative_path, str_replace('*', '', $exclude)) === 0) {
            $skip = true;
            break;
        }
    }

    if (!$skip && $item->isFile()) {
        $files_to_backup[] = $path;
        $total_files++;

        if ($total_files % 100 == 0) {
            echo "<p>수집 중... {$total_files}개 파일</p>";
            flush();
        }
    }
}

echo "<p>✓ 총 {$total_files}개 파일 발견</p>";
echo "<hr>";

// ZIP 생성
echo "<p>📦 압축 파일 생성 중...</p>";
flush();

$zip = new ZipArchive();
if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("❌ ZIP 파일 생성 실패");
}

$compressed = 0;
foreach ($files_to_backup as $file) {
    $relative_path = str_replace(__DIR__ . '/', '', $file);
    $zip->addFile($file, $relative_path);
    $compressed++;

    if ($compressed % 100 == 0) {
        $percent = round(($compressed / $total_files) * 100, 1);
        echo "<p>압축 중... {$compressed}/{$total_files} ({$percent}%)</p>";
        flush();
    }
}

$zip->close();

$zip_size = filesize($zip_file);
$zip_size_mb = round($zip_size / 1024 / 1024, 2);

echo "<hr>";
echo "<h3>✅ 백업 완료!</h3>";
echo "<p><strong>파일명:</strong> " . basename($zip_file) . "</p>";
echo "<p><strong>크기:</strong> {$zip_size_mb} MB</p>";
echo "<p><strong>파일 수:</strong> {$total_files}개</p>";
echo "<hr>";

echo "<h3>📥 다운로드</h3>";
echo "<p><a href='" . basename($zip_file) . "' download style='background: #4caf50; color: white; padding: 15px 30px; text-decoration: none; font-size: 18px; border-radius: 5px; display: inline-block;'>💾 백업 파일 다운로드 ({$zip_size_mb} MB)</a></p>";

echo "<hr>";
echo "<h3>⚠️ 주의사항</h3>";
echo "<ul>";
echo "<li>다운로드 후 서버에서 백업 파일을 삭제하세요</li>";
echo "<li>데이터베이스는 별도로 백업해야 합니다</li>";
echo "<li>ImgFolder 용량이 크면 시간이 오래 걸릴 수 있습니다</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>🗑️ 백업 파일 삭제</h3>";
echo "<form method='POST' style='margin-top: 20px;'>";
echo "<input type='hidden' name='delete_backup' value='" . basename($zip_file) . "'>";
echo "<button type='submit' onclick='return confirm(\"정말 삭제하시겠습니까?\")' style='background: #f44336; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 3px;'>🗑️ 백업 파일 삭제</button>";
echo "</form>";

// 백업 파일 삭제 처리
if (isset($_POST['delete_backup'])) {
    $file_to_delete = __DIR__ . '/' . basename($_POST['delete_backup']);
    if (file_exists($file_to_delete) && unlink($file_to_delete)) {
        echo "<p style='color: green; font-weight: bold;'>✓ 백업 파일이 삭제되었습니다.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ 삭제 실패</p>";
    }
}
?>
