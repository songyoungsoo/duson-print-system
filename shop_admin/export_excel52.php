<?php
include "lib52.php";
$connect = dbconn();

// 검색 파라미터 받기 (PHP 5.2)
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';

// WHERE 조건 구성
$where_conditions = array();
$where_conditions[] = "(zip1 like '%구%' ) or (zip2 like '%-%')";

if($search_name != '') {
  $search_name = mysqli_real_escape_string($connect, $search_name);
  $where_conditions[] = "name like '%$search_name%'";
}

if($search_company != '') {
  $search_company = mysqli_real_escape_string($connect, $search_company);
  $where_conditions[] = "company like '%$search_company%'";
}

if($search_date_start != '' && $search_date_end != '') {
  $search_date_start = mysqli_real_escape_string($connect, $search_date_start);
  $search_date_end = mysqli_real_escape_string($connect, $search_date_end);
  $where_conditions[] = "Date >= '$search_date_start' and Date <= '$search_date_end'";
} else if($search_date_start != '') {
  $search_date_start = mysqli_real_escape_string($connect, $search_date_start);
  $where_conditions[] = "Date >= '$search_date_start'";
} else if($search_date_end != '') {
  $search_date_end = mysqli_real_escape_string($connect, $search_date_end);
  $where_conditions[] = "Date <= '$search_date_end'";
}

$where_sql = implode(' and ', $where_conditions);

// 데이터 조회 (LIMIT 없이 전체 데이터)
$query = "select * from mlangorder_printauto where $where_sql order by no desc";
$result = mysqli_query($connect, $query);

// 파일명 생성
$filename = "주문목록_" . date('Y-m-d_His') . ".xls";

// 헤더 설정 (엑셀 다운로드)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=" . $filename);
header("Pragma: no-cache");
header("Expires: 0");

// BOM 추가 (한글 깨짐 방지)
echo "\xEF\xBB\xBF";

// 엑셀 테이블 시작
echo "<html xmlns:x=\"urn:schemas-microsoft-com:office:excel\">";
echo "<head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
echo "<style>";
echo "table { border-collapse: collapse; }";
echo "td, th { border: 1px solid #000; padding: 5px; text-align: center; }";
echo "th { background-color: #99CCFF; font-weight: bold; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<table>";
echo "<tr>";
echo "<th>번호</th>";
echo "<th>수하인명</th>";
echo "<th>상호</th>";
echo "<th>우편번호</th>";
echo "<th>주소1</th>";
echo "<th>주소2</th>";
echo "<th>전화</th>";
echo "<th>핸드폰</th>";
echo "<th>박스수량</th>";
echo "<th>택배비</th>";
echo "<th>운임구분</th>";
echo "<th>품목명</th>";
echo "<th>주문일자</th>";
echo "<th>배송메시지</th>";
echo "</tr>";

$row_num = 1;
while($data = mysqli_fetch_array($result)) {
  // 박스수량 및 택배비 계산 로직
  $r = 1; // 기본 박스수량
  $w = 2500; // 기본 택배비

  if(preg_match("/16절/", $data['Type_1'])) {
    $r = 2;
    $w = 3000;
  } else if(preg_match("/a4/", $data['Type_1'])) {
    $r = 1;
    $w = 4000;
  } else if(preg_match("/a5/", $data['Type_1'])) {
    $r = 1;
    $w = 4000;
  } else if(preg_match("/NameCard/", $data['Type'])) {
    $r = 1;
    $w = 2500;
  } else if(preg_match("/MerchandiseBond/", $data['Type'])) {
    $r = 1;
    $w = 2500;
  } else if(preg_match("/sticker/", $data['Type'])) {
    $r = 1;
    $w = 2500;
  } else if(preg_match("/스티카/", $data['Type'])) {
    $r = 1;
    $w = 2500;
  } else if(preg_match("/envelop/", $data['Type'])) {
    $r = 1;
    $w = 3000;
  }

  echo "<tr>";
  echo "<td>" . $row_num . "</td>";
  echo "<td>" . htmlspecialchars(isset($data['name']) ? $data['name'] : '') . "</td>";
  echo "<td>" . htmlspecialchars(isset($data['company']) ? $data['company'] : '') . "</td>";
  echo "<td>" . htmlspecialchars(isset($data['zip']) ? $data['zip'] : '') . "</td>";
  echo "<td>" . htmlspecialchars(isset($data['zip1']) ? $data['zip1'] : '') . "</td>";
  echo "<td>" . htmlspecialchars(isset($data['zip2']) ? $data['zip2'] : '') . "</td>";
  echo "<td>" . htmlspecialchars(isset($data['phone']) ? $data['phone'] : '') . "</td>";
  echo "<td>" . htmlspecialchars(isset($data['Hendphone']) ? $data['Hendphone'] : '') . "</td>";
  echo "<td>" . $r . "</td>";
  echo "<td>" . number_format($w) . "</td>";
  echo "<td>착불</td>";
  echo "<td>" . htmlspecialchars(isset($data['Type_1']) ? $data['Type_1'] : '') . "</td>";
  echo "<td>" . htmlspecialchars(isset($data['Date']) ? $data['Date'] : '') . "</td>";
  echo "<td>" . htmlspecialchars(isset($data['Type']) ? $data['Type'] : '') . "</td>";
  echo "</tr>";

  $row_num++;
}

echo "</table>";
echo "</body>";
echo "</html>";

mysqli_close($connect);
?>
