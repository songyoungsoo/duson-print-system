<?php
  include "lib.php";
  // 주문 데이터가 있는 dsp1830 DB 연결
  require_once __DIR__ . '/../db.php';
  $connect = $db;
   
  $l[1] = "주문접수"; 
  $l[2] = "입금확인"; 
  $l[3] = "작업중"; 
  $l[4] = "배송중"; 
  $l[0] = "주문취소"; 
  
$start = $_GET['start'] ?? 1;
if(!$start) $start = 1;
$PHP_SELF = $_SERVER['PHP_SELF'];
$href = ''; 

  // 전체 페이지 구하기 
  $query = "select count(*) from mlangorder_printauto"; 
  $result = mysqli_query($connect, $query); 
  $data = mysqli_fetch_array($result); 
  $total = $data[0]; 

  echo "<li> 총 게시물수 : $total  "; 

   // 한화면에 표시될 페이지수 
  $pagenum = 10; 

  // 총페이지수 
  $pages = round($total / $pagenum); 

  // 시작변수 
  $s = $pagenum * ($start-1); 


  $query = "select * from mlangorder_printauto order by no desc"; 
  //$query = "select * from mlangorder_printauto where (zip1 like '% $zip1 %') or (zip2 like '%-%') order by no desc";   
  $query .= " limit $s, $pagenum "; 
  echo $query; 
  $result = mysqli_query($connect, $query);
  ?> 
<table width=100% border=1> 
  <tr bgcolor="#99CCFF"> 
    <td > 수하인명 
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
<?php
  $total = 0;
  while($data = mysqli_fetch_array($result)){
    // Type_1이 JSON인지 확인하고 파싱
    $type1_display = $data['Type_1'];
    $type1_raw = $data['Type_1']; // 박스수량/택배비 계산용 원본 값

    if (!empty($data['Type_1']) && substr(trim($data['Type_1']), 0, 1) === '{') {
        $json_data = json_decode($data['Type_1'], true);
        if ($json_data && isset($json_data['formatted_display'])) {
            // 줄바꿈을 <br>로 변환
            $type1_display = nl2br(htmlspecialchars($json_data['formatted_display']));
        }
    }
?>
 <?php
    $r = 1; $w = 3000; // 기본값
    if(preg_match("/16절/i", $type1_raw)){
        $r=2; $w=3000;
    } elseif(preg_match("/a4/i", $type1_raw)){
        $r=1; $w=4000;
    } elseif(preg_match("/a5/i", $type1_raw)){
        $r=1; $w=4000;
    } elseif(preg_match("/NameCard/i", $data['Type'])){
        $r=1; $w=2500;
    } elseif(preg_match("/MerchandiseBond/i", $data['Type'])){
        $r=1; $w=2500;
    } elseif(preg_match("/sticker/i", $data['Type'])){
        $r=1; $w=3000;
    } elseif(preg_match("/envelop/i", $data['Type'])){
        $r=1; $w=3000;
    }
?>
  <tr>
    <td> <?php echo htmlspecialchars($data['name'] ?? '')?>
    <td> <?php echo htmlspecialchars($data['zip'] ?? '')?>
    <td> <?php echo htmlspecialchars($data['zip1'] ?? '')?> <?php echo htmlspecialchars($data['zip2'] ?? '')?>
    <td> <?php echo htmlspecialchars($data['phone'] ?? '')?>
    <td> <?php echo htmlspecialchars($data['Hendphone'] ?? '')?>
    <td align='center'> <?php echo $r; ?> </td>
    <td> <?php echo $w; ?>
    <td> 착불
    <td> <?php echo $type1_display?>
    <td> <?php echo htmlspecialchars($data['cont'] ?? '')?>
    <td> <?php echo htmlspecialchars($data['Type'] ?? '')?>
      <?php

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
?>