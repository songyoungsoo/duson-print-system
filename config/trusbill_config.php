<?php
/**
 * 트러스빌 전자세금계산서 API 설정
 * 경로: /config/trusbill_config.php
 */

// 트러스빌 API 설정
define('TRUSBILL_API_KEY', ''); // TODO: 트러스빌에서 발급받은 API 키 입력
define('TRUSBILL_API_SECRET', ''); // TODO: 트러스빌에서 발급받은 API Secret 입력
define('TRUSBILL_API_URL', 'https://api.trusbill.com/v1'); // 운영 환경
define('TRUSBILL_TEST_MODE', true); // true: 테스트 환경, false: 운영 환경

// 테스트 환경 URL
if (TRUSBILL_TEST_MODE) {
    define('TRUSBILL_API_ENDPOINT', 'https://sandbox-api.trusbill.com/v1');
} else {
    define('TRUSBILL_API_ENDPOINT', TRUSBILL_API_URL);
}

// 공급자(발행자) 정보 - 두손기획인쇄
// SSOT: /mlangprintauto/includes/company_info.php
define('SUPPLIER_BUSINESS_NUMBER', '1070645106'); // 사업자등록번호 (하이픈 제거) — company_info.php: 107-06-45106
define('SUPPLIER_COMPANY_NAME', '두손기획인쇄');
define('SUPPLIER_CEO_NAME', '차경선');
define('SUPPLIER_ADDRESS', '서울 영등포구 영등포로 36길 9 송호빌딩 1층');
define('SUPPLIER_BUSINESS_TYPE', '제조·도매'); // 업태 — company_info.php SSOT
define('SUPPLIER_BUSINESS_ITEM', '인쇄업·광고물'); // 종목 — company_info.php SSOT
define('SUPPLIER_EMAIL', 'dsp1830@naver.com');
define('SUPPLIER_PHONE', '02-2632-1830');
define('SUPPLIER_CONTACT_NAME', '차경선'); // 대표자 겸 담당자

// 로그 설정
define('TRUSBILL_LOG_ENABLED', true);
define('TRUSBILL_LOG_PATH', __DIR__ . '/../logs/trusbill/');

// 로그 디렉토리 생성
if (TRUSBILL_LOG_ENABLED && !file_exists(TRUSBILL_LOG_PATH)) {
    mkdir(TRUSBILL_LOG_PATH, 0755, true);
}
