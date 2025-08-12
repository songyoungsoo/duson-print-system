<?php
session_start();
$session_id = session_id();
include $_SERVER['DOCUMENT_ROOT'] ."/db.php"; 
include $_SERVER['DOCUMENT_ROOT'] ."/MlangPrintAuto/MlangPrintAutoTop_s.php";
$userid = isset($_SESSION['id_login_ok']) ? $_SESSION['id_login_ok']['id'] : false;
$query = "SELECT * FROM member WHERE id ='" . mysqli_real_escape_string($userid) . "'";
$result = mysqli_query($query, $db);
$data = mysqli_fetch_array($result);
$userEmail = $data['email'];
// echo $data; exit;
if (!$result) {
  die("쿼리 실행에 실패했습니다: " . mysqli_error());
}


// }
   
$l[1] = "주문접수";
$l[2] = "입금확인";
$l[3] = "작업중";
$l[4] = "배송중";
$l[0] = "주문취소";
  
$start = 0; 
if(!$start) $start = 1; 

  // 전체 페이지 구하기 
  $query = "SELECT count(*) from MlangOrder_PrintAuto WHERE email='$userEmail'";
  $result = mysqli_query($query, $db); 
  $data = mysqli_fetch_array($result); 
  $total = $data[0]; 

  echo "<li>Total : $total  "; 

   // 한화면에 표시될 페이지수 
  $pagenum = 10; 

  // 총페이지수 
  $pages = round($total / $pagenum); 

  // 시작변수 
  $s = $pagenum * ($start-1); 


  $query = "SELECT * from MlangOrder_PrintAuto WHERE email='$userEmail' order by no desc"; 
  $query .= " limit $s, $pagenum "; 
  // echo $query;?> 
  <br>
  <?php $result = mysqli_query($query, $db);
  ?> 
  <br>
<table align='center' width=680 border='0' cellspacing='1' cellpadding='2' bgcolor='#000000'> 
<tr> 
<th> NO 
<th> 이름
<th> 주문내용 
<th> 총금액
<th> 일자
</tr>
<? 
  $total = 0; 
  while($data = mysqli_fetch_array($result)){ 
?>
<?php
$name =  $data['name'];
$Type_1 =  $data['Type_1'];
?>

  <tr> 
  <td width=6% bgcolor='#FFFFFF'>  <a href="./order_view_my.php?no=<?php echo  $data['no'] ?>"><?php echo  $data['no'] ?></a> 
  <td width=15% bgcolor='#FFFFFF'> <?php echo $name ?> 
  <td width=*% bgcolor='#FFFFFF'> <?php echo $Type_1 ?> 
  <td width=7% bgcolor='#FFFFFF'> <?php echo $data['money_4']?>  
  <td width=18% bgcolor='#FFFFFF'> <?php echo $data['date']?>
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
<a href="<?php echo  $_SERVER['PHP_SELF'] ?>?start=1<?php echo $href?>">first</a> 
<? } ?> 

<a href="<?php echo  $_SERVER['PHP_SELF'] ?>?start=<?php echo  $prev ?><?php echo  $href ?>">[pre]</a>

<? 

   for($i=$a; $i<=$b; $i++){ 

     if($start==$i) {?> 
        <b><?php echo $i?></b> 
     <? }else{  ?> 
		<a href="<?php echo  $_SERVER['PHP_SELF'] ?>?start=<?php echo $i?><?php echo $href?>">[<?php echo $i?>]</a> 
   <? } ?> 

<? } ?> 

<?php if ($next != $pages) { ?>
<a href="<?php echo  $_SERVER['PHP_SELF'] ?>?start=<?php echo  $next ?><?php echo  $href ?>">[next]</a>
<?php } ?>

<a href="<?php echo  $_SERVER['PHP_SELF'] ?>?start=<?php echo  $pages ?><?php echo  $href ?>">[end]</a>
<?php
include $_SERVER['DOCUMENT_ROOT'] ."/MlangPrintAuto/MlangPrintAutoDown.php";
?> 