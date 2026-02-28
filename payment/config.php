<?php
/**
 * KG이니시스 결제 설정 파일
 * 두손기획인쇄 - dsp114.co.kr
 *
 * 가맹점코드: dsp1147479
 */

// ================================
// ⚠️ 운영/테스트 모드 설정
// ================================
// true: 테스트 모드 (실제 결제 안됨, INIpayTest 사용)
// false: 운영 모드 (실제 결제됨, MID: dsp1147479 사용)
//
// 🔐 로컬 환경은 테스트 모드 유지
define('INICIS_TEST_MODE', true);

// ================================
// 가맹점 정보
// ================================
if (INICIS_TEST_MODE) {
    // 테스트용
    define('INICIS_MID', 'INIpayTest');
    define('INICIS_SIGNKEY', 'SU5JTElURV9UUklQTEVERVNfS0VZU1RS');
} else {
    // 운영용 - KG이니시스 실제 가맹점 정보
    define('INICIS_MID', 'dsp1147479');
    define('INICIS_SIGNKEY', 'YXgxUnVtVlNvZndWUWg4RWVFUGZwUT09'); // KG이니시스 웹결제 Sign Key
}

// ================================
// 도메인 설정
// ================================
$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';

if (strpos($server_name, 'dsp114.co.kr') !== false) {
    define('SITE_DOMAIN', 'https://dsp114.co.kr');
} elseif (strpos($server_name, 'dsp1830.shop') !== false) {
    define('SITE_DOMAIN', 'https://dsp1830.shop');
} else {
    define('SITE_DOMAIN', 'http://localhost');
}

// 결제 결과 URL
define('INICIS_RETURN_URL', SITE_DOMAIN . '/payment/return.php');
define('INICIS_CLOSE_URL', SITE_DOMAIN . '/payment/close.php');

// ================================
// JS 라이브러리 URL
// ================================
if (INICIS_TEST_MODE) {
    define('INICIS_JS_URL', 'https://stgstdpay.inicis.com/stdjs/INIStdPay.js');
} else {
    define('INICIS_JS_URL', 'https://stdpay.inicis.com/stdjs/INIStdPay.js');
}

// ================================
// 로그 설정
// ================================
define('INICIS_LOG_ENABLED', true);
define('INICIS_LOG_DIR', __DIR__ . '/logs');

// 로그 디렉토리 생성
if (INICIS_LOG_ENABLED && !is_dir(INICIS_LOG_DIR)) {
    mkdir(INICIS_LOG_DIR, 0755, true);
}

// ================================
// DB 연결
// ================================
require_once __DIR__ . '/../db.php';

// ================================
// 세션 시작
// ================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================================
// 유틸리티 함수
// ================================

/**
 * 로그 기록
 */
function inicis_log($message, $type = 'info') {
    if (!INICIS_LOG_ENABLED) return;

    $log_file = INICIS_LOG_DIR . '/inicis_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] [{$type}] {$message}\n";

    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * 주문번호 생성 (MID_타임스탬프_랜덤)
 */
function generate_order_id() {
    return INICIS_MID . '_' . date('YmdHis') . '_' . sprintf('%04d', mt_rand(0, 9999));
}

/**
 * 금액 포맷
 */
function format_price($amount) {
    return number_format((int)$amount);
}

/**
 * 상품명 정리 (특수문자 제거)
 */
function sanitize_goods_name($name) {
    $name = preg_replace('/[^\w\sㄱ-힣]/u', '', $name);
    if (mb_strlen($name) > 50) {
        $name = mb_substr($name, 0, 47) . '...';
    }
    return $name ?: '인쇄물';
}

/**
 * 구매자명 정리
 */
function sanitize_buyer_name($name) {
    $name = preg_replace('/[^\w\sㄱ-힣]/u', '', $name);
    if (mb_strlen($name) > 30) {
        $name = mb_substr($name, 0, 30);
    }
    return $name ?: '고객';
}

/**
 * 전화번호 정리 (숫자만)
 */
function sanitize_phone($phone) {
    return preg_replace('/[^0-9]/', '', $phone) ?: '01000000000';
}

inicis_log('설정 파일 로드 완료 (모드: ' . (INICIS_TEST_MODE ? '테스트' : '운영') . ', 도메인: ' . SITE_DOMAIN . ')');
