<style>
td,input,li,a,span,p{font-size:9pt}
.style2 {font-size: small}
body {
	margin-left: 0px;
	margin-top: 0px;
}
</style><? 
   include "../lib/func.php"; 
   $connect = dbconn(); 
   
?>
<table width="500"border="1" >
 <tr>
     <td height="68" background="img/post.jpg">
<tr>
     <td>
     
    <DIV style="DISPLAY: inline; OVERFLOW: auto; WIDTH: 500px; HEIGHT: 500px" ms_positioning="FlowLayout">

      <p align="center">
  [예] &quot;영등포동&quot;과 같이 동명을 입력해주세요  </p>
<form action=<?php echo $PHP_SELF?> > 
  <div align="center"><span>우편번호 검색</span> : 
    <input type=text name=s> 
    <input type=submit value='검색'> 
  </div>
</form> 

  <div align="left" >
    <script> 
   function copy(a, b, c){ 
      opener.order_info.zip1.value = a; 
      opener.order_info.zip2.value = b; 
      opener.order_info.address1.value = c; 
      opener.order_info.address2.focus(); 
      window.close(); 

   } 

  </script> 
    <? 
    if($s){ 

    $query = "select * from zipcode where (GUGUN like '%$s%') or (DONG like '%$s%')  "; 
    $result = mysql_query($query, $connect); 
    while($data = mysql_fetch_array($result)){ 
       $tmp = explode("-",$data[ZIPCODE]); 
       $a = $tmp[0]; 
       $b = $tmp[1]; 
?> 
  </div>
   
  <div align="left" >
 <li><a href=# onclick="copy('<?php echo $a?>','<?php echo $b?>','<?php echo $data[SIDO]?> <?php echo $data[GUGUN]?> <?php echo $data[DONG]?>')"> 
    <?php echo $data[ZIPCODE]?> 
    <?php echo $data[SIDO]?> 
    <?php echo $data[GUGUN]?> 
    <?php echo $data[DONG]?> 
    <?php echo $data[BUNJI]?>
    </a> 
      <? 
    } 
?> 
      
      
      <? } ?> 
      
      </div>
  </div>
</td>
<tr>
 <td height="10" bgcolor="#000066" >
</tr>
</table> 

 