<?php
/**
 * db_constants.php include 제거 스크립트
 * 카페24 호스팅 환경 호환성을 위해 불필요한 파일 참조 제거
 */

$directory = __DIR__ . '/MlangPrintAuto';
$fileCount = 0;
$modifiedCount = 0;

function removeDbConstants($file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // db_constants.php include 라인 제거 패턴들
    $patterns = [
        // include 문 제거
        '/include\s+["\'].*?db_constants\.php["\']\s*;\s*\n/i',
        '/include_once\s+["\'].*?db_constants\.php["\']\s*;\s*\n/i',
        '/require\s+["\'].*?db_constants\.php["\']\s*;\s*\n/i',
        '/require_once\s+["\'].*?db_constants\.php["\']\s*;\s*\n/i',
        
        // 관련 주석도 제거
        '/\/\/\s*보안 상수 정의.*?\n.*?include\s+["\'].*?db_constants\.php["\']\s*;\s*\n/i',
        
        // 빈 줄 정리
        '/\n\n\n+/s'
    ];
    
    $replacements = [
        '', // include 제거
        '', // include_once 제거
        '', // require 제거
        '', // require_once 제거
        '', // 주석과 함께 제거
        "\n\n" // 빈 줄 정리
    ];
    
    for ($i = 0; $i < count($patterns); $i++) {
        $content = preg_replace($patterns[$i], $replacements[$i], $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        return true;
    }
    
    return false;
}

function scanDirectory($dir) {
    global $fileCount, $modifiedCount;
    
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            scanDirectory($path);
        } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $fileCount++;
            if (removeDbConstants($path)) {
                $modifiedCount++;
                echo "✅ 수정됨: " . str_replace(__DIR__, '', $path) . "\n";
            }
        }
    }
}

echo "=== db_constants.php 참조 제거 시작 ===\n\n";
scanDirectory($directory);
echo "\n=== 완료 ===\n";
echo "검사한 파일: {$fileCount}개\n";
echo "수정된 파일: {$modifiedCount}개\n";
?>