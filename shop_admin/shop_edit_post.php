<?php 
  include "lib.php"; 
  $connect = dbconn(); 

  if($img_name){ 
      unlink("../shop/data/".$old_name); 
      move_uploaded_file($img,"../shop/data/".$img_name); 
      $tmp = " img='$img_name',    "; 
  } 

  $query = "update shop_data set 
            name='$name', 
            comment='$comment', 
            $tmp 
            price='$price', 
            memo='$memo' 
            where no='$no' "; 
  mysqli_query($connect, $query); 
?> 
<script> 
    location.href = 'shop_list.php'; 
</script>
?>