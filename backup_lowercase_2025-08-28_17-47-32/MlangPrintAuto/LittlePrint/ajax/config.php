<?php
/**
 * LittlePrint Ajax API용 설정 파일
 * 데이터베이스 연결 정보와 공통 설정을 관리합니다.
 */

// 세션 시작 (기존 시스템과의 호환성 유지)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 데이터베이스 연결 정보
define('DB_HOST', 'localhost');
define('DB_USER', 'duson1830');
define('DB_PASSWORD', 'du1830');
define('DB_NAME', 'duson1830');

// LittlePrint 전용 테이블 설정
define('CATEGORY_TABLE', "MlangPrintAuto_transactionCate');
define('PRICE_TABLE', "MlangPrintAuto_LittlePrint');
define('PAGE_NAME', 'littleprint');

// Ajax 응답 관련 설정
define('AJAX_TIMEOUT', 30); // 초 단위
define('MAX_RESULTS_PER_PAGE', 100);

// 에러 보고 설정 (개발 환경에서만 사용)
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 로그 파일 경로
define('ERROR_LOG_PATH', __DIR__ . '/logs/error.log');

// CORS 설정 (필요시)
function setCORSHeaders() {
    // 현재는 같은 도메인에서만 사용하므로 기본 설정
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_HOST']);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
}

// 에러 로그 기록 함수
function logError($message, $context = []) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    $logDir = dirname(ERROR_LOG_PATH);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents(ERROR_LOG_PATH, json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
}

// 디버그 모드 확인
function isDebugMode() {
    return defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE;
}

// 요청이 Ajax인지 확인
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// 보안 헤더 설정
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
}