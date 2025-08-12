<?php
session_start();

include $_SERVER['DOCUMENT_ROOT'] ."/db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_SESSION['id_login_ok']['id'];
    $currentPassword = mysqli_real_escape_string($db, $_POST['current_password']);
    $newPassword = mysqli_real_escape_string($db, $_POST['new_password']);
    $confirmPassword = mysqli_real_escape_string($db, $_POST['confirm_password']);

    $query = "SELECT * FROM member WHERE id='$id'";
    $result = mysqli_query($query, $db);

    if ($result) {
        $data = mysqli_fetch_array($result);

        if ($data) {
            // Use crypt function with Blowfish
            // if (crypt($currentPassword, $data['pass']) === $data['pass']) {
            if ($currentPassword == $data['pass']) {
                // Check if the new passwords match
                if ($newPassword === $confirmPassword) {
                    // Use crypt with Blowfish to hash the new password
                    // $hashedPassword = crypt($newPassword, '$2a$10$' . bin2hex(random_bytes(22))); // 22 characters for the salt
                    // $randomBytes = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM); // 22 bytes for the salt
                    // $salt = bin2hex($randomBytes);
                    // $hashedPassword = crypt($newPassword, '$2a$10$' . $salt);
                    
                    // Update the user's password in the database
                    $updateQuery = "UPDATE member SET pass='$newPassword' WHERE id='$id'";
                    $updateResult = mysqli_query($updateQuery, $db);

                    if ($updateResult) {
                        $success = "비밀번호가 성공적으로 변경되었습니다.";
                        // Add JavaScript to close the window and return to the previous page
                        echo '<script>
                                alert("'.$success.'");
                                if (window.opener && window.opener.notifyPasswordChangeCompleted) {
                                    window.opener.notifyPasswordChangeCompleted();
                                }
                                window.close();
                            </script>';
                    } else {
                        $error = "비밀번호 변경에 실패했습니다.";
                        // Add JavaScript to display an error message and return to the previous page
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
    // Add this code when the password change operation is completed
    function notifyPasswordChangeCompleted() {
        // Add any additional actions you want to perform when the password change is completed
        alert('Password change completed!');
    }

    window.opener && window.opener.notifyPasswordChangeCompleted();
</script>

</body>
</html>
?>