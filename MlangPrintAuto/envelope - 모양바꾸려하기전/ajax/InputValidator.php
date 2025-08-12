<?php
/**
 * Envelope 시스템용 입력 검증 클래스
 * 사용자 입력값의 유효성을 검증하고 보안을 강화합니다.
 */

class InputValidator {
    
    /**
     * 카테고리 타입 검증
     * 
     * @param mixed $value 검증할 값
     * @return int 검증된 카테고리 ID
     */
    public function validateCategoryType($value) {
        debugLog("Validating category type: {$value}", 'VALIDATOR');
        
        // 필수값 확인
        if (empty($value) && $value !== '0') {
            errorResponse('MISSING_PARAMETER', 'category_type 파라미터가 필요합니다.');
        }
        
        // 정수 검증
        $categoryType = filter_var($value, FILTER_VALIDATE_INT);
        if ($categoryType === false) {
            errorResponse('INVALID_PARAMETER', 'category_type은 정수여야 합니다.');
        }
        
        // 범위 검증 (양수)
        if ($categoryType < 1) {
            errorResponse('INVALID_PARAMETER', 'category_type은 1 이상이어야 합니다.');
        }
        
        // 최대값 검증 (합리적인 범위)
        if ($categoryType > 999999) {
            errorResponse('INVALID_PARAMETER', 'category_type 값이 너무 큽니다.');
        }
        
        debugLog("Category type validated: {$categoryType}", 'VALIDATOR');
        return $categoryType;
    }
    
    /**
     * envelope 타입 검증
     * 
     * @param mixed $value 검증할 값
     * @return int 검증된 envelope 타입 ID
     */
    public function validateEnvelopeType($value) {
        debugLog("Validating envelope type: {$value}", 'VALIDATOR');
        
        // 필수값 확인
        if (empty($value) && $value !== '0') {
            errorResponse('MISSING_PARAMETER', 'envelope_type 파라미터가 필요합니다.');
        }
        
        // 정수 검증
        $envelopeType = filter_var($value, FILTER_VALIDATE_INT);
        if ($envelopeType === false) {
            errorResponse('INVALID_PARAMETER', 'envelope_type은 정수여야 합니다.');
        }
        
        // 범위 검증 (양수)
        if ($envelopeType < 1) {
            errorResponse('INVALID_PARAMETER', 'envelope_type은 1 이상이어야 합니다.');
        }
        
        // 최대값 검증
        if ($envelopeType > 999999) {
            errorResponse('INVALID_PARAMETER', 'envelope_type 값이 너무 큽니다.');
        }
        
        debugLog("Envelope type validated: {$envelopeType}", 'VALIDATOR');
        return $envelopeType;
    }
    
    /**
     * 수량 검증
     * 
     * @param mixed $value 검증할 값
     * @return string 검증된 수량
     */
    public function validateQuantity($value) {
        debugLog("Validating quantity: {$value}", 'VALIDATOR');
        
        // 필수값 확인
        if (empty($value) && $value !== '0') {
            errorResponse('MISSING_PARAMETER', 'quantity 파라미터가 필요합니다.');
        }
        
        // 문자열로 변환 후 정리
        $quantity = trim((string)$value);
        
        // 빈 값 확인
        if ($quantity === '') {
            errorResponse('INVALID_PARAMETER', 'quantity는 빈 값일 수 없습니다.');
        }
        
        // 허용되는 수량 패턴 (숫자만 또는 숫자+단위)
        if (!preg_match('/^[0-9]+$/', $quantity)) {
            errorResponse('INVALID_PARAMETER', 'quantity는 숫자여야 합니다.');
        }
        
        // 수량 범위 검증
        $numericQuantity = intval($quantity);
        if ($numericQuantity < 1) {
            errorResponse('INVALID_PARAMETER', 'quantity는 1 이상이어야 합니다.');
        }
        
        if ($numericQuantity > 100000) {
            errorResponse('INVALID_PARAMETER', 'quantity 값이 너무 큽니다.');
        }
        
        debugLog("Quantity validated: {$quantity}", 'VALIDATOR');
        return $quantity;
    }
    
    /**
     * 인쇄 타입 검증 (POtype)
     * 
     * @param mixed $value 검증할 값
     * @return int 검증된 인쇄 타입
     */
    public function validatePrintType($value) {
        debugLog("Validating print type: {$value}", 'VALIDATOR');
        
        // 필수값 확인
        if (empty($value) && $value !== '0') {
            errorResponse('MISSING_PARAMETER', 'print_type 파라미터가 필요합니다.');
        }
        
        // 정수 검증
        $printType = filter_var($value, FILTER_VALIDATE_INT);
        if ($printType === false) {
            errorResponse('INVALID_PARAMETER', 'print_type은 정수여야 합니다.');
        }
        
        // envelope 시스템에서 허용되는 인쇄 타입: 1(마스터1도), 2(마스터2도), 3(칼라4도)
        $allowedTypes = [1, 2, 3];
        if (!in_array($printType, $allowedTypes)) {
            errorResponse('INVALID_PARAMETER', 'print_type은 1, 2, 3 중 하나여야 합니다.');
        }
        
        debugLog("Print type validated: {$printType}", 'VALIDATOR');
        return $printType;
    }
    
    /**
     * 주문 타입 검증 (ordertype)
     * 
     * @param mixed $value 검증할 값
     * @return string 검증된 주문 타입
     */
    public function validateOrderType($value) {
        debugLog("Validating order type: {$value}", 'VALIDATOR');
        
        // 필수값 확인
        if (empty($value)) {
            errorResponse('MISSING_PARAMETER', 'order_type 파라미터가 필요합니다.');
        }
        
        // 문자열로 변환 후 정리
        $orderType = trim((string)$value);
        
        // 허용되는 주문 타입
        $allowedTypes = ['total', 'print', 'design'];
        if (!in_array($orderType, $allowedTypes)) {
            errorResponse('INVALID_PARAMETER', 'order_type은 total, print, design 중 하나여야 합니다.');
        }
        
        debugLog("Order type validated: {$orderType}", 'VALIDATOR');
        return $orderType;
    }
    
    /**
     * 숫자 검증 (일반적인 용도)
     * 
     * @param mixed $value 검증할 값
     * @param int|null $min 최소값
     * @param int|null $max 최대값
     * @return int 검증된 숫자
     */
    public function validateNumeric($value, $min = null, $max = null) {
        debugLog("Validating numeric value: {$value}", 'VALIDATOR');
        
        // 정수 검증
        $numericValue = filter_var($value, FILTER_VALIDATE_INT);
        if ($numericValue === false) {
            errorResponse('INVALID_PARAMETER', '숫자 값이 유효하지 않습니다.');
        }
        
        // 최소값 검증
        if ($min !== null && $numericValue < $min) {
            errorResponse('INVALID_PARAMETER', "값은 {$min} 이상이어야 합니다.");
        }
        
        // 최대값 검증
        if ($max !== null && $numericValue > $max) {
            errorResponse('INVALID_PARAMETER', "값은 {$max} 이하여야 합니다.");
        }
        
        debugLog("Numeric value validated: {$numericValue}", 'VALIDATOR');
        return $numericValue;
    }
    
    /**
     * 입력값 정리 (XSS 방지)
     * 
     * @param mixed $input 정리할 입력값
     * @return string 정리된 입력값
     */
    public function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        // 문자열로 변환
        $sanitized = (string)$input;
        
        // HTML 태그 제거
        $sanitized = strip_tags($sanitized);
        
        // 특수 문자 이스케이프
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        
        // 앞뒤 공백 제거
        $sanitized = trim($sanitized);
        
        return $sanitized;
    }
    
    /**
     * 가격 계산용 파라미터 일괄 검증
     * 
     * @param array $params 검증할 파라미터 배열
     * @return array 검증된 파라미터 배열
     */
    public function validatePriceCalculationParams($params) {
        debugLog("Validating price calculation params", 'VALIDATOR');
        
        $validated = [];
        
        // MY_type (구분) 검증
        if (!isset($params['MY_type'])) {
            errorResponse('MISSING_PARAMETER', 'MY_type 파라미터가 필요합니다.');
        }
        $validated['MY_type'] = $this->validateCategoryType($params['MY_type']);
        
        // PN_type (종류) 검증
        if (!isset($params['PN_type'])) {
            errorResponse('MISSING_PARAMETER', 'PN_type 파라미터가 필요합니다.');
        }
        $validated['PN_type'] = $this->validateEnvelopeType($params['PN_type']);
        
        // MY_amount (수량) 검증
        if (!isset($params['MY_amount'])) {
            errorResponse('MISSING_PARAMETER', 'MY_amount 파라미터가 필요합니다.');
        }
        $validated['MY_amount'] = $this->validateQuantity($params['MY_amount']);
        
        // POtype (인쇄색상) 검증
        if (!isset($params['POtype'])) {
            errorResponse('MISSING_PARAMETER', 'POtype 파라미터가 필요합니다.');
        }
        $validated['POtype'] = $this->validatePrintType($params['POtype']);
        
        // ordertype (주문타입) 검증
        if (!isset($params['ordertype'])) {
            errorResponse('MISSING_PARAMETER', 'ordertype 파라미터가 필요합니다.');
        }
        $validated['ordertype'] = $this->validateOrderType($params['ordertype']);
        
        debugLog("All price calculation params validated", 'VALIDATOR');
        return $validated;
    }
    
    /**
     * HTTP 메소드 검증
     * 
     * @param string $expectedMethod 예상되는 HTTP 메소드
     */
    public function validateHttpMethod($expectedMethod) {
        $currentMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        
        if (strtoupper($currentMethod) !== strtoupper($expectedMethod)) {
            errorResponse('METHOD_NOT_ALLOWED', "이 엔드포인트는 {$expectedMethod} 메소드만 허용합니다.", null, 405);
        }
        
        debugLog("HTTP method validated: {$currentMethod}", 'VALIDATOR');
    }
    
    /**
     * CSRF 토큰 검증 (필요한 경우)
     * 
     * @param string $token 검증할 토큰
     */
    public function validateCsrfToken($token) {
        // 세션이 시작되지 않았다면 시작
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        if (empty($sessionToken) || $token !== $sessionToken) {
            errorResponse('INVALID_TOKEN', 'CSRF 토큰이 유효하지 않습니다.', null, 403);
        }
        
        debugLog("CSRF token validated", 'VALIDATOR');
    }
}
?>