<? 
  include "lib.php"; 
  $connect = dbconn(); 

  $query = "select * from shop_data "; 
  $result = mysqli_query($connect, $query); 
?> 
<table border=1 width=100%> 
  <tr> 
    <td> 상품명 
    <td> 이미지 
    <td> 금액 
    <td> 기타 
<? 
  while($data = mysqli_fetch_array($result)){ 
?> 
    <tr> 
      <td> <?php echo $data['name']?> 
      <td> <img src=../shop/data/<?php echo $data['img']?> height=40> 
      <td> <?php echo $data['st_price']?> 
      <td> <a href=shop_edit.php?no=<?php echo $data['no']?>>수정</a> 
<? 
  } 
?> 



</table>
?>