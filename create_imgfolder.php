<?php
// ImgFolder 디렉토리 생성 및 권한 설정 스크립트

echo "<h2>ImgFolder 디렉토리 생성 및 권한 설정</h2>";

$base_dir = __DIR__;
$img_folder = $base_dir . '/ImgFolder';

echo "<h3>1. 현재 상태 확인</h3>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>항목</th><th>상태</th></tr>";

// ImgFolder 존재 여부
if (file_exists($img_folder)) {
    echo "<tr><td>ImgFolder 디렉토리</td><td style='color: blue;'>✓ 이미 존재</td></tr>";

    // 권한 확인
    $perms = fileperms($img_folder);
    $perms_str = substr(sprintf('%o', $perms), -4);
    echo "<tr><td>현재 권한</td><td>$perms_str</td></tr>";

    // 쓰기 가능 여부
    if (is_writable($img_folder)) {
        echo "<tr><td>쓰기 권한</td><td style='color: green;'>✓ 쓰기 가능</td></tr>";
    } else {
        echo "<tr><td>쓰기 권한</td><td style='color: red;'>✗ 쓰기 불가</td></tr>";
    }
} else {
    echo "<tr><td>ImgFolder 디렉토리</td><td style='color: orange;'>✗ 존재하지 않음</td></tr>";
}

echo "</table>";

echo "<h3>2. 디렉토리 생성 시도</h3>";

$success = true;

if (!file_exists($img_folder)) {
    if (mkdir($img_folder, 0777, true)) {
        echo "<p style='color: green;'>✓ ImgFolder 디렉토리 생성 성공</p>";
    } else {
        echo "<p style='color: red;'>✗ ImgFolder 디렉토리 생성 실패</p>";
        echo "<p>에러: " . error_get_last()['message'] . "</p>";
        $success = false;
    }
}

// 권한 설정
if (file_exists($img_folder)) {
    if (chmod($img_folder, 0777)) {
        echo "<p style='color: green;'>✓ 권한 설정 성공 (0777)</p>";
    } else {
        echo "<p style='color: orange;'>⚠ 권한 설정 실패 (기존 권한 유지)</p>";
    }
}

echo "<h3>3. 테스트 업로드 경로 생성</h3>";

// 테스트용 레거시 경로 생성
include "includes/upload_config.php";

$test_path_info = generateLegacyUploadPath('inserted');
$test_img_folder = $test_path_info['img_folder'];
$test_physical_path = $test_path_info['physical_path'];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>항목</th><th>값</th></tr>";
echo "<tr><td>상대 경로</td><td>$test_img_folder</td></tr>";
echo "<tr><td>절대 경로</td><td>$test_physical_path</td></tr>";
echo "</table>";

echo "<h3>4. 테스트 디렉토리 생성</h3>";

if (createLegacyUploadDirectory($test_physical_path)) {
    echo "<p style='color: green;'>✓ 테스트 디렉토리 생성 성공</p>";
    echo "<p>경로: $test_physical_path</p>";

    // 디렉토리 구조 확인
    $parts = explode('/', trim(str_replace($base_dir . '/', '', $test_physical_path), '/'));
    echo "<h4>생성된 디렉토리 구조:</h4>";
    echo "<pre>";
    echo "ImgFolder/\n";
    $current = $base_dir . '/ImgFolder';
    foreach (array_slice($parts, 1) as $index => $part) {
        echo str_repeat("  ", $index + 1) . "└── $part/\n";
        $current .= '/' . $part;
    }
    echo "</pre>";

    // 권한 확인
    echo "<h4>각 디렉토리 권한:</h4>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>디렉토리</th><th>권한</th><th>쓰기 가능</th></tr>";

    $current = $base_dir . '/ImgFolder';
    $rel_path = 'ImgFolder';

    if (file_exists($current)) {
        $perms = substr(sprintf('%o', fileperms($current)), -4);
        $writable = is_writable($current) ? '✓' : '✗';
        $color = is_writable($current) ? 'green' : 'red';
        echo "<tr><td>$rel_path</td><td>$perms</td><td style='color: $color;'>$writable</td></tr>";
    }

    foreach (array_slice($parts, 1) as $part) {
        $current .= '/' . $part;
        $rel_path .= '/' . $part;
        if (file_exists($current)) {
            $perms = substr(sprintf('%o', fileperms($current)), -4);
            $writable = is_writable($current) ? '✓' : '✗';
            $color = is_writable($current) ? 'green' : 'red';
            echo "<tr><td>$rel_path</td><td>$perms</td><td style='color: $color;'>$writable</td></tr>";
        }
    }
    echo "</table>";

} else {
    echo "<p style='color: red;'>✗ 테스트 디렉토리 생성 실패</p>";
    echo "<p>에러: " . error_get_last()['message'] . "</p>";
    $success = false;
}

echo "<hr>";

if ($success) {
    echo "<h3 style='color: green;'>✅ 설정 완료!</h3>";
    echo "<p><strong>이제 전단지 페이지에서 업로드가 정상적으로 작동합니다.</strong></p>";
    echo "<p><a href='mlangprintauto/inserted/index.php' style='font-size: 18px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>→ 전단지 페이지 테스트하기</a></p>";
} else {
    echo "<h3 style='color: red;'>⚠️ 수동 설정 필요</h3>";
    echo "<p>FTP 또는 SSH로 접속하여 다음 명령어를 실행하세요:</p>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border-left: 3px solid #ff5722;'>";
    echo "mkdir -p $img_folder\n";
    echo "chmod 777 $img_folder\n";
    echo "chmod 777 -R $img_folder\n";
    echo "</pre>";
}

echo "<hr>";
echo "<p><a href='check_shop_temp_columns.php'>→ 데이터베이스 컬럼 확인</a></p>";
echo "<p><a href='add_shop_temp_columns.php'>→ 컬럼 추가 스크립트</a></p>";
?>
