<?php
/**
 * [모바일] 카다록 가격 계산 API
 *
 * PriceCalculationService 중앙 서비스 사용
 * 레거시 응답 형식 유지
 *
 * @migrated 2026-01-14
 */

header("Content-Type: application/json");

// 중앙 서비스 로드
require_once __DIR__ . "/../../../db.php";
require_once __DIR__ . "/../../../includes/functions.php";
require_once __DIR__ . "/../../../includes/PriceCalculationService.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 수집 (GET 방식)
$params = $_GET;

// 추가 옵션 총액 처리
$additional_options_total = intval($params['additional_options_total'] ?? 0);
if ($additional_options_total > 0) {
    $params['additional_options_total'] = $additional_options_total;
}

// 중앙 서비스로 가격 계산
$service = new PriceCalculationService($db);
$result = $service->calculate('cadarok', $params);

// 레거시 응답 형식으로 변환
if ($result['success']) {
    $data = $result['data'];

    $response_data = [
        'base_price' => $data['base_price'],
        'design_price' => $data['design_price'],
        'additional_options_total' => $data['additional_options_total'],
        'total_price' => $data['order_price'],
        'total_with_vat' => $data['total_with_vat']
    ];

    success_response($response_data, '가격 계산 완료');
} else {
    error_response($result['error']['message']);
}

mysqli_close($db);
