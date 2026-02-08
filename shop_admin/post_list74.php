<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>로젠 주소 목록</title>
<style>
td,input,li{font-size:9pt}
.btn-logen {
    background-color: #1E4E79;
    color: white;
    font-weight: bold;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-left: 5px;
}
.btn-logen:hover {
    background-color: #173d5e;
}
</style>
</head>
<body>
<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  include "../db.php";
  $connect = $db;
  $GGTABLE="mlangprintauto_transactioncate";
  $l[1] = "주문접수";
  $l[2] = "입금확인";
  $l[3] = "작업중";
  $l[4] = "배송중";
  $l[0] = "주문취소";

  $type_labels = array(
    'NameCard' => '명함',
    'Inserted' => '전단지',
    'inserted' => '전단지',
    'NcrFlambeau' => '양식지',
    'ncrflambeau' => '양식지',
    'Sticker' => '스티커',
    'sticker' => '스티커',
    'sticker_new' => '스티커',
    'Msticker' => '자석스티커',
    'msticker' => '자석스티커',
    'Envelope' => '봉투',
    'envelope' => '봉투',
    'LittlePrint' => '포스터',
    'littleprint' => '포스터',
    'MerchandiseBond' => '상품권',
    'merchandisebond' => '상품권',
    'Cadarok' => '카다록',
    'cadarok' => '카다록',
  );

  $start = isset($_GET['start']) ? intval($_GET['start']) : 1;
  if($start < 1) $start = 1;
  $PHP_SELF = $_SERVER['PHP_SELF'];

  // 검색 파라미터 받기
  $search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
  $search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
  $search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
  $search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
  $search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
  $search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

  // WHERE 조건 구성
  // 1) 방문수령 제외
  // 2) 주소 패턴 필터: 구(지번) + 로/길/대로(도로명) + 5자리 우편번호
  $base_condition = "(delivery != '방문' AND delivery != '방문수령' OR delivery IS NULL)
    AND (
      (zip1 LIKE '%구 %' OR zip1 LIKE '%구%동%')
      OR (zip1 LIKE '%로 %' OR zip1 LIKE '%로%번길%')
      OR (zip1 LIKE '%길 %')
      OR (zip1 LIKE '%대로 %' OR zip1 LIKE '%대로%번길%')
      OR (zip2 LIKE '%-%')
      OR (zip REGEXP '^[0-9]{5}$')
    )";
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
    $search_conditions[] = "date >= '$search_date_start_esc 00:00:00' AND date <= '$search_date_end_esc 23:59:59'";
  } else if($search_date_start != '') {
    $search_date_start_esc = mysqli_real_escape_string($connect, $search_date_start);
    $search_conditions[] = "date >= '$search_date_start_esc 00:00:00'";
  } else if($search_date_end != '') {
    $search_date_end_esc = mysqli_real_escape_string($connect, $search_date_end);
    $search_conditions[] = "date <= '$search_date_end_esc 23:59:59'";
  }

  // 주문번호 범위 검색
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

  // 전체 게시물 구하기
  $query = "SELECT COUNT(*) FROM mlangorder_printauto WHERE $where_sql";
  $result = safe_mysqli_query($connect, $query);
  if (!$result) {
      die("Query Error: " . mysqli_error($connect) . "<br>Query: " . $query);
  }
  $data = mysqli_fetch_array($result);
  $total = $data[0];

  // 한화면에 표시될 페이지수
  $pagenum = 20;

  // 총페이지수
  $pages = round($total / $pagenum);
  if($pages < 1) $pages = 1;

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

  $query = "SELECT * FROM mlangorder_printauto WHERE $where_sql ORDER BY no DESC";
  $query .= " LIMIT $s, $pagenum";
  $result = safe_mysqli_query($connect, $query);
  if (!$result) {
      die("<br>Query Error: " . mysqli_error($connect) . "<br>Query: " . $query);
  }
?>

<li> 총 게시물수 : <?php echo $total ?>

<!-- 검색 폼 -->
<form method="get" action="<?php echo $PHP_SELF?>" id="searchForm">
<table border="1" cellpadding="3" cellspacing="0" style="margin-bottom:10px; border-collapse: collapse;">
  <tr>
    <td bgcolor="#1E4E79" style="padding: 5px; color:#fff;"><b>검색</b></td>
    <td style="padding: 5px; white-space:nowrap;">
      이름: <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name)?>" size="6">
      회사: <input type="text" name="search_company" value="<?php echo htmlspecialchars($search_company)?>" size="6">
      날짜: <input type="date" name="search_date_start" value="<?php echo htmlspecialchars($search_date_start)?>">~<input type="date" name="search_date_end" value="<?php echo htmlspecialchars($search_date_end)?>">
      주문번호: <input type="text" name="search_no_start" value="<?php echo htmlspecialchars($search_no_start)?>" size="5">~<input type="text" name="search_no_end" value="<?php echo htmlspecialchars($search_no_end)?>" size="5">
      <input type="submit" value="검색">
      <input type="button" value="초기화" onclick="location.href='<?php echo $PHP_SELF?>'">
    </td>
  </tr>
  <tr>
    <td bgcolor="#1E4E79" style="padding: 5px; color:#fff;"><b>내보내기</b></td>
    <td style="padding: 5px;">
      <input type="button" value="로젠택배 엑셀 (선택)" onclick="exportSelectedToLogenExcel()" class="btn-logen">
      <input type="button" value="로젠택배 엑셀 (전체)" onclick="exportAllToLogenExcel()" class="btn-logen">
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

// 로젠택배 선택 항목 다운로드
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
  form.action = 'export_logen_excel74.php';
  form.target = '_blank';

  var input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'selected_nos';
  input.value = selected.join(',');
  form.appendChild(input);

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

  form.action = 'export_logen_excel74.php';
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
  <tr bgcolor="#1E4E79" style="color:#fff;">
    <td style="padding: 3px;"><input type="checkbox" onclick="toggleAll(this)"></td>
    <td style="padding: 3px;"> 주문번호</td>
    <td style="padding: 3px;"> 날짜</td>
    <td style="padding: 3px;"> 수하인명</td>
    <td style="padding: 3px;"> 우편번호</td>
    <td style="padding: 3px;"> 주소</td>
    <td style="padding: 3px;"> 전화</td>
    <td style="padding: 3px;"> 핸드폰</td>
    <td style="padding: 3px;"> 박스수량</td>
    <td style="padding: 3px;"> 택배비</td>
    <td style="padding: 3px;"> 운임구분</td>
    <td style="padding: 3px;"> Type</td>
    <td style="padding: 3px;"> 기타</td>
    <td style="padding: 3px;"> 품목</td>
  </tr>

<?php
  while($data = mysqli_fetch_array($result)){
    // Type_1이 JSON인지 확인하고 파싱
    $type1_display = isset($data['Type_1']) ? $data['Type_1'] : '';
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    if (!empty($data['Type_1']) && substr(trim($data['Type_1']), 0, 1) === '{') {
        $json_data = json_decode($data['Type_1'], true);
        if ($json_data) {
            if (isset($json_data['formatted_display'])) {
                // formatted_display 있으면 그대로 사용
                $type1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $json_data['formatted_display']);
            } else {
                // formatted_display 없으면 spec 필드들로 자동 조합
                $parts = array();
                if (!empty($json_data['spec_material'])) $parts[] = $json_data['spec_material'];
                if (!empty($json_data['spec_size'])) $parts[] = $json_data['spec_size'];
                if (!empty($json_data['spec_sides'])) $parts[] = $json_data['spec_sides'];
                if (!empty($json_data['quantity_display'])) $parts[] = $json_data['quantity_display'];
                if (!empty($json_data['spec_design'])) $parts[] = $json_data['spec_design'];
                $type1_display = !empty($parts) ? implode(' / ', $parts) : $data['Type_1'];
            }
            $type1_display = htmlspecialchars($type1_display);
        }
    }

    // ===== 택배비 자동 계산 (연 단위 룩업 테이블) =====
    // 규격별 1연당: [박스 수, 택배비(원)]
    // 특약 기준: 1박스 23kg까지 6,000원, B5 2박스 7,000원
    $shipping_rules = array(
        'A6'  => array('boxes' => 1, 'cost' => 4000),   // 소형 - 가벼움
        'B6'  => array('boxes' => 1, 'cost' => 4000),   // 32절
        'A5'  => array('boxes' => 1, 'cost' => 6000),   // A5
        'B5'  => array('boxes' => 2, 'cost' => 7000),   // 16절 특약 2박스=7,000원
        'A4'  => array('boxes' => 1, 'cost' => 6000),   // A4 1박스=6,000원
        'B4'  => array('boxes' => 2, 'cost' => 12000),  // 8절
        'A3'  => array('boxes' => 2, 'cost' => 12000),  // A3
    );

    // 규격 감지 (Type_1에서 파싱)
    $detected_size = '';
    if (preg_match('/16절|B5/i', $type1_raw)) $detected_size = 'B5';
    elseif (preg_match('/32절|B6/i', $type1_raw)) $detected_size = 'B6';
    elseif (preg_match('/8절|B4/i', $type1_raw)) $detected_size = 'B4';
    elseif (preg_match('/A3/i', $type1_raw)) $detected_size = 'A3';
    elseif (preg_match('/A4/i', $type1_raw)) $detected_size = 'A4';
    elseif (preg_match('/A5/i', $type1_raw)) $detected_size = 'A5';
    elseif (preg_match('/A6/i', $type1_raw)) $detected_size = 'A6';

    // 연수 감지: DB quantity_value 우선, 없으면 기본 1연
    $yeon = 1;
    if (!empty($data['quantity_value']) && floatval($data['quantity_value']) > 0) {
        $yeon = floatval($data['quantity_value']);
    }

    // 택배비 계산
    $r = 1; $w = 3000; // 기본값 (명함, 스티커 등 소형 제품)

    if (!empty($detected_size) && isset($shipping_rules[$detected_size])) {
        // 전단지 등 규격 감지된 제품 → 연수 기반 계산
        $rule = $shipping_rules[$detected_size];
        $r = (int)ceil($yeon) * $rule['boxes'];
        $w = (int)ceil($yeon) * $rule['cost'];
    } elseif (preg_match("/NameCard/i", $data['Type'])) {
        $r = 1; $w = 3000;
    } elseif (preg_match("/MerchandiseBond/i", $data['Type'])) {
        $r = 1; $w = 3000;
    } elseif (preg_match("/sticker/i", $data['Type'])) {
        $r = 1; $w = 3000;
    } elseif (preg_match("/envelop/i", $data['Type'])) {
        $r = 1; $w = 3000;
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
      <option value="선불">선불</option>
    </select></td>
    <td style="padding: 3px;"><?php
      $raw_type = isset($data['Type']) ? trim($data['Type']) : '';
      $display_type = $raw_type;
      if (!empty($raw_type) && substr($raw_type, 0, 1) === '{') {
          $jt = json_decode($raw_type, true);
          if ($jt && isset($jt['product_type'])) $display_type = $jt['product_type'];
      }
      echo htmlspecialchars(isset($type_labels[$display_type]) ? $type_labels[$display_type] : $display_type);
    ?></td>
    <td style="padding: 3px;"><?php echo $no?></td>
    <td style="padding: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"><?php echo $type1_display?></td>
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
