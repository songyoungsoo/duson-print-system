<?php
require_once __DIR__ . '/includes/UploadPathHelper.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== 업로드 경로 테스트 ===\n\n";

echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "현재 디렉토리: " . __DIR__ . "\n\n";

try {
    $paths = UploadPathHelper::generateUploadPath('inserted');
    
    echo "=== 생성된 경로 ===\n";
    echo "full_path: " . $paths['full_path'] . "\n";
    echo "db_path: " . $paths['db_path'] . "\n";
    echo "web_path: " . $paths['web_path'] . "\n\n";
    
    echo "=== 폴더 존재 여부 ===\n";
    echo "폴더 존재: " . (file_exists($paths['full_path']) ? '예' : '아니오') . "\n\n";
    
    echo "=== 폴더 생성 테스트 ===\n";
    if (!file_exists($paths['full_path'])) {
        if (mkdir($paths['full_path'], 0755, true)) {
            echo "✅ 폴더 생성 성공!\n";
            echo "생성된 경로: " . $paths['full_path'] . "\n";
        } else {
            echo "❌ 폴더 생성 실패!\n";
            echo "시도한 경로: " . $paths['full_path'] . "\n";
        }
    } else {
        echo "✅ 폴더 이미 존재\n";
    }
    
    echo "\n=== 권한 확인 ===\n";
    $imgFolder = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder';
    echo "ImgFolder 경로: $imgFolder\n";
    echo "ImgFolder 존재: " . (file_exists($imgFolder) ? '예' : '아니오') . "\n";
    echo "ImgFolder 쓰기 가능: " . (is_writable($imgFolder) ? '예' : '아니오') . "\n";
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}
?>
