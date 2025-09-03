<?php
header('Content-Type: application/json; charset=utf-8');

// === 기본 설정 ===
$HomeDir = "../../";
include "$HomeDir/db.php";

// === 입력값 받기 ===
$MY_type    = $_POST['MY_type']    ?? '';
$MY_Fsd     = $_POST['MY_Fsd']     ?? '';
$PN_type    = $_POST['PN_type']    ?? '';
$MY_amount  = $_POST['MY_amount']  ?? '';
$ordertype  = $_POST['ordertype']  ?? '';

$response = [
  "PriceForm" => 0,
  "DS_PriceForm" => 0,
  "Order_PriceForm" => 0,
  "VAT_PriceForm" => 0,
  "Total_PriceForm" => 0,
  "StyleForm" => $MY_type,
  "SectionForm" => $MY_Fsd,
  "QuantityForm" => $MY_amount,
  "DesignForm" => $ordertype
];

if ($MY_type && $MY_Fsd && $PN_type && $MY_amount) {
  $stmt = mysqli_prepare($db, "SELECT money FROM MlangPrintAuto_cadarok WHERE style = ? AND Section = ? AND TreeSelect = ? AND quantity = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "sssi", $MY_type, $MY_Fsd, $PN_type, $MY_amount);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if ($row = mysqli_fetch_assoc($result)) {
    $print_price = (int)$row['money'];
    $order_price = $print_price;
    $vat = (int)round($order_price * 0.1);
    $total_price = $order_price + $vat;

    $response["PriceForm"] = $print_price;
    $response["Order_PriceForm"] = $order_price;
    $response["VAT_PriceForm"] = $vat;
    $response["Total_PriceForm"] = $total_price;
  }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
