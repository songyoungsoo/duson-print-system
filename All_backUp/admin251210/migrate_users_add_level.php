<?php
/**
 * Users 테이블에 level 컬럼 추가 마이그레이션
 * 실행 후 삭제 권장
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

echo "<h2>🔧 Users 테이블 마이그레이션</h2>";
echo "<hr>";

// 1. 현재 테이블 구조 확인
echo "<h3>1. 현재 테이블 구조 확인</h3>";
$check_query = "DESCRIBE users";
$result = mysqli_query($db, $check_query);

if (!$result) {
    die("❌ 테이블 확인 실패: " . mysqli_error($db));
}

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>컬럼명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";

$has_level = false;
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";

    if ($row['Field'] === 'level') {
        $has_level = true;
    }
}
echo "</table>";

// 2. level 컬럼 추가 (없는 경우만)
echo "<br><h3>2. level 컬럼 추가</h3>";

if ($has_level) {
    echo "✅ level 컬럼이 이미 존재합니다. 스킵합니다.<br>";
} else {
    $alter_query = "ALTER TABLE users ADD COLUMN level TINYINT DEFAULT 5 COMMENT '회원등급: 5=일반회원(기본값), 1=관리자' AFTER name";

    if (mysqli_query($db, $alter_query)) {
        echo "✅ level 컬럼이 성공적으로 추가되었습니다.<br>";
    } else {
        die("❌ level 컬럼 추가 실패: " . mysqli_error($db));
    }
}

// 3. admin 계정의 level 업데이트
echo "<br><h3>3. admin 계정 권한 설정</h3>";

$update_admin_query = "UPDATE users SET level = 1 WHERE username = 'admin'";
if (mysqli_query($db, $update_admin_query)) {
    $affected = mysqli_affected_rows($db);
    if ($affected > 0) {
        echo "✅ admin 계정의 level이 1로 업데이트되었습니다. (영향받은 행: {$affected})<br>";
    } else {
        echo "⚠️ admin 계정이 없거나 이미 level=1 입니다.<br>";
    }
} else {
    echo "❌ admin 계정 업데이트 실패: " . mysqli_error($db) . "<br>";
}

// 4. 최종 결과 확인
echo "<br><h3>4. 마이그레이션 후 테이블 구조</h3>";
$result = mysqli_query($db, "DESCRIBE users");

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>컬럼명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td><strong>{$row['Field']}</strong></td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// 5. admin 계정 정보 확인
echo "<br><h3>5. admin 계정 정보</h3>";
$admin_check = mysqli_query($db, "SELECT id, username, name, level, created_at FROM users WHERE username = 'admin'");

if ($admin = mysqli_fetch_assoc($admin_check)) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Level</th><th>Created</th></tr>";
    echo "<tr>";
    echo "<td>{$admin['id']}</td>";
    echo "<td>{$admin['username']}</td>";
    echo "<td>{$admin['name']}</td>";
    echo "<td><strong style='color: red;'>{$admin['level']}</strong></td>";
    echo "<td>{$admin['created_at']}</td>";
    echo "</tr>";
    echo "</table>";
} else {
    echo "⚠️ admin 계정이 존재하지 않습니다.<br>";
}

echo "<br><hr>";
echo "<h3>✅ 마이그레이션 완료!</h3>";
echo "<p>이제 <code>level</code> 컬럼이 추가되었고, admin 계정은 level=1로 설정되었습니다.</p>";
echo "<p><strong>권한 레벨:</strong></p>";
echo "<ul>";
echo "<li><strong>5 = 일반회원 (기본값)</strong></li>";
echo "<li><strong>1 = 관리자</strong> (공지사항 관리 권한)</li>";
echo "</ul>";

echo "<br><p style='color: #999;'><em>⚠️ 이 파일은 보안을 위해 실행 후 삭제하는 것을 권장합니다.</em></p>";

mysqli_close($db);
?>
