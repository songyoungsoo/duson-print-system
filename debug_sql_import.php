<?php
/**
 * SQL 파일 가져오기 디버깅
 */

include 'db.php';
$connect = $db;

echo "<h2>🔍 SQL 파일 가져오기 디버깅</h2>";
echo "<pre>";

// SQL 파일 경로
$sql_file_path = "C:\\Users\\ysung\\Downloads\\member (1).sql";

// Step 1: SQL 파일 분석
echo "=== SQL 파일 분석 ===\n";

if (!file_exists($sql_file_path)) {
    echo "❌ SQL 파일을 찾을 수 없습니다: {$sql_file_path}\n";
    exit;
}

$sql_content = file_get_contents($sql_file_path);
echo "✅ SQL 파일 크기: " . number_format(strlen($sql_content)) . " bytes\n";

// 다양한 INSERT 패턴 찾기
echo "\n=== INSERT 문 패턴 분석 ===\n";

// 패턴 1: INSERT INTO member VALUES
preg_match_all('/INSERT INTO member VALUES \([^;]+\);/si', $sql_content, $matches1);
echo "패턴 1 (INSERT INTO member VALUES): " . count($matches1[0]) . "개\n";

// 패턴 2: INSERT INTO `member` VALUES
preg_match_all('/INSERT INTO `member` VALUES \([^;]+\);/si', $sql_content, $matches2);
echo "패턴 2 (INSERT INTO `member` VALUES): " . count($matches2[0]) . "개\n";

// 패턴 3: 멀티라인 INSERT
preg_match_all('/INSERT INTO .*?member.*? VALUES.*?\(.*?\);/si', $sql_content, $matches3);
echo "패턴 3 (멀티라인 INSERT): " . count($matches3[0]) . "개\n";

// 실제 VALUES 개수 세기
preg_match_all('/VALUES\s*\([^)]+\)/si', $sql_content, $values_matches);
echo "전체 VALUES 절 개수: " . count($values_matches[0]) . "개\n";

// Step 2: 실제 데이터 확인
echo "\n=== 첫 5개 INSERT 문 확인 ===\n";
$all_inserts = array_merge($matches1[0], $matches2[0]);
for ($i = 0; $i < min(5, count($all_inserts)); $i++) {
    $stmt = $all_inserts[$i];
    echo "\n--- INSERT #{$i} (처음 200자) ---\n";
    echo substr($stmt, 0, 200) . "...\n";
    
    // 필드 개수 확인
    preg_match('/VALUES\s*\(([^)]+)\)/i', $stmt, $value_match);
    if (isset($value_match[1])) {
        $fields = explode(',', $value_match[1]);
        echo "필드 개수: " . count($fields) . "개\n";
    }
}

// Step 3: MEMBER 테이블 구조 확인
echo "\n=== MEMBER 테이블 구조 ===\n";
$columns = mysqli_query($connect, "SHOW COLUMNS FROM member");
$column_count = mysqli_num_rows($columns);
echo "테이블 컬럼 개수: {$column_count}개\n";
echo "컬럼 목록:\n";
$col_names = [];
while ($col = mysqli_fetch_assoc($columns)) {
    $col_names[] = $col['Field'];
    echo "- {$col['Field']} ({$col['Type']})\n";
}

// Step 4: 하나씩 실행해보기
echo "\n=== INSERT 문 실행 테스트 ===\n";
$test_count = min(10, count($all_inserts));
$success = 0;
$fail = 0;

for ($i = 0; $i < $test_count; $i++) {
    $stmt = $all_inserts[$i];
    
    // 테스트용 임시 테이블에서 실행
    $test_stmt = str_replace('member', 'member_test', $stmt);
    
    // 원본 테이블에서 실행
    if (mysqli_query($connect, $stmt)) {
        $success++;
        echo "✅ INSERT #{$i}: 성공\n";
    } else {
        $fail++;
        echo "❌ INSERT #{$i}: 실패 - " . mysqli_error($connect) . "\n";
        
        // 실패한 문장 일부 출력
        echo "   실패한 SQL (처음 100자): " . substr($stmt, 0, 100) . "...\n";
    }
}

echo "\n=== 실행 결과 ===\n";
echo "성공: {$success}개\n";
echo "실패: {$fail}개\n";

// Step 5: 현재 MEMBER 테이블 상태
echo "\n=== 현재 MEMBER 테이블 상태 ===\n";
$count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as cnt FROM member"))['cnt'];
echo "현재 MEMBER 테이블 레코드 수: {$count}개\n";

// 가능한 문제들
echo "\n=== 가능한 문제 원인 ===\n";
if (count($all_inserts) < 200) {
    echo "⚠️  INSERT 문이 예상보다 적습니다. SQL 파일 형식 확인 필요\n";
}
if ($column_count != count($fields)) {
    echo "⚠️  테이블 컬럼 수와 INSERT 값 개수가 다를 수 있습니다\n";
}
if ($fail > 0) {
    echo "⚠️  일부 INSERT 문이 실패했습니다. 에러 메시지 확인 필요\n";
}

echo "</pre>";

echo '<br><br>';
echo '<a href="import_and_migrate_members.php" style="background:#007cba;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px;">🔄 마이그레이션 재시도</a> ';
echo '<a href="check_migration_gaps.php" style="background:#17a2b8;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;margin-left:10px;font-size:16px;">📊 누락 분석</a>';
?>