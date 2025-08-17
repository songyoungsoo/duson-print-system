<?php
/**
 * LittlePrint용 입력값 검증을 담당하는 클래스
 * SQL 인젝션 방지와 데이터 유효성 검사를 수행합니다.
 * 
 * LittlePrint 시스템의 특징:
 * - 수량: 100매, 200매, ..., 1000매 (매 단위)
 * - 인쇄면: 양면(2)이 기본값, 단면(1)
 */
class InputValidator {
    
    /**
     * 종류(MY_type) 값 검증
     * @param mixed $value 검증할 값
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function validateCategoryType($value) {
        // 정수형 검증
        $sanitized = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($sanitized === false || $sanitized === null) {
            return [false, null, '종류 값은 숫자여야 합니다.'];
        }
        
        if ($sanitized < 1) {
            return [false, null, '종류 값은 1 이상이어야 합니다.'];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * 종이종류(MY_Fsd) 값 검증
     * @param mixed $value 검증할 값
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function validatePaperType($value) {
        $sanitized = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($sanitized === false || $sanitized === null) {
            return [false, null, '종이종류 값은 숫자여야 합니다.'];
        }
        
        if ($sanitized < 1) {
            return [false, null, '종이종류 값은 1 이상이어야 합니다.'];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * 종이규격(PN_type) 값 검증
     * @param mixed $value 검증할 값
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function validatePaperSize($value) {
        $sanitized = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($sanitized === false || $sanitized === null) {
            return [false, null, '종이규격 값은 숫자여야 합니다.'];
        }
        
        if ($sanitized < 1) {
            return [false, null, '종이규격 값은 1 이상이어야 합니다.'];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * 수량(MY_amount) 값 검증 - LittlePrint는 매 단위
     * @param mixed $value 검증할 값
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function validateQuantity($value) {
        $sanitized = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($sanitized === false || $sanitized === null) {
            return [false, null, '수량 값은 숫자여야 합니다.'];
        }
        
        // LittlePrint 허용 수량: 100, 200, 300, ..., 1000
        $allowedQuantities = [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000];
        
        if (!in_array($sanitized, $allowedQuantities)) {
            return [false, null, '수량은 100매~1000매 범위에서 100매 단위로만 선택 가능합니다.'];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * 인쇄면(POtype) 값 검증
     * @param mixed $value 검증할 값
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function validatePrintSide($value) {
        $sanitized = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($sanitized === false || $sanitized === null) {
            return [false, null, '인쇄면 값은 숫자여야 합니다.'];
        }
        
        // 1: 단면, 2: 양면
        if (!in_array($sanitized, [1, 2])) {
            return [false, null, '인쇄면은 1(단면) 또는 2(양면)만 가능합니다.'];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * 주문형태(ordertype) 값 검증
     * @param mixed $value 검증할 값
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function validateOrderType($value) {
        if (!is_string($value)) {
            return [false, null, '주문형태 값은 문자열이어야 합니다.'];
        }
        
        $sanitized = trim($value);
        
        // 허용되는 주문형태: total(디자인+인쇄), print(인쇄만), design(디자인만)
        $allowedTypes = ['total', 'print', 'design'];
        
        if (!in_array($sanitized, $allowedTypes)) {
            return [false, null, '주문형태는 total, print, design 중 하나여야 합니다.'];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * 숫자 값 검증 (범위 포함)
     * @param mixed $value 검증할 값
     * @param int|null $min 최소값
     * @param int|null $max 최대값
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function validateNumeric($value, $min = null, $max = null) {
        $sanitized = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($sanitized === false || $sanitized === null) {
            return [false, null, '숫자 값이 유효하지 않습니다.'];
        }
        
        if ($min !== null && $sanitized < $min) {
            return [false, null, "값은 {$min} 이상이어야 합니다."];
        }
        
        if ($max !== null && $sanitized > $max) {
            return [false, null, "값은 {$max} 이하여야 합니다."];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * 소수점 숫자 검증
     * @param mixed $value 검증할 값
     * @param float|null $min 최소값
     * @param float|null $max 최대값
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function validateFloat($value, $min = null, $max = null) {
        $sanitized = filter_var($value, FILTER_VALIDATE_FLOAT);
        
        if ($sanitized === false || $sanitized === null) {
            return [false, null, '소수점 값이 유효하지 않습니다.'];
        }
        
        if ($min !== null && $sanitized < $min) {
            return [false, null, "값은 {$min} 이상이어야 합니다."];
        }
        
        if ($max !== null && $sanitized > $max) {
            return [false, null, "값은 {$max} 이하여야 합니다."];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * 문자열 입력값 정리 및 검증
     * @param string $input 입력 문자열
     * @param int $maxLength 최대 길이
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function sanitizeString($input, $maxLength = 255) {
        if (!is_string($input)) {
            return [false, null, '문자열 값이 아닙니다.'];
        }
        
        // HTML 태그 제거 및 특수문자 이스케이프
        $sanitized = htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
        
        if (strlen($sanitized) > $maxLength) {
            return [false, null, "문자열 길이는 {$maxLength}자를 초과할 수 없습니다."];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * LittlePrint 가격 계산용 파라미터 일괄 검증
     * @param array $params 검증할 파라미터들
     * @return array [isValid, sanitizedValues, errors]
     */
    public function validatePriceCalculationParams($params) {
        $sanitizedValues = [];
        $errors = [];
        $isValid = true;
        
        // 종류 검증
        if (isset($params['MY_type'])) {
            $result = $this->validateCategoryType($params['MY_type']);
            if (!$result[0]) {
                $errors['MY_type'] = $result[2];
                $isValid = false;
            } else {
                $sanitizedValues['MY_type'] = $result[1];
            }
        }
        
        // 종이종류 검증
        if (isset($params['MY_Fsd'])) {
            $result = $this->validatePaperType($params['MY_Fsd']);
            if (!$result[0]) {
                $errors['MY_Fsd'] = $result[2];
                $isValid = false;
            } else {
                $sanitizedValues['MY_Fsd'] = $result[1];
            }
        }
        
        // 종이규격 검증
        if (isset($params['PN_type'])) {
            $result = $this->validatePaperSize($params['PN_type']);
            if (!$result[0]) {
                $errors['PN_type'] = $result[2];
                $isValid = false;
            } else {
                $sanitizedValues['PN_type'] = $result[1];
            }
        }
        
        // 수량 검증
        if (isset($params['MY_amount'])) {
            $result = $this->validateQuantity($params['MY_amount']);
            if (!$result[0]) {
                $errors['MY_amount'] = $result[2];
                $isValid = false;
            } else {
                $sanitizedValues['MY_amount'] = $result[1];
            }
        }
        
        // 인쇄면 검증
        if (isset($params['POtype'])) {
            $result = $this->validatePrintSide($params['POtype']);
            if (!$result[0]) {
                $errors['POtype'] = $result[2];
                $isValid = false;
            } else {
                $sanitizedValues['POtype'] = $result[1];
            }
        }
        
        // 주문형태 검증
        if (isset($params['ordertype'])) {
            $result = $this->validateOrderType($params['ordertype']);
            if (!$result[0]) {
                $errors['ordertype'] = $result[2];
                $isValid = false;
            } else {
                $sanitizedValues['ordertype'] = $result[1];
            }
        }
        
        return [$isValid, $sanitizedValues, $errors];
    }
    
    /**
     * SQL 인젝션 방지를 위한 추가 이스케이프 처리
     * @param string $value 이스케이프할 값
     * @param mysqli $connection 데이터베이스 연결
     * @return string 이스케이프된 값
     */
    public function escapeForSQL($value, $connection) {
        return mysqli_real_escape_string($connection, $value);
    }
}