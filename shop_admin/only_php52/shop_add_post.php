<? 
  include "lib.php"; 
  $connect = dbconn(); 

  if($img_name){ 
      move_uploaded_file($img,"../shop/data/".iconv('utf-8','euc-kr',$img_name)); 
  } 


  $query = "insert into shop_data2(name, comment, price, memo, img) 
            values('$name','$comment','$price','$memo','$img_name') "; 
  mysql_query($query,$connect); 


?> 
<script> 
    location.href = 'shop_add.php'; 
</script>
