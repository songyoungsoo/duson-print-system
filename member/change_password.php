<?php
session_start();
$id_login_ok = isset($_SESSION['id_login_ok']) ? $_SESSION['id_login_ok'] : false;
// Include necessary files and database connection
include "../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data and check if the user is logged in
    $id = $id_login_ok; // Assuming you store user ID in the session
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate the current password against the database
    $query = "SELECT * FROM member WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        // Password hashing should be used for security
        if (password_verify($currentPassword, $data['pass'])) {
            // Check if the new passwords match
            if ($newPassword === $confirmPassword) {
                // Update the user's password in the database
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateQuery = "UPDATE member SET pass = ? WHERE id = ?";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bind_param('ss', $hashedPassword, $id);

                if ($updateStmt->execute()) {
                    $success = "비밀번호가 성공적으로 변경되었습니다.";
                } else {
                    $error = "비밀번호 변경에 실패하였습니다.";
                }
                $updateStmt->close();
            } else {
                $error = "새 비밀번호와 확인 비밀번호가 일치하지 않습니다.";
            }
        } else {
            $error = "현재 비밀번호가 일치하지 않습니다.";
        }
    } else {
        $error = "사용자를 찾을 수 없습니다.";
    }

    $stmt->close();
    $db->close();
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
