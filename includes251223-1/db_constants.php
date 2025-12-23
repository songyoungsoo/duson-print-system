<?php
/**
 * DB 접근 허용 상수 정의
 * 모든 페이지에서 db.php 사용 전에 include 필요
 */

// 중복 정의 방지
if (!defined('DB_ACCESS_ALLOWED')) {
    define('DB_ACCESS_ALLOWED', true);
}

// 환경 설정 (중복 정의 방지)
if (!defined('PRODUCTION')) {
    define('PRODUCTION', false); // 개발환경에서는 false, 운영환경에서는 true
}

// 추가 보안 상수들 (중복 정의 방지)
if (!defined('SECURE_MODE')) {
    define('SECURE_MODE', true);
}

if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', !PRODUCTION);
}
?>