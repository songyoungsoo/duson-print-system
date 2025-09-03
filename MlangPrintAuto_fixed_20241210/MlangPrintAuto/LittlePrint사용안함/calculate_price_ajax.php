<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// GET 방식으로 데이터 가져오기
$MY_type = $_GET['MY_type'] ?? '';        // 구분 (590)
$PN_type = $_GET['PN_type'] ?? '';        // 종이규격 (610, 611, 612, 613)
$TreeSelect = $_GET['TreeSelect'] ?? '';  // 종이종류 (604, 679, 680 등)
$MY_amount = $_GET['MY_amount'] ?? '';    // 수량
$POtype = $_GET['POtype'] ?? '';          // 인쇄면 (1, 2)
$ordertype = $_GET['ordertype'] ?? '';    // 디자인편집

// 입력값 검증
if (empty($MY_type) || empty($PN_type) || empty($TreeSelect) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    error_response('필수 입력값이 누락되었습니다.');
}

// 공통함수를 사용하여 가격 계산
$conditions = [
    'style' => $MY_type,
    'Section' => $PN_type,
    'TreeSelect' => $TreeSelect,
    'quantity' => $MY_amount,
    'POtype' => $POtype
];

$price_result = calculateProductPrice($db, "MlangPrintAuto_LittlePrint", $conditions, $ordertype);

if ($price_result) {
    // 성공 응답
    $response_data = [
        'Price' => format_number($price_result['base_price']),
        'DS_Price' => format_number($price_result['design_price']),
        'Order_Price' => format_number($price_result['total_price']),
        'PriceForm' => $price_result['base_price'],
        'DS_PriceForm' => $price_result['design_price'],
        'Order_PriceForm' => $price_result['total_price'],
        'VAT_PriceForm' => $price_result['vat'],
        'Total_PriceForm' => $price_result['total_with_vat'],
        'StyleForm' => $MY_type,
        'SectionForm' => $PN_type,
        'TreeSelectForm' => $TreeSelect,
        'QuantityForm' => $MY_amount,
        'DesignForm' => $ordertype
    ];
    
    success_response($response_data);
} else {
    error_response('견적을 수행할 관련 정보가 없습니다.\n\n다른 항목으로 견적을 해주시기 바랍니다.');
}

mysqli_close($db);
?>