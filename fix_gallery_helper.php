<?php
/**
 * gallery_helper.php include 조건부 처리 스크립트
 * 카페24 호스팅 환경 호환성을 위해 파일 존재 체크 추가
 */

$directory = __DIR__ . '/MlangPrintAuto';
$fileCount = 0;
$modifiedCount = 0;

function fixGalleryHelper($file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // gallery_helper.php include를 조건부로 변경하는 패턴들
    $patterns = [
        // 단순 include를 조건부로 변경
        '/include\s+"([^"]*gallery_helper\.php)"\s*;/i',
        '/include\s+\'([^\']*gallery_helper\.php)\'\s*;/i',
        '/include_once\s+"([^"]*gallery_helper\.php)"\s*;/i',
        '/include_once\s+\'([^\']*gallery_helper\.php)\'\s*;/i',
    ];
    
    foreach ($patterns as $pattern) {
        $content = preg_replace_callback($pattern, function($matches) {
            $path = $matches[1];
            return "if (file_exists('$path')) { include_once '$path'; }";
        }, $content);
    }
    
    // init_gallery_system 함수 호출도 조건부로 처리
    $content = preg_replace(
        '/init_gallery_system\([\'"]([^\'"]+)[\'"]\);/i',
        'if (function_exists("init_gallery_system")) { init_gallery_system("$1"); }',
        $content
    );
    
    // include_product_gallery 함수 호출도 조건부로 처리
    $content = preg_replace(
        '/include_product_gallery\(/i',
        'if (function_exists("include_product_gallery")) { include_product_gallery(',
        $content
    );
    
    // 닫는 괄호 추가 (include_product_gallery용)
    $content = str_replace(
        'include_product_gallery"));',
        'include_product_gallery());}',
        $content
    );
    
    // include_gallery_assets 함수 호출도 조건부로 처리
    $content = preg_replace(
        '/include_gallery_assets\(\);/i',
        'if (function_exists("include_gallery_assets")) { include_gallery_assets(); }',
        $content
    );
    
    // GALLERY_ASSETS_NEEDED 체크도 조건부로
    $content = preg_replace(
        '/if\s*\(defined\([\'"]GALLERY_ASSETS_NEEDED[\'"]\)\)\s*\{/i',
        'if (defined("GALLERY_ASSETS_NEEDED") && function_exists("include_gallery_assets")) {',
        $content
    );
    
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
            if (fixGalleryHelper($path)) {
                $modifiedCount++;
                echo "✅ 수정됨: " . str_replace(__DIR__, '', $path) . "\n";
            }
        }
    }
}

echo "=== gallery_helper.php 조건부 처리 시작 ===\n\n";
scanDirectory($directory);
echo "\n=== 완료 ===\n";
echo "검사한 파일: {$fileCount}개\n";
echo "수정된 파일: {$modifiedCount}개\n";
?>