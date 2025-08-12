<?php
header('Content-Type: application/json; charset=utf-8');

// 간단한 테스트용 가격 계산
$response = [
    "PriceForm" => 100000,
    "DS_PriceForm" => 20000,
    "Order_PriceForm" => 120000,
    "VAT_PriceForm" => 12000,
    "Total_PriceForm" => 132000,
    "StyleForm" => $_POST['MY_type'] ?? '',
    "SectionForm" => $_POST['MY_Fsd'] ?? '',
    "QuantityForm" => $_POST['MY_amount'] ?? '',
    "DesignForm" => $_POST['ordertype'] ?? ''
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>