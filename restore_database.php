<?php
/**
 * 웹 서버 데이터베이스 복원 스크립트
 *
 * 사용법:
 * 1. 이 파일을 웹 서버에 업로드
 * 2. clean.sql 파일도 동일한 위치에 업로드
 * 3. 브라우저에서 접속: https://dsp1830.shop/restore_database.php?key=DS1830RESTORE
 *
 * ⚠️ 보안: 작업 완료 후 반드시 이 파일을 삭제하세요!
 */

// 보안 키 (URL 파라미터로 전달)
$security_key = 'DS1830RESTORE';
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $security_key) {
    die('❌ 접근 거부: 올바른 보안 키가 필요합니다.');
}

// 데이터베이스 설정
$db_host = 'localhost';
$db_user = 'dsp1830';
$db_pass = 'ds701018';
$db_name = 'dsp1830';

// clean_webserver.sql 파일 경로 (MySQL 5.7 호환 버전)
$sql_file = __DIR__ . '/clean_webserver.sql';

// 타임아웃 설정 (대용량 SQL 처리)
set_time_limit(300); // 5분
ini_set('memory_limit', '256M');

echo "<html><head><meta charset='utf-8'><title>데이터베이스 복원</title></head><body>";
echo "<h1>🔧 데이터베이스 복원 작업</h1>";
echo "<pre>";

// 1. SQL 파일 확인
echo "\n📂 1단계: SQL 파일 확인...\n";
if (!file_exists($sql_file)) {
    die("❌ 오류: clean_webserver.sql 파일을 찾을 수 없습니다.\n경로: $sql_file\n");
}
$file_size = filesize($sql_file);
echo "✅ clean_webserver.sql 파일 발견 (" . number_format($file_size / 1024, 2) . " KB)\n";
echo "   (MySQL 5.7+ 호환 버전: utf8mb4_unicode_ci 사용)\n";

// 2. 데이터베이스 연결
echo "\n🔌 2단계: 데이터베이스 연결...\n";
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die("❌ 연결 실패: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');
echo "✅ 데이터베이스 연결 성공\n";

// 3. 기존 데이터베이스 백업
echo "\n💾 3단계: 현재 데이터베이스 백업...\n";
$backup_file = __DIR__ . '/backup_production_' . date('YmdHis') . '.sql';
$backup_command = "mysqldump -h $db_host -u $db_user -p$db_pass $db_name > $backup_file 2>&1";
exec($backup_command, $backup_output, $backup_return);

if ($backup_return === 0 && file_exists($backup_file)) {
    $backup_size = filesize($backup_file);
    echo "✅ 백업 완료: " . basename($backup_file) . " (" . number_format($backup_size / 1024, 2) . " KB)\n";
} else {
    echo "⚠️ 백업 실패 (계속 진행합니다):\n";
    echo implode("\n", $backup_output) . "\n";
}

// 4. 모든 테이블 삭제
echo "\n🗑️ 4단계: 기존 테이블 삭제...\n";
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

$tables_result = $mysqli->query("SHOW TABLES");
$table_count = 0;
while ($row = $tables_result->fetch_array()) {
    $table = $row[0];
    if ($mysqli->query("DROP TABLE IF EXISTS `$table`")) {
        echo "  ✓ 삭제: $table\n";
        $table_count++;
    }
}
echo "✅ $table_count 개 테이블 삭제 완료\n";

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

// 5. clean_webserver.sql 임포트
echo "\n📥 5단계: clean_webserver.sql 임포트...\n";

// MySQL 설정 최적화
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0"); // 외래 키 체크 비활성화
$mysqli->query("SET GLOBAL max_allowed_packet=67108864"); // 64MB
$mysqli->query("SET GLOBAL wait_timeout=300"); // 5분
$mysqli->query("SET GLOBAL interactive_timeout=300");
$mysqli->query("SET SESSION wait_timeout=300");
$mysqli->query("SET SESSION interactive_timeout=300");
$mysqli->query("SET autocommit=0");

echo "  ⚙️ MySQL 설정 최적화 완료\n";
echo "  ⚙️ 외래 키 체크 비활성화 (테이블 순서 문제 해결)\n";

// 파일을 청크 단위로 읽어서 처리
$queries = 0;
$errors = 0;
$current_query = '';
$in_delimiter = false;

$handle = fopen($sql_file, 'r');
if (!$handle) {
    die("❌ SQL 파일을 열 수 없습니다.\n");
}

echo "  📖 SQL 파일 읽기 시작...\n";
$line_count = 0;

while (($line = fgets($handle)) !== false) {
    $line_count++;

    // 진행 상황 표시 (10000줄마다)
    if ($line_count % 10000 === 0) {
        echo "  ... $line_count 줄 처리 중...\n";
        flush();
    }

    // 주석 및 빈 줄 건너뛰기
    $trimmed = trim($line);
    if (empty($trimmed) ||
        strpos($trimmed, '--') === 0 ||
        strpos($trimmed, '/*') === 0 ||
        strpos($trimmed, 'mysqldump:') === 0) {
        continue;
    }

    // DELIMITER 처리
    if (stripos($trimmed, 'DELIMITER') === 0) {
        $in_delimiter = !$in_delimiter;
        continue;
    }

    $current_query .= $line;

    // 쿼리 종료 감지 (세미콜론)
    if (!$in_delimiter && substr($trimmed, -1) === ';') {
        $current_query = trim($current_query);

        if (!empty($current_query)) {
            try {
                if ($mysqli->query($current_query)) {
                    $queries++;
                } else {
                    // 에러가 발생해도 계속 진행 (일부 무시 가능한 에러)
                    if (strpos($mysqli->error, 'already exists') === false &&
                        strpos($mysqli->error, 'Unknown table') === false) {
                        echo "  ⚠️ 쿼리 오류 (계속): " . substr($mysqli->error, 0, 100) . "...\n";
                        $errors++;
                    }
                }

                // 주기적으로 커밋 (1000 쿼리마다)
                if ($queries % 1000 === 0) {
                    $mysqli->commit();
                    $mysqli->begin_transaction();
                }

            } catch (Exception $e) {
                echo "  ⚠️ 예외 발생: " . substr($e->getMessage(), 0, 100) . "...\n";
                $errors++;
            }
        }

        $current_query = '';
    }
}

fclose($handle);

// 최종 커밋
$mysqli->commit();
$mysqli->query("SET autocommit=1");

// 외래 키 체크 다시 활성화
$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
echo "  ⚙️ 외래 키 체크 재활성화\n";

echo "✅ SQL 임포트 완료\n";
echo "  - 처리된 줄: " . number_format($line_count) . "\n";
echo "  - 실행된 쿼리: " . number_format($queries) . "\n";
echo "  - 오류 수: $errors\n";

// 6. 결과 확인
echo "\n📊 6단계: 결과 확인...\n";
$tables_result = $mysqli->query("SHOW TABLES");
$final_table_count = $tables_result->num_rows;
echo "✅ 최종 테이블 수: $final_table_count 개\n";

// 주요 테이블 확인
$check_tables = ['member', 'mlangorder_printauto', 'shop_temp', 'users'];
foreach ($check_tables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        $count_result = $mysqli->query("SELECT COUNT(*) as cnt FROM `$table`");
        $count = $count_result->fetch_assoc()['cnt'];
        echo "  ✓ $table: $count 행\n";
    } else {
        echo "  ⚠️ $table: 테이블 없음\n";
    }
}

// 7. 완료
echo "\n" . str_repeat("=", 50) . "\n";
if ($errors === 0) {
    echo "✅ 데이터베이스 복원 완료!\n";
    echo "\n📋 요약:\n";
    echo "  - 백업 파일: " . basename($backup_file) . "\n";
    echo "  - 삭제된 테이블: $table_count 개\n";
    echo "  - 새로 생성된 테이블: $final_table_count 개\n";
    echo "  - 실행된 쿼리: $queries 개\n";
    echo "\n⚠️ 보안: 이 파일(restore_database.php)을 즉시 삭제하세요!\n";
} else {
    echo "❌ 복원 중 오류 발생\n";
    echo "\n🔄 복원 방법:\n";
    echo "  mysql -u $db_user -p$db_pass $db_name < " . basename($backup_file) . "\n";
}

echo "</pre></body></html>";

$mysqli->close();
?>
