<?php
require_once __DIR__ . '/../db.php';

// 최근 주문 10개 확인
$query = "SELECT no, name, Type, logen_tracking_no, waybill_date, delivery_company FROM mlangorder_printauto ORDER BY no DESC LIMIT 10";
$result = mysqli_query($db, $query);

if (!$result) {
    die("<p style='color:red;'>SQL Error: " . mysqli_error($db) . "</p>");
}

echo "<h3>최근 주문 10개:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>주문번호</th><th>이름</th><th>제품</th><th>송장번호</th><th>택배사</th><th>등록일</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    $tracking_no = htmlspecialchars($row['logen_tracking_no'] ?? '-');
    $company = htmlspecialchars($row['delivery_company'] ?? '-');
    $date = $row['waybill_date'] ?? '-';
    echo "<tr>";
    echo "<td>{$row['no']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$tracking_no}</td>";
    echo "<td>{$company}</td>";
    echo "<td>{$date}</td>";
    echo "</tr>";
}
echo "</table>";

// 송장번호 없는 주문 개수 확인
$countQuery = "SELECT COUNT(*) as count FROM mlangorder_printauto WHERE logen_tracking_no IS NULL OR logen_tracking_no = '' OR logen_tracking_no = '0'";
$countResult = mysqli_query($db, $countQuery);
$count = mysqli_fetch_assoc($countResult)['count'];

echo "<p>송장번호 없는 주문: <strong>{$count}건</strong></p>";
