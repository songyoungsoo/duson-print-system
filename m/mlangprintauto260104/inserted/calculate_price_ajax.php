<?php
/**
 * [모바일] 전단지 가격 계산 API
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
require_once __DIR__ . "/../../../includes/PriceCalculationService.php";

if (!$db) {
    die(json_encode(['success' => false, 'error' => ['message' => '데이터베이스 연결에 실패했습니다.']]));
}

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
            'Price' => $data['Price'],
            'DS_Price' => $data['DS_Price'],
            'Order_Price' => $data['Order_Price'],
            'Additional_Options' => number_format($additional_options_total),
            'PriceForm' => $data['PriceForm'],
            'DS_PriceForm' => $data['DS_PriceForm'],
            'Order_PriceForm' => $data['Order_PriceForm'],
            'Additional_Options_Form' => $additional_options_total,
            'VAT_PriceForm' => $data['VAT_PriceForm'],
            'Total_PriceForm' => $data['Total_PriceForm'],
            'StyleForm' => $data['StyleForm'],
            'SectionForm' => $data['SectionForm'],
            'QuantityForm' => $data['QuantityForm'],
            'DesignForm' => $data['DesignForm'],
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
