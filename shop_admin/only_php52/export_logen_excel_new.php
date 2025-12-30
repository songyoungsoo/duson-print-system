<?php
// PHP 5.2 호환 - EUC-KR
// Column order: 수하인명 | 우편번호 | 주소 | 전화 | 핸드폰 | 박스수량 | 택배비 | 운임구분 | Type | 기타(주문번호) | 품목

include "lib_mysql.php";
$connect = dbconn();

// POST로 선택된 주문번호 받기
$selected_nos = isset($_POST['selected_nos']) ? $_POST['selected_nos'] : '';

// 사용자 수정값 받기
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

// GET 검색 파라미터
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건 생성
$where_conditions = array();
if ($selected_nos != '') {
    $nos_array = explode(',', $selected_nos);
    $nos_cleaned = array();
    foreach ($nos_array as $no) {
        $nos_cleaned[] = intval($no);
    }
    $where_conditions[] = "no IN (" . implode(',', $nos_cleaned) . ")";
} else {
    // 기본 조건
    $where_conditions[] = "((zip1 LIKE '%구%') OR (zip2 LIKE '%-%'))";
    if ($search_name != '') {
        $where_conditions[] = "name LIKE '%" . mysql_real_escape_string($search_name) . "%'";
    }
    if ($search_company != '') {
        $where_conditions[] = "company LIKE '%" . mysql_real_escape_string($search_company) . "%'";
    }
    if ($search_date_start != '' && $search_date_end != '') {
        $where_conditions[] = "date >= '" . mysql_real_escape_string($search_date_start) . "' AND date <= '" . mysql_real_escape_string($search_date_end) . "'";
    } else if ($search_date_start != '') {
        $where_conditions[] = "date >= '" . mysql_real_escape_string($search_date_start) . "'";
    } else if ($search_date_end != '') {
        $where_conditions[] = "date <= '" . mysql_real_escape_string($search_date_end) . "'";
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
$query = "SELECT * FROM MlangOrder_PrintAuto $where_sql ORDER BY no DESC";
$result = mysql_query($query, $connect);

// 데이터 수집
$rows = array();
while ($data = mysql_fetch_array($result)) {
    $order_no = $data['no'];
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    // 박스수량, 택배비 계산
    $r = 1; $w = 3000;
    if(preg_match("/16절/i", $type1_raw)){ $r=2; $w=3000; }
    elseif(preg_match("/a4/i", $type1_raw)){ $r=1; $w=4000; }
    elseif(preg_match("/a5/i", $type1_raw)){ $r=1; $w=4000; }
    elseif(preg_match("/NameCard/i", $data['Type'])){ $r=1; $w=3000; }
    elseif(preg_match("/MerchandiseBond/i", $data['Type'])){ $r=1; $w=3000; }
    elseif(preg_match("/sticker/i", $data['Type'])){ $r=1; $w=3000; }
    elseif(preg_match("/envelop/i", $data['Type'])){ $r=1; $w=3000; }

    // 사용자 수정값 적용
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
    $type = isset($data['Type']) ? trim($data['Type']) : '';
    $full_address = trim($zip1 . ' ' . $zip2);

    // 품목 (Type_1) 처리
    $type_1_display = $type1_raw;
    if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
        $json_data = json_decode($type1_raw, true);
        if ($json_data && isset($json_data['formatted_display'])) {
            $type_1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $json_data['formatted_display']);
        }
    }

    // Column order: 수하인명 | 우편번호 | 주소 | 전화 | 핸드폰 | 박스수량 | 택배비 | 운임구분 | Type | 기타(주문번호) | 품목
    $rows[] = array($name, $zip, $full_address, $phone, $hendphone, $r, $w, $fee_type, $type, $order_no, $type_1_display);
}
mysql_close($connect);

// 엑셀 출력
$filename = "logen_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=euc-kr");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=euc-kr">
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
<td><?php echo htmlspecialchars($row[1]); ?></td>
<td><?php echo htmlspecialchars($row[2]); ?></td>
<td><?php echo htmlspecialchars($row[3]); ?></td>
<td><?php echo htmlspecialchars($row[4]); ?></td>
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
