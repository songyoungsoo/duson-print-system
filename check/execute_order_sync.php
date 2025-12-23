<?php
/**
 * 웹에서 실행할 mlangorder_printauto 80173번 이후 동기화 스크립트
 * 프로덕션 서버에서 직접 실행
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h2>프로덕션 서버 mlangorder_printauto 테이블 동기화</h2>";
echo "<pre>";

// 데이터베이스 연결
include 'db.php';

if (!$db) {
    die("❌ 데이터베이스 연결 실패\n");
}

echo "✅ 데이터베이스 연결 성공\n\n";

// SQL 모드 설정
mysqli_query($db, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

// 실행 확인
$confirm = $_GET['confirm'] ?? '';
if ($confirm !== 'yes') {
    echo "⚠️ 경고: 이 작업은 mlangorder_printauto 테이블의 80173번 이후 데이터를 교체합니다!\n\n";
    echo "백업이 자동으로 생성되지만, 신중하게 진행하세요.\n\n";
    echo "실행하려면 URL에 ?confirm=yes를 추가하세요.\n";
    echo "예: " . $_SERVER['PHP_SELF'] . "?confirm=yes\n";
    exit;
}

echo "=== 동기화 시작 ===\n\n";

$backup_date = date('Ymd_His');

// 1. 백업 생성
echo "1. 백업 생성 중...\n";

$result = mysqli_query($db, "CREATE TABLE IF NOT EXISTS mlangorder_printauto_backup_$backup_date AS SELECT * FROM mlangorder_printauto WHERE no > 80173");
if ($result) {
    $count = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto_backup_$backup_date");
    $row = mysqli_fetch_assoc($count);
    echo "   ✅ 80173 이후 데이터 백업: mlangorder_printauto_backup_$backup_date ({$row['cnt']}개)\n";
}

// 2. 현재 상태 확인
echo "\n2. 현재 데이터 확인:\n";
$result = mysqli_query($db, "SELECT COUNT(*) as total, MAX(no) as max_no FROM mlangorder_printauto");
$row = mysqli_fetch_assoc($result);
echo "   📊 현재 레코드: {$row['total']}개\n";
echo "   📊 최대 번호: {$row['max_no']}\n";

// 3. 80173 이후 데이터 삭제
echo "\n3. 80173 이후 기존 데이터 삭제 중...\n";
$result = mysqli_query($db, "DELETE FROM mlangorder_printauto WHERE no > 80173");
if ($result) {
    $affected = mysqli_affected_rows($db);
    echo "   ✅ 삭제 완료: $affected개 레코드\n";
}

// 4. SQL 파일 실행
echo "\n4. 새 데이터 삽입 중...\n";

$sql_file = __DIR__ . '/sql251109/mlangorder_after_80173_fixed.sql';
if (!file_exists($sql_file)) {
    die("   ❌ SQL 파일 없음: $sql_file\n");
}

$sql_content = file_get_contents($sql_file);
$lines = explode("\n", $sql_content);
$insert_count = 0;
$error_count = 0;
$datetime_errors = 0;

foreach ($lines as $line_num => $line) {
    $line = trim($line);

    // INSERT 문만 실행
    if (strpos($line, 'INSERT INTO mlangorder_printauto VALUES') === 0) {
        if (mysqli_query($db, $line)) {
            $insert_count++;
            if ($insert_count % 500 == 0) {
                echo "   진행 중: $insert_count개 삽입...\n";
            }
        } else {
            $error = mysqli_error($db);
            $error_count++;

            // datetime 에러는 카운트만
            if (strpos($error, 'Incorrect datetime value') !== false) {
                $datetime_errors++;
            } else if ($error_count <= 5) {
                echo "   ⚠️ INSERT 실패 (라인 $line_num): " . substr($line, 0, 80) . "...\n";
                echo "      에러: $error\n";
            }
        }
    }
}

echo "   ✅ 데이터 삽입 완료: $insert_count개 성공\n";
if ($error_count > 0) {
    echo "   ⚠️ 실패: $error_count개 (datetime 형식 오류: $datetime_errors개)\n";
}

// 5. 검증
echo "\n5. 최종 데이터 확인:\n";
$result = mysqli_query($db, "SELECT COUNT(*) as total, MIN(no) as min_no, MAX(no) as max_no FROM mlangorder_printauto");
$row = mysqli_fetch_assoc($result);
echo "   📊 전체 레코드: {$row['total']}개\n";
echo "   📊 최소 번호: {$row['min_no']}\n";
echo "   📊 최대 번호: {$row['max_no']}\n";

$result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE no > 80173");
$row = mysqli_fetch_assoc($result);
echo "   📊 80173 이후: {$row['cnt']}개\n";

// 샘플 데이터
echo "\n6. 샘플 데이터 (최근 5개):\n";
$result = mysqli_query($db, "SELECT no, Type, name, date FROM mlangorder_printauto WHERE no > 80173 ORDER BY no DESC LIMIT 5");
echo "   <table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "   <tr><th>번호</th><th>제품</th><th>이름</th><th>날짜</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "   <tr><td>{$row['no']}</td><td>{$row['Type']}</td><td>{$row['name']}</td><td>{$row['date']}</td></tr>";
}
echo "   </table>";

mysqli_close($db);

echo "\n\n✅ 동기화 완료!\n";
echo "\n백업 테이블:\n";
echo "  - mlangorder_printauto_backup_$backup_date\n";
echo "</pre>";
?>
