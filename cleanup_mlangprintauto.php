<?php
/**
 * MlangPrintAuto 폴더 정리 스크립트
 * 백업, 테스트, 개발용 파일 및 디렉토리 정리
 * 
 * 실행 전 주의사항:
 * 1. 전체 프로젝트 백업 완료 확인
 * 2. 웹서버 중단 후 실행 권장
 * 3. 삭제 전 파일 목록 검토 필수
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$base_dir = __DIR__ . '/MlangPrintAuto';
$dry_run = false; // 실제 삭제 실행

echo "🧹 MlangPrintAuto 폴더 정리 스크립트\n";
echo "=====================================\n\n";

if ($dry_run) {
    echo "⚠️  DRY RUN 모드 - 실제 삭제하지 않고 목록만 출력\n\n";
} else {
    echo "🚨 실제 삭제 모드 - 주의하세요!\n\n";
}

// 삭제할 디렉토리 목록
$delete_directories = [
    '_backup',
    '_archive', 
    'LittlePrint사용안함',
    'sticker01',
    'cadarokTwo',
    'OfferOrder'
];

// 삭제할 파일 패턴
$delete_file_patterns = [
    // 백업 파일
    '*_backup.php',
    '*_old.php', 
    '*복사본.php',
    'db.php.member_backup',
    
    // 테스트 파일
    'test_*.html',
    'test_*.php',
    'check_*.php',
    
    // 개발/설정 파일
    'setup_*.php',
    'index20140215.php',
    'FormSemple*.php',
    '*_utf8.php',
    
    // 문서 파일
    '*.md',
    
    // 임시 파일
    '*.backup*',
    '*_temp.*',
    'usage_example.php'
];

// 보존할 중요 파일 (삭제 방지)
$preserve_files = [
    'index.php',
    'index_compact.php',
    'calculate_price_ajax.php',
    'add_to_basket.php',
    'get_paper_types.php',
    'get_quantities.php',
    'price_cal.php',
    'db.php',
    'db_ajax.php',
    'inc.php'
];

$deleted_count = 0;
$skipped_count = 0;
$total_size = 0;

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

function deleteDirectory($dir, $dry_run = true) {
    global $deleted_count, $total_size;
    
    if (!is_dir($dir)) return false;
    
    $files = array_diff(scandir($dir), array('.', '..'));
    $dir_size = 0;
    
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            $dir_size += deleteDirectory($path, $dry_run);
        } else {
            $file_size = filesize($path);
            $dir_size += $file_size;
            if (!$dry_run) {
                unlink($path);
            }
        }
    }
    
    if (!$dry_run) {
        rmdir($dir);
    }
    
    return $dir_size;
}

// 1. 디렉토리 삭제
echo "🗂️  삭제 대상 디렉토리:\n";
echo "----------------------------\n";

foreach ($delete_directories as $dir_name) {
    $dir_path = $base_dir . DIRECTORY_SEPARATOR . $dir_name;
    if (is_dir($dir_path)) {
        $size = deleteDirectory($dir_path, true); // 크기 계산용
        echo "📁 {$dir_name}/ (" . formatBytes($size) . ")\n";
        
        if (!$dry_run) {
            $actual_size = deleteDirectory($dir_path, false);
            $total_size += $actual_size;
            $deleted_count++;
            echo "   ✅ 삭제됨\n";
        } else {
            $total_size += $size;
        }
    }
}

echo "\n";

// 2. 파일 삭제 (재귀적으로 모든 하위 폴더 검색)
echo "📄 삭제 대상 파일들:\n";
echo "----------------------\n";

function scanForFiles($dir, $patterns, $preserve_files, $dry_run = true) {
    global $deleted_count, $total_size;
    
    if (!is_dir($dir)) return;
    
    // 백업 디렉토리는 건너뛰기
    if (strpos($dir, '_backup') !== false || 
        strpos($dir, '사용안함') !== false ||
        strpos($dir, 'sticker01') !== false ||
        strpos($dir, 'cadarokTwo') !== false) {
        return;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    
    foreach ($files as $file) {
        $file_path = $dir . DIRECTORY_SEPARATOR . $file;
        
        if (is_dir($file_path)) {
            scanForFiles($file_path, $patterns, $preserve_files, $dry_run);
        } else {
            // 보존할 파일인지 확인
            if (in_array($file, $preserve_files)) {
                continue;
            }
            
            // 삭제 패턴과 매치되는지 확인
            foreach ($patterns as $pattern) {
                if (fnmatch($pattern, $file)) {
                    $file_size = filesize($file_path);
                    $relative_path = str_replace($GLOBALS['base_dir'] . DIRECTORY_SEPARATOR, '', $file_path);
                    
                    echo "🗑️  {$relative_path} (" . formatBytes($file_size) . ")\n";
                    
                    if (!$dry_run) {
                        unlink($file_path);
                        $total_size += $file_size;
                        $deleted_count++;
                        echo "   ✅ 삭제됨\n";
                    } else {
                        $total_size += $file_size;
                    }
                    break;
                }
            }
        }
    }
}

scanForFiles($base_dir, $delete_file_patterns, $preserve_files, $dry_run);

// 3. 결과 요약
echo "\n📊 정리 결과 요약:\n";
echo "==================\n";
echo "삭제 대상 항목: " . ($deleted_count > 0 ? $deleted_count : "계산 중...") . "\n";
echo "절약 용량: " . formatBytes($total_size) . "\n";

if ($dry_run) {
    echo "\n⚠️  실제 삭제를 원하면 \$dry_run = false; 로 설정 후 재실행\n";
    echo "🔒 현재는 DRY RUN 모드로 파일이 삭제되지 않습니다\n";
} else {
    echo "\n✅ 정리 작업이 완료되었습니다!\n";
}

echo "\n📁 정리 후 남은 핵심 품목 폴더:\n";
echo "- inserted/ (전단지)\n";
echo "- NameCard/ (명함)\n"; 
echo "- sticker/ (일반스티커 - 구)\n";
echo "- sticker_new/ (일반스티커 - 신)\n";
echo "- msticker/ (자석스티커)\n";
echo "- envelope/ (봉투)\n";
echo "- LittlePrint/ (포스터)\n";
echo "- Poster/ (포스터 복사본)\n";
echo "- cadarok/ (카다록)\n";
echo "- MerchandiseBond/ (상품권)\n";
echo "- NcrFlambeau/ (전표)\n";
echo "- shop/ (장바구니 시스템)\n";

echo "\n🎯 정리 완료 후 기대 효과:\n";
echo "- 📦 용량 절약: ~" . formatBytes($total_size) . "\n";
echo "- 🚀 성능 향상: 파일 탐색 속도 개선\n";
echo "- 🧹 코드 정리: 개발/유지보수 효율성 증대\n";
echo "- 🔍 가독성: 핵심 파일만 남겨 구조 명확화\n";

?>