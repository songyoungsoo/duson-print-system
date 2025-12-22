<?php
// 파일 업로드 디버깅 로그 확인 스크립트
header('Content-Type: text/html; charset=utf-8');

echo "<h2>파일 업로드 디버깅 로그 확인</h2>";
echo "<hr>";

// PHP 에러 로그 위치 확인
$error_log_path = ini_get('error_log');
echo "<h3>PHP 에러 로그 경로</h3>";
echo "<p><code>$error_log_path</code></p>";

// 최근 에러 로그 읽기 (마지막 200줄)
$log_files = [
    '/var/log/apache2/error.log',
    '/var/log/php_errors.log',
    '/var/log/httpd/error_log',
    $error_log_path
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file) && is_readable($log_file)) {
        echo "<h3>로그 파일: $log_file</h3>";

        // 마지막 200줄 읽기
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -200);

        // FILE UPLOAD DEBUG 관련 라인만 필터링
        $upload_logs = array_filter($recent_lines, function($line) {
            return strpos($line, 'FILE UPLOAD DEBUG') !== false ||
                   strpos($line, 'Inserted add_to_basket') !== false ||
                   strpos($line, 'FILES array') !== false ||
                   strpos($line, 'Physical path') !== false ||
                   strpos($line, 'File [') !== false ||
                   strpos($line, 'moved successfully') !== false ||
                   strpos($line, 'Failed to move') !== false ||
                   strpos($line, 'Upload summary') !== false;
        });

        if (!empty($upload_logs)) {
            echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 500px; overflow-y: auto;'>";
            foreach ($upload_logs as $line) {
                echo htmlspecialchars($line);
            }
            echo "</pre>";
        } else {
            echo "<p style='color: #999;'>관련 로그 없음</p>";
        }
    } else {
        echo "<p style='color: #ccc;'>$log_file - 접근 불가</p>";
    }
}

// $_FILES 테스트 폼
echo "<hr>";
echo "<h3>파일 업로드 테스트</h3>";
echo "<p>간단한 파일 업로드를 테스트해서 $_FILES 구조를 확인합니다.</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES)) {
    echo "<h4>📤 수신된 $_FILES 데이터:</h4>";
    echo "<pre style='background: #e8f5e9; padding: 10px; border: 1px solid #4caf50;'>";
    print_r($_FILES);
    echo "</pre>";

    echo "<h4>📋 $_POST 데이터:</h4>";
    echo "<pre style='background: #e3f2fd; padding: 10px; border: 1px solid #2196f3;'>";
    print_r($_POST);
    echo "</pre>";
}

?>

<form method="POST" enctype="multipart/form-data" style="background: #fff3e0; padding: 20px; border: 1px solid #ff9800;">
    <p><strong>테스트 1: 단일 파일</strong></p>
    <input type="file" name="test_file">
    <br><br>

    <p><strong>테스트 2: 다중 파일 (배열)</strong></p>
    <input type="file" name="uploaded_files[]" multiple>
    <br><br>

    <button type="submit" style="background: #4caf50; color: white; padding: 10px 20px; border: none; cursor: pointer;">
        파일 업로드 테스트
    </button>
</form>

<hr>
<h3>현재 PHP 설정</h3>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
    <tr>
        <td><strong>upload_max_filesize</strong></td>
        <td><?php echo ini_get('upload_max_filesize'); ?></td>
    </tr>
    <tr>
        <td><strong>post_max_size</strong></td>
        <td><?php echo ini_get('post_max_size'); ?></td>
    </tr>
    <tr>
        <td><strong>max_file_uploads</strong></td>
        <td><?php echo ini_get('max_file_uploads'); ?></td>
    </tr>
    <tr>
        <td><strong>upload_tmp_dir</strong></td>
        <td><?php echo ini_get('upload_tmp_dir') ?: '기본값 사용'; ?></td>
    </tr>
    <tr>
        <td><strong>file_uploads</strong></td>
        <td><?php echo ini_get('file_uploads') ? 'ON' : 'OFF'; ?></td>
    </tr>
</table>

<hr>
<h3>ImgFolder 디렉토리 상태</h3>
<?php
$img_folder = __DIR__ . '/ImgFolder';
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><td><strong>경로</strong></td><td>$img_folder</td></tr>";
echo "<tr><td><strong>존재</strong></td><td>" . (file_exists($img_folder) ? '✓ YES' : '✗ NO') . "</td></tr>";

if (file_exists($img_folder)) {
    echo "<tr><td><strong>쓰기 가능</strong></td><td>" . (is_writable($img_folder) ? '✓ YES' : '✗ NO') . "</td></tr>";
    echo "<tr><td><strong>권한</strong></td><td>" . substr(sprintf('%o', fileperms($img_folder)), -4) . "</td></tr>";
    echo "<tr><td><strong>소유자</strong></td><td>" . posix_getpwuid(fileowner($img_folder))['name'] . "</td></tr>";

    // 최근 생성된 디렉토리 확인
    $recent_dirs = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($img_folder, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            $recent_dirs[$item->getPathname()] = $item->getMTime();
        }
    }

    arsort($recent_dirs);
    $recent_dirs = array_slice($recent_dirs, 0, 10, true);

    echo "<tr><td colspan='2'><strong>최근 생성된 디렉토리 (Top 10)</strong></td></tr>";
    foreach ($recent_dirs as $dir => $mtime) {
        $relative = str_replace($img_folder, '', $dir);
        echo "<tr><td colspan='2'><code>$relative</code> - " . date('Y-m-d H:i:s', $mtime) . "</td></tr>";
    }
}
echo "</table>";
?>
