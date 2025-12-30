<? 
   session_start(); 
   $session_id = session_id(); 
		
include "../lib/func.php"; 
   $connect = dbconn();

  $query = "select * from shop_data1"; 
  $result = mysql_query($query, $connect); 
  if(!$result) die(mysql_error()); 
  $data = mysql_fetch_array($result); 
?> 
<li> 금액 수정하는 화면(data1-일반요율) 
  <form action=data_edit_post.php method=post enctype=multipart/form-data> 
<input type=hidden name=no value="<?=$no?>"> 
<input type=hidden name=old_name value="<?=$data[urlencode(img)]?>"> 

<table>
   <tr>
	 <td>1000
     <td><input type=text name=s1 size=15 value="<?=$data[0]?>">
   <tr>
	 <td>2-4000
     <td><input type=text name=s2 size=15 value="<?=$data[1]?>"> 
   <tr>
	 <td>5000
     <td><input type=text name=s3 size=15 value="<?=$data[2]?>">
   <tr>
	 <td>6-9000
     <td><input type=text name=s4 size=15 value="<?=$data[3]?>">
   <tr>
	 <td>10000
     <td><input type=text name=s5 size=15 value="<?=$data[4]?>">	 
   <tr>
	 <td>2-50000
     <td><input type=text name=s6 size=15 value="<?=$data[5]?>">
   <tr>
	 <td>50000이상
     <td><input type=text name=s7 size=15 value="<?=$data[6]?>">
   <tr colspan=2>
<td ><p>
  <input name="submit" type=submit value=수정하기>
  </p>
</form>
<br>
<?
  $query = "select * from shop_data2"; 
  $result = mysql_query($query, $connect); 
  if(!$result) die(mysql_error()); 
  $data = mysql_fetch_array($result); 
?>   
<li> 금액 수정하는 화면 (data2-강접요율) 
  <form action=data_edit_post.php method=post enctype=multipart/form-data> 
<input type=hidden name=no value="<?=$no?>"> 
<input type=hidden name=old_name value="<?=$data[urlencode(img)]?>"> 

<table>
   <tr>
	 <td>1000
     <td><input type=text name=s1 size=15 value="<?=$data[s1]?>">
   <tr>
	 <td>2-4000
     <td><input type=text name=s2 size=15 value="<?=$data[s2]?>"> 
   <tr>
	 <td>5000
     <td><input type=text name=s3 size=15 value="<?=$data[s3]?>">
   <tr>
	 <td>6-9000
     <td><input type=text name=s4 size=15 value="<?=$data[s4]?>">
   <tr>
	 <td>10000
     <td><input type=text name=s5 size=15 value="<?=$data[s5]?>">	 
   <tr>
	 <td>2-50000
     <td><input type=text name=s6 size=15 value="<?=$data[s6]?>">
   <tr>
	 <td>50000이상
     <td><input type=text name=s7 size=15 value="<?=$data[s7]?>">
   <tr>
     <td ><p>
  <input name="submit" type=submit value=수정하기>
  </p>
</form>
<br>
<?

  $query = "select * from shop_data3"; 
  $result = mysql_query($query, $connect); 
  if(!$result) die(mysql_error()); 
  $data = mysql_fetch_array($result); 
?> 
<li> 금액 수정하는 화면(data3-특수지요율)  
  <form action=data_edit_post.php method=post enctype=multipart/form-data> 
<input type=hidden name=no value="<?=$no?>"> 
<input type=hidden name=old_name value="<?=$data[urlencode(img)]?>"> 

<table>
   <tr>
	 <td>1000
     <td><input type=text name=s1 size=15 value="<?=$data[0]?>">
   <tr>
	 <td>2-4000
     <td><input type=text name=s2 size=15 value="<?=$data[1]?>"> 
   <tr>
	 <td>5000
     <td><input type=text name=s3 size=15 value="<?=$data[2]?>">
   <tr>
	 <td>6-9000
     <td><input type=text name=s4 size=15 value="<?=$data[3]?>">
   <tr>
	 <td>10000
     <td><input type=text name=s5 size=15 value="<?=$data[4]?>">	 
   <tr>
	 <td>2-50000
     <td><input type=text name=s6 size=15 value="<?=$data[5]?>">
   <tr>
	 <td>50000이상
     <td><input type=text name=s7 size=15 value="<?=$data[6]?>">
   <tr>
     <td ><p>
         <input name="submit" type=submit value=수정하기>
         </p>
</form>

