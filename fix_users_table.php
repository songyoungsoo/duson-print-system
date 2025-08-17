<?php
include "db.php";

echo "<h2>🔧 users 테이블 구조 수정</h2>";

// 1. 현재 users 테이블 구조 확인
echo "<h3>📋 현재 users 테이블 구조:</h3>";
$desc_result = mysqli_query($db, "DESCRIBE users");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>필드명</th><th>타입</th><th>NULL</th><th>키</th><th>기본값</th></tr>";
while ($row = mysqli_fetch_assoc($desc_result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 2. 필요한 필드들 추가
echo "<h3>🔨 필요한 필드 추가:</h3>";

$fields_to_add = [
    'member_id' => "ALTER TABLE users ADD COLUMN member_id VARCHAR(50) DEFAULT NULL",
    'old_password' => "ALTER TABLE users ADD COLUMN old_password VARCHAR(50) DEFAULT NULL", 
    'login_count' => "ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0",
    'last_login' => "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL",
    'hendphone' => "ALTER TABLE users ADD COLUMN hendphone VARCHAR(20) DEFAULT NULL"
];

foreach ($fields_to_add as $field_name => $query) {
    // 필드 존재 여부 확인
    $check_field = mysqli_query($db, "SHOW COLUMNS FROM users LIKE '$field_name'");
    
    if (mysqli_num_rows($check_field) == 0) {
        // 필드가 없으면 추가
        if (mysqli_query($db, $query)) {
            echo "<p>✅ <strong>$field_name</strong> 필드 추가 완료</p>";
        } else {
            echo "<p>❌ <strong>$field_name</strong> 필드 추가 실패: " . mysqli_error($db) . "</p>";
        }
    } else {
        echo "<p>ℹ️ <strong>$field_name</strong> 필드 이미 존재</p>";
    }
}

// 3. 업데이트된 테이블 구조 확인
echo "<h3>📋 업데이트된 users 테이블 구조:</h3>";
$updated_desc = mysqli_query($db, "DESCRIBE users");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>필드명</th><th>타입</th><th>NULL</th><th>키</th><th>기본값</th></tr>";
while ($row = mysqli_fetch_assoc($updated_desc)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
echo "<h4>✅ users 테이블 구조 수정 완료!</h4>";
echo "<p>이제 다시 마이그레이션을 실행할 수 있습니다:</p>";
echo "<p><a href='phone_migration.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>📞 전화번호 마이그레이션 실행</a></p>";
echo "</div>";

mysqli_close($db);
?>