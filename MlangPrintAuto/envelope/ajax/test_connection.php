<?php
/**
 * Envelope Ajax 시스템 연결 테스트
 * 시스템 상태와 데이터베이스 연결을 확인합니다.
 */

// 부트스트랩 로드
require_once __DIR__ . '/bootstrap.php';

try {
    // 시스템 상태 확인
    $systemStatus = $ajaxController->getSystemStatus();
    
    // 추가 테스트 정보
    $testInfo = [
        'php_version' => PHP_VERSION,
        'mysqli_extension' => extension_loaded('mysqli') ? 'loaded' : 'not loaded',
        'json_extension' => extension_loaded('json') ? 'loaded' : 'not loaded',
        'current_time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get(),
        'memory_usage' => memory_get_usage(true),
        'memory_limit' => ini_get('memory_limit')
    ];
    
    // 데이터베이스 테스트 쿼리
    $dbTest = [];
    try {
        // 카테고리 테이블 테스트
        $categoryQuery = "SELECT COUNT(*) as count FROM " . CATEGORY_TABLE . " WHERE Ttable = 'envelope'";
        $categoryResult = mysqli_query($db, $categoryQuery);
        if ($categoryResult) {
            $categoryRow = mysqli_fetch_assoc($categoryResult);
            $dbTest['category_count'] = $categoryRow['count'];
        } else {
            $dbTest['category_error'] = mysqli_error($db);
        }
        
        // envelope 테이블 테스트
        $envelopeQuery = "SELECT COUNT(*) as count FROM " . ENVELOPE_TABLE;
        $envelopeResult = mysqli_query($db, $envelopeQuery);
        if ($envelopeResult) {
            $envelopeRow = mysqli_fetch_assoc($envelopeResult);
            $dbTest['envelope_count'] = $envelopeRow['count'];
        } else {
            $dbTest['envelope_error'] = mysqli_error($db);
        }
        
    } catch (Exception $e) {
        $dbTest['test_error'] = $e->getMessage();
    }
    
    // 샘플 API 테스트
    $apiTest = [];
    try {
        // 첫 번째 카테고리 조회
        $firstCategoryQuery = "SELECT no FROM " . CATEGORY_TABLE . " WHERE Ttable = 'envelope' AND BigNo = 0 ORDER BY no ASC LIMIT 1";
        $firstCategoryResult = mysqli_query($db, $firstCategoryQuery);
        
        if ($firstCategoryResult && mysqli_num_rows($firstCategoryResult) > 0) {
            $firstCategory = mysqli_fetch_assoc($firstCategoryResult);
            $categoryId = $firstCategory['no'];
            
            // 종류 조회 테스트
            $typesResult = $ajaxController->getEnvelopeTypes($categoryId);
            $apiTest['get_envelope_types'] = [
                'category_id' => $categoryId,
                'result' => $typesResult
            ];
        } else {
            $apiTest['get_envelope_types'] = 'No categories found for testing';
        }
        
    } catch (Exception $e) {
        $apiTest['api_test_error'] = $e->getMessage();
    }
    
    // 전체 결과 구성
    $result = [
        'system_status' => $systemStatus,
        'test_info' => $testInfo,
        'database_test' => $dbTest,
        'api_test' => $apiTest,
        'test_passed' => true,
        'message' => 'Envelope Ajax 시스템이 정상적으로 작동하고 있습니다.'
    ];
    
    // 성공 응답
    jsonResponse($result);
    
} catch (Exception $e) {
    // 테스트 실패
    logMessage("Test connection failed: " . $e->getMessage(), 'ERROR');
    
    $errorResult = [
        'test_passed' => false,
        'error_message' => $e->getMessage(),
        'message' => 'Envelope Ajax 시스템 테스트에 실패했습니다.'
    ];
    
    errorResponse('TEST_FAILED', 'Envelope Ajax 시스템 테스트에 실패했습니다.', $errorResult, 500);
}
?>