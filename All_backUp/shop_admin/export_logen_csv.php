<?php
/**
 * 로젠택배 양식 CSV 내보내기
 * PHP 5.2 호환 버전
 */

ob_start();
include "lib.php";
$connect = dbconn();
mysql_select_db("duson1830", $connect);
ob_end_clean();

// 선택된 주문번호들
$selected_nos = isset($_POST['selected_nos']) ? $_POST['selected_nos'] : '';

// 사용자가 수정한 값들
$custom_box_qty = array();
$custom_delivery_fee = array();
$custom_fee_type = array();

if (isset($_POST['box_qty_json']) && !empty($_POST['box_qty_json'])) {
    $decoded = json_decode($_POST['box_qty_json'], true);
    $custom_box_qty = is_array($decoded) ? $decoded : array();
}
if (isset($_POST['delivery_fee_json']) && !empty($_POST['delivery_fee_json'])) {
    $decoded = json_decode($_POST['delivery_fee_json'], true);
    $custom_delivery_fee = is_array($decoded) ? $decoded : array();
}
if (isset($_POST['fee_type_json']) && !empty($_POST['fee_type_json'])) {
    $decoded = json_decode($_POST['fee_type_json'], true);
    $custom_fee_type = is_array($decoded) ? $decoded : array();
}

// 검색 파라미터
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건
$where_conditions = array();

if ($selected_nos != '') {
    $nos_array = explode(',', $selected_nos);
    $nos_cleaned = array();
    foreach ($nos_array as $no) {
        $nos_cleaned[] = intval($no);
    }
    $nos_string = implode(',', $nos_cleaned);
    $where_conditions[] = "no IN ($nos_string)";
} else {
    $where_conditions[] = "(zip1 like '%구%') or (zip2 like '%-%')";
    if ($search_name != '') {
        $search_name_esc = mysql_real_escape_string($search_name);
        $where_conditions[] = "name like '%$search_name_esc%'";
    }
    if ($search_date_start != '' && $search_date_end != '') {
        $where_conditions[] = "date >= '" . mysql_real_escape_string($search_date_start) . "' and date <= '" . mysql_real_escape_string($search_date_end) . "'";
    }
    if ($search_no_start != '' && $search_no_end != '') {
        $where_conditions[] = "no >= " . intval($search_no_start) . " and no <= " . intval($search_no_end);
    }
}

$where_sql = count($where_conditions) > 0 ? "WHERE " . implode(' and ', $where_conditions) : "";
$query = "select * from MlangOrder_PrintAuto $where_sql order by no desc";
$result = mysql_query($query);

if (!$result) {
    die("Query Error: " . mysql_error());
}

// CSV 헤더
$filename = "logen_" . date('Y-m-d_His') . ".csv";
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Pragma: no-cache");
header("Expires: 0");

// UTF-8 BOM
echo "\xEF\xBB\xBF";

// CSV 헤더 행
echo "수하인명,우편번호,주소,전화,핸드폰,박스수량,택배비,운임구분,품목명,기타,배송메세지\n";

// 데이터 행
while ($data = mysql_fetch_array($result)) {
    $order_no = $data['no'];
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    // 박스수량/택배비 계산
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

    if (isset($custom_box_qty[$order_no])) {
        $r = intval($custom_box_qty[$order_no]);
    }
    if (isset($custom_delivery_fee[$order_no])) {
        $w = intval($custom_delivery_fee[$order_no]);
    }
    $fee_type = '착불';
    if (isset($custom_fee_type[$order_no])) {
        $fee_type = $custom_fee_type[$order_no];
    }

    // 주소
    $zip = isset($data['zip']) ? $data['zip'] : '';
    $zip1 = isset($data['zip1']) ? $data['zip1'] : '';
    $zip2 = isset($data['zip2']) ? $data['zip2'] : '';
    $full_address = trim($zip1 . ' ' . $zip2);

    // Type_1 처리
    $type_1_display = $type1_raw;
    if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
        $json_data = json_decode($type1_raw, true);
        if ($json_data && isset($json_data['formatted_display'])) {
            $type_1_display = $json_data['formatted_display'];
        }
    }
    // 줄바꿈을 공백으로 변환 (CSV용)
    $type_1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $type_1_display);

    // CSV 이스케이프 함수
    $name = isset($data['name']) ? $data['name'] : '';
    $phone = isset($data['phone']) ? $data['phone'] : '';
    $hendphone = isset($data['Hendphone']) ? $data['Hendphone'] : '';
    $type = isset($data['Type']) ? $data['Type'] : '';

    // CSV 출력 (쉼표가 포함된 필드는 따옴표로 감싸기)
    $row = array(
        '"' . str_replace('"', '""', $name) . '"',
        $zip,
        '"' . str_replace('"', '""', $full_address) . '"',
        $phone,
        $hendphone,
        $r,
        $w,
        $fee_type,
        '"' . str_replace('"', '""', $type_1_display) . '"',
        '',
        '"' . str_replace('"', '""', $type) . '"'
    );

    echo implode(',', $row) . "\n";
}

mysql_close($connect);
?>
