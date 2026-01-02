<?php
header('Content-Type: text/plain; charset=utf-8');
include __DIR__ . '/db.php';
mysqli_set_charset($db, 'utf8mb4');

echo "=== 견적서 #128 품목 데이터 ===\n";
$result = mysqli_query($db, "SELECT id, quantity, unit_price, supply_price FROM quote_items WHERE quote_id = 128");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $calc = $row['supply_price'] / $row['quantity'];
        echo "ID: {$row['id']}\n";
        echo "  수량: {$row['quantity']}\n";
        echo "  단가(DB): {$row['unit_price']}\n";
        echo "  공급가: {$row['supply_price']}\n";
        echo "  계산값: {$row['supply_price']} / {$row['quantity']} = " . round($calc, 2) . "\n\n";
    }
} else {
    echo "Error: " . mysqli_error($db) . "\n";
}
?>
