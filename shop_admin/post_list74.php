<?
  include "lib.php";
  $connect = dbconn();
  $DbDir="..";
  $GGTABLE="mlangprintauto_transactionCate";
  $l[1] = "주문접수";
  $l[2] = "입금확인";
  $l[3] = "작업중";
  $l[4] = "배송중";
  $l[0] = "주문취소";

if(!$start) $start = 1;

  // 검색 파라미터 받기 (PHP 7.4)
  $search_name = trim($_GET['search_name'] ?? '');
  $search_company = trim($_GET['search_company'] ?? '');
  $search_date_start = trim($_GET['search_date_start'] ?? '');
  $search_date_end = trim($_GET['search_date_end'] ?? '');

  // WHERE 조건 구성
  $where_conditions = [];
  $where_conditions[] = "(zip1 like '%구%' ) or (zip2 like '%-%')";

  if($search_name !== '') {
    $search_name = mysqli_real_escape_string($connect, $search_name);
    $where_conditions[] = "name like '%$search_name%'";
  }

  if($search_company !== '') {
    $search_company = mysqli_real_escape_string($connect, $search_company);
    $where_conditions[] = "company like '%$search_company%'";
  }

  if($search_date_start !== '' && $search_date_end !== '') {
    $search_date_start = mysqli_real_escape_string($connect, $search_date_start);
    $search_date_end = mysqli_real_escape_string($connect, $search_date_end);
    $where_conditions[] = "Date >= '$search_date_start' and Date <= '$search_date_end'";
  } elseif($search_date_start !== '') {
    $search_date_start = mysqli_real_escape_string($connect, $search_date_start);
    $where_conditions[] = "Date >= '$search_date_start'";
  } elseif($search_date_end !== '') {
    $search_date_end = mysqli_real_escape_string($connect, $search_date_end);
    $where_conditions[] = "Date <= '$search_date_end'";
  }

  $where_sql = implode(' and ', $where_conditions);

  // 전체 페이지 구하기
  $query = "select count(*) from mlangorder_printauto where $where_sql";
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

  // 검색 파라미터를 URL에 추가하기 위한 변수
  $search_params = '';
  if($search_name !== '') $search_params .= "&search_name=" . urlencode($search_name);
  if($search_company !== '') $search_params .= "&search_company=" . urlencode($search_company);
  if($search_date_start !== '') $search_params .= "&search_date_start=" . urlencode($search_date_start);
  if($search_date_end !== '') $search_params .= "&search_date_end=" . urlencode($search_date_end);

  //$query = "select * from mlangorder_printauto order by no desc";
  //$query = "select * from mlangorder_printauto where (zip1 like '%구%' ) or (zip2 like '%-%') order by no desc";
  $query = "select * from mlangorder_printauto where $where_sql order by no desc";
  $query .= " limit $s, $pagenum ";
  echo $query;
  $result = mysqli_query($connect, $query);
  ?>

<!-- 검색 폼 추가 -->
<form method="get" action="<?php echo $_SERVER['PHP_SELF']?>" id="searchForm">
<table border="1" cellpadding="5" cellspacing="0" style="margin-bottom:10px;">
  <tr>
    <td bgcolor="#CCCCCC"><b>검색</b></td>
    <td>
      이름: <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name)?>" size="15">
      상호: <input type="text" name="search_company" value="<?php echo htmlspecialchars($search_company)?>" size="15">
      기간: <input type="text" name="search_date_start" value="<?php echo htmlspecialchars($search_date_start)?>" size="10" placeholder="YYYY-MM-DD">
      ~
      <input type="text" name="search_date_end" value="<?php echo htmlspecialchars($search_date_end)?>" size="10" placeholder="YYYY-MM-DD">
      <input type="submit" value="검색">
      <input type="button" value="초기화" onclick="location.href='<?php echo $_SERVER['PHP_SELF']?>'">
      <input type="button" value="엑셀 다운로드" onclick="exportToExcel()" style="background-color:#28a745; color:white; font-weight:bold;">
    </td>
  </tr>
</table>
</form>

<script>
function exportToExcel() {
  var form = document.getElementById('searchForm');
  var originalAction = form.action;
  form.action = 'export_excel74.php';
  form.target = '_blank';
  form.submit();
  form.action = originalAction;
  form.target = '';
}
</script> 
<table width=100% border=1> 
  <tr bgcolor="#99CCFF"> 
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
  while($data = mysqli_fetch_array($result)){ 
?> 
<? if(preg_match("16절",$data['Type_1'])){ // 16절만 2박스박스계산및 박스당가격
			$r=2;
			$w=3000;} else
	if(preg_match("a4",$data['Type_1'])){
		    $r=1;
			$w=4000;}else
	if(preg_match("a5",$data['Type_1'])){
		    $r=1;
			$w=4000;} else
	if(preg_match("NameCard",$data['Type'])){
		    $r=1;
			$w=2500;} else
	if(preg_match("MerchandiseBond",$data['Type'])){
		    $r=1;
			$w=2500;} else
	if(preg_match("sticker",$data['Type'])){
		    $r=1;
			$w=2500;}
	if(preg_match("스티카",$data['Type'])){
		    $r=1;
			$w=2500;} else
	if(preg_match("envelop",$data['Type'])){
		    $r=1;
			$w=3000;
	} 
			
			?>
  <tr> 
    <td> <?php echo $data['name']?> 
    <td> <?php echo $data['zip']?> 
    <td> <?php echo $data['zip1']?> <?php echo $data['zip2']?>
    <td> <?php echo $data['phone']?>
    <td width="120">  <a href="http://www.webhard.co.kr/webII/page/sms/main_sms.php"><?php echo $data['Hendphone']?></a> 
    <td align='center'> <?php echo  $r; ?> </td>
           
    <td>  <?php echo  $w; ?>
    <td> 착불
    <td> <?php echo $data['Type_1']?> 
    <td> &nbsp;    
    <td> <?php echo $data['Type']?>    
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
<a href=<?php echo $PHP_SELF?>?start=1<?php echo $search_params?>>맨처음</a>
<? } ?>

<a href=<?php echo $PHP_SELF?>?start=<?php echo $prev?><?php echo $search_params?>>[이전]</a>
<?

   for($i=$a; $i<=$b; $i++){

     if($start==$i) {?>
        <b><?php echo $i?></b>
     <? }else{  ?>
       <a href=<?php echo $PHP_SELF?>?start=<?php echo $i?><?php echo $search_params?>>[<?php echo $i?>]</a>
   <? } ?>

<? } ?>

<? if($next!=$pages){ ?>
<a href=<?php echo $PHP_SELF?>?start=<?php echo $next?><?php echo $search_params?>>[다음]</a>
<? } ?>

<a href=<?php echo $PHP_SELF?>?start=<?php echo $pages?><?php echo $search_params?>>맨끝</a>
?>