<?php
/**
 * Envelope Ajax 시스템 부트스트랩 파일
 * 모든 Ajax 엔드포인트에서 공통으로 사용되는 초기화 코드
 */

// 세션 시작 (필요한 경우)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 기본 설정 로드
require_once __DIR__ . '/config.php';

// 클래스 파일들 로드
require_once __DIR__ . '/InputValidator.php';
require_once __DIR__ . '/DatabaseManager.php';
require_once __DIR__ . '/AjaxController.php';

// 데이터베이스 연결
try {
    $db = mysqli_connect($host, $user, $password, $dataname);
    
    if (!$db) {
        throw new Exception('데이터베이스 연결 실패: ' . mysqli_connect_error());
    }
    
    // UTF-8 설정
    mysqli_set_charset($db, 'utf8');
    
    debugLog('Database connected successfully', 'BOOTSTRAP');
    
} catch (Exception $e) {
    logMessage('Database connection failed: ' . $e->getMessage(), 'ERROR');
    errorResponse('DATABASE_CONNECTION_FAILED', '데이터베이스 연결에 실패했습니다.', null, 500);
}

// Ajax 컨트롤러 초기화
try {
    $ajaxController = new AjaxController($db);
    debugLog('AjaxController initialized successfully', 'BOOTSTRAP');
    
} catch (Exception $e) {
    logMessage('AjaxController initialization failed: ' . $e->getMessage(), 'ERROR');
    errorResponse('CONTROLLER_INIT_FAILED', '컨트롤러 초기화에 실패했습니다.', null, 500);
}

// 공통 헤더 설정
header('X-Powered-By: Envelope Ajax System v1.0');

// 요청 정보 로깅
$requestInfo = [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    'timestamp' => date('Y-m-d H:i:s')
];

debugLog('Request info: ' . json_encode($requestInfo), 'BOOTSTRAP');

// 요청 메소드별 파라미터 추출 함수
function getRequestParams() {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    switch (strtoupper($method)) {
        case 'GET':
            return $_GET;
            
        case 'POST':
            // JSON 데이터 처리
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $jsonData = file_get_contents('php://input');
                $decodedData = json_decode($jsonData, true);
                return $decodedData ?? $_POST;
            }
            return $_POST;
            
        case 'PUT':
        case 'PATCH':
            parse_str(file_get_contents('php://input'), $putData);
            return $putData;
            
        default:
            return [];
    }
}

// 요청 파라미터 로깅
$requestParams = getRequestParams();
debugLog('Request params: ' . json_encode($requestParams), 'BOOTSTRAP');

// 정리 함수 등록
register_shutdown_function(function() use ($db) {
    if ($db && mysqli_ping($db)) {
        mysqli_close($db);
        debugLog('Database connection closed', 'BOOTSTRAP');
    }
    
    debugLog('Bootstrap shutdown completed', 'BOOTSTRAP');
});

// 부트스트랩 완료 로그
logMessage('Envelope Ajax bootstrap completed successfully', 'INFO');
?>