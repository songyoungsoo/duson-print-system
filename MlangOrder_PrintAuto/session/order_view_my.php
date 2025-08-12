<?php
session_start();
$session_id = session_id();

include "lib.php";

if (!isset($_SESSION['isLogin'])) {
  header("Location: login.php");
  exit();
}

$userEmail = $_SESSION['isLogin']['email'];

$query = "SELECT * FROM MlangOrder_PrintAuto WHERE email='" . mysql_real_escape_string($userEmail) . "'";
$result = mysql_query($query, $connect);

if (!$result) {
  die("쿼리 실행에 실패했습니다: " . mysql_error());
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

    <meta http-equiv="Content-Type" content="text/html; charset=EUC-KR" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY ORDER</title>
</head>
<!-- <li>
  <span class="OW">주문목록</span> -->
 <!-- <table width="800" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000">
    <tr bgcolor="#CCCCFF">
      <th><span class="OW">제품명</span></th>
      <th><span class="OW">가로</span></th>
      <th><span class="OW">세로</span></th>
      <th><span class="OW">수량</span></th>
      <th><span class="OW">도안</span></th>
      <th><span class="OW">도무송</span></th>
      <th><span class="OW">금액</span></th>
      <th><span class="OW">합계(VAT포함)</span></th>
    </tr>

    <?php 
  //   $query = "SELECT * FROM MlangOrder_PrintAuto WHERE no='$no'";
  //   $result = mysql_query($query, $connect);
  //   if (!$result) die(mysql_error());
  //   $total = 0;
  //   while ($data = mysql_fetch_array($result)) {
  //   ?>
  //     <tr>
  //       <td bgcolor="#FFFFFF"><span class="OW"><?= substr($data['jong'], 4, 6) ?></span></td>
  //       <td bgcolor="#FFFFFF"><span class="OW">iconv('EUC-KR','UTF-8',<?= $data['garo'] ?>);</span></td>
  //       <td bgcolor="#FFFFFF"><span class="OW"><?= $data['sero'] ?></span></td>
  //       <td bgcolor="#FFFFFF"><span class="OW"><?= $data['mesu'] ?></span></td>
  //       <td bgcolor="#FFFFFF"><span class="OW"><?= $data['uhyung'] ?></span></td>
  //       <td bgcolor="#FFFFFF"><span class="OW"><?= $data['domusong'] ?></span></td>
  //       <td bgcolor="#FFFFFF"><span class="OW"><?= $data['st_price'] ?></span></td>
  //       <td bgcolor="#FFFFFF"><span class="OW"><?= $data['st_price_vat'] ?></span></td>
  //     </tr>
  //   <?php
  //     $total += $data['st_price'];
  //   }
  //   ?>
  //   <tr bgcolor="#CCCCFF">
  //     <td bgcolor="#DDECDD"><span class="OW">합계</span></td>
  //     <td colspan="5" bgcolor="#DDECDD"></td>
  //     <td bgcolor="#DDECDD"><span class="OW">￦<?= number_format($total) ?></span></td>
  //     <td bgcolor="#DDECDD"><span class="OW"><strong>￦<?= number_format($total * 1.1) ?></strong></span></td>
  //   </tr>
  // </table> -->

  <?php
  $query = "SELECT * FROM MlangOrder_PrintAuto WHERE no='$no'";
  $result = mysql_query($query, $connect);
  $data = mysql_fetch_array($result);

  $no = iconv('EUC-KR','UTF-8', $data['no']);
  $date = iconv('EUC-KR','UTF-8', $data['date']);
  $name = iconv('EUC-KR','UTF-8', $data['name']);
  $Type_1 = iconv('EUC-KR','UTF-8', $data['Type_1']);
  $phone = iconv('EUC-KR','UTF-8', $data['phone']);
  $Hendphone = iconv('EUC-KR','UTF-8', $data['Hendphone']);
  $delivery = iconv('EUC-KR','UTF-8', $data['delivery']);
  $address1 = iconv('EUC-KR','UTF-8', $data['zip1']);
  $address2 = iconv('EUC-KR','UTF-8', $data['zip2']);
  $money_4 = iconv('EUC-KR','UTF-8', $data['money_4']);
  $email = iconv('EUC-KR','UTF-8', $data['email']);
  $bank = iconv('EUC-KR','UTF-8', $data['bank']);
  $bankname = iconv('EUC-KR','UTF-8', $data['bankname']);
  $cont = iconv('EUC-KR','UTF-8', $data['cont']);
  $ImgFolder = iconv('EUC-KR','UTF-8', $data['ImgFolder']);
  ?>

  <br>

  <li><span class="OW">ORDER INFOMATION</span>
    <table width="800" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000">
      <tr bgcolor="#CCCCFF">
        <th bgcolor="#FFFFFF" class="OW">NO</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $no ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">DATE</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $date ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">NAME</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $name ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">ORDER</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $Type_1 ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">TEL</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $phone ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">MOBILE</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $Hendphone ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">DELEVERY</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $delivery ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">ADDRESS</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $data['zip'] ?><br><?= $address1 ?> <?= $address2 ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">TOTAL</th>
        <td bgcolor="#FFFFFF" class="OW"><?= number_format($money_4) ?> (VAT:<?= number_format($money_4*1.1) ?>)</td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">EMAIL</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $email ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">BANK</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $bank ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">Depositor name</th>
        <td bgcolor="#FFFFFF" class="OW"><?= $bankname ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">MEMO</th>
        <td bgcolor="#FFFFFF" class="OW"><?= nl2br($cont) ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW">UPLOAD</th>
        <td bgcolor="#FFFFFF" class="OW"><a href="download.php?downfile=<?= $ImgFolder ?>"><?= $ImgFolder ?></a></td>
      </tr>
    </table>
    <br>
</div>
