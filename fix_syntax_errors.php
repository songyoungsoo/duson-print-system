<?php
/**
 * Fix PHP syntax errors introduced during gallery_helper modifications
 */

echo "PHP 문법 오류 수정 시작...\n";

$files_to_fix = [
    'MlangPrintAuto/NameCard/index.php',
    'MlangPrintAuto/msticker/index.php', 
    'MlangPrintAuto/envelope/index.php',
    'MlangPrintAuto/cadarok/index.php',
    'MlangPrintAuto/NcrFlambeau/index.php',
    'MlangPrintAuto/MerchandiseBond/index.php',
    'MlangPrintAuto/LittlePrint/index.php',
    'MlangPrintAuto/inserted/index.php',
    'MlangPrintAuto/sticker_new/index.php'
];

$fixes_applied = 0;

foreach ($files_to_fix as $file_path) {
    $full_path = __DIR__ . '/' . $file_path;
    
    if (!file_exists($full_path)) {
        echo "파일이 존재하지 않음: $file_path\n";
        continue;
    }
    
    echo "처리 중: $file_path\n";
    $content = file_get_contents($full_path);
    $original_content = $content;
    $file_fixes = 0;
    
    // Fix 1: Unclosed include_product_gallery function calls
    if (preg_match('/if \(function_exists\("include_product_gallery"\)\) \{ include_product_gallery\([^}]+\);(?!\s*\})/', $content)) {
        $content = preg_replace(
            '/if \(function_exists\("include_product_gallery"\)\) \{ include_product_gallery\(([^)]+(?:\([^)]*\))*[^)]*)\);(?!\s*\})/',
            'if (function_exists("include_product_gallery")) { include_product_gallery($1); }',
            $content
        );
        $file_fixes++;
        echo "  - include_product_gallery 함수 호출 수정\n";
    }
    
    // Fix 2: Mismatched quotes in getCategoryOptions calls
    if (preg_match('/getCategoryOptions\(\$db,\s*"[^"]+\'\s*,\s*\'[^\']+\'\)/', $content)) {
        $content = preg_replace(
            '/getCategoryOptions\(\$db,\s*"([^"]+)\'\s*,\s*\'([^\']+)\'\)/',
            'getCategoryOptions($db, "$1", "$2")',
            $content
        );
        $file_fixes++;
        echo "  - getCategoryOptions 따옴표 수정\n";
    }
    
    // Fix 3: NcrFlambeau specific - wrong auth.php path  
    if (strpos($file_path, 'NcrFlambeau') !== false) {
        if (strpos($content, 'include "../includes/auth.php"') !== false) {
            $content = str_replace('include "../includes/auth.php"', 'include "../../includes/auth.php"', $content);
            $file_fixes++;
            echo "  - auth.php 경로 수정\n";
        }
    }
    
    // Save the file if changes were made
    if ($content !== $original_content) {
        file_put_contents($full_path, $content);
        $fixes_applied += $file_fixes;
        echo "  ✅ $file_fixes 개 오류 수정 완료\n";
    } else {
        echo "  ✅ 수정할 오류 없음\n";
    }
    
    echo "\n";
}

echo "=== 수정 완료 ===\n";
echo "총 $fixes_applied 개 문법 오류 수정\n";
echo "모든 파일 처리 완료!\n";
?>