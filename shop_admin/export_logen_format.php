<?php
/**
 * 로젠택배 iLOGEN 양식 엑셀 내보내기 (UTF-8 + 자동 택배비 계산)
 * PHP 7.4 호환 버전 - UTF-8
 *
 * 개선사항:
 * 1. UTF-8 인코딩 완벽 지원 (BOM 추가)
 * 2. 설정 파일 기반 자동 택배비/박스 수 계산
 * 3. 로젠택배 iLOGEN 컬럼 순서 준수
 * 4. CSV 형식으로 엑셀 호환성 향상
 * 5. PHP 7.4 mysqli 호환
 */

include "lib.php";
// 주문 데이터가 있는 dsp1830 DB 연결
require_once __DIR__ . '/../db.php';
$connect = $db;

require_once dirname(__FILE__) . '/delivery_rules_config.php';
require_once dirname(__FILE__) . '/delivery_calculator.php';

// 택배비 규칙 로드
$deliveryRules = require dirname(__FILE__) . '/delivery_rules_config.php';

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
// 기본 조건 제거 - 모든 데이터 다운로드 가능하도록 수정
// $where_conditions[] = "(zip1 like '%구%' ) or (zip2 like '%-%')";

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
        $search_no_start = intval($search_no_start);
        $search_no_end = intval($search_no_end);
        $where_conditions[] = "no >= $search_no_start and no <= $search_no_end";
    } else if ($search_no_start != '') {
        $search_no_start = intval($search_no_start);
        $where_conditions[] = "no >= $search_no_start";
    } else if ($search_no_end != '') {
        $search_no_end = intval($search_no_end);
        $where_conditions[] = "no <= $search_no_end";
    }
}

// WHERE 절 생성 (조건이 있을 때만)
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(' and ', $where_conditions);
} else {
    $where_sql = "";
}

// 데이터 조회 (LIMIT 없이 전체 데이터) - 테이블명 소문자로 수정
$query = "select * from mlangorder_printauto $where_sql order by no desc";
$result = mysqli_query($connect, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($connect) . "<br>Query: " . $query);
}

// 파일명 생성 - 타임스탬프 포함
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

// 헤더 출력 (수동 CSV 생성 - UTF-8 완벽 지원)
foreach ($headers as $i => $header) {
    if ($i > 0) echo ',';
    // 쉼표나 따옴표가 있으면 따옴표로 감싸기
    if (strpos($header, ',') !== false || strpos($header, '"') !== false) {
        echo '"' . str_replace('"', '""', $header) . '"';
    } else {
        echo $header;
    }
}
echo "\r\n";

// 데이터 출력
while ($data = mysqli_fetch_array($result)) {
    // 택배비 및 박스 수량 자동 계산
    $deliveryInfo = getDeliveryInfo($data, $deliveryRules);
    $box_count = $deliveryInfo['box'];
    $delivery_price = $deliveryInfo['price'];

    // 주소 합치기 (우편번호 제외, 주소만)
    $zip1 = isset($data['zip1']) ? $data['zip1'] : '';
    $zip2 = isset($data['zip2']) ? $data['zip2'] : '';
    $full_address = trim($zip1 . ' ' . $zip2);

    // Type_1 필드 처리 (JSON인 경우 formatted_display 추출)
    $type_1_display = isset($data['Type_1']) ? $data['Type_1'] : '';
    if (!empty($data['Type_1']) && substr(trim($data['Type_1']), 0, 1) === '{') {
        $json_data = json_decode($data['Type_1'], true);
        if ($json_data && isset($json_data['formatted_display'])) {
            $type_1_display = $json_data['formatted_display'];
        }
    }

    // CSV 행 데이터 (로젠택배 iLOGEN 순서)
    $row = array(
        isset($data['no']) ? $data['no'] : '',                              // 주문번호
        isset($data['name']) ? $data['name'] : '',                          // 수하인명
        isset($data['phone']) ? $data['phone'] : '',                        // 수하인전화
        isset($data['Hendphone']) ? $data['Hendphone'] : '',                // 수하인휴대폰
        $full_address,                                                       // 수하인주소 (우편번호 제외)
        $type_1_display,                                                     // 물품명 (JSON 처리)
        $box_count,                                                          // 수량(박스) - 자동 계산
        isset($data['Type']) ? $data['Type'] : ''                           // 배송메세지
    );

    // 행 출력 (수동 CSV 생성 - UTF-8 완벽 지원)
    foreach ($row as $i => $field) {
        if ($i > 0) echo ',';
        // 쉼표, 따옴표, 줄바꿈이 있으면 따옴표로 감싸기
        if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false || strpos($field, "\r") !== false) {
            echo '"' . str_replace('"', '""', $field) . '"';
        } else {
            echo $field;
        }
    }
    echo "\r\n";
}

mysqli_close($connect);
?>
