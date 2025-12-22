<?php
/**
 * 최근 주문 목록 가져오기 (송장번호 없는 주문)
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../db.php';

// 송장번호가 없는 최근 주문 20개 조회
$query = "SELECT no, name, Type, date
          FROM mlangorder_printauto
          WHERE (logen_tracking_no IS NULL OR logen_tracking_no = '' OR logen_tracking_no = '0')
          ORDER BY no DESC
          LIMIT 20";

$result = mysqli_query($db, $query);

$orders = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
} else {
    error_log("SQL Error: " . mysqli_error($db));
}

echo json_encode([
    'success' => true,
    'orders' => $orders
], JSON_UNESCAPED_UNICODE);
