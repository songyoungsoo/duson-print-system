<? 
  include "lib.php"; 
  $connect = dbconn();
   
  $l[1] = "주문접수"; 
  $l[2] = "입금확인"; 
  $l[3] = "작업중"; 
  $l[4] = "배송중"; 
  $l[0] = "주문취소"; 
  
if(!$start) $start = 1; 

  // 전체 페이지 구하기 
  $query = "select count(*) from MlangOrder_PrintAuto "; 
  $result = mysql_query($query, $connect); 
  $data = mysql_fetch_array($result); 
  $total = $data[0]; 

  echo "<li> 총 게시물수 : $total  "; 

   // 한화면에 표시될 페이지수 
  $pagenum = 10; 

  // 총페이지수 
  $pages = round($total / $pagenum); 

  // 시작변수 
  $s = $pagenum * ($start-1); 


  $query = "select * from MlangOrder_PrintAuto order by no desc"; 
  $query .= " limit $s, $pagenum "; 
  echo $query; 
  $result = mysql_query($query, $connect);
  ?> 
<table width=100% border=1> 
  <tr> 
    <td> 주문번호 
    <td> 주문자 
    <td> 전체금액 
    <td> 현재상태 
    <td> 기타 
<? 
  $total = 0; 
  while($data = mysql_fetch_array($result)){ 
?> 
  <tr> 
    <td>  <a href=../shop/order_view_one.php?no=<?php echo $data[no]?>><?php echo $data[no]?></a> 
    <td> <?php echo $data[name]?> 
    <td> <?php echo $data[phone]?> 
    <td> <?php echo $l[$data[location]]?>  
    <td> 
      <a href=step.php?step=-1&no=<?php echo $data[no]?>> ←</a> 
      <a href=step.php?step=1&no=<?php echo $data[no]?>> →</a>
      <?php
   $money += $data[money_4];
 } ?>  
  </table> 

<hr> 


<? 
    $a = $start - 5; 
    $b = $start + 5; 

    if($a<1) $a = 1; 
    if($b>$pages) $b = $pages; 

    $prev = $start - 10; 
    $next = $start + 10; 

    if($prev<=1) $prev = 1; 
    if($next>=$pages) $next = $pages; 
?> 

<? if($prev!=1){ ?> 
<a href=<?php echo $PHP_SELF?>?start=1<?php echo $href?>>맨처음</a> 
<? } ?> 

<a href=<?php echo $PHP_SELF?>?start=<?php echo $prev?><?php echo $href?>>[이전]</a> 
<? 

   for($i=$a; $i<=$b; $i++){ 

     if($start==$i) {?> 
        <b><?php echo $i?></b> 
     <? }else{  ?> 
       <a href=<?php echo $PHP_SELF?>?start=<?php echo $i?><?php echo $href?>>[<?php echo $i?>]</a> 
   <? } ?> 

<? } ?> 

<? if($next!=$pages){ ?> 
<a href=<?php echo $PHP_SELF?>?start=<?php echo $next?><?php echo $href?>>[다음]</a> 
<? } ?> 

<a href=<?php echo $PHP_SELF?>?start=<?php echo $pages?><?php echo $href?>>맨끝</a> 
