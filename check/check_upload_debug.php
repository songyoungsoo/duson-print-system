<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== 전단지 파일 업로드 디버그 ===\n\n";

// 1. 최근 주문 확인
require_once 'db.php';

$query = "SELECT no, session_id, product_type, ThingCate, ImgFolder, uploaded_files, created_at 
          FROM shop_temp 
          ORDER BY no DESC 
          LIMIT 10";

$result = mysqli_query($db, $query);

echo "📦 최근 10개 주문 (모든 품목):\n\n";

while ($row = mysqli_fetch_assoc($result)) {
    echo "주문 #{$row['no']}\n";
    echo "  - 세션: {$row['session_id']}\n";
    echo "  - 품목: {$row['product_type']}\n";
    echo "  - ThingCate: {$row['ThingCate']}\n";
    echo "  - ImgFolder: {$row['ImgFolder']}\n";
    echo "  - uploaded_files: " . (empty($row['uploaded_files']) ? '❌ 비어있음' : '✅ 있음') . "\n";
    
    if (!empty($row['uploaded_files'])) {
        $files = json_decode($row['uploaded_files'], true);
        echo "  - 파일 개수: " . count($files) . "\n";
        foreach ($files as $i => $file) {
            echo "    [$i] {$file['original_name']} ({$file['size']} bytes)\n";
        }
    }
    
    // 실제 폴더 확인
    if (!empty($row['ImgFolder'])) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $row['ImgFolder'];
        echo "  - 폴더 경로: $full_path\n";
        echo "  - 폴더 존재: " . (is_dir($full_path) ? '✅' : '❌') . "\n";
        
        if (is_dir($full_path)) {
            $files_in_folder = array_diff(scandir($full_path), ['.', '..']);
            echo "  - 폴더 내 파일: " . count($files_in_folder) . "개\n";
            foreach ($files_in_folder as $file) {
                $file_path = $full_path . '/' . $file;
                $file_size = filesize($file_path);
                echo "    - $file (" . number_format($file_size) . " bytes)\n";
            }
        }
    }
    
    echo "  - 생성일: {$row['created_at']}\n";
    echo "\n";
}

// 2. PHP 설정 확인
echo "\n=== PHP 업로드 설정 ===\n\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

// 3. 임시 디렉토리 확인
echo "\n=== 임시 디렉토리 ===\n\n";
$temp_dir = sys_get_temp_dir();
echo "임시 디렉토리: $temp_dir\n";
echo "쓰기 가능: " . (is_writable($temp_dir) ? '✅' : '❌') . "\n";

// 4. ImgFolder 권한 확인
echo "\n=== ImgFolder 권한 ===\n\n";
$img_folder = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder';
echo "ImgFolder 경로: $img_folder\n";
echo "존재: " . (is_dir($img_folder) ? '✅' : '❌') . "\n";
echo "쓰기 가능: " . (is_writable($img_folder) ? '✅' : '❌') . "\n";

// 5. 최근 에러 로그 확인 (있다면)
echo "\n=== PHP 에러 로그 ===\n\n";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    echo "에러 로그 파일: $error_log\n";
    $lines = file($error_log);
    $recent_lines = array_slice($lines, -20);
    echo "최근 20줄:\n";
    echo implode('', $recent_lines);
} else {
    echo "에러 로그 파일 없음\n";
}

mysqli_close($db);
?>
