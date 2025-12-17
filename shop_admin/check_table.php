<?php
require_once __DIR__ . '/../db.php';

echo "<h3>mlangorder_printauto 테이블 구조:</h3>";
$result = mysqli_query($db, "DESCRIBE mlangorder_printauto");

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>컬럼명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td><strong>{$row['Field']}</strong></td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>" . ($row['Default'] ?? '-') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h4>배송 관련 컬럼 찾기:</h4>";
$result2 = mysqli_query($db, "DESCRIBE mlangorder_printauto");
echo "<ul>";
while ($row = mysqli_fetch_assoc($result2)) {
    $field = strtolower($row['Field']);
    if (strpos($field, 'way') !== false ||
        strpos($field, 'track') !== false ||
        strpos($field, 'invoice') !== false ||
        strpos($field, 'bill') !== false ||
        strpos($field, 'delivery') !== false ||
        strpos($field, 'ship') !== false) {
        echo "<li><strong>" . $row['Field'] . "</strong> - " . $row['Type'] . "</li>";
    }
}
echo "</ul>";
