<?php
header("Content-Type: application/json");

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

if (!$connect) {
    die(json_encode(['success' => false, 'error' => '데이터베이스 연결 실패']));
}

mysqli_set_charset($connect, "utf8");

// 활성화된 코팅 옵션 조회 (전단지와 동일)
$query = "SELECT option_type, option_name, base_price
          FROM additional_options_config
          WHERE option_category = 'coating' AND is_active = 1
          ORDER BY sort_order ASC";

$result = mysqli_query($connect, $query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => '쿼리 실행 실패']);
    exit;
}

$coating_types = [];
while ($row = mysqli_fetch_assoc($result)) {
    $coating_types[] = [
        'value' => $row['option_type'],
        'label' => $row['option_name'] . ' (+' . number_format($row['base_price']) . '원)',
        'price' => intval($row['base_price']),
        'name' => $row['option_name']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $coating_types
]);

mysqli_close($connect);
?>
