<? 
   session_start(); 
   $session_id = session_id(); 
		
include "../lib/func.php"; 
   $connect = dbconn();

	 
   $query = "insert into shop_data1(s1,s2,s3,s4,s5,s6,s7)
         values('$s1','$s2','$s3','$s4','$s5','$s6','$s7')";
   mysql_query($query,$connect);
   	 
?>
<script>
  location.href="data.php";
</script>
