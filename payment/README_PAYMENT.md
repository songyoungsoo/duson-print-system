# KG이니시스 결제 시스템 설정 가이드

## 📋 현재 설정 정보

### 가맹점 정보
- **가맹점 ID (MID)**: `dsp1147479`
- **Sign Key**: `cEdnbCtISFZ1QUNpNm5hbG1JY1RlQT09`
- **도메인**: `https://dsp114.co.kr`

### 운영 모드 상태
- **현재 모드**: 🟢 **운영 모드** (실제 결제 활성화)
- **운영 서버**: `https://stdpay.inicis.com`

---

## 🔐 운영 모드로 전환하기 (실제 결제 활성화)

### ⚠️ 주의사항
운영 모드로 전환하면 **실제 카드 결제가 진행됩니다!**
- 개발 및 테스트가 완료된 후에만 전환하세요
- 반드시 운영 서버(dsp114.co.kr)에서만 전환하세요
- localhost에서는 절대 운영 모드로 전환하지 마세요

### 전환 방법

**1단계**: `/var/www/html/payment/inicis_config.php` 파일 수정

```php
// 24번 라인 찾기
define('INICIS_TEST_MODE', true);

// 아래와 같이 변경
define('INICIS_TEST_MODE', false);  // ← true를 false로 변경
```

**2단계**: 설정 확인

```bash
# 로그 확인
tail -f /var/www/html/payment/logs/inicis_*.log
```

로그에서 다음과 같이 표시되어야 합니다:
```
[2026-01-29 XX:XX:XX] [info] KG이니시스 설정 로드 완료 (모드: 운영, ...)
```

**3단계**: 테스트 결제 진행

- ⚠️ **소액(100원~1,000원)으로 테스트하세요**
- 실제 카드로 결제 후 즉시 취소 처리
- 결제 승인 번호 확인
- 데이터베이스에 결제 정보 저장 확인

---

## 🧪 테스트 모드 정보

### 테스트 카드 번호

| 카드사 | 카드번호 | 유효기간 | CVC |
|--------|---------|---------|-----|
| 신한카드 | 9410-1234-5678-1234 | 임의 (미래) | 123 |
| 국민카드 | 9430-1234-5678-1234 | 임의 (미래) | 123 |
| 삼성카드 | 9435-1234-5678-1234 | 임의 (미래) | 123 |

- **비밀번호**: 아무 숫자 (예: 00)
- **생년월일**: 아무 날짜 (예: 900101)

### 테스트 결과
- 승인은 되지만 **실제 청구는 안됨**
- 결제 결과는 정상적으로 반환됨
- 데이터베이스 저장 로직 테스트 가능

---

## 📂 파일 구조

```
/var/www/html/payment/
├── inicis_config.php      # 메인 설정 파일 (환경별 자동 감지)
├── config.php             # 레거시 설정 파일
├── inicis_request.php     # 결제 요청 페이지
├── inicis_return.php      # 결제 완료 콜백 (팝업 닫기 + 부모창 리다이렉트)
├── inicis_close.php       # 결제창 닫기 콜백 (팝업 닫기 + 부모창 리다이렉트)
├── success.php            # 결제 성공 페이지
├── kakaopay_*.php         # 카카오페이 관련
├── naverpay_*.php         # 네이버페이 관련
└── logs/                  # 결제 로그 디렉토리
    └── inicis_YYYY-MM-DD.log
```

---

## 🔄 결제 흐름

### 정상 결제 흐름
```
1. inicis_request.php (결제 요청)
   ↓
2. 이니시스 결제창 (팝업)
   ↓
3. inicis_return.php (결제 결과 처리)
   ↓ 팝업 자동 닫힘 + 부모창 리다이렉트
4. success.php (결제 완료 페이지)
```

### 결제 취소/닫기 흐름
```
1. inicis_request.php (결제 요청)
   ↓
2. 이니시스 결제창 (팝업)
   ↓ 사용자가 X 버튼 또는 취소
3. inicis_close.php (취소 처리)
   ↓ 팝업 자동 닫힘 + 부모창 리다이렉트
4. OrderComplete_universal.php?payment=cancelled
```

### 팝업/iframe 처리 로직
`inicis_return.php`와 `inicis_close.php`는 다음 순서로 부모 창을 찾아 리다이렉트:
1. `window.opener` (팝업인 경우) → 부모 창 리다이렉트 후 팝업 닫기
2. `window.parent` (iframe인 경우) → 부모 프레임 리다이렉트
3. 직접 `window.location` (일반 페이지인 경우)

---

## 🔍 환경별 자동 감지

### Localhost (개발)
```php
INICIS_RETURN_URL = "http://localhost/payment/inicis_return.php"
INICIS_CLOSE_URL  = "http://localhost/payment/inicis_close.php"
```

### 운영 서버 (dsp114.co.kr)
```php
INICIS_RETURN_URL = "https://dsp114.co.kr/payment/inicis_return.php"
INICIS_CLOSE_URL  = "https://dsp114.co.kr/payment/inicis_close.php"
```

---

## 📞 문의

- **KG이니시스 고객센터**: 1588-4954
- **기술지원**: 평일 09:00~18:00
- **가맹점관리자**: https://iniweb.inicis.com

---

**작성일**: 2026-01-29  
**최종 수정**: 2026-02-05 (팝업 닫기 로직 추가)  
**버전**: 1.1  
**담당**: 두손기획인쇄 개발팀
