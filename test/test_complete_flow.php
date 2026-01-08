<?php
/**
 * 전체 플로우 테스트: 데이터 추가 → 조회 → ProductSpecFormatter 테스트
 */
session_start();
require_once '/var/www/html/db.php';
require_once '/var/www/html/includes/ProductSpecFormatter.php';

$sessionId = session_id();
echo "Session ID: $sessionId\n\n";

// Step 1: 테스트 데이터 추가
echo "=== Step 1: 테스트 데이터 추가 ===\n";

$query = "INSERT INTO quotation_temp (
    session_id, product_type,
    jong, garo, sero, domusong, mesu,
    st_price, st_price_vat,
    quantity_display,
    data_version,
    regdate
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())";

$stmt = mysqli_prepare($db, $query);

$productType = 'sticker';
$jong = '유포지';
$garo = '100';
$sero = '100';
$domusong = '사각';
$mesu = '1000';
$st_price = 50000;
$st_price_vat = 55000;
$quantity_display = '1,000';
$data_version = 2;

mysqli_stmt_bind_param($stmt, "ssssssddssi",
    $sessionId, $productType,
    $jong, $garo, $sero, $domusong, $mesu,
    $st_price, $st_price_vat,
    $quantity_display,
    $data_version
);

if (mysqli_stmt_execute($stmt)) {
    echo "✅ 테스트 데이터 추가 성공\n";
} else {
    echo "❌ 오류: " . mysqli_error($db) . "\n";
    exit;
}
mysqli_stmt_close($stmt);

echo "\n";

// Step 2: 데이터 조회 (create.php와 동일한 방식)
echo "=== Step 2: 데이터 조회 (create.php 로직) ===\n";

$query = "SELECT * FROM quotation_temp WHERE session_id = ? ORDER BY regdate ASC";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $sessionId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$quoteTempItems = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quoteTempItems[] = $row;
}
mysqli_stmt_close($stmt);

echo "총 " . count($quoteTempItems) . "개 품목 로드됨\n\n";

// Step 3: 각 품목 테스트
echo "=== Step 3: ProductSpecFormatter 테스트 ===\n";

foreach ($quoteTempItems as $index => $item) {
    echo "\n품목 #" . ($index + 1) . ":\n";
    echo str_repeat("-", 50) . "\n";
    echo "product_type: " . ($item['product_type'] ?? 'NULL') . "\n";
    echo "mesu: " . ($item['mesu'] ?? 'NULL') . "\n";
    echo "quantity_display isset: " . (isset($item['quantity_display']) ? 'YES' : 'NO') . "\n";
    echo "quantity_display value: '" . ($item['quantity_display'] ?? 'NULL') . "'\n";
    echo "quantity_display empty: " . (empty($item['quantity_display']) ? 'YES' : 'NO') . "\n";
    echo "data_version: " . ($item['data_version'] ?? 'NULL') . "\n";
    echo "\n";

    echo "ProductSpecFormatter::getQuantityDisplay() 호출 중...\n";

    // 디버그 로그가 error_log로 출력될 것임
    $qtyDisplay = ProductSpecFormatter::getQuantityDisplay($item);

    echo "\n";
    echo "결과: '$qtyDisplay'\n";
    echo "\n";

    if ($qtyDisplay == '1') {
        echo "❌❌❌ 문제 발생! '1'이 반환됨 ❌❌❌\n";
        echo "\n원인 분석:\n";
        echo "- quantity_display 필드가 empty로 판단되었을 가능성\n";
        echo "- 또는 레거시 로직이 실행되었을 가능성\n";
    } elseif ($qtyDisplay == '1,000') {
        echo "✅✅✅ 정상! '1,000'이 반환됨 ✅✅✅\n";
    } elseif ($qtyDisplay == '1000') {
        echo "✅ 정상! '1000'이 반환됨 (콤마 없음)\n";
    } else {
        echo "⚠️  예상치 못한 값: '$qtyDisplay'\n";
    }

    echo "\n";
}

// 정리
$query = "DELETE FROM quotation_temp WHERE session_id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $sessionId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo "\n=== 테스트 완료 (테스트 데이터 삭제됨) ===\n";

mysqli_close($db);
