<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== 파일 업로드 테스트 결과 ===\n\n";

echo "POST 데이터:\n";
print_r($_POST);
echo "\n";

echo "FILES 데이터:\n";
print_r($_FILES);
echo "\n";

echo "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set') . "\n";
echo "Content-Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'not set') . "\n";
echo "\n";

if (!empty($_FILES['uploaded_files'])) {
    echo "파일 수신 성공!\n";
    echo "파일 개수: " . count($_FILES['uploaded_files']['name']) . "\n";
    foreach ($_FILES['uploaded_files']['name'] as $index => $filename) {
        echo "파일 $index: $filename (" . $_FILES['uploaded_files']['size'][$index] . " bytes)\n";
    }
} else {
    echo "⚠️ 파일이 수신되지 않았습니다.\n";
}

echo "\n=== 테스트 완료 ===\n";
?>
