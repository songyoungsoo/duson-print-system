<?php
/**
 * dsp114.com 전체 FTP 백업 스크립트
 *
 * 사용법: php /var/www/html/scripts/backup_dsp114_full.php
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

echo "=== dsp114.com 전체 백업 시작: " . date('Y-m-d H:i:s') . " ===\n\n";

// FTP 설정
$ftp_host = "dsp114.com";
$ftp_user = "duson1830";
$ftp_pass = "du1830";
$ftp_base = "/www";

// 로컬 백업 경로
$backup_date = date('Ymd_His');
$backup_dir = "/var/www/html/backup_dsp114_{$backup_date}";

// 백업 디렉토리 생성
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
    echo "백업 디렉토리 생성: {$backup_dir}\n";
}

// FTP 연결
$ftp = ftp_connect($ftp_host, 21, 30);
if (!$ftp) {
    die("❌ FTP 연결 실패: {$ftp_host}\n");
}

if (!ftp_login($ftp, $ftp_user, $ftp_pass)) {
    die("❌ FTP 로그인 실패\n");
}

ftp_pasv($ftp, true);
echo "✅ FTP 연결 성공: {$ftp_host}\n\n";

// 통계
$stats = [
    'dirs' => 0,
    'files' => 0,
    'downloaded' => 0,
    'failed' => 0,
    'total_size' => 0,
];

/**
 * FTP 디렉토리 재귀 다운로드
 */
function downloadDirectory($ftp, $remote_dir, $local_dir, &$stats) {
    // 로컬 디렉토리 생성
    if (!is_dir($local_dir)) {
        mkdir($local_dir, 0755, true);
        $stats['dirs']++;
    }

    // 디렉토리 내용 가져오기
    $contents = @ftp_nlist($ftp, $remote_dir);

    if ($contents === false) {
        echo "  ⚠️ 디렉토리 읽기 실패: {$remote_dir}\n";
        return;
    }

    foreach ($contents as $item) {
        // 현재 디렉토리와 상위 디렉토리 제외
        $basename = basename($item);
        if ($basename == '.' || $basename == '..') {
            continue;
        }

        $remote_path = $item;
        $local_path = $local_dir . '/' . $basename;

        // 디렉토리인지 파일인지 확인
        $size = @ftp_size($ftp, $remote_path);

        if ($size == -1) {
            // 디렉토리
            echo "📁 {$remote_path}\n";
            downloadDirectory($ftp, $remote_path, $local_path, $stats);
        } else {
            // 파일
            $stats['files']++;

            if (@ftp_get($ftp, $local_path, $remote_path, FTP_BINARY)) {
                $stats['downloaded']++;
                $stats['total_size'] += $size;

                // 진행상황 표시 (100개마다)
                if ($stats['downloaded'] % 100 == 0) {
                    $size_mb = round($stats['total_size'] / 1024 / 1024, 2);
                    echo "  📥 {$stats['downloaded']}개 파일 다운로드 완료 ({$size_mb} MB)\n";
                }
            } else {
                $stats['failed']++;
                echo "  ❌ 실패: {$remote_path}\n";
            }
        }
    }
}

// 주요 디렉토리 백업 (FTP 홈 기준 상대경로)
$directories_to_backup = [
    'www',  // 전체 웹 디렉토리
];

foreach ($directories_to_backup as $dir) {
    echo "\n📂 백업 시작: {$dir}\n";
    echo str_repeat("-", 50) . "\n";

    $local_target = $backup_dir;
    downloadDirectory($ftp, $dir, $local_target, $stats);
}

ftp_close($ftp);

// 결과 출력
$total_size_mb = round($stats['total_size'] / 1024 / 1024, 2);

echo "\n" . str_repeat("=", 50) . "\n";
echo "=== 백업 완료: " . date('Y-m-d H:i:s') . " ===\n";
echo str_repeat("=", 50) . "\n";
echo "백업 위치: {$backup_dir}\n";
echo "디렉토리: {$stats['dirs']}개\n";
echo "총 파일: {$stats['files']}개\n";
echo "다운로드 성공: {$stats['downloaded']}개\n";
echo "다운로드 실패: {$stats['failed']}개\n";
echo "총 용량: {$total_size_mb} MB\n";

// 백업 정보 저장
$info_file = $backup_dir . '/backup_info.txt';
file_put_contents($info_file,
    "dsp114.com 백업 정보\n" .
    "====================\n" .
    "백업 일시: " . date('Y-m-d H:i:s') . "\n" .
    "디렉토리: {$stats['dirs']}개\n" .
    "파일: {$stats['downloaded']}개\n" .
    "용량: {$total_size_mb} MB\n" .
    "실패: {$stats['failed']}개\n"
);

echo "\n✅ 백업 정보 저장: {$info_file}\n";
?>
