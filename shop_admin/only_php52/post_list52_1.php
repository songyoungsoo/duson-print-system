<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<title>??? ??? ???</title>
<style>
body { font-size: 9pt; }
td, input, li, select { font-size: 9pt; }
table { border-collapse: collapse; }
table td, table th { border: 1px solid #ccc; padding: 2px 4px; }
a { text-decoration: none; color: #333; }
a:hover { color: #0066FF; }
.btn-excel { background-color:#28a745; color:white; font-weight:bold; padding:5px 15px; border:none; cursor:pointer; border-radius:4px; }
.btn-excel-all { background-color:#007bff; color:white; font-weight:bold; padding:5px 15px; border:none; cursor:pointer; border-radius:4px; margin-left:10px; }
input.edit-field { width: 50px; text-align: center; }
input.edit-field.saving { background-color: #ffffcc; }
input.edit-field.saved { background-color: #ccffcc; }
input.edit-field.error { background-color: #ffcccc; }
select.edit-field { }
select.edit-field.saving { background-color: #ffffcc; }
select.edit-field.saved { background-color: #ccffcc; }
select.edit-field.error { background-color: #ffcccc; }
</style>
</head>
<body>
<?
  include "lib_mysql.php";
  $connect = dbconn();

$start = isset($_GET['start']) ? intval($_GET['start']) : 1;
if($start < 1) $start = 1;

$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

$where_conditions = array();
// �ּҰ� �ִ� �͸� (zip1 �Ǵ� zip2�� ������� ���� ���)
$where_conditions[] = "((TRIM(zip1) != '' AND zip1 IS NOT NULL) OR (TRIM(zip2) != '' AND zip2 IS NOT NULL))";

if($search_name != '') {
  $sn = mysql_real_escape_string($search_name, $connect);
  $where_conditions[] = "name like '%$sn%'";
}
if($search_company != '') {
  $sc = mysql_real_escape_string($search_company, $connect);
  $where_conditions[] = "bizname like '%$sc%'";
}
if($search_date_start != '' && $search_date_end != '') {
  $where_conditions[] = "date >= '$search_date_start' and date <= '$search_date_end 23:59:59'";
} else if($search_date_start != '') {
  $where_conditions[] = "date >= '$search_date_start'";
} else if($search_date_end != '') {
  $where_conditions[] = "date <= '$search_date_end 23:59:59'";
}
if($search_no_start != '' && $search_no_end != '') {
  $where_conditions[] = "no >= " . intval($search_no_start) . " and no <= " . intval($search_no_end);
} else if($search_no_start != '') {
  $where_conditions[] = "no >= " . intval($search_no_start);
} else if($search_no_end != '') {
  $where_conditions[] = "no <= " . intval($search_no_end);
}

$where_sql = implode(' and ', $where_conditions);

$query = "select count(*) from MlangOrder_PrintAuto where $where_sql";
$result = mysql_query($query, $connect);
$data = mysql_fetch_array($result);
$total = $data[0];

$pagenum = 20;
$pages = ceil($total / $pagenum);
if($pages < 1) $pages = 1;
$s = $pagenum * ($start-1);

$search_params = '';
if($search_name != '') $search_params .= "&search_name=" . urlencode($search_name);
if($search_company != '') $search_params .= "&search_company=" . urlencode($search_company);
if($search_date_start != '') $search_params .= "&search_date_start=" . urlencode($search_date_start);
if($search_date_end != '') $search_params .= "&search_date_end=" . urlencode($search_date_end);
if($search_no_start != '') $search_params .= "&search_no_start=" . urlencode($search_no_start);
if($search_no_end != '') $search_params .= "&search_no_end=" . urlencode($search_no_end);

$query = "select * from MlangOrder_PrintAuto where $where_sql order by no desc limit $s, $pagenum";
$result = mysql_query($query, $connect);
?>

<li> ?? ?????? : <?=$total?>

<form method="get" id="searchForm">
<table style="margin:10px 0;">
<tr>
  <td>???:</td>
  <td><input type="text" name="search_name" value="<?=htmlspecialchars($search_name)?>" size="12"></td>
  <td>???:</td>
  <td><input type="text" name="search_company" value="<?=htmlspecialchars($search_company)?>" size="15"></td>
  <td>???:</td>
  <td>
    <input type="text" name="search_date_start" value="<?=htmlspecialchars($search_date_start)?>" size="10" placeholder="YYYY-MM-DD">
    ~
    <input type="text" name="search_date_end" value="<?=htmlspecialchars($search_date_end)?>" size="10" placeholder="YYYY-MM-DD">
  </td>
  <td>??????:</td>
  <td>
    <input type="text" name="search_no_start" value="<?=htmlspecialchars($search_no_start)?>" size="8">
    ~
    <input type="text" name="search_no_end" value="<?=htmlspecialchars($search_no_end)?>" size="8">
  </td>
  <td>
    <input type="submit" value="???">
    <input type="button" value="????" onclick="location.href='post_list52.php'">
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

function saveField(no, field, element) {
  var value = element.value;
  element.className = element.className.replace(' saved', '').replace(' error', '') + ' saving';
  
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'update_logen.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if(xhr.readyState == 4) {
      element.className = element.className.replace(' saving', '');
      if(xhr.status == 200) {
        try {
          var resp = eval('(' + xhr.responseText + ')');
          if(resp.success) {
            element.className = element.className.replace(' error', '') + ' saved';
            setTimeout(function(){ element.className = element.className.replace(' saved', ''); }, 1000);
          } else {
            element.className = element.className.replace(' saved', '') + ' error';
            alert('???? ????: ' + resp.message);
          }
        } catch(e) {
          element.className = element.className.replace(' saved', '') + ' error';
        }
      } else {
        element.className = element.className.replace(' saved', '') + ' error';
      }
    }
  };
  xhr.send('no=' + no + '&field=' + field + '&value=' + encodeURIComponent(value));
}

function exportSelectedToExcel() {
  var checkboxes = document.getElementsByName('selected_no[]');
  var selected = [];
  var boxQty = {};
  var deliveryFee = {};
  var feeType = {};
  
  for(var i=0; i<checkboxes.length; i++) {
    if(checkboxes[i].checked) {
      var no = checkboxes[i].value;
      selected.push(no);
      
      var boxEl = document.getElementById('box_' + no);
      var feeEl = document.getElementById('fee_' + no);
      var typeEl = document.getElementById('feetype_' + no);
      
      if(boxEl) boxQty[no] = boxEl.value;
      if(feeEl) deliveryFee[no] = feeEl.value;
      if(typeEl) feeType[no] = typeEl.value;
    }
  }
  if(selected.length === 0) {
    alert('???��??? ????? ?????????.');
    return;
  }
  
  var form = document.createElement('form');
  form.method = 'POST';
  form.action = 'export_logen_excel.php';
  form.target = '_blank';
  
  var input1 = document.createElement('input');
  input1.type = 'hidden';
  input1.name = 'selected_nos';
  input1.value = selected.join(',');
  form.appendChild(input1);
  
  var input2 = document.createElement('input');
  input2.type = 'hidden';
  input2.name = 'box_qty_json';
  input2.value = JSON.stringify(boxQty);
  form.appendChild(input2);
  
  var input3 = document.createElement('input');
  input3.type = 'hidden';
  input3.name = 'delivery_fee_json';
  input3.value = JSON.stringify(deliveryFee);
  form.appendChild(input3);
  
  var input4 = document.createElement('input');
  input4.type = 'hidden';
  input4.name = 'fee_type_json';
  input4.value = JSON.stringify(feeType);
  form.appendChild(input4);
  
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}

function exportAllToExcel() {
  alert('??? ????????? ???? ???????? ??????????.');
}
</script>

<form id="listForm">
<table width="100%">
<tr bgcolor="#f0f0f0">
  <td><input type="checkbox" onclick="toggleAll(this)"></td>
  <td>??????</td>
  <td>???</td>
  <td>???</td>
  <td>???????</td>
  <td>???</td>
  <td>???</td>
  <td>?????</td>
  <td>???????</td>
  <td>????</td>
  <td>???????</td>
  <td>Type</td>
  <td>??????</td>
  <td>???</td>
</tr>
<?
while($data = mysql_fetch_array($result)){
  $box = $data['logen_box_qty'];
  $fee = $data['logen_delivery_fee'];
  $feetype = $data['logen_fee_type'];
  
  if(empty($box)) {
    $box = 1;
    if(preg_match("/16??/",$data['Type_1'])){ $box = 2; }
  }
  if(empty($fee)) {
    $fee = 3000;
    if(preg_match("/16??/",$data['Type_1'])){ $fee = 3000; }
    else if(preg_match("/a4/i",$data['Type_1'])){ $fee = 4000; }
    else if(preg_match("/a5/i",$data['Type_1'])){ $fee = 4000; }
    else if(preg_match("/NameCard/i",$data['Type'])){ $fee = 2500; }
    else if(preg_match("/MerchandiseBond/i",$data['Type'])){ $fee = 2500; }
    else if(preg_match("/sticker/i",$data['Type'])){ $fee = 2500; }
    else if(preg_match("/envelop/i",$data['Type'])){ $fee = 3000; }
  }
  if(empty($feetype)) {
    $feetype = '????';
  }
  
  $no = $data['no'];
?>
<tr>
  <td><input type="checkbox" name="selected_no[]" value="<?=$no?>"></td>
  <td><?=$no?></td>
  <td><?=$data['date']?></td>
  <td><?=$data['name']?></td>
  <td><?=$data['zip']?></td>
  <td><?=$data['zip1']?> <?=$data['zip2']?></td>
  <td><?=$data['phone']?></td>
  <td><?=$data['Hendphone']?></td>
  <td><input type="text" class="edit-field" id="box_<?=$no?>" value="<?=$box?>" onchange="saveField(<?=$no?>, 'logen_box_qty', this)"></td>
  <td><input type="text" class="edit-field" id="fee_<?=$no?>" value="<?=$fee?>" onchange="saveField(<?=$no?>, 'logen_delivery_fee', this)"></td>
  <td><select class="edit-field" id="feetype_<?=$no?>" onchange="saveField(<?=$no?>, 'logen_fee_type', this)"><option value="????"<?=($feetype=='????')?' selected':''?>>????</option><option value="????"<?=($feetype=='????')?' selected':''?>>????</option></select></td>
  <td><?=$data['Type']?></td>
  <td><?=$no?></td>
  <td><?=$data['Type_1']?></td>
</tr>
<? } ?>
</table>
</form>

<p>
<button class="btn-excel" onclick="exportSelectedToExcel()">??????? ???? ???? ???��?</button>
<button class="btn-excel-all" onclick="exportAllToExcel()">??? ???? ???? ???��?</button>
</p>

<?
$a = max(1, $start - 5);
$b = min($pages, $start + 5);
$prev = max(1, $start - 10);
$next = min($pages, $start + 10);

echo "??? ";
if($start > 1) echo "<a href='?start=1$search_params'>???</a> ";
for($i=$a; $i<=$b; $i++){
  if($start==$i) echo "<b>[$i]</b> ";
  else echo "<a href='?start=$i$search_params'>[$i]</a> ";
}
if($start < $pages) echo "<a href='?start=$pages$search_params'>[????]</a> ";
echo "???";
?>
</body>
</html>
