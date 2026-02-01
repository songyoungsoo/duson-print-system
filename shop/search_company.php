<?php
/**
 * 회사명/이름 검색 API (users 테이블 사용)
 * 
 * 마이그레이션: member → users 테이블
 * - mysql_* → mysqli prepared statements
 * - member.id → users.username
 * - member.phone1/2/3 → users.phone (combined)
 * - member.sample6_* → users.postcode/address/detail_address/extra_address
 * - member.po1-6 → users.business_number/name/owner/type/item/address
 */

include $_SERVER['DOCUMENT_ROOT'] . "/db.php";

$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

if ($searchTerm == '') {
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array("error" => "검색어가 비어 있습니다.")));
}

$searchTerm = urldecode($searchTerm);
$searchTermLike = "%" . $searchTerm . "%";

$query = "SELECT username, name, email, phone, 
          postcode, address, detail_address, extra_address,
          business_number, business_name, business_owner, 
          business_type, business_item, business_address
          FROM users 
          WHERE name LIKE ? 
          LIMIT 10";

$stmt = mysqli_prepare($db, $query);
if (!$stmt) {
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array("error" => "SQL 준비 오류")));
}

mysqli_stmt_bind_param($stmt, "s", $searchTermLike);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array("error" => "SQL 실행 오류")));
}

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    // phone → phone1/2/3 분리 (레거시 API 호환)
    $phone_parts = explode('-', $row['phone'] ?? '');
    $phone1 = $phone_parts[0] ?? '';
    $phone2 = $phone_parts[1] ?? '';
    $phone3 = $phone_parts[2] ?? '';

    $data[] = array(
        "label" => $row['name'],
        "id" => $row['username'],
        "name" => $row['name'],
        "email" => $row['email'] ?? '',
        "phone1" => $phone1,
        "phone2" => $phone2,
        "phone3" => $phone3,
        "hendphone1" => $phone1,
        "hendphone2" => $phone2,
        "hendphone3" => $phone3,
        "postcode" => $row['postcode'] ?? '',
        "address" => $row['address'] ?? '',
        "detailAddress" => $row['detail_address'] ?? '',
        "extraAddress" => $row['extra_address'] ?? '',
        "po1" => $row['business_number'] ?? '',   // po1-6: 레거시 API 키 유지
        "po2" => $row['business_name'] ?? '',
        "po3" => $row['business_owner'] ?? '',
        "po4" => $row['business_type'] ?? '',
        "po5" => $row['business_item'] ?? '',
        "po6" => $row['business_address'] ?? ''
    );
}

mysqli_stmt_close($stmt);

if (empty($data)) {
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array("error" => "No data found for '$searchTerm'")));
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
