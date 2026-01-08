<?php
/**
 * 주문 #84445 표시 테스트
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
require_once 'includes/ProductSpecFormatter.php';

$formatter = new ProductSpecFormatter($db);

// 주문 #84445 조회
$query = "SELECT no, Type, Type_1, product_type FROM mlangorder_printauto WHERE no = 84445";
$result = mysqli_query($db, $query);
$order = mysqli_fetch_assoc($result);

echo "<h2>주문 #84445 테스트</h2>";
echo "<pre>";
echo "Type: " . $order['Type'] . "\n";
echo "product_type: " . $order['product_type'] . "\n\n";

// Type_1 JSON 파싱
$type_data = $order['Type_1'];
$json_data = json_decode($type_data, true);

echo "Type_1 JSON 구조:\n";
print_r($json_data);
echo "\n";

// data_version 확인
echo "data_version: " . ($json_data['data_version'] ?? 'NULL') . "\n";
echo "spec_type: " . ($json_data['spec_type'] ?? 'NULL') . "\n";
echo "spec_material: " . ($json_data['spec_material'] ?? 'NULL') . "\n";
echo "quantity_display: " . ($json_data['quantity_display'] ?? 'NULL') . "\n\n";

// OrderComplete_universal.php와 동일한 로직
if (isset($json_data['data_version']) && $json_data['data_version'] == 2) {
    $item = array_merge($order, $json_data);
    echo "✅ 신규 데이터 감지 (data_version=2) - flat structure 사용\n\n";
} else {
    if (isset($json_data['order_details'])) {
        $item = array_merge($order, $json_data['order_details']);
        $item['product_type'] = $json_data['product_type'] ?? '';
        echo "⚠️ 레거시 데이터 - nested structure (order_details) 사용\n\n";
    } else {
        $item = array_merge($order, $json_data);
        echo "⚠️ 레거시 데이터 - flat structure 사용\n\n";
    }
}

echo "최종 \$item 구조:\n";
echo "  spec_type: " . ($item['spec_type'] ?? 'NULL') . "\n";
echo "  spec_material: " . ($item['spec_material'] ?? 'NULL') . "\n";
echo "  spec_size: " . ($item['spec_size'] ?? 'NULL') . "\n";
echo "  quantity_display: " . ($item['quantity_display'] ?? 'NULL') . "\n";
echo "  data_version: " . ($item['data_version'] ?? 'NULL') . "\n\n";

// ProductSpecFormatter 테스트
echo "=== ProductSpecFormatter 테스트 ===\n\n";
$specs = $formatter->format($item);

echo "line1: " . ($specs['line1'] ?? 'EMPTY') . "\n";
echo "line2: " . ($specs['line2'] ?? 'EMPTY') . "\n";
echo "additional: " . ($specs['additional'] ?? 'EMPTY') . "\n";

echo "</pre>";

// HTML 표시
?>
<hr>
<h3>실제 표시 결과</h3>
<div class="specs-cell" style="line-height: 1.6;">
    <?php if (!empty($specs['line1'])): ?>
        <div style="color: #2d3748; margin-bottom: 2px;"><?php echo htmlspecialchars($specs['line1']); ?></div>
    <?php else: ?>
        <div style="color: red;">❌ line1이 비어있습니다!</div>
    <?php endif; ?>

    <?php if (!empty($specs['line2'])): ?>
        <div style="color: #4a5568;"><?php echo htmlspecialchars($specs['line2']); ?></div>
    <?php else: ?>
        <div style="color: red;">❌ line2가 비어있습니다!</div>
    <?php endif; ?>
</div>
