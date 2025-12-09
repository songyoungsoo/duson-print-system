<?php
/**
 * 로젠택배 양식 엑셀 내보내기 (XLS 형식) - 전체 컬럼
 * PHP 5.2 호환 / EUC-KR DB -> UTF-8 출력
 */

// lib.php의 style 태그 출력을 버퍼링하여 무시
ob_start();
include "lib.php";
ob_end_clean();
$connect = dbconn();

require_once dirname(__FILE__) . '/delivery_rules_config.php';
require_once dirname(__FILE__) . '/delivery_calculator.php';

$deliveryRules = require dirname(__FILE__) . '/delivery_rules_config.php';

/**
 * EUC-KR을 UTF-8로 변환
 */
function to_utf8($str) {
    if (empty($str)) {
        return '';
    }
    return iconv('EUC-KR', 'UTF-8//IGNORE', $str);
}

// 선택된 주문번호들 (POST로 전달)
$selected_nos = isset($_POST['selected_nos']) ? $_POST['selected_nos'] : '';

// 사용자가 수정한 박스수량, 택배비, 운임구분 (JSON으로 전달)
$custom_box_qty = array();
$custom_delivery_fee = array();
$custom_fee_type = array();

if (isset($_POST['box_qty_json']) && !empty($_POST['box_qty_json'])) {
    $decoded = json_decode($_POST['box_qty_json'], true);
    $custom_box_qty = $decoded ? $decoded : array();
}
if (isset($_POST['delivery_fee_json']) && !empty($_POST['delivery_fee_json'])) {
    $decoded = json_decode($_POST['delivery_fee_json'], true);
    $custom_delivery_fee = $decoded ? $decoded : array();
}
if (isset($_POST['fee_type_json']) && !empty($_POST['fee_type_json'])) {
    $decoded = json_decode($_POST['fee_type_json'], true);
    $custom_fee_type = $decoded ? $decoded : array();
}

// 검색 파라미터 받기
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건 구성
$where_conditions = array();

if ($selected_nos != '') {
    // 선택된 항목만 내보내기 (체크박스 선택)
    $nos_array = explode(',', $selected_nos);
    $nos_cleaned = array();
    foreach ($nos_array as $no) {
        $nos_cleaned[] = intval($no);
    }
    $nos_string = implode(',', $nos_cleaned);
    $where_conditions[] = "no IN ($nos_string)";
} else {
    // 전체 내보내기 시에만 기본 조건 적용
    $where_conditions[] = "(zip1 like '%구%' ) or (zip2 like '%-%')";
    if ($search_name != '') {
        $search_name_esc = mysql_real_escape_string($search_name);
        $where_conditions[] = "name like '%$search_name_esc%'";
    }
    if ($search_company != '') {
        $search_company_esc = mysql_real_escape_string($search_company);
        $where_conditions[] = "company like '%$search_company_esc%'";
    }
    if ($search_date_start != '' && $search_date_end != '') {
        $search_date_start_esc = mysql_real_escape_string($search_date_start);
        $search_date_end_esc = mysql_real_escape_string($search_date_end);
        $where_conditions[] = "date >= '$search_date_start_esc' and date <= '$search_date_end_esc'";
    } else if ($search_date_start != '') {
        $search_date_start_esc = mysql_real_escape_string($search_date_start);
        $where_conditions[] = "date >= '$search_date_start_esc'";
    } else if ($search_date_end != '') {
        $search_date_end_esc = mysql_real_escape_string($search_date_end);
        $where_conditions[] = "date <= '$search_date_end_esc'";
    }
    if ($search_no_start != '' && $search_no_end != '') {
        $where_conditions[] = "no >= " . intval($search_no_start) . " and no <= " . intval($search_no_end);
    } else if ($search_no_start != '') {
        $where_conditions[] = "no >= " . intval($search_no_start);
    } else if ($search_no_end != '') {
        $where_conditions[] = "no <= " . intval($search_no_end);
    }
}

$where_sql = count($where_conditions) > 0 ? "WHERE " . implode(' and ', $where_conditions) : "";
$query = "select * from MlangOrder_PrintAuto $where_sql order by no desc";
$result = mysql_query($query, $connect);

if (!$result) {
    die("Query Error: " . mysql_error());
}

// 엑셀 파일 헤더
$filename = "logen_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Pragma: no-cache");
header("Expires: 0");

// UTF-8 BOM
echo "\xEF\xBB\xBF";
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=UTF-8">
<!--[if gte mso 9]>
<xml>
<x:ExcelWorkbook>
<x:ExcelWorksheets>
<x:ExcelWorksheet>
<x:Name><?php echo to_utf8('로젠택배'); ?></x:Name>
<x:WorksheetOptions>
<x:Panes></x:Panes>
</x:WorksheetOptions>
</x:ExcelWorksheet>
</x:ExcelWorksheets>
</x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
td { mso-number-format:\@; }
.header { background-color: #4472C4; color: white; font-weight: bold; text-align: center; }
.data { border: 1px solid #D0D0D0; vertical-align: top; }
.center { text-align: center; }
</style>
</head>
<body>
<table border="1" cellpadding="3" cellspacing="0">
<tr>
    <td class="header"><?php echo to_utf8('수하인명'); ?></td>
    <td class="header"><?php echo to_utf8('우편번호'); ?></td>
    <td class="header"><?php echo to_utf8('주소'); ?></td>
    <td class="header"><?php echo to_utf8('전화'); ?></td>
    <td class="header"><?php echo to_utf8('핸드폰'); ?></td>
    <td class="header"><?php echo to_utf8('박스수량'); ?></td>
    <td class="header"><?php echo to_utf8('택배비'); ?></td>
    <td class="header"><?php echo to_utf8('운임구분'); ?></td>
    <td class="header"><?php echo to_utf8('품목명'); ?></td>
    <td class="header"><?php echo to_utf8('기타'); ?></td>
    <td class="header"><?php echo to_utf8('배송메세지'); ?></td>
</tr>
<?php
while ($data = mysql_fetch_array($result)) {
    $order_no = $data['no'];

    // Type_1 원본값 (박스/택배비 계산용)
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    // 박스수량/택배비 계산 (하드코딩 규칙) - 기본값
    $r = 1; $w = 3000;
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

    // 사용자가 수정한 값이 있으면 덮어씌우기
    if (isset($custom_box_qty[$order_no])) {
        $r = intval($custom_box_qty[$order_no]);
    }
    if (isset($custom_delivery_fee[$order_no])) {
        $w = intval($custom_delivery_fee[$order_no]);
    }
    $fee_type = '착불'; // 기본값
    if (isset($custom_fee_type[$order_no])) {
        $fee_type = $custom_fee_type[$order_no];
    }

    // 주소 합치기
    $zip = isset($data['zip']) ? $data['zip'] : '';
    $zip1 = isset($data['zip1']) ? $data['zip1'] : '';
    $zip2 = isset($data['zip2']) ? $data['zip2'] : '';
    $full_address = trim($zip1 . ' ' . $zip2);

    // Type_1 JSON 처리
    $type_1_display = $type1_raw;
    if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
        $json_data = json_decode($type1_raw, true);
        if ($json_data && isset($json_data['formatted_display'])) {
            $type_1_display = $json_data['formatted_display'];
        }
    }
?>
<tr>
    <td class="data"><?php echo to_utf8(isset($data['name']) ? $data['name'] : ''); ?></td>
    <td class="data"><?php echo to_utf8($zip); ?></td>
    <td class="data"><?php echo to_utf8($full_address); ?></td>
    <td class="data"><?php echo to_utf8(isset($data['phone']) ? $data['phone'] : ''); ?></td>
    <td class="data"><?php echo to_utf8(isset($data['Hendphone']) ? $data['Hendphone'] : ''); ?></td>
    <td class="data center"><?php echo $r; ?></td>
    <td class="data center"><?php echo $w; ?></td>
    <td class="data center"><?php echo to_utf8($fee_type); ?></td>
    <td class="data"><?php echo nl2br(to_utf8($type_1_display)); ?></td>
    <td class="data">&nbsp;</td>
    <td class="data"><?php echo to_utf8(isset($data['Type']) ? $data['Type'] : ''); ?></td>
</tr>
<?php } ?>
</table>
</body>
</html>
<?php
mysql_close($connect);
?>
