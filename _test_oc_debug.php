<?php
// 임시 디버그 — 프로덕션 OrderComplete 다건 주문 500 에러 원인 추적
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db.php';
$connect = $db;

$orders = $_GET['orders'] ?? '84664,84665,84666';
$order_numbers = explode(',', $orders);
$order_list = [];
$total_amount = 0;
$total_amount_vat = 0;

foreach ($order_numbers as $order_no) {
    $order_no = trim($order_no);
    if (!empty($order_no)) {
        $query = "SELECT no, money_4, money_5, logen_fee_type, logen_delivery_fee FROM mlangorder_printauto WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($connect, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $order_no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $order_list[] = $row;
                $total_amount += $row['money_4'];
                $total_amount_vat += $row['money_5'];
            }
            mysqli_stmt_close($stmt);
        }
    }
}

echo "order_list count: " . count($order_list) . "\n";
echo "total_amount_vat: " . $total_amount_vat . "\n";

// 택배비 합산 코드 (500 에러 원인 후보)
$oc_shipping_fee_raw = intval($order_list[0]['logen_delivery_fee'] ?? 0);
$oc_shipping_vat = 0;
$oc_shipping_total = 0;
if (($order_list[0]['logen_fee_type'] ?? '') === '선불' && $oc_shipping_fee_raw > 0) {
    $oc_shipping_vat = round($oc_shipping_fee_raw * 0.1);
    $oc_shipping_total = $oc_shipping_fee_raw + $oc_shipping_vat;
}
$total_with_shipping = $total_amount_vat + $oc_shipping_total;

echo "shipping: $oc_shipping_fee_raw + VAT $oc_shipping_vat = $oc_shipping_total\n";
echo "total_with_shipping: $total_with_shipping\n";
echo "OK - no error\n";
