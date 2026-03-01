<?php
header("Content-Type: application/json");

// 데이터베이스 연결
include __DIR__ . "/../../../db.php";
$connect = $db;

if (!$connect) {
    die(json_encode(['success' => false, 'error' => '데이터베이스 연결 실패']));
}

mysqli_set_charset($connect, "utf8");

// 활성화된 코팅 옵션 조회 (premium_options SSOT)
$query = "SELECT v.variant_name AS option_name, v.pricing_config
          FROM premium_options o
          JOIN premium_option_variants v ON o.id = v.option_id
          WHERE o.product_type = 'inserted' AND o.option_name = '코팅' AND o.is_active = 1 AND v.is_active = 1
          ORDER BY v.display_order ASC";

$result = mysqli_query($connect, $query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => '쿼리 실행 실패']);
    exit;
}

$typeMap = ['단면유광'=>'single', '양면유광'=>'double', '단면무광'=>'single_matte', '양면무광'=>'double_matte'];
$coating_types = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pc = json_decode($row['pricing_config'], true);
    $base_price = (int)($pc['base_price'] ?? 0);
    $optType = $typeMap[$row['option_name']] ?? $row['option_name'];
    $coating_types[] = [
        'value' => $optType,
        'label' => $row['option_name'] . ' (+' . number_format($base_price) . '원)',
        'price' => $base_price,
        'name' => $row['option_name']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $coating_types
]);

mysqli_close($connect);
?>
