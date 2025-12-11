<? 
   session_start(); 
   $session_id = session_id(); 

  include "../lib/func.php"; 
  $connect = dbconn(); 
   
  $query = "delete from shop_temp where no='$no' and session_id='$session_id' "; 
  mysql_query($query, $connect); 
?> 
<script> 
  location.href='basket.php'; 
</script> 

