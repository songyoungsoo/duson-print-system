<?php
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
include $_SERVER['DOCUMENT_ROOT'] ."/db.php";

// 신규/레거시 세션 통합 지원
$userid = null;
$username = null;
$name = '';

// 1. 신규 세션 체크 (users 테이블)
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $userid = $_SESSION['user_id'];

    // users 테이블에서 조회
    $query = "SELECT name FROM users WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        if ($data) {
            $name = $data['name'];
        }
        mysqli_stmt_close($stmt);
    }
}

// 2. 레거시 세션 폴백 (member 테이블)
if (empty($name) && isset($_SESSION['id_login_ok']) && is_array($_SESSION['id_login_ok'])) {
    $username = $_SESSION['id_login_ok']['id'];

    // member 테이블에서 조회
    $query = "SELECT name FROM member WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        if ($data) {
            $name = $data['name'];
        }
        mysqli_stmt_close($stmt);
    }
}

// 로그인 여부 판단
$is_logged_in = !empty($name);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY INFOMATION</title>
    <style>
td, input, li {font-size:9pt}
th {
  background-color: #CCCCFF;
  font-size: 9pt;
  text-decoration: none;
}
.border {border-color:#CCC}
</style>
</head>
<body>
<div align="right">
  <?php
  if (!$is_logged_in) { ?>

<a href="<?php echo $DOCUMENT_ROOT; ?>/member/login.php" style="font-weight: bold; color: blue; font-size: 9pt; font-family: 굴림;">로그인</a>|
<a href="<?php echo $DOCUMENT_ROOT; ?>/member/join.php" style="font-weight: bold; font-size: 9pt; font-family: 굴림;">회원가입</a>|
<?php } else { ?>
<a style="color: blue; font-size: 8.5pt; font-family: 굴림;"><?php echo htmlspecialchars($name); ?>님 환영!</a>|
<a href="<?php echo $DOCUMENT_ROOT; ?>/session/logOut.php" style="color: blue; font-size: 8.5pt; font-family: 굴림;">로그아웃</a> |
<a href="<?php echo $DOCUMENT_ROOT; ?>/session/my_info.php" style="font-size: 8.5pt; font-family: 굴림;">내정보</a> |
<a href="<?php echo $DOCUMENT_ROOT; ?>/session/orderhistory.php" style="font-size: 8.5pt; font-family: 굴림;">내주문내역</a> |


<?php } ?>
</div>
</body>
</html>
