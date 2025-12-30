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
  $query = "select count(*) from shop_list "; 
  $result = mysql_query($query, $connect); 
  $data = mysql_fetch_array($result); 
  $ap = $data[0]; 
  echo "<li> 총 게시물수 : $ap  "; 

   // 한화면에 표시될 페이지수 
  $pagenum = 10; 

  // 총페이지수 
  $pages = round($ap/$pagenum); 

  // 시작변수 
  $s = $pagenum * ($start-1); 


  $query = "select * from shop_list order by no desc"; /**/
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
    echo $data;
?> 
  <tr> 
    <td>  <a href=../shop/order_view.php?order_id=<?=$data[order_id]?>><?=$data[order_id]?></a> 
    <td> <?=$data[name]?> 
    <td> <?=$data[hphone]?> 
    <td> <?=$l[$data[location]]?>  
    <td> 
      <a href=step.php?step=-1&no=<?=$data[no]?>> ←</a> 
      <a href=step.php?step=1&no=<?=$data[no]?>> →</a>
    <?
  $total += $data[st_price];
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
<a href=<?=$PHP_SELF?>?start=1<?=$href?>>맨처음</a> 
<? } ?> 

<a href=<?=$PHP_SELF?>?start=<?=$prev?><?=$href?>>[이전]</a> 
<? 

   for($i=$a; $i<=$b; $i++){ 

     if($start==$i) {?> 
        <b><?=$i?></b> 
     <? }else{  ?> 
       <a href=<?=$PHP_SELF?>?start=<?=$i?><?=$href?>>[<?=$i?>]</a> 
   <? } ?> 

<? } ?> 

<? if($next!=$pages){ ?> 
<a href=<?=$PHP_SELF?>?start=<?=$next?><?=$href?>>[다음]</a> 
<? } ?> 

<a href=<?=$PHP_SELF?>?start=<?=$pages?><?=$href?>>맨끝</a> 
