<?php
/**
 * 백업 프로세스 중지 및 임시 파일 삭제
 */
header('Content-Type: text/html; charset=utf-8');

echo "<h2>🛑 백업 프로세스 중지</h2>";
echo "<hr>";

// 백업 파일 찾기
$backup_files = glob(__DIR__ . '/backup_*.zip');

if (empty($backup_files)) {
    echo "<p>삭제할 백업 파일이 없습니다.</p>";
} else {
    echo "<h3>발견된 백업 파일:</h3>";
    echo "<ul>";
    foreach ($backup_files as $file) {
        $filename = basename($file);
        $size = filesize($file);
        $size_mb = round($size / 1024 / 1024, 2);

        if (unlink($file)) {
            echo "<li style='color: green;'>✓ 삭제됨: {$filename} ({$size_mb} MB)</li>";
        } else {
            echo "<li style='color: red;'>✗ 삭제 실패: {$filename}</li>";
        }
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>홈으로 돌아가기</a></p>";
?>
