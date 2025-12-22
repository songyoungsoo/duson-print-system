<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../db.php";

echo "<h3>테이블 구조 확인</h3>";

// 테이블 구조 확인
$query = "DESCRIBE mlangorder_printauto";
$result = safe_mysqli_query($db, $query);

echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr><h3>샘플 데이터 (최근 1개)</h3>";

// 샘플 데이터 확인
$query = "select * from mlangorder_printauto where (zip1 like '%구%' ) or (zip2 like '%-%') order by no desc limit 1";
$result = safe_mysqli_query($db, $query);

if($data = mysqli_fetch_assoc($result)) {
    echo "<table border='1'>";
    foreach($data as $key => $value) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
        echo "<td>" . htmlspecialchars(substr($value, 0, 100)) . (strlen($value) > 100 ? "..." : "") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
