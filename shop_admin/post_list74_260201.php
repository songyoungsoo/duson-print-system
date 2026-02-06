<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>주문 목록</title>
<style>
td,input,li{font-size:9pt}
</style>
</head>
<body>
<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  include "../db.php";
  $connect = $db;
  $DbDir="..";
  $GGTABLE="mlangprintauto_transactionCate";
  $l[1] = "주문접수";
  $l[2] = "입금확인";
  $l[3] = "작업중";
  $l[4] = "배송중";
  $l[0] = "주문취소";

$start = isset($_GET['start']) ? intval($_GET['start']) : 1;
if($start < 1) $start = 1;

  // 검색 파라미터 받기 (PHP 7.4)
  $search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
  $search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
  $search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
  $search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
  $search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
  $search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

  // WHERE 조건 구성
  $where_conditions = array();
  $where_conditions[] = "(zip1 IS NOT NULL AND zip1 != '')";

  if($search_name != '') {
    $search_name = mysqli_real_escape_string($connect, $search_name);
    $where_conditions[] = "name like '%$search_name%'";
  }

  if($search_company != '') {
    $search_company = mysqli_real_escape_string($connect, $search_company);
    $where_conditions[] = "company like '%$search_company%'";
  }

  if($search_date_start != '' && $search_date_end != '') {
    $search_date_start = mysqli_real_escape_string($connect, $search_date_start);
    $search_date_end = mysqli_real_escape_string($connect, $search_date_end);
    $where_conditions[] = "date >= '$search_date_start' and date <= '$search_date_end'";
  } else if($search_date_start != '') {
    $search_date_start = mysqli_real_escape_string($connect, $search_date_start);
    $where_conditions[] = "date >= '$search_date_start'";
  } else if($search_date_end != '') {
    $search_date_end = mysqli_real_escape_string($connect, $search_date_end);
    $where_conditions[] = "date <= '$search_date_end'";
  }

  // 주문번호 범위 검색 추가
  if($search_no_start != '' && $search_no_end != '') {
    $search_no_start = intval($search_no_start);
    $search_no_end = intval($search_no_end);
    $where_conditions[] = "no >= $search_no_start and no <= $search_no_end";
  } else if($search_no_start != '') {
    $search_no_start = intval($search_no_start);
    $where_conditions[] = "no >= $search_no_start";
  } else if($search_no_end != '') {
    $search_no_end = intval($search_no_end);
    $where_conditions[] = "no <= $search_no_end";
  }

  $where_sql = implode(' and ', $where_conditions);

  // 전체 페이지 구하기
  $query = "select count(*) from mlangorder_printauto where $where_sql";
  $result = safe_mysqli_query($connect, $query);
  if (!$result) {
      die("Query Error: " . mysqli_error($connect) . "<br>Query: " . $query);
  }
  $data = mysqli_fetch_array($result);
  $total = $data[0];

  echo "<li> 총 게시물수 : $total  ";

   // 한화면에 표시될 페이지수
  $pagenum = 20;

  // 총페이지수
  $pages = round($total / $pagenum);

  // 시작변수
  $s = $pagenum * ($start-1);

  // 검색 파라미터를 URL에 추가하기 위한 변수
  $search_params = '';
  if($search_name != '') $search_params .= "&search_name=" . urlencode($search_name);
  if($search_company != '') $search_params .= "&search_company=" . urlencode($search_company);
  if($search_date_start != '') $search_params .= "&search_date_start=" . urlencode($search_date_start);
  if($search_date_end != '') $search_params .= "&search_date_end=" . urlencode($search_date_end);
  if($search_no_start != '') $search_params .= "&search_no_start=" . urlencode($search_no_start);
  if($search_no_end != '') $search_params .= "&search_no_end=" . urlencode($search_no_end);

  $query = "select * from mlangorder_printauto where $where_sql order by no desc";
  $query .= " limit $s, $pagenum ";
  $result = safe_mysqli_query($connect, $query);
  if (!$result) {
      die("<br>Query Error: " . mysqli_error($connect) . "<br>Query: " . $query);
  }
  ?>

<!-- 검색 폼 추가 -->
<form method="get" action="<?php echo $_SERVER['PHP_SELF']?>" id="searchForm">
<table border="1" cellpadding="5" cellspacing="0" style="margin-bottom:10px;">
  <tr>
    <td bgcolor="#CCCCCC"><b>검색</b></td>
    <td style="white-space:nowrap;">
      이름: <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name)?>" size="8">
      상호: <input type="text" name="search_company" value="<?php echo htmlspecialchars($search_company)?>" size="8">
      날짜: <input type="date" name="search_date_start" value="<?php echo htmlspecialchars($search_date_start)?>">~<input type="date" name="search_date_end" value="<?php echo htmlspecialchars($search_date_end)?>">
      주문번호: <input type="text" name="search_no_start" value="<?php echo htmlspecialchars($search_no_start)?>" size="7">~<input type="text" name="search_no_end" value="<?php echo htmlspecialchars($search_no_end)?>" size="7">
      <input type="submit" value="검색">
      <input type="button" value="초기화" onclick="location.href='<?php echo $_SERVER['PHP_SELF']?>'"><br>
      <input type="button" value="선택 항목 다운로드" onclick="exportSelectedToExcel()" style="background-color:#28a745; color:white; font-weight:bold; margin-top:4px;">
      <input type="button" value="전체 항목 다운로드" onclick="exportAllToExcel()" style="background-color:#007bff; color:white; font-weight:bold; margin-top:4px;">
    </td>
  </tr>
</table>
</form>

<script>
function toggleAll(source) {
  var checkboxes = document.getElementsByName('selected_no[]');
  for(var i=0; i<checkboxes.length; i++) {
    checkboxes[i].checked = source.checked;
  }
}

function exportSelectedToExcel() {
  var checkboxes = document.getElementsByName('selected_no[]');
  var selected = [];
  for(var i=0; i<checkboxes.length; i++) {
    if(checkboxes[i].checked) {
      selected.push(checkboxes[i].value);
    }
  }

  if(selected.length === 0) {
    alert('다운로드할 항목을 선택해주세요.');
    return;
  }

  var form = document.createElement('form');
  form.method = 'POST';
  form.action = 'export_excel74.php';
  form.target = '_blank';

  var input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'selected_nos';
  input.value = selected.join(',');
  form.appendChild(input);

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}

function exportAllToExcel() {
  var form = document.getElementById('searchForm');
  var originalAction = form.action;
  var originalMethod = form.method;
  form.action = 'export_excel74.php';
  form.method = 'get';
  form.target = '_blank';
  form.submit();
  form.action = originalAction;
  form.method = originalMethod;
  form.target = '';
}
</script>

<form id="listForm">
<table width=100% border=1>
  <tr bgcolor="#99CCFF">
    <td><input type="checkbox" onclick="toggleAll(this)"></td>
    <td> 주문번호
    <td> 날짜
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

<?php
  $total = 0;
  while($data = mysqli_fetch_array($result)){
?>
<?php if(preg_match("/16절/",$data['Type_1'])){ 
		$r=2;
		$w=3000;} else
if(preg_match("/a4/",$data['Type_1'])){
	    $r=1;
		$w=4000;}else
if(preg_match("/a5/",$data['Type_1'])){
	    $r=1;
		$w=4000;} else
if(preg_match("/NameCard/",$data['Type'])){
	    $r=1;
		$w=2500;} else
if(preg_match("/MerchandiseBond/",$data['Type'])){
	    $r=1;
		$w=2500;} else
if(preg_match("/sticker/",$data['Type'])){
	    $r=1;
		$w=2500;}
if(preg_match("/스티카/",$data['Type'])){
	    $r=1;
		$w=2500;} else
if(preg_match("/envelop/",$data['Type'])){
	    $r=1;
		$w=3000;
}
?>
  <tr>
    <td><input type="checkbox" name="selected_no[]" value="<?php echo $data['no']?>"></td>
    <td><?php echo $data['no']?></td>
    <td><?php echo isset($data['date']) ? $data['date'] : ''?></td>
    <td><?php
      $name_display = '';
      if (isset($data['name']) && $data['name'] != '0' && !empty($data['name'])) {
        $name_display = htmlspecialchars($data['name']);
      } else if (isset($data['bizname']) && !empty($data['bizname'])) {
        $name_display = htmlspecialchars($data['bizname']);
      } else {
        $name_display = '-';
      }
      echo $name_display;
    ?></td>
    <td><?php echo isset($data['zip']) ? $data['zip'] : ''?></td>
    <td><?php echo (isset($data['zip1']) ? $data['zip1'] : '') . ' ' . (isset($data['zip2']) ? $data['zip2'] : '')?></td>
    <td><?php echo isset($data['phone']) ? $data['phone'] : ''?></td>
    <td width="120"><a href="http://www.webhard.co.kr/webII/page/sms/main_sms.php"><?php echo isset($data['Hendphone']) ? $data['Hendphone'] : ''?></a></td>
    <td align='center'><?php echo  $r; ?></td>
    <td><?php echo  $w; ?></td>
    <td>착불</td>
    <td><?php
      // Type_1이 JSON인 경우 파싱
      $type1_display = $data['Type_1'];
      if (!empty($type1_display) && $type1_display[0] == '{') {
        $json_data = json_decode($type1_display, true);
        if (isset($json_data['formatted_display'])) {
          // 줄바꿈을 | 로 변경하고 "항목명: " 제거
          $formatted = $json_data['formatted_display'];
          $formatted = preg_replace('/^[^:]+:\s*/m', '', $formatted); // 각 줄의 "항목명: " 제거
          $formatted = str_replace("\n", ' | ', $formatted); // 줄바꿈을 | 로 변경
          echo htmlspecialchars($formatted);
        } else {
          echo htmlspecialchars($type1_display);
        }
      } else {
        echo htmlspecialchars($type1_display);
      }
    ?></td>
    <td>&nbsp;</td>
    <td><?php
      // Type이 JSON인 경우 파싱
      $type_display = $data['Type'];
      if (!empty($type_display) && $type_display[0] == '{') {
        $json_data = json_decode($type_display, true);
        if (isset($json_data['formatted_display'])) {
          // 줄바꿈을 | 로 변경하고 "항목명: " 제거
          $formatted = $json_data['formatted_display'];
          $formatted = preg_replace('/^[^:]+:\s*/m', '', $formatted);
          $formatted = str_replace("\n", ' | ', $formatted);
          echo htmlspecialchars($formatted);
        } else if (isset($json_data['product_type'])) {
          echo htmlspecialchars($json_data['product_type']);
        } else {
          echo htmlspecialchars($type_display);
        }
      } else {
        echo htmlspecialchars($type_display);
      }
    ?></td>
  </tr>
<?php } ?>
  </table>
</form>

<hr>

<?php
    $a = $start - 5;
    $b = $start + 5;

    if($a<1) $a = 1;
    if($b>$pages) $b = $pages;

    $prev = $start - 10;
    $next = $start + 10;

    if($prev<=1) $prev = 1;
    if($next>=$pages) $next = $pages;
?>

<?php if($prev!=1){ ?>
<a href=<?php echo $_SERVER['PHP_SELF']?>?start=1<?php echo $search_params?>>맨처음</a>
<?php } ?>

<a href=<?php echo $_SERVER['PHP_SELF']?>?start=<?php echo $prev?><?php echo $search_params?>>[이전]</a>
<?php
   for($i=$a; $i<=$b; $i++){
     if($start==$i) {?>
        <b><?php echo $i?></b>
     <?php }else{  ?>
       <a href=<?php echo $_SERVER['PHP_SELF']?>?start=<?php echo $i?><?php echo $search_params?>>[<?php echo $i?>]</a>
   <?php } ?>
<?php } ?>

<?php if($next!=$pages){ ?>
<a href=<?php echo $_SERVER['PHP_SELF']?>?start=<?php echo $next?><?php echo $search_params?>>[다음]</a>
<?php } ?>

<a href=<?php echo $_SERVER['PHP_SELF']?>?start=<?php echo $pages?><?php echo $search_params?>>맨끝</a>
?>
</body>
</html>
