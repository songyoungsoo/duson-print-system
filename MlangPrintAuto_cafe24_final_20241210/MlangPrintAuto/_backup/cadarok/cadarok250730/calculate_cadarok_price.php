<?php
include "../../db.php";

$size = $_GET['size'] ?? '';
$pages = $_GET['pages'] ?? '';
$cover_paper = $_GET['cover_paper'] ?? '';
$inner_paper = $_GET['inner_paper'] ?? '';
$binding = $_GET['binding'] ?? '';
$quantity = $_GET['quantity'] ?? '';
$design = $_GET['design'] ?? '';

$response = ['success' => false, 'data' => null, 'error' => ['message' => '']];

if ($size && $pages && $cover_paper && $inner_paper && $binding && $quantity) {
    $TABLE = "MlangPrintAuto_cadarok";
    $query = "SELECT * FROM $TABLE WHERE 
                size_no = ? AND 
                page_no = ? AND 
                cover_paper_no = ? AND 
                inner_paper_no = ? AND 
                binding_no = ? AND 
                quantity_no = ?";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ssssss", $size, $pages, $cover_paper, $inner_paper, $binding, $quantity);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $price = $row['price'];
        $ds_price = ($design == 'total') ? $row['ds_price'] : 0;
        $order_price = $price + $ds_price;
        $vat_price = $order_price * 1.1;

        $response['success'] = true;
        $response['data'] = [
            'Price' => number_format($price),
            'DS_Price' => number_format($ds_price),
            'Order_Price' => number_format($order_price),
            'PriceForm' => $price,
            'DS_PriceForm' => $ds_price,
            'Order_PriceForm' => $order_price,
            'VAT_PriceForm' => $vat_price,
            'Total_PriceForm' => $vat_price
        ];
    } else {
        $response['error']['message'] = '해당 조건의 가격 정보가 없습니다.';
    }
} else {
    $response['error']['message'] = '모든 옵션을 선택해주세요.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>