<?php
// PHP 7.4 호환 버전 - UTF-8
include "lib.php";
// 주문 데이터가 있는 dsp1830 DB 연결
require_once __DIR__ . '/../db.php';
$connect = $db;

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
$where_conditions = array();
$where_conditions[] = "(zip1 like '%구%' ) or (zip2 like '%-%')";

if($search_name != '') {
  $search_name_esc = mysqli_real_escape_string($connect, $search_name);
  $where_conditions[] = "name like '%$search_name_esc%'";
}

if($search_company != '') {
  $search_company_esc = mysqli_real_escape_string($connect, $search_company);
  $where_conditions[] = "company like '%$search_company_esc%'";
}

if($search_date_start != '' && $search_date_end != '') {
  $search_date_start_esc = mysqli_real_escape_string($connect, $search_date_start);
  $search_date_end_esc = mysqli_real_escape_string($connect, $search_date_end);
  $where_conditions[] = "date >= '$search_date_start_esc' and date <= '$search_date_end_esc'";
} else if($search_date_start != '') {
  $search_date_start_esc = mysqli_real_escape_string($connect, $search_date_start);
  $where_conditions[] = "date >= '$search_date_start_esc'";
} else if($search_date_end != '') {
  $search_date_end_esc = mysqli_real_escape_string($connect, $search_date_end);
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
      날짜: <input type="text" name="search_date_start" value="<?php echo htmlspecialchars($search_date_start)?>" size="8" placeholder="YYYY-MM-DD">~<input type="text" name="search_date_end" value="<?php echo htmlspecialchars($search_date_end)?>" size="8" placeholder="YYYY-MM-DD">
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
      <br><br>
      <input type="button" value="🚀 로젠 API 자동 접수 (선택)" onclick="autoRegisterLogen()" class="btn-logen" style="background-color:#28a745; color:white; font-weight:bold; padding:8px 16px;">
      <span style="color:#666; font-size:11px; margin-left:10px;">※ 선택한 주문을 로젠택배에 자동 접수하고 송장번호를 즉시 발급받습니다</span>
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

// 로젠 API 자동 배송 접수
function autoRegisterLogen() {
  var checkboxes = document.getElementsByName('selected_no[]');
  var selected = [];

  for(var i=0; i<checkboxes.length; i++) {
    if(checkboxes[i].checked) {
      selected.push(parseInt(checkboxes[i].value));
    }
  }

  if(selected.length === 0) {
    alert('배송 접수할 주문을 선택해주세요.');
    return;
  }

  if(!confirm('선택한 ' + selected.length + '건을 로젠택배에 자동 접수하시겠습니까?\n\n송장번호가 즉시 발급되며, 주문 정보에 자동 저장됩니다.')) {
    return;
  }

  // 로딩 표시
  var loadingDiv = document.createElement('div');
  loadingDiv.id = 'logenLoading';
  loadingDiv.style.cssText = 'position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(0,0,0,0.8); color:white; padding:30px 50px; border-radius:10px; z-index:9999; font-size:16px;';
  loadingDiv.innerHTML = '🚀 로젠택배 API 처리 중...<br><br><span style="font-size:12px;">선택한 ' + selected.length + '건을 접수하고 있습니다</span>';
  document.body.appendChild(loadingDiv);

  // AJAX로 API 호출
  fetch('logen_auto_register.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      order_nos: selected
    })
  })
  .then(function(response) {
    return response.json();
  })
  .then(function(data) {
    // 로딩 제거
    document.body.removeChild(loadingDiv);

    if(data.success) {
      var message = '✅ 배송 접수 완료!\n\n';
      message += '- 성공: ' + data.registered + '건\n';
      if(data.failed > 0) {
        message += '- 실패: ' + data.failed + '건\n\n';
      }
      message += '\n송장번호가 자동 저장되었습니다.\n페이지를 새로고침합니다.';
      alert(message);
      location.reload();
    } else {
      var errorMsg = '❌ 배송 접수 실패\n\n' + data.message;
      if(data.details && data.details.length > 0) {
        errorMsg += '\n\n실패 상세:\n';
        data.details.forEach(function(detail) {
          if(!detail.success) {
            errorMsg += '- 주문 #' + detail.order_no + ': ' + detail.message + '\n';
          }
        });
      }
      alert(errorMsg);
    }
  })
  .catch(function(error) {
    // 로딩 제거
    if(document.getElementById('logenLoading')) {
      document.body.removeChild(loadingDiv);
    }
    alert('❌ API 통신 오류: ' + error.message);
  });
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
// 박스 하드코딩 계산 (기존유지)
$r = 1; $w = 3000; // 기본값
if(preg_match("/16절/i", $type1_raw)){
    $r=2; $w=3000;
} elseif(preg_match("/a4/i", $type1_raw)){
    $r=1; $w=4000;
} elseif(preg_match("/a5/i", $type1_raw)){
    $r=1; $w=4000;
} elseif(preg_match("/NameCard/i", $data['Type'])){
    $r=1; $w=3000;  // 2500 → 3000 (최저금액 통일)
} elseif(preg_match("/MerchandiseBond/i", $data['Type'])){
    $r=1; $w=3000;  // 2500 → 3000 (최저금액 통일)
} elseif(preg_match("/sticker/i", $data['Type'])){
    $r=1; $w=3000;  // 2500 → 3000 (최저금액 통일)
} elseif(preg_match("/스티카/i", $data['Type'])){
    $r=1; $w=3000;  // 2500 → 3000 (최저금액 통일)
} elseif(preg_match("/envelop/i", $data['Type'])){
    $r=1; $w=3000;
}
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
    <td style="padding: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"><?php echo $type1_display?></td>
    <td style="padding: 3px;">dsno<?php echo htmlspecialchars($data['no'] ?? '')?></td>
    <td style="padding: 3px;"><?php echo htmlspecialchars($data['Type'] ?? '')?></td>
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
