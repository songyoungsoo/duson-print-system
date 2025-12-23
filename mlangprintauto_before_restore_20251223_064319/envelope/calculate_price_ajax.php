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

// 봉투 추가 옵션 파라미터 (간소화)
$envelope_tape_enabled = $_GET['envelope_tape_enabled'] ?? '';

if (empty($style) || empty($section) || empty($potype) || empty($quantity) || empty($ordertype)) {
    error_response('모든 옵션을 선택해주세요.');
}

$TABLE = "mlangprintauto_envelope";

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
    
    // 봉투 추가 옵션 가격 계산
    $additional_options_price = 0;
    $additional_options_details = [];

    if (!empty($envelope_tape_enabled)) {
        // 메인 수량을 테이프 수량으로 사용 (간소화된 로직)
        $tape_quantity = intval($quantity);

        if ($tape_quantity > 0) {
            // 테이프 가격 계산: 500매는 25,000원 고정, 나머지는 수량 × 40원
            if ($tape_quantity == 500) {
                $tape_price = 25000;
            } else {
                $tape_price = $tape_quantity * 40;
            }

            $additional_options_price += $tape_price;
            $additional_options_details['envelope_tape'] = [
                'quantity' => $tape_quantity,
                'price' => $tape_price
            ];
        }
    }

    $total_price = $base_price + $design_price + $additional_options_price;
    $total_with_vat = $total_price * 1.1;

    $response_data = [
        'base_price' => $base_price,
        'design_price' => $design_price,
        'additional_options_price' => $additional_options_price,
        'additional_options_details' => $additional_options_details,
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
        
        $total_price = $base_price + $design_price;
        $total_with_vat = $total_price * 1.1;

        $response_data = [
            'base_price' => $base_price,
            'design_price' => $design_price,
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