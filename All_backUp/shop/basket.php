<?php 
// 🚫 이 장바구니는 폐쇄되었습니다 - 통합 장바구니로 리다이렉트
session_start();
$session_id = session_id();

// 통합 장바구니 시스템으로 리다이렉트
header('Location: /mlangprintauto/shop/cart.php');
exit();

// ========== 아래 코드는 더 이상 실행되지 않습니다 ==========
$HomeDir="../../";
include "../mlangprintauto/mlangprintautotop.php";
include "../lib/func.php"; 
include "../includes/AdditionalOptionsDisplay.php";
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
        <td width="120">추가옵션
        <td>도안비
        <td>금액
        <td>부가세포함
        <td>기타
  <?php 
  $query = "SELECT * FROM shop_temp WHERE session_id='$session_id'"; 
  $result = mysqli_query($connect, $query);
  $total = 0;
  $total_vat = 0;
  $optionsDisplay = getAdditionalOptionsDisplay($connect);
   
  while ($data = mysqli_fetch_array($result)) { 
    $j = substr($data['jong'], 4, 12);
    $j1 = substr($data['jong'], 0, 3);
    $d = substr($data['domusong'], 6, 4);
    $d1 = substr($data['domusong'], 0, 5);
    
    // 추가 옵션 가격 계산
    $base_price = intval($data['st_price']);
    
    // 추가 옵션이 있는지 확인 (coating_price, folding_price, creasing_price 필드가 있으면)
    $has_additional_options = isset($data['coating_price']) || isset($data['folding_price']) || isset($data['creasing_price']);
    
    if ($has_additional_options) {
        // AdditionalOptionsDisplay 클래스를 사용하여 계산
        $price_with_options = $optionsDisplay->calculateTotalWithOptions($base_price, $data);
        $final_price = $price_with_options['total_price'];
        $final_price_vat = $price_with_options['total_vat'];
    } else {
        // 추가 옵션이 없으면 기본 가격 사용
        $final_price = $base_price;
        $final_price_vat = intval($data['st_price_vat']);
    }
?>
      <tr align="center" bgcolor="#FFFFFF" style="height: 35px;">
        <td><?php echo  $data['no'] ?></td>
        <td><?php echo  $j ?></td>
        <td><?php echo  $data['garo'] ?></td>
        <td><?php echo  $data['sero'] ?></td>
        <td><?php echo  $data['mesu'] ?></td>
        <td><?php echo  $d ?></td>
        <td style="font-size: 11px; text-align: left; padding: 4px;">
            <?php 
            // 추가 옵션이 있는 경우만 표시
            if ($has_additional_options) {
                echo $optionsDisplay->getCartColumnHtml($data);
            } else {
                echo '<span style="color: #6c757d;">옵션 없음</span>';
            }
            ?>
        </td>
        <td><?php echo  $data['uhyung'] ?></td>
        <td><strong><?php echo number_format($final_price); ?>원</strong></td>
        <td><strong><?php echo number_format($final_price_vat); ?>원</strong></td>
        <td><a href="del_b.php?no=<?php echo  $data['no'] ?>" onclick="return confirm('정말 삭제할까요?');">삭제</a></td>
<?php 
    $total += $final_price;
    $total_vat += $final_price_vat; 
  } 
?>	

  <tr bgcolor="#CCCCFF" style="height: 35px;"> 
    <td bgcolor="#DDECDD"> 합계 
    <td colspan="7" bgcolor="#DDECDD">
    <td bgcolor="#DDECDD"><strong>￦
        <?php echo number_format($total)?> 
    </strong>
    <td bgcolor="#DDECDD"><strong>￦
        <?php echo number_format($total_vat)?>
        </strong>
    <td bgcolor="#DDECDD">
    </table> 
	<br>
     <a href="javascript:history.back(1);"><img src="img/pre.gif" width="99" height="31" border="0" /></a>
     <a href="quotation.php" target="_blank" style="display: inline-block; margin: 0 5px;">
         <button style="background: #3498db; color: white; border: none; padding: 8px 20px; font-size: 14px; border-radius: 5px; cursor: pointer; height: 31px;">
             📄 견적서 보기
         </button>
     </a>
     <a href=order.php><img src="img/order.gif" width="99" height="31" border="0" ></a><br>

  </div>
	<p align="center"><img src="../mlangprintauto/img/dechre1.png" width="601" height="872" alt=""/></p>																			 
<?php
include "../mlangprintauto/DhtmlText.php";
?>
<?php
include "../mlangprintauto/mlangprintautoDown.php";
?> 