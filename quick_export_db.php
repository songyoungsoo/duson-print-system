<?php
/**
 * 빠른 데이터베이스 백업 스크립트
 */
set_time_limit(300);
ini_set('memory_limit', '512M');

$host = 'localhost';
$user = 'dsp1830';
$password = 'ds701018';
$database = 'dsp1830';

// mysqldump 사용 (서버에 설치되어 있다면)
$backup_file = 'dsp1830_backup.sql';
$command = "mysqldump -h $host -u $user -p$password $database > $backup_file 2>&1";

exec($command, $output, $return_var);

if ($return_var === 0 && file_exists($backup_file)) {
    echo "백업 성공!\n";
    echo "파일: $backup_file\n";
    echo "크기: " . round(filesize($backup_file) / 1024 / 1024, 2) . " MB\n";
    echo "다운로드: http://dsp1830.shop/$backup_file\n";
} else {
    echo "mysqldump 실패. 출력:\n";
    echo implode("\n", $output);
    echo "\n\nPHP 방식으로 시도합니다...\n\n";

    // 브라우저로 직접 다운로드하도록 변경
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="dsp1830_backup.sql"');

    $conn = mysqli_connect($host, $user, $password, $database);
    if (!$conn) die("연결 실패");

    mysqli_set_charset($conn, "utf8mb4");

    echo "-- Database: $database\n";
    echo "-- Date: " . date('Y-m-d H:i:s') . "\n\n";

    $tables = mysqli_query($conn, "SHOW TABLES");
    while ($table = mysqli_fetch_array($tables)) {
        $tableName = $table[0];

        // 구조
        $create = mysqli_query($conn, "SHOW CREATE TABLE `$tableName`");
        $row = mysqli_fetch_array($create);
        echo "\n-- Table: $tableName\n";
        echo "DROP TABLE IF EXISTS `$tableName`;\n";
        echo $row[1] . ";\n\n";

        // 데이터 (제한적으로)
        $data = mysqli_query($conn, "SELECT * FROM `$tableName` LIMIT 1000");
        if (mysqli_num_rows($data) > 0) {
            echo "INSERT INTO `$tableName` VALUES ";
            $first = true;
            while ($row = mysqli_fetch_array($data, MYSQLI_NUM)) {
                if (!$first) echo ",";
                echo "\n(";
                for ($i = 0; $i < count($row); $i++) {
                    if ($i > 0) echo ",";
                    if (isset($row[$i])) {
                        echo "'" . mysqli_real_escape_string($conn, $row[$i]) . "'";
                    } else {
                        echo "NULL";
                    }
                }
                echo ")";
                $first = false;
            }
            echo ";\n";
        }
    }

    mysqli_close($conn);
}
?>
