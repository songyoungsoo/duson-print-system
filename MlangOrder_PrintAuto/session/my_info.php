<?php
session_start();
$session_id = session_id();

include "lib.php";

if (!isset($_SESSION['isLogin'])) {
  header("Location: login.php");
  exit();
}

$userid = $_SESSION['isLogin']['id'];
$userEmail = $_SESSION['isLogin']['email'];

$query = "SELECT * FROM MlangOrder_PrintAuto WHERE email='" . mysql_real_escape_string($userEmail) . "'";
$result = mysql_query($query, $connect);

if (!$result) {
  die("쿼리 실행에 실패했습니다: " . mysql_error());
}

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
    <title>MY INFOMATION</title>
</head>

  <?php
  $query = "SELECT * FROM member WHERE id='$userid'";
  $result = mysql_query($query, $connect);
  $data = mysql_fetch_array($result);

  $id = iconv('EUC-KR','UTF-8', $data['id']);
  $pass = iconv('EUC-KR','UTF-8', $data['pass']);
  $name = iconv('EUC-KR','UTF-8', $data['name']);
  $sample6_postcode = iconv('EUC-KR','UTF-8', $data['sample6_postcode']);
  $sample6_address = iconv('EUC-KR','UTF-8', $data['sample6_address']);
  $sample6_detailAddress = iconv('EUC-KR','UTF-8', $data['sample6_detailAddress']);
  $email = iconv('EUC-KR','UTF-8', $data['email']);
  $date = iconv('EUC-KR','UTF-8', $data['date']);
  $po1 = iconv('EUC-KR','UTF-8', $data['po1']);
  $po2 = iconv('EUC-KR','UTF-8', $data['po2']);
  $po3 = iconv('EUC-KR','UTF-8', $data['po3']);
  $po4 = iconv('EUC-KR','UTF-8', $data['po4']);
  $po5 = iconv('EUC-KR','UTF-8', $data['po5']);
  $po6 = iconv('EUC-KR','UTF-8', $data['po6']);
  $po7 = iconv('EUC-KR','UTF-8', $data['po7']);
  ?>

  <br>

  <span class="OW"><h3><li>MY INFOMATION</h3></span>
    <table width="800" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000">
      <tr bgcolor="#CCCCFF">
        <th bgcolor="#FFFFFF" class="OW"><?= iconv('EUC-KR','UTF-8','아이디')?></th>
        <td bgcolor="#FFFFFF" class="OW"><?= $id ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW"><?= iconv('EUC-KR','UTF-8','비밀번호')?></th>
        <td bgcolor="#FFFFFF" class="OW"><?= $pass ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW"><?= iconv('EUC-KR','UTF-8','회사명/이름')?></th>
        <td bgcolor="#FFFFFF" class="OW"><?= $name ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW"><?= iconv('EUC-KR','UTF-8','주소')?></th>
        <td bgcolor="#FFFFFF" class="OW"><?= $sample6_postcode ?><br><?= $sample6_address ?> <?= $sample6_detailAddress ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW"><?= iconv('EUC-KR','UTF-8','전화')?></th>
        <td bgcolor="#FFFFFF" class="OW"><?= $data['phone1'] ?>-<?= $data['phone2'] ?>-<?= $data['phone3'] ?></td>
      <tr>
      <th bgcolor="#FFFFFF" class="OW"><?= iconv('EUC-KR','UTF-8','핸드폰')?></th>
      <td bgcolor="#FFFFFF" class="OW"><?= $data['hendphone1'] ?>-<?= $data['hendphone2'] ?>-<?= $data['hendphone3'] ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW"><?= iconv('EUC-KR','UTF-8','이메일')?></th>
        <td bgcolor="#FFFFFF" class="OW"><?= $email ?></td>
      </tr>
      <tr>
      <th bgcolor="#FFFFFF" class="OW"><?= iconv('EUC-KR','UTF-8','사업자내용')?></th>
        <td bgcolor="#FFFFFF" class="OW"><?=  $po1 ?><br><?=  $po2 ?><br><?=  $po3 ?><br><?=  $po4 ?><br><?=  $po5 ?><br><?=  $po6 ?><br><?=  $po7 ?></td>
      </tr>

    </table>
    <br>
</div>
