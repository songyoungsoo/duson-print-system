<?php
/**
 * dsp114.com 교정시안 이미지 FTP 동기화 스크립트 (폴더 스캔 방식)
 * 
 * 사용법: php /var/www/html/scripts/sync_images_from_dsp114.php [start_no] [end_no]
 */

echo "=== dsp114.com 이미지 FTP 동기화 시작: " . date('Y-m-d H:i:s') . " ===\n\n";

// FTP 설정
$ftp_host = "dsp114.com";
$ftp_user = "duson1830";
$ftp_pass = "du1830";
$ftp_base = "www";

// 로컬 설정
$local_imgfolder = "/var/www/html/ImgFolder";
$local_shop_data = "/var/www/html/shop/data";

// DB 설정
$db_host = "localhost";
$db_user = "dsp1830";
$db_pass = "ds701018";
$db_name = "dsp1830";

// 폴더명 매핑 (dsp114 → 로컬)
$folder_mapping = [
    '_MlangPrintAuto_sticker_index.php' => '_MlangPrintAuto_sticker_new_index.php',
];

// 범위 설정
$start_no = isset($argv[1]) ? intval($argv[1]) : 79993;
$end_no = isset($argv[2]) ? intval($argv[2]) : 84229;

echo "처리 범위: no {$start_no} ~ {$end_no}\n\n";

// FTP 연결
$ftp = ftp_connect($ftp_host);
if (!$ftp || !ftp_login($ftp, $ftp_user, $ftp_pass)) {
    die("FTP 연결 실패\n");
}
ftp_pasv($ftp, true);
echo "FTP 연결 성공\n\n";

// DB 연결
$db = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($db->connect_error) {
    die("DB 연결 실패: " . $db->connect_error . "\n");
}
$db->set_charset('utf8mb4');

// 통계
$stats = [
    'total' => 0,
    'downloaded' => 0,
    'skipped' => 0,
    'failed' => 0,
    'folders_processed' => 0,
    'db_updated' => 0,
];

// 1. MlangPrintAuto 폴더 경로 데이터 조회 (폴더 단위로 처리)
$query = "SELECT DISTINCT ImgFolder 
          FROM mlangorder_printauto 
          WHERE no BETWEEN ? AND ?
            AND ImgFolder LIKE '_MlangPrintAuto_%'
            AND ImgFolder != ''
          ORDER BY ImgFolder";
$stmt = $db->prepare($query);
$stmt->bind_param("ii", $start_no, $end_no);
$stmt->execute();
$result = $stmt->get_result();

echo "=== MlangPrintAuto 폴더 동기화 ===\n";
$folder_count = $result->num_rows;
echo "처리할 폴더: {$folder_count}개\n\n";

while ($row = $result->fetch_assoc()) {
    $img_folder = $row['ImgFolder'];
    $stats['folders_processed']++;
    
    // FTP 경로
    $ftp_folder = "{$ftp_base}/ImgFolder/{$img_folder}";
    
    // 로컬 경로 (폴더 매핑 적용)
    $mapped_folder = $img_folder;
    foreach ($folder_mapping as $old => $new) {
        if (strpos($img_folder, $old) !== false) {
            $mapped_folder = str_replace($old, $new, $img_folder);
            break;
        }
    }
    $local_folder = "{$local_imgfolder}/{$mapped_folder}";
    
    // FTP 폴더 내 파일 목록
    $files = @ftp_nlist($ftp, $ftp_folder);
    if (!$files || count($files) == 0) {
        continue;
    }
    
    // 로컬 폴더 생성
    if (!is_dir($local_folder)) {
        mkdir($local_folder, 0755, true);
    }
    
    // 각 파일 다운로드
    foreach ($files as $ftp_file) {
        $filename = basename($ftp_file);
        if ($filename == '.' || $filename == '..') continue;
        
        $stats['total']++;
        $local_path = "{$local_folder}/{$filename}";
        
        // 이미 존재하면 건너뜀
        if (file_exists($local_path)) {
            $stats['skipped']++;
            continue;
        }
        
        // 다운로드
        $temp_file = tempnam(sys_get_temp_dir(), 'ftp_');
        if (@ftp_get($ftp, $temp_file, $ftp_file, FTP_BINARY)) {
            if (rename($temp_file, $local_path)) {
                $stats['downloaded']++;
            } else {
                $stats['failed']++;
                @unlink($temp_file);
            }
        } else {
            $stats['failed']++;
            @unlink($temp_file);
        }
    }
    
    // DB 업데이트 (폴더 매핑 변경된 경우)
    if ($mapped_folder !== $img_folder) {
        $update_sql = "UPDATE mlangorder_printauto SET ImgFolder = ? WHERE ImgFolder = ? AND no BETWEEN ? AND ?";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->bind_param("ssii", $mapped_folder, $img_folder, $start_no, $end_no);
        if ($update_stmt->execute()) {
            $stats['db_updated'] += $update_stmt->affected_rows;
        }
        $update_stmt->close();
    }
    
    // 진행 표시
    echo "✅ {$stats['folders_processed']}/{$folder_count}: {$img_folder}\n";
}
$stmt->close();

// 2. shop/data 파일 동기화
echo "\n=== shop/data 파일 동기화 ===\n";

$query2 = "SELECT DISTINCT ThingCate 
           FROM mlangorder_printauto 
           WHERE no BETWEEN ? AND ?
             AND ImgFolder LIKE '../shop/data%'
             AND ThingCate IS NOT NULL AND ThingCate != ''";
$stmt2 = $db->prepare($query2);
$stmt2->bind_param("ii", $start_no, $end_no);
$stmt2->execute();
$result2 = $stmt2->get_result();

echo "처리할 파일: " . $result2->num_rows . "개\n\n";

// 로컬 shop/data 폴더 생성
if (!is_dir($local_shop_data)) {
    mkdir($local_shop_data, 0755, true);
}

while ($row = $result2->fetch_assoc()) {
    $filename = $row['ThingCate'];
    $stats['total']++;
    
    $ftp_path = "{$ftp_base}/shop/data/{$filename}";
    $local_path = "{$local_shop_data}/{$filename}";
    
    // 이미 존재하면 건너뜀
    if (file_exists($local_path)) {
        $stats['skipped']++;
        continue;
    }
    
    // 다운로드
    $temp_file = tempnam(sys_get_temp_dir(), 'ftp_');
    if (@ftp_get($ftp, $temp_file, $ftp_path, FTP_BINARY)) {
        if (rename($temp_file, $local_path)) {
            $stats['downloaded']++;
            echo "✅ {$filename}\n";
        } else {
            $stats['failed']++;
            @unlink($temp_file);
        }
    } else {
        $stats['failed']++;
        @unlink($temp_file);
    }
}
$stmt2->close();

$db->close();
ftp_close($ftp);

echo "\n=== 동기화 완료: " . date('Y-m-d H:i:s') . " ===\n";
echo "폴더 처리: {$stats['folders_processed']}개\n";
echo "총 파일: {$stats['total']}개\n";
echo "다운로드: {$stats['downloaded']}개\n";
echo "건너뜀: {$stats['skipped']}개\n";
echo "실패: {$stats['failed']}개\n";
echo "DB 업데이트: {$stats['db_updated']}개\n";
?>
