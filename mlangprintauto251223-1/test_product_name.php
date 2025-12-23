<?php
session_start();
include "db.php";
include "mlangprintauto/shop_temp_helper.php";

mysqli_set_charset($db, "utf8");

// 최근 전단지 아이템 확인
$query = "SELECT * FROM shop_temp WHERE product_type IN ('inserted', 'leaflet') ORDER BY no DESC LIMIT 3";
$result = mysqli_query($db, $query);

echo "<h1>전단지 상품명 표시 테스트</h1>";
echo "<pre>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "\n=== 상품번호: " . $row['no'] . " ===\n";
    echo "product_type (원본): '" . $row['product_type'] . "'\n";

    $formatted = formatCartItemForDisplay($db, $row);
    echo "formatted name (표시): '" . $formatted['name'] . "'\n";

    // Switch 문 직접 테스트
    $test_name = '';
    switch ($row['product_type']) {
        case 'leaflet':
            $test_name = '📄 전단지 (leaflet case)';
            break;
        case 'inserted':
            $test_name = '📄 전단지 (inserted case)';
            break;
        default:
            $test_name = '기타 상품 (default case)';
    }
    echo "switch 테스트: '" . $test_name . "'\n";

    // 디버깅: product_type 길이 및 공백 확인
    echo "product_type 길이: " . strlen($row['product_type']) . "\n";
    echo "product_type hex: " . bin2hex($row['product_type']) . "\n";
}

echo "</pre>";
?>
