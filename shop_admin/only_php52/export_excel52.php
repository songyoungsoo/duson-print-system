<?
include "lib_mysql.php";
$connect = dbconn();

$selected_nos = isset($_POST['selected_nos']) ? $_POST['selected_nos'] : '';

if(empty($selected_nos)) {
    die('선택된 항목이 없습니다.');
}

$nos_arr = explode(',', $selected_nos);
$nos_safe = array();
foreach($nos_arr as $n) {
    $nos_safe[] = intval($n);
}
$nos_in = implode(',', $nos_safe);

$query = "SELECT * FROM MlangOrder_PrintAuto WHERE no IN ($nos_in) ORDER BY no DESC";
$result = mysql_query($query, $connect);

$filename = "logen_" . date('Ymd_His') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=euc-kr");
header("Content-Disposition: attachment; filename=" . $filename);
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<!--[if gte mso 9]>
<xml>
<x:ExcelWorkbook>
<x:ExcelWorksheets>
<x:ExcelWorksheet>
<x:Name>Sheet1</x:Name>
<x:WorksheetOptions>
<x:Panes></x:Panes>
</x:WorksheetOptions>
</x:ExcelWorksheet>
</x:ExcelWorksheets>
</x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
table { border-collapse: collapse; }
td, th { border: 1px solid #000; padding: 5px; text-align: center; mso-number-format:'\@'; }
th { background-color: #99CCFF; font-weight: bold; }
</style>
</head>
<body>
<table>
<tr>
<th>수하인명</th>
<th>우편번호</th>
<th>주소</th>
<th>전화</th>
<th>핸드폰</th>
<th>박스수량</th>
<th>택배비</th>
<th>운임구분</th>
<th>품목명</th>
<th>기타</th>
<th>배송메세지</th>
</tr>
<?
while($data = mysql_fetch_array($result)) {
    $box = $data['logen_box_qty'];
    $fee = $data['logen_delivery_fee'];
    $feetype = $data['logen_fee_type'];
    
    if(empty($box)) {
        $box = 1;
        if(preg_match("/16절/",$data['Type_1'])){ $box = 2; }
    }
    if(empty($fee)) {
        $fee = 3000;
        if(preg_match("/16절/",$data['Type_1'])){ $fee = 3000; }
        else if(preg_match("/a4/i",$data['Type_1'])){ $fee = 4000; }
        else if(preg_match("/a5/i",$data['Type_1'])){ $fee = 4000; }
        else if(preg_match("/NameCard/i",$data['Type'])){ $fee = 2500; }
        else if(preg_match("/MerchandiseBond/i",$data['Type'])){ $fee = 2500; }
        else if(preg_match("/sticker/i",$data['Type'])){ $fee = 2500; }
        else if(preg_match("/envelop/i",$data['Type'])){ $fee = 3000; }
    }
    if(empty($feetype)) {
        $feetype = '착불';
    }
    
    $name = trim($data['name']);
    if(empty($name) || $name == '0') {
        $name = trim($data['bizname']);
    }
    if(empty($name)) {
        $name = '-';
    }
    
    $address = trim($data['zip1']) . ' ' . trim($data['zip2']);
?>
<tr>
<td><?=htmlspecialchars($name)?></td>
<td><?=htmlspecialchars($data['zip'])?></td>
<td><?=htmlspecialchars($address)?></td>
<td><?=htmlspecialchars($data['phone'])?></td>
<td><?=htmlspecialchars($data['Hendphone'])?></td>
<td><?=$box?></td>
<td><?=number_format($fee)?></td>
<td><?=htmlspecialchars($feetype)?></td>
<td><?=htmlspecialchars($data['Type_1'])?></td>
<td></td>
<td></td>
</tr>
<? } ?>
</table>
</body>
</html>
