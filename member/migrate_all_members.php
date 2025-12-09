<?php
/**
 * 일괄 회원 마이그레이션
 * member 테이블의 모든 회원을 users 테이블로 이전
 */

include "../db.php";

echo "<h2>회원 마이그레이션 시작</h2>";
echo "<pre>";

// 1. member 테이블에서 모든 회원 조회
$member_query = "SELECT * FROM member ORDER BY id";
$member_result = mysqli_query($db, $member_query);

if (!$member_result) {
    die("member 테이블 조회 실패: " . mysqli_error($db));
}

$total = mysqli_num_rows($member_result);
echo "총 {$total}명 회원 발견\n\n";

$migrated = 0;
$skipped = 0;
$errors = 0;

while ($member = mysqli_fetch_assoc($member_result)) {
    $username = $member['id'];

    // 2. users 테이블에 이미 존재하는지 확인
    $check_query = "SELECT id FROM users WHERE username = ?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_fetch_assoc($check_result)) {
        echo "⏭️  SKIP: {$username} (이미 존재)\n";
        $skipped++;
        mysqli_stmt_close($check_stmt);
        continue;
    }
    mysqli_stmt_close($check_stmt);

    // 3. users 테이블에 등록
    $hashed_password = password_hash($member['pass'], PASSWORD_DEFAULT);

    $insert_query = "INSERT INTO users (username, password, old_password, name, email, phone,
                                       login_count, last_login, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $insert_stmt = mysqli_prepare($db, $insert_query);

    $login_count = $member['Logincount'] ?? 0;
    $last_login = $member['EndLogin'] ?? date("Y-m-d H:i:s");

    mysqli_stmt_bind_param($insert_stmt, "ssssssss",
        $username,
        $hashed_password,
        $member['pass'],           // old_password: 레거시 평문 비밀번호 보관
        $member['name'],
        $member['email'],
        $member['phone'],
        $login_count,
        $last_login
    );

    if (mysqli_stmt_execute($insert_stmt)) {
        echo "✅ {$username} → users (이름: {$member['name']})\n";
        $migrated++;
    } else {
        echo "❌ ERROR: {$username} - " . mysqli_error($db) . "\n";
        $errors++;
    }

    mysqli_stmt_close($insert_stmt);
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "마이그레이션 완료!\n";
echo "✅ 성공: {$migrated}명\n";
echo "⏭️  건너뜀: {$skipped}명 (이미 존재)\n";
echo "❌ 실패: {$errors}명\n";
echo "📊 총: {$total}명\n";
echo "</pre>";

mysqli_close($db);
?>
