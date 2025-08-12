<?php
session_start();
$session_id = session_id();
include $_SERVER['DOCUMENT_ROOT'] ."/db.php"; 
include $_SERVER['DOCUMENT_ROOT'] ."/MlangPrintAuto/MlangPrintAutoTop_s.php";

if (!isset($_SESSION['id_login_ok'])) {
  header("Location: login.php");
  exit();
}

$userEmail = $_SESSION['id_login_ok']['email'];

$query = "SELECT * FROM MlangOrder_PrintAuto WHERE email='" . mysqli_real_escape_string($userEmail) . "'";
$result = mysqli_query($query, $db);

if (!$result) {
  die("쿼리 실행에 실패했습니다: " . mysqli_error());
}

$l[1] = "주문접수";
$l[2] = "입금확인";
$l[3] = "배송중";
$l[4] = "배송완료";
$l[0] = "주문취소";
?>

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

</style>
<!DOCTYPE html>
<html lang="ko">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY ORDER</title>
</head>
<body>

  <?php
  $query = "SELECT * FROM MlangOrder_PrintAuto WHERE no='$no'";
  $result = mysqli_query($query, $db);
  $data = mysqli_fetch_array($result);

  $no =  $data['no'];
  $date =  $data['date'];
  $name =  $data['name'];
  $Type_1 =  $data['Type_1'];
  $phone =  $data['phone'];
  $Hendphone =  $data['Hendphone'];
  $delivery =  $data['delivery'];
  $address1 =  $data['zip1'];
  $address2 =  $data['zip2'];
  $money_4 =  $data['money_4'];
  $email =  $data['email'];
  $bank =  $data['bank'];
  $bankname =  $data['bankname'];
  $cont =  $data['cont'];
  $ImgFolder =  $data['ImgFolder'];
  ?>

  <br>

  <li><span class="OW">ORDER INFOMATION</span>
    <table width="650" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000">
      <tr bgcolor="#CCCCFF">
        <th bgcolor="#FFFFFF" class="OW">NO</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $no ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">DATE</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $date ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">NAME</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $name ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">ORDER</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $Type_1 ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">TEL</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $phone ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">MOBILE</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $Hendphone ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">DELEVERY</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $delivery ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">ADDRESS</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['zip'] ?><br><?php echo  $address1 ?> <?php echo  $address2 ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">TOTAL</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  number_format($money_4) ?> (VAT:<?php echo  number_format($money_4*1.1) ?>)</td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">EMAIL</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $email ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">BANK</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $bank ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">Depositor name</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  $bankname ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">MEMO</th>
        <td bgcolor="#FFFFFF" class="OW"><?php echo  nl2br($cont) ?></td>
      </tr>
      <!-- <tr>
      <th bgcolor="#FFFFFF" class="OW">UPLOAD</th>
        <td bgcolor="#FFFFFF" class="OW"><a href="download.php?downfile=<?php echo  $ImgFolder ?>"><?php echo  $ImgFolder ?></a></td>
      </tr> -->
    </table>
    <br>
    <div align =center> <button onclick="goBack()">이전페이지</button> </div>
    <script>
function goBack() {
    window.history.back();
}
</script>

 </body>
</html>
<?php
include $_SERVER['DOCUMENT_ROOT'] ."/MlangPrintAuto/MlangPrintAutoDown.php";
?> 