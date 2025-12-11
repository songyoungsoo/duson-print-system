<? 
  include "lib.php"; 
  if (!isset($_SESSION['isLogin'])) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['isLogin']['email'];

$query = "SELECT * FROM mlangorder_printauto WHERE email='" . mysql_real_escape_string($userEmail) . "'";
$result = mysql_query($query, $connect);

if (!$result) {
    die("쿼리 실행에 실패했습니다: " . mysql_error());
}
   
$l[1] = "주문접수";
$l[2] = "입금확인";
$l[3] = "작업중";
$l[4] = "배송중";
$l[0] = "주문취소";
  
$start = 0; 
if(!$start) $start = 1; 

  // 전체 페이지 구하기 
  $query = "SELECT count(*) from mlangorder_printauto WHERE email='$userEmail'";
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


  $query = "SELECT * from mlangorder_printauto WHERE email='$userEmail' order by no desc"; 
  $query .= " limit $s, $pagenum "; 
  echo $query; 
  $result = mysql_query($query, $connect);
  ?> 
<table width=100%  align='center' width=600 border='0' cellspacing='1' cellpadding='2' bgcolor='#000000'> 
<tr> 
<th> NO 
<th> NAME 
<th> CONTENT 
<th> Total Amaunt
<th> DATE
</tr>
<? 
  $total = 0; 
  while($data = mysql_fetch_array($result)){ 
?>
<?php
$name = iconv('EUC-KR','UTF-8', $data['name']);
$Type_1 = iconv('EUC-KR','UTF-8', $data['Type_1']);
?>

  <tr> 
  <td bgcolor='#FFFFFF'>  <a href="./order_view_my.php?no=<?= $data['no'] ?>"><?= $data['no'] ?></a> 
  <td bgcolor='#FFFFFF'> <?=$name ?> 
  <td bgcolor='#FFFFFF'> <?=$Type_1 ?> 
  <td bgcolor='#FFFFFF'> <?=$data['money_4']?>  
  <td bgcolor='#FFFFFF'> <?=$data['date']?>
</tr>
 <? } ?>  
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
<a href="<?= $_SERVER['PHP_SELF'] ?>?start=1<?=$href?>">맨처음</a> 
<? } ?> 

<a href="<?= $_SERVER['PHP_SELF'] ?>?start=<?= $prev ?><?= $href ?>">[이전]</a>

<? 

   for($i=$a; $i<=$b; $i++){ 

     if($start==$i) {?> 
        <b><?=$i?></b> 
     <? }else{  ?> 
		<a href="<?= $_SERVER['PHP_SELF'] ?>?start=<?=$i?><?=$href?>">[<?=$i?>]</a> 
   <? } ?> 

<? } ?> 

<?php if ($next != $pages) { ?>
<a href="<?= $_SERVER['PHP_SELF'] ?>?start=<?= $next ?><?= $href ?>">[다음]</a>
<?php } ?>

<a href="<?= $_SERVER['PHP_SELF'] ?>?start=<?= $pages ?><?= $href ?>">맨끝</a>
