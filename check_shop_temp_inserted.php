<?php
// shop_temp 테이블의 전단지 데이터 분석
include "db.php";

mysqli_set_charset($db, "utf8");

echo "<h2>shop_temp 테이블 - 전단지(inserted) 데이터 분석</h2>";

// 1. 최근 전단지 데이터 조회
$query = "SELECT no, session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, mesu, POtype, ordertype, st_price, st_price_vat 
          FROM shop_temp 
          WHERE product_type IN ('inserted', 'leaflet') 
          ORDER BY no DESC 
          LIMIT 10";

$result = mysqli_query($db, $query);

if ($result) {
    echo "<h3>최근 전단지 장바구니 데이터 (최근 10개):</h3>";
    echo "<table border='1' style='border-collapse: collapse; font-size: 12px;'>";
    echo "<tr>";
    echo "<th>no</th>";
    echo "<th>product_type</th>";
    echo "<th>MY_amount<br>(연수)</th>";
    echo "<th>mesu<br>(매수)</th>";
    echo "<th>MY_type</th>";
    echo "<th>PN_type</th>";
    echo "<th>MY_Fsd</th>";
    echo "<th>POtype</th>";
    echo "<th>ordertype</th>";
    echo "<th>st_price</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['no'] . "</td>";
        echo "<td>" . $row['product_type'] . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['MY_amount']) . "</strong></td>";
        echo "<td><strong>" . htmlspecialchars($row['mesu']) . "</strong></td>";
        echo "<td>" . $row['MY_type'] . "</td>";
        echo "<td>" . $row['PN_type'] . "</td>";
        echo "<td>" . $row['MY_Fsd'] . "</td>";
        echo "<td>" . $row['POtype'] . "</td>";
        echo "<td>" . $row['ordertype'] . "</td>";
        echo "<td>" . number_format($row['st_price']) . "원</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>분석:</h3>";
    echo "<ul>";
    echo "<li><strong>MY_amount</strong>: 연수 (예: 0.5, 1, 2 등)</li>";
    echo "<li><strong>mesu</strong>: 실제 매수 (예: 2000, 4000 등)</li>";
    echo "<li>장바구니 표시: MY_amount + '연 (' + mesu + '매)'</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>쿼리 오류: " . mysqli_error($db) . "</p>";
}

// 2. shop_temp 테이블 구조 확인
echo "<h3>shop_temp 테이블 구조 (MY_amount, mesu 컬럼):</h3>";
$columns_query = "SHOW COLUMNS FROM shop_temp WHERE Field IN ('MY_amount', 'mesu')";
$columns_result = mysqli_query($db, $columns_query);

if ($columns_result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($col = mysqli_fetch_assoc($columns_result)) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($db);
?>
