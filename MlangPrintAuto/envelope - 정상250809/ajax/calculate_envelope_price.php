<?php
/**
 * Envelope 가격 계산 API
 * 
 * 기존 price_cal.php의 로직을 Ajax 방식으로 변환
 * 모든 드롭다운 선택값을 받아 mlangprintauto_envelope 테이블에서 가격 조회
 * 인쇄비, 디자인비, 총액 계산 로직 구현
 * 
 * 요구사항: 2.1, 2.2, 2.3, 3.1, 3.2
 * 
 * 파라미터:
 * - MY_type: 구분 (필수)
 * - PN_type: 종류 (필수)
 * - MY_amount: 수량 (필수)
 * - POtype: 인쇄색상 (필수) - 1:마스터1도, 2:마스터2도, 3:칼라4도
 * - ordertype: 주문타입 (필수) - total:전체, print:인쇄만, design:디자인만
 * 
 * 응답 형식:
 * {
 *   "success": true,
 *   "data": {
 *     "message": "가격이 성공적으로 계산되었습니다.",
 *     "price_data": {
 *       "print_price": 50000,
 *       "design_price": 30000,
 *       "subtotal_price": 80000,
 *       "vat_price": 8000,
 *       "total_price": 88000,
 *       "quantity_display": "1000매",
 *       "formatted": {
 *         "print_price": "50,000",
 *         "design_price": "30,000",
 *         "subtotal_price": "80,000",
 *         "vat_price": "8,000",
 *         "total_price": "88,000"
 *       }
 *     }
 *   }
 * }
 */

// 기본 설정 및 클래스 로드
require_once 'bootstrap.php';

try {
    // HTTP 메소드 검증 (POST만 허용)
    $validator = new InputValidator();
    $validator->validateHttpMethod('POST');
    
    // Content-Type 확인 및 파라미터 추출
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        // JSON 요청 처리
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            errorResponse('INVALID_JSON', 'JSON 형식이 올바르지 않습니다.');
        }
        $params = $input;
    } else {
        // 일반 POST 요청 처리 (FormData 또는 URL-encoded)
        $params = $_POST;
    }
    
    debugLog('Price calculation request received: ' . json_encode($params), 'API');
    
    // 필수 파라미터 존재 여부 사전 확인
    $requiredParams = ['MY_type', 'PN_type', 'MY_amount', 'POtype', 'ordertype'];
    foreach ($requiredParams as $param) {
        if (!isset($params[$param]) || $params[$param] === '') {
            errorResponse('MISSING_PARAMETER', "{$param} 파라미터가 필요합니다.");
        }
    }
    
    // 컨트롤러 초기화
    $controller = new AjaxController($db);
    
    // 가격 계산 실행
    $result = $controller->calculatePrice($params);
    
    // 성공 응답
    $controller->jsonResponse($result, true, 200);
    
} catch (Exception $e) {
    // 에러 로깅
    logMessage('Price calculation error: ' . $e->getMessage(), 'ERROR');
    
    // 개발 모드에서는 상세 에러 정보 포함
    $errorDetails = DEBUG_MODE ? $e->getMessage() : null;
    
    // 에러 응답
    errorResponse('CALCULATION_ERROR', '가격 계산 중 오류가 발생했습니다.', $errorDetails);
}
?>