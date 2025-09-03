<?php
include 'db.php';

echo "<h2>📊 전체 회원 데이터 분석</h2>";
echo "<pre>";

// 전체 회원 수 확인
$total_count = mysqli_query($db, "SELECT COUNT(*) as total FROM member");
$total = mysqli_fetch_assoc($total_count)['total'];
echo "=== 전체 회원 수: {$total}명 ===\n\n";

if ($total > 5) {
    echo "✅ 예상대로 {$total}명의 회원이 있습니다!\n";
} else {
    echo "⚠️  예상보다 적은 {$total}명만 있습니다.\n";
}

// 회원 등급별 분포
echo "\n=== 회원 등급별 분포 ===\n";
$level_stats = mysqli_query($db, "SELECT level, COUNT(*) as count FROM member GROUP BY level ORDER BY level");
while ($row = mysqli_fetch_assoc($level_stats)) {
    echo "레벨 {$row['level']}: {$row['count']}명\n";
}

// 최근 가입 회원 (상위 10명)
echo "\n=== 최근 가입 회원 (상위 10명) ===\n";
$recent_members = mysqli_query($db, "SELECT no, id, name, email, date FROM member ORDER BY no DESC LIMIT 10");
echo sprintf("%-5s %-15s %-15s %-25s %-20s\n", "번호", "ID", "이름", "이메일", "가입일");
echo str_repeat("-", 80) . "\n";
while ($row = mysqli_fetch_assoc($recent_members)) {
    echo sprintf("%-5s %-15s %-15s %-25s %-20s\n", 
        $row['no'], 
        $row['id'], 
        $row['name'], 
        substr($row['email'], 0, 24),
        $row['date']
    );
}

// 오래된 회원 (하위 10명)  
echo "\n=== 가장 오래된 회원 (하위 10명) ===\n";
$old_members = mysqli_query($db, "SELECT no, id, name, email, date FROM member ORDER BY no ASC LIMIT 10");
echo sprintf("%-5s %-15s %-15s %-25s %-20s\n", "번호", "ID", "이름", "이메일", "가입일");
echo str_repeat("-", 80) . "\n";
while ($row = mysqli_fetch_assoc($old_members)) {
    echo sprintf("%-5s %-15s %-15s %-25s %-20s\n", 
        $row['no'], 
        $row['id'], 
        $row['name'], 
        substr($row['email'], 0, 24),
        $row['date']
    );
}

// 활동 회원 분석
echo "\n=== 활동 회원 분석 ===\n";
$active_stats = mysqli_query($db, "
    SELECT 
        COUNT(*) as total_members,
        COUNT(CASE WHEN Logincount > 0 THEN 1 END) as logged_in_members,
        COUNT(CASE WHEN EndLogin > '2024-01-01' THEN 1 END) as recent_login_2024,
        COUNT(CASE WHEN EndLogin > '2025-01-01' THEN 1 END) as recent_login_2025,
        AVG(Logincount) as avg_login_count
    FROM member
");
$stats = mysqli_fetch_assoc($active_stats);
echo "전체 회원: {$stats['total_members']}명\n";
echo "로그인 경험 있는 회원: {$stats['logged_in_members']}명\n";
echo "2024년 이후 로그인: {$stats['recent_login_2024']}명\n";
echo "2025년 이후 로그인: {$stats['recent_login_2025']}명\n";
echo "평균 로그인 횟수: " . round($stats['avg_login_count'], 2) . "회\n";

// 이메일 도메인 분석
echo "\n=== 이메일 도메인 분석 (상위 10개) ===\n";
$domain_stats = mysqli_query($db, "
    SELECT 
        SUBSTRING(email, LOCATE('@', email) + 1) as domain,
        COUNT(*) as count 
    FROM member 
    WHERE email LIKE '%@%' 
    GROUP BY SUBSTRING(email, LOCATE('@', email) + 1) 
    ORDER BY count DESC 
    LIMIT 10
");
while ($row = mysqli_fetch_assoc($domain_stats)) {
    echo "{$row['domain']}: {$row['count']}명\n";
}

// 데이터 무결성 체크
echo "\n=== 데이터 무결성 체크 ===\n";
$integrity_check = mysqli_query($db, "
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN id = '' OR id IS NULL THEN 1 END) as empty_id,
        COUNT(CASE WHEN pass = '' OR pass IS NULL THEN 1 END) as empty_password,
        COUNT(CASE WHEN name = '' OR name IS NULL THEN 1 END) as empty_name,
        COUNT(CASE WHEN email = '' OR email IS NULL THEN 1 END) as empty_email
    FROM member
");
$integrity = mysqli_fetch_assoc($integrity_check);
echo "전체: {$integrity['total']}명\n";
echo "ID 없음: {$integrity['empty_id']}명\n";
echo "비밀번호 없음: {$integrity['empty_password']}명\n"; 
echo "이름 없음: {$integrity['empty_name']}명\n";
echo "이메일 없음: {$integrity['empty_email']}명\n";

echo "\n=== 분석 완료 ===\n";
echo "실제 마이그레이션 시 {$total}명 모든 회원이 이전됩니다.\n";

echo "</pre>";

echo '<br><br>';
echo '<a href="migrate_member_to_users.php" style="background:#007cba;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px;">🚀 ' . $total . '명 회원 마이그레이션 실행</a> ';
echo '<a href="index.php" style="background:#28a745;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;margin-left:10px;font-size:16px;">메인으로</a>';
?>