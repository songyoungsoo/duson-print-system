<? 
   session_start(); 
   $session_id = session_id(); 
		
include "../lib/func.php"; 
   $connect = dbconn();
   
  $query = "select * from shop_data1"; 
  $result = mysqli_query($connect, $query); 
  if(!$result) die(mysqli_error()); 
  $data = mysqli_fetch_array($result);
?> 
<li> 금액 입력하는 화면 
<form action=data_il_post.php method=post enctype=multipart/form-data> 
<input type=hidden name=no value="<?php echo $no?>"> 
<input type=hidden name=old_name value="<?php echo $data[urlencode(img)]?>"> 

<table>
   <tr>
	 <td>1000
     <td><input type=text name=s1 size=15 value="<?php echo $data['s1']?>">
   <tr>
	 <td>2-4000
     <td><input type=text name=s2 size=15 value="<?php echo $data['s2']?>"> 
   <tr>
	 <td>5000
     <td><input type=text name=s3 size=15 value="<?php echo $data['s3']?>">
   <tr>
	 <td>6-9000
     <td><input type=text name=s4 size=15 value="<?php echo $data['s4']?>">
   <tr>
	 <td>10000
     <td><input type=text name=s5 size=15 value="<?php echo $data['s5']?>">	 
   <tr>
	 <td>2-50000
     <td><input type=text name=s6 size=15 value="<?php echo $data['s6']?>">
   <tr>
	 <td>50000이상
     <td><input type=text name=s7 size=15 value="<?php echo $data['s7']?>">
	    <tr>
<td ><input name="submit" type=submit value=수정하기>
</form>
?>