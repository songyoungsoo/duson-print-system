<?php
/**
 * Ajax 요청을 처리하는 기본 컨트롤러 클래스
 * 공통 응답 형식과 에러 처리를 담당합니다.
 */
class AjaxController {
    private $db;
    private $validator;
    
    public function __construct($database) {
        $this->db = $database;
        $this->validator = new InputValidator();
        
        // JSON 응답 헤더 설정
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
    }
    
    /**
     * 성공 응답을 JSON 형태로 반환
     * @param mixed $data 응답 데이터
     * @return string JSON 응답
     */
    protected function successResponse($data) {
        return json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * 에러 응답을 JSON 형태로 반환
     * @param string $message 에러 메시지
     * @param string $code 에러 코드
     * @param int $httpCode HTTP 상태 코드
     * @return string JSON 응답
     */
    protected function errorResponse($message, $code = 'GENERAL_ERROR', $httpCode = 400) {
        http_response_code($httpCode);
        return json_encode([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * 데이터베이스 에러 처리
     * @param Exception $e 예외 객체
     * @return string JSON 에러 응답
     */
    protected function handleDatabaseError($e) {
        // 에러 로그 기록
        error_log("Database Error: " . $e->getMessage());
        
        return $this->errorResponse(
            '데이터베이스 처리 중 오류가 발생했습니다.',
            'DATABASE_ERROR',
            500
        );
    }
    
    /**
     * 입력 검증 에러 처리
     * @param string $field 필드명
     * @param string $message 에러 메시지
     * @return string JSON 에러 응답
     */
    protected function handleValidationError($field, $message) {
        return $this->errorResponse(
            "{$field}: {$message}",
            'VALIDATION_ERROR',
            400
        );
    }
    
    /**
     * 요청 메소드 검증
     * @param string $expectedMethod 예상 메소드 (GET, POST 등)
     * @return bool 검증 결과
     */
    protected function validateRequestMethod($expectedMethod) {
        if ($_SERVER['REQUEST_METHOD'] !== $expectedMethod) {
            echo $this->errorResponse(
                "잘못된 요청 메소드입니다. {$expectedMethod} 메소드를 사용해주세요.",
                'INVALID_METHOD',
                405
            );
            return false;
        }
        return true;
    }
    
    /**
     * CSRF 토큰 검증 (향후 확장용)
     * @return bool 검증 결과
     */
    protected function validateCSRFToken() {
        // 현재는 기본 구현만 제공
        // 실제 프로젝트에서는 세션 기반 CSRF 토큰 검증 구현
        return true;
    }
}