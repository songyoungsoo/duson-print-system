<?  
$HomeDir="../../";
include "../MlangPrintAuto/MlangPrintAutoTop.php";
include "../lib/func.php"; 
$connect = dbconn(); 
 
  $l[1] = "주문접수"; 
  $l[2] = "입금확인"; 
  $l[3] = "배송중"; 
  $l[4] = "배송완료"; 
  $l[0] = "주문취소"; 
?> 
    <div align="center" colspan="6">
     <a href="javascript:history.back(1);"><img src="img/pre.gif" width="99" height="31" border="0" /></a>
    <a href=basket.php><img src="img/jang.gif" width="99" height="31" border="0" /></a><br>
<form action=<?php echo $PHP_SELF?> > 
<table width=300 border=1> 
    <tr> 
      <td bgcolor="#D0EFF9"> 이름 
      <td> <input type=text name=name size=27>
    <tr> 
      <td bgcolor="#D0EFF9"> 비밀번호
      <td> <input type=password name=password size=30> 
    <tr> 
      <td align="center" colspan=2> <input type=submit value='주문조회'> 
</table>
</form>
</div>
<? 
  if($name){ 
?> 
   <table width=100% border=1> 
    <tr> 
      <td bgcolor="#D0EFF9"> 주문자 
      <td bgcolor="#D0EFF9"> 주문번호 
      (클릭시 내역확인) 
      <td bgcolor="#D0EFF9"> 주문일 
      <td bgcolor="#D0EFF9"> 금액 
      <td bgcolor="#D0EFF9"> 현재위치 
<?php
  // mysqli 사용으로 변경 및 배열 키를 문자열로 사용
  $query = "SELECT * FROM shop_list WHERE name='$name' AND password='$password'";
  $result = mysqli_query($connect, $query);
  if ($result) {
    while ($data = mysqli_fetch_assoc($result)) {
?>
    <tr>
      <td> <?php echo  htmlspecialchars($data['name']) ?> 
      <td> <a href="order_view.php?order_id=<?php echo  urlencode($data['order_id']) ?>"><?php echo  htmlspecialchars($data['order_id']) ?></a>
      <td> <?php echo  date("Y/m/d H:i:s", $data['regdate']) ?> 
      <td> <?php echo  htmlspecialchars($data['st_price']) ?> 
      <td> <?php echo  isset($l[$data['location']]) ? $l[$data['location']] : '' ?> 
<?php
    }
  } else {
    echo "<tr><td colspan='5'>조회 결과가 없습니다.</td></tr>";
  }
?>

   </table> 
<? 
  } 
?> 

<?php
include "../MlangPrintAuto/DhtmlText.php";
?>
<?php
include "../MlangPrintAuto/MlangPrintAutoDown.php";
?> 