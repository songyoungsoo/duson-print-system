<?php
include "db.php";

echo "🔄 빠른 마이그레이션 시작...\n\n";

// member 테이블에서 users로 데이터 복사
$member_query = "SELECT * FROM member ORDER BY id";
$member_result = mysqli_query($db, $member_query);

$migrated = 0;
$errors = 0;

while ($member = mysqli_fetch_assoc($member_result)) {
    $member_id = mysqli_real_escape_string($db, $member['id']);
    $member_name = mysqli_real_escape_string($db, $member['name'] ?? '');
    $member_email = mysqli_real_escape_string($db, $member['email'] ?? '');
    $member_phone = mysqli_real_escape_string($db, $member['phone'] ?? '');
    $old_password = mysqli_real_escape_string($db, $member['pass'] ?? '');
    $login_count = intval($member['Logincount'] ?? 0);
    $last_login = $member['EndLogin'] ?? null;
    
    // 새 비밀번호 해시
    $new_password = !empty($old_password) ? $old_password : '123456';
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // 중복 확인
    $check_query = "SELECT id FROM users WHERE username = '$member_id' OR member_id = '$member_id'";
    $check_result = mysqli_query($db, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        continue; // 이미 존재
    }
    
    // 삽입
    $insert_query = "INSERT INTO users (
        username, password, name, email, phone, 
        member_id, old_password, login_count, last_login
    ) VALUES (
        '$member_id', '$hashed_password', '$member_name', '$member_email', '$member_phone',
        '$member_id', '$old_password', '$login_count', " . ($last_login ? "'$last_login'" : "NULL") . "
    )";
    
    if (mysqli_query($db, $insert_query)) {
        $migrated++;
        echo "✅ $member_id ($member_name)\n";
    } else {
        $errors++;
        echo "❌ $member_id: " . mysqli_error($db) . "\n";
    }
}

echo "\n📊 마이그레이션 완료:\n";
echo "✅ 성공: $migrated개\n";
echo "❌ 실패: $errors개\n";

mysqli_close($db);
?>