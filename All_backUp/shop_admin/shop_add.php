<?php 
include "../lib/func.php"; 
   $connect = dbconn();
?> 
<li> 상품 추가하는 화면 
<form action=shop_add_post.php method=post enctype=multipart/form-data> 
<table width=100% border=1> 
  <tr> 
    <td> 상품명 
    <td> <input type=text name=name size=30> 
  <tr> 
    <td> 짧은설명 
    <td> <input type=text name=comment size=50> 
  <tr> 
    <td> 금액 
    <td> <input type=text name=price size=10> 
  <tr> 
    <td> 설명 
    <td> <textarea name=memo cols=50 rows=10></textarea> 
  <tr> 
    <td> 사진 
    <td> <input type=file name=img size=10> 
  <tr> 
    <td colspan=2> 
      <input type=submit value='등록하기'> 
</table> 
</form>
?>