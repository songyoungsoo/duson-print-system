<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$orders = $input['orders'] ?? '';
$method = $input['method'] ?? '';

if (empty($orders) || empty($method)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$orders_normalized = str_replace(',', '_', $orders);
$order_numbers = explode('_', $orders_normalized);

$method_text = '';
if ($method === 'bank') {
    $method_text = '무통장입금(예정)';
} elseif ($method === 'card') {
    $method_text = '카드결제(시도)';
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$success_count = 0;
foreach ($order_numbers as $order_no) {
    $order_no = intval($order_no);
    if ($order_no > 0) {
        $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET bank = ? WHERE no = ? AND OrderStyle != '11'");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'si', $method_text, $order_no);
            mysqli_stmt_execute($stmt);
            $success_count += mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

echo json_encode(['success' => true, 'updated' => $success_count]);
