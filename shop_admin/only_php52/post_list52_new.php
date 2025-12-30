<?php
// PHP 5.2 호환 버전 - EUC-KR
include "lib_mysql.php";
$connect = dbconn();

$DbDir="..";
$GGTABLE="mlangprintauto_transactionCate";
$l[1] = "주문접수";
$l[2] = "입금확인";
$l[3] = "작업중";
$l[4] = "배송중";
$l[0] = "주문취소";

$start = isset($_GET['start']) ? intval($_GET['start']) : 1;
if(!$start) $start = 1;
$PHP_SELF = $_SERVER['PHP_SELF'];

// 검색 파라미터 받기
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건 생성
$base_condition = "((zip1 LIKE '%구%') OR (zip2 LIKE '%-%'))";
$search_conditions = array();

if($search_name != '') {
  $search_name_esc = mysql_real_escape_string($search_name, $connect);
  $search_conditions[] = "name LIKE '%$search_name_esc%'";
}

if($search_company != '') {
  $search_company_esc = mysql_real_escape_string($search_company, $connect);
  $search_conditions[] = "company LIKE '%$search_company_esc%'";
}

if($search_date_start != '' && $search_date_end != '') {
  $search_date_start_esc = mysql_real_escape_string($search_date_start, $connect);
  $search_date_end_esc = mysql_real_escape_string($search_date_end, $connect);
  $search_conditions[] = "date >= '$search_date_start_esc' AND date <= '$search_date_end_esc'";
} else if($search_date_start != '') {
  $search_date_start_esc = mysql_real_escape_string($search_date_start, $connect);
  $search_conditions[] = "date >= '$search_date_start_esc'";
} else if($search_date_end != '') {
  $search_date_end_esc = mysql_real_escape_string($search_date_end, $connect);
  $search_conditions[] = "date <= '$search_date_end_esc'";
}

// 주문번호 범위 검색 추가
if($search_no_start != '' && $search_no_end != '') {
  $no_start = intval($search_no_start);
  $no_end = intval($search_no_end);
  $search_conditions[] = "no >= $no_start AND no <= $no_end";
} else if($search_no_start != '') {
  $no_start = intval($search_no_start);
  $search_conditions[] = "no >= $no_start";
} else if($search_no_end != '') {
  $no_end = intval($search_no_end);
  $search_conditions[] = "no <= $no_end";
}

// WHERE 절 생성
if(count($search_conditions) > 0) {
  $where_sql = $base_condition . ' AND (' . implode(' AND ', $search_conditions) . ')';
} else {
  $where_sql = $base_condition;
}

// 전체 페이지 구하기
$query = "select count(*) from MlangOrder_PrintAuto where $where_sql";
$result = mysql_query($query, $connect);
$data = mysql_fetch_array($result);
$total = $data[0];

// 한화면에 표시될 페이지수
$pagenum = 20;

// 총페이지수
$pages = round($total / $pagenum);

// 시작변수
$s = $pagenum * ($start-1);

// 검색 파라미터를 URL에 추가
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
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<title>로젠 주소 목록</title>
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
<table border="1" cellpadding="3" cellspacing="0" style="margin-bottom:10px; border-collapse: collapse;">
  <tr>
    <td bgcolor="#CCCCCC" style="padding: 5px;"><b>검색</b></td>
    <td style="padding: 5px;">
      이름: <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name)?>" size="6">
      회사: <input type="text" name="search_company" value="<?php echo htmlspecialchars($search_company)?>" size="6">
      날짜: <input type="text" name="search_date_start" value="<?php echo htmlspecialchars($search_date_start)?>" size="10" placeholder="YYYY-MM-DD">~<input type="text" name="search_date_end" value="<?php echo htmlspecialchars($search_date_end)?>" size="10" placeholder="YYYY-MM-DD">
      주문번호: <input type="text" name="search_no_start" value="<?php echo htmlspecialchars($search_no_start)?>" size="5">~<input type="text" name="search_no_end" value="<?php echo htmlspecialchars($search_no_end)?>" size="5">
      <input type="submit" value="검색">
      <input type="button" value="초기화" onclick="location.href='<?php echo $PHP_SELF?>'">
    </td>
  </tr>
  <tr>
    <td bgcolor="#CCCCCC" style="padding: 5px;"><b>선택항목</b></td>
    <td style="padding: 5px;">
      <input type="button" value="로젠택배 엑셀 (선택)" onclick="exportSelectedToLogenExcel()" class="btn-logen" style="background-color:#1976D2;">
      <input type="button" value="로젠택배 엑셀 (전체)" onclick="exportAllToLogenExcel()" class="btn-logen" style="background-color:#1976D2;">
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
      var qtyInput = document.getElementById('box_qty_' + no);
      var feeInput = document.getElementById('delivery_fee_' + no);
      var typeSelect = document.getElementById('fee_type_' + no);
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
  inputQty.value = JSON.stringify(boxQty);
  form.appendChild(inputQty);

  var inputFee = document.createElement('input');
  inputFee.type = 'hidden';
  inputFee.name = 'delivery_fee_json';
  inputFee.value = JSON.stringify(deliveryFee);
  form.appendChild(inputFee);

  var inputType = document.createElement('input');
  inputType.type = 'hidden';
  inputType.name = 'fee_type_json';
  inputType.value = JSON.stringify(feeType);
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
</script>

<form id="listForm">
<table width=100% border="1" cellpadding="3" cellspacing="0" style="border-collapse: collapse;">
  <tr bgcolor="#99CCFF">
    <td style="padding: 3px;"><input type="checkbox" onclick="toggleAll(this)"></td>
    <td style="padding: 3px;"> 주문번호
    <td style="padding: 3px;"> 날짜
    <td style="padding: 3px;"> 수하인명
    <td style="padding: 3px;"> 우편번호
    <td style="padding: 3px;"> 주소
    <td style="padding: 3px;"> 전화
    <td style="padding: 3px;"> 핸드폰
    <td style="padding: 3px;"> 박스수량
    <td style="padding: 3px;"> 택배비
    <td style="padding: 3px;"> 운임구분
    <td style="padding: 3px;"> Type
    <td style="padding: 3px;"> 기타
    <td style="padding: 3px;"> 품목

<?php
  $row_count = 0;
  while($data = mysql_fetch_array($result)){
    // Type_1이 JSON인지 확인하고 파싱
    $type1_display = isset($data['Type_1']) ? $data['Type_1'] : '';
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    if (!empty($data['Type_1']) && substr(trim($data['Type_1']), 0, 1) === '{') {
        $json_data = json_decode($data['Type_1'], true);
        if ($json_data && isset($json_data['formatted_display'])) {
            // 줄바꿈 제거하고 공백으로 변경
            $type1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $json_data['formatted_display']);
            $type1_display = htmlspecialchars($type1_display);
        }
    }
?>
<?php
// 박스 하드코딩 계산
$r = 1; $w = 3000; // 기본값
if(preg_match("/16절/i", $type1_raw)){
    $r=2; $w=3000;
} elseif(preg_match("/a4/i", $type1_raw)){
    $r=1; $w=4000;
} elseif(preg_match("/a5/i", $type1_raw)){
    $r=1; $w=4000;
} elseif(preg_match("/NameCard/i", $data['Type'])){
    $r=1; $w=3000;
} elseif(preg_match("/MerchandiseBond/i", $data['Type'])){
    $r=1; $w=3000;
} elseif(preg_match("/sticker/i", $data['Type'])){
    $r=1; $w=3000;
} elseif(preg_match("/envelop/i", $data['Type'])){
    $r=1; $w=3000;
}
$no = $data['no'];
?>
  <tr>
    <td style="padding: 3px;"><input type="checkbox" name="selected_no[]" value="<?php echo $no?>"></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($no)?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars(isset($data['date']) ? $data['date'] : '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars(isset($data['name']) ? $data['name'] : '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars(isset($data['zip']) ? $data['zip'] : '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars(isset($data['zip1']) ? $data['zip1'] : '')?> <?php echo htmlspecialchars(isset($data['zip2']) ? $data['zip2'] : '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars(isset($data['phone']) ? $data['phone'] : '')?></td>
    <td style="padding: 3px;" width="120"><?php echo htmlspecialchars(isset($data['Hendphone']) ? $data['Hendphone'] : '')?></td>
    <td style="padding: 3px;" align='center'><input type="text" id="box_qty_<?php echo $no?>" name="box_qty[<?php echo $no?>]" value="<?php echo $r; ?>" size="2" style="text-align:center;"></td>
    <td style="padding: 3px;"><input type="text" id="delivery_fee_<?php echo $no?>" name="delivery_fee[<?php echo $no?>]" value="<?php echo $w; ?>" size="5"></td>
    <td style="padding: 3px;"><select id="fee_type_<?php echo $no?>" name="fee_type[<?php echo $no?>]" style="font-size:9pt;">
      <option value="착불" selected>착불</option>
      <option value="신용">신용</option>
    </select></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars(isset($data['Type']) ? $data['Type'] : '')?></td>
    <td style="padding: 3px;"><?php echo $no?></td>
    <td style="padding: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"><?php echo $type1_display?></td>
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
