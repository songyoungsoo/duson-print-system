<?php
/**
 * LittlePrint Ajax API 구조 테스트 파일
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
    $testResult = $validator->validateCategoryType(1);
    
    // 실제 데이터 조회 테스트
    $categories = $db->getMainCategories();
    
    // 성공 응답 테스트
    echo json_encode([
        'success' => true,
        'message' => 'LittlePrint Ajax API 구조가 정상적으로 설정되었습니다.',
        'data' => [
            'database_connected' => true,
            'controller_created' => true,
            'validator_working' => $testResult[0],
            'categories_count' => count($categories),
            'sample_categories' => array_slice($categories, 0, 3), // 처음 3개만 표시
            'config' => [
                'category_table' => CATEGORY_TABLE,
                'price_table' => PRICE_TABLE,
                'page_name' => PAGE_NAME
            ],
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
            'message' => $e->getMessage(),
            'trace' => isDebugMode() ? $e->getTraceAsString() : null
        ]
    ], JSON_UNESCAPED_UNICODE);
}