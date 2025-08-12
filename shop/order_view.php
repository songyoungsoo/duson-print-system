<? 
   session_start(); 
   $session_id = session_id();
 
$HomeDir="../../";
include "../lib/func.php"; 
$connect = dbconn(); 

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
.OV {
	font-size: 12px;
}
.OW {
	font-size: 14px;
}
-->
</style>
<li> <span class="OW">
  주문목록
</span>
  <table width="800" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000"> 
    <tr bgcolor="#CCCCFF"> 
      <td><span class="OW"> 제품명 
        </span>
      <td><span class="OW"> 가로
          </span>
      <td><span class="OW"> 세로
            </span>
      <td><span class="OW"> 수량 
              </span>
      <td><span class="OW"> 도안 
                </span>
      <td><span class="OW"> 도무송     
                  </span>
      <td><span class="OW"> 금액 
                    </span>
      <td><span class="OW"> 합계(VAT포함) 
                      </span>
<?php
  $query = "SELECT * FROM shop_order WHERE order_id='$order_id'";
  $result = mysqli_query($connect, $query);
  if (!$result) {
      die(mysqli_error($connect));
  }
  $total = 0;
  while ($data = mysqli_fetch_assoc($result)) {
?>
    <tr>
      <td bgcolor="#FFFFFF"><span class="OW">
        <?php echo  substr($data['jong'], 4, 6) ?>
        </span></td>
      <td bgcolor="#FFFFFF"><span class="OW">
        <?php echo  $data['garo'] ?>
        </span></td>
      <td bgcolor="#FFFFFF"><span class="OW">
        <?php echo  $data['sero'] ?>
        </span></td>
      <td bgcolor="#FFFFFF"><span class="OW">
        <?php echo  $data['mesu'] ?>
        </span></td>
      <td bgcolor="#FFFFFF"><span class="OW">
        <?php echo  $data['uhyung'] ?>
        </span></td>
      <td bgcolor="#FFFFFF"><span class="OW">
        <?php echo  $data['domusong'] ?>
        </span></td>
      <td bgcolor="#FFFFFF"><span class="OW">
        <?php echo  $data['st_price'] ?>
        </span></td>
      <td bgcolor="#FFFFFF"><span class="OW">
        <?php echo  $data['st_price_vat'] ?>
        </span></td>
    </tr>
<?php
    $total += $data['st_price'];
  }
?>
        </span>
    <tr bgcolor="#CCCCFF"> 
      <td bgcolor="#DDECDD"><span class="OW"> 합계 
                                            </span>
      <td colspan="5" bgcolor="#DDECDD">
      <td bgcolor="#DDECDD"><span class="OW">￦
        <?php echo number_format($total)?> 
        </span>
      <td bgcolor="#DDECDD"><span class="OW"><strong>￦
        <?php echo number_format($total*1.1)?>
        </strong>    
        </span>
    </table> 
  <span class="OW">
<?php 
  $query = "select * from shop_list where order_id='$order_id' "; 
  $result = mysqli_query($connect, $query); 
  $data = mysqli_fetch_array($result);
?> 
<br> 
  </span>
<li><span class="OW"> 주문자 정보</span>
<table width="800" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000">
  <tr bgcolor="#CCCCFF">
    <td bgcolor="#FFFFFF" class="OW">주문번호</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['order_id'] ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">주문시각</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  date("Y/m/d H:i:s", $data['regdate']) ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">이름</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['name'] ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">비밀번호</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['password'] ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">전화번호</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['phone'] ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">휴대폰</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['hphone'] ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">수령방법</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['delivery'] ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">주소</td>
    <td bgcolor="#FFFFFF" class="OW">
      <?php echo  $data['zip'] ?><br>
      <?php echo  $data['address1'] ?> <?php echo  $data['address2'] ?>
    </td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">총액</td>
    <td bgcolor="#FFFFFF" class="OW">￦<?php echo  number_format($total) ?> VAT별도</td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">이메일</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['email'] ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">입금은행</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['bank'] ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">입금자명</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  $data['ipkeum'] ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">메모</td>
    <td bgcolor="#FFFFFF" class="OW"><?php echo  nl2br($data['memo']) ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="OW">자료</td>
    <td bgcolor="#FFFFFF" class="OW">
      <a href='download.php?downfile=<?php echo  $data['img'] ?>'><?php echo  $data['img'] ?></a>
    </td>
  </tr>
</table>
<br>
</div>
---------------------------------------------------------------------<br />
<li><span class="OW">
  <?php echo  date("Y/m/d H:i:s", $data['regdate']) ?>
  / 주문자 : <?php echo  $data['name'] ?>
</span>
  <table width="800" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000"> 
    <tr bgcolor="#CCCCFF"> 
      <td><span class="OW"> 제품명 
        </span>
      <td><span class="OW"> 가로
          </span>
      <td><span class="OW"> 세로
            </span>
      <td><span class="OW"> 수량 
              </span>
      <td><span class="OW"> 도안 
                </span>
      <td><span class="OW"> 도무송     
                  </span>
      <td><span class="OW"> 금액 
                    </span>
      <td><span class="OW"> 합계(VAT포함) 
<?php 
  $query = "SELECT * FROM shop_order WHERE order_id='$order_id' "; 
  $result = mysqli_query($connect, $query);
  if (!$result) die(mysqli_error($connect)); 
  $total = 0; 
  while ($data = mysqli_fetch_assoc($result)) { 
?> 
        </span>
    <tr> 
      <td bgcolor="#FFFFFF"> <span class="OW">
        <?php echo substr($data['jong'],4,6)?>
        </span>
      <td bgcolor="#FFFFFF"> <span class="OW">
        <?php echo $data['garo']?>
        </span>
      <td bgcolor="#FFFFFF"> <span class="OW">
        <?php echo $data['sero']?>
        </span>
      <td bgcolor="#FFFFFF"> <span class="OW">
        <?php echo $data['mesu']?>
        </span>
      <td bgcolor="#FFFFFF"> <span class="OW">
        <?php echo $data['uhyung']?>
        </span>
      <td bgcolor="#FFFFFF"> <span class="OW">
        <?php echo $data['domusong']?>
        </span>
      <td bgcolor="#FFFFFF"> <span class="OW">
        <?php echo $data['st_price']?> 
        </span>
      <td bgcolor="#FFFFFF"> <span class="OW">
  <?php echo $data['st_price_vat']?> 
                                        
  <? 
   $total += $data['st_price']; 
} 
?> 
        </span>
    <tr bgcolor="#CCCCFF"> 
      <td bgcolor="#DDECDD"><span class="OW"> 합계 
                                            </span>
      <td colspan="5" bgcolor="#DDECDD">
      <td bgcolor="#DDECDD"><span class="OW">￦
        <?php echo number_format($total)?> 
        </span>
      <td bgcolor="#DDECDD"><span class="OW"><strong>￦
        <?php echo number_format($total*1.1)?>
        </strong>    
        </span>
  </table> 

  <span class="OW">
<?php 
  $query = "select * from shop_list where order_id='$order_id' "; 
  $result = mysqli_query($connect, $query);
  if (!$result) {
      die('Query Error: ' . mysqli_error($connect));
  }
  $data = mysqli_fetch_array($result);
?> 
<br> 
  </span>
<li><span class="OW"> 주문자 정보 </span>
<table width="800" border="0" cellspacing="1" cellpadding="2" bgcolor="#000000"> 
  <tr bgcolor="#CCCCFF"> 
    <td bgcolor="#FFFFFF" class="OW"> 주문번호 
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['order_id']?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 주문시각 
    <td bgcolor="#FFFFFF" class="OW"><?php echo date("Y/m/d H:i:s", $data['regdate'])?></td>
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 이름 
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['name']?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 비밀번호 
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['password']?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 전화번호 
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['phone']?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 휴대폰
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['hphone']?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 수령방법
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['delivery']?>     
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 주소 
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['zip']?> <br> 
    <?php echo $data['address1']?> <?php echo $data['address2']?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 총액 
    <td bgcolor="#FFFFFF" class="OW"> ￦<?php echo number_format($total)?> VAT별도 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 이메일 
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['email']?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 입금은행 
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['bank']?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 입금자명 
    <td bgcolor="#FFFFFF" class="OW"> <?php echo $data['ipkeum']?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 메모 
    <td bgcolor="#FFFFFF" class="OW"> <?php echo nl2br($data['memo'])?> 
  <tr> 
    <td bgcolor="#FFFFFF" class="OW"> 자료 
    <td bgcolor="#FFFFFF" class="OW"><a href='download.php?downfile=<?php echo $data['img']?>'><?php echo $data['img']?></a>
  </tr>
  
</table>
<br>
</div> 
<div align="left">
    <a href="javascript:history.back(1);"><img src="img/pre.gif" width="99" height="31" border="0" /></a>
    <a href=view.php><img src="img/auto.gif" width="99" height="31" border="0" /></a>
</div> 

