<?php
header("Content-Type: application/json");
include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 받기
$style = $_GET['MY_type'] ?? '';
$section = $_GET['Section'] ?? '';
$potype = $_GET['POtype'] ?? '';
$quantity = $_GET['MY_amount'] ?? '';
$ordertype = $_GET['ordertype'] ?? '';

// 추가 옵션 총액 받기
$additional_options_total = intval($_GET['additional_options_total'] ?? 0);

// POtype은 선택사항 (빈 문자열 허용)
if (empty($style) || empty($section) || empty($quantity) || empty($ordertype)) {
    error_response('모든 옵션을 선택해주세요.');
}

$TABLE = "mlangprintauto_cadarok";

// 데이터베이스에서 가격 정보 조회
$query = "SELECT money, DesignMoney 
          FROM $TABLE 
          WHERE style='" . mysqli_real_escape_string($db, $style) . "' 
          AND Section='" . mysqli_real_escape_string($db, $section) . "' 
          AND POtype='" . mysqli_real_escape_string($db, $potype) . "'
          AND quantity='" . mysqli_real_escape_string($db, $quantity) . "'";

$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    $base_price = (int)$row['money'];
    $design_price_db = (int)$row['DesignMoney'];

    $design_price = ($ordertype === 'total') ? $design_price_db : 0;

    // 추가 옵션 포함하여 총 가격 계산
    $total_price = $base_price + $design_price + $additional_options_total;
    $total_with_vat = $total_price * 1.1;

    $response_data = [
        'base_price' => $base_price,
        'design_price' => $design_price,
        'additional_options_total' => $additional_options_total,
        'total_price' => $total_price,
        'total_with_vat' => $total_with_vat
    ];
    
    success_response($response_data, '가격 계산 완료');

} else {
    // 혹시 POtype 없이 데이터가 있는지 확인
    $query_fallback = "SELECT money, DesignMoney 
                       FROM $TABLE 
                       WHERE style='" . mysqli_real_escape_string($db, $style) . "' 
                       AND Section='" . mysqli_real_escape_string($db, $section) . "' 
                       AND quantity='" . mysqli_real_escape_string($db, $quantity) . "'";
    
    $result_fallback = mysqli_query($db, $query_fallback);

    if ($result_fallback && mysqli_num_rows($result_fallback) > 0) {
        $row = mysqli_fetch_assoc($result_fallback);

        $base_price = (int)$row['money'];
        $design_price_db = (int)$row['DesignMoney'];

        $design_price = ($ordertype === 'total') ? $design_price_db : 0;

        // 추가 옵션 포함하여 총 가격 계산
        $total_price = $base_price + $design_price + $additional_options_total;
        $total_with_vat = $total_price * 1.1;

        $response_data = [
            'base_price' => $base_price,
            'design_price' => $design_price,
            'additional_options_total' => $additional_options_total,
            'total_price' => $total_price,
            'total_with_vat' => $total_with_vat
        ];
        
        success_response($response_data, '가격 계산 완료 (fallback)');
    } else {
        error_response('해당 조건의 가격 정보를 찾을 수 없습니다.');
    }
}

mysqli_close($db);
?>