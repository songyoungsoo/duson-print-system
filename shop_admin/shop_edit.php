<? 
include "../lib/func.php"; 
   $connect = dbconn(); 

  $query = "select * from shop_data where no='$no' "; 
  $result = mysqli_query($connect, $query); 
  if(!$result) die(mysqli_error($connect)); 
  $data = mysqli_fetch_array($result); 
?> 
<li> 상품 수정하는 화면 
<form action=shop_edit_post.php method=post enctype=multipart/form-data> 
<input type=hidden name=no value="<?php echo $no?>"> 
<input type=hidden name=old_name value="<?php echo $data['img']?>"> 
<table width=100% border=1> 
  <tr> 
    <td> 상품명 
    <td> <input type=text name=name size=30 value="<?php echo $data['name']?>"> 
  <tr> 
    <td> 짧은설명 
    <td> <input type=text name=comment size=50 value="<?php echo $data['comment']?>"> 
  <tr> 
    <td> 금액 
    <td> <input type=text name=price size=10  value="<?php echo $data['price']?>"> 
  <tr>
    <td> 설명 
    <td> <textarea name=memo cols=50 rows=10><?php echo $data['memo']?></textarea> 
  <tr> 
    <td> 사진 
    <td><img src="../shop/data/<?php echo urlencode($data['img'])?>" height=40> <br>
     <input type=file name=img size=10> 

  <tr> 
    <td colspan=2> 
      <input type=submit value='수정하기'> 
</table> 
</form>
?>