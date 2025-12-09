<?php
include "../db.php";

$new_password = 'du701018';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$username = 'admin';

echo "<h2>비밀번호 업데이트</h2>";
echo "Username: $username<br>";
echo "New Password: $new_password<br>";
echo "Hashed: $hashed_password<br><br>";

// users 테이블 업데이트
$update_query = "UPDATE users SET password = ? WHERE username = ?";
$stmt = mysqli_prepare($db, $update_query);
mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $username);

if (mysqli_stmt_execute($stmt)) {
    echo "✅ users 테이블 비밀번호 업데이트 성공!<br>";

    // 확인
    $check_query = "SELECT id, username, name FROM users WHERE username = ?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo "<br>업데이트된 계정 정보:<br>";
        echo "ID: " . $row['id'] . "<br>";
        echo "Username: " . $row['username'] . "<br>";
        echo "Name: " . $row['name'] . "<br>";
    }

    mysqli_stmt_close($check_stmt);
} else {
    echo "❌ 업데이트 실패: " . mysqli_error($db) . "<br>";
}

mysqli_stmt_close($stmt);
mysqli_close($db);

echo "<br><br><a href='login.php'>로그인 페이지로 이동</a>";
?>
