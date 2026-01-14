<?php
/**
 * 명함 가격 계산 API
 *
 * PriceCalculationService 중앙 서비스 사용
 * 레거시 응답 형식 유지
 *
 * @migrated 2026-01-14
 */

header("Content-Type: application/json");

// 중앙 서비스 로드
require_once __DIR__ . "/../../db.php";
require_once __DIR__ . "/../../includes/functions.php";
require_once __DIR__ . "/../../includes/PriceCalculationService.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 수집 (GET/POST 모두 지원)
$params = array_merge($_GET, $_POST);

// 프리미엄 옵션 처리
$premium_total_direct = isset($params['premium_options_total']) ? intval($params['premium_options_total']) : 0;

// JSON 프리미엄 옵션 처리
$premium_options_json = $params['premium_options'] ?? '';
if (!empty($premium_options_json) && is_string($premium_options_json)) {
    $decoded = json_decode($premium_options_json, true);
    if (is_array($decoded)) {
        $params['premium_options'] = $decoded;
    }
}

// 프리미엄 옵션 총액 추가
if ($premium_total_direct > 0) {
    $params['premium_options_total'] = $premium_total_direct;
}

// 중앙 서비스로 가격 계산
$service = new PriceCalculationService($db);
$result = $service->calculate('namecard', $params);

// 레거시 응답 형식으로 변환
if ($result['success']) {
    $data = $result['data'];

    // 프리미엄 옵션 상세 내역 구성
    $premium_details = [];
    if (!empty($data['additional_options_details'])) {
        foreach ($data['additional_options_details'] as $key => $info) {
            $premium_details[] = ['name' => $info['name'], 'price' => $info['price']];
        }
    }

    $response_data = [
        'success' => true,
        'base_price' => $data['base_price'],
        'design_price' => $data['design_price'],
        'premium_total' => $data['additional_options_total'],
        'premium_details' => $premium_details,
        'total_price' => $data['order_price'],
        'total_with_vat' => $data['total_with_vat'],
        'vat_price' => $data['total_with_vat']  // 레거시 호환
    ];

    success_response($response_data, '가격 계산 완료');
} else {
    error_response($result['error']['message']);
}

mysqli_close($db);
