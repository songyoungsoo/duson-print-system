<?php
session_start();
$id_login_ok = isset($_SESSION['id_login_ok']) ? $_SESSION['id_login_ok'] : false;
// Include necessary files and database connection
include "../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data and check if the user is logged in
    $id = $id_login_ok; // $_SESSION['id_login_ok'] = username string
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // users 테이블에서 비밀번호 조회 (PRIMARY)
    $query = "SELECT password FROM users WHERE username = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        // 비밀번호 검증 (bcrypt 해시 또는 평문 모두 지원)
        $stored_password = $data['password'];
        $password_valid = false;

        // bcrypt 해시인 경우 ($2y$로 시작하고 60자)
        if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
            $password_valid = password_verify($currentPassword, $stored_password);
        } else {
            // 평문 비밀번호인 경우 직접 비교
            $password_valid = ($currentPassword === $stored_password);
        }

        if ($password_valid) {
            // Check if the new passwords match
            if ($newPassword === $confirmPassword) {
                // Update the user's password in the database
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // users 테이블 업데이트 (PRIMARY)
                $updateQuery = "UPDATE users SET password = ? WHERE username = ?";
                $updateStmt = mysqli_prepare($db, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, 'ss', $hashedPassword, $id);

                if (mysqli_stmt_execute($updateStmt)) {
                    mysqli_stmt_close($updateStmt);

                    // member 테이블도 동기화 (dual write)
                    $syncQuery = "UPDATE member SET pass = ? WHERE id = ?";
                    $syncStmt = mysqli_prepare($db, $syncQuery);
                    if ($syncStmt) {
                        mysqli_stmt_bind_param($syncStmt, 'ss', $hashedPassword, $id);
                        mysqli_stmt_execute($syncStmt);
                        mysqli_stmt_close($syncStmt);
                    }

                    $success = "비밀번호가 성공적으로 변경되었습니다.";
                } else {
                    $error = "비밀번호 변경에 실패하였습니다.";
                    mysqli_stmt_close($updateStmt);
                }
            } else {
                $error = "새 비밀번호와 확인 비밀번호가 일치하지 않습니다.";
            }
        } else {
            $error = "현재 비밀번호가 일치하지 않습니다.";
        }
    } else {
        $error = "사용자를 찾을 수 없습니다.";
    }

    mysqli_stmt_close($stmt);
}

// Display the HTML form with appropriate messages
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 변경</title>
</head>
<body>

<h2>비밀번호 변경</h2>

<?php
if (isset($error)) {
    echo "<p style='color: red;'>오류: $error</p>";
}

if (isset($success)) {
    echo "<p style='color: green;'>$success</p>";
}
?>

<form action="change_password.php" method="post">
    <label for="current_password">현재 비밀번호:</label>
    <input type="password" name="current_password" required>

    <label for="new_password">새 비밀번호:</label>
    <input type="password" name="new_password" required>

    <label for="confirm_password">새 비밀번호 확인:</label>
    <input type="password" name="confirm_password" required>

    <input type="submit" value="비밀번호 변경">
</form>

</body>
</html>
