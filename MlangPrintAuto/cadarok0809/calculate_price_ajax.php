<?php
// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// GET 파라미터 받기
$MY_type = $_GET['MY_type'] ?? '';      // 구분
$MY_Fsd = $_GET['MY_Fsd'] ?? '';        // 규격
$PN_type = $_GET['PN_type'] ?? '';      // 종이종류
$MY_amount = $_GET['MY_amount'] ?? '';  // 수량
$ordertype = $_GET['ordertype'] ?? 'print'; // 주문방법

$TABLE = "MlangPrintAuto_cadarok";

// 입력값 검증
if (empty($MY_type) || empty($MY_Fsd) || empty($PN_type) || empty($MY_amount)) {
    error_response('필수 파라미터가 누락되었습니다.');
}

// 가격 계산을 위한 조건 설정
$conditions = [
    'style' => $MY_type,
    'Section' => $MY_Fsd,
    'TreeSelect' => $PN_type,
    'quantity' => $MY_amount
];

// 공통함수를 사용한 가격 계산
$price_result = calculateProductPrice($db, $TABLE, $conditions, $ordertype);

if ($price_result) {
    // 응답 데이터 구성
    $response_data = [
        'base_price' => $price_result['base_price'],
        'design_price' => $price_result['design_price'],
        'total_price' => $price_result['total_price'],
        'vat' => $price_result['vat'],
        'total_with_vat' => $price_result['total_with_vat'],
        'formatted' => $price_result['formatted'],
        
        // 기존 호환성을 위한 필드들
        'PriceForm' => $price_result['base_price'],
        'DS_PriceForm' => $price_result['design_price'],
        'Order_PriceForm' => $price_result['total_price'],
        'VAT_PriceForm' => $price_result['vat'],
        'Total_PriceForm' => $price_result['total_with_vat'],
        'StyleForm' => $MY_type,
        'SectionForm' => $MY_Fsd,
        'QuantityForm' => $MY_amount,
        'DesignForm' => $ordertype
    ];
    
    mysqli_close($db);
    success_response($response_data);
} else {
    mysqli_close($db);
    error_response('해당 조건의 가격 정보를 찾을 수 없습니다.');
}
?>