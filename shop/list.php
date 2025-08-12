<? 
  include "../lib/func.php"; 
  $connect = dbconn(); 

?> 
<a href=basket.php>[장바구니]</a> 
<a href=../shop/reservation.php>[주문조회]</a>
<table width=100%  border=1> 

<? 
  $query = "select * from shop_data "; 
  $result = mysql_query($query); 
  while($data = mysql_fetch_array($result)){ 
?> 
  <tr> 
    <td> <a href=view.php?no=<?php echo $data[no]?>><img src=data/<?php echo $data[urlencode(img)]?> height=50 border=0></a> 
    <td> 
       <b> <a href=view.php?no=<?php echo $data[no]?>><?php echo $data[name]?></a></b> <br> 
       <?php echo $data[comment]?><br> 
       ￦<?php echo number_format($data[price])?> 

<? 
  } 
?> 
</table> 
