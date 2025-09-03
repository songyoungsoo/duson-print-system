<?php
/**
 * ImgFolder 디렉토리 확인 및 생성
 * 경로: MlangPrintAuto/shop/check_imgfolder.php
 */

echo "<h1>📁 ImgFolder 디렉토리 확인</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>";

// 현재 파일 위치 확인
$current_dir = dirname(__FILE__);
echo "<p><strong>현재 파일 위치:</strong> " . htmlspecialchars($current_dir) . "</p>";

// ImgFolder 경로들 확인
$possible_paths = [
    $current_dir . "/../../ImgFolder",
    dirname($current_dir) . "/ImgFolder", 
    dirname(dirname($current_dir)) . "/ImgFolder"
];

echo "<h2>🔍 가능한 ImgFolder 경로들:</h2>";
foreach ($possible_paths as $index => $path) {
    $real_path = realpath($path);
    $exists = file_exists($path);
    $is_dir = is_dir($path);
    $writable = is_writable($path);
    
    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>경로 " . ($index + 1) . ":</h3>";
    echo "<p><strong>상대 경로:</strong> " . htmlspecialchars($path) . "</p>";
    echo "<p><strong>절대 경로:</strong> " . htmlspecialchars($real_path ?: '존재하지 않음') . "</p>";
    echo "<p><strong>존재 여부:</strong> " . ($exists ? '✅ 존재함' : '❌ 존재하지 않음') . "</p>";
    echo "<p><strong>디렉토리 여부:</strong> " . ($is_dir ? '✅ 디렉토리임' : '❌ 디렉토리 아님') . "</p>";
    echo "<p><strong>쓰기 권한:</strong> " . ($writable ? '✅ 쓰기 가능' : '❌ 쓰기 불가') . "</p>";
    echo "</div>";
}

// ImgFolder 생성 시도
echo "<h2>🔧 ImgFolder 생성 시도</h2>";

$target_path = $current_dir . "/../../ImgFolder";
if (!file_exists($target_path)) {
    echo "<p>ImgFolder가 존재하지 않습니다. 생성을 시도합니다...</p>";
    
    if (mkdir($target_path, 0755, true)) {
        echo "<p style='color: green;'>✅ ImgFolder 생성 성공: " . htmlspecialchars(realpath($target_path)) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ ImgFolder 생성 실패</p>";
        
        // 상위 디렉토리 권한 확인
        $parent_dir = dirname($target_path);
        echo "<p><strong>상위 디렉토리:</strong> " . htmlspecialchars($parent_dir) . "</p>";
        echo "<p><strong>상위 디렉토리 쓰기 권한:</strong> " . (is_writable($parent_dir) ? '✅ 가능' : '❌ 불가') . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ ImgFolder가 이미 존재합니다: " . htmlspecialchars(realpath($target_path)) . "</p>";
}

// 테스트 디렉토리 생성
echo "<h2>🧪 테스트 디렉토리 생성</h2>";

$test_path = $target_path . "/test_" . time();
if (file_exists($target_path)) {
    if (mkdir($test_path, 0755)) {
        echo "<p style='color: green;'>✅ 테스트 디렉토리 생성 성공: " . htmlspecialchars($test_path) . "</p>";
        
        // 테스트 파일 생성
        $test_file = $test_path . "/test.txt";
        if (file_put_contents($test_file, "테스트 파일입니다.")) {
            echo "<p style='color: green;'>✅ 테스트 파일 생성 성공</p>";
            
            // 정리
            unlink($test_file);
            rmdir($test_path);
            echo "<p style='color: blue;'>🧹 테스트 파일 및 디렉토리 정리 완료</p>";
        } else {
            echo "<p style='color: red;'>❌ 테스트 파일 생성 실패</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ 테스트 디렉토리 생성 실패</p>";
    }
} else {
    echo "<p style='color: red;'>❌ ImgFolder가 존재하지 않아 테스트를 건너뜁니다.</p>";
}

echo "</div>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 20px;
    background-color: #f5f5f5;
}

h1, h2, h3 {
    color: #333;
}
</style>