<?php
/**
 * KG이니시스 결제 설정 파일
 * 두손기획인쇄 - 결제 시스템
 *
 * @package DusonPrinting
 * @version 1.0
 */

// ================================
// KG이니시스 기본 설정
// ================================

// 테스트/운영 모드 설정
define('INICIS_TEST_MODE', true); // true: 테스트 모드, false: 운영 모드

// 상점 아이디 (MID)
if (INICIS_TEST_MODE) {
    define('INICIS_MID', 'INIpayTest'); // 테스트용 MID
} else {
    define('INICIS_MID', 'YOUR_REAL_MID'); // 실제 가맹점 MID로 변경 필요
}

// 상점 키 (Signkey)
if (INICIS_TEST_MODE) {
    define('INICIS_SIGNKEY', 'SU5JTElURV9UUklQTEVERVNfS0VZU1RS'); // 테스트용 Signkey
} else {
    define('INICIS_SIGNKEY', 'YOUR_REAL_SIGNKEY'); // 실제 Signkey로 변경 필요
}

// API URL 설정
if (INICIS_TEST_MODE) {
    define('INICIS_STD_URL', 'https://stgstdpay.inicis.com/stdjs/INIStdPay.js'); // 표준결제 테스트 URL
    define('INICIS_RETURN_URL', 'http://localhost/payment/inicis_return.php'); // 결제 결과 수신 URL
    define('INICIS_CLOSE_URL', 'http://localhost/payment/inicis_close.php'); // 결제창 닫기 URL
} else {
    define('INICIS_STD_URL', 'https://stdpay.inicis.com/stdjs/INIStdPay.js'); // 표준결제 운영 URL
    define('INICIS_RETURN_URL', 'https://www.dsp1830.shop/payment/inicis_return.php'); // 실제 도메인으로 변경
    define('INICIS_CLOSE_URL', 'https://www.dsp1830.shop/payment/inicis_close.php'); // 실제 도메인으로 변경
}

// ================================
// 결제 옵션 설정
// ================================

// 결제 수단 설정
define('INICIS_PAYMENT_METHODS', 'Card:HPP:Bank'); // Card:신용카드, HPP:휴대폰, Bank:계좌이체

// 환불 계좌 입력 여부 (가상계좌 사용 시)
define('INICIS_VBANK_REFUND', 'Y'); // Y: 입력, N: 입력안함

// 결제창 언어 설정
define('INICIS_LANGUAGE', 'ko'); // ko: 한국어, en: 영어

// 결제창 화면 설정
define('INICIS_PAYMENT_POPUP', 1); // 0: 페이지 이동, 1: 팝업

// ================================
// 보안 설정
// ================================

// IP 화이트리스트 (운영 시 설정 권장)
define('INICIS_IP_WHITELIST', [
    '127.0.0.1',
    'localhost',
    // KG이니시스 서버 IP 추가
    '211.219.96.165',
    '118.129.210.25'
]);

// 해시 알고리즘
define('INICIS_HASH_ALGO', 'sha256');

// 로그 저장 여부
define('INICIS_ENABLE_LOG', true);

// 로그 디렉토리
define('INICIS_LOG_DIR', __DIR__ . '/logs');

// ================================
// 상품 설정
// ================================

// 상품명 최대 길이
define('INICIS_GOODS_NAME_MAX', 50);

// 구매자명 최대 길이
define('INICIS_BUYER_NAME_MAX', 30);

// ================================
// 헬퍼 함수
// ================================

/**
 * 주문번호 생성
 * 형식: 날짜(YYYYMMDD) + 시간(HHmmss) + 랜덤(4자리)
 *
 * @return string 주문번호
 */
function generateInicisOrderId() {
    return date('YmdHis') . sprintf('%04d', mt_rand(0, 9999));
}

/**
 * 타임스탬프 생성
 * 형식: YYYYMMDDHHmmss
 *
 * @return string 타임스탬프
 */
function getInicisTimestamp() {
    return date('YmdHis');
}

/**
 * 해시 생성 (서명)
 *
 * @param string $oid 주문번호
 * @param string $price 금액
 * @param string $timestamp 타임스탬프
 * @return string 해시값
 */
function generateInicisSignature($oid, $price, $timestamp) {
    $data = INICIS_SIGNKEY . $oid . $price . $timestamp;
    return hash(INICIS_HASH_ALGO, $data);
}

/**
 * 로그 기록
 *
 * @param string $message 로그 메시지
 * @param string $type 로그 타입 (request, response, error)
 */
function logInicisTransaction($message, $type = 'info') {
    if (!INICIS_ENABLE_LOG) {
        return;
    }

    // 로그 디렉토리 생성
    if (!is_dir(INICIS_LOG_DIR)) {
        mkdir(INICIS_LOG_DIR, 0755, true);
    }

    $log_file = INICIS_LOG_DIR . '/inicis_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');

    $log_message = "[{$timestamp}] [{$type}] {$message}\n";

    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * IP 검증
 *
 * @param string $ip IP 주소
 * @return bool 허용 여부
 */
function validateInicisIP($ip) {
    if (INICIS_TEST_MODE) {
        return true; // 테스트 모드에서는 모든 IP 허용
    }

    return in_array($ip, INICIS_IP_WHITELIST);
}

/**
 * 금액 포맷
 *
 * @param int|string $amount 금액
 * @return string 포맷된 금액
 */
function formatInicisAmount($amount) {
    return number_format((int)$amount);
}

/**
 * 상품명 정리 (특수문자 제거)
 *
 * @param string $goods_name 상품명
 * @return string 정리된 상품명
 */
function sanitizeGoodsName($goods_name) {
    // 특수문자 제거 (이니시스는 일부 특수문자 제한)
    $goods_name = preg_replace('/[^\w\sㄱ-힣]/u', '', $goods_name);

    // 길이 제한
    if (mb_strlen($goods_name) > INICIS_GOODS_NAME_MAX) {
        $goods_name = mb_substr($goods_name, 0, INICIS_GOODS_NAME_MAX) . '...';
    }

    return $goods_name;
}

/**
 * 구매자명 정리
 *
 * @param string $buyer_name 구매자명
 * @return string 정리된 구매자명
 */
function sanitizeBuyerName($buyer_name) {
    // 특수문자 제거
    $buyer_name = preg_replace('/[^\w\sㄱ-힣]/u', '', $buyer_name);

    // 길이 제한
    if (mb_strlen($buyer_name) > INICIS_BUYER_NAME_MAX) {
        $buyer_name = mb_substr($buyer_name, 0, INICIS_BUYER_NAME_MAX);
    }

    return $buyer_name;
}

/**
 * 전화번호 포맷 (하이픈 제거)
 *
 * @param string $phone 전화번호
 * @return string 정리된 전화번호
 */
function sanitizePhone($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}

/**
 * 이메일 검증
 *
 * @param string $email 이메일
 * @return bool 유효 여부
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ================================
// 에러 코드 정의
// ================================

define('INICIS_ERROR_CODES', [
    '00' => '정상 처리',
    '01' => '결제 취소',
    '02' => '거래 시간 초과',
    '03' => '카드 정보 오류',
    '04' => '한도 초과',
    '05' => '잔액 부족',
    '06' => '중복 거래',
    '07' => '서명 검증 실패',
    '08' => 'IP 제한',
    '09' => '시스템 오류',
    '99' => '알 수 없는 오류'
]);

/**
 * 에러 메시지 가져오기
 *
 * @param string $code 에러 코드
 * @return string 에러 메시지
 */
function getInicisErrorMessage($code) {
    return INICIS_ERROR_CODES[$code] ?? INICIS_ERROR_CODES['99'];
}

// ================================
// 초기화
// ================================

// 세션 시작 (아직 시작되지 않았다면)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 로그 디렉토리 생성
if (INICIS_ENABLE_LOG && !is_dir(INICIS_LOG_DIR)) {
    mkdir(INICIS_LOG_DIR, 0755, true);
}

// 초기화 로그
logInicisTransaction('KG이니시스 설정 로드 완료 (모드: ' . (INICIS_TEST_MODE ? '테스트' : '운영') . ')', 'info');
