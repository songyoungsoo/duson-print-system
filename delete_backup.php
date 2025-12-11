<?php
/**
 * 백업 파일 삭제 스크립트
 */
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filename'])) {
    $filename = basename($_POST['filename']); // 보안: 경로 조작 방지
    $filepath = __DIR__ . '/' . $filename;

    echo "<h2>🗑️ 백업 파일 삭제</h2><hr>";

    // 파일명 검증 (백업 파일만 삭제 가능)
    if (strpos($filename, 'dsp1830_backup_') !== 0) {
        echo "<p style='color: red;'>✗ 잘못된 파일명입니다. 백업 파일만 삭제할 수 있습니다.</p>";
        exit;
    }

    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; border-radius: 5px;'>";
            echo "<h3>✓ 삭제 완료</h3>";
            echo "<p><strong>{$filename}</strong> 파일이 성공적으로 삭제되었습니다.</p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>✗ 파일 삭제에 실패했습니다. 권한을 확인하세요.</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ 파일이 존재하지 않습니다.</p>";
    }

    echo "<hr>";
    echo "<p><a href='download_all_files.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>← 다운로드 페이지로</a></p>";

} else {
    echo "<p>잘못된 접근입니다.</p>";
}
?>
