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

$query = "SELECT * FROM users WHERE username = ?";
// 3-step verification: placeholders=1, types=1("s"), vars=1
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
  die("데이터베이스 접속에 오류가 발생했습니다: " . mysqli_error($db));
}

$data = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'] ?? '';
  $sample6_postcode = $_POST['sample6_postcode'] ?? '';
  $sample6_address = $_POST['sample6_address'] ?? '';
  $sample6_detailAddress = $_POST['sample6_detailAddress'] ?? '';
  $email = $_POST['email'] ?? '';
  $phone1 = $_POST['phone1'] ?? '';
  $phone2 = $_POST['phone2'] ?? '';
  $phone3 = $_POST['phone3'] ?? '';
  $hendphone1 = $_POST['hendphone1'] ?? '';
  $hendphone2 = $_POST['hendphone2'] ?? '';
  $hendphone3 = $_POST['hendphone3'] ?? '';
  $po1 = $_POST['po1'] ?? '';
  $po2 = $_POST['po2'] ?? '';
  $po3 = $_POST['po3'] ?? '';
  $po4 = $_POST['po4'] ?? '';
  $po5 = $_POST['po5'] ?? '';
  $po6 = $_POST['po6'] ?? '';
  $po7 = $_POST['po7'] ?? '';

  // phone 결합: 핸드폰 우선, 없으면 전화번호 사용
  $phone_combined = '';
  if (!empty($hendphone1) && !empty($hendphone2) && !empty($hendphone3)) {
    $phone_combined = $hendphone1 . '-' . $hendphone2 . '-' . $hendphone3;
  } elseif (!empty($phone1) && !empty($phone2) && !empty($phone3)) {
    $phone_combined = $phone1 . '-' . $phone2 . '-' . $phone3;
  }

  // users 테이블 UPDATE
  $update_query = "UPDATE users SET name=?, postcode=?, address=?, detail_address=?, email=?, phone=?, business_number=?, business_name=?, business_owner=?, business_type=?, business_item=?, business_address=?, tax_invoice_email=? WHERE username=?";
  // 3-step verification: placeholders=14, types=14("ssssssssssssss"), vars=14
  $stmt = mysqli_prepare($db, $update_query);
  mysqli_stmt_bind_param($stmt, "ssssssssssssss",
    $name, $sample6_postcode, $sample6_address, $sample6_detailAddress,
    $email, $phone_combined,
    $po1, $po2, $po3, $po4, $po5, $po6, $po7,
    $userid
  );
  $update_result = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  if (!$update_result) {
    die("회원 정보 업데이트에 실패했습니다: " . mysqli_error($db));
  }

  // member 테이블 동시 업데이트 (backward compatibility dual write)
  $member_update_query = "UPDATE member SET name=?, sample6_postcode=?, sample6_address=?, sample6_detailAddress=?, email=?, phone1=?, phone2=?, phone3=?, hendphone1=?, hendphone2=?, hendphone3=?, po1=?, po2=?, po3=?, po4=?, po5=?, po6=?, po7=? WHERE id=?";
  // 3-step verification: placeholders=19, types=19("sssssssssssssssssss"), vars=19
  $member_stmt = mysqli_prepare($db, $member_update_query);
  if ($member_stmt) {
    mysqli_stmt_bind_param($member_stmt, "sssssssssssssssssss",
      $name, $sample6_postcode, $sample6_address, $sample6_detailAddress,
      $email, $phone1, $phone2, $phone3, $hendphone1, $hendphone2, $hendphone3,
      $po1, $po2, $po3, $po4, $po5, $po6, $po7,
      $userid
    );
    mysqli_stmt_execute($member_stmt);
    mysqli_stmt_close($member_stmt);
  }

  // 업데이트 후 다시 users에서 불러오기
  $query = "SELECT * FROM users WHERE username = ?";
  // 3-step verification: placeholders=1, types=1("s"), vars=1
  $stmt = mysqli_prepare($db, $query);
  mysqli_stmt_bind_param($stmt, "s", $userid);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $data = mysqli_fetch_array($result);
  mysqli_stmt_close($stmt);
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
$id = $data['username'];
$name = $data['name'];
$sample6_postcode = $data['postcode'];
$sample6_address = $data['address'];
$sample6_detailAddress = $data['detail_address'];
$email = $data['email'];
$date = $data['created_at'];
$po1 = $data['business_number'];
$po2 = $data['business_name'];
$po3 = $data['business_owner'];
$po4 = $data['business_type'];
$po5 = $data['business_item'];
$po6 = $data['business_address'];
$po7 = $data['tax_invoice_email'];

// phone 분리: users.phone ("010-1234-5678") → phone1, phone2, phone3
list($phone1, $phone2, $phone3) = array_pad(explode('-', $data['phone'] ?? ''), 3, '');
// hendphone: users 테이블에 별도 필드 없음, phone과 동일하게 표시
list($hendphone1, $hendphone2, $hendphone3) = array_pad(explode('-', $data['phone'] ?? ''), 3, '');
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
      <td bgcolor="#FFFFFF" class="OW"><input type="password" value="********" readonly> <a href="change_password.php" style="font-size:9pt; color:blue;" onclick="window.open(this.href,'ChangePasswordWindow','width=400,height=400'); return false;">&lt;비밀번호변경&gt;</a></td>
    </tr>
    <tr>
      <th bgcolor="#FFFFFF" class="OW">상호/성명</th>
      <td bgcolor="#FFFFFF" class="OW"><input type="text" name="name" value="<?php echo  htmlspecialchars($name) ?>"></td>
    </tr>
    <tr>
      <th bgcolor="#FFFFFF" class="OW">주소</th>
      <td bgcolor="#FFFFFF" class="OW">
        <input type="text" name="sample6_postcode" value="<?php echo  htmlspecialchars($sample6_postcode) ?>" class="long-input"><br>
        <input type="text" name="sample6_address" value="<?php echo  htmlspecialchars($sample6_address) ?>" class="long-input"><br>
        <input type="text" name="sample6_detailAddress" value="<?php echo  htmlspecialchars($sample6_detailAddress) ?>" class="long-input">
      </td>
    </tr>
    <tr>
      <th bgcolor="#FFFFFF" class="OW">전화</th>
      <td bgcolor="#FFFFFF" class="OW">
        <input type="text" name="phone1" value="<?php echo  htmlspecialchars($phone1) ?>">-
        <input type="text" name="phone2" value="<?php echo  htmlspecialchars($phone2) ?>">-
        <input type="text" name="phone3" value="<?php echo  htmlspecialchars($phone3) ?>">
      </td>
    </tr>
    <tr>
    <th bgcolor="#FFFFFF" class="OW">핸드폰</th>
      <td bgcolor="#FFFFFF" class="OW">
      <input type="text" name="hendphone1" value="<?php echo  htmlspecialchars($hendphone1) ?>">-
      <input type="text" name="hendphone2" value="<?php echo  htmlspecialchars($hendphone2) ?>">-
      <input type="text" name="hendphone3" value="<?php echo  htmlspecialchars($hendphone3) ?>"> 
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">이메일</th>
        <td bgcolor="#FFFFFF" class="OW">
        <input type="text" name="email" value="<?php echo  htmlspecialchars($email ?? '') ?>" class="long-input">
        <!-- <?php echo  $email ?></td> -->
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">사업자정보</th>
        <td bgcolor="#FFFFFF" class="OW">
        <input type="text" name="po1" value="<?php echo  htmlspecialchars($po1 ?? '') ?>" class="long-input"> - 등록번호<br>          
        <input type="text" name="po2" value="<?php echo  htmlspecialchars($po2 ?? '') ?>" class="long-input"> - 상호<br>         
        <input type="text" name="po3" value="<?php echo  htmlspecialchars($po3 ?? '') ?>" class="long-input"> - 대표자명<br>         
        <input type="text" name="po4" value="<?php echo  htmlspecialchars($po4 ?? '') ?>" class="long-input"> - 업태<br>         
        <input type="text" name="po5" value="<?php echo  htmlspecialchars($po5 ?? '') ?>" class="long-input"> - 종목<br> 
        <input type="text" name="po6" value="<?php echo  htmlspecialchars($po6 ?? '') ?>" class="long-input"> - 사업장주소<br>         
        <input type="text" name="po7" value="<?php echo  htmlspecialchars($po7 ?? '') ?>" class="long-input"> - 계산서수취이메일         
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
