<?php
/**
 * 로컬 ImgFolder 이미지를 dsp1830.shop으로 FTP 업로드
 *
 * 사용법: php /var/www/html/scripts/upload_images_to_dsp1830shop.php
 */

echo "=== dsp1830.shop 이미지 FTP 업로드 시작: " . date('Y-m-d H:i:s') . " ===\n\n";

// FTP 설정
$ftp_host = "dsp1830.shop";
$ftp_user = "dsp1830";
$ftp_pass = "ds701018";
$ftp_base = "www/ImgFolder";

// 로컬 설정
$local_imgfolder = "/var/www/html/ImgFolder";

// 동기화할 폴더 패턴
$sync_folders = [
    '_MlangPrintAuto_inserted_index.php',
    '_MlangPrintAuto_sticker_new_index.php',
    '_MlangPrintAuto_envelope_index.php',
    '_MlangPrintAuto_cadarok_index.php',
    '_MlangPrintAuto_MerchandiseBond_index.php',
    '_MlangPrintAuto_NcrFlambeau_index.php',
    '_MlangPrintAuto_NameCard_index.php',
    '_MlangPrintAuto_LittlePrint_index.php',
    '_MlangPrintAuto_msticker_index.php',
    '_MlangPrintAuto_unknown',
];

// FTP 연결
$ftp = ftp_connect($ftp_host);
if (!$ftp) {
    die("FTP 연결 실패: {$ftp_host}\n");
}

if (!ftp_login($ftp, $ftp_user, $ftp_pass)) {
    die("FTP 로그인 실패\n");
}

ftp_pasv($ftp, true);
echo "FTP 연결 성공: {$ftp_host}\n\n";

// 통계
$stats = [
    'total' => 0,
    'uploaded' => 0,
    'skipped' => 0,
    'failed' => 0,
    'dirs_created' => 0,
];

/**
 * FTP 디렉토리 재귀 생성
 */
function ftp_mkdirs($ftp, $path) {
    global $stats;
    $parts = explode('/', $path);
    $current = '';

    foreach ($parts as $part) {
        if (empty($part)) continue;
        $current .= '/' . $part;

        // 디렉토리 존재 확인
        if (@ftp_chdir($ftp, $current)) {
            ftp_chdir($ftp, '/');
            continue;
        }

        // 디렉토리 생성
        if (@ftp_mkdir($ftp, $current)) {
            $stats['dirs_created']++;
        }
    }

    return true;
}

/**
 * FTP 파일 존재 확인
 */
function ftp_file_exists($ftp, $path) {
    $size = @ftp_size($ftp, $path);
    return $size >= 0;
}

// 각 폴더 처리
foreach ($sync_folders as $folder) {
    $local_folder_path = "{$local_imgfolder}/{$folder}";

    if (!is_dir($local_folder_path)) {
        echo "⏭️  폴더 없음: {$folder}\n";
        continue;
    }

    echo "📁 처리 중: {$folder}\n";

    // 재귀적으로 모든 파일 찾기
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($local_folder_path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $folder_files = 0;
    $folder_uploaded = 0;

    foreach ($iterator as $file) {
        if ($file->isDir()) continue;

        $local_path = $file->getPathname();
        $relative_path = str_replace($local_imgfolder . '/', '', $local_path);
        $ftp_path = "{$ftp_base}/{$relative_path}";

        $stats['total']++;
        $folder_files++;

        // FTP 디렉토리 생성
        $ftp_dir = dirname($ftp_path);
        ftp_mkdirs($ftp, $ftp_dir);

        // 파일이 이미 존재하면 건너뜀
        if (ftp_file_exists($ftp, $ftp_path)) {
            $stats['skipped']++;
            continue;
        }

        // 업로드
        if (@ftp_put($ftp, $ftp_path, $local_path, FTP_BINARY)) {
            $stats['uploaded']++;
            $folder_uploaded++;
        } else {
            $stats['failed']++;
            echo "  ❌ 실패: {$relative_path}\n";
        }
    }

    echo "  ✅ {$folder_uploaded}/{$folder_files} 파일 업로드\n";
}

ftp_close($ftp);

echo "\n=== 업로드 완료: " . date('Y-m-d H:i:s') . " ===\n";
echo "총 파일: {$stats['total']}개\n";
echo "업로드: {$stats['uploaded']}개\n";
echo "건너뜀 (이미 존재): {$stats['skipped']}개\n";
echo "실패: {$stats['failed']}개\n";
echo "생성된 디렉토리: {$stats['dirs_created']}개\n";
?>
