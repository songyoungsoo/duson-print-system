<?php
/**
 * 데이터베이스 백업 스크립트
 * 원격 서버에 업로드하여 실행하면 DB를 SQL 파일로 내보냅니다.
 */

// 데이터베이스 연결 정보
$host = 'localhost';
$user = 'dsp1830';
$password = 'ds701018';
$database = 'dsp1830';

// 출력 파일명
$backup_file = 'dsp1830_backup_' . date('Y-m-d_His') . '.sql';

// 데이터베이스 연결
$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("연결 실패: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// 헤더 출력
$output = "-- MySQL Database Backup\n";
$output .= "-- Database: $database\n";
$output .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
$output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$output .= "SET time_zone = \"+00:00\";\n\n";
$output .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
$output .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
$output .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
$output .= "/*!40101 SET NAMES utf8mb4 */;\n\n";

// 모든 테이블 가져오기
$tables_result = mysqli_query($conn, "SHOW TABLES");
$tables = array();

while ($row = mysqli_fetch_array($tables_result)) {
    $tables[] = $row[0];
}

echo "총 " . count($tables) . "개의 테이블을 백업합니다...\n\n";

// 각 테이블 백업
foreach ($tables as $table) {
    echo "백업 중: $table\n";

    // 테이블 구조
    $output .= "-- --------------------------------------------------------\n";
    $output .= "-- Table structure for table `$table`\n";
    $output .= "-- --------------------------------------------------------\n\n";
    $output .= "DROP TABLE IF EXISTS `$table`;\n";

    $create_table = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
    $row = mysqli_fetch_array($create_table);
    $output .= $row[1] . ";\n\n";

    // 테이블 데이터
    $rows = mysqli_query($conn, "SELECT * FROM `$table`");
    $num_rows = mysqli_num_rows($rows);

    if ($num_rows > 0) {
        $output .= "-- Dumping data for table `$table`\n\n";
        $output .= "INSERT INTO `$table` VALUES\n";

        $count = 0;
        while ($row = mysqli_fetch_array($rows, MYSQLI_NUM)) {
            $count++;
            $output .= "(";

            for ($i = 0; $i < count($row); $i++) {
                if (isset($row[$i])) {
                    $output .= "'" . mysqli_real_escape_string($conn, $row[$i]) . "'";
                } else {
                    $output .= "NULL";
                }

                if ($i < count($row) - 1) {
                    $output .= ", ";
                }
            }

            if ($count < $num_rows) {
                $output .= "),\n";
            } else {
                $output .= ");\n\n";
            }
        }
    }
}

$output .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
$output .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
$output .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

// 파일로 저장
file_put_contents($backup_file, $output);

mysqli_close($conn);

echo "\n백업 완료: $backup_file\n";
echo "파일 크기: " . round(filesize($backup_file) / 1024 / 1024, 2) . " MB\n";
echo "\n다운로드 링크: http://" . $_SERVER['HTTP_HOST'] . "/" . $backup_file . "\n";

// 다운로드 옵션 제공
if (isset($_GET['download'])) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $backup_file . '"');
    header('Content-Length: ' . filesize($backup_file));
    readfile($backup_file);
    exit;
}
?>
