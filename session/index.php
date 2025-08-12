<?php
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
include $_SERVER['DOCUMENT_ROOT'] ."/db.php";

$userid = isset($_SESSION['id_login_ok']) ? $_SESSION['id_login_ok']['id'] : false;
$query = "SELECT * FROM member WHERE id ='" . mysqli_real_escape_string($db, $userid) . "'";
$result = mysqli_query($db, $query);

if (!$result) {
  die("쿼리 실행에 실패했습니다: " . mysqli_error($db));
}

$name = '';
if ($userid) {
  $query = "SELECT * FROM member WHERE id='" . mysqli_real_escape_string($db, $userid) . "'";
  $result = mysqli_query($db, $query);
  $data = mysqli_fetch_array($result);
  if ($data) {
    $name = $data['name'];
  }
}

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
  if (!$userid || !$name) { ?>

<a href="<?php $DOCUMENT_ROOT; ?>/member/login.php" style="font-weight: bold; color: blue; font-size: 9pt; font-family: 굴림;">로그인</a>|
<a href="<?php $DOCUMENT_ROOT; ?>/member/join.php" style="font-weight: bold; font-size: 9pt; font-family: 굴림;">회원가입</a>|
<?php } else { ?>
<a style="color: blue; font-size: 8.5pt; font-family: 굴림;"><?php echo  $name ?>님 환영!</a>|
<a href="<?php $DOCUMENT_ROOT; ?>/session/logOut.php" style="color: blue; font-size: 8.5pt; font-family: 굴림;">로그아웃</a> |
<a href="<?php $DOCUMENT_ROOT; ?>/session/my_info.php" style="font-size: 8.5pt; font-family: 굴림;">내정보</a> |
<a href="<?php $DOCUMENT_ROOT; ?>/session/orderhistory.php" style="font-size: 8.5pt; font-family: 굴림;">내주문내역</a> |


<?php } ?>
</div>
</body>
</html>
