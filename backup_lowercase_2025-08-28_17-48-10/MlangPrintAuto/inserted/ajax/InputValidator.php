<?php
/**
 * 입력값 검증을 담당하는 클래스
 * SQL 인젝션 방지와 데이터 유효성 검사를 수행합니다.
 */
class InputValidator {
    
    /**
     * 인쇄색상(print_type) 값 검증
     * @param mixed $value 검증할 값
     * @return array [isValid, sanitizedValue, errorMessage]
     */
    public function validatePrintType($value) {
        // 정수형 검증
        $sanitized = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($sanitized === false || $sanitized === null) {
            return [false, null, '인쇄색상 값은 숫자여야 합니다.'];
        }
        
        if ($sanitized < 1) {
            return [false, null, '인쇄색상 값은 1 이상이어야 합니다.'];
        }
        
        return [true, $sanitized, null];
    }
    
    /**
     * 종이종류(paper_type) 값 검증
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
     * 배열 형태의 여러 값 검증
     * @param array $values 검증할 값들
     * @param array $rules 검증 규칙
     * @return array [isValid, sanitizedValues, errors]
     */
    public function validateMultiple($values, $rules) {
        $sanitizedValues = [];
        $errors = [];
        $isValid = true;
        
        foreach ($rules as $field => $rule) {
            $value = isset($values[$field]) ? $values[$field] : null;
            
            if ($rule['required'] && ($value === null || $value === '')) {
                $errors[$field] = "{$field}는 필수 입력값입니다.";
                $isValid = false;
                continue;
            }
            
            if ($value !== null && $value !== '') {
                $validationMethod = $rule['method'];
                $params = isset($rule['params']) ? $rule['params'] : [];
                
                $result = call_user_func_array([$this, $validationMethod], array_merge([$value], $params));
                
                if (!$result[0]) {
                    $errors[$field] = $result[2];
                    $isValid = false;
                } else {
                    $sanitizedValues[$field] = $result[1];
                }
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