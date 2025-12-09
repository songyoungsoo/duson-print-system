<?php
/**
 * 파일 업로드 디렉토리 권한 수정 스크립트
 * URL: http://dsp1830.shop/fix_upload_permissions.php
 *
 * 실행 후 반드시 삭제할 것!
 */

// 보안: 로컬에서만 실행 가능
$allowed_ips = ['127.0.0.1', '::1', 'localhost'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips) && !isset($_GET['force'])) {
    die('Access denied. Only localhost allowed.');
}

echo "<h1>Upload Directory Permissions Fix</h1>";
echo "<pre>";

$base_path = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/';
$directories = [
    '_MlangPrintAuto_inserted_index.php',
    '_MlangPrintAuto_namecard_index.php',
    '_MlangPrintAuto_NameCard_index.php',
    '_MlangPrintAuto_envelope_index.php',
    '_MlangPrintAuto_cadarok_index.php',
    '_MlangPrintAuto_littleprint_index.php',
    '_MlangPrintAuto_merchandisebond_index.php',
    '_MlangPrintAuto_MerchandiseBond_index.php',
    '_MlangPrintAuto_msticker_index.php',
    '_MlangPrintAuto_ncrflambeau_index.php',
    '_MlangPrintAuto_sticker_new_index.php'
];

$fixed_count = 0;
$failed_count = 0;

foreach ($directories as $dir) {
    $full_path = $base_path . $dir;

    if (!is_dir($full_path)) {
        echo "❌ 디렉토리 없음: $dir\n";
        continue;
    }

    // 현재 권한 확인
    $current_perms = substr(sprintf('%o', fileperms($full_path)), -4);
    echo "\n📁 $dir\n";
    echo "   현재 권한: $current_perms\n";

    // 775 권한으로 변경
    if (chmod($full_path, 0775)) {
        $fixed_count++;
        echo "   ✅ 권한 변경 성공: 0775\n";

        // 재귀적으로 하위 디렉토리도 수정
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($full_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                chmod($item->getPathname(), 0775);
            } else {
                chmod($item->getPathname(), 0664);
            }
        }
        echo "   ✅ 하위 항목 권한 변경 완료\n";
    } else {
        $failed_count++;
        echo "   ❌ 권한 변경 실패\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "완료: ✅ $fixed_count개 성공, ❌ $failed_count개 실패\n";
echo str_repeat("=", 50) . "\n";

echo "\n⚠️  보안 경고: 이 스크립트를 반드시 삭제하세요!\n";
echo "삭제 명령: rm " . __FILE__ . "\n";

echo "</pre>";
?>
