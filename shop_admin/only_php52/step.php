<? 
  include "lib.php"; 
  $connect = dbconn(); 

  $query = "update shop_list set location=location+$step where no='$no' "; 
  mysql_query($query, $connect); 
?> 
<script> 
   location.href='order_list.php'; 
</script> 

