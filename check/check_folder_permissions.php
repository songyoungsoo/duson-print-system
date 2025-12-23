<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== 폴더 권한 확인 ===\n\n";

$folders_to_check = [
    '/ImgFolder',
    '/ImgFolder/_MlangPrintAuto_inserted_index.php',
    '/ImgFolder/_MlangPrintAuto_envelope_index.php',
    '/ImgFolder/_MlangPrintAuto_namecard_index.php',
    '/ImgFolder/_MlangPrintAuto_sticker_new_index.php',
    '/ImgFolder/_MlangPrintAuto_msticker_index.php',
    '/ImgFolder/_MlangPrintAuto_cadarok_index.php',
    '/ImgFolder/_MlangPrintAuto_littleprint_index.php',
    '/ImgFolder/_MlangPrintAuto_ncrflambeau_index.php',
    '/ImgFolder/_MlangPrintAuto_merchandisebond_index.php',
];

foreach ($folders_to_check as $folder) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . $folder;
    
    echo "📁 $folder\n";
    echo "   전체 경로: $full_path\n";
    
    if (file_exists($full_path)) {
        echo "   존재: ✅ Yes\n";
        echo "   타입: " . (is_dir($full_path) ? "디렉토리" : "파일") . "\n";
        
        // 권한 확인
        $perms = fileperms($full_path);
        $perms_octal = substr(sprintf('%o', $perms), -4);
        echo "   권한: $perms_octal\n";
        
        // 읽기/쓰기/실행 권한
        echo "   읽기 가능: " . (is_readable($full_path) ? "✅" : "❌") . "\n";
        echo "   쓰기 가능: " . (is_writable($full_path) ? "✅" : "❌") . "\n";
        echo "   실행 가능: " . (is_executable($full_path) ? "✅" : "❌") . "\n";
        
        // 소유자 정보
        $owner = posix_getpwuid(fileowner($full_path));
        $group = posix_getgrgid(filegroup($full_path));
        echo "   소유자: " . $owner['name'] . " (UID: " . $owner['uid'] . ")\n";
        echo "   그룹: " . $group['name'] . " (GID: " . $group['gid'] . ")\n";
        
        // 현재 PHP 프로세스 사용자
        $current_user = posix_getpwuid(posix_geteuid());
        echo "   PHP 실행 사용자: " . $current_user['name'] . " (UID: " . $current_user['uid'] . ")\n";
        
        // 권한 문제 진단
        if (!is_writable($full_path)) {
            echo "   ⚠️ 경고: 쓰기 권한 없음!\n";
            echo "   해결: chmod 755 $full_path\n";
        }
        
    } else {
        echo "   존재: ❌ No\n";
        echo "   ⚠️ 폴더가 존재하지 않습니다.\n";
        
        // 상위 폴더 확인
        $parent = dirname($full_path);
        if (is_writable($parent)) {
            echo "   ✅ 상위 폴더 쓰기 가능 - 자동 생성 가능\n";
        } else {
            echo "   ❌ 상위 폴더 쓰기 불가 - 수동 생성 필요\n";
        }
    }
    
    echo "\n";
}

// 테스트: 파일 생성 시도
echo "=== 파일 생성 테스트 ===\n\n";

$test_folders = [
    $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/_MlangPrintAuto_inserted_index.php/test',
    $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/_MlangPrintAuto_envelope_index.php/test',
];

foreach ($test_folders as $test_folder) {
    echo "📝 테스트: $test_folder\n";
    
    // 폴더 생성 시도
    if (!is_dir($test_folder)) {
        if (@mkdir($test_folder, 0755, true)) {
            echo "   ✅ 폴더 생성 성공\n";
            
            // 파일 쓰기 시도
            $test_file = $test_folder . '/test.txt';
            if (@file_put_contents($test_file, 'test')) {
                echo "   ✅ 파일 쓰기 성공\n";
                
                // 정리
                @unlink($test_file);
                @rmdir($test_folder);
                echo "   ✅ 테스트 파일 삭제 완료\n";
            } else {
                echo "   ❌ 파일 쓰기 실패\n";
                echo "   에러: " . error_get_last()['message'] . "\n";
            }
        } else {
            echo "   ❌ 폴더 생성 실패\n";
            echo "   에러: " . error_get_last()['message'] . "\n";
        }
    } else {
        echo "   ⚠️ 테스트 폴더가 이미 존재함\n";
    }
    
    echo "\n";
}

// PHP 설정 확인
echo "=== PHP 설정 ===\n\n";
echo "upload_tmp_dir: " . ini_get('upload_tmp_dir') . "\n";
echo "open_basedir: " . (ini_get('open_basedir') ?: '제한 없음') . "\n";
echo "safe_mode: " . (ini_get('safe_mode') ? 'On' : 'Off') . "\n";

// 임시 디렉토리 확인
$temp_dir = sys_get_temp_dir();
echo "\n임시 디렉토리: $temp_dir\n";
echo "쓰기 가능: " . (is_writable($temp_dir) ? "✅" : "❌") . "\n";

?>
