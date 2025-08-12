<?php
session_start();
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

if (!$connect) {
    die('데이터베이스 연결 실패: ' . mysqli_connect_error());
}

mysqli_set_charset($connect, "utf8");

echo "<h2>현재 세션 ID: " . htmlspecialchars($session_id) . "</h2>";

// shop_temp 테이블 구조 확인
echo "<h3>shop_temp 테이블 구조:</h3>";
$structure_query = "DESCRIBE shop_temp";
$result = mysqli_query($connect, $structure_query);

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "테이블 구조 조회 실패: " . mysqli_error($connect);
}

// 현재 장바구니 데이터 확인
echo "<h3>현재 장바구니 데이터:</h3>";
$cart_query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
$stmt = mysqli_prepare($connect, $cart_query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>no</th><th>product_type</th><th>jong</th><th>garo</th><th>sero</th><th>mesu</th><th>uhyung</th><th>domusong</th><th>st_price</th><th>st_price_vat</th><th>MY_type</th><th>MY_Fsd</th><th>PN_type</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['product_type'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['jong'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['garo'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['sero'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['mesu'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['uhyung'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['domusong'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['st_price'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['st_price_vat'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['MY_type'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['MY_Fsd'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['PN_type'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "장바구니가 비어있습니다.";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "쿼리 준비 실패: " . mysqli_error($connect);
}

// 전체 shop_temp 테이블 데이터 확인 (다른 세션 포함)
echo "<h3>전체 shop_temp 테이블 데이터:</h3>";
$all_query = "SELECT * FROM shop_temp ORDER BY no DESC LIMIT 10";
$result = mysqli_query($connect, $all_query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>no</th><th>session_id</th><th>product_type</th><th>jong</th><th>garo</th><th>sero</th><th>mesu</th><th>st_price</th><th>st_price_vat</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['no']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($row['session_id'], 0, 10)) . "...</td>";
            echo "<td>" . htmlspecialchars($row['product_type'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['jong'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['garo'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['sero'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['mesu'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['st_price'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['st_price_vat'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "shop_temp 테이블에 데이터가 없습니다.";
    }
} else {
    echo "전체 데이터 조회 실패: " . mysqli_error($connect);
}

mysqli_close($connect);
?>

<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>