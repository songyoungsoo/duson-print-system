<? 
   session_start(); 
   $session_id = session_id(); 
		
include "../lib/func.php"; 
   $connect = dbconn();

  $query = "select * from shop_d1"; 
  $result = mysqli_query($connect, $query); 
  if(!$result) die(mysqli_error()); 
  $data = mysqli_fetch_array($result); 
?> 

  <form action=data_edit_post.php method=post enctype=multipart/form-data>
    <input type=hidden name=no value="<?php echo $no?>">

<table>
    <tr>
	 <td colspan="2">
<li> 일반아트지스티커 금액 수정하는 화면(shop_d1 요율) 
   <tr>
	 <td>1000
     <td><input type=text name=il0 size=15 value="<?php echo $data['il0']?>">
   <tr>
	 <td>2-4000
     <td><input type=text name=il1 size=15 value="<?php echo $data['il1']?>"> 
   <tr>
	 <td>5000
     <td><input type=text name=il2 size=15 value="<?php echo $data['il2']?>">
   <tr>
	 <td>6-9000
     <td><input type=text name=il3 size=15 value="<?php echo $data['il3']?>">
   <tr>
	 <td>10000
     <td><input type=text name=il4 size=15 value="<?php echo $data['il4']?>">	 
   <tr>
	 <td>2-50000
     <td><input type=text name=il5 size=15 value="<?php echo $data['il5']?>">
   <tr>
	 <td>50000이상
     <td><input type=text name=il6 size=15 value="<?php echo $data['il6']?>">
    <tr>
	 <td>
     <td>
<br><br>
<?php
  $query = "select * from shop_d2"; 
  $result = mysqli_query($connect, $query); 
  if(!$result) die(mysqli_error()); 
  $data = mysqli_fetch_array($result); 
?>   

    <tr>
	 <td colspan="2">
<li> 강접스티커 금액 수정하는 화면 (shop_d2-강접요율) 

   <tr>
	 <td>1000
     <td><input type=text name=ka0 size=15 value="<?php echo $data['ka0']?>">
   <tr>
	 <td>2-4000
     <td><input type=text name=ka1 size=15 value="<?php echo $data['ka1']?>"> 
   <tr>
	 <td>5000
     <td><input type=text name=ka2 size=15 value="<?php echo $data['ka2']?>">
   <tr>
	 <td>6-9000
     <td><input type=text name=ka3 size=15 value="<?php echo $data['ka3']?>">
   <tr>
	 <td>10000
     <td><input type=text name=ka4 size=15 value="<?php echo $data['ka4']?>">	 
   <tr>
	 <td>2-50000
     <td><input type=text name=ka5 size=15 value="<?php echo $data['ka5']?>">
   <tr>
	 <td>50000이상
     <td><input type=text name=ka6 size=15 value="<?php echo $data['ka6']?>">
    <tr>
	 <td>
     <td>
    

<br><br>
<?php
  $query = "select * from shop_d3"; 
  $result = mysqli_query($connect, $query); 
  if(!$result) die(mysqli_error()); 
  $data = mysqli_fetch_array($result); 
?>
    <tr>
	 <td colspan="2"> 
<li> 특수지스티커 수정하는 화면shop_d3-특수지요율)  


   <tr>
	 <td>1000
     <td><input type=text name=sp0 size=15 value="<?php echo $data['sp0']?>">
   <tr>
	 <td>2-4000
     <td><input type=text name=sp1 size=15 value="<?php echo $data['sp1']?>"> 
   <tr>
	 <td>5000
     <td><input type=text name=sp2 size=15 value="<?php echo $data['sp2']?>">
   <tr>
	 <td>6-9000
     <td><input type=text name=sp3 size=15 value="<?php echo $data['sp3']?>">
   <tr>
	 <td>10000
     <td><input type=text name=sp4 size=15 value="<?php echo $data['sp4']?>">	 
   <tr>
	 <td>2-50000
     <td><input type=text name=sp5 size=15 value="<?php echo $data['sp5']?>">
   <tr>
	 <td>50000이상
     <td><input type=text name=sp6 size=15 value="<?php echo $data['sp6']?>">
     <br><br>
     <?php

$query = "select * from shop_d4"; 
$result = mysqli_query($connect, $query); 
if(!$result) die(mysqli_error()); 
$data = mysqli_fetch_array($result); 
?>
  <tr>
 <td colspan="2"> 
<li> 초강접스티커 수정하는 화면shop_d4-초강접요율)  


 <tr>
 <td>1000
   <td><input type=text name=ck0 size=15 value="<?php echo $data['ck0']?>">
 <tr>
 <td>2-4000
   <td><input type=text name=ck1 size=15 value="<?php echo $data['ck1']?>"> 
 <tr>
 <td>5000
   <td><input type=text name=ck2 size=15 value="<?php echo $data['ck2']?>">
 <tr>
 <td>6-9000
   <td><input type=text name=ck3 size=15 value="<?php echo $data['ck3']?>">
 <tr>
 <td>10000
   <td><input type=text name=ck4 size=15 value="<?php echo $data['ck4']?>">	 
 <tr>
 <td>2-50000
   <td><input type=text name=ck5 size=15 value="<?php echo $data['ck5']?>">
 <tr>
 <td>50000이상
   <td><input type=text name=ck6 size=15 value="<?php echo $data['ck6']?>">


   <tr>
     <td colspan="2"><p>
         <br><br><input name="submit" type=submit onclick="return confirm('정말 수정할까요?');" value=수정하기>
         </p>
</form>
?>