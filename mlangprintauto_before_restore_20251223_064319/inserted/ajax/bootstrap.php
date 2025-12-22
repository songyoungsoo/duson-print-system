<?php
/**
 * Ajax API 부트스트랩 파일
 * 모든 Ajax 엔드포인트에서 공통으로 사용되는 초기화 코드
 */

// 설정 파일 로드
require_once __DIR__ . '/config.php';

// 클래스 파일들 로드
require_once __DIR__ . '/InputValidator.php';
require_once __DIR__ . '/DatabaseManager.php';
require_once __DIR__ . '/AjaxController.php';

// 보안 헤더 설정
setSecurityHeaders();

// CORS 헤더 설정 (필요시)
setCORSHeaders();

// Ajax 요청인지 확인 (선택적 검증)
if (!isAjaxRequest() && !isDebugMode()) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INVALID_REQUEST',
            'message' => 'Ajax 요청만 허용됩니다.'
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 전역 에러 핸들러 설정
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $errorInfo = [
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line
    ];
    
    logError('PHP Error: ' . $message, $errorInfo);
    
    // 개발 모드가 아닌 경우 일반적인 에러 메시지 반환
    if (!isDebugMode()) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => '서버 내부 오류가 발생했습니다.'
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    return false;
});

// 예외 핸들러 설정
set_exception_handler(function($exception) {
    $errorInfo = [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ];
    
    logError('Uncaught Exception: ' . $exception->getMessage(), $errorInfo);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INTERNAL_ERROR',
            'message' => isDebugMode() ? $exception->getMessage() : '서버 내부 오류가 발생했습니다.'
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * 데이터베이스 매니저 인스턴스 생성 함수
 * @return DatabaseManager 데이터베이스 매니저 인스턴스
 * @throws Exception 연결 실패 시
 */
function createDatabaseManager() {
    try {
        return new DatabaseManager(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    } catch (Exception $e) {
        logError('Database connection failed: ' . $e->getMessage());
        throw new Exception('데이터베이스 연결에 실패했습니다.');
    }
}

/**
 * Ajax 컨트롤러 인스턴스 생성 함수
 * @return AjaxController Ajax 컨트롤러 인스턴스
 * @throws Exception 초기화 실패 시
 */
function createAjaxController() {
    $db = createDatabaseManager();
    return new AjaxController($db);
}

/**
 * 요청 파라미터 안전하게 가져오기
 * @param string $key 파라미터 키
 * @param mixed $default 기본값
 * @param string $method 요청 메소드 (GET, POST)
 * @return mixed 파라미터 값
 */
function getRequestParam($key, $default = null, $method = 'GET') {
    $source = ($method === 'POST') ? $_POST : $_GET;
    return isset($source[$key]) ? $source[$key] : $default;
}

/**
 * JSON 입력 데이터 파싱
 * @return array|null 파싱된 데이터
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    if (empty($input)) {
        return null;
    }
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('잘못된 JSON 형식입니다.');
    }
    
    return $data;
}