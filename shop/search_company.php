<?php
include "../db.php";  // 데이터베이스 연결

$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

// 데이터베이스 연결
$db = mysql_connect($host, $user, $password);
if (!$db) {
    die(json_encode(array("error" => "데이터베이스 연결 실패: " . mysql_error())));
}

mysql_select_db($dataname, $db);
mysql_query("SET NAMES 'utf8'", $db);  // 🔹 문자셋 설정 (필요한 경우 'utf8'으로 변경 가능)

// 🔹 검색어 가져오기 및 URL 디코딩 추가
$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

if ($searchTerm == '') {
    die(json_encode(array("error" => "검색어가 비어 있습니다.")));
}

$searchTerm = urldecode($searchTerm);  // 🔹 한글 URL 디코딩 추가

// ✅ UTF-8 환경일 경우 UTF-8 → UTF-8 변환
$searchTerm = iconv("UTF-8", "UTF-8", $searchTerm);

// ✅ SQL Injection 방지
$searchTerm = mysql_real_escape_string($searchTerm);
$searchTermLike = "%" . $searchTerm . "%";  

// 🔹 SQL 실행
$query = "SELECT id, name, email, phone1, phone2, phone3, hendphone1, hendphone2, hendphone3, sample6_postcode AS postcode, 
          sample6_address AS address, sample6_detailAddress AS detailAddress, sample6_extraAddress AS extraAddress, 
          po1, po2, po3, po4, po5, po6 
          FROM member 
          WHERE name LIKE '$searchTermLike' 
          LIMIT 10";
          
error_log("SQL 실행됨: " . $query);  // 🔹 SQL 로그 남기기

$result = mysql_query($query, $db);

if (!$result) {
    die(json_encode(array("error" => "SQL 실행 오류: " . mysql_error())));
}

// 데이터 배열 초기화
$data = array();
while ($row = mysql_fetch_assoc($result)) {
    // NULL 값을 빈 문자열("")로 변환 + 한글 변환// 🔹 데이터 가져오기 및 한글 변환
    foreach ($row as $key => $value) {
        if ($value !== null) {
            $row[$key] = iconv("UTF-8", "UTF-8", $value);
        }
    }

    $data[] = array(
        "label" => $row['name'],
        "id" => $row['id'],
        "name" => $row['name'],
        "email" => $row['email'],
        "phone1" => $row['phone1'],
        "phone2" => $row['phone2'],
        "phone3" => $row['phone3'],
        "hendphone1" => $row['hendphone1'],
        "hendphone2" => $row['hendphone2'],
        "hendphone3" => $row['hendphone3'],
        "postcode" => $row['postcode'],
        "address" => $row['address'],
        "detailAddress" => $row['detailAddress'],
        "extraAddress" => $row['extraAddress'],
        "po1" => $row['po1'],
        "po2" => $row['po2'],
        "po3" => $row['po3'],
        "po4" => $row['po4'],
        "po5" => $row['po5'],
        "po6" => $row['po6']
    );
}

// 🚨 검색된 데이터가 없을 때 확인
if (empty($data)) {
    die(json_encode(array("error" => "No data found for '$searchTerm'")));
}

// ✅ PHP 5.3에서는 JSON_UNESCAPED_UNICODE를 지원하지 않으므로 str_replace()를 사용
$json_data = str_replace("\\/", "/", json_encode($data)); 

// ✅ JSON 데이터 출력
header('Content-Type: application/json; charset=UTF-8');
echo $json_data;
?>