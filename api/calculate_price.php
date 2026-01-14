<?php
/**
 * 통합 가격 계산 API
 *
 * 모든 품목의 가격 계산을 단일 엔드포인트로 처리
 * 레거시 API와 호환성 유지
 *
 * @endpoint GET/POST /api/calculate_price.php
 * @param string product_type 품목 코드 (필수)
 * @param mixed ... 품목별 파라미터 (레거시 또는 표준)
 *
 * @example 전단지: ?product_type=inserted&MY_type=...&PN_type=...
 * @example 명함:   ?product_type=namecard&MY_type=...&Section=...
 * @example 스티커: POST product_type=sticker_new&jong=...&garo=...
 */

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");

// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 출력 버퍼링
ob_start();

// 오류 제어
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // DB 연결
    require_once __DIR__ . '/../db.php';
    require_once __DIR__ . '/../includes/PriceCalculationService.php';

    if (!$db) {
        throw new Exception('데이터베이스 연결에 실패했습니다.');
    }

    mysqli_set_charset($db, "utf8");

    // 파라미터 수집 (GET/POST 모두 지원)
    $params = array_merge($_GET, $_POST);

    // 품목 타입 확인
    $productType = $params['product_type'] ?? '';

    if (empty($productType)) {
        throw new Exception('품목 타입(product_type)을 지정해주세요.');
    }

    // 지원 품목 확인
    $supportedProducts = PriceCalculationService::getSupportedProducts();
    if (!in_array($productType, $supportedProducts)) {
        throw new Exception("지원하지 않는 품목입니다: {$productType}. 지원 품목: " . implode(', ', $supportedProducts));
    }

    // product_type 파라미터 제거 (서비스에 전달하지 않음)
    unset($params['product_type']);

    // 가격 계산 서비스 호출
    $service = new PriceCalculationService($db);
    $result = $service->calculate($productType, $params);

    // JSON 응답
    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => $e->getMessage()
        ]
    ], JSON_UNESCAPED_UNICODE);
}

// DB 연결 종료
if (isset($db) && $db) {
    mysqli_close($db);
}

ob_end_flush();
