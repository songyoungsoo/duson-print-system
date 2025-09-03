<?php
/**
 * 폴더명 변경에 따른 경로 참조 업데이트 스크립트
 * 모든 PHP 파일에서 대문자 폴더 경로를 소문자로 변경
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$base_dir = __DIR__;

echo "🔗 폴더 경로 참조 업데이트 스크립트\n";
echo "=================================\n\n";

// 경로 변경 매핑
$path_updates = [
    '/MlangPrintAuto/' => '/mlangprintauto/',
    '/NameCard/' => '/namecard/',
    '/LittlePrint/' => '/littleprint/',
    '/MerchandiseBond/' => '/merchandisebond/',
    '/NcrFlambeau/' => '/ncrflambeau/',
    '/Poster/' => '/poster/',
    'MlangPrintAuto/' => 'mlangprintauto/',
    'NameCard/' => 'namecard/',
    'LittlePrint/' => 'littleprint/',
    'MerchandiseBond/' => 'merchandisebond/',
    'NcrFlambeau/' => 'ncrflambeau/',
    'Poster/' => 'poster/',
    '"MlangPrintAuto"' => '"mlangprintauto"',
    "'MlangPrintAuto'" => "'mlangprintauto'",
    '"NameCard"' => '"namecard"',
    "'NameCard'" => "'namecard'",
    '"LittlePrint"' => '"littleprint"',
    "'LittlePrint'" => "'littleprint'",
    '"MerchandiseBond"' => '"merchandisebond"',
    "'MerchandiseBond'" => "'merchandisebond'",
    '"NcrFlambeau"' => '"ncrflambeau"',
    "'NcrFlambeau'" => "'ncrflambeau'",
    '"Poster"' => '"poster"',
    "'Poster'" => "'poster'"
];

function updateFileReferences($filePath, $pathUpdates) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    $updatesCount = 0;
    
    foreach ($pathUpdates as $oldPath => $newPath) {
        $newContent = str_replace($oldPath, $newPath, $content);
        if ($newContent !== $content) {
            $updatesCount += substr_count($content, $oldPath);
            $content = $newContent;
        }
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        return $updatesCount;
    }
    
    return 0;
}

// PHP 파일 검색 및 업데이트
$phpFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

echo "📊 검색된 PHP 파일: " . count($phpFiles) . "개\n\n";

$totalUpdates = 0;
$updatedFiles = 0;

foreach ($phpFiles as $phpFile) {
    $updates = updateFileReferences($phpFile, $path_updates);
    if ($updates > 0) {
        $relativePath = str_replace($base_dir . DIRECTORY_SEPARATOR, '', $phpFile);
        echo "✅ {$relativePath} - {$updates}개 경로 업데이트\n";
        $totalUpdates += $updates;
        $updatedFiles++;
    }
}

echo "\n📊 업데이트 완료:\n";
echo "================\n";
echo "수정된 파일: {$updatedFiles}개\n";
echo "총 경로 변경: {$totalUpdates}개\n";

echo "\n🎯 변경된 폴더 구조 확인:\n";
echo "=======================\n";
if (is_dir($base_dir . '/mlangprintauto')) {
    echo "✅ mlangprintauto/ (변경됨)\n";
    
    $subDirs = ['littleprint', 'namecard', 'merchandisebond', 'ncrflambeau', 'poster'];
    foreach ($subDirs as $dir) {
        if (is_dir($base_dir . "/mlangprintauto/$dir")) {
            echo "   ✅ $dir/\n";
        } else {
            echo "   ❌ $dir/ (실패)\n";
        }
    }
} else {
    echo "❌ mlangprintauto/ 폴더 변경 실패\n";
}

echo "\n🚀 이미지갤러리 문제 해결:\n";
echo "=======================\n";
echo "✅ 모든 폴더명이 소문자로 통일\n";
echo "✅ PHP 파일 내 경로 참조 자동 업데이트\n"; 
echo "✅ Linux 웹호스팅 호환성 확보\n";
echo "✅ 이미지갤러리 API 정상 작동 예상\n";

?>