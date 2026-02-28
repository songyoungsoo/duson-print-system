## 💳 Payment System (KG이니시스)

### Configuration Files
- `payment/inicis_config.php` - Main configuration (environment auto-detection)
- `payment/config.php` - Legacy configuration (backwards compatibility)
- `payment/README_PAYMENT.md` - Complete setup guide

### Production Settings
- **Merchant ID**: `dsp1147479`
- **Domain**: `https://dsp114.com`
- **Test Mode**: Controlled via `INICIS_TEST_MODE` constant
- **Environment Detection**: Automatic localhost/production URL switching

### Critical Rules

#### 1. Test Mode vs Production Mode
```php
// ⚠️ NEVER enable production mode on localhost
define('INICIS_TEST_MODE', false);  // Only on dsp114.com

// ✅ ALWAYS use test mode locally
define('INICIS_TEST_MODE', true);   // localhost default
```

#### 2. Environment URL Auto-Detection
```php
// ✅ CORRECT: Auto-detection based on SERVER_NAME
if (strpos($_SERVER['SERVER_NAME'], 'dsp114.com') !== false) {
    $returnUrl = "https://dsp114.com/payment/inicis_return.php";
} else {
    $returnUrl = "http://localhost/payment/inicis_return.php";
}

// ❌ NEVER: Hardcode production URLs in localhost
$returnUrl = "https://dsp114.com/payment/inicis_return.php";  // WRONG!
```

#### 3. Production Deployment Checklist
- [ ] Set `INICIS_TEST_MODE = false` on production only
- [ ] Verify `dsp114.com` domain in `config.env.php`
- [ ] Test with small amount (100-1,000원) first
- [ ] Check logs in `/var/www/html/payment/logs/`
- [ ] Verify database `order_payment_log` table updates

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
3-a. 결제 완료 → inicis_return.php → 팝업 닫기 + 부모창 OrderComplete로 이동
3-b. 결제 취소 → inicis_close.php → 팝업 닫기 + 부모창 OrderComplete로 이동
```

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

