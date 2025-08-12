<?php
/**
 * Envelope 종류 조회 API
 * 구분(MY_type)에 따른 종류(PN_type) 목록을 반환합니다.
 * 
 * Method: GET
 * Parameters:
 *   - category_type (required): 구분 ID
 * 
 * Response:
 *   - success: true/false
 *   - data: 종류 목록 또는 에러 정보
 */

// 부트스트랩 로드
require_once __DIR__ . '/bootstrap.php';

try {
    // HTTP 메소드 검증
    $validator = new InputValidator();
    $validator->validateHttpMethod('GET');
    
    // 요청 파라미터 가져오기
    $params = getRequestParams();
    
    // category_type 파라미터 검증
    if (!isset($params['category_type'])) {
        errorResponse('MISSING_PARAMETER', 'category_type 파라미터가 필요합니다.');
    }
    
    $categoryType = $params['category_type'];
    
    logMessage("Envelope types request for category: {$categoryType}", 'INFO');
    
    // Ajax 컨트롤러를 통해 종류 목록 조회
    $result = $ajaxController->getEnvelopeTypes($categoryType);
    
    // 성공 응답
    jsonResponse($result);
    
} catch (Exception $e) {
    // 예외 처리
    logMessage("Exception in get_envelope_types.php: " . $e->getMessage(), 'ERROR');
    errorResponse('INTERNAL_ERROR', '서버 내부 오류가 발생했습니다.');
}
?>