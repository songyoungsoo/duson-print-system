<?php
/**
 * NCR양식지 가격 계산 API
 *
 * PriceCalculationService 중앙 서비스 사용
 * 레거시 응답 형식 유지
 *
 * @migrated 2026-01-14
 */

ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// 중앙 서비스 로드
require_once __DIR__ . "/../../db.php";
require_once __DIR__ . "/../../includes/functions.php";
require_once __DIR__ . "/../../includes/PriceCalculationService.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 안전한 JSON 응답 함수 (레거시 호환)
function safe_json_response($success = true, $data = null, $message = '') {
    ob_clean();

    $response = array(
        'success' => $success,
        'message' => $message
    );

    if ($data !== null) {
        $response['data'] = $data;
    }

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// 파라미터 수집 (POST 방식)
$params = $_POST;

// 추가 옵션 총액 처리
$additional_options_total = intval($params['additional_options_total'] ?? 0);
if ($additional_options_total > 0) {
    $params['additional_options_total'] = $additional_options_total;
}

// 필수 파라미터 검증
$MY_type = $params['MY_type'] ?? '';
$MY_Fsd = $params['MY_Fsd'] ?? '';
$PN_type = $params['PN_type'] ?? '';
$MY_amount = $params['MY_amount'] ?? '';
$ordertype = $params['ordertype'] ?? '';

if (empty($MY_type) || empty($MY_Fsd) || empty($PN_type) || empty($MY_amount) || empty($ordertype)) {
    safe_json_response(false, null, '모든 옵션을 선택해주세요.');
}

// 디버그 로그
error_log("NcrFlambeau 가격 계산 요청: MY_type=$MY_type, MY_Fsd=$MY_Fsd, PN_type=$PN_type, MY_amount=$MY_amount, ordertype=$ordertype");

// 중앙 서비스로 가격 계산
$service = new PriceCalculationService($db);
$result = $service->calculate('ncrflambeau', $params);

// 레거시 응답 형식으로 변환
if ($result['success']) {
    $data = $result['data'];

    $price_data = [
        'base_price' => $data['base_price'],
        'design_price' => $data['design_price'],
        'additional_options_total' => $additional_options_total,
        'total_price' => $data['order_price'],
        'vat_price' => $data['total_with_vat'],
        'formatted' => [
            'base_price' => number_format($data['base_price']) . '원',
            'design_price' => number_format($data['design_price']) . '원',
            'additional_options' => number_format($additional_options_total) . '원',
            'total_price' => number_format($data['order_price']) . '원',
            'vat_price' => number_format($data['total_with_vat']) . '원'
        ]
    ];

    error_log("NcrFlambeau 가격 계산 성공: " . json_encode($price_data));
    safe_json_response(true, $price_data, '가격 계산 완료');
} else {
    error_log("NcrFlambeau 가격 데이터 없음");
    safe_json_response(false, null, $result['error']['message']);
}

mysqli_close($db);
