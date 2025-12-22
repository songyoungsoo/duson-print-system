<?php
header('Content-Type: text/plain; charset=utf-8');

$base = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/_MlangPrintAuto_inserted_index.php';

echo "=== 실제 전단지 파일 업로드 확인 ===\n";
echo "시간: " . date('Y-m-d H:i:s') . "\n";
echo "디렉토리: $base\n\n";

// 오늘 날짜 디렉토리 확인
$today_year = date('Y');
$today_dir = $base . '/' . $today_year;

echo "오늘 연도 디렉토리: $today_dir\n";
echo "존재 여부: " . (is_dir($today_dir) ? 'YES' : 'NO') . "\n\n";

if (!is_dir($today_dir)) {
    echo "❌ 오늘 업로드된 파일이 없습니다.\n";
    exit;
}

// 재귀적으로 모든 파일 찾기
function findAllFiles($dir, $depth = 0, $maxDepth = 5) {
    if ($depth > $maxDepth || !is_dir($dir)) return [];

    $files = [];
    $items = @scandir($dir);
    if (!$items) return [];

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $dir . '/' . $item;
        if (is_file($path)) {
            $files[] = [
                'path' => $path,
                'name' => $item,
                'size' => filesize($path),
                'mtime' => filemtime($path),
                'dir' => dirname($path)
            ];
        } elseif (is_dir($path)) {
            $files = array_merge($files, findAllFiles($path, $depth + 1, $maxDepth));
        }
    }

    return $files;
}

$all_files = findAllFiles($today_dir);

echo "=== 발견된 파일 목록 ===\n";
echo "총 파일 수: " . count($all_files) . "\n\n";

if (count($all_files) === 0) {
    echo "❌ 오늘 업로드된 파일이 없습니다.\n";
    echo "\n💡 테스트 방법:\n";
    echo "1. http://dsp1830.shop/mlangprintauto/inserted/ 접속\n";
    echo "2. 옵션 선택 후 '견적 계산'\n";
    echo "3. '파일 업로드 및 주문하기' 버튼\n";
    echo "4. 파일 선택 후 업로드\n";
} else {
    // 최근 파일 10개 표시
    usort($all_files, function($a, $b) {
        return $b['mtime'] - $a['mtime'];
    });

    echo "최근 업로드된 파일 (최대 10개):\n";
    foreach (array_slice($all_files, 0, 10) as $idx => $file) {
        echo "\n[" . ($idx + 1) . "] " . $file['name'] . "\n";
        echo "    크기: " . number_format($file['size']) . " bytes\n";
        echo "    업로드: " . date('Y-m-d H:i:s', $file['mtime']) . "\n";
        echo "    경로: " . str_replace($base . '/', '', $file['dir']) . "\n";
    }

    echo "\n✅ 파일 업로드가 정상 작동하고 있습니다!\n";
}
?>
