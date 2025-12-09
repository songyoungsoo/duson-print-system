<?php
// PHP 5.2 호환 버전 - EUC-KR
include "lib.php";
$connect = dbconn();

$DbDir="..";
$GGTABLE="mlangprintauto_transactionCate";
$l[1] = "주문접수";
$l[2] = "입금확인";
$l[3] = "작업중";
$l[4] = "배송중";
$l[0] = "주문취소";

$start = isset($_GET['start']) ? $_GET['start'] : 1;
if(!$start) $start = 1;
$PHP_SELF = $_SERVER['PHP_SELF'];

// 검색 파라미터 받기 (PHP 5.2)
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건 생성
$where_conditions = array();
$where_conditions[] = "(zip1 like '%구%' ) or (zip2 like '%-%')";

if($search_name != '') {
  $search_name_esc = mysql_real_escape_string($search_name);
  $where_conditions[] = "name like '%$search_name_esc%'";
}

if($search_company != '') {
  $search_company_esc = mysql_real_escape_string($search_company);
  $where_conditions[] = "company like '%$search_company_esc%'";
}

if($search_date_start != '' && $search_date_end != '') {
  $search_date_start_esc = mysql_real_escape_string($search_date_start);
  $search_date_end_esc = mysql_real_escape_string($search_date_end);
  $where_conditions[] = "date >= '$search_date_start_esc' and date <= '$search_date_end_esc'";
} else if($search_date_start != '') {
  $search_date_start_esc = mysql_real_escape_string($search_date_start);
  $where_conditions[] = "date >= '$search_date_start_esc'";
} else if($search_date_end != '') {
  $search_date_end_esc = mysql_real_escape_string($search_date_end);
  $where_conditions[] = "date <= '$search_date_end_esc'";
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
$query = "select count(*) from MlangOrder_PrintAuto where $where_sql";
$result = mysql_query($query, $connect);
if (!$result) {
    die("Query Error: " . mysql_error() . "<br>Query: " . $query);
}
$data = mysql_fetch_array($result);
$total = $data[0];

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

$query = "select * from MlangOrder_PrintAuto where $where_sql order by no desc";
$query .= " limit $s, $pagenum ";
$result = mysql_query($query, $connect);
if (!$result) {
    die("<br>Query Error: " . mysql_error() . "<br>Query: " . $query);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<title>주문 목록 - 로젠주소추출</title>
<style>
td,input,li{font-size:9pt}
.btn-logen {
    background-color: #03C75A;
    color: white;
    font-weight: bold;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-left: 5px;
}
.btn-logen:hover {
    background-color: #02a849;
}
</style>
</head>
<body>

<li> 총 게시물수 : <?php echo $total ?>

<!-- 검색 폼 추가 -->
<form method="get" action="<?php echo $PHP_SELF?>" id="searchForm">
<table border="1" cellpadding="5" cellspacing="0" style="margin-bottom:10px;">
  <tr>
    <td bgcolor="#CCCCCC"><b>검색</b></td>
    <td>
      이름: <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name)?>" size="8">
      회사: <input type="text" name="search_company" value="<?php echo htmlspecialchars($search_company)?>" size="8">
      날짜: <input type="text" name="search_date_start" value="<?php echo htmlspecialchars($search_date_start)?>" size="8" placeholder="YYYY-MM-DD">~<input type="text" name="search_date_end" value="<?php echo htmlspecialchars($search_date_end)?>" size="8" placeholder="YYYY-MM-DD">
      주문번호: <input type="text" name="search_no_start" value="<?php echo htmlspecialchars($search_no_start)?>" size="6">~<input type="text" name="search_no_end" value="<?php echo htmlspecialchars($search_no_end)?>" size="6"><br>
      <input type="submit" value="검색">
      <input type="button" value="초기화" onclick="location.href='<?php echo $PHP_SELF?>'">
      <br>
      <input type="button" value=" 로젠택배 CSV (선택)" onclick="exportSelectedToLogen()" class="btn-logen">
      <input type="button" value=" 로젠택배 CSV (전체)" onclick="exportAllToLogen()" class="btn-logen">
      <input type="button" value=" 로젠택배 엑셀 (선택)" onclick="exportSelectedToLogenExcel()" class="btn-logen" style="background-color:#1976D2;">
      <input type="button" value=" 로젠택배 엑셀 (전체)" onclick="exportAllToLogenExcel()" class="btn-logen" style="background-color:#1976D2;">
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

// 로젠택배 CSV 양식 다운로드 함수
function exportSelectedToLogen() {
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
  form.action = 'export_logen_format.php';
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

function exportAllToLogen() {
  var form = document.getElementById('searchForm');
  var originalAction = form.action;
  var originalMethod = form.method;
  var originalTarget = form.target;

  form.action = 'export_logen_format.php';
  form.method = 'get';
  form.target = '_blank';
  form.submit();

  form.action = originalAction;
  form.method = originalMethod;
  form.target = originalTarget;
}

// 로젠택배 엑셀 양식 다운로드 함수
function exportSelectedToLogenExcel() {
  var checkboxes = document.getElementsByName('selected_no[]');
  var selected = [];
  var boxQty = {};
  var deliveryFee = {};
  var feeType = {};

  for(var i=0; i<checkboxes.length; i++) {
    if(checkboxes[i].checked) {
      var no = checkboxes[i].value;
      selected.push(no);
      // 수정된 값 수집
      var qtyInput = document.getElementsByName('box_qty[' + no + ']')[0];
      var feeInput = document.getElementsByName('delivery_fee[' + no + ']')[0];
      var typeSelect = document.getElementsByName('fee_type[' + no + ']')[0];
      if(qtyInput) boxQty[no] = qtyInput.value;
      if(feeInput) deliveryFee[no] = feeInput.value;
      if(typeSelect) feeType[no] = typeSelect.value;
    }
  }

  if(selected.length === 0) {
    alert('다운로드할 항목을 선택해주세요.');
    return;
  }

  var form = document.createElement('form');
  form.method = 'POST';
  form.action = 'export_logen_excel.php';
  form.target = '_blank';

  var input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'selected_nos';
  input.value = selected.join(',');
  form.appendChild(input);

  // 수정된 값도 전송
  var inputQty = document.createElement('input');
  inputQty.type = 'hidden';
  inputQty.name = 'box_qty_json';
  inputQty.value = jsonStringify(boxQty);
  form.appendChild(inputQty);

  var inputFee = document.createElement('input');
  inputFee.type = 'hidden';
  inputFee.name = 'delivery_fee_json';
  inputFee.value = jsonStringify(deliveryFee);
  form.appendChild(inputFee);

  var inputType = document.createElement('input');
  inputType.type = 'hidden';
  inputType.name = 'fee_type_json';
  inputType.value = jsonStringify(feeType);
  form.appendChild(inputType);

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}

function exportAllToLogenExcel() {
  var form = document.getElementById('searchForm');
  var originalAction = form.action;
  var originalMethod = form.method;
  var originalTarget = form.target;

  form.action = 'export_logen_excel.php';
  form.method = 'get';
  form.target = '_blank';
  form.submit();

  form.action = originalAction;
  form.method = originalMethod;
  form.target = originalTarget;
}

// 간단한 JSON.stringify 대체 (IE6/7 호환)
function jsonStringify(obj) {
  var parts = [];
  for (var key in obj) {
    if (obj.hasOwnProperty(key)) {
      parts.push('"' + key + '":"' + obj[key] + '"');
    }
  }
  return '{' + parts.join(',') + '}';
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
  $row_count = 0;
  while($data = mysql_fetch_array($result)){
    // Type_1이 JSON인지 확인하고 파싱
    $type1_display = isset($data['Type_1']) ? $data['Type_1'] : '';
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    if (!empty($data['Type_1']) && substr(trim($data['Type_1']), 0, 1) === '{') {
        $json_data = json_decode($data['Type_1'], true);
        if ($json_data && isset($json_data['formatted_display'])) {
            $type1_display = nl2br(htmlspecialchars($json_data['formatted_display']));
        }
    }
?>
<?php
// 박스 하드코딩 계산 (기존유지)
$r = 1; $w = 3000; // 기본값
if(preg_match("/16/i", $type1_raw)){
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
    $r=1; $w=2500;
} elseif(preg_match("/envelop/i", $data['Type'])){
    $r=1; $w=3000;
}
?>
  <tr>
    <td><input type="checkbox" name="selected_no[]" value="<?php echo $data['no']?>"></td>
    <td><?php echo htmlspecialchars(isset($data['no']) ? $data['no'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['date']) ? $data['date'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['name']) ? $data['name'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['zip']) ? $data['zip'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['zip1']) ? $data['zip1'] : '')?> <?php echo htmlspecialchars(isset($data['zip2']) ? $data['zip2'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['phone']) ? $data['phone'] : '')?></td>
    <td width="120"><a href="http://www.webhard.co.kr/webII/page/sms/main_sms.php"><?php echo htmlspecialchars(isset($data['Hendphone']) ? $data['Hendphone'] : '')?></a></td>
    <td align='center'><input type="text" name="box_qty[<?php echo $data['no']?>]" value="<?php echo $r; ?>" size="2" style="text-align:center;"></td>
    <td><input type="text" name="delivery_fee[<?php echo $data['no']?>]" value="<?php echo $w; ?>" size="5"></td>
    <td><select name="fee_type[<?php echo $data['no']?>]" style="font-size:9pt;">
      <option value="착불" selected>착불</option>
      <option value="신용">신용</option>
      <option value="퀵">퀵</option>
    </select></td>
    <td><?php echo $type1_display?></td>
    <td>&nbsp;</td>
    <td><?php echo htmlspecialchars(isset($data['Type']) ? $data['Type'] : '')?></td>
  </tr>
  <?php

 } ?>
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
<a href="<?php echo $PHP_SELF?>?start=1<?php echo $search_params?>">맨처음</a>
<?php } ?>

<a href="<?php echo $PHP_SELF?>?start=<?php echo $prev?><?php echo $search_params?>">[이전]</a>
<?php

   for($i=$a; $i<=$b; $i++){

     if($start==$i) {?>
        <b><?php echo $i?></b>
     <?php }else{  ?>
       <a href="<?php echo $PHP_SELF?>?start=<?php echo $i?><?php echo $search_params?>">[<?php echo $i?>]</a>
   <?php } ?>

<?php } ?>

<?php if($next!=$pages){ ?>
<a href="<?php echo $PHP_SELF?>?start=<?php echo $next?><?php echo $search_params?>">[다음]</a>
<?php } ?>

<a href="<?php echo $PHP_SELF?>?start=<?php echo $pages?><?php echo $search_params?>">맨끝</a>

</body>
</html>
