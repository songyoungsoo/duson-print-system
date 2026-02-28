<?php
/**
 * [LEGACY] 결제 설정 파일 — inicis_config.php로 통합됨
 * 
 * 이 파일은 하위 호환성을 위해 inicis_config.php를 로드합니다.
 * 새 코드에서는 직접 inicis_config.php를 사용하세요.
 * 
 * ⚠️ 기존 config.php는 INICIS_TEST_MODE=true (테스트 모드)였으나,
 *    inicis_config.php는 INICIS_TEST_MODE=false (운영 모드)입니다.
 *    이 래퍼는 운영 설정을 사용합니다.
 */

// inicis_config.php가 모든 설정을 제공 (운영 모드, MID, SIGNKEY, URL, 함수 등)
if (!defined('INICIS_TEST_MODE')) {
    require_once __DIR__ . '/inicis_config.php';
}

// 하위 호환 함수 매핑 (레거시 코드에서 사용하던 함수명)
if (!function_exists('inicis_log')) {
    function inicis_log($message, $type = 'info') {
        if (function_exists('logInicisTransaction')) {
            logInicisTransaction($message, $type);
        }
    }
}

if (!function_exists('generate_order_id')) {
    function generate_order_id() {
        return function_exists('generateInicisOrderId') ? generateInicisOrderId() : date('YmdHis') . sprintf('%04d', mt_rand(0, 9999));
    }
}

if (!function_exists('format_price')) {
    function format_price($amount) {
        return function_exists('formatInicisAmount') ? formatInicisAmount($amount) : number_format((int)$amount);
    }
}

if (!function_exists('sanitize_goods_name')) {
    function sanitize_goods_name($name) {
        return function_exists('sanitizeGoodsName') ? sanitizeGoodsName($name) : $name;
    }
}

if (!function_exists('sanitize_buyer_name')) {
    function sanitize_buyer_name($name) {
        return function_exists('sanitizeBuyerName') ? sanitizeBuyerName($name) : $name;
    }
}

if (!function_exists('sanitize_phone')) {
    function sanitize_phone($phone) {
        return function_exists('sanitizePhone') ? sanitizePhone($phone) : preg_replace('/[^0-9]/', '', $phone);
    }
}

// SITE_DOMAIN 하위 호환 (레거시 코드에서 사용)
if (!defined('SITE_DOMAIN')) {
    $server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
    if (strpos($server_name, 'dsp114.com') !== false) {
        define('SITE_DOMAIN', 'https://dsp114.com');
    } elseif (strpos($server_name, 'localhost') !== false || strpos($server_name, '127.0.0.1') !== false) {
        define('SITE_DOMAIN', 'http://localhost');
    } else {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        define('SITE_DOMAIN', $protocol . $server_name);
    }
}
