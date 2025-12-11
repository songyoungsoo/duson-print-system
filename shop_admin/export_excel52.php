<?php
include "lib_mysql.php";
$connect = dbconn();

// 선택된 주문번호들 (POST로 전달)
$selected_nos = isset($_POST['selected_nos']) ? $_POST['selected_nos'] : '';

// 검색 파라미터 받기 (GET으로 전달 - 전체 다운로드 시)
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건 생성
$where_conditions = array();
$where_conditions[] = "((zip1 like '%구%') or (zip2 like '%-%'))";

// 선택된 항목 다운로드하는 경우
if($selected_nos != '') {
  $nos_array = explode(',', $selected_nos);
  $nos_cleaned = array();
  foreach($nos_array as $no) {
    $no_val = intval($no);
    if($no_val > 0) {
      $nos_cleaned[] = $no_val;
    }
  }
  if(count($nos_cleaned) > 0) {
    $nos_string = implode(',', $nos_cleaned);
    $where_conditions[] = "no IN ($nos_string)";
  }
} else {
  // 전체 다운로드 - 검색 조건 적용
  if($search_name != '') {
    $search_name = mysql_real_escape_string($search_name, $connect);
    $where_conditions[] = "name like '%$search_name%'";
  }

  if($search_company != '') {
    $search_company = mysql_real_escape_string($search_company, $connect);
    $where_conditions[] = "company like '%$search_company%'";
  }

  if($search_date_start != '' && $search_date_end != '') {
    $search_date_start = mysql_real_escape_string($search_date_start, $connect);
    $search_date_end = mysql_real_escape_string($search_date_end, $connect);
    $where_conditions[] = "date >= '$search_date_start' and date <= '$search_date_end 23:59:59'";
  } else if($search_date_start != '') {
    $search_date_start = mysql_real_escape_string($search_date_start, $connect);
    $where_conditions[] = "date >= '$search_date_start'";
  } else if($search_date_end != '') {
    $search_date_end = mysql_real_escape_string($search_date_end, $connect);
    $where_conditions[] = "date <= '$search_date_end 23:59:59'";
  }

  if($search_no_start != '' && $search_no_end != '') {
    $search_no_start = intval($search_no_start);
    $search_no_end = intval($search_no_end);
    $where_conditions[] = "no >= $search_no_start and no <= $search_no_end";
  } else if($search_no_start != '') {
    $search_no_start = intval($search_no_start);
    $where_conditions[] = "no >= $search_no_start";
  } else if($search_no_end != '') {
    $search_no_end = intval($search_no_end);
    $where_conditions[] = "no <= $search_no_end";
  }
}

$where_sql = implode(' AND ', $where_conditions);

// 데이터 조회 (LIMIT 없이 전체 데이터)
$query = "SELECT * FROM MlangOrder_PrintAuto WHERE $where_sql ORDER BY no DESC";
$result = mysql_query($query, $connect);

// 파일명 생성
$filename = "logen_" . date('Ymd_His') . ".xls";

// UTF-8 인코딩으로 헤더 설정 (엑셀에서 UTF-8 BOM으로 인식)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Pragma: no-cache");
header("Expires: 0");

// UTF-8 BOM 출력 (엑셀에서 한글 인식)
echo "\xEF\xBB\xBF";

// 엑셀 HTML 테이블 시작
echo "<html xmlns:x=\"urn:schemas-microsoft-com:office:excel\">";
echo "<head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
echo "<style>";
echo "table { border-collapse: collapse; }";
echo "td, th { border: 1px solid #000; padding: 5px; text-align: center; mso-number-format:\\@; }";
echo "th { background-color: #99CCFF; font-weight: bold; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<table>";
echo "<tr>";
echo "<th>수하인명</th>";
echo "<th>우편번호</th>";
echo "<th>주소</th>";
echo "<th>전화</th>";
echo "<th>핸드폰</th>";
echo "<th>박스수량</th>";
echo "<th>택배비</th>";
echo "<th>운임구분</th>";
echo "<th>품목명</th>";
echo "<th>기타</th>";
echo "<th>배송메세지</th>";
echo "</tr>";

$count = 0;
while($data = mysql_fetch_array($result)) {
  $count++;

  // 박스수량 및 택배비 계산 로직
  $r = 1; // 기본 박스수량
  $w = 2500; // 기본 택배비

  $type_1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';
  $type_raw = isset($data['Type']) ? $data['Type'] : '';

  // EUC-KR에서 UTF-8로 변환 (DB 데이터)
  $type_1 = @iconv('EUC-KR', 'UTF-8//IGNORE', $type_1_raw);
  $type = @iconv('EUC-KR', 'UTF-8//IGNORE', $type_raw);

  // 변환 실패시 원본 사용
  if($type_1 === false) $type_1 = $type_1_raw;
  if($type === false) $type = $type_raw;

  if(preg_match("/16절/", $type_1)) {
    $r = 2;
    $w = 3000;
  } else if(preg_match("/a4/i", $type_1)) {
    $r = 1;
    $w = 4000;
  } else if(preg_match("/a5/i", $type_1)) {
    $r = 1;
    $w = 4000;
  } else if(preg_match("/NameCard/i", $type)) {
    $r = 1;
    $w = 2500;
  } else if(preg_match("/MerchandiseBond/i", $type)) {
    $r = 1;
    $w = 2500;
  } else if(preg_match("/sticker/i", $type)) {
    $r = 1;
    $w = 2500;
  } else if(preg_match("/스티커/", $type)) {
    $r = 1;
    $w = 2500;
  } else if(preg_match("/envelop/i", $type)) {
    $r = 1;
    $w = 3000;
  }

  // 수하인명 처리 (name이 0이거나 빈값이면 bizname 사용)
  $name_raw = isset($data['name']) ? trim($data['name']) : '';
  $bizname_raw = isset($data['bizname']) ? trim($data['bizname']) : '';

  // EUC-KR에서 UTF-8로 변환
  $name = @iconv('EUC-KR', 'UTF-8//IGNORE', $name_raw);
  $bizname = @iconv('EUC-KR', 'UTF-8//IGNORE', $bizname_raw);
  if($name === false) $name = $name_raw;
  if($bizname === false) $bizname = $bizname_raw;

  if($name == '' || $name == '0') {
    $name = ($bizname != '') ? $bizname : '-';
  }

  // 나머지 필드도 변환
  $zip = isset($data['zip']) ? $data['zip'] : '';
  $zip1_raw = isset($data['zip1']) ? $data['zip1'] : '';
  $zip2_raw = isset($data['zip2']) ? $data['zip2'] : '';
  $phone = isset($data['phone']) ? $data['phone'] : '';
  $hendphone = isset($data['Hendphone']) ? $data['Hendphone'] : '';

  $zip1 = @iconv('EUC-KR', 'UTF-8//IGNORE', $zip1_raw);
  $zip2 = @iconv('EUC-KR', 'UTF-8//IGNORE', $zip2_raw);
  if($zip1 === false) $zip1 = $zip1_raw;
  if($zip2 === false) $zip2 = $zip2_raw;

  echo "<tr>";
  echo "<td>" . htmlspecialchars($name) . "</td>";
  echo "<td>" . htmlspecialchars($zip) . "</td>";
  echo "<td>" . htmlspecialchars($zip1) . " " . htmlspecialchars($zip2) . "</td>";
  echo "<td>" . htmlspecialchars($phone) . "</td>";
  echo "<td>" . htmlspecialchars($hendphone) . "</td>";
  echo "<td>" . $r . "</td>";
  echo "<td>" . number_format($w) . "</td>";
  echo "<td>착불</td>";
  echo "<td>" . htmlspecialchars($type_1) . "</td>";
  echo "<td>&nbsp;</td>";
  echo "<td>" . htmlspecialchars($type) . "</td>";
  echo "</tr>";
}

echo "</table>";
echo "</body>";
echo "</html>";

mysql_close($connect);
?>
