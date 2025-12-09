<?php
/**
 * 로젠택배 iLOGEN 양식 CSV 내보내기
 * PHP 5.2 호환 / EUC-KR DB -> UTF-8 출력
 */

// lib.php의 style 태그 출력을 버퍼링하여 무시
ob_start();
include "lib.php";
ob_end_clean();
$connect = dbconn();

require_once dirname(__FILE__) . '/delivery_rules_config.php';
require_once dirname(__FILE__) . '/delivery_calculator.php';

// 택배비 규칙 로드
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

// 검색 파라미터 받기 (GET으로 전달 - 전체 다운로드 시)
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건 구성
$where_conditions = array();

// 선택된 항목 다운로드하는 경우
if ($selected_nos != '') {
    $nos_array = explode(',', $selected_nos);
    $nos_cleaned = array();
    foreach ($nos_array as $no) {
        $nos_cleaned[] = intval($no);
    }
    $nos_string = implode(',', $nos_cleaned);
    $where_conditions[] = "no IN ($nos_string)";
} else {
    // 전체 다운로드 - 검색 조건 적용
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

// WHERE 절 생성
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(' and ', $where_conditions);
} else {
    $where_sql = "";
}

// 데이터 조회
$query = "select * from MlangOrder_PrintAuto $where_sql order by no desc";
$result = mysql_query($query, $connect);

if (!$result) {
    die("Query Error: " . mysql_error());
}

// 파일명 생성
$filename = "logen_" . date('Y-m-d_His') . ".csv";

// 헤더 설정 (CSV 다운로드)
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Pragma: no-cache");
header("Expires: 0");

// UTF-8 BOM 추가 (엑셀에서 한글 깨짐 방지)
echo "\xEF\xBB\xBF";

// 로젠택배 iLOGEN 양식 헤더 (8개 컬럼)
$headers = array(
    '주문번호',
    '수하인명',
    '수하인전화',
    '수하인휴대폰',
    '수하인주소',
    '물품명',
    '수량(박스)',
    '배송메세지'
);

// 헤더 출력 (UTF-8로 변환)
$header_line = array();
foreach ($headers as $header) {
    $header_line[] = to_utf8($header);
}
echo implode(',', $header_line) . "\r\n";

// 데이터 출력
while ($data = mysql_fetch_array($result)) {
    // 택배비 및 박스 수량 자동 계산
    $deliveryInfo = getDeliveryInfo($data, $deliveryRules);
    $box_count = $deliveryInfo['box'];

    // 주소 합치기 (우편번호 제외, 주소만)
    $zip1 = isset($data['zip1']) ? $data['zip1'] : '';
    $zip2 = isset($data['zip2']) ? $data['zip2'] : '';
    $full_address = trim($zip1 . ' ' . $zip2);

    // Type_1 필드 처리 (JSON인 경우 formatted_display 추출)
    $type_1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';
    $type_1_display = $type_1_raw;
    if (!empty($type_1_raw) && substr(trim($type_1_raw), 0, 1) === '{') {
        $json_data = json_decode($type_1_raw, true);
        if ($json_data && isset($json_data['formatted_display'])) {
            $type_1_display = $json_data['formatted_display'];
        }
    }

    // CSV 행 데이터 (로젠택배 iLOGEN 순서)
    $row = array(
        isset($data['no']) ? $data['no'] : '',
        isset($data['name']) ? $data['name'] : '',
        isset($data['phone']) ? $data['phone'] : '',
        isset($data['Hendphone']) ? $data['Hendphone'] : '',
        $full_address,
        $type_1_display,
        $box_count,
        isset($data['Type']) ? $data['Type'] : ''
    );

    // UTF-8로 변환하여 출력
    $output_row = array();
    foreach ($row as $field) {
        $utf8_field = to_utf8($field);
        // 쉼표, 따옴표, 줄바꿈이 있으면 따옴표로 감싸기
        if (strpos($utf8_field, ',') !== false || strpos($utf8_field, '"') !== false || strpos($utf8_field, "\n") !== false) {
            $output_row[] = '"' . str_replace('"', '""', $utf8_field) . '"';
        } else {
            $output_row[] = $utf8_field;
        }
    }
    echo implode(',', $output_row) . "\r\n";
}

mysql_close($connect);
?>
