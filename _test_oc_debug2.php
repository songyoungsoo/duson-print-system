<?php
// 임시 디버그 v2 — OrderComplete 500 에러 원인 추적
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB 연결
require_once __DIR__ . '/db.php';
$connect = $db;

echo "Step 1: DB connected\n";

// OrderComplete와 동일한 include
include __DIR__ . "/includes/AdditionalOptionsDisplay.php";
include __DIR__ . "/includes/quantity_formatter.php";
include __DIR__ . "/includes/ProductSpecFormatter.php";
include __DIR__ . "/includes/SpecDisplayService.php";

echo "Step 2: Includes loaded\n";

$optionsDisplay = new AdditionalOptionsDisplay($connect);
$specFormatter = new ProductSpecFormatter($connect);
$specDisplayService = new SpecDisplayService($connect);

echo "Step 3: Services initialized\n";

$orders = $_GET['orders'] ?? '84664_84665_84666';
$orders_normalized = str_replace(',', '_', $orders);
$order_numbers = explode('_', $orders_normalized);

echo "Step 4: orders parsed - " . count($order_numbers) . " items\n";

$order_list = [];
$total_amount = 0;
$total_amount_vat = 0;

foreach ($order_numbers as $order_no) {
    $order_no = trim($order_no);
    if (!empty($order_no)) {
        $query = "SELECT * FROM mlangorder_printauto WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($connect, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $order_no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $order_list[] = $row;
                $total_amount += $row['money_4'];
                $total_amount_vat += $row['money_5'];
                echo "  Order $order_no: money_5={$row['money_5']}, Type={$row['Type']}\n";
            } else {
                echo "  Order $order_no: NOT FOUND\n";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

echo "Step 5: Orders loaded - " . count($order_list) . " items, total_vat=$total_amount_vat\n";

// 택배비 합산
$oc_shipping_fee_raw = intval($order_list[0]['logen_delivery_fee'] ?? 0);
$oc_shipping_vat = 0;
$oc_shipping_total = 0;
if (($order_list[0]['logen_fee_type'] ?? '') === '선불' && $oc_shipping_fee_raw > 0) {
    $oc_shipping_vat = round($oc_shipping_fee_raw * 0.1);
    $oc_shipping_total = $oc_shipping_fee_raw + $oc_shipping_vat;
}
$total_with_shipping = $total_amount_vat + $oc_shipping_total;

echo "Step 6: Shipping calculated - fee=$oc_shipping_fee_raw, total_with_shipping=$total_with_shipping\n";
echo "ALL OK\n";
