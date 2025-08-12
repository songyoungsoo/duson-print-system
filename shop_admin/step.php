<?php 
  include "lib.php"; 
  $connect = dbconn(); 

  $query = "update shop_list set location=location+$step where no='$no' "; 
  mysqli_query($connect, $query); 
?> 
<script> 
   location.href='order_list.php'; 
</script>
?>