<?php
/**
 * NAS DB Import Script (sknas205 버전)
 * delta SQL(스키마+데이터)을 sknas205 NAS MySQL에 import
 * 호출: http://sknas205.ipdisk.co.kr:8000/db_import.php?key=duson_nas_2026
 * 
 * 이 파일은 sknas205 NAS의 웹루트에 db_import.php로 배포해야 함
 * FTP 경로: /HDD1/duson260118/db_import.php
 */

$SECRET_KEY = 'duson_nas_2026';
$DELTA_FILE = '/mnt/HDD1/duson260118/db_backups/delta_orders.sql';
$DB_HOST    = 'localhost';
$DB_USER    = 'dsp1830';
$DB_PASS    = 'ds701018';
$DB_NAME    = 'dsp1830';

$key = $_GET['key'] ?? '';
if ($key !== $SECRET_KEY) {
    http_response_code(403);
    die('Unauthorized');
}

if (!file_exists($DELTA_FILE)) {
    http_response_code(404);
    die('Delta file not found: ' . $DELTA_FILE);
}

$filesize = round(filesize($DELTA_FILE) / 1024, 1);
$mtime    = date('Y-m-d H:i:s', filemtime($DELTA_FILE));

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    http_response_code(500);
    die('DB connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

$sql = file_get_contents($DELTA_FILE);
if ($sql === false) {
    http_response_code(500);
    die('Cannot read delta file');
}

// 세미콜론 기준으로 SQL 구문 분리 (mysqldump 형식에 맞게)
$count      = 0;
$errors     = 0;
$error_msgs = [];

// 한 줄씩 처리 (mysqldump는 INSERT가 한 줄로 출력됨)
$lines = explode("\n", $sql);
$buffer = '';

foreach ($lines as $line) {
    $trimmed = trim($line);

    // 주석 및 빈 줄 스킵
    if (empty($trimmed) || substr($trimmed, 0, 2) === '--' || $trimmed[0] === '#') continue;

    $buffer .= $line . "\n";

    // 세미콜론으로 끝나면 실행
    if (substr($trimmed, -1) === ';') {
        $stmt = trim($buffer);
        $buffer = '';

        // 실행할 구문 필터 (데이터 변경 + 스키마 관련)
        $upper = strtoupper(substr($stmt, 0, 20));
        $skip_keywords = ['SET @', '/*!'];
        $should_skip = false;
        foreach ($skip_keywords as $sk) {
            if (strpos($stmt, $sk) === 0) { $should_skip = true; break; }
        }
        if ($should_skip) continue;

        if (mysqli_query($conn, $stmt)) {
            $count++;
        } else {
            $err = mysqli_error($conn);
            // 무시해도 되는 에러는 카운트하지 않음
            if (strpos($err, 'already exists') === false) {
                $errors++;
                if (count($error_msgs) < 3) {
                    $error_msgs[] = $err . ' [' . substr($stmt, 0, 60) . '...]';
                }
            }
        }
    }
}

mysqli_close($conn);

$err_detail = $errors > 0 ? ' | ERR: ' . implode('; ', $error_msgs) : '';
echo "OK | {$count} stmts | {$errors} errors | {$filesize}KB | {$mtime}{$err_detail}";
