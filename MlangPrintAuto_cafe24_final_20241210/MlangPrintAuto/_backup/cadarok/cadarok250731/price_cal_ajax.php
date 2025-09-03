<?php
include "../../db.php";

// JSON으로 전송된 데이터를 받습니다.
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$my_type = $data['MY_type'] ?? '';
$pn_type_title = $data['PN_type'] ?? ''; // 종이종류는 title로 받음
$my_fsd_title = $data['MY_Fsd'] ?? ''; // 규격은 title로 받음
$my_amount = $data['MY_amount'] ?? '';
$ordertype = $data['ordertype'] ?? '';

$response = ['success' => false, 'data' => null, 'message' => ''];

if ($my_type && $pn_type_title && $my_fsd_title && $my_amount) {
    $TABLE = "MlangPrintAuto_cadarok";
    
    // transactionCate 테이블에서 title에 해당하는 no 값을 찾습니다.
    $GGTABLE = "MlangPrintAuto_transactionCate";
    
    $query_fsd_no = "SELECT no FROM $GGTABLE WHERE Ttable='cadarok' AND title = ? LIMIT 1";
    $stmt_fsd = mysqli_prepare($db, $query_fsd_no);
    mysqli_stmt_bind_param($stmt_fsd, "s", $my_fsd_title);
    mysqli_stmt_execute($stmt_fsd);
    $result_fsd = mysqli_stmt_get_result($stmt_fsd);
    $row_fsd = mysqli_fetch_assoc($result_fsd);
    $my_fsd_no = $row_fsd['no'] ?? '';

    $query_pn_no = "SELECT no FROM $GGTABLE WHERE Ttable='cadarok' AND title = ? LIMIT 1";
    $stmt_pn = mysqli_prepare($db, $query_pn_no);
    mysqli_stmt_bind_param($stmt_pn, "s", $pn_type_title);
    mysqli_stmt_execute($stmt_pn);
    $result_pn = mysqli_stmt_get_result($stmt_pn);
    $row_pn = mysqli_fetch_assoc($result_pn);
    $pn_type_no = $row_pn['no'] ?? '';

    if (!$my_fsd_no) {
        $response['message'] = '규격 정보(title: ' . $my_fsd_title . ')를 찾을 수 없습니다.';
    } elseif (!$pn_type_no) {
        $response['message'] = '종이종류 정보(title: ' . $pn_type_title . ')를 찾을 수 없습니다.';
    } else {
        $query = "SELECT * FROM $TABLE WHERE 
                    MY_type = ? AND 
                    PN_type = ? AND 
                    MY_Fsd = ? AND 
                    MY_amount = ?";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $my_type, $pn_type_no, $my_fsd_no, $my_amount);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $price = $row['price'];
            $ds_price = ($ordertype == 'total') ? $row['ds_price'] : 0;
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
            $response['message'] = '해당 조건의 가격 정보가 없습니다. (MY_type: ' . $my_type . ', PN_type_no: ' . $pn_type_no . ', MY_Fsd_no: ' . $my_fsd_no . ', MY_amount: ' . $my_amount . ')';
        }
    }
} else {
    $response['message'] = '모든 옵션을 선택해주세요. (MY_type: ' . ($my_type ?? 'null') . ', PN_type_title: ' . ($pn_type_title ?? 'null') . ', MY_Fsd_title: ' . ($my_fsd_title ?? 'null') . ', MY_amount: ' . ($my_amount ?? 'null') . ')';
}

header('Content-Type: application/json');
echo json_encode($response);
?>