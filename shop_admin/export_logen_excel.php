<?php
/**
 * 로젠택배 양식 엑셀 내보내기 (XLS 형식) - 전체 컬럼
 * PHP 7.4 호환 버전 - UTF-8
 */

include "lib.php";
require_once __DIR__ . '/../db.php';
$connect = $db;

require_once dirname(__FILE__) . '/delivery_rules_config.php';
require_once dirname(__FILE__) . '/delivery_calculator.php';

$deliveryRules = require dirname(__FILE__) . '/delivery_rules_config.php';

// 선택된 주문번호들 (POST로 전달)
$selected_nos = isset($_POST['selected_nos']) ? $_POST['selected_nos'] : '';

// 사용자가 수정한 박스수량, 택배비, 운임구분 (JSON으로 전달)
$custom_box_qty = array();
$custom_delivery_fee = array();
$custom_fee_type = array();

if (isset($_POST['box_qty_json']) && !empty($_POST['box_qty_json'])) {
    $custom_box_qty = json_decode($_POST['box_qty_json'], true) ?: array();
}
if (isset($_POST['delivery_fee_json']) && !empty($_POST['delivery_fee_json'])) {
    $custom_delivery_fee = json_decode($_POST['delivery_fee_json'], true) ?: array();
}
if (isset($_POST['fee_type_json']) && !empty($_POST['fee_type_json'])) {
    $custom_fee_type = json_decode($_POST['fee_type_json'], true) ?: array();
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
    // 선택된 항목만 내보내기 (체크박스 선택) - 기본 조건 없이 선택된 것만
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
        $search_name_esc = mysqli_real_escape_string($connect, $search_name);
        $where_conditions[] = "name like '%$search_name_esc%'";
    }
    if ($search_company != '') {
        $search_company_esc = mysqli_real_escape_string($connect, $search_company);
        $where_conditions[] = "company like '%$search_company_esc%'";
    }
    if ($search_date_start != '' && $search_date_end != '') {
        $search_date_start_esc = mysqli_real_escape_string($connect, $search_date_start);
        $search_date_end_esc = mysqli_real_escape_string($connect, $search_date_end);
        $where_conditions[] = "date >= '$search_date_start_esc' and date <= '$search_date_end_esc'";
    } else if ($search_date_start != '') {
        $search_date_start_esc = mysqli_real_escape_string($connect, $search_date_start);
        $where_conditions[] = "date >= '$search_date_start_esc'";
    } else if ($search_date_end != '') {
        $search_date_end_esc = mysqli_real_escape_string($connect, $search_date_end);
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
$query = "select * from mlangorder_printauto $where_sql order by no desc";
$result = mysqli_query($connect, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($connect));
}

// 엑셀 파일 헤더
$filename = "logen_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=UTF-8">
<!--[if gte mso 9]>
<xml>
<x:ExcelWorkbook>
<x:ExcelWorksheets>
<x:ExcelWorksheet>
<x:Name>로젠택배</x:Name>
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
    <td class="header">수하인명</td>
    <td class="header">우편번호</td>
    <td class="header">주소</td>
    <td class="header">전화</td>
    <td class="header">핸드폰</td>
    <td class="header">박스수량</td>
    <td class="header">택배비</td>
    <td class="header">운임구분</td>
    <td class="header">품목명</td>
    <td class="header">기타</td>
    <td class="header">배송메세지</td>
</tr>
<?php
while ($data = mysqli_fetch_array($result)) {
    $order_no = $data['no'];

    // Type_1 원본값 (박스/택배비 계산용)
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    // 박스수량/택배비 계산 (하드코딩 규칙) - 기본값
    $r = 1; $w = 3000;
    if(preg_match("/16절/i", $type1_raw)){
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
    } elseif(preg_match("/스티카/i", $data['Type'])){
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
            // 줄바꿈 제거하고 공백으로 변경 (한 줄 표시)
            $type_1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $json_data['formatted_display']);
        }
    }
?>
<tr>
    <td class="data"><?php echo htmlspecialchars($data['name'] ?? ''); ?></td>
    <td class="data" style="mso-number-format:'\@';"><?php echo htmlspecialchars($zip); ?></td>
    <td class="data"><?php echo htmlspecialchars($full_address); ?></td>
    <td class="data" style="mso-number-format:'\@';"><?php echo htmlspecialchars($data['phone'] ?? ''); ?></td>
    <td class="data" style="mso-number-format:'\@';"><?php echo htmlspecialchars($data['Hendphone'] ?? ''); ?></td>
    <td class="data center"><?php echo $r; ?></td>
    <td class="data center"><?php echo $w; ?></td>
    <td class="data center"><?php echo htmlspecialchars($fee_type); ?></td>
    <td class="data"><?php echo htmlspecialchars($type_1_display); ?></td>
    <td class="data"><?php echo htmlspecialchars($data['no'] ?? ''); ?></td>
    <td class="data"><?php echo htmlspecialchars($data['Type'] ?? ''); ?></td>
</tr>
<?php } ?>
</table>
</body>
</html>
<?php
mysqli_close($connect);
?>
