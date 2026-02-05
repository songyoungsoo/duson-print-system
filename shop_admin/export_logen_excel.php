<?php
/**
 * 로젠택배 양식 엑셀 내보내기 (XLSX 형식) - SimpleXLSXGen 사용
 * PHP 7.4 호환 버전 - UTF-8
 * 진짜 .xlsx 파일 생성
 */

include "lib.php";
require_once __DIR__ . '/../db.php';
$connect = $db;

require_once dirname(__FILE__) . '/delivery_rules_config.php';
require_once dirname(__FILE__) . '/delivery_calculator.php';
require_once dirname(__FILE__) . '/SimpleXLSXGen.php';

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
    // 선택된 항목만 내보내기
    $nos_array = explode(',', $selected_nos);
    $nos_cleaned = array();
    foreach ($nos_array as $no) {
        $nos_cleaned[] = intval($no);
    }
    $nos_string = implode(',', $nos_cleaned);
    $where_conditions[] = "no IN ($nos_string)";
} else {
    // 전체 내보내기 시에만 기본 조건 적용 (주소가 있으면 모두 포함)
    $where_conditions[] = "(zip1 IS NOT NULL AND zip1 != '')";
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

// 데이터 배열 생성 (SimpleXLSXGen 형식)
$data_array = array();

// 헤더 행 추가
$data_array[] = array(
    '수하인명',
    '우편번호',
    '주소',
    '전화',
    '핸드폰',
    '박스수량',
    '택배비',
    '운임구분',
    '품목명',
    '기타',
    '배송메세지'
);

// 데이터 행 추가
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
        if ($json_data) {
            if (isset($json_data['formatted_display'])) {
                // formatted_display 있으면 그대로 사용
                $type_1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $json_data['formatted_display']);
            } else {
                // formatted_display 없으면 spec 필드들로 자동 조합
                $parts = array();
                if (!empty($json_data['spec_material'])) $parts[] = $json_data['spec_material'];
                if (!empty($json_data['spec_size'])) $parts[] = $json_data['spec_size'];
                if (!empty($json_data['spec_sides'])) $parts[] = $json_data['spec_sides'];
                if (!empty($json_data['quantity_display'])) $parts[] = $json_data['quantity_display'];
                if (!empty($json_data['spec_design'])) $parts[] = $json_data['spec_design'];
                $type_1_display = !empty($parts) ? implode(' / ', $parts) : $type1_raw;
            }
        }
    }

    // 데이터 행 추가
    $data_array[] = array(
        $data['name'] ?? '',                    // 수하인명
        $zip,                                   // 우편번호
        $full_address,                          // 주소
        $data['phone'] ?? '',                   // 전화
        $data['Hendphone'] ?? '',               // 핸드폰
        $r,                                     // 박스수량
        $w,                                     // 택배비
        $fee_type,                              // 운임구분
        $type_1_display,                        // 품목명
        'dsno' . ($data['no'] ?? ''),          // 기타 (dsno + 주문번호)
        $data['Type'] ?? ''                     // 배송메세지
    );
}

mysqli_close($connect);

// SimpleXLSXGen으로 Excel 파일 생성
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data_array);

// 파일명 생성
$filename = "logen_" . date('Y-m-d_His') . ".xlsx";

// 다운로드 헤더 설정 및 출력
$xlsx->downloadAs($filename);
?>
