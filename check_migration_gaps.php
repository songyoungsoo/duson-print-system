<?php
/**
 * 마이그레이션 누락 분석
 */

include 'db.php';
$connect = $db;

echo "<h2>📊 마이그레이션 누락 분석</h2>";
echo "<pre>";

// 1. 전체 카운트 확인
$member_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM member"))['count'];
$users_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM users WHERE migrated_from_member = 1"))['count'];
$admin_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM users WHERE migrated_from_member IS NULL OR migrated_from_member = 0"))['count'];

echo "=== 전체 현황 ===\n";
echo "MEMBER 테이블: {$member_count}명\n";
echo "마이그레이션된 회원: {$users_count}명\n";
echo "관리자 계정: {$admin_count}명\n";
echo "누락된 회원: " . ($member_count - $users_count) . "명\n\n";

// 2. 중복 username 확인
echo "=== 중복 username 분석 ===\n";
$duplicate_check = mysqli_query($connect, "
    SELECT id, COUNT(*) as cnt 
    FROM member 
    GROUP BY id 
    HAVING COUNT(*) > 1
    ORDER BY cnt DESC
    LIMIT 10
");

if (mysqli_num_rows($duplicate_check) > 0) {
    echo "중복된 username:\n";
    while ($row = mysqli_fetch_assoc($duplicate_check)) {
        echo "- '{$row['id']}': {$row['cnt']}개\n";
    }
} else {
    echo "✅ MEMBER 테이블에 중복 username 없음\n";
}

// 3. 빈 필드 확인
echo "\n=== 필수 필드 누락 분석 ===\n";
$empty_fields = mysqli_query($connect, "
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN id = '' OR id IS NULL THEN 1 END) as empty_id,
        COUNT(CASE WHEN pass = '' OR pass IS NULL THEN 1 END) as empty_pass,
        COUNT(CASE WHEN name = '' OR name IS NULL THEN 1 END) as empty_name
    FROM member
");
$fields = mysqli_fetch_assoc($empty_fields);
echo "전체: {$fields['total']}명\n";
echo "ID 없음: {$fields['empty_id']}명\n";
echo "비밀번호 없음: {$fields['empty_pass']}명\n";
echo "이름 없음: {$fields['empty_name']}명\n";

// 4. 마이그레이션 실패한 회원들의 패턴 확인
echo "\n=== 실패한 회원들 샘플 (첫 10명) ===\n";
$failed_members = mysqli_query($connect, "
    SELECT m.no, m.id, m.name, m.email 
    FROM member m 
    LEFT JOIN users u ON m.id = u.username 
    WHERE u.username IS NULL 
    LIMIT 10
");

if (mysqli_num_rows($failed_members) > 0) {
    echo sprintf("%-5s %-15s %-15s %-25s\n", "No", "ID", "Name", "Email");
    echo str_repeat("-", 65) . "\n";
    while ($row = mysqli_fetch_assoc($failed_members)) {
        echo sprintf("%-5s %-15s %-15s %-25s\n", 
            $row['no'], 
            $row['id'] ?: '[EMPTY]', 
            $row['name'] ?: '[EMPTY]', 
            substr($row['email'] ?: '[EMPTY]', 0, 24)
        );
    }
}

echo "\n=== 권장 조치 ===\n";
if ($member_count - $users_count > 0) {
    echo "🔧 다시 마이그레이션을 실행하여 누락된 {누락수}명을 처리하세요\n";
    echo "📝 또는 실패 원인을 해결한 후 재실행하세요\n";
} else {
    echo "✅ 모든 회원이 성공적으로 마이그레이션되었습니다!\n";
}

echo "</pre>";

echo '<br><br>';
echo '<a href="import_and_migrate_members.php" style="background:#007cba;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px;">🔄 다시 마이그레이션 실행</a> ';
echo '<a href="index.php" style="background:#28a745;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;margin-left:10px;font-size:16px;">🏠 메인으로</a>';
?>