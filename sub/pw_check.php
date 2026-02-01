<?php
include $_SERVER['DOCUMENT_ROOT'] . "/db.php";

$password_input = isset($_POST["password"]) ? $_POST["password"] : '';

if (empty($password_input)) {
    header("Location: ./checkboard.htm");
    exit;
}

$stmt = mysqli_prepare($db, "SELECT id, password FROM users WHERE is_admin = 1 LIMIT 1");
if (!$stmt) {
    header("Location: ./checkboard.htm");
    exit;
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($admin) {
    $stored_pw = $admin['password'];
    $authenticated = false;

    // bcrypt 지원 + 평문 폴백
    if (strlen($stored_pw) === 60 && strpos($stored_pw, '$2y$') === 0) {
        $authenticated = password_verify($password_input, $stored_pw);
    } else {
        $authenticated = ($password_input === $stored_pw);
    }

    if ($authenticated) {
        header("Location: ../mlangorder_printauto/WindowSian.php?mode=OrderView&no=77328");
        exit;
    }
}

header("Location: ./checkboard.htm");
exit;
?>
