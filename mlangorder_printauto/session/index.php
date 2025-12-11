<?php
include "lib.php"; 

$isLogin = isset($_SESSION['isLogin']) ? $_SESSION['isLogin'] : false;
echo $isLogin;
print_r($isLogin);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>홈</title>
</head>
<body>

<?php if (!$isLogin) { ?>
    로그인 후 이용해 주세요. <br>
    <a href="login.php">로그인</a>
<?php } else { ?>
    <a href="my_info.php">내정보</a> |
    <a href="orderhistory.php">내주문내역</a> |
    <a href="logOut.php">로그아웃</a>
<?php } ?>

</body>
</html>
