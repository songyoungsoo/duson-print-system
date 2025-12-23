<?php
/**
 * 디스크 사용량 확인 스크립트
 * 실행: http://dsp1830.shop/check_disk_usage.php
 */

set_time_limit(300); // 5분 제한
ini_set('memory_limit', '256M');

header('Content-Type: text/html; charset=utf-8');

echo "<h2>💾 디스크 사용량 분석</h2>";
echo "<hr>";

$base_dir = __DIR__;

// 전체 디스크 정보
echo "<h3>📊 서버 디스크 정보</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";

$total_space = disk_total_space($base_dir);
$free_space = disk_free_space($base_dir);
$used_space = $total_space - $free_space;

echo "<tr><td><strong>전체 용량</strong></td><td>" . formatBytes($total_space) . "</td></tr>";
echo "<tr><td><strong>사용 중</strong></td><td>" . formatBytes($used_space) . " (" . round(($used_space / $total_space) * 100, 1) . "%)</td></tr>";
echo "<tr><td><strong>남은 공간</strong></td><td>" . formatBytes($free_space) . " (" . round(($free_space / $total_space) * 100, 1) . "%)</td></tr>";
echo "</table>";

echo "<hr>";
echo "<h3>📁 디렉토리별 사용량 (상위 20개)</h3>";
echo "<p>분석 중... (시간이 걸릴 수 있습니다)</p>";
flush();

// 디렉토리별 크기 계산
$dir_sizes = [];

$directories = array_filter(glob($base_dir . '/*'), 'is_dir');

foreach ($directories as $dir) {
    $dir_name = basename($dir);

    // 백업 파일 제외
    if (strpos($dir_name, 'backup_') === 0) {
        continue;
    }

    $size = getDirSize($dir);
    $dir_sizes[$dir_name] = $size;

    echo "<script>console.log('분석 완료: {$dir_name} - " . formatBytes($size) . "');</script>";
    flush();
}

// 크기 순으로 정렬
arsort($dir_sizes);

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>순위</th><th>디렉토리</th><th>크기</th><th>비율</th></tr>";

$rank = 1;
$total_analyzed = array_sum($dir_sizes);

foreach (array_slice($dir_sizes, 0, 20, true) as $dir => $size) {
    $percent = $total_analyzed > 0 ? round(($size / $total_analyzed) * 100, 1) : 0;

    echo "<tr>";
    echo "<td>{$rank}</td>";
    echo "<td><strong>{$dir}</strong></td>";
    echo "<td>" . formatBytes($size) . "</td>";
    echo "<td>{$percent}%</td>";
    echo "</tr>";

    $rank++;
}

echo "</table>";

echo "<hr>";
echo "<h3>📄 개별 파일 (상위 20개)</h3>";

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $files[$file->getPathname()] = $file->getSize();
    }
}

arsort($files);

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>순위</th><th>파일 경로</th><th>크기</th></tr>";

$rank = 1;
foreach (array_slice($files, 0, 20, true) as $file => $size) {
    $relative_path = str_replace($base_dir . '/', '', $file);

    echo "<tr>";
    echo "<td>{$rank}</td>";
    echo "<td><code>{$relative_path}</code></td>";
    echo "<td>" . formatBytes($size) . "</td>";
    echo "</tr>";

    $rank++;
}

echo "</table>";

echo "<hr>";
echo "<h3>📦 전체 웹사이트 크기</h3>";
echo "<p style='font-size: 24px; font-weight: bold; color: #2196f3;'>";
echo formatBytes($total_analyzed);
echo "</p>";

// 헬퍼 함수
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

function getDirSize($dir) {
    $size = 0;

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
    } catch (Exception $e) {
        // 권한 오류 등 무시
    }

    return $size;
}
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    margin: 20px;
    background: #f5f5f5;
}

h2, h3 {
    color: #333;
}

table {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

th {
    font-weight: bold;
}

tr:hover {
    background: #f9f9f9;
}
</style>
