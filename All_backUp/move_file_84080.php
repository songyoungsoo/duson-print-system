<?php
// 84080 주문 파일 이동 스크립트

$source_dir = "uploads/2025/11/07/222.108.84.120/";
$target_dir = "uploads/orders/84080/";

// 대상 디렉토리가 없으면 생성
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
    echo "✅ 디렉토리 생성: $target_dir\n";
}

// 소스 디렉토리의 모든 파일 가져오기
if (is_dir($source_dir)) {
    $files = scandir($source_dir);
    $moved_count = 0;
    
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $source_file = $source_dir . $file;
            $target_file = $target_dir . $file;
            
            if (is_file($source_file)) {
                // 파일 복사
                if (copy($source_file, $target_file)) {
                    echo "✅ 파일 복사 성공: $file\n";
                    echo "   원본: $source_file\n";
                    echo "   대상: $target_file\n";
                    $moved_count++;
                    
                    // 원본 파일 삭제 (선택사항 - 주석 해제하면 이동)
                    // unlink($source_file);
                } else {
                    echo "❌ 파일 복사 실패: $file\n";
                }
            }
        }
    }
    
    echo "\n📊 총 $moved_count 개 파일 처리 완료\n";
    
    // 대상 디렉토리 파일 목록 확인
    echo "\n📁 대상 디렉토리 파일 목록:\n";
    $target_files = scandir($target_dir);
    foreach ($target_files as $file) {
        if ($file != "." && $file != "..") {
            $size = filesize($target_dir . $file);
            $size_mb = round($size / 1024 / 1024, 2);
            echo "   - $file ({$size_mb}MB)\n";
        }
    }
} else {
    echo "❌ 소스 디렉토리를 찾을 수 없습니다: $source_dir\n";
}
?>
