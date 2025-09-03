<?php
include "../../db.php";

// JSON으로 전송된 데이터를 받습니다.
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$my_type = $data['MY_type'] ?? '';
$pn_type = $data['PN_type'] ?? ''; // 종이종류는 no로 직접 받음
$my_fsd = $data['MY_Fsd'] ?? ''; // 규격은 no로 직접 받음
$my_amount = $data['MY_amount'] ?? '';
$ordertype = $data['ordertype'] ?? '';

$response = ['success' => false, 'data' => null, 'message' => ''];

if ($my_type && $pn_type && $my_fsd && $my_amount) {
    $TABLE = "MlangPrintAuto_cadarok";
    
    // 컬럼 이름을 스키마에 맞게 수정
    $query = "SELECT * FROM $TABLE WHERE 
                style = ? AND 
                TreeSelect = ? AND 
                Section = ? AND 
                quantity = ?";

    $stmt = mysqli_prepare($db, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $my_type, $pn_type, $my_fsd, $my_amount);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $price = $row['money']; // money 컬럼 사용
            $ds_price = ($ordertype == 'total') ? $row['DesignMoney'] : 0; // DesignMoney 컬럼 사용
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
                'Total_PriceForm' => $vat_price,
                'StyleForm' => '카다록',
                'SectionForm' => '', // 필요에 따라 추가
                'QuantityForm' => $my_amount,
                'DesignForm' => $ordertype
            ];
        } else {
            $response['message'] = '해당 조건의 가격 정보가 없습니다. (style: ' . $my_type . ', TreeSelect: ' . $pn_type . ', Section: ' . $my_fsd . ', quantity: ' . $my_amount . ')';
        }
    } else {
        $response['message'] = '데이터베이스 쿼리 준비 실패: ' . mysqli_error($db);
    }
} else {
    $response['message'] = '모든 옵션을 선택해주세요. (style: ' . ($my_type ?? 'null') . ', TreeSelect: ' . ($pn_type ?? 'null') . ', Section: ' . ($my_fsd ?? 'null') . ', quantity: ' . ($my_amount ?? 'null') . ')';
}

header('Content-Type: application/json');
echo json_encode($response);
?>