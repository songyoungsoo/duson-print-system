<?php
session_start();
$session_id = session_id();
include $_SERVER['DOCUMENT_ROOT'] ."/db.php"; 
include $_SERVER['DOCUMENT_ROOT'] ."/MlangPrintAuto/MlangPrintAutoTop_s.php";

// if (!isset($_SESSION['id_login_ok'])) {
//   header("Location: ../member/login.php");
//   exit();
// }

$userid = $_SESSION['id_login_ok']['id'];
// $userpass = $_SESSION['id_login_ok']['pass'];

$query = "SELECT * FROM member WHERE id ='" . mysqli_real_escape_string($userid) . "'";
$result = mysqli_query($query, $db);

if (!$result) {
  die("데이터베이스접속에러입니다: " . mysqli_error());
}

?>

<!-- <style type="text/css">

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

</style> -->
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY INFOMATION</title>
    <!-- ... Other head elements ... -->
    <!-- <script>
        function openChangePasswordWindow() {
            // Open a new window for password change
            var changePasswordWindow = window.open('change_password.php', 'ChangePasswordWindow', 'width=500,height=400');

            // Close the window after 5 seconds (adjust the time as needed)
            setTimeout(function () {
                changePasswordWindow.close();
            }, 5000);
        }
    </script> -->
    <script>
    function openChangePasswordWindow(linkElement) {
        // Open a new window for password change
        var changePasswordWindow = window.open('change_password.php', 'ChangePasswordWindow', 'width=400,height=400');

        // Check if the window is closed every 500 milliseconds
        var checkClosed = setInterval(function () {
            if (changePasswordWindow.closed !== false) {
                // Window is closed, clear the interval and perform any additional actions
                clearInterval(checkClosed);

                // Add your additional actions here
                // For example, you can redirect to another page or display a success message
                
                // Close the parent window
                window.close();
            }
        }, 500);
    }
</script>

</head>

  <?php
  $query = "SELECT * FROM member WHERE id='$userid'";
  $result = mysqli_query($query, $db);
  $data = mysqli_fetch_array($result);

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
  <span class="OW"><h3><li>MY INFOMATION</h3></span>
    <table width="650" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000">
      <tr bgcolor="#CCCCFF">
        <th bgcolor="#FFFFFF" class="OW">아이디</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $id ?></td>
      </tr>
      <tr>
    <th bgcolor="#FFFFFF" class="OW">비밀번호</th>
    <td bgcolor="#FFFFFF" class="OW"><input type="password" value="<?php echo  htmlspecialchars($pass) ?>" readonly>
    <!-- <a href="javascript:void(0);" onclick="openChangePasswordWindow()" style="font-weight: bold; color: blue;"> <비밀번호변경> </a> -->
    <a href="javascript:void(0);" onclick="openChangePasswordWindow(this)" style="font-weight: bold; color: blue;"> <비밀번호변경> </a>

    </td>
     </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">상호/성명</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $name ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">주소</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $sample6_postcode ?><br><?php echo  $sample6_address ?> <?php echo  $sample6_detailAddress ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">전화</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['phone1'] ?>-<?php echo  $data['phone2'] ?>-<?php echo  $data['phone3'] ?></td>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">핸드폰</th>
      <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['hendphone1'] ?>-<?php echo  $data['hendphone2'] ?>-<?php echo  $data['hendphone3'] ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">이메일</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $email ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">사업자정보</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo   $po1 ?><br><?php echo   $po2 ?><br><?php echo   $po3 ?><br><?php echo   $po4 ?><br><?php echo   $po5 ?><br><?php echo   $po6 ?><br><?php echo   $po7 ?></td>
      </tr>
    </table>
    <br>
    <div align="right">
          <!-- Add this button at the end of your existing code -->
          <a onclick="goBack()" align="center" style="font-weight: bold; cursor: pointer; color: blue;"><이전페이지></a>
          <a href="edit_profile.php" align="right" style="font-weight: bold; color: blue;"><내정보수정></a>
    </div>
    <!-- <div align="center">
  <p onclick="goBack()" style="cursor: pointer;">이전 페이지</p>
</div> -->

<script>
  function goBack() {
    window.history.back();
  }
</script>

<?php
include $_SERVER['DOCUMENT_ROOT'] ."/MlangPrintAuto/MlangPrintAutoDown.php";
?> 