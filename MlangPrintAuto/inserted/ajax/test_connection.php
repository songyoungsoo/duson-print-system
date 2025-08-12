<?php
/**
 * Ajax API 구조 테스트 파일
 * 데이터베이스 연결과 기본 클래스들이 정상 작동하는지 확인합니다.
 */

// 개발 모드 활성화 (테스트용)
define('DEVELOPMENT_MODE', true);

// 부트스트랩 로드
require_once __DIR__ . '/bootstrap.php';

try {
    // 데이터베이스 연결 테스트
    $db = createDatabaseManager();
    
    // Ajax 컨트롤러 생성 테스트
    $controller = createAjaxController();
    
    // 입력 검증 테스트
    $validator = new InputValidator();
    $testResult = $validator->validatePrintType(1);
    
    // 성공 응답 테스트
    echo json_encode([
        'success' => true,
        'message' => 'Ajax API 구조가 정상적으로 설정되었습니다.',
        'data' => [
            'database_connected' => true,
            'controller_created' => true,
            'validator_working' => $testResult[0],
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // 에러 응답
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SETUP_ERROR',
            'message' => $e->getMessage()
        ]
    ], JSON_UNESCAPED_UNICODE);
}