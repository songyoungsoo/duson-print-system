<?php
/**
 * create.php의 quotation_temp 로딩 로직 테스트
 */
session_start();
require_once '/var/www/html/db.php';
require_once '/var/www/html/includes/ProductSpecFormatter.php';

// 테스트 세션 ID (test_sticker_quantity.php에서 생성한 것)
$sessionId = session_id();

echo "Session ID: $sessionId\n\n";

// create.php와 동일한 쿼리
$query = "SELECT * FROM quotation_temp WHERE session_id = ? ORDER BY regdate ASC";
$stmt = mysqli_prepare($db, $query);

if (!$stmt) {
    echo "❌ 쿼리 준비 실패: " . mysqli_error($db) . "\n";
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $sessionId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$quoteTempItems = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quoteTempItems[] = $row;
}
mysqli_stmt_close($stmt);

echo "총 " . count($quoteTempItems) . "개 품목 로드됨\n\n";

if (count($quoteTempItems) == 0) {
    echo "❌ quotation_temp에 데이터 없음\n";
    echo "테스트 데이터를 먼저 추가하세요: php /tmp/test_sticker_quantity.php\n";
    exit;
}

// 각 품목 처리 (create.php 로직 재현)
foreach ($quoteTempItems as $index => $item) {
    echo "=== 품목 #" . ($index + 1) . " ===\n";
    echo "product_type: " . ($item['product_type'] ?? 'NULL') . "\n";
    echo "mesu: " . ($item['mesu'] ?? 'NULL') . "\n";
    echo "quantity_display isset: " . (isset($item['quantity_display']) ? 'YES' : 'NO') . "\n";
    echo "quantity_display value: " . ($item['quantity_display'] ?? 'NULL') . "\n";
    echo "quantity_display empty: " . (empty($item['quantity_display']) ? 'YES' : 'NO') . "\n";
    echo "data_version: " . ($item['data_version'] ?? 'NULL') . "\n";
    echo "\n";

    // create.php Line 513과 동일
    $qtyDisplay = ProductSpecFormatter::getQuantityDisplay($item);

    echo "getQuantityDisplay() 결과: $qtyDisplay\n";

    if ($qtyDisplay == '1') {
        echo "❌ 문제 발생! '1'이 반환됨\n";
    } elseif ($qtyDisplay == '1,000' || $qtyDisplay == '1000') {
        echo "✅ 정상! '$qtyDisplay'가 반환됨\n";
    } else {
        echo "⚠️  예상치 못한 값: '$qtyDisplay'\n";
    }

    echo "\n";
    echo str_repeat("-", 50) . "\n\n";
}

mysqli_close($db);
