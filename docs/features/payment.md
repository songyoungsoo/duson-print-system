## 💳 Payment System (KG이니시스)

### Configuration Files
- `payment/inicis_config.php` - Main configuration (environment auto-detection)
- `payment/inicis_config.production.php` - Production overrides (IP whitelist, Sign Key)
- `payment/config.php` - Legacy configuration (backwards compatibility)
- `payment/README_PAYMENT.md` - Complete setup guide

### Production Settings
- **Merchant ID**: `dsp1147479`
- **Domain**: `https://dsp114.com` (주) / `https://dsp114.co.kr` (보조) — SITE_URL 자동 감지
- **Sign Key**: `YXgxUnVtVlNvZndWUWg4RWVFUGZwUT09` (2026-02-28 변경, 두 도메인 공용)
- **Test Mode**: Controlled via `INICIS_TEST_MODE` constant
- **Environment Detection**: `config.env.php` SITE_URL 기반 자동 전환 (듀얼 도메인 대응)

### Critical Rules

#### 1. Test Mode vs Production Mode
```php
// ⚠️ NEVER enable production mode on localhost
define('INICIS_TEST_MODE', false);  // Only on production (dsp114.com / dsp114.co.kr)

// ✅ ALWAYS use test mode locally
define('INICIS_TEST_MODE', true);   // localhost default
```

#### 2. Environment URL Auto-Detection (듀얼 도메인 대응)
```php
// ✅ CORRECT: SITE_URL 기반 자동 전환 (dsp114.com, dsp114.co.kr 모두 대응)
if (EnvironmentDetector::isProduction()) {
    $returnUrl = SITE_URL . "/payment/inicis_return.php";
    // dsp114.com 접속 → https://dsp114.com/payment/inicis_return.php
    // dsp114.co.kr 접속 → https://dsp114.co.kr/payment/inicis_return.php
} else {
    $returnUrl = "http://localhost/payment/inicis_return.php";
}

// ❌ NEVER: Hardcode production URLs
$returnUrl = "https://dsp114.com/payment/inicis_return.php";  // WRONG!
```

#### 3. Production Deployment Checklist
- [ ] Set `INICIS_TEST_MODE = false` on production only
- [ ] Verify `config.env.php` domain detection (dsp114.com + dsp114.co.kr)
- [ ] Test with small amount (100-1,000원) — **두 도메인 모두에서 테스트**
- [ ] Check logs in `/var/www/html/payment/logs/`
- [ ] Verify database `payment_inicis` table updates
- [ ] Verify returnUrl matches the access domain

### Test Card Numbers (Test Mode Only)
| Bank | Card Number | Expiry | CVC |
|------|-------------|--------|-----|
| 신한 | 9410-1234-5678-1234 | Any future | 123 |
| 국민 | 9430-1234-5678-1234 | Any future | 123 |
| 삼성 | 9435-1234-5678-1234 | Any future | 123 |

### UI/UX Features
- **Payment Warning Modal**: Reminds users to confirm shipping/design before payment
- **Contact Emphasis**: Phone number (02-2632-1830) prominently displayed
- **Clean Interface**: Payment method icons removed for simplicity

### Payment Flow (Popup Handling)
```
1. inicis_request.php → 결제 요청
2. 이니시스 결제창 (팝업)
3-a. 결제 완료 → inicis_return.php → 팝업 닫기 + 부모창 OrderComplete_universal.php로 이동
3-b. 결제 취소 → inicis_close.php → 팝업 닫기 + 부모창 OrderComplete_universal.php로 이동 (payment=cancelled)
```

### IP Whitelist (inicis_config.production.php)
이니시스 서버에서 `inicis_return.php`로 콜백할 때 IP 검증을 수행합니다.

```php
// 허용된 이니시스 서버 IP
define('INICIS_IP_WHITELIST', [
    '127.0.0.1', 'localhost',
    '211.219.96.165',   // 이니시스 표준
    '118.129.210.25',   // 이니시스 표준
    '222.108.84.120',   // 이니시스 표준결제 콜백 (2026-02-28 추가)
]);
```

**⚠️ 결제 후 "Access Denied" 에러 발생 시**: `inicis_return.php` 195행의 `validateInicisIP()` 확인 → 로그에서 차단된 IP 확인 → 화이트리스트에 추가

#### Popup Close Logic (inicis_return.php, inicis_close.php)
```javascript
// 팝업/iframe 자동 감지 및 부모 창 리다이렉트
if (window.opener && !window.opener.closed) {
    window.opener.location.href = redirectUrl;
    window.close();
} else if (window.parent && window.parent !== window) {
    window.parent.location.href = redirectUrl;
} else {
    window.location.href = redirectUrl;
}
```

### Admin Notification (카드결제 관리자 알림)

카드결제 완료 시 관리자에게 자동 이메일 알림이 발송됩니다.

**구현 위치**: `payment/inicis_return.php`

**알림 수신자**: `dsp1830@naver.com`

**이메일 내용**:
- 주문번호, 결제금액, 결제수단
- 거래번호(TID), 주문자명, 연락처
- 결제시각
- 관리자 페이지 바로가기 링크

**발송 조건**:
- 결제 성공 (resultCode = '0000' 또는 '00')
- 주문 상태 업데이트 성공 후

```php
// mailer() 함수 사용 예시
$mail_result = mailer(
    '두손기획인쇄',           // 발신자명
    'dsp1830@naver.com',      // 발신 이메일
    $admin_email,              // 수신 이메일
    $admin_subject,            // 제목
    $admin_body,               // 본문 (HTML)
    1,                         // 타입: 1=HTML
    ""                         // 첨부파일: 없음 (빈 문자열 필수!)
);
```

