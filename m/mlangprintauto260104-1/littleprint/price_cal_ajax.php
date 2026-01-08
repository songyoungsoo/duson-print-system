<?php
header('Content-Type: application/json; charset=utf-8');
include "../../db.php";

$response = ['success' => false, 'message' => '알 수 없는 오류가 발생했습니다.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = '잘못된 요청 방식입니다.';
    echo json_encode($response);
    exit;
}

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

$MY_type = $json_obj['MY_type'] ?? '';
$MY_Fsd = $json_obj['MY_Fsd'] ?? '';
$PN_type = $json_obj['PN_type'] ?? '';
$MY_amount = $json_obj['MY_amount'] ?? '';
$POtype = $json_obj['POtype'] ?? '';
$ordertype = $json_obj['ordertype'] ?? '';

if (empty($MY_type) || empty($MY_Fsd) || empty($PN_type) || empty($MY_amount) || empty($POtype)) {
    $response['message'] = '모든 옵션을 선택해야 합니다.';
    // 이 경우는 정상적인 흐름이므로 success: false를 보내지 않음
    echo json_encode(['success' => true, 'data' => null]); 
    exit;
}

if (!$db) {
    $response['message'] = '데이터베이스 연결에 실패했습니다.';
    echo json_encode($response);
    exit;
}

$query = "
    SELECT money, DesignMoney 
    FROM mlangprintauto_littleprint 
    WHERE style = ? AND TreeSelect = ? AND Section = ? AND quantity = ? AND POtype = ?
";

$stmt = mysqli_prepare($db, $query);
if (!$stmt) {
    $response['message'] = 'DB 쿼리 준비에 실패했습니다: ' . mysqli_error($db);
    echo json_encode($response);
    exit;
}

mysqli_stmt_bind_param($stmt, 'sssss', $MY_type, $MY_Fsd, $PN_type, $MY_amount, $POtype);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $price = (int)$row['money'];
    $design_price = ($ordertype === 'total') ? (int)$row['DesignMoney'] : 0;
    $order_price = $price + $design_price;
    $vat_price = $order_price * 0.1;
    $total_price = $order_price + $vat_price;

    $get_title = function($no) use ($db) {
        $q = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'LittlePrint' LIMIT 1";
        $s = mysqli_prepare($db, $q);
        mysqli_stmt_bind_param($s, 's', $no);
        mysqli_stmt_execute($s);
        $r = mysqli_stmt_get_result($s);
        $res = mysqli_fetch_assoc($r);
        mysqli_stmt_close($s);
        return $res['title'] ?? '';
    };

    $response = [
        'success' => true,
        'data' => [
            'Price' => number_format($price),
            'DS_Price' => number_format($design_price),
            'Order_Price' => number_format($order_price),
            'PriceForm' => $price,
            'DS_PriceForm' => $design_price,
            'Order_PriceForm' => $order_price,
            'VAT_PriceForm' => $vat_price,
            'Total_PriceForm' => $total_price,
            'StyleForm' => $get_title($MY_type),
            'SectionForm' => $get_title($PN_type),
            'QuantityForm' => $MY_amount . "매",
            'DesignForm' => $ordertype === 'total' ? '디자인+인쇄' : '인쇄만 의뢰',
        ]
    ];
} else {
    $response['message'] = '해당 조건의 가격 정보가 없습니다. 옵션을 다시 확인해주세요.';
}

mysqli_stmt_close($stmt);
mysqli_close($db);

echo json_encode($response);
?>