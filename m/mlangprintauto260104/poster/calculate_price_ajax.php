<?php
/**
 * [모바일] 포스터 가격 계산 API
 *
 * PriceCalculationService 중앙 서비스 사용
 * littleprint 테이블 기반 가격 조회
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

// Section 매핑 로직 (레거시 호환)
$section_mapping = [
    '604' => '610', '605' => '610', '606' => '610', '607' => '610',
    '608' => '610', '609' => '610', '679' => '610', '680' => '610', '958' => '610'
];

// 매핑된 section 값 적용
$original_section = $params['Section'] ?? '';
if (isset($section_mapping[$original_section])) {
    $params['Section'] = $section_mapping[$original_section];
}

// 중앙 서비스로 가격 계산
$service = new PriceCalculationService($db);
$result = $service->calculate('littleprint', $params);

// 레거시 응답 형식으로 변환
if ($result['success']) {
    $data = $result['data'];

    $response_data = [
        'base_price' => $data['base_price'],
        'design_price' => $data['design_price'],
        'total_price' => $data['order_price'],
        'vat' => $data['vat_price'],
        'total_with_vat' => $data['total_with_vat'],
        'Price' => $data['Price'] ?? number_format($data['base_price']),
        'DS_Price' => $data['DS_Price'] ?? number_format($data['design_price']),
        'Order_Price' => $data['Order_Price'] ?? number_format($data['order_price']),
        'PriceForm' => $data['PriceForm'] ?? $data['base_price'],
        'DS_PriceForm' => $data['DS_PriceForm'] ?? $data['design_price'],
        'Order_PriceForm' => $data['Order_PriceForm'] ?? $data['order_price'],
        'VAT_PriceForm' => $data['VAT_PriceForm'] ?? $data['vat_price'],
        'Total_PriceForm' => $data['Total_PriceForm'] ?? $data['total_with_vat'],
        'StyleForm' => $data['StyleForm'] ?? ($params['MY_type'] ?? ''),
        'SectionForm' => $original_section,
        'QuantityForm' => $data['QuantityForm'] ?? ($params['MY_amount'] ?? ''),
        'DesignForm' => $data['DesignForm'] ?? ($params['ordertype'] ?? '')
    ];

    success_response($response_data);
} else {
    error_response($result['error']['message']);
}

mysqli_close($db);
