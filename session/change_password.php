<?php
session_start();

include $_SERVER['DOCUMENT_ROOT'] ."/db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_SESSION['id_login_ok']['id'];
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $query = "SELECT password FROM users WHERE username = ?";
    // 3-step verification: placeholders=1, types=1("s"), vars=1
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($data) {
            $stored_password = $data['password'];
            $password_valid = false;

            // bcrypt 검증 우선, plaintext fallback
            if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
                $password_valid = password_verify($currentPassword, $stored_password);
            } else {
                $password_valid = ($currentPassword === $stored_password);
            }

            if ($password_valid) {
                if ($newPassword === $confirmPassword) {
                    // 항상 bcrypt로 저장
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    // users 테이블 업데이트
                    $updateQuery = "UPDATE users SET password = ? WHERE username = ?";
                    // 3-step verification: placeholders=2, types=2("ss"), vars=2
                    $updateStmt = mysqli_prepare($db, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, "ss", $hashedPassword, $id);
                    $updateResult = mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);

                    // member 테이블 동시 업데이트 (backward compatibility dual write)
                    $memberUpdateQuery = "UPDATE member SET pass = ? WHERE id = ?";
                    // 3-step verification: placeholders=2, types=2("ss"), vars=2
                    $memberStmt = mysqli_prepare($db, $memberUpdateQuery);
                    if ($memberStmt) {
                        mysqli_stmt_bind_param($memberStmt, "ss", $hashedPassword, $id);
                        mysqli_stmt_execute($memberStmt);
                        mysqli_stmt_close($memberStmt);
                    }

                    if ($updateResult) {
                        $success = "비밀번호가 성공적으로 변경되었습니다.";
                        echo '<script>
                                alert("'.$success.'");
                                if (window.opener && window.opener.notifyPasswordChangeCompleted) {
                                    window.opener.notifyPasswordChangeCompleted();
                                }
                                window.close();
                            </script>';
                    } else {
                        $error = "비밀번호 변경에 실패했습니다.";
                        echo '<script>
                                alert("'.$error.'");
                                window.history.back();
                            </script>';
                    }
                } else {
                    $error = "새로운 비밀번호와 확인 비밀번호가 일치하지 않습니다.";
                }
            } else {
                $error = "현재 비밀번호가 일치하지 않습니다.";
            }
        } else {
            $error = "사용자를 찾을 수 없습니다.";
        }
    } else {
        die("쿼리 실행에 실패했습니다: " . mysqli_error($db));
    }
}
?>

<!-- HTML form for changing the password -->
<!-- <!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 변경</title>
</head>
<body>

<h3>비밀번호 변경</h3>

<?php
if (isset($error)) {
    echo "<p style='color: red;'>에러: $error</p>";
}

if (isset($success)) {
    echo "<p style='color: green;'>$success</p>";
}
?>

<form action="<?php echo  $_SERVER['PHP_SELF'] ?>" method="post">
    <label for="current_password">현재 비밀번호:</label>
    <input type="password" name="current_password" required><br>

    <label for="new_password">새로운 비밀번호:</label>
    <input type="password" name="new_password" required><br>

    <label for="confirm_password">새로운 비밀번호 확인:</label>
    <input type="password" name="confirm_password" required><br>

    <input type="submit" value="비밀번호 변경">    
</form>

<script>
    // Add this code when the password change operation is completed
    function notifyPasswordChangeCompleted() {
        // Add any additional actions you want to perform when the password change is completed
        alert('Password change completed!');
    }

    window.opener && window.opener.notifyPasswordChangeCompleted();
</script>

</body>
</html> -->
<!-- <!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 변경</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        h3 {
            color: #333;
        }

        form {
            max-width: 400px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        p {
            margin-top: 10px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>
<body>

<h3>비밀번호 변경</h3>

<?php
if (isset($error)) {
    echo "<p class='error'>에러: $error</p>";
}

if (isset($success)) {
    echo "<p class='success'>$success</p>";
}
?>

<form action="<?php echo  $_SERVER['PHP_SELF'] ?>" method="post">
    <label for="current_password">현재 비밀번호:</label>
    <input type="password" name="current_password" required>

    <label for="new_password">새로운 비밀번호:</label>
    <input type="password" name="new_password" required>

    <label for="confirm_password">새로운 비밀번호 확인:</label>
    <input type="password" name="confirm_password" required>

    <input type="submit" value="비밀번호 변경">    
</form>

<script>
    // Add this code when the password change operation is completed
    function notifyPasswordChangeCompleted() {
        // Add any additional actions you want to perform when the password change is completed
        alert('Password change completed!');
    }

    window.opener && window.opener.notifyPasswordChangeCompleted();
</script>

</body>
</html> -->
<!-- <!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 변경</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 5px;
        }

        h3 {
            color: #333;
            font-size: 9pt; /* Font size set to 9pt */
        }

        form {
            max-width: 400px;
            margin: 5px auto;
            background-color: #fff;
            padding: 5px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 9pt; /* Font size set to 9pt */
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 9pt; /* Font size set to 9pt */
        }

        input[type="submit"] {
            background-color: #4caf50;
            color: #fff;
            cursor: pointer;
            font-size: 9pt; /* Font size set to 9pt */
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        p {
            margin-top: 10px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>
<body>

<h3>비밀번호 변경</h3>

<?php
if (isset($error)) {
    echo "<p class='error'>에러: $error</p>";
}

if (isset($success)) {
    echo "<p class='success'>$success</p>";
}
?>

<form action="<?php echo  $_SERVER['PHP_SELF'] ?>" method="post">
    <label for="current_password">현재 비밀번호:</label>
    <input type="password" name="current_password" required>

    <label for="new_password">새로운 비밀번호:</label>
    <input type="password" name="new_password" required>

    <label for="confirm_password">새로운 비밀번호 확인:</label>
    <input type="password" name="confirm_password" required>

    <input type="submit" value="비밀번호 변경">    
</form>

<script>
    // Add this code when the password change operation is completed
    function notifyPasswordChangeCompleted() {
        // Add any additional actions you want to perform when the password change is completed
        alert('Password change completed!');
    }

    window.opener && window.opener.notifyPasswordChangeCompleted();
</script>

</body>
</html> -->
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 변경</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        h3 {
            color: #333;
            font-size: 9pt;
        }

        form {
            max-width: 400px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 9pt;
        }

        input {
            width: 100%;
            padding: 5px; /* Reduced height for input fields */
            margin-bottom: 10px; /* Reduced margin-bottom for input fields */
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 9pt;
        }

        input[type="submit"] {
            background-color: #4caf50;
            color: #fff;
            cursor: pointer;
            font-size: 9pt;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        p {
            margin-top: 10px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>
<body>

<h3>비밀번호 변경</h3>

<?php
if (isset($error)) {
    echo "<p class='error'>에러: $error</p>";
}

if (isset($success)) {
    echo "<p class='success'>$success</p>";
}
?>

<form action="<?php echo  $_SERVER['PHP_SELF'] ?>" method="post">
    <label for="current_password">현재 비밀번호:</label>
    <input type="password" name="current_password" required>

    <label for="new_password">새로운 비밀번호:</label>
    <input type="password" name="new_password" required>

    <label for="confirm_password">새로운 비밀번호 확인:</label>
    <input type="password" name="confirm_password" required>

    <input type="submit" value="비밀번호 변경">    
</form>

<script>
    function notifyPasswordChangeCompleted() {
        alert('Password change completed!');
    }

    window.opener && window.opener.notifyPasswordChangeCompleted();
</script>

</body>
</html>
?>
