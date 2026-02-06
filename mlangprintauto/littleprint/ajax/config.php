<?php
/**
 * LittlePrint Ajax API용 설정 파일
 * 데이터베이스 연결 정보와 공통 설정을 관리합니다.
 */

// 세션 시작 (기존 시스템과의 호환성 유지)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 데이터베이스 연결 정보 - config.env.php 재사용 (SSOT)
require_once __DIR__ . '/../../../config.env.php';
$db_config = get_db_config();

define('DB_HOST', $db_config['host']);
define('DB_USER', $db_config['user']);
define('DB_PASSWORD', $db_config['password']);
define('DB_NAME', $db_config['database']);

// 개발 모드 설정 (config.env.php의 debug 플래그 사용)
define('DEVELOPMENT_MODE', $db_config['debug'] ?? false);

// LittlePrint 전용 테이블 설정
define('CATEGORY_TABLE', "mlangprintauto_transactioncate");
define('PRICE_TABLE', "mlangprintauto_littleprint");
define('PAGE_NAME', 'LittlePrint');

// Ajax 응답 관련 설정
define('AJAX_TIMEOUT', 30); // 초 단위
define('MAX_RESULTS_PER_PAGE', 100);

// 에러 보고 설정은 config.env.php에서 이미 처리됨

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