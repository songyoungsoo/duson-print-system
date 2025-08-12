<?php
/**
 * Envelope Ajax 시스템 설정 파일
 * envelope 시스템에 특화된 설정값들을 관리합니다.
 */

// 에러 리포팅 설정
error_reporting(E_ALL);
ini_set('display_errors', 1); // 프로덕션에서는 0으로 설정

// 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// CORS 설정 (필요한 경우)
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type');

// envelope 시스템 특화 설정
define('ENVELOPE_TABLE', 'mlangprintauto_envelope');
define('CATEGORY_TABLE', 'mlangprintauto_transactioncate');
define('PRODUCT_TYPE', 'envelope');

// 응답 타임아웃 설정
define('AJAX_TIMEOUT', 30); // 30초

// 로그 설정
define('ENABLE_LOGGING', true);
define('LOG_FILE', __DIR__ . '/logs/envelope_ajax.log');

// 디버그 모드 설정
define('DEBUG_MODE', true); // 프로덕션에서는 false로 설정

// 데이터베이스 연결 설정은 상위 디렉토리의 db.php를 사용
require_once __DIR__ . '/../../../db.php';

// 공통 함수들
function logMessage($message, $level = 'INFO') {
    if (!ENABLE_LOGGING) return;
    
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

function debugLog($data, $label = 'DEBUG') {
    if (!DEBUG_MODE) return;
    
    $message = $label . ': ' . (is_array($data) || is_object($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data);
    logMessage($message, 'DEBUG');
}

// 에러 핸들러 설정
function handleError($errno, $errstr, $errfile, $errline) {
    $message = "Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}";
    logMessage($message, 'ERROR');
    
    if (!DEBUG_MODE) {
        // 프로덕션에서는 일반적인 에러 메시지만 반환
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
}

set_error_handler('handleError');

// 예외 핸들러 설정
function handleException($exception) {
    $message = "Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    logMessage($message, 'ERROR');
    
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

set_exception_handler('handleException');

// 공통 응답 함수
function jsonResponse($data, $success = true, $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($success) {
        $response['data'] = $data;
    } else {
        $response['error'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function errorResponse($code, $message, $details = null, $httpCode = 400) {
    $error = [
        'code' => $code,
        'message' => $message
    ];
    
    if ($details !== null) {
        $error['details'] = $details;
    }
    
    logMessage("Error Response: {$code} - {$message}", 'ERROR');
    jsonResponse($error, false, $httpCode);
}

// 입력 검증 헬퍼 함수
function validateRequired($value, $fieldName) {
    if (empty($value) && $value !== '0') {
        errorResponse('MISSING_PARAMETER', "{$fieldName} 파라미터가 필요합니다.");
    }
    return $value;
}

function validateInteger($value, $fieldName, $min = null, $max = null) {
    $intValue = filter_var($value, FILTER_VALIDATE_INT);
    if ($intValue === false) {
        errorResponse('INVALID_PARAMETER', "{$fieldName}는 정수여야 합니다.");
    }
    
    if ($min !== null && $intValue < $min) {
        errorResponse('INVALID_PARAMETER', "{$fieldName}는 {$min} 이상이어야 합니다.");
    }
    
    if ($max !== null && $intValue > $max) {
        errorResponse('INVALID_PARAMETER', "{$fieldName}는 {$max} 이하여야 합니다.");
    }
    
    return $intValue;
}

// 시작 로그
logMessage("Envelope Ajax system initialized", 'INFO');
?>