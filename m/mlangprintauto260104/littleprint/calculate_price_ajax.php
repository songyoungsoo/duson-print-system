<?php
/**
 * [모바일] 포스터 가격 계산 API
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

// 추가 옵션 총액 처리
$additional_options_total = intval($params['additional_options_total'] ?? 0);
if ($additional_options_total > 0) {
    $params['additional_options_total'] = $additional_options_total;
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
        'additional_options_total' => $additional_options_total,
        'total_price' => $data['order_price'],
        'vat' => $data['vat_price'],
        'total_with_vat' => $data['total_with_vat'],
        'Price' => $data['Price'],
        'DS_Price' => $data['DS_Price'],
        'Order_Price' => $data['Order_Price'],
        'PriceForm' => $data['PriceForm'],
        'DS_PriceForm' => $data['DS_PriceForm'],
        'Order_PriceForm' => $data['Order_PriceForm'],
        'VAT_PriceForm' => $data['VAT_PriceForm'],
        'Total_PriceForm' => $data['Total_PriceForm'],
        'StyleForm' => $data['StyleForm'],
        'SectionForm' => $original_section,
        'QuantityForm' => $data['QuantityForm'],
        'DesignForm' => $data['DesignForm']
    ];

    success_response($response_data);
} else {
    error_response($result['error']['message']);
}

mysqli_close($db);
