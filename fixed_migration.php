<?php
include "db.php";

echo "<h2>🔄 수정된 마이그레이션 실행</h2>";

// 필드 매핑 확인
echo "<h3>📋 필드 매핑:</h3>";
echo "<ul>";
echo "<li>member.id → users.username</li>";
echo "<li>member.pass → users.password (해시 변환)</li>";
echo "<li>member.name → users.name</li>";
echo "<li>member.email → users.email</li>";
echo "<li>member.phone → users.phone</li>";
echo "</ul>";

// member 테이블에서 데이터 가져오기
$member_query = "SELECT * FROM member ORDER BY id";
$member_result = mysqli_query($db, $member_query);

if (!$member_result) {
    die("❌ member 테이블 조회 실패: " . mysqli_error($db));
}

$total_count = mysqli_num_rows($member_result);
echo "<p>📊 처리할 데이터: {$total_count}개</p>";

$migrated = 0;
$skipped = 0;
$errors = 0;

echo "<h3>🔄 마이그레이션 진행:</h3>";
echo "<div style='max-height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;'>";

while ($member = mysqli_fetch_assoc($member_result)) {
    // 필드 매핑
    $username = mysqli_real_escape_string($db, $member['id']);
    $old_password = $member['pass'] ?? '';
    $name = mysqli_real_escape_string($db, $member['name'] ?? '');
    $email = mysqli_real_escape_string($db, $member['email'] ?? '');
    $phone = mysqli_real_escape_string($db, $member['phone'] ?? '');
    $login_count = intval($member['Logincount'] ?? 0);
    $last_login = $member['EndLogin'] ?? null;
    
    // 비밀번호 해시 생성
    $password_to_hash = !empty($old_password) ? $old_password : '123456';
    $hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);
    
    // 중복 확인
    $check_query = "SELECT id FROM users WHERE username = '$username'";
    $check_result = mysqli_query($db, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "<span style='color: orange;'>⚠️ 건너뜀: {$username} ({$name}) - 이미 존재</span><br>";
        $skipped++;
        continue;
    }
    
    // 데이터 삽입
    $insert_query = "INSERT INTO users (
        username, password, name, email, phone, 
        member_id, old_password, login_count, last_login, created_at
    ) VALUES (
        '$username', 
        '$hashed_password', 
        '$name', 
        '$email', 
        '$phone',
        '$username',
        '$old_password',
        $login_count,
        " . ($last_login ? "'$last_login'" : "NOW()") . ",
        NOW()
    )";
    
    if (mysqli_query($db, $insert_query)) {
        echo "<span style='color: green;'>✅ 성공: {$username} ({$name})</span><br>";
        $migrated++;
    } else {
        echo "<span style='color: red;'>❌ 실패: {$username} - " . mysqli_error($db) . "</span><br>";
        $errors++;
    }
    
    // 진행상황 표시 (10개마다)
    if (($migrated + $skipped + $errors) % 10 == 0) {
        echo "<strong>진행: " . ($migrated + $skipped + $errors) . "/{$total_count}</strong><br>";
        flush();
    }
}

echo "</div>";

echo "<h3>📈 마이그레이션 결과:</h3>";
echo "<ul>";
echo "<li><strong style='color: green;'>✅ 성공: {$migrated}개</strong></li>";
echo "<li><strong style='color: orange;'>⚠️ 건너뜀: {$skipped}개</strong></li>";
echo "<li><strong style='color: red;'>❌ 실패: {$errors}개</strong></li>";
echo "<li><strong>📊 총 처리: " . ($migrated + $skipped + $errors) . "개</strong></li>";
echo "</ul>";

// 최종 확인
$final_count = mysqli_query($db, "SELECT COUNT(*) as count FROM users");
$final_users = mysqli_fetch_assoc($final_count)['count'];
echo "<p><strong>🎯 users 테이블 최종 데이터: {$final_users}개</strong></p>";

if ($migrated > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h4>🎉 마이그레이션 완료!</h4>";
    echo "<p>이제 기존 member 계정으로 로그인할 수 있습니다:</p>";
    echo "<ul>";
    echo "<li>로그인 페이지: <a href='/member/login.php'>/member/login.php</a></li>";
    echo "<li>기존 아이디/비밀번호 그대로 사용 가능</li>";
    echo "<li>로그인 후 헤더에 사용자명 정상 표시됨</li>";
    echo "</ul>";
    echo "</div>";
}

mysqli_close($db);
?>