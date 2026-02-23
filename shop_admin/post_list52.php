<?php
// PHP 7.4 호환 버전 - UTF-8
include "lib.php";
// 주문 데이터가 있는 dsp1830 DB 연결
require_once __DIR__ . '/../db.php';
$connect = $db;

// 규격별 택배비 룩업 (delivery_manager.php와 동일)
$shipping_rules = [
    'A6'  => ['boxes' => 1, 'cost' => 4000],
    'B6'  => ['boxes' => 1, 'cost' => 4000],
    'A5'  => ['boxes' => 1, 'cost' => 6000],
    'B5'  => ['boxes' => 2, 'cost' => 7000],
    'A4'  => ['boxes' => 1, 'cost' => 6000],
    'B4'  => ['boxes' => 2, 'cost' => 12000],
    'A3'  => ['boxes' => 2, 'cost' => 12000],
];

// 택배비 자동 계산 (규격+연수 기반, delivery_manager.php calcShipping과 동일)
function calcShipping52($data, $shipping_rules) {
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';
    $detected_size = '';
    if (preg_match('/16절|B5/i', $type1_raw)) $detected_size = 'B5';
    elseif (preg_match('/32절|B6/i', $type1_raw)) $detected_size = 'B6';
    elseif (preg_match('/8절|B4/i', $type1_raw)) $detected_size = 'B4';
    elseif (preg_match('/A3/i', $type1_raw)) $detected_size = 'A3';
    elseif (preg_match('/A4/i', $type1_raw)) $detected_size = 'A4';
    elseif (preg_match('/A5/i', $type1_raw)) $detected_size = 'A5';
    elseif (preg_match('/A6/i', $type1_raw)) $detected_size = 'A6';

    $yeon = 1;
    if (!empty($data['quantity_value']) && floatval($data['quantity_value']) > 0) {
        $yeon = floatval($data['quantity_value']);
    }

    $r = 1; $w = 3000;
    if (!empty($detected_size) && isset($shipping_rules[$detected_size])) {
        $rule = $shipping_rules[$detected_size];
        $r = (int)ceil($yeon) * $rule['boxes'];
        $w = (int)ceil($yeon) * $rule['cost'];
        // A4 특약: 0.5연(2000매) 이하 = 1박스 3,500원 (로젠 계약)
        if ($detected_size === 'A4' && $yeon <= 0.5) {
            $r = 1;
            $w = 3500;
        }
    } elseif (preg_match('/NameCard/i', $data['Type'])) { $r = 1; $w = 3000; }
    elseif (preg_match('/MerchandiseBond/i', $data['Type'])) { $r = 1; $w = 3000; }
    elseif (preg_match('/sticker/i', $data['Type'])) { $r = 1; $w = 3000; }
    elseif (preg_match('/envelop/i', $data['Type'])) {
        // 봉투 종류 감지 (Type_1에서 대봉투/소봉투/자켓 구분)
        $is_big = (mb_strpos($type1_raw, '대봉투') !== false);
        $is_jacket = (preg_match('/쟈켓|자켓/u', $type1_raw));

        // 수량 파싱 (Type_1에서 숫자만 있는 줄)
        $qty = 500;
        $env_lines = preg_split('/\r?\n/', trim($type1_raw));
        foreach ($env_lines as $el) {
            $el = trim($el);
            if (preg_match('/^[\d,]+$/', $el) && intval(str_replace(',', '', $el)) >= 100) {
                $qty = intval(str_replace(',', '', $el));
                break;
            }
        }

        // 펼침면 크기 기반 무게 계산 (대봉투/소봉투/자켓 공통)
        if ($is_big) {
            $env_w = 510; $env_h = 387; $env_gsm = 120; // 대봉투 120g
        } elseif ($is_jacket) {
            $env_w = 262; $env_h = 238; $env_gsm = 100;
        } else {
            $env_w = 238; $env_h = 262; $env_gsm = 100; // 소봉투
        }
        $weight_per_piece = $env_gsm * ($env_w / 1000) * ($env_h / 1000); // g
        $total_kg = round(($weight_per_piece * $qty) / 1000, 1);
        // 박스 분리: 20kg 초과 시 분리
        $r = max(1, (int)ceil($total_kg / 20));
        $kg_per_box = ($r > 0) ? $total_kg / $r : $total_kg;
        if ($is_big) {
            // 대봉투 특약: 3,500원/box (로젠 계약)
            $w = $r * 3500;
        } else {
            // 소봉투/자켓: 무게별 택배비 (로젠 요금표)
            if ($kg_per_box <= 3) $fee_per_box = 3000;
            elseif ($kg_per_box <= 10) $fee_per_box = 3500;
            elseif ($kg_per_box <= 15) $fee_per_box = 4000;
            elseif ($kg_per_box <= 20) $fee_per_box = 5000;
            else $fee_per_box = 6000;
            $w = $r * $fee_per_box;
        }
    }

    return ['boxes' => $r, 'fee' => $w];
}

$DbDir="..";
$GGTABLE="mlangprintauto_transactionCate";
$l[1] = "주문접수";
$l[2] = "입금확인";
$l[3] = "작업중";
$l[4] = "배송중";
$l[0] = "주문취소";

$start = $_GET['start'] ?? 1;
if(!$start) $start = 1;
$PHP_SELF = $_SERVER['PHP_SELF'];

// 검색 파라미터 받기 (PHP 7.4)
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건 생성
// 기본 조건 (괄호로 묶어서 OR 문제 해결)
$base_condition = "((zip1 LIKE '%구%') OR (zip2 LIKE '%-%'))";
$search_conditions = array();

if($search_name != '') {
  $search_name_esc = mysqli_real_escape_string($connect, $search_name);
  $search_conditions[] = "name LIKE '%$search_name_esc%'";
}

if($search_company != '') {
  $search_company_esc = mysqli_real_escape_string($connect, $search_company);
  $search_conditions[] = "company LIKE '%$search_company_esc%'";
}

if($search_date_start != '' && $search_date_end != '') {
  $search_date_start_esc = mysqli_real_escape_string($connect, $search_date_start);
  $search_date_end_esc = mysqli_real_escape_string($connect, $search_date_end);
  $search_conditions[] = "date >= '$search_date_start_esc' AND date <= '$search_date_end_esc'";
} else if($search_date_start != '') {
  $search_date_start_esc = mysqli_real_escape_string($connect, $search_date_start);
  $search_conditions[] = "date >= '$search_date_start_esc'";
} else if($search_date_end != '') {
  $search_date_end_esc = mysqli_real_escape_string($connect, $search_date_end);
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

// WHERE 절 생성: 기본조건 AND (검색조건들)
if(count($search_conditions) > 0) {
  $where_sql = $base_condition . ' AND (' . implode(' AND ', $search_conditions) . ')';
} else {
  $where_sql = $base_condition;
}

// 전체 페이지 구하기
$query = "select count(*) from mlangorder_printauto where $where_sql";
$result = mysqli_query($connect, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($connect) . "<br>Query: " . $query);
}
$data = mysqli_fetch_array($result);
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

$query = "select * from mlangorder_printauto where $where_sql order by no desc";
$query .= " limit $s, $pagenum ";
$result = mysqli_query($connect, $query);
if (!$result) {
    die("<br>Query Error: " . mysqli_error($connect) . "<br>Query: " . $query);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>주문 목록</title>
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
      날짜: <input type="date" name="search_date_start" value="<?php echo htmlspecialchars($search_date_start)?>" style="font-size:9pt;">~<input type="date" name="search_date_end" value="<?php echo htmlspecialchars($search_date_end)?>" style="font-size:9pt;">
      주문번호: <input type="text" name="search_no_start" value="<?php echo htmlspecialchars($search_no_start ?? '')?>" size="5">~<input type="text" name="search_no_end" value="<?php echo htmlspecialchars($search_no_end ?? '')?>" size="5">
      <input type="submit" value="검색">
      <input type="button" value="초기화" onclick="location.href='<?php echo $PHP_SELF?>'">
    </td>
  </tr>
  <tr>
    <td bgcolor="#CCCCCC" style="padding: 5px;"><b>선택항목</b></td>
    <td style="padding: 5px;">
      <input type="button" value="로젠택배 CSV (선택)" onclick="exportSelectedToLogen()" class="btn-logen">
      <input type="button" value="로젠택배 CSV (전체)" onclick="exportAllToLogen()" class="btn-logen">
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
  form.action = 'export_excel52.php';
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
  form.action = 'export_excel52.php';
  form.method = 'get';
  form.target = '_blank';
  form.submit();
  form.action = originalAction;
  form.method = originalMethod;
  form.target = '';
}

// 로젠택배 양식 다운로드 함수
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
      var qtyInput = document.querySelector('input[name="box_qty[' + no + ']"]');
      var feeInput = document.querySelector('input[name="delivery_fee[' + no + ']"]');
      var typeSelect = document.querySelector('select[name="fee_type[' + no + ']"]');
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
    <td style="padding: 3px;"> 품목명
    <td style="padding: 3px;"> 기타
    <td style="padding: 3px;"> 배송메세지

<?php
  $row_count = 0;
  while($data = mysqli_fetch_array($result)){
    // Type_1이 JSON인지 확인하고 파싱
    $type1_display = $data['Type_1'] ?? '';
    $type1_raw = $data['Type_1'] ?? '';

    if (!empty($data['Type_1']) && substr(trim($data['Type_1']), 0, 1) === '{') {
        $json_data = json_decode($data['Type_1'], true);
        if ($json_data && isset($json_data['formatted_display'])) {
            // 줄바꿈 제거하고 공백으로 변경 (한 줄 표시)
            $type1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $json_data['formatted_display']);
            $type1_display = htmlspecialchars($type1_display);
        }
    }
?>
<?php
// 택배비 계산 (delivery_manager.php와 동일 규칙)
$ship = calcShipping52($data, $shipping_rules);
$r = $ship['boxes'];
$w = $ship['fee'];
?>
  <tr>
    <td style="padding: 3px;"><input type="checkbox" name="selected_no[]" value="<?php echo $data['no']?>"></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($data['no'] ?? '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($data['date'] ?? '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($data['name'] ?? '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($data['zip'] ?? '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($data['zip1'] ?? '')?> <?php echo htmlspecialchars($data['zip2'] ?? '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($data['phone'] ?? '')?></td>
    <td style="padding: 3px;" width="120"><a href="http://www.webhard.co.kr/webII/page/sms/main_sms.php"><?php echo htmlspecialchars($data['Hendphone'] ?? '')?></a></td>
    <td style="padding: 3px;" align='center'><input type="text" name="box_qty[<?php echo $data['no']?>]" value="<?php echo $r; ?>" size="2" style="text-align:center;"></td>
    <td style="padding: 3px;"><input type="text" name="delivery_fee[<?php echo $data['no']?>]" value="<?php echo $w; ?>" size="5"></td>
    <td style="padding: 3px;"><select name="fee_type[<?php echo $data['no']?>]" style="font-size:9pt;">
      <option value="착불" selected>착불</option>
      <option value="신용">신용</option>
      <option value="퀵">퀵</option>
    </select></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($data['Type'] ?? '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($data['no'] ?? '')?></td>
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
