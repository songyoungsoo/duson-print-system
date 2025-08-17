<?php
include "db.php";

echo "<h2>📞 전화번호 구조 대응 마이그레이션</h2>";

// 1. users 테이블에 hendphone 필드 추가
echo "<h3>🔧 users 테이블 구조 업데이트:</h3>";

$add_hendphone = "ALTER TABLE users ADD COLUMN IF NOT EXISTS hendphone VARCHAR(20) DEFAULT NULL";
if (mysqli_query($db, $add_hendphone)) {
    echo "<p>✅ hendphone 필드 추가 완료</p>";
} else {
    echo "<p>⚠️ hendphone 필드: " . mysqli_error($db) . "</p>";
}

// 2. 필드 매핑 설명
echo "<h3>📋 전화번호 필드 매핑:</h3>";
echo "<ul>";
echo "<li><strong>phone1-phone2-phone3</strong> → <strong>users.phone</strong> (예: 010-1234-5678)</li>";
echo "<li><strong>hendphone1-hendphone2-hendphone3</strong> → <strong>users.hendphone</strong> (예: 031-123-4567)</li>";
echo "<li>빈 값은 NULL로 처리</li>";
echo "</ul>";

// 3. member 테이블 샘플 데이터 확인
echo "<h3>📊 member 테이블 전화번호 샘플:</h3>";
$sample_query = "SELECT id, name, phone1, phone2, phone3, hendphone1, hendphone2, hendphone3 FROM member LIMIT 5";
$sample_result = mysqli_query($db, $sample_query);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>이름</th><th>phone1</th><th>phone2</th><th>phone3</th><th>hendphone1</th><th>hendphone2</th><th>hendphone3</th><th>→ phone</th><th>→ hendphone</th></tr>";

while ($row = mysqli_fetch_assoc($sample_result)) {
    // phone 조합
    $phone_parts = array_filter([$row['phone1'], $row['phone2'], $row['phone3']]);
    $combined_phone = !empty($phone_parts) ? implode('-', $phone_parts) : null;
    
    // hendphone 조합
    $hendphone_parts = array_filter([$row['hendphone1'], $row['hendphone2'], $row['hendphone3']]);
    $combined_hendphone = !empty($hendphone_parts) ? implode('-', $hendphone_parts) : null;
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['phone1'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['phone2'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['phone3'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['hendphone1'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['hendphone2'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['hendphone3'] ?? '') . "</td>";
    echo "<td><strong>" . htmlspecialchars($combined_phone ?? 'NULL') . "</strong></td>";
    echo "<td><strong>" . htmlspecialchars($combined_hendphone ?? 'NULL') . "</strong></td>";
    echo "</tr>";
}
echo "</table>";

// 4. 실제 마이그레이션 실행
echo "<h3>🔄 마이그레이션 실행:</h3>";

$member_query = "SELECT * FROM member ORDER BY no";
$member_result = mysqli_query($db, $member_query);

if (!$member_result) {
    die("❌ member 테이블 조회 실패: " . mysqli_error($db));
}

$total_count = mysqli_num_rows($member_result);
echo "<p>📊 처리할 데이터: {$total_count}개</p>";

$migrated = 0;
$skipped = 0;
$errors = 0;

echo "<div style='max-height: 400px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;'>";

while ($member = mysqli_fetch_assoc($member_result)) {
    // 기본 필드 매핑
    $username = mysqli_real_escape_string($db, $member['id']);
    $old_password = $member['pass'] ?? '';
    $name = mysqli_real_escape_string($db, $member['name'] ?? '');
    
    // 전화번호 조합
    $phone_parts = array_filter([
        trim($member['phone1'] ?? ''), 
        trim($member['phone2'] ?? ''), 
        trim($member['phone3'] ?? '')
    ]);
    $combined_phone = !empty($phone_parts) ? implode('-', $phone_parts) : null;
    
    // hendphone 조합
    $hendphone_parts = array_filter([
        trim($member['hendphone1'] ?? ''), 
        trim($member['hendphone2'] ?? ''), 
        trim($member['hendphone3'] ?? '')
    ]);
    $combined_hendphone = !empty($hendphone_parts) ? implode('-', $hendphone_parts) : null;
    
    // 이스케이프 처리
    $phone_escaped = $combined_phone ? mysqli_real_escape_string($db, $combined_phone) : null;
    $hendphone_escaped = $combined_hendphone ? mysqli_real_escape_string($db, $combined_hendphone) : null;
    
    // 비밀번호 해시
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
        username, password, name, email, phone, hendphone,
        member_id, old_password, created_at
    ) VALUES (
        '$username', 
        '$hashed_password', 
        '$name', 
        NULL,
        " . ($phone_escaped ? "'$phone_escaped'" : "NULL") . ",
        " . ($hendphone_escaped ? "'$hendphone_escaped'" : "NULL") . ",
        '$username',
        '$old_password',
        NOW()
    )";
    
    if (mysqli_query($db, $insert_query)) {
        $phone_display = $combined_phone ?: 'NULL';
        $hendphone_display = $combined_hendphone ?: 'NULL';
        echo "<span style='color: green;'>✅ {$username} ({$name}) - phone: {$phone_display}, hendphone: {$hendphone_display}</span><br>";
        $migrated++;
    } else {
        echo "<span style='color: red;'>❌ 실패: {$username} - " . mysqli_error($db) . "</span><br>";
        $errors++;
    }
    
    // 진행상황 표시
    if (($migrated + $skipped + $errors) % 20 == 0) {
        echo "<strong style='background: #f0f0f0; padding: 2px;'>진행: " . ($migrated + $skipped + $errors) . "/{$total_count}</strong><br>";
        flush();
    }
}

echo "</div>";

// 결과 요약
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

// 샘플 결과 확인
echo "<h3>🔍 마이그레이션 결과 샘플:</h3>";
$result_sample = mysqli_query($db, "SELECT username, name, phone, hendphone FROM users WHERE member_id IS NOT NULL LIMIT 5");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Username</th><th>이름</th><th>Phone</th><th>Hendphone</th></tr>";
while ($row = mysqli_fetch_assoc($result_sample)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['phone'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['hendphone'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

mysqli_close($db);
?>