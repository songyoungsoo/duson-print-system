<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/ProductSpecFormatter.php';

$sessionId = session_id();
echo "현재 세션 ID: {$sessionId}\n\n";

$query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no ASC";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $sessionId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$formatter = new ProductSpecFormatter($db);

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "장바구니 품목 디버그\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$index = 0;
while ($item = mysqli_fetch_assoc($result)) {
    $index++;
    $productType = $item['product_type'] ?? '';
    if (empty($productType) && !empty($item['jong'])) $productType = 'sticker';

    $qty = ProductSpecFormatter::getQuantity($item);
    $qtyDisplay = ProductSpecFormatter::getQuantityDisplay($item);

    echo "{$index}. 품목 no={$item['no']}\n";
    echo "   product_type: {$productType}\n";
    echo "   MY_amount: " . ($item['MY_amount'] ?? 'NULL') . "\n";
    echo "   mesu: " . ($item['mesu'] ?? 'NULL') . "\n";
    echo "   jong: " . ($item['jong'] ?? 'NULL') . "\n";
    echo "   → getQuantity(): {$qty}\n";
    echo "   → getQuantityDisplay(): {$qtyDisplay}\n";
    echo "\n";
}

if ($index === 0) {
    echo "❌ 현재 세션에 장바구니 품목이 없습니다.\n";
}

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
