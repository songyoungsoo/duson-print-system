<?php
session_start();
include "db.php"; // DB 연결 정보

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // 이메일이 회원 목록에 있는지 확인
    $query = "SELECT * FROM member WHERE email = '" . mysqli_real_escape_string($db, $email) . "'";
    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) == 1) {
        // 임시 비밀번호 생성
        $tempPassword = generateRandomPassword();
        
        // 비밀번호 업데이트
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE member SET password = '$hashedPassword' WHERE email = '$email'";
        mysqli_query($db, $updateQuery);

        // 여기에서 이메일을 통해 임시 비밀번호를 전송하는 코드를 추가해야 합니다.

        echo "임시 비밀번호가 이메일로 전송되었습니다. 로그인 후 새로운 비밀번호를 설정하세요.";
    } else {
        echo "입력한 이메일이 회원 목록에 없습니다.";
    }
}

// 임시 비밀번호 생성 함수
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++)





    <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEmail = $_POST['user_email']; // 사용자가 입력한 이메일 주소

    // TODO: 이메일이 데이터베이스에 있는지 확인하는 코드를 작성  // 이메일이 회원 목록에 있는지 확인
    $query = "SELECT * FROM member WHERE email = '" . mysql_real_escape_string($email, $db ) . "'";
    $result = mysql_query( $query,$db);

    if (mysql_num_rows($result) == 1) {
        // 임시 비밀번호 생성
    $temporaryPassword = bin2hex(random_bytes(8)); // 8자리의 랜덤한 문자열
        // TODO: 데이터베이스에 임시 비밀번호를 저장하는 코드를 작성
        $updateQuery = "UPDATE member SET pass = '$temporaryPassword' WHERE email = '$email'";
        mysql_query($updateQuery,$db );

        // 여기에서 이메일을 통해 임시 비밀번호를 전송하는 코드를 추가해야 합니다.

        echo "임시 비밀번호가 이메일로 전송되었습니다. 로그인 후 새로운 비밀번호를 설정하세요.";




    // TODO: 사용자에게 임시 비밀번호를 이메일로 전송하는 코드를 작성
    mail($userEmail, "비밀번호 재설정", "임시 비밀번호: $temporaryPassword");

    // TODO: 사용자에게 임시 비밀번호를 전송한 후의 추가 작업을 수행 (예: 사용자에게 알림 표시 등)

    echo "임시 비밀번호가 이메일로 전송되었습니다. 로그인 후 비밀번호를 변경하세요.";
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=EUC-KR" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 재설정</title>
</head>
<body>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    이메일 주소: <input type="text" name="user_email" required>
    <input type="submit" value="비밀번호 재설정">
</form>

</body>
</html>
} else {
    die("쿼리 실행에 실패했습니다: " . mysql_error($db));
}
}
?>
