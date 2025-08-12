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
<!--
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
-->
</style>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>내주문</title>
</head>
<li>
  <span class="OW">주문목록</span>
  <table width="800" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000">
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
    $query = "SELECT * FROM shop_order WHERE order_id='$order_id'";
    $result = mysql_query($query, $connect);
    if (!$result) die(mysql_error());
    $total = 0;
    while ($data = mysql_fetch_array($result)) {
    ?>
      <tr>
        <td bgcolor="#FFFFFF"><span class="OW"><?= substr($data['jong'], 4, 6) ?></span></td>
        <td bgcolor="#FFFFFF"><span class="OW">iconv('EUC-KR','UTF-8',<?= $data['garo'] ?>);</span></td>
        <td bgcolor="#FFFFFF"><span class="OW"><?= $data['sero'] ?></span></td>
        <td bgcolor="#FFFFFF"><span class="OW"><?= $data['mesu'] ?></span></td>
        <td bgcolor="#FFFFFF"><span class="OW"><?= $data['uhyung'] ?></span></td>
        <td bgcolor="#FFFFFF"><span class="OW"><?= $data['domusong'] ?></span></td>
        <td bgcolor="#FFFFFF"><span class="OW"><?= $data['st_price'] ?></span></td>
        <td bgcolor="#FFFFFF"><span class="OW"><?= $data['st_price_vat'] ?></span></td>
      </tr>
    <?php
      $total += $data['st_price'];
    }
    ?>
    <tr bgcolor="#CCCCFF">
      <td bgcolor="#DDECDD"><span class="OW">합계</span></td>
      <td colspan="5" bgcolor="#DDECDD"></td>
      <td bgcolor="#DDECDD"><span class="OW">￦<?= number_format($total) ?></span></td>
      <td bgcolor="#DDECDD"><span class="OW"><strong>￦<?= number_format($total * 1.1) ?></strong></span></td>
    </tr>
  </table>

  <?php
  $query = "SELECT * FROM MlangOrder_PrintAuto WHERE no='$no'";
  $result = mysql_query($query, $connect);
  $data = mysql_fetch_array($result);

  $no = iconv('EUC-KR','UTF-8', $data['no']);
  $date = iconv('EUC-KR','UTF-8', $data['date']);
  $name = iconv('EUC-KR','UTF-8', $data['name']);
  $pass = iconv('EUC-KR','UTF-8', $data['pass']);
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
        <td bgcolor="#FFFFFF" class="OW">NO</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $no ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">TIME</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $date ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">NAME</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $name ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">PASSWORD</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $pass ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">TEL</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $phone ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">MOBILE</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $Hendphone ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">DELEVERY</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $delivery ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">ADDRESS</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $data['zip'] ?><br><?= $address1 ?> <?= $address2 ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">TOTAL</td>
        <td bgcolor="#FFFFFF" class="OW">￦<?= number_format($money_4) ?> VAT별도</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">EMAIL</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $email ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">BANK</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $bank ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">Depositor name</td>
        <td bgcolor="#FFFFFF" class="OW"><?= $bankname ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">MEMO</td>
        <td bgcolor="#FFFFFF" class="OW"><?= nl2br($cont) ?></td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" class="OW">UPLOAD</td>
        <td bgcolor="#FFFFFF" class="OW"><a href="download.php?downfile=<?= $data['ImgFolder'] ?>"><?= $data['ImgFolder'] ?></a></td>
      </tr>
    </table>
    <br>
</div>
