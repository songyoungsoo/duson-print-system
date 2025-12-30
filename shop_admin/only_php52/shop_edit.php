<? 
include "../lib/func.php"; 
   $connect = dbconn(); 

  $query = "select * from shop_data where no='$no' "; 
  $result = mysql_query($query, $connect); 
  if(!$result) die(mysql_error()); 
  $data = mysql_fetch_array($result); 
?> 
<li> 상품 수정하는 화면 
<form action=shop_edit_post.php method=post enctype=multipart/form-data> 
<input type=hidden name=no value="<?=$no?>"> 
<input type=hidden name=old_name value="<?=$data[urlencode(img)]?>"> 
<table width=100% border=1> 
  <tr> 
    <td> 상품명 
    <td> <input type=text name=name size=30 value="<?=$data[name]?>"> 
  <tr> 
    <td> 짧은설명 
    <td> <input type=text name=comment size=50 value="<?=$data[comment]?>"> 
  <tr> 
    <td> 금액 
    <td> <input type=text name=price size=10  value="<?=$data[price]?>"> 
  <tr>
    <td> 설명 
    <td> <textarea name=memo cols=50 rows=10><?=$data[memo]?></textarea> 
  <tr> 
    <td> 사진 
    <td><img src=../shop/data/<?=$data[urlencode(img)]?>  height=40> <br> 
     <input type=file name=img size=10> 

  <tr> 
    <td colspan=2> 
      <input type=submit value='수정하기'> 
</table> 
</form> 

