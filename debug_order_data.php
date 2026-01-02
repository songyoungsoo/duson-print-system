<?php
require_once 'db.php';

$sql = "SELECT no, Type, product_type, Type_1 FROM mlangorder_printauto ORDER BY no DESC LIMIT 10";
$result = mysqli_query($db, $sql);

if (!$result) {
    echo "Error: " . mysqli_error($db) . "\n";
    exit;
}

while ($row = mysqli_fetch_assoc($result)) {
    echo "=== Order: " . $row['no'] . " | Type: " . $row['Type'] . " | product_type: " . $row['product_type'] . " ===\n";
    $json = json_decode($row['Type_1'], true);
    if ($json) {
        echo "JSON Keys: " . implode(", ", array_keys($json)) . "\n";
        echo "product_type in JSON: " . ($json['product_type'] ?? 'NOT SET') . "\n";
        echo "MY_amount: " . ($json['MY_amount'] ?? 'NOT SET') . "\n";
    } else {
        echo "Type_1 (text): " . substr($row['Type_1'], 0, 200) . "\n";
    }
    echo "\n";
}
