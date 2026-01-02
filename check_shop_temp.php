<?php
include "db.php";

$query = "DESCRIBE shop_temp";
$result = mysqli_query($db, $query);

echo "<h2>shop_temp 테이블 구조</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
    echo "</tr>";
}
echo "</table>";

mysqli_close($db);
?>
<style>
table { border-collapse: collapse; }
th { background: #4CAF50; color: white; padding: 8px; }
td { padding: 5px; }
</style>
