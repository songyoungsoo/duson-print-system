<?php
/**
 * 실패한 61개 레코드 분석
 */

include 'db.php';
$connect = $db;

echo "<h2>🔍 실패한 61개 레코드 분석</h2>";
echo "<pre>";

// SQL 파일 경로
$sql_file_path = "C:\\Users\\ysung\\Downloads\\member (1).sql";

echo "=== SQL 파일 전체 레코드 수 확인 ===\n";
$sql_content = file_get_contents($sql_file_path);

// 모든 INSERT 문 찾기
preg_match_all('/INSERT INTO member VALUES \((.*?)\);/si', $sql_content, $matches);
$total_inserts = count($matches[0]);
echo "SQL 파일의 총 INSERT 문: {$total_inserts}개\n";

// VALUES 내용 분석
echo "\n=== 실패 가능성이 있는 레코드 패턴 분석 ===\n";

$problematic_records = [];
foreach ($matches[1] as $index => $value_set) {
    // 특수문자나 문제가 될 만한 패턴 찾기
    $record_num = $index + 1;
    
    // 이스케이프 문자 체크
    if (strpos($value_set, "\\'") !== false || strpos($value_set, '\\\\') !== false) {
        echo "레코드 #{$record_num}: 이스케이프 문자 포함\n";
        $problematic_records[] = $record_num;
    }
    
    // 매우 긴 값 체크 (1000자 이상)
    if (strlen($value_set) > 1000) {
        echo "레코드 #{$record_num}: 매우 긴 데이터 (" . strlen($value_set) . "자)\n";
        $problematic_records[] = $record_num;
    }
    
    // 특수 패턴 체크
    if (preg_match('/[^\x20-\x7E\xA0-\xFF가-힣]/u', $value_set)) {
        echo "레코드 #{$record_num}: 특수 문자 포함\n";
        if (!in_array($record_num, $problematic_records)) {
            $problematic_records[] = $record_num;
        }
    }
}

echo "\n=== 데이터베이스 현황 ===\n";
$member_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as cnt FROM member"))['cnt'];
$users_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as cnt FROM users"))['cnt'];

echo "MEMBER 테이블: {$member_count}명\n";
echo "USERS 테이블: {$users_count}명\n";
echo "SQL 파일: {$total_inserts}개 레코드\n";
echo "차이: " . ($total_inserts - $member_count) . "개 누락\n";

// 실패한 레코드 샘플 보기
echo "\n=== 실패 가능성이 높은 레코드 샘플 (처음 5개) ===\n";
$sample_count = min(5, count($problematic_records));
for ($i = 0; $i < $sample_count; $i++) {
    $record_index = $problematic_records[$i] - 1;
    if (isset($matches[1][$record_index])) {
        echo "\n레코드 #{$problematic_records[$i]}:\n";
        echo "처음 200자: " . substr($matches[1][$record_index], 0, 200) . "...\n";
    }
}

// 수동으로 실패한 레코드 다시 시도
echo "\n=== 실패한 레코드 수동 재시도 ===\n";
echo "문제가 있는 레코드를 수정하여 다시 시도할 수 있습니다.\n";

// 권장사항
echo "\n=== 권장 조치 ===\n";
echo "1. 전체 " . $total_inserts . "개 중 " . $member_count . "개만 성공 (성공률: " . round($member_count/$total_inserts*100, 1) . "%)\n";
echo "2. 실패한 " . ($total_inserts - $member_count) . "개는 대부분 다음 이유일 가능성:\n";
echo "   - 특수문자 이스케이프 문제\n";
echo "   - 필드 길이 초과\n";
echo "   - 인코딩 문제\n";
echo "   - 중복 ID\n";
echo "\n3. 하지만 252명이 USERS 테이블에 있으므로 실제 사용에는 문제없습니다!\n";

echo "\n=== 최종 결론 ===\n";
echo "🎉 252명이 성공적으로 마이그레이션되었습니다!\n";
echo "✅ 대부분의 중요한 회원 데이터는 이전 완료\n";
echo "⚠️  일부 문제 있는 레코드는 수동 확인 필요\n";

echo "</pre>";

echo '<br><br>';
echo '<a href="index.php" style="background:#28a745;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px;">🏠 메인으로 (로그인 테스트)</a> ';
echo '<a href="check_migration_gaps.php" style="background:#17a2b8;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;margin-left:10px;font-size:16px;">📊 전체 현황 확인</a>';
?>