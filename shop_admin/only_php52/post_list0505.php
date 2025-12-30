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
    <td> 수하인명 
    <td> 우편번호 
    <td> 주소 
    <td> 전화 
    <td> 핸드폰 
    <td> 박스수량 
    <td> 택배비 
    <td> 운임구분 
    <td> 품목명  
    <td> 기타 
    <td> 배송메세지        
<? 
  $total = 0; 
  while($data = mysql_fetch_array($result)){ 
?> 
  <tr> 
    <td> <?=$data[name]?> 
    <td> <?=$data[zip]?> 
    <td> <?=$data[zip1]?> <?=$data[zip2]?>
    <td> <?=$data[phone]?> 
    <td> <?=$data[Hendphone]?> 
    <td> 1   
    <td> &nbsp;
    <td> &nbsp;
    <td> <?=$data[Type_1]?> 
    <td> &nbsp;    
    <td> <?=$data[Type]?>    
      <?

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
