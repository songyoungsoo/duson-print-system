<?php
// 프로덕션 서버 파일 업로드 테스트

header('Content-Type: text/plain; charset=utf-8');

$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

require_once __DIR__ . '/includes/UploadPathHelper.php';

echo "=== 프로덕션 파일 업로드 테스트 ===\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Remote IP: " . $_SERVER['REMOTE_ADDR'] . "\n\n";

try {
    $paths = UploadPathHelper::generateUploadPath('inserted');

    echo "Generated Paths:\n";
    echo "  Full path: " . $paths['full_path'] . "\n";
    echo "  Web path: " . $paths['web_path'] . "\n";
    echo "  DB path: " . $paths['db_path'] . "\n\n";

    // 디렉토리 생성 테스트
    if (!file_exists($paths['full_path'])) {
        if (mkdir($paths['full_path'], 0775, true)) {
            echo "✅ Directory created successfully\n";
        } else {
            echo "❌ Failed to create directory\n";
            echo "Error: " . error_get_last()['message'] . "\n";
            exit(1);
        }
    } else {
        echo "✅ Directory already exists\n";
    }

    // 권한 확인
    $perms = fileperms($paths['full_path']);
    echo "Directory permissions: " . substr(sprintf('%o', $perms), -4) . "\n";

    // 테스트 파일 생성
    $test_file = $paths['full_path'] . '/test_upload_' . time() . '.txt';
    $content = 'Test upload file created at ' . date('Y-m-d H:i:s');

    if (file_put_contents($test_file, $content)) {
        echo "✅ File created: " . basename($test_file) . "\n";
        echo "✅ File size: " . filesize($test_file) . " bytes\n";
        echo "✅ File content: " . file_get_contents($test_file) . "\n\n";

        // 파일 목록
        echo "=== Files in directory ===\n";
        $files = scandir($paths['full_path']);
        $count = 0;
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "  - $file (" . filesize($paths['full_path'] . '/' . $file) . " bytes)\n";
                $count++;
            }
        }
        echo "\nTotal files: $count\n";

        // 정리
        unlink($test_file);
        echo "\n✅ Test file removed\n";

    } else {
        echo "❌ Failed to create file\n";
        echo "Error: " . error_get_last()['message'] . "\n";
        exit(1);
    }

    echo "\n✅✅✅ ALL TESTS PASSED! ✅✅✅\n";

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    exit(1);
}
?>
