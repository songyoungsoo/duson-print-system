<?php
/**
 * 서버 경로 확인
 */

echo "=== 서버 경로 정보 ===\n\n";

echo "1. 현재 스크립트 위치:\n";
echo "   - __FILE__: " . __FILE__ . "\n";
echo "   - __DIR__: " . __DIR__ . "\n";

echo "\n2. 서버 환경 변수:\n";
echo "   - DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";
echo "   - SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET') . "\n";
echo "   - PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'NOT SET') . "\n";

echo "\n3. db.php 파일 찾기:\n";
$possible_paths = [
    __DIR__ . '/db.php',
    $_SERVER['DOCUMENT_ROOT'] . '/db.php',
    '../db.php',
    '../../db.php',
    '/var/www/html/db.php',
    '/home/dsp1830/www/db.php',
    '/dsp1830/www/db.php',
];

foreach ($possible_paths as $path) {
    $exists = file_exists($path);
    echo "   - $path: " . ($exists ? "✅ 존재" : "❌ 없음") . "\n";
    if ($exists) {
        echo "     실제 경로: " . realpath($path) . "\n";
    }
}

echo "\n4. 현재 디렉토리 파일 목록 (상위 10개):\n";
$files = scandir(__DIR__);
$count = 0;
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..' && $count < 10) {
        echo "   - $file\n";
        $count++;
    }
}

echo "\n5. 상위 디렉토리 파일 목록 (상위 10개):\n";
$parent_dir = dirname(__DIR__);
if (is_dir($parent_dir)) {
    $files = scandir($parent_dir);
    $count = 0;
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && $count < 10) {
            echo "   - $file\n";
            $count++;
        }
    }
}

echo "\n=== 확인 완료 ===\n";
?>
