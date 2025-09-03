<?php
header('Content-Type: application/json; charset=utf-8');

include "db_ajax.php";

$TABLE = "mlangprintauto_namecard";
$TABLE = "mlangprintauto_transactioncate";

$NC_type = $_GET['NC_type'] ?? '';
$NC_paper = $_GET['NC_paper'] ?? '';
$NC_amount = $_GET['NC_amount'] ?? '';
$POtype = $_GET['POtype'] ?? '1';
$ordertype = $_GET['ordertype'] ?? 'total';

$quantity_map = [
    '500' => '500',
    '1000' => '1000', 
    '2000' => '2000',
    '3000' => '3000',
    '4000' => '4000',
    '5000' => '5000',
    '기타' => '1000'
];

$mapped_quantity = $quantity_map[$NC_amount] ?? $NC_amount;

$query = "SELECT * FROM $TABLE WHERE style='$NC_type' AND Section='$NC_paper' AND quantity='$mapped_quantity' AND POtype='$POtype'";
$result = mysqli_query($db, $query);
$row = mysqli_fetch_array($result);

if ($row) {
    if ($ordertype == "print") {
        $Price = $row['money'];
        $DesignMoneyOk = 0;
        $Order_PricOk = $Price + $DesignMoneyOk;
        $VAT_PriceOk = $Order_PricOk / 10;
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;
    } elseif ($ordertype == "design") {
        $Price = 0;
        $DesignMoneyOk = $row['DesignMoney'] ?? 30000;
        $Order_PricOk = $Price + $DesignMoneyOk;
        $VAT_PriceOk = $Order_PricOk / 10;
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;
    } else {
        $Price = $row['money'];
        $DesignMoneyOk = $row['DesignMoney'] ?? 30000;
        $Order_PricOk = $Price + $DesignMoneyOk;
        $VAT_PriceOk = $Order_PricOk / 10;
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;
    }

    // 텍스트 정보 가져오기
    $type_query = "SELECT title FROM $GGTABLE WHERE no='$NC_type'";
    $type_result = mysqli_query($db, $type_query);
    $type_row = mysqli_fetch_array($type_result);
    $type_text = $type_row['title'] ?? $NC_type;

    $paper_query = "SELECT title FROM $GGTABLE WHERE no='$NC_paper'";
    $paper_result = mysqli_query($db, $paper_query);
    $paper_row = mysqli_fetch_array($paper_result);
    $paper_text = $paper_row['title'] ?? $NC_paper;

    $design_text = '';
    if ($ordertype == 'total') $design_text = '디자인+인쇄';
    else if ($ordertype == 'print') $design_text = '인쇄만 의뢰';
    else if ($ordertype == 'design') $design_text = '디자인만 의뢰';
    else $design_text = $ordertype;

    $sides_text = ($POtype == '2') ? '양면' : '단면';

    echo json_encode([
        'success' => true,
        'data' => [
            'NC_Price' => number_format($Price),
            'NC_DS_Price' => number_format($DesignMoneyOk),
            'NC_Order_Price' => number_format($Order_PricOk),
            'StyleForm' => $type_text,
            'SectionForm' => $paper_text,
            'QuantityForm' => $NC_amount . '매',
            'DesignForm' => $design_text,
            'SidesForm' => $sides_text,
            'PriceForm' => $Price,
            'DS_PriceForm' => $DesignMoneyOk,
            'Order_PriceForm' => $Order_PricOk,
            'VAT_PriceForm' => $VAT_PriceOk,
            'Total_PriceForm' => $Total_PriceOk
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => '해당 명함 조건의 가격 정보가 없습니다.\n\n다른 옵션으로 선택해주시기 바랍니다.'
    ]);
}

mysqli_close($db);
?>