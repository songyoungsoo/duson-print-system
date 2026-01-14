<?php
/**
 * [모바일] 전단지 가격 계산 API (251028 버전)
 *
 * PriceCalculationService 중앙 서비스 사용
 * 레거시 응답 형식 유지
 *
 * @migrated 2026-01-14
 */

header("Content-Type: application/json");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 중앙 서비스 로드
require_once __DIR__ . "/../../../db.php";
require_once __DIR__ . "/../../../includes/functions.php";
require_once __DIR__ . "/../../../includes/PriceCalculationService.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 수집 (GET 방식)
$params = $_GET;

// 추가 옵션 처리
$additional_options_total = intval($params['premium_options_total'] ?? $params['additional_options_total'] ?? 0);
if ($additional_options_total > 0) {
    $params['additional_options_total'] = $additional_options_total;
}

// 중앙 서비스로 가격 계산
$service = new PriceCalculationService($db);
$result = $service->calculate('inserted', $params);

// 레거시 응답 형식으로 출력
if ($result['success']) {
    $data = $result['data'];

    $response = [
        'success' => true,
        'data' => [
            'Price' => number_format($data['base_price']),
            'DS_Price' => number_format($data['design_price']),
            'Order_Price' => number_format($data['order_price']),
            'Additional_Options' => number_format($additional_options_total),
            'PriceForm' => $data['base_price'],
            'DS_PriceForm' => $data['design_price'],
            'Order_PriceForm' => $data['order_price'],
            'Additional_Options_Form' => $additional_options_total,
            'VAT_PriceForm' => $data['vat_price'],
            'Total_PriceForm' => $data['total_with_vat'],
            'StyleForm' => $data['StyleForm'] ?? ($params['MY_type'] ?? ''),
            'SectionForm' => $data['SectionForm'] ?? ($params['PN_type'] ?? ''),
            'QuantityForm' => $data['QuantityForm'] ?? ($params['MY_amount'] ?? ''),
            'DesignForm' => $data['DesignForm'] ?? ($params['ordertype'] ?? ''),
            'MY_amountRight' => $data['MY_amountRight'] ?? ''
        ]
    ];

    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error']
    ]);
}

mysqli_close($db);
