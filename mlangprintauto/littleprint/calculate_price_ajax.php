<?php
/**
 * 포스터 가격 계산 API
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
require_once __DIR__ . "/../../db.php";
require_once __DIR__ . "/../../includes/functions.php";
require_once __DIR__ . "/../../includes/PriceCalculationService.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 수집 (GET 방식)
$params = $_GET;

// Section 매핑 로직 (레거시 호환)
// 프론트엔드: Section = 종이종류(604 등), PN_type = 규격(610 등)
// DB: style=590, Section=규격(610), TreeSelect=종이종류(604)
$section_mapping = [
    '604' => '610', '605' => '610', '606' => '610', '607' => '610',
    '608' => '610', '609' => '610', '679' => '610', '680' => '610', '958' => '610'
];

// 원본 Section 값 저장 (종이종류 = TreeSelect)
$original_section = $params['Section'] ?? '';

// 프론트엔드에서 PN_type으로 규격이 오면 그대로 사용, 아니면 매핑
if (!empty($params['PN_type'])) {
    // PN_type이 있으면: Section=종이종류, PN_type=규격
    $params['MY_Fsd'] = $original_section;  // TreeSelect로 전달
    $params['Section'] = $params['PN_type']; // 규격으로 사용
} else if (isset($section_mapping[$original_section])) {
    // PN_type이 없고 Section이 종이종류인 경우: 매핑
    $params['MY_Fsd'] = $original_section;  // TreeSelect로 전달
    $params['Section'] = $section_mapping[$original_section]; // 규격으로 변환
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

    // 추가 옵션 포함 재계산
    $total_price_with_options = $data['order_price'];
    $total_with_vat_with_options = $data['total_with_vat'];

    $response_data = [
        // JavaScript가 기대하는 필드명들
        'base_price' => $data['base_price'],
        'design_price' => $data['design_price'],
        'additional_options_total' => $additional_options_total,
        'total_price' => $total_price_with_options,
        'vat' => $data['vat_price'],
        'total_with_vat' => $total_with_vat_with_options,

        // 기존 호환성을 위한 필드들
        'Price' => $data['Price'],
        'DS_Price' => $data['DS_Price'],
        'Order_Price' => $data['Order_Price'],
        'PriceForm' => $data['PriceForm'],
        'DS_PriceForm' => $data['DS_PriceForm'],
        'Order_PriceForm' => $data['Order_PriceForm'],
        'VAT_PriceForm' => $data['VAT_PriceForm'],
        'Total_PriceForm' => $data['Total_PriceForm'],
        'StyleForm' => $data['StyleForm'],
        'SectionForm' => $original_section, // 원본 섹션 값 유지
        'QuantityForm' => $data['QuantityForm'],
        'DesignForm' => $data['DesignForm']
    ];

    success_response($response_data);
} else {
    error_response($result['error']['message']);
}

mysqli_close($db);
