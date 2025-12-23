<?php
/**
 * 견적서 테이블 표시 테스트
 */
require_once __DIR__ . '/../../db.php';

$quote_id = 142; // 매수 정보가 있는 전단지 견적

// 견적서 항목 조회
$query = "SELECT * FROM quote_items WHERE quote_id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $quote_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo "=== 견적서 #{$quote_id} 테이블 표시 테스트 ===\n\n";

foreach ($items as $index => $item) {
    echo "항목 " . ($index + 1) . ":\n";
    echo "- 품목: " . $item['product_name'] . "\n";
    echo "- 규격: " . $item['specification'] . "\n";

    // 수량 표시 로직 (detail.php와 동일)
    $productType = $item['product_type'] ?? '';
    if (in_array($productType, ['inserted', 'leaflet'])) {
        // 전단지/리플렛: 연수 + (매수) 형식
        $sourceData = json_decode($item['source_data'], true);
        $yeonsu = floatval($item['quantity']);
        $yeonDisplay = rtrim(rtrim(sprintf('%.1f', $yeonsu), '0'), '.') . '연';
        if (!empty($sourceData['mesu'])) {
            $yeonDisplay .= ' (' . number_format($sourceData['mesu']) . '매)';
        }
        echo "- 수량: " . $yeonDisplay . "\n";
    } else {
        // 기타 제품: 일반 수량
        $qty = floatval($item['quantity']);
        $qtyDisplay = (floor($qty) == $qty) ? number_format($qty) : number_format($qty, 1);
        echo "- 수량: " . $qtyDisplay . "\n";
    }

    echo "- 단위: " . $item['unit'] . "\n";
    echo "- 총액: " . number_format($item['total_price']) . " 원\n";
    echo "\n";
}

echo "\n=== 테스트 완료 ===\n";
?>
