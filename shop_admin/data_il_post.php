<?php 
   session_start(); 
   $session_id = session_id(); 

		
include "../lib/func.php"; 
   $connect = dbconn();
   $regdate = time();
  
  
   $query = "insert into shop_data1(s1,s2,s3,s4,s5,s6,s7)
         values('$s1','$s2','$s3','$s4','$s5','$s6','$s7')";
   mysqli_query($connect, $query);
   	 
?>
<script>
  location.href="data_il.php";
</script>
?>