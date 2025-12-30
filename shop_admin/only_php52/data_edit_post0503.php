<? 
include "../lib/func.php"; 
   $connect = dbconn();

  if($img_name){ 
      unlink("../shop/data/".$old_name); 
      move_uploaded_file($img,"../shop/data/".$img_name); 
      $tmp = " img='$img_name',    "; 
  } 

  $query = "update shop_data1 set 
            s1='$s1', 
            s2='$s2', 
            s3='$s3', 
            s4='$s4', 
            s5='$s5', 
            s6='$s6', 
            s7='$s7'"; 					
  mysql_query($query, $connect); 
  $query = "update shop_data2 set 
            s1='$s1', 
            s2='$s2', 
            s3='$s3', 
            s4='$s4', 
            s5='$s5', 
            s6='$s6', 
            s7='$s7'"; 					
  mysql_query($query, $connect); 
  $query = "update shop_data3 set 
            s1='$s1', 
            s2='$s2', 
            s3='$s3', 
            s4='$s4', 
            s5='$s5', 
            s6='$s6', 
            s7='$s7'"; 					
  mysql_query($query, $connect);   
?> 
<script> 
    location.href = 'data_edit.php'; 
</script> 