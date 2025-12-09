<?php
session_start();
include "../db.php";

echo "<h2>로그인 디버그 테스트</h2>";

// 테스트할 계정 정보
$test_id = 'admin';
$test_pass = 'du701018';

echo "<h3>1. member 테이블 조회</h3>";
$member_query = "SELECT * FROM member WHERE id = ?";
$member_stmt = mysqli_prepare($db, $member_query);
mysqli_stmt_bind_param($member_stmt, "s", $test_id);
mysqli_stmt_execute($member_stmt);
$member_result = mysqli_stmt_get_result($member_stmt);

if ($member = mysqli_fetch_assoc($member_result)) {
    echo "✅ member 테이블에서 계정 찾음<br>";
    echo "ID: " . htmlspecialchars($member['id']) . "<br>";
    echo "Name: " . htmlspecialchars($member['name']) . "<br>";
    echo "DB Password: " . htmlspecialchars($member['pass']) . "<br>";
    echo "Input Password: " . htmlspecialchars($test_pass) . "<br>";
    echo "Password Match: " . ($test_pass === $member['pass'] ? '✅ YES' : '❌ NO') . "<br>";
    echo "<br>";

    // 바이트 단위 비교
    echo "<h4>바이트 단위 비교:</h4>";
    echo "DB Password Length: " . strlen($member['pass']) . " bytes<br>";
    echo "Input Password Length: " . strlen($test_pass) . " bytes<br>";
    echo "DB Password (hex): " . bin2hex($member['pass']) . "<br>";
    echo "Input Password (hex): " . bin2hex($test_pass) . "<br>";

    // 공백 체크
    $db_pass_trimmed = trim($member['pass']);
    echo "<br>Trimmed DB Password: '" . htmlspecialchars($db_pass_trimmed) . "'<br>";
    echo "Trimmed Match: " . ($test_pass === $db_pass_trimmed ? '✅ YES' : '❌ NO') . "<br>";

} else {
    echo "❌ member 테이블에서 계정을 찾을 수 없음<br>";
}

echo "<br><h3>2. users 테이블 조회</h3>";
$query = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $test_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    echo "✅ users 테이블에서 계정 찾음<br>";
    echo "Username: " . htmlspecialchars($user['username']) . "<br>";
    echo "Name: " . htmlspecialchars($user['name']) . "<br>";
} else {
    echo "❌ users 테이블에서 계정을 찾을 수 없음<br>";
}

mysqli_close($db);
?>
