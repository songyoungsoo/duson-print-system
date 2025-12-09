<?php
session_start();
$session_id = session_id();
include $_SERVER['DOCUMENT_ROOT'] ."/db.php"; 
include $_SERVER['DOCUMENT_ROOT'] ."/mlangprintauto/mlangprintautotop_s.php";

// if (!isset($_SESSION['id_login_ok'])) {
//   header("Location: ../member/login.php");
//   exit();
//}

$userid = $_SESSION['id_login_ok']['id'];
// $userpass = $_SESSION['id_login_ok']['pass'];

// MySQLi 문법으로 수정 (mysql_* → mysqli_*)

$query = "SELECT * FROM member WHERE id = ?";
$stmt = mysqli_prepare($query, $db);
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
  die("데이터베이스 접속에 오류가 발생했습니다: " . mysqli_error($db));
}

$data = mysqli_fetch_array($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $pass = mysqli_real_escape_string($db, $_POST['pass']);
  $name = mysqli_real_escape_string($db, $_POST['name']);
  $sample6_postcode = mysqli_real_escape_string($db, $_POST['sample6_postcode']);
  $sample6_address = mysqli_real_escape_string($db, $_POST['sample6_address']);
  $sample6_detailAddress = mysqli_real_escape_string($db, $_POST['sample6_detailAddress']);
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $phone1 = mysqli_real_escape_string($db, $_POST['phone1']);
  $phone2 = mysqli_real_escape_string($db, $_POST['phone2']);
  $phone3 = mysqli_real_escape_string($db, $_POST['phone3']);
  $hendphone1 = mysqli_real_escape_string($db, $_POST['hendphone1']);
  $hendphone2 = mysqli_real_escape_string($db, $_POST['hendphone2']);
  $hendphone3 = mysqli_real_escape_string($db, $_POST['hendphone3']);
  $po1 = mysqli_real_escape_string($db, $_POST['po1']);
  $po2 = mysqli_real_escape_string($db, $_POST['po2']);
  $po3 = mysqli_real_escape_string($db, $_POST['po3']);
  $po4 = mysqli_real_escape_string($db, $_POST['po4']);
  $po5 = mysqli_real_escape_string($db, $_POST['po5']);
  $po6 = mysqli_real_escape_string($db, $_POST['po6']);
  $po7 = mysqli_real_escape_string($db, $_POST['po7']);

  $update_query = "UPDATE member SET pass=?, name=?, sample6_postcode=?, sample6_address=?, sample6_detailAddress=?, email=?, phone1=?, phone2=?, phone3=?, hendphone1=?, hendphone2=?, hendphone3=?, po1=?, po2=?, po3=?, po4=?, po5=?, po6=?, po7=? WHERE id=?";
  $stmt = mysqli_prepare($update_query, $db);
  mysqli_stmt_bind_param($stmt, "ssssssssssssssssssss", $pass, $name, $sample6_postcode, $sample6_address, $sample6_detailAddress, $email, $phone1, $phone2, $phone3, $hendphone1, $hendphone2, $hendphone3, $po1, $po2, $po3, $po4, $po5, $po6, $po7, $userid);
  $update_result = mysqli_stmt_execute($stmt);

  if (!$update_result) {
    die("회원 정보 업데이트에 실패했습니다: " . mysqli_error($db));
  }

  // 업데이트가 성공적으로 이루어지면 다시 회원 정보를 불러옵니다.
  $query = "SELECT * FROM member WHERE id = ?";
  $stmt = mysqli_prepare($query, $db);
  mysqli_stmt_bind_param($stmt, "s", $userid);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $data = mysqli_fetch_array($result);
}

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY INFORMATION</title>
    <script>
        function confirmUpdate() {
            return confirm("정말 수정하시겠습니까?");
        }
    </script>
    <style type="text/css">

.style1 {
  color: #FF0000;
  font-weight: bold;
}
.OV {
  font-size: 9px;
}
.OW {
  font-size: 12px;
}
 .long-input {
        width: 450px;
 }
</style>
</head>

<?php
$id = $data['id'];
$pass = $data['pass'];
$name = $data['name'];
$sample6_postcode = $data['sample6_postcode'];
$sample6_address = $data['sample6_address'];
$sample6_detailAddress = $data['sample6_detailAddress'];
$email = $data['email'];
$date = $data['date'];
$po1 = $data['po1'];
$po2 = $data['po2'];
$po3 = $data['po3'];
$po4 = $data['po4'];
$po5 = $data['po5'];
$po6 = $data['po6'];
$po7 = $data['po7'];
?>
<br>
<span class="OW"><h3><li>내 정보수정</h3></span>
<form method="POST" action="" onsubmit="return confirmUpdate();">

  <table width="650" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000">
    <tr bgcolor="#CCCCFF">
      <th bgcolor="#FFFFFF" class="OW">아이디</th>
      <td bgcolor="#FFFFFF" class="OW"><?php echo  $id ?></td>
    </tr>
    <tr>
      <th bgcolor="#FFFFFF" class="OW">비밀번호</th>
      <td bgcolor="#FFFFFF" class="OW"><input type="password" name="pass" value="<?php echo  htmlspecialchars($pass) ?>"></td>
    </tr>
    <tr>
      <th bgcolor="#FFFFFF" class="OW">상호/성명</th>
      <td bgcolor="#FFFFFF" class="OW"><input type="text" name="name" value="<?php echo  $name ?>"></td>
    </tr>
    <tr>
      <th bgcolor="#FFFFFF" class="OW">주소</th>
      <td bgcolor="#FFFFFF" class="OW">
        <input type="text" name="sample6_postcode" value="<?php echo  $sample6_postcode ?>" class="long-input"><br>
        <input type="text" name="sample6_address" value="<?php echo  $sample6_address ?>" class="long-input"><br>
        <input type="text" name="sample6_detailAddress" value="<?php echo  $sample6_detailAddress ?>" class="long-input">
      </td>
    </tr>
    <tr>
      <th bgcolor="#FFFFFF" class="OW">전화</th>
      <td bgcolor="#FFFFFF" class="OW">
        <input type="text" name="phone1" value="<?php echo  $data['phone1'] ?>">-
        <input type="text" name="phone2" value="<?php echo  $data['phone2'] ?>">-
        <input type="text" name="phone3" value="<?php echo  $data['phone3'] ?>">
      </td>
    </tr>
    <tr>
    <th bgcolor="#FFFFFF" class="OW">핸드폰</th>
      <td bgcolor="#FFFFFF" class="OW">
      <input type="text" name="hendphone1" value="<?php echo  $data['hendphone1'] ?>">-
      <input type="text" name="hendphone2" value="<?php echo  $data['hendphone2'] ?>">-
      <input type="text" name="hendphone3" value="<?php echo  $data['hendphone3'] ?>"> 
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">이메일</th>
        <td bgcolor="#FFFFFF" class="OW">
        <input type="text" name="email" value="<?php echo  $data['email'] ?>" class="long-input">
        <!-- <?php echo  $email ?></td> -->
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">사업자정보</th>
        <td bgcolor="#FFFFFF" class="OW">
        <input type="text" name="po1" value="<?php echo  $data['po1'] ?>" class="long-input"> - 등록번호<br>          
        <input type="text" name="po2" value="<?php echo  $data['po2'] ?>" class="long-input"> - 상호<br>         
        <input type="text" name="po3" value="<?php echo  $data['po3'] ?>" class="long-input"> - 대표자명<br>         
        <input type="text" name="po4" value="<?php echo  $data['po4'] ?>" class="long-input"> - 업태<br>         
        <input type="text" name="po5" value="<?php echo  $data['po5'] ?>" class="long-input"> - 종목<br> 
        <input type="text" name="po6" value="<?php echo  $data['po6'] ?>" class="long-input"> - 사업장주소<br>         
        <input type="text" name="po7" value="<?php echo  $data['po7'] ?>" class="long-input"> - 계산서수취이메일         
      </td>
      </tr>
    </table>
    <br>
    <div align="center"><input type="submit" value="내정보수정"></div>
</form>
<div align =center> <button onclick="goBack()">이전페이지</button> </div>
    <script>
function goBack() {
    window.history.back();
}
</script>
<?php
include $_SERVER['DOCUMENT_ROOT'] ."/mlangprintauto/mlangprintautoDown.php";
?>