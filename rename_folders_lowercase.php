<?php
/**
 * 폴더명 소문자 변환 스크립트 
 * Linux 웹호스팅 호환성을 위한 안전한 폴더명 변환
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$base_dir = __DIR__;
$dry_run = false; // 실제 변환 실행

echo "📁 폴더명 소문자 변환 스크립트\n";
echo "==============================\n\n";

if ($dry_run) {
    echo "🔍 DRY RUN 모드 - 실제 변환하지 않고 계획만 출력\n\n";
}

// 폴더명 변환 매핑
$folder_mappings = [
    'MlangPrintAuto' => 'mlangprintauto',
    'MlangPrintAuto/LittlePrint' => 'mlangprintauto/littleprint',
    'MlangPrintAuto/NameCard' => 'mlangprintauto/namecard', 
    'MlangPrintAuto/MerchandiseBond' => 'mlangprintauto/merchandisebond',
    'MlangPrintAuto/NcrFlambeau' => 'mlangprintauto/ncrflambeau',
    'MlangPrintAuto/Poster' => 'mlangprintauto/poster'
];

// 경로 참조 업데이트가 필요한 파일들
$path_reference_files = [
    'index.php',
    'left.php', 
    'api/gallery_items.php',
    'account/orders/detail.php',
    'includes/header.php',
    'includes/footer.php'
];

echo "📋 변환 계획:\n";
echo "=============\n";

foreach ($folder_mappings as $old_path => $new_path) {
    $full_old_path = $base_dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $old_path);
    $full_new_path = $base_dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $new_path);
    
    if (is_dir($full_old_path)) {
        echo "📁 {$old_path} → {$new_path}\n";
        
        if (!$dry_run) {
            if (!rename($full_old_path, $full_new_path)) {
                echo "   ❌ 변환 실패: {$old_path}\n";
            } else {
                echo "   ✅ 변환 완료\n";
            }
        }
    } else {
        echo "⚠️  폴더 없음: {$old_path}\n";
    }
}

echo "\n🔗 경로 참조 업데이트가 필요한 파일들:\n";
echo "=====================================\n";

// PHP 파일 내 경로 참조 확인
$path_updates = [
    '/MlangPrintAuto/' => '/mlangprintauto/',
    '/NameCard/' => '/namecard/',
    '/LittlePrint/' => '/littleprint/',
    '/MerchandiseBond/' => '/merchandisebond/',
    '/NcrFlambeau/' => '/ncrflambeau/',
    '/Poster/' => '/poster/',
    'MlangPrintAuto/' => 'mlangprintauto/',
    'NameCard/' => 'namecard/',
    'LittlePrint/' => 'littleprint/',
    'MerchandiseBond/' => 'merchandisebond/',
    'NcrFlambeau/' => 'ncrflambeau/',
    'Poster/' => 'poster/'
];

$total_updates_needed = 0;

foreach ($path_reference_files as $file) {
    $file_path = $base_dir . DIRECTORY_SEPARATOR . $file;
    
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        $updates_needed = 0;
        
        foreach ($path_updates as $old_ref => $new_ref) {
            if (strpos($content, $old_ref) !== false) {
                $updates_needed++;
                $total_updates_needed++;
            }
        }
        
        if ($updates_needed > 0) {
            echo "🔧 {$file} - {$updates_needed}개 참조 수정 필요\n";
        } else {
            echo "✅ {$file} - 수정 불필요\n";
        }
    } else {
        echo "❓ {$file} - 파일 없음\n";
    }
}

echo "\n📊 변환 영향 분석:\n";
echo "=================\n";
echo "변환할 폴더: " . count($folder_mappings) . "개\n";
echo "수정할 경로 참조: {$total_updates_needed}개\n";

echo "\n⚠️  주의사항:\n";
echo "===========\n";
echo "1. 웹서버 중단 후 작업 권장\n";
echo "2. 전체 백업 완료 확인 필수\n";
echo "3. 폴더명 변경 → 경로 참조 수정 순서로 진행\n";
echo "4. 변경 후 모든 품목 동작 테스트 필수\n";

if ($dry_run) {
    echo "\n🔄 실제 변환을 원하면 \$dry_run = false; 로 설정 후:\n";
    echo "   1. 이 스크립트 실행 (폴더명 변경)\n";
    echo "   2. 별도 스크립트로 경로 참조 수정\n";
}

echo "\n🎯 변환 후 예상 구조:\n";
echo "===================\n";
echo "mlangprintauto/\n";
echo "├── littleprint/     # (전 LittlePrint)\n";
echo "├── namecard/        # (전 NameCard)\n";
echo "├── merchandisebond/ # (전 MerchandiseBond)\n";
echo "├── ncrflambeau/     # (전 NcrFlambeau)\n";
echo "├── poster/          # (전 Poster)\n";
echo "├── inserted/        # (이미 소문자)\n";
echo "├── sticker/         # (이미 소문자)\n";
echo "├── msticker/        # (이미 소문자)\n";
echo "├── envelope/        # (이미 소문자)\n";
echo "├── cadarok/         # (이미 소문자)\n";
echo "└── shop/            # (이미 소문자)\n";

?>