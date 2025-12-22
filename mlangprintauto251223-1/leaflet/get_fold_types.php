<?php
header("Content-Type: application/json");

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

if (!$connect) {
    die(json_encode(['success' => false, 'error' => '데이터베이스 연결 실패']));
}

mysqli_set_charset($connect, "utf8");

// 활성화된 접지 옵션 조회
$query = "SELECT fold_type, additional_price, description
          FROM mlangprintauto_leaflet_fold
          WHERE is_active = 1
          ORDER BY display_order ASC";

$result = mysqli_query($connect, $query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => '쿼리 실행 실패']);
    exit;
}

$fold_types = [];
while ($row = mysqli_fetch_assoc($result)) {
    $fold_types[] = [
        'value' => $row['fold_type'],
        'label' => $row['fold_type'] . ' (+' . number_format($row['additional_price']) . '원)',
        'price' => intval($row['additional_price']),
        'name' => $row['fold_type']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $fold_types
]);

mysqli_close($connect);
?>