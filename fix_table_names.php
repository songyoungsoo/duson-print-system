<?php
/**
 * 테이블명 대소문자 일괄 수정 스크립트
 * 카페24 호스팅 환경에 맞게 모든 테이블명을 대문자 형식으로 변경
 */

$directory = __DIR__ . '/MlangPrintAuto';
$fileCount = 0;
$modifiedCount = 0;

// 테이블명 매핑 (소문자 -> 대문자)
$tableMapping = [
    'mlangprintauto_transactioncate' => 'MlangPrintAuto_transactionCate',
    'mlangprintauto_namecard' => 'MlangPrintAuto_NameCard', 
    'mlangprintauto_envelope' => 'MlangPrintAuto_envelope',
    'mlangprintauto_littleprint' => 'MlangPrintAuto_LittlePrint',
    'mlangprintauto_merchandisebond' => 'MlangPrintAuto_MerchandiseBond',
    'mlangprintauto_ncrflambeau' => 'MlangPrintAuto_NcrFlambeau',
    'mlangprintauto_cadarok' => 'MlangPrintAuto_cadarok',
    'mlangprintauto_inserted' => 'MlangPrintAuto_inserted',
    'mlangprintauto_msticker' => 'MlangPrintAuto_msticker',
    'mlangprintauto_sticker' => 'MlangPrintAuto_sticker',
    'mlangorder_printauto' => 'MlangOrder_PrintAuto',
    'mlang_portfolio_bbs' => 'Mlang_portfolio_bbs'
];

function processFile($file, $tableMapping) {
    $content = file_get_contents($file);
    $originalContent = $content;
    $modified = false;
    
    foreach ($tableMapping as $oldName => $newName) {
        // SQL 쿼리 내의 테이블명 변경 (대소문자 무시)
        $patterns = [
            // FROM 절
            '/FROM\s+' . preg_quote($oldName, '/') . '\b/i',
            // JOIN 절  
            '/JOIN\s+' . preg_quote($oldName, '/') . '\b/i',
            // UPDATE 절
            '/UPDATE\s+' . preg_quote($oldName, '/') . '\b/i',
            // INSERT INTO 절
            '/INSERT\s+INTO\s+' . preg_quote($oldName, '/') . '\b/i',
            // 변수에 할당된 테이블명
            '/\$TABLE\s*=\s*["\']' . preg_quote($oldName, '/') . '["\']/i',
            // 쿼리 문자열 내 테이블명
            '/["\']' . preg_quote($oldName, '/') . '\b/i'
        ];
        
        $replacements = [
            'FROM ' . $newName,
            'JOIN ' . $newName,
            'UPDATE ' . $newName,
            'INSERT INTO ' . $newName,
            '$TABLE = "' . $newName . '"',
            '"' . $newName
        ];
        
        for ($i = 0; $i < count($patterns); $i++) {
            $newContent = preg_replace($patterns[$i], $replacements[$i], $content);
            if ($newContent !== $content) {
                $content = $newContent;
                $modified = true;
            }
        }
    }
    
    if ($modified) {
        file_put_contents($file, $content);
        return true;
    }
    
    return false;
}

function scanDirectory($dir, $tableMapping) {
    global $fileCount, $modifiedCount;
    
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            scanDirectory($path, $tableMapping);
        } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $fileCount++;
            if (processFile($path, $tableMapping)) {
                $modifiedCount++;
                echo "✅ 수정됨: " . str_replace(__DIR__, '', $path) . "\n";
            }
        }
    }
}

echo "=== 테이블명 대소문자 일괄 수정 시작 ===\n\n";
scanDirectory($directory, $tableMapping);
echo "\n=== 완료 ===\n";
echo "검사한 파일: {$fileCount}개\n";
echo "수정된 파일: {$modifiedCount}개\n";
?>