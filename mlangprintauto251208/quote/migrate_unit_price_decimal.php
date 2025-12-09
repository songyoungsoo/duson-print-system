<?php
/**
 * 견적서 단가 컬럼 타입 변경
 * INT → DECIMAL(10, 1)
 * 전단지 단가 소수점 1자리 지원
 */

require_once __DIR__ . '/../../db.php';

echo "<h2>견적서 단가 컬럼 타입 변경</h2>";

// quote_items 테이블의 unit_price 컬럼을 DECIMAL(10, 1)로 변경
$sql = "ALTER TABLE quote_items MODIFY COLUMN unit_price DECIMAL(10, 1) DEFAULT 0";

if (mysqli_query($db, $sql)) {
    echo "<p style='color: green;'>✅ quote_items.unit_price 컬럼이 DECIMAL(10, 1)로 변경되었습니다.</p>";

    // 변경 확인
    $result = mysqli_query($db, "DESCRIBE quote_items");
    echo "<h3>quote_items 테이블 구조:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $highlight = ($row['Field'] == 'unit_price') ? "style='background: #d4edda;'" : "";
        echo "<tr $highlight>";
        echo "<td>{$row['Field']}</td>";
        echo "<td><strong>{$row['Type']}</strong></td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<p style='margin-top: 20px;'><strong>완료!</strong> 이제 전단지 단가가 소수점 1자리로 저장됩니다.</p>";
    echo "<p>예: 49000 ÷ 2000 = 24.5 (이전: 24)</p>";

} else {
    echo "<p style='color: red;'>❌ 오류: " . mysqli_error($db) . "</p>";
}

mysqli_close($db);
?>
