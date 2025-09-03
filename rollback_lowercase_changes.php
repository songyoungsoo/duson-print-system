<?php
/**
 * 소문자 테이블명 변경 롤백 스크립트
 * 백업 위치: C:\xampp\htdocs/backup_lowercase_2025-08-28_17-48-10
 */

header('Content-Type: text/html; charset=utf-8');

function rollbackChanges() {
    $backupDir = 'C:\xampp\htdocs/backup_lowercase_2025-08-28_17-48-10';
    
    if (!is_dir($backupDir)) {
        echo "<p style='color:red'>백업 디렉토리를 찾을 수 없습니다: $backupDir</p>";
        return false;
    }
    
    $restored = 0;
    $failed = 0;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($backupDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $backupPath = $file->getRealPath();
            $originalPath = str_replace($backupDir, __DIR__, $backupPath);
            
            if (copy($backupPath, $originalPath)) {
                echo "<p style='color:green'>✅ 복원됨: " . str_replace(__DIR__, '', $originalPath) . "</p>";
                $restored++;
            } else {
                echo "<p style='color:red'>❌ 복원 실패: " . str_replace(__DIR__, '', $originalPath) . "</p>";
                $failed++;
            }
        }
    }
    
    echo "<h3>롤백 완료</h3>";
    echo "<p>복원된 파일: $restored개</p>";
    echo "<p>실패한 파일: $failed개</p>";
    return true;
}

echo '<h1>🔄 소문자 변환 롤백</h1>';

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    rollbackChanges();
} else {
    echo '<p>정말로 롤백하시겠습니까?</p>';
    echo '<a href="?confirm=yes" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">예, 롤백 실행</a>';
    echo ' ';
    echo '<a href="../" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">취소</a>';
}
?>