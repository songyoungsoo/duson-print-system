<?php 
   session_start(); 
   $session_id = session_id();

$HomeDir="../../";
include "../MlangPrintAuto/MlangPrintAutoTop.php";
include "../lib/func.php"; 
$connect = dbconn(); 
?>
<style type="text/css">
<!--
.boldB {
	font-family: "돋움";
	font-size: 9pt;
	font-weight: bold;
	color: #06F;
}
-->
</style>




  <div align="center">
    <li> 주문하실 내역입니다.(<span class="boldB">주문하실 &quot;건&quot;만 남겨두시고 나머지는 &quot;삭제&quot;</span>를 해주세요) <img src="img/basket.gif" width="60" height="70" /><br>
    <table width="600"  align="center" border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5">
      <tr  align="center" bgcolor="#E1E1FF">
        <td width="30">NO
        <td width="70">재질
        <td width="50">가로(mm)
        <td width="50">세로(mm)
        <td width="40">매수(매)
        <td width="70">도무송<br>(타입)	
        <td>도안비
        <td>금액
        <td>부가세포함
        <td>기타
  <?php 
  $query = "SELECT * FROM shop_temp WHERE session_id='$session_id'"; 
  $result = mysqli_query($connect, $query);
  $total = 0; 
  while ($data = mysqli_fetch_array($result)) { 
    $j = substr($data['jong'], 4, 12);
    $j1 = substr($data['jong'], 0, 3);
    $d = substr($data['domusong'], 6, 4);
    $d1 = substr($data['domusong'], 0, 5);
?>
      <tr align="center" bgcolor="#FFFFFF" style="height: 35px;">
        <td><?php echo  $data['no'] ?></td>
        <td><?php echo  $j ?></td>
        <td><?php echo  $data['garo'] ?></td>
        <td><?php echo  $data['sero'] ?></td>
        <td><?php echo  $data['mesu'] ?></td>
        <td><?php echo  $d ?></td>
        <td><?php echo  $data['uhyung'] ?></td>
        <td><strong><?php echo  $data['st_price'] ?> </strong></td>
        <td><strong><?php echo  $data['st_price_vat'] ?></strong></td>
        <td><a href="del_b.php?no=<?php echo  $data['no'] ?>" onclick="return confirm('정말 삭제할까요?');">삭제</a></td>
<?php 
    $total += $data['st_price']; 
  } 
?>	

  <tr bgcolor="#CCCCFF" style="height: 35px;"> 
    <td bgcolor="#DDECDD"> 합계 
    <td colspan="6" bgcolor="#DDECDD">
    <td bgcolor="#DDECDD"><strong>￦
        <?php echo number_format($total)?> 
    </strong>
    <td colspan="2" bgcolor="#DDECDD"><strong>￦
        <?php echo number_format($total*1.1)?>
        </strong>
    </table> 
	<br>
     <a href="javascript:history.back(1);"><img src="img/pre.gif" width="99" height="31" border="0" /></a>
     <a href=order.php><img src="img/order.gif" width="99" height="31" border="0" ></a><br>

  </div>
	<p align="center"><img src="../MlangPrintAuto/img/dechre1.png" width="601" height="872" alt=""/></p>																			 
<?php
include "../MlangPrintAuto/DhtmlText.php";
?>
<?php
include "../MlangPrintAuto/MlangPrintAutoDown.php";
?> 