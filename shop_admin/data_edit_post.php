<?php 
include "../lib/func.php"; 
   $connect = dbconn();

  if($img_name){ 
      unlink("../shop/data/".$old_name); 
      move_uploaded_file($img,"../shop/data/".$img_name); 
      $tmp = " img='$img_name',    "; 
  } 
 
  $query = "update shop_d1 set
            il0='$il0',
			il1='$il1', 
            il2='$il2', 
            il3='$il3', 
            il4='$il4', 
            il5='$il5', 
            il6='$il6'"; 					
  mysqli_query($connect, $query); 
  $query = "update shop_d2 set 
            ka0='$ka0', 
            ka1='$ka1', 
            ka2='$ka2', 
            ka3='$ka3', 
            ka4='$ka4', 
            ka5='$ka5', 
            ka6='$ka6'"; 					
  mysqli_query($connect, $query); 
  $query = "update shop_d3 set 
            sp0='$sp0', 
            sp1='$sp1', 
            sp2='$sp2', 
            sp3='$sp3', 
            sp4='$sp4', 
            sp5='$sp5', 
            sp6='$sp6'"; 					
  mysqli_query($connect, $query); 
  $query = "update shop_d4 set 
  ck0='$ck0', 
  ck1='$ck1', 
  ck2='$ck2', 
  ck3='$ck3', 
  ck4='$ck4', 
  ck5='$ck5', 
  ck6='$ck6'"; 	
  mysqli_query($connect, $query); 		  
?> 
<script> 
    location.href = 'data_edit.php'; 
</script>
?>