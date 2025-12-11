<?php
include "lib.php";
if (!isset($_SESSION['isLogin'])) {
    header("Location: login.php");
    exit();
}
$userID = $_SESSION['isLogin']['id'];
$query = "SET NAMES 'UTF-8'";
mysql_query($query, $connect);
$query = "SELECT * FROM member WHERE id='" . mysql_real_escape_string($userID) . "'";
$result = mysql_query($query, $connect);
if (!$result) {
    die("쿼리 실행에 실패했습니다: " . mysql_error());
}
$userData = mysql_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>내정보</title>
</head>
<body>
<h1>내정보</h1>
<?php
$name = iconv('EUC-KR','UTF-8', $userData['name']);
$email = iconv('UTF-8', 'EUC-KR', $userData['email']);

echo $email;
?>
<p><strong>이름:</strong> <?php echo $name; ?></p>
<p><strong>Email:</strong> <?php echo $userData['email']; ?></p>
</body>
</html>
