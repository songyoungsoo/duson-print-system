<?php
header('Content-Type: text/plain; charset=utf-8');

$base = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/_MlangPrintAuto_inserted_index.php';

echo "=== 전단지 업로드 디렉토리 확인 ===\n";
echo "Base directory: $base\n";
echo "Exists: " . (is_dir($base) ? 'YES' : 'NO') . "\n";
echo "Permissions: " . substr(sprintf('%o', fileperms($base)), -4) . "\n";

// 최근 업로드 디렉토리 5개 확인
echo "\n=== 최근 업로드 디렉토리 (최근 5개) ===\n";

function listRecentDirs($path, $depth = 0, $maxDepth = 4) {
    if ($depth >= $maxDepth || !is_dir($path)) return [];

    $items = [];
    $dirs = @scandir($path);
    if (!$dirs) return [];

    foreach ($dirs as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $path . '/' . $item;
        if (is_dir($fullPath)) {
            $items[] = [
                'path' => $fullPath,
                'name' => $item,
                'mtime' => filemtime($fullPath),
                'depth' => $depth
            ];
            $items = array_merge($items, listRecentDirs($fullPath, $depth + 1, $maxDepth));
        }
    }
    return $items;
}

$allDirs = listRecentDirs($base);
usort($allDirs, function($a, $b) {
    return $b['mtime'] - $a['mtime'];
});

$count = 0;
foreach (array_slice($allDirs, 0, 10) as $dir) {
    if ($dir['depth'] === 3) { // 최종 타임스탬프 디렉토리만 표시
        echo str_repeat('  ', $dir['depth']) . "→ " . basename($dir['path']);
        echo " (" . date('Y-m-d H:i:s', $dir['mtime']) . ")\n";

        // 파일 목록
        $files = @scandir($dir['path']);
        if ($files) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $dir['path'] . '/' . $file;
                    echo str_repeat('  ', $dir['depth'] + 1) . "📄 $file (" . filesize($filePath) . " bytes)\n";
                }
            }
        }
        $count++;
        if ($count >= 5) break;
    }
}

if ($count === 0) {
    echo "  (아직 업로드된 파일이 없습니다)\n";
}

echo "\n✅ 디렉토리 확인 완료\n";
?>
