<?php
/**
 * UploadPathHelper 테스트 스크립트
 * URL: http://dsp1830.shop/mlangprintauto/inserted/test_upload_path.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>UploadPathHelper 테스트</h1>";

// 1. 파일 존재 확인
$helperPath = __DIR__ . '/../../includes/UploadPathHelper.php';
echo "<h2>1. 파일 존재 확인</h2>";
echo "경로: $helperPath<br>";
echo "존재: " . (file_exists($helperPath) ? '✅ YES' : '❌ NO') . "<br>";

if (file_exists($helperPath)) {
    echo "파일 크기: " . filesize($helperPath) . " bytes<br>";
    echo "읽기 가능: " . (is_readable($helperPath) ? '✅ YES' : '❌ NO') . "<br>";
}

echo "<hr>";

// 2. require 테스트
echo "<h2>2. Require 테스트</h2>";
try {
    require_once $helperPath;
    echo "✅ require_once 성공<br>";
} catch (Exception $e) {
    echo "❌ 에러: " . $e->getMessage() . "<br>";
    exit;
}

echo "<hr>";

// 3. 클래스 존재 확인
echo "<h2>3. 클래스 존재 확인</h2>";
echo "UploadPathHelper 클래스: " . (class_exists('UploadPathHelper') ? '✅ YES' : '❌ NO') . "<br>";

echo "<hr>";

// 4. 경로 생성 테스트
echo "<h2>4. 경로 생성 테스트</h2>";
try {
    $paths = UploadPathHelper::generateUploadPath('inserted');
    echo "<pre>";
    print_r($paths);
    echo "</pre>";

    echo "생성될 디렉토리: " . $paths['full_path'] . "<br>";
    echo "DB 저장 경로: " . $paths['db_path'] . "<br>";

} catch (Exception $e) {
    echo "❌ 에러: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>테스트 완료!</strong></p>";
?>
