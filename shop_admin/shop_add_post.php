<?php 
  include "lib.php"; 
  $connect = dbconn(); 

  if($img_name){ 
      move_uploaded_file($img,"../shop/data/".iconv('utf-8','UTF-8',$img_name)); 
  } 


  $query = "insert into shop_data2(name, comment, price, memo, img) 
            values('$name','$comment','$price','$memo','$img_name') "; 
  mysqli_query($connect, $query); 


?> 
<script> 
    location.href = 'shop_add.php'; 
</script>
?>