<?php
/**
 * 디스크 사용량 확인 스크립트
 * 웹 서버의 전체 디스크 사용량과 주요 폴더별 크기를 확인합니다.
 */

// 시간 제한 해제 (큰 디렉토리 스캔 시 필요)
set_time_limit(300);

/**
 * 디렉토리 크기 계산 (재귀적)
 */
function getDirectorySize($path) {
    $total_size = 0;
    $path = realpath($path);

    if ($path === false || !file_exists($path)) {
        return 0;
    }

    if (is_file($path)) {
        return filesize($path);
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CATCH_GET_CHILD
    );

    foreach ($files as $file) {
        try {
            if ($file->isFile()) {
                $total_size += $file->getSize();
            }
        } catch (Exception $e) {
            // 권한 문제 등으로 접근 불가능한 파일은 스킵
            continue;
        }
    }

    return $total_size;
}

/**
 * 바이트를 읽기 쉬운 형식으로 변환
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

// 전체 디스크 정보
$total_space = disk_total_space($_SERVER['DOCUMENT_ROOT']);
$free_space = disk_free_space($_SERVER['DOCUMENT_ROOT']);
$used_space = $total_space - $free_space;

// 주요 폴더 목록
$folders = [
    'ImgFolder',
    'mlangorder_printauto',
    'mlangprintauto',
    'admin',
    'uploads',
    'CLAUDE_DOCS'
];

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>디스크 사용량 확인</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Malgun Gothic', sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4CAF50;
        }
        .summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .summary-item {
            margin: 10px 0;
            font-size: 16px;
        }
        .summary-item strong {
            display: inline-block;
            width: 150px;
            color: #555;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin: 15px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .warning {
            background: linear-gradient(90deg, #ff9800, #f57c00);
        }
        .danger {
            background: linear-gradient(90deg, #f44336, #d32f2f);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #4CAF50;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .folder-name {
            font-weight: 500;
            color: #333;
        }
        .folder-size {
            color: #666;
        }
        .loading {
            text-align: center;
            padding: 20px;
            color: #888;
        }
        .timestamp {
            text-align: right;
            color: #888;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 디스크 사용량 분석</h1>

        <div class="summary">
            <div class="summary-item">
                <strong>📁 전체 용량:</strong> <?php echo formatBytes($total_space); ?>
            </div>
            <div class="summary-item">
                <strong>✅ 사용 가능:</strong> <?php echo formatBytes($free_space); ?>
            </div>
            <div class="summary-item">
                <strong>⚠️ 사용 중:</strong> <?php echo formatBytes($used_space); ?>
            </div>

            <?php
            $usage_percent = ($used_space / $total_space) * 100;
            $bar_class = '';
            if ($usage_percent > 90) {
                $bar_class = 'danger';
            } elseif ($usage_percent > 75) {
                $bar_class = 'warning';
            }
            ?>

            <div class="progress-bar">
                <div class="progress-fill <?php echo $bar_class; ?>" style="width: <?php echo $usage_percent; ?>%">
                    <?php echo round($usage_percent, 1); ?>%
                </div>
            </div>
        </div>

        <h2>📂 주요 폴더별 사용량</h2>
        <table>
            <thead>
                <tr>
                    <th>폴더명</th>
                    <th>크기</th>
                    <th>경로</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($folders as $folder) {
                    $folder_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $folder;
                    echo '<tr>';
                    echo '<td class="folder-name">' . htmlspecialchars($folder) . '</td>';

                    if (file_exists($folder_path)) {
                        echo '<td class="loading">계산 중...</td>';
                        echo '<td>' . htmlspecialchars($folder_path) . '</td>';
                        echo '</tr>';
                        flush();
                        ob_flush();

                        $size = getDirectorySize($folder_path);
                        echo '<script>';
                        echo 'document.querySelector("tbody tr:nth-last-child(1) td:nth-child(2)").textContent = "' . formatBytes($size) . '";';
                        echo 'document.querySelector("tbody tr:nth-last-child(1) td:nth-child(2)").classList.remove("loading");';
                        echo 'document.querySelector("tbody tr:nth-last-child(1) td:nth-child(2)").classList.add("folder-size");';
                        echo '</script>';
                        flush();
                        ob_flush();
                    } else {
                        echo '<td class="folder-size">존재하지 않음</td>';
                        echo '<td>-</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>

        <div class="timestamp">
            마지막 업데이트: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
</body>
</html>
