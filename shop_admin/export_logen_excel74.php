<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

include "../db.php";
$connect = $db;

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

$selected_nos = isset($_POST['selected_nos']) ? $_POST['selected_nos'] : '';

$custom_box_qty = array();
$custom_delivery_fee = array();
$custom_fee_type = array();

if (isset($_POST['box_qty_json']) && $_POST['box_qty_json'] != '') {
    $decoded = json_decode($_POST['box_qty_json'], true);
    if (is_array($decoded)) $custom_box_qty = $decoded;
}
if (isset($_POST['delivery_fee_json']) && $_POST['delivery_fee_json'] != '') {
    $decoded = json_decode($_POST['delivery_fee_json'], true);
    if (is_array($decoded)) $custom_delivery_fee = $decoded;
}
if (isset($_POST['fee_type_json']) && $_POST['fee_type_json'] != '') {
    $decoded = json_decode($_POST['fee_type_json'], true);
    if (is_array($decoded)) $custom_fee_type = $decoded;
}

$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

$where_conditions = array();
if ($selected_nos != '') {
    $nos_array = explode(',', $selected_nos);
    $nos_cleaned = array();
    foreach ($nos_array as $no) {
        $nos_cleaned[] = intval($no);
    }
    $where_conditions[] = "no IN (" . implode(',', $nos_cleaned) . ")";
} else {
    $where_conditions[] = "(delivery != '방문' AND delivery != '방문수령' OR delivery IS NULL)";
    if ($search_name != '') {
        $where_conditions[] = "name LIKE '%" . mysqli_real_escape_string($connect, $search_name) . "%'";
    }
    if ($search_company != '') {
        $where_conditions[] = "company LIKE '%" . mysqli_real_escape_string($connect, $search_company) . "%'";
    }
    if ($search_date_start != '' && $search_date_end != '') {
        $where_conditions[] = "date >= '" . mysqli_real_escape_string($connect, $search_date_start) . " 00:00:00' AND date <= '" . mysqli_real_escape_string($connect, $search_date_end) . " 23:59:59'";
    } else if ($search_date_start != '') {
        $where_conditions[] = "date >= '" . mysqli_real_escape_string($connect, $search_date_start) . " 00:00:00'";
    } else if ($search_date_end != '') {
        $where_conditions[] = "date <= '" . mysqli_real_escape_string($connect, $search_date_end) . " 23:59:59'";
    }
    if ($search_no_start != '' && $search_no_end != '') {
        $where_conditions[] = "no >= " . intval($search_no_start) . " AND no <= " . intval($search_no_end);
    } else if ($search_no_start != '') {
        $where_conditions[] = "no >= " . intval($search_no_start);
    } else if ($search_no_end != '') {
        $where_conditions[] = "no <= " . intval($search_no_end);
    }
}

$where_sql = "WHERE " . implode(' AND ', $where_conditions);
$query = "SELECT * FROM mlangorder_printauto $where_sql ORDER BY no DESC";
$result = safe_mysqli_query($connect, $query);

$rows = array();
while ($data = mysqli_fetch_array($result)) {
    $order_no = $data['no'];
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    // ===== 택배비 자동 계산 (연 단위 룩업 테이블) =====
    $shipping_rules = array(
        'A6'  => array('boxes' => 1, 'cost' => 4000),
        'B6'  => array('boxes' => 1, 'cost' => 4000),
        'A5'  => array('boxes' => 1, 'cost' => 6000),
        'B5'  => array('boxes' => 2, 'cost' => 7000),   // 16절 특약
        'A4'  => array('boxes' => 1, 'cost' => 6000),
        'B4'  => array('boxes' => 2, 'cost' => 12000),
        'A3'  => array('boxes' => 2, 'cost' => 12000),
    );

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
    } elseif (preg_match("/NameCard/i", $data['Type'])) {
        $r = 1; $w = 3000;
    } elseif (preg_match("/MerchandiseBond/i", $data['Type'])) {
        $r = 1; $w = 3000;
    } elseif (preg_match("/sticker/i", $data['Type'])) {
        $r = 1; $w = 3000;
    } elseif (preg_match("/envelop/i", $data['Type'])) {
        $r = 1; $w = 3000;
    }

    if (isset($custom_box_qty[$order_no]) && $custom_box_qty[$order_no] != '') {
        $r = intval($custom_box_qty[$order_no]);
    }
    if (isset($custom_delivery_fee[$order_no]) && $custom_delivery_fee[$order_no] != '') {
        $w = intval($custom_delivery_fee[$order_no]);
    }
    $fee_type = '착불';
    if (isset($custom_fee_type[$order_no]) && $custom_fee_type[$order_no] != '') {
        $fee_type = $custom_fee_type[$order_no];
    }

    $name = isset($data['name']) ? trim($data['name']) : '';
    $zip = isset($data['zip']) ? trim($data['zip']) : '';
    $zip1 = isset($data['zip1']) ? trim($data['zip1']) : '';
    $zip2 = isset($data['zip2']) ? trim($data['zip2']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $hendphone = isset($data['Hendphone']) ? trim($data['Hendphone']) : '';
    $type_raw = isset($data['Type']) ? trim($data['Type']) : '';
    $type_display = $type_raw;
    if (!empty($type_raw) && substr($type_raw, 0, 1) === '{') {
        $jt = json_decode($type_raw, true);
        if ($jt && isset($jt['product_type'])) $type_display = $jt['product_type'];
    }
    $type = isset($type_labels[$type_display]) ? $type_labels[$type_display] : $type_display;
    $full_address = trim($zip1 . ' ' . $zip2);

    $type_1_display = $type1_raw;
    if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
        $json_data = json_decode($type1_raw, true);
        if ($json_data) {
            if (isset($json_data['formatted_display'])) {
                $type_1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $json_data['formatted_display']);
            } else {
                $parts = array();
                if (!empty($json_data['spec_material'])) $parts[] = $json_data['spec_material'];
                if (!empty($json_data['spec_size'])) $parts[] = $json_data['spec_size'];
                if (!empty($json_data['spec_sides'])) $parts[] = $json_data['spec_sides'];
                if (!empty($json_data['quantity_display'])) $parts[] = $json_data['quantity_display'];
                if (!empty($json_data['spec_design'])) $parts[] = $json_data['spec_design'];
                $type_1_display = !empty($parts) ? implode(' / ', $parts) : '';
            }
        }
    }

    $rows[] = array($name, $zip, $full_address, $phone, $hendphone, $r, $w, $fee_type, $type, $order_no, $type_1_display);
}

$filename = "logen_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF";
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=utf-8">
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
</head>
<body>
<table border="1">
<tr style="background-color:#CCCCCC; font-weight:bold;">
<td>수하인명</td>
<td>우편번호</td>
<td>주소</td>
<td>전화</td>
<td>핸드폰</td>
<td>박스수량</td>
<td>택배비</td>
<td>운임구분</td>
<td>Type</td>
<td>기타</td>
<td>품목</td>
</tr>
<?php foreach ($rows as $row): ?>
<tr>
<td><?php echo htmlspecialchars($row[0]); ?></td>
<td style="mso-number-format:'\@'"><?php echo htmlspecialchars($row[1]); ?></td>
<td><?php echo htmlspecialchars($row[2]); ?></td>
<td style="mso-number-format:'\@'"><?php echo htmlspecialchars($row[3]); ?></td>
<td style="mso-number-format:'\@'"><?php echo htmlspecialchars($row[4]); ?></td>
<td><?php echo $row[5]; ?></td>
<td><?php echo $row[6]; ?></td>
<td><?php echo htmlspecialchars($row[7]); ?></td>
<td><?php echo htmlspecialchars($row[8]); ?></td>
<td><?php echo htmlspecialchars($row[9]); ?></td>
<td><?php echo htmlspecialchars($row[10]); ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
