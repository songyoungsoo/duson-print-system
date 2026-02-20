# AGENTS.md - Duson Planning Print System

## 🚨 CRITICAL - PRODUCTION SERVER INFO (배포 필수 확인!)

**⚠️ 운영 서버 FTP/웹 루트 구조 - 배포 시 반드시 확인!**

**서버 변경 내역:**
- ❌ 구 서버: `dsp1830.shop` (마이그레이션 완료, 더 이상 사용 안 함)
- ✅ 현재 운영: `dsp114.co.kr` (2026년 2월 현재)

```
FTP 접속 정보 (dsp114.co.kr):
├─ Host: dsp114.co.kr
├─ User: dsp1830
├─ Pass: cH*j@yzj093BeTtc
└─ Protocol: FTP (plain, port 21)

FTP 서버: ProFTPD 사용

FTP 디렉토리 구조:
/ (FTP 루트)
├─ httpdocs/          ← ✅ 실제 웹 루트 (https://dsp114.co.kr/)
│  ├─ index.php       ← 메인 페이지
│  ├─ payment/        ← 결제 시스템
│  ├─ mlangprintauto/ ← 제품 페이지
│  ├─ includes/
│  └─ ...
├─ public_html/       ← ❌ 웹 루트 아님! (별도 디렉토리)
├─ logs/              ← 서버 로그
└─ error_docs/        ← 에러 문서

🎯 배포 시 업로드 경로:
✅ 올바름: /httpdocs/payment/inicis_return.php
❌ 틀림:   /payment/inicis_return.php
❌ 틀림:   /public_html/payment/inicis_return.php
```

**배포 전 체크리스트:**
- [ ] 업로드 경로가 `/httpdocs/`로 시작하는가?
- [ ] curl 또는 FTP 클라이언트에서 경로 확인했는가?
- [ ] 업로드 후 https://dsp114.co.kr/ 에서 동작 확인했는가?

---

## 🏢 Plesk의 특징 및 .htaccess 호환성

**Plesk 배포판 특징:**
- 웹 UI로 도메인, SSL, DB, FTP 등을 관리
- nginx + Apache 조합을 기본 구성으로 사용 (이게 .htaccess 충돌 원인)
- FTP 서버로 ProFTPD 사용
- 고객용 웹사이트와 관리자용 웹사이트 분리 배포 지원

**.htaccess 호환성 문제 (2026-02-07 발견):**

```apache
# ❌ Plesk Apache 2.4 호환되지 않는 구문 (500 에러 유발)
Options +Indexes
Order allow,deny
Allow from all

# ✅ Plesk Apache 2.4 호환 구문
<Directory>
    Require all granted
</Directory>

# ✅ Plesk Apache 2.4 + nginx 프록시 사용 시
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.php [L]
</IfModule>
```

**기존 .htaccess 삭제 후 이미지 정상 작동 (2026-02-07):**
- 위치: `/mlangorder_printauto/upload/.htaccess`
- 원인: Apache 2.2 구문 사용 (mod_access_compat 미설치)
- 해결: 파일 삭제 후 자동으로 nginx가 처리

**✅ .htaccess 재작성 시 주의사항:**

1. **nginx + Apache 조합 인지**:
   - nginx는 클라이언트에 직접 응답, Apache로 프록시
   - .htaccess는 Apache 2.4 구문만 사용
   - nginx 설정과 충돌 방지

2. **AllowOverride 제한**: Plesk에서 AllowOverride 설정 필요
   - `/httpdocs`에는 허용
   - `/admin`, `/sub` 등에는 제한

3. **ProFTPD FTP 서버와 호환성**: SSL, 권한, chroot 설정 확인

**⚠️ 중요 안내:**
- Plesk 환경에서 .htaccess는 **신중하게 작성** 필요
- Apache 2.2 구문 사용 시 500 에러 발생 가능
- 삭제 후 정상 작동 → 신중하게 복구 필요

```

**Plesk 관리 패널 (서버 관리):**
```
Plesk 접속 정보:
├─ URL: https://cmshom.co.kr:8443/login_up.php
├─ 아이디: 두손기획
├─ 비밀번호: h%42D9u2m
├─ 용도: 서버 관리, phpMyAdmin, SSL, 도메인 설정
└─ phpMyAdmin: Plesk → 데이터베이스 → phpMyAdmin 접속
```

```
NAS 접속 정보 (백업 서버):
┌──────────────────────────────────────────────────────────────┐
│  🏠 1차 NAS: dsp1830.ipdisk.co.kr:8000                       │
│     ├─ User: admin                                           │
│     ├─ Pass: 1830                                            │
│     └─ 용도: 전체 데이터 백업 (마이그레이션)                   │
├──────────────────────────────────────────────────────────────┤
│  🏠 2차 NAS: sknas205.ipdisk.co.kr                           │
│     ├─ User: sknas205                                        │
│     ├─ Pass: sknas205204203                                  │
│     └─ 용도: 추가 백업                                        │
└──────────────────────────────────────────────────────────────┘
```

---

## 💾 NAS 백업 서버 INFO (자동 동기화)

**⚠️ NAS FTP 구조 - Git 변경사항 자동 백업**

```
NAS 접속 정보 (dsp1830.ipdisk.co.kr):
├─ Host: dsp1830.ipdisk.co.kr
├─ User: admin
├─ Pass: 1830
├─ Port: 21
└─ Protocol: FTP (plain)

NAS 디렉토리 구조:
/HDD2/share/              ← NAS 백업 루트
├─ mlangprintauto/        ← 제품 페이지 백업
├─ payment/               ← 결제 시스템 백업
├─ includes/              ← 공통 컴포넌트 백업
├─ AGENTS.md              ← 시스템 문서 백업
└─ ...                    ← Git 추적 파일 전체

🎯 NAS 동기화 방법:
# 마지막 커밋 변경 파일만 동기화
./scripts/sync_to_nas.sh

# 특정 커밋 이후 변경사항 동기화
./scripts/sync_to_nas.sh HEAD~3

# 미리보기 (실제 업로드 없음)
./scripts/sync_to_nas.sh --dry-run

# 특정 파일만 업로드
./scripts/sync_to_nas.sh --file payment/inicis_return.php
```

**NAS 동기화 체크리스트:**
- [ ] Git 커밋 완료 후 실행하는가?
- [ ] 프로덕션 배포 전/후에 NAS 백업했는가?
- [ ] 동기화 로그에 실패한 파일이 없는가?

---

## 🏗️ System Overview

**Duson Planning Print System (두손기획인쇄)** - PHP 7.4 기반 인쇄 주문 관리 시스템
- **Backend**: PHP 7.4+ with MySQL 5.7+
- **Frontend**: Mixed (PHP templates + modern JavaScript)
- **Testing**: Playwright (E2E) + Python test utilities
- **Local Document Root**: `/var/www/html` (개발 환경)
- **Production Web Root**: `/httpdocs/` (운영 서버 FTP 기준)
- **Environment**: Multi-environment (localhost/staging/production)

## 🚀 Build, Test & Development Commands

### Environment Setup
```bash
# Start servers (WSL2 Ubuntu)
sudo service apache2 start
sudo service mysql start

# Verify installation
http://localhost/
```

### Playwright Testing
```bash
# Install dependencies
npm install

# Run all tests
npx playwright test

# Run specific test groups (parallel optimized)
npx playwright test --project="group-a-readonly"     # Read-only tests (max parallel)
npx playwright test --project="group-b-calculation" # Price calculation tests
npx playwright test --project="group-c-features"    # Single feature tests
npx playwright test --project="group-d-e2e"        # E2E flows (limited parallel)
npx playwright test --project="group-e-admin"       # Admin functions (sequential)

# Run single test file
npx playwright test tests/tier-1-readonly/page-loading.tier-1.spec.ts

# Debug mode
npx playwright test --debug

# Generate reports
npx playwright test --reporter=html
```

### Production Deployment
```bash
# FTP deployment to production server
./scripts/deploy_to_production.sh

# Verify all products have correct CSS
./scripts/verify_all_products.sh

# Sync image folders
./scripts/sync_imgfolder.sh
```

### Database Operations
```bash
# Fetch production schema
./scripts/fetch_production_schema.sh

# Verify all products
./scripts/verify_all_products.sh
```

## 🎯 Code Style Guidelines

### PHP Standards

#### 1. File Naming & Structure
- **All lowercase**: `cateadmin_title.php` (NOT `CateAdmin_title.php`)
- **Table names**: Always lowercase (`mlangprintauto_namecard`)
- **Includes**: Use lowercase paths (Linux case-sensitive)
- **No symlinks**: Use actual directories only

#### 2. Database Operations (CRITICAL)

**bind_param Validation Rule (3-Step Verification)**:
```php
// ❌ NEVER: Count by sight
mysqli_stmt_bind_param($stmt, "issss...", ...);

// ✅ ALWAYS: 3-step verification
$placeholder_count = substr_count($query, '?');  // 1
$type_count = strlen($type_string);             // 2
$var_count = 7; // Manual count                    // 3

if ($placeholder_count === $type_count && $type_count === $var_count) {
    mysqli_stmt_bind_param($stmt, $type_string, ...);
}
```

**Database Connection**:
- Connection variable: `$db` (legacy alias: `$conn = $db`)
- Character set: utf8mb4
- Environment auto-detection via `config.env.php`

#### 3. Quantity Display Handling (MANDATORY)

```php
// ❌ NEVER: Use quantity_display without unit validation
$line2 = implode(' / ', [$spec_sides, $item['quantity_display'], $spec_design]);

// ✅ ALWAYS: Validate unit, fallback to formatQuantity()
$quantity_display = $item['quantity_display'] ?? '';

// Unit validation: 매, 연, 부, 권, 개, 장
if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $quantity_display = $this->formatQuantity($item);
}

$line2 = implode(' / ', [$spec_sides, $quantity_display, $spec_design]);
```

#### 4. Unit Code vs Product Type (CRITICAL DISTINCTION)

```php
// ❌ NEVER: Use getUnitCode() with product_type (BUG!)
$unitCode = QuantityFormatter::getUnitCode($productType);  // 'sticker' → 'E' (ERROR)

// ✅ ALWAYS: Use getProductUnitCode() for product types
$unitCode = QuantityFormatter::getProductUnitCode($productType);  // 'sticker' → 'S' (CORRECT)
```

#### 5. Error Handling
- Never suppress type errors with `as any`, `@ts-ignore`, `@ts-expect-error`
- Use proper exception handling for database operations
- Validate all user inputs before processing
- Use prepared statements exclusively for database queries

### CSS Standards

#### !important Usage PROHIBITED ⚠️
```css
/* ❌ NEVER: !important usage */
.product-nav {
    display: grid !important;  // ABSOLUTELY FORBIDDEN
}

/* ✅ ALWAYS: Use specificity hierarchy */
/* Level 1: Basic styles (1 class) */
.product-nav { display: flex; }

/* Level 2: Context/state (2 classes) */
.mobile-view .product-nav { display: grid; }

/* Level 3: Specific selectors (3+ classes or parent included) */
body.cart-page .mobile-view .product-nav { display: grid; }
```

**CSS Debugging Protocol**:
1. Diagnose "why it's not working" with dev tools first
2. Check container elements before content alignment
3. Verify margin, padding, width, display, position of parent
4. Only use !important after completing the above checklist

### JavaScript/TypeScript Standards

#### Playwright Test Organization
- **Group A**: Read-only tests (maximum parallelism)
- **Group B**: Price calculation tests (maximum parallelism)  
- **Group C**: Single feature tests (limited parallelism)
- **Group D**: E2E flows (resource-limited parallelism)
- **Group E**: Admin functions (sequential execution)

#### Test File Naming
- Format: `[functionality].[group/tier]-[level].spec.ts`
- Examples: `page-loading.group-a.spec.ts`, `price-calculation.tier-2.spec.ts`

## 📦 Product Type Mapping (9 Standard Products)

| # | Product Name | Folder Name (FORCED) | ❌ Forbidden Names | Unit |
|---|-------------|---------------------|------------------|-------|
| 1 | 전단지 | `inserted` | leaflet | 연 |
| 2 | 스티커 | `sticker_new` | sticker | 매 |
| 3 | 자석스티커 | `msticker` | - | 매 |
| 4 | 명함 | `namecard` | - | 매 |
| 5 | 봉투 | `envelope` | - | 매 |
| 6 | 포스터 | `littleprint` | poster | 매 |
| 7 | 상품권 | `merchandisebond` | giftcard | 매 |
| 8 | 카다록 | `cadarok` | catalog | 부 |
| 9 | NCR양식지 | `ncrflambeau` | form, ncr | 권 |

## 🔧 Critical SSOT (Single Source of Truth) Files

### Core Logic Files
- `includes/QuantityFormatter.php` - Quantity/unit formatting SSOT
- `includes/ProductSpecFormatter.php` - Product specification formatter
- `lib/core_print_logic.php` - Central logic facade

### Quote System Files  
- `mlangprintauto/quote/includes/QuoteManager.php` - Quote data management
- `mlangprintauto/quote/includes/QuoteTableRenderer.php` - Table rendering SSOT

### Legacy Detection Patterns
```php
// Detect stickers from legacy data (product_type empty)
if (empty($productType) && !empty($tempItem['jong']) && !empty($tempItem['garo'])) {
    $productType = 'sticker';
}

// Or detect from product_name
if (empty($productType) && stripos($productName, '스티커') !== false) {
    $productType = 'sticker';
}
```

## 💳 Payment System (KG이니시스)

### Configuration Files
- `payment/inicis_config.php` - Main configuration (environment auto-detection)
- `payment/config.php` - Legacy configuration (backwards compatibility)
- `payment/README_PAYMENT.md` - Complete setup guide

### Production Settings
- **Merchant ID**: `dsp1147479`
- **Domain**: `https://dsp114.co.kr`
- **Test Mode**: Controlled via `INICIS_TEST_MODE` constant
- **Environment Detection**: Automatic localhost/production URL switching

### Critical Rules

#### 1. Test Mode vs Production Mode
```php
// ⚠️ NEVER enable production mode on localhost
define('INICIS_TEST_MODE', false);  // Only on dsp114.co.kr

// ✅ ALWAYS use test mode locally
define('INICIS_TEST_MODE', true);   // localhost default
```

#### 2. Environment URL Auto-Detection
```php
// ✅ CORRECT: Auto-detection based on SERVER_NAME
if (strpos($_SERVER['SERVER_NAME'], 'dsp114.co.kr') !== false) {
    $returnUrl = "https://dsp114.co.kr/payment/inicis_return.php";
} else {
    $returnUrl = "http://localhost/payment/inicis_return.php";
}

// ❌ NEVER: Hardcode production URLs in localhost
$returnUrl = "https://dsp114.co.kr/payment/inicis_return.php";  // WRONG!
```

#### 3. Production Deployment Checklist
- [ ] Set `INICIS_TEST_MODE = false` on production only
- [ ] Verify `dsp114.co.kr` domain in `config.env.php`
- [ ] Test with small amount (100-1,000원) first
- [ ] Check logs in `/var/www/html/payment/logs/`
- [ ] Verify database `payment_inicis` table updates

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
3-a. 결제 완료 → inicis_return.php → 팝업 닫기 + 부모창 success.php로 이동
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

## 📦 배송 추정 시스템 (Shipping Calculator)

### 시스템 개요

택배 시 **무게만 추정** 표시하고, 박스수/택배비/송장번호는 **관리자가 직접 입력**하는 반수동 시스템.
실제 택배비는 관리자가 전화 확인 후 확정 (카드결제: 전화로 카드번호 받아 단말기 수기결제).

| 항목 | 값 |
|------|-----|
| **공통 모듈** | `includes/ShippingCalculator.php` |
| **AJAX API** | `includes/shipping_api.php` (estimate/rates/rates_save/order_estimate/logen_save) |
| **주문 페이지** | `mlangorder_printauto/OnlineOrder_unified.php` (고객용) |
| **관리자 OrderView** | `mlangorder_printauto/OrderFormOrderTree.php` (주문 상세) |
| **관리자 주문목록** | `admin/mlangprintauto/orderlist.php` (배송 모달) |
| **관리자 로젠** | `shop_admin/post_list74.php` (로젠택배 관리) |
| **DB 테이블** | `shipping_rates` (요금표), `mlangorder_printauto` (logen_* 컬럼) |

### 무게 계산 공식

```
용지무게(g) = 평량(gsm) × 절당면적(m²) × 매수
코팅가산: 유광/무광 ×1.04, 라미네이팅 ×1.12
총무게 = 종이무게 + 부자재 가산
```

**⚠️ 박스수는 추정하지 않음** — 회사마다 박스 규격이 달라 추정 불가, 관리자가 직접 입력.

### ShippingCalculator 메서드

| 메서드 | 용도 | 입력 |
|--------|------|------|
| `estimateFromCart($cartItems)` | 고객 주문 페이지 (AJAX) | 장바구니 배열 |
| `estimateFromOrder($orderData)` | 관리자 주문 상세/목록 | DB 주문 row |
| `loadRates($db)` | DB 요금표 로드 (캐싱) | DB 커넥션 |
| `getRatesForDisplay($db)` | 요금표 반환 | DB 커넥션 |

### DB 테이블: shipping_rates

```sql
CREATE TABLE shipping_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rate_group VARCHAR(50) NOT NULL,  -- 'logen_weight' 또는 'logen_16'
    label VARCHAR(100),
    max_kg DECIMAL(5,1) NOT NULL,
    fee INT NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- 초기 데이터: logen_weight (3kg/3000, 10kg/3500, 15kg/4000, 20kg/5000, 23kg/6000)
-- logen_16 (16절 고정 3500원)
```

### 관리자 화면 동작 (2026-02-16 통일)

**추정 영역** (자동 계산, 읽기 전용):
```
📦 배송 정보 [추정]
예상 무게: 약 12.7kg (부자재 포함)
※ 추정치이며 실제와 다를 수 있습니다.
```

**확정 영역** (관리자 수동 입력):
```
운임구분: [착불/선불] 선택
박스 수량: [ ] 직접 입력
택배비:   [ ] 직접 입력
송장번호: [ ] 직접 입력
💾 저장 → shipping_api.php?action=logen_save
```

**적용 위치**:
- `OrderFormOrderTree.php` — 주문 상세 페이지 (추정 무게 + 확정 입력 폼)
- `orderlist.php` — 주문 목록 배송 모달 (추정 무게 + 확정 입력 폼)

### 주문 페이지 동작 (고객용)

```
배송방법 "택배" 선택 → 운임구분(착불/선불) 라디오 표시
  ├─ 착불: 기본값, 추가 정보 없음
  └─ 선불: AJAX로 무게 추정 표시
      ├─ "⚠ 추정" 배지 + "실제 무게는 다를 수 있습니다"
      ├─ 추정 무게: 약 X.Xkg
      └─ 📞 02-2632-1830 전화 안내
```

### Critical Rules

1. ✅ **택배비(선불) 확정 시 합계금액에 합산 표시** — DB money_5는 수정하지 않고 화면 표시만 (인쇄 레이아웃 포함)
2. ❌ **품목 계산 코드와 얽히면 안 됨** — PriceCalculationService 수정 금지
3. ❌ **박스수/택배비 추정 금지** — 회사마다 달라 추정 불가, 관리자 직접 입력
4. ✅ **무게만 추정** — 고객/관리자 모두 무게 추정값만 표시
5. ✅ **"추정"임을 반드시 명시** — 실제 무게와 다를 수 있음
6. ✅ **확정 정보는 관리자 수동 입력** — 박스수/택배비/송장번호

## 🔐 Authentication System

### System Architecture (4 Independent Layers)

#### 1. User Authentication
- **Files**: `/includes/auth.php`, `/member/login_unified.php`
- **Database**: `users` table (bcrypt), `member` table (legacy)
- **Features**: Remember me (30 days), auto-upgrade plaintext passwords

#### 2. Admin Authentication
- **Files**: `/admin/includes/admin_auth.php`
- **Database**: `admin_users` table
- **Features**: Role-based access, session timeout

#### 3. Order Management Authentication
- **Files**: `/sub/checkboard_auth.php`
- **Access**: Order verification with password

#### 4. Customer Order Lookup
- **Files**: `/sub/my_orders_auth.php`
- **Access**: Phone + password verification

### Password Storage Standards

#### Bcrypt Format (Modern)
```php
// ✅ ALWAYS: New passwords use bcrypt
$hash = password_hash($password, PASSWORD_DEFAULT);
// Result: $2y$10$... (60 characters)
```

#### Plaintext Support (Legacy)
```php
// ✅ ALWAYS: Support legacy plaintext + auto-upgrade
if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
    // Bcrypt verification
    $login_success = password_verify($password, $stored_password);
} else {
    // Plaintext verification + auto-upgrade
    if ($password === $stored_password) {
        $login_success = true;
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        // UPDATE users SET password = $new_hash WHERE id = ?
    }
}
```

### Critical SSOT Files
- `includes/auth.php` - Main user authentication (bcrypt + plaintext support)
- `member/login_unified.php` - Header login handler
- `mlangorder_printauto/OnlineOrder_unified.php` - Order page modal login

### Session Management
- **Session Duration**: 8 hours
- **Remember Token**: 30 days (stored in `remember_tokens` table)
- **Cart Session Preservation**: Session ID passed via hidden field during login/signup

### Cart (장바구니) System

**테이블**: `shop_temp`

**장바구니 흐름**:
```
1. 제품 페이지 "장바구니 담기" → shop_temp INSERT
2. 장바구니/주문 페이지 → shop_temp 조회 (session_id로)
3. "주문완료" 클릭 → mlangorder_printauto INSERT + shop_temp DELETE
```

**세션 만료 시 장바구니**:
- 세션 만료(8시간) 후 새 세션 ID 발급
- 이전 session_id와 달라서 장바구니 조회 불가
- 데이터는 DB에 남아있음 (orphaned data)

**자동 정리 기능 (2026-02-05 추가)**:
```php
// mlangprintauto/shop_temp_helper.php - cleanupOldCartItems()
// 장바구니 조회 시 7일 이상 된 데이터 자동 삭제
DELETE FROM shop_temp WHERE regdate < UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY)
```

| 항목 | 값 |
|------|-----|
| 정리 주기 | 장바구니 조회 시 자동 실행 |
| 삭제 기준 | 7일 이상 경과 |
| 로그 | error_log에 삭제 건수 기록 |

### Authentication Consistency Rule (CRITICAL)

```php
// ❌ WRONG: Header login supports plaintext, order login doesn't
// Header (login_unified.php): password_verify() + plaintext fallback ✓
// Order page (auth.php): password_verify() only ✗
// Result: Same user can't login on order page!

// ✅ CORRECT: Both use identical verification logic
// Header login: bcrypt + plaintext with auto-upgrade
// Order login: bcrypt + plaintext with auto-upgrade
// Result: Consistent behavior across all login points
```

## 🔍 교정 관리 시스템 (Dashboard Proofs)

### 파일 구조
| 파일 | 용도 |
|------|------|
| `dashboard/proofs/index.php` | 교정 목록 + 이미지 뷰어 + 파일 업로드 UI |
| `dashboard/proofs/api.php` | 파일 목록 조회 / 파일 업로드 API |

### 교정파일 저장 경로
```
/mlangorder_printauto/upload/{주문번호}/
  ├─ 20260208_153000_시안_최종.jpg    (커스텀 이름)
  ├─ 20260208_a3f1b2c4.png            (자동 이름)
  └─ ...
```

### 이미지 뷰어 동작
```
"보기" 클릭 → API 파일 목록 조회 → 이미지 100% 원본 크기 오버레이 (스크롤)
  ├─ 여러 이미지: ‹ › 화살표 + 방향키 네비게이션 + 카운터(1/3)
  ├─ 닫기: 이미지 클릭 / 배경 클릭 / ESC / ✕ 버튼
  └─ 비이미지 파일: 새 탭으로 열기
```

### 파일 업로드 기능
- 파일 누적 추가 (선택/드롭 반복 가능)
- 개별 삭제, 이미지 썸네일 미리보기
- 파일명 자동 입력 (편집 가능, 확장자 별도 표시)
- 20MB/파일 제한, 허용 형식: jpg, jpeg, png, gif, pdf, ai, psd, zip
- 업로드 진행률 표시, 완료 후 페이지 새로고침 없이 행 갱신

### 교정 갤러리 (Public Proof Gallery)

**파일**: `popup/proof_gallery.php`

#### 기능 개요
```
https://dsp114.co.kr/popup/proof_gallery.php?cate=전단지&page=1
```
- 고객 주문 교정 이미지 갤러리
- 24개/페이지, pagination 지원
- 2가지 소스 혼합:
  1. Gallery 샘플: `/ImgFolder/inserted/gallery/` (101개)
  2. 실제 주문 이미지: `/mlangorder_printauto/upload/{주문번호}/` (1,046개)

#### Multi-File Upload JSON Parsing (2026-02-10 수정)

**문제**: `admin.php`에서 다중 파일 업로드 지원 후, `ThingCate` 컬럼에 JSON 배열 저장
```php
// 기존 (단일 파일): "20260208_abc.jpg"
// 신규 (다중 파일): '[{"original_name":"file.jpg","saved_name":"20260208_abc.jpg","size":1024,"type":"jpg"}]'
```

**해결**: `proof_gallery.php` (lines 189-210)에 JSON 파싱 로직 추가
```php
if (strpos($thing_cate, '[{') === 0 || strpos($thing_cate, '{"') === 0) {
    $decoded = json_decode($thing_cate, true);
    if (is_array($decoded)) {
        foreach ($decoded as $file_info) {
            if (isset($file_info['saved_name'])) {
                $files_to_check[] = $file_info['saved_name'];
            }
        }
    }
} else {
    $files_to_check[] = $thing_cate;
}
```

#### upload 디렉토리 이미지 500 에러 해결 (2026-02-10)

**문제**: 갤러리 5페이지 이상에서 upload 디렉토리 이미지 500 Internal Server Error

**원인**: `/httpdocs/mlangorder_printauto/upload/.htaccess` 파일이 Plesk Apache 2.4와 호환되지 않는 구문 포함
- `Options +Indexes` → Plesk에서 AllowOverride 제한으로 500 에러 유발
- `Order allow,deny` / `Allow from all` → Apache 2.2 구문 (mod_access_compat 미설치)
- Apache 2.2 + 2.4 구문 혼합 사용

**해결**: 해당 `.htaccess` 파일 삭제 (FTP로 프로덕션에서 제거)

**Critical Rules**:
- ❌ `/mlangorder_printauto/upload/`에 `.htaccess` 파일 생성 금지 (500 에러 유발)
- ✅ 해당 디렉토리는 `.htaccess` 없이 이미지 정상 서빙됨
- ⚠️ curl 기본 UA는 nginx에서 403 차단됨 (브라우저 UA 필요)

**검증 방법**:
```bash
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
curl -s -o /dev/null -w "%{http_code}" -A "$UA" "https://dsp114.co.kr/mlangorder_printauto/upload/79678/4820231127133915.jpg"
# 200이면 정상
```

## 📋 견적서 시스템 (Admin Quotes)

### 견적서 상태 흐름 (CRITICAL)

```
생성/저장 → draft (임시저장)
            ↓ "발송" 버튼 클릭 (이메일 발송)
          sent (발송됨)
            ↓ 고객 열람
          viewed (열람)
            ↓ 고객 승인/거절
          accepted / rejected
```

### 상태 변경 규칙
```php
// ✅ CORRECT: 저장 시 무조건 draft
$status = 'draft';  // saveQuote()

// ✅ CORRECT: sent는 이메일 발송 API에서만 변경
$manager->updateStatus($quoteId, 'sent');  // send_email.php

// ❌ WRONG: 저장 시 sent 설정 (이메일 안 보냈는데 "발송됨" 표시)
$status = $isDraft ? 'draft' : 'sent';
```

### 견적서 테마 — 홈페이지 헤더색 통일 (2026-02-15)

**두 개의 독립적 견적서 이메일 시스템이 홈페이지 헤더색(`#1E4E79`)으로 통일됨:**

| 시스템 | 파일 | 용도 |
|--------|------|------|
| 관리자 견적서 | `QuoteRenderer.php` | 다중 품목, 회사정보, PDF 첨부 (HTML/Email/PDF 3가지 출력) |
| 플로팅 견적받기 | `quote_request_api.php` | 단일 품목, 공급받는자/공급자 50:50 테이블, `$customerBody` 인라인 HTML |

**컬러 팔레트 (홈페이지 헤더 `#1E4E79` 기준):**

| 용도 | 색상 코드 |
|------|----------|
| 헤더/라벨 셀 배경 | `#1E4E79` (홈페이지 `.top-header` 동일) |
| 테이블 외곽선 | `#2a6496` |
| 테이블 내부선 | `#94a3b8` |
| 헤더 내부선 | `#3a7ab5` |
| 품목 테이블 내부선 | `#cbd5e1` |
| 연한 블루 배경 (합계행/공급자라벨) | `#e8eff7` |
| 값 셀 배경 | `#f8fafc` |
| 헤더 글씨 | `#ffffff` |
| PDF Fill | `SetFillColor(30, 78, 121)` |
| PDF Draw | `SetDrawColor(42, 100, 150)` |

**플로팅 견적서 이메일 구조:**
- 공급받는자 (50%): 견적일, 상호/성명, 연락처, 이메일
- 공급자 (50%): 등록번호, 상호, 대표자, 연락처
- company_info.php SSOT 활용 (`getCompanyInfo()`)

**QuoteRenderer 출력별 테마 적용:**

| 출력 형식 | 메서드 | 스타일 방식 |
|----------|--------|------------|
| HTML 미리보기 | `renderLegacyHTML()` | CSS 클래스 기반 |
| 이메일 발송 | `renderEmailBody()` | 인라인 스타일 (이메일 호환) |
| Legacy PDF | `renderLegacyPDF()` | TCPDF SetFillColor/SetTextColor/SetDrawColor |
| Standard PDF | `renderStandardPDF()` | mPDF CSS |

**관련 파일:**
- `admin/mlangprintauto/quote/includes/QuoteRenderer.php` — 관리자 견적서 렌더러
- `mlangprintauto/quote/standard/layout.php` — 브라우저 미리보기 CSS
- `includes/quote_request_api.php` — 플로팅 견적받기 고객 이메일
- `mlangprintauto/includes/company_info.php` — 회사 정보 SSOT

### 견적번호 체계 (2026-02-16)

**3가지 독립적 번호 체계:**

| 시스템 | 접두어 | 형식 | 예시 | 테이블 |
|--------|--------|------|------|--------|
| 관리자 견적서 | `AQ` | `AQ-YYYYMMDD-NNNN` | `AQ-20260208-0004` | `admin_quotes.quote_no` |
| 플로팅 견적받기 | `FQ` | `FQ-YYYYMMDD-NNN` | `FQ-20260216-001` | `quote_requests.quote_no` |
| 세금계산서 | `TAX` | `TAXYYYYMMDDNNNNNN` | `TAX20241109000001` | `tax_invoices.invoice_number` |

**FQ 번호 생성 로직** (`includes/quote_request_api.php`):
```php
// 당일 MAX 순번 조회 → +1
$fqPrefix = 'FQ-' . date('Ymd') . '-';
$seqQuery = "SELECT quote_no FROM quote_requests WHERE quote_no LIKE ? ORDER BY quote_no DESC LIMIT 1";
// → FQ-20260216-001, FQ-20260216-002, ...
```

### 견적 삭제 기능 (2026-02-19)

**대시보드 견적 목록** (`/dashboard/quotes/index.php`):
- 개별 삭제: 각 행 액션 컬럼의 빨간 "삭제" 링크
- 일괄 삭제: 행 앞 체크박스 선택 → 하단 빨간 바에서 "선택 삭제"
- 전체선택: thead 체크박스로 현재 페이지 전체 선택/해제

**API**: `/dashboard/api/quotes.php`
| action | 입력 | 동작 |
|--------|------|------|
| `delete` | `{ id: N }` | 단일 견적 삭제 (items → quotes 순서) |
| `bulk_delete` | `{ ids: [N, ...] }` | 일괄 삭제 |

**⚠️ 하드 삭제** — `admin_quotes` + `admin_quote_items` 에서 완전 삭제 (복구 불가)

### 이메일 발송 제한
- SMTP: 네이버 (`smtp.naver.com:465/ssl`, dsp1830)
- 네이버→네이버: ✅ 정상
- 네이버→Gmail: ⚠️ Gmail 스팸 필터에 의해 차단됨 (미해결)
- 향후: Gmail SMTP 이중 발송 구현 예정

### 대시보드 iframe 임베드
```
dashboard/embed.php?url=/admin/mlangprintauto/admin.php  → 주문 관리(구)
dashboard/embed.php?url=/admin/mlangprintauto/admin.php?mode=sian  → 교정 관리(구)
dashboard/embed.php?url=/admin/mlangprintauto/quote/  → 견적서(구)
dashboard/embed.php?url=/admin/mlangprintauto/option_prices.php  → 옵션 가격
```

## ⚡ Development Workflow

### Before Starting Work
1. Read `CLAUDE.md` for project-specific rules
2. Check existing patterns in similar files
3. Verify CSS specificity before using !important
4. Validate bind_param parameters (3-step rule)

### After Completing Work
1. Run `lsp_diagnostics` on changed files
2. Run relevant Playwright tests
3. Verify no existing functionality is broken
4. Test on multiple environments if applicable

### Code Quality Gates
- ✅ All bind_param calls validated (3-step rule)
- ✅ No !important usage in CSS
- ✅ Proper unit validation for quantity displays
- ✅ Correct product type → unit code mapping
- ✅ Playwright tests passing for affected areas

## 📋 주문 폼 데이터 흐름 (Order Form Data Flow)

### 주문 입력 → DB 저장 → 관리자 표시

```
OnlineOrder_unified.php (폼 입력)
  → form action="ProcessOrder_unified.php" (POST 처리)
    → INSERT INTO mlangorder_printauto (DB 저장)
      → admin.php?mode=OrderView (admin 조회)
        → OrderFormOrderTree.php (화면 렌더링)
```

### 주문자 정보 필드 매핑

| 폼 필드 | POST name | DB 컬럼 | 관리자 라벨 |
|---------|-----------|---------|------------|
| 성명/상호 | `username` | `name` | 이름 |
| 이메일 | `email` | `email` | 이메일 |
| 전화번호 | `phone` | `phone` | 전화 |
| 핸드폰 | `Hendphone` | `Hendphone` | 휴대폰 |
| 우편번호 | `sample6_postcode` | `zip` | 우편번호 |
| 주소 | `sample6_address` | `zip1` | 주소 |
| 상세주소 | `sample6_detailAddress` | `zip2` | 상세주소 |
| 물품수령방법 | `delivery_method` | `delivery` | 배송지 |
| 결제방법 | `payment_method` | `bank` | 입금은행 |
| 입금자명 | `bankname` | `bankname` | 입금자명 |
| 요청사항 | `cont` | `cont` | 비고 |

### 사업자 정보 필드 매핑

| 폼 필드 | POST name | DB 저장 방식 | 관리자 라벨 |
|---------|-----------|-------------|------------|
| 상호(회사명) | `business_name` | `bizname` (상호 + 사업자번호 형식) | 사업자명 |
| 사업자등록번호 | `business_number` | `bizname` + `cont` 텍스트 | 사업자명/비고 |
| 대표자명 | `business_owner` | `bizname` + `cont` 텍스트 | 사업자명/비고 |
| 업태 | `business_type` | `cont` 텍스트 | 비고 |
| 종목 | `business_item` | `cont` 텍스트 | 비고 |
| 사업장주소 | `business_address` (JS hidden) | `cont` 텍스트 | 비고 |
| 세금용메일 | `tax_invoice_email` | `cont` 텍스트 | 비고 |

### 결제방법 UI 동작

```
◉ 계좌이체 (기본값)  → 입금자명 입력란 표시 (필수, 주문자명 자동채움)
                       → 주문자명 ≠ 입금자명 시 confirm 경고
○ 카드결제           → 입금자명 숨김
○ 현금               → 입금자명 숨김
○ 기타               → 입금자명 숨김 (요청사항에 기재)
```

### 사업자 정보 자동 채움 (로그인 회원)

회원가입 시 `users` 테이블에 저장된 사업자 정보가 주문 폼에서 자동 채워짐:
- `users.business_name` → 상호(회사명)
- `users.business_number` → 사업자등록번호
- `users.business_owner` → 대표자명
- `users.business_type` → 업태
- `users.business_item` → 종목
- `users.business_address` → 사업장주소 (우편번호/주소/상세 자동 파싱)
- `users.tax_invoice_email` → 세금용 메일

**구현**: `toggleBusinessInfo()` JS 함수에서 `memberInfo` 객체 활용

## 🎨 UI/UX Improvements

### 방문자분석 URL 한글화 (2026-02-12)
**구현 위치**: `dashboard/visitors/index.php`

URL 경로 → 한글 제품명 매핑 (클릭 가능한 링크):
- `/mlangprintauto/sticker_new/index.php` → **스티커** (파란색 링크)
- `/mlangprintauto/inserted/index.php` → **전단지**
- 9개 제품 + 로그인/회원가입/주문서/장바구니 등 30개 경로 매핑

**매핑 구조**:
- `PAGE_NAME_MAP`: 정확 경로 매칭 (30개)
- `PAGE_PATH_PATTERNS`: 부분 경로 매칭 (17개 패턴)
- `getPageName(url)`: 2단계 매칭 함수

**적용 위치**: 인기 페이지, 진입/이탈 페이지, 실시간 방문자 테이블

### 주문통계 숫자 카운트업 애니메이션 (2026-02-12)
**구현 위치**: `dashboard/stats/index.php`

요약 카드 4개에 0→목표값 카운트업 애니메이션:
- `animateNumber(el, target, 800, isCurrency)` 함수
- easeOutExpo 이징 (`1 - Math.pow(2, -10 * progress)`)
- 통화 축약값(만/억) 애니메이션 중 포맷 유지
- `requestAnimationFrame` 기반 부드러운 렌더링

### 명함 재질 Hover 효과 (2026-01-28)
**변경 전**:
- 돋보기 아이콘 🔍 표시
- 어두운 overlay 배경 (rgba(0,0,0,0.4))
- 이미지 1.1배 확대

**변경 후**:
- ✅ "클릭하면 확대되어보입니다" 텍스트 메시지
- ✅ 투명 overlay (깔끔한 UI)
- ✅ 이미지 1.1배 확대 유지
- ✅ 부드러운 fade-in 애니메이션

**구현 위치**: `mlangprintauto/namecard/explane_namecard.php`

### 카톡상담 버튼 SVG 원형 이미지 교체 (2026-02-16)
**구현 위치**: `includes/sidebar.php`

우측 플로팅 메뉴의 카톡상담 버튼을 TALK.svg 벡터 원형 이미지로 교체:

**변경 전**:
- CSS 노란 원형 배경 (`#FEE500`) + 50×50 `talk_icon.png` 아이콘 + 별도 "카톡상담" HTML 라벨
- 3개 요소 (배경/아이콘/텍스트) 조합

**변경 후**:
- ✅ `TALK.svg` 벡터 이미지가 원형 버튼 전체를 차지 (노란 원형 + 말풍선 TALK + 카톡상담 텍스트 일체형)
- ✅ SVG 4KB (기존 PNG 대비 5배 작음)
- ✅ 반응형 전 구간 (100px/70px/52px) 벡터 스케일링으로 깨짐 없음
- ✅ "TALK" 글자가 path 데이터라 폰트 미설치 환경에서도 정확 렌더링

**관련 파일**:
- `/TALK.svg` — 카카오톡 원형 벡터 아이콘 (425.2×425.2 viewBox)
- `/TALK.png` — PNG 래스터 백업 (426×426, 미사용)
- `/TALK.ai` — Illustrator 원본 (웹 사용 불가)

**CSS 변경**: `.fm-kakao-circle`에서 background/border 제거, `.fm-kakao-full` 클래스 추가 (100% fill)

### 사이드바 패널 호버 UX 개선 (2026-02-16)
**구현 위치**: `includes/sidebar.php`

**문제**: 패널이 마우스 호버로 열리지만, 마우스가 버튼→패널 사이 빈 공간을 지날 때 패널이 즉시 사라짐

**해결 (2가지 병행)**:
1. **300ms mouseleave 딜레이** — 마우스가 버튼을 벗어나도 300ms 유예, 패널 위에 도달하면 타이머 취소
2. **📌 클릭=고정 힌트** — 전 패널(5개) 헤더에 `<span class="fm-pin-hint">📌 클릭=고정</span>` 표시, 고정(pinned) 상태에서는 자동 숨김

**JS 동작** (line 519~553):
```javascript
// mouseleave: 300ms 딜레이 후 닫기
item.addEventListener('mouseleave', function() {
    if (this.classList.contains('pinned')) return;
    this.dataset.closeTimer = setTimeout(() => {
        this.classList.remove('active');
    }, 300);
});

// mouseenter: 타이머 취소 (패널 위에 도달)
item.addEventListener('mouseenter', function() {
    clearTimeout(this.dataset.closeTimer);
});
```

**CSS**:
- `.fm-panel-title` → `display: flex; justify-content: space-between;` (제목+힌트 양쪽 정렬)
- `.fm-pin-hint` → `font-size: 10px; opacity: 0.7;` (작고 은은하게)
- `.fm-item.pinned .fm-pin-hint` → `display: none;` (고정 시 힌트 숨김)

**적용 패널**: 고객센터, 파일전송, 업무안내, 입금안내, 운영시간 (전체 5개)

### 대시보드 레이아웃 최적화 (2026-02-17)

**구현 위치**: `dashboard/includes/header.php`, `dashboard/includes/sidebar.php`, `dashboard/includes/footer.php`, `dashboard/orders/view.php`

**변경 전**: 대시보드 페이지 높이가 사이드바 메뉴(982px)에 의해 결정 → 주문 상세 1,350px, 뷰포트(900px) 초과로 스크롤 필요

**변경 후**:
- ✅ `header.php`: 레이아웃 컨테이너 `min-h-screen` → `h-screen overflow-hidden` (고정 높이)
- ✅ `sidebar.php`: `overflow-y-auto` 추가 (사이드바 독립 스크롤, 페이지 높이에 영향 안 줌)
- ✅ `footer.php`: 푸터 HTML 제거 (53px 절약, 관리자 페이지에 불필요)
- ✅ `view.php`: 모든 카드 `p-4`→`p-3`, 간격/마진 축소, 요청사항 `max-h-32 overflow-y-auto`
- 결과: **1,350px → 900px** (뷰포트에 스크롤 없이 모든 정보 표시)

**레이아웃 구조**:
```
<div class="flex h-screen pt-11 overflow-hidden">  ← 뷰포트 고정
  <aside overflow-y-auto>  ← 사이드바 독립 스크롤
  <main overflow-y-auto>   ← 메인 콘텐츠 독립 스크롤
</div>
```

**영향 범위**: 대시보드 전체 페이지 (header/sidebar/footer 공통 컴포넌트)

### 마이페이지 주문 상태 OrderStyle 통일 (2026-02-17)

**구현 위치**: `mypage/index.php`

**변경 전**: `level` 컬럼(5단계) 기반 — 대시보드 `OrderStyle` 변경이 반영 안 됨

**변경 후**:
- ✅ `OrderStyle` 컬럼 기반으로 통일 (SSOT)
- ✅ `getCustomerStatus()` 함수: OrderStyle 11가지 → 고객용 5단계 그룹핑
  - 주문접수: OrderStyle 0,1,2
  - 접수완료: OrderStyle 3,4
  - 작업중: OrderStyle 5,6,7,9,10
  - 작업완료: OrderStyle 8
  - 배송중: 송장번호 존재 시
- ✅ 필터/쿼리/표시 모두 OrderStyle 기반

**상태 변경 경로**: `dashboard/orders/view.php` → 상태 드롭다운 → POST `/dashboard/api/orders.php?action=update` → `UPDATE mlangorder_printauto SET OrderStyle = ?` → 마이페이지에 즉시 반영

### 프로필 사업자 상세주소 레거시 파싱 개선 (2026-02-17)

**구현 위치**: `mypage/profile.php`

**문제**: `business_address`에 `|||` 구분자 없이 저장된 레거시 데이터가 전부 readonly 메인 주소 필드에 들어가서 상세주소 필드가 빈 상태로 남음. 사용자가 상세주소를 수정할 수 없음.

**예시 데이터**: `[07301] 서울 영등포구 영등포로36길 9 1층 두손기획인쇄 (영등포동4가)` (구분자 없음)

**해결**: DOMContentLoaded 파싱 시 도로명주소 패턴(`/^(.+(?:로|길|가)\s*\d+(?:-\d+)?)\s+(.+)$/`)으로 자동 분리:
- 메인 주소(readonly): `서울 영등포구 영등포로36길 9`
- 상세주소(editable): `1층 두손기획인쇄`
- 참고항목(editable): `(영등포동4가)`

**정규화**: 페이지 로드 시 `|||` 없는 레거시 데이터를 즉시 정규 형식으로 변환 (`updateBusinessAddress()` 자동 호출)

**저장 형식**: `[우편번호] 메인주소|||상세주소 (참고항목)` — 이후 페이지 로드 시 `|||` 기반 정상 파싱

## 📧 Email System (주문 완료 이메일)

### 시스템 구성

| 파일 | 용도 |
|------|------|
| `mlangorder_printauto/mailer.lib.php` | PHPMailer 래퍼 (SMTP 설정) |
| `mlangorder_printauto/send_order_email.php` | 이메일 발송 API |
| `mlangorder_printauto/OrderComplete_universal.php` | 주문 완료 시 자동 발송 호출 |
| `mlangorder_printauto/PHPMailer/` | PHPMailer 라이브러리 |

### SMTP 설정 (네이버)

```php
$mail->Host = "smtp.naver.com";
$mail->Port = 465;
$mail->SMTPSecure = "ssl";
$mail->Username = "dsp1830";
$mail->Password = "2CP3P5BTS83Y";
```

### 이메일 발송 흐름

```
1. 주문 완료 → OrderComplete_universal.php 로드
2. JavaScript에서 send_order_email.php로 POST 요청
3. send_order_email.php에서 HTML 템플릿 생성
4. mailer() 함수로 네이버 SMTP 통해 발송
5. 고객 이메일로 주문 확인 메일 수신
```

### 자동 발송 조건

- 최초 주문 완료 시에만 발송 (결제 취소/실패 시 발송 안 함)
- `sessionStorage`로 중복 발송 방지
- 이메일 주소 유효성 검증 후 발송

### mailer() 함수 시그니처

```php
function mailer($fname, $fmail, $to, $subject, $content, $type=1, $file, $cc="", $bcc="")
// $type: 0=text, 1=html, 2=text+html
// $file: 첨부파일 배열 또는 "" (빈 문자열)
```

### PHP 8.2 호환성 패치 (2026-02-05)

`PHPMailer/PHPMailer.php` Line 3612:
```php
// 변경 전 (PHP 8.2에서 오류)
filter_var('http://' . $host, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)

// 변경 후 (PHP 8.2 호환)
filter_var('http://' . $host, FILTER_VALIDATE_URL)
```

### Critical Rules

1. ❌ `mailer()` 호출 시 `$file` 파라미터 생략 금지 → 빈 문자열 `""` 필수
2. ❌ 복잡한 HTML 템플릿에서 정의되지 않은 변수 사용 금지
3. ✅ 운영 서버 PHP 버전 확인 필수 (현재 8.2.30)

## 🚨 Common Pitfalls to Avoid

### Database & Core Logic
1. ❌ bind_param count mismatch → customer name saved as '0'
2. ❌ Uppercase table names → SELECT failure
3. ❌ Uppercase include paths → file not found on Linux
4. ❌ `getUnitCode($productType)` → sticker "개" unit bug
5. ❌ Direct quantity formatting without unit validation
6. ❌ number_format(0.5) → "1" rounding error
7. ❌ Changing `littleprint` to `poster` → system-wide errors

### CSS & Frontend
8. ❌ CSS !important usage without proper diagnosis

### Plesk .htaccess (2026-02-07)
17. ❌ Apache 2.2 구문 사용 (Order, Allow) → Plesk 500 에러 유발
18. ❌ `.htaccess`를 잘못 작성하면 이미지/페이지가 500 에러 발생
19. ❌ Plesk는 nginx + Apache 조합 사용 → .htaccess는 Apache 2.4 호환만 사용
20. ❌ `/mlangorder_printauto/upload/`에 `.htaccess` 파일 생성 시 500 에러 발생 (삭제 후 정상 작동)

### PHP 8.2 호환성 — mysqli_close 순서 (2026-02-15, CRITICAL)

**⚠️ 이것만 기억해: `mysqli_close($db)` 뒤에서 `$db`를 쓰면 PHP 8.2에서 Fatal Error로 죽는다.**

```
로컬 PHP 7.4:  mysqli_close($db) → mysqli_query($db, ...) → false 반환 (조용히 넘어감)
프로덕션 PHP 8.2: mysqli_close($db) → mysqli_query($db, ...) → ❌ Fatal Error: mysqli object is already closed
```

**실제 사고 (2026-02-15):**
- `quote_gauge.php`(플로팅 견적 위젯)가 내부에서 `mysqli_query($db, ...)` 사용
- 4개 제품 페이지(포스터/상품권/자석스티커/카다록)에서 `mysqli_close($db)`를 include 앞에 배치
- 로컬에서 정상 → 프로덕션에서 위젯 안 보임 (Fatal Error가 display_errors=Off라 숨겨짐)
- 원인 찾기 어려웠음: 에러 메시지 없이 include 결과물만 사라짐

**반드시 지킬 것:**
```php
// ❌ 절대 금지: DB 닫은 뒤에 DB 사용하는 include
mysqli_close($db);
include 'quote_gauge.php';  // 내부에서 $db 사용 → PHP 8.2 Fatal Error

// ✅ 올바른 순서: include 먼저, DB 닫기는 맨 마지막
include 'quote_gauge.php';  // $db 정상 사용
if (isset($db) && $db) { mysqli_close($db); }  // 페이지 끝에서 정리
</body>
```

**진단 팁:**
- 프로덕션에서만 안 되고 로컬에서 되면 → PHP 버전 차이 의심 (로컬 7.4 vs 프로덕션 8.2)
- include 결과물이 HTML에 안 나타나면 → include 대상 파일 내부의 Fatal Error 의심
- `require_once`로 이미 로드된 `db.php`는 재실행 안 됨 → `$db`가 닫힌 상태 그대로 유지

21. ❌ `mysqli_close($db)` 후에 `$db` 사용하는 include → PHP 8.2 Fatal Error (로컬에서 안 잡힘!)

### Payment System
9. ❌ Enabling production mode on localhost → real payments triggered
10. ❌ Hardcoding production URLs → closeUrl domain mismatch error
11. ❌ Forgetting to test with small amounts → accidental large payments
12. ❌ Not checking logs after deployment → silent payment failures

### Authentication
13. ❌ Inconsistent password verification → same user can't login everywhere
14. ❌ Not preserving cart session during login → cart data loss
15. ❌ Only supporting bcrypt → legacy users locked out
16. ❌ Forgetting auto-upgrade → users stuck with plaintext passwords

## ✅ member → users 마이그레이션 완료 (2026-02-02)

**상태: 6단계 완료 (7단계 member DROP은 의도적 보류)**

모든 활성 PHP 코드가 `users` 테이블을 primary로 사용하도록 전환 완료.
`member` 테이블은 backward compatibility를 위해 유지 (이중 쓰기).

### 마이그레이션 결과 요약

| 단계 | 범위 | 상태 |
|------|------|------|
| 1단계 | 회원가입/관리자 (`register_process`, `admin/member/`) | ✅ 완료 |
| 2단계 | 로그인 (`login_unified`, `session/loginProc`) | ✅ 완료 |
| 3단계 | session/ 디렉토리 (7개 파일) | ✅ 완료 |
| 4단계 | 주문 시스템 (`OnlineOrder`, `OrderFormOrderOne`, `WindowSian`) | ✅ 완료 |
| 5단계 | 관리자 (`admin/config`, `AdminConfig`, `MlangPoll/admin`) | ✅ 완료 |
| 6단계 | 나머지 전체 (BBS 23개 skin, member/, lib/, shop/, sub/ 등) | ✅ 완료 |
| 7단계 | member 테이블 DROP | ⏸️ 의도적 보류 |

### 의도적으로 member 참조를 유지하는 파일

| 파일 | 이유 |
|------|------|
| `member/register_process.php` | users INSERT + member 이중 INSERT |
| `member/change_password.php` | users UPDATE + member sync UPDATE |
| `member/password_reset.php` | users UPDATE + member sync UPDATE |
| `admin/AdminConfig.php` | users UPDATE + member sync UPDATE |
| `bbs/PointChick.php` | member.money (포인트 시스템, users에 컬럼 없음) |

### 컬럼 매핑 (member → users)

```
member.no → users.id (PK)
member.id → users.username
member.pass → users.password (bcrypt)
member.name → users.name
member.phone1-2-3 → users.phone (통합)
member.hendphone1-2-3 → users.phone
member.sample6_postcode → users.postcode
member.sample6_address → users.address
member.sample6_detailAddress → users.detail_address
member.po1-7 → users.business_number/name/owner/type/item/address/tax_invoice_email
```

### Admin 패턴
```php
// 이전: SELECT * FROM member WHERE no='1'
// 현재: SELECT username AS id, password AS pass FROM users WHERE is_admin = 1 LIMIT 1
```

## 🔄 데이터 마이그레이션 (dsp114.com → 2개 타겟 서버)

### 📋 빠른 참조 (Quick Reference)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    dsp114.com (소스 - 폐쇄 예정)                             │
│                    PHP 5.2 | MySQL | EUC-KR                                 │
│                    http://dsp114.com/export_api.php                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
              ┌─────────────────────┴─────────────────────┐
              ▼                                           ▼
┌──────────────────────────────┐       ┌──────────────────────────────────────┐
│  🏢 dsp114.co.kr             │       │  🏠 dsp1830.ipdisk.co.kr:8000        │
│     (임대 서버 - 운영)        │       │     (개인 NAS - 전체 백업)            │
├──────────────────────────────┤       ├──────────────────────────────────────┤
│ 용량: 제한적 (할당량)         │       │ 용량: 750GB+ (충분)                   │
│ PHP: 7.x | MySQL: 5.7+       │       │ PHP: 7.3.17 | MySQL: 5.6.30          │
├──────────────────────────────┤       ├──────────────────────────────────────┤
│ 📁 파일 필터:                 │       │ 📁 파일 필터:                         │
│   교정: 75000번 이상          │       │   교정: 전체 (min_no=0)               │
│   원고: 2026년 이후           │       │   원고: 전체 (min_year=2000)          │
├──────────────────────────────┤       ├──────────────────────────────────────┤
│ 🔗 대시보드:                  │       │ 🔗 대시보드:                          │
│ https://dsp114.co.kr         │       │ http://dsp1830.ipdisk.co.kr:8000     │
│ /system/migration/index.php  │       │ /system/migration/index.php          │
└──────────────────────────────┘       └──────────────────────────────────────┘
                    비밀번호: duson2026!migration (양쪽 동일)
```

### 🔧 마이그레이션 도구

| 항목 | 값 |
|------|-----|
| **대시보드** | `/system/migration/index.php` |
| **비밀번호** | `duson2026!migration` |
| **소스 API** | `http://dsp114.com/export_api.php` |
| **API 키** | `duson_migration_sync_2026_xK9m` |
| **동기화 엔진** | `/system/migration/MigrationSync.php` |

### 📊 서버별 설정 차이

| 설정 | dsp114.co.kr | NAS (dsp1830.ipdisk.co.kr) |
|------|--------------|----------------------------|
| `FILE_FILTER_MIN_NO` | **75000** | **0** |
| `FILE_FILTER_MIN_YEAR` | **2026** | **2000** |
| 교정파일 범위 | 75000번 이상 | **전체** |
| 원고파일 범위 | 2026년 이후 | **전체** |
| 목적 | 운영 (최근 데이터만) | 아카이브 (완전 백업) |

### ⚠️ 중요 규칙

1. **무시할 테이블**: `users`, `qna`, `Mlang_board_bbs`, `Mlang_portfolio_bbs` — 타겟 서버에서 미사용
2. **dsp114.co.kr 날짜 필터**: `since=2026-01-29` — 이전 데이터는 이미 존재
3. **NAS 전체 백업**: dsp114.com 폐쇄 대비 모든 데이터 영구 보관

### 🗄️ 3개 서버 상세 사양

#### 소스: dsp114.com (폐쇄 예정)
| 항목 | 값 |
|------|-----|
| **PHP** | 5.2 (mysql_* 함수) |
| **DB** | MySQL (EUC-KR) |
| **DB 계정** | `duson1830` / `du1830` |
| **웹루트** | `/home/neo_web2/duson1830/www/` |
| **상태** | ⚠️ 일일 트래픽 제한, 폐쇄 예정 |

#### 타겟 1: dsp114.co.kr (운영 서버)
| 항목 | 값 |
|------|-----|
| **유형** | Plesk 임대 서버 |
| **PHP** | 7.x |
| **DB** | MySQL 5.7+ |
| **FTP** | `dsp1830` / `cH*j@yzj093BeTtc` |
| **웹루트** | `/httpdocs/` |
| **용량** | 제한적 (할당량 주의) |

#### 타겟 2: dsp1830.ipdisk.co.kr:8000 (NAS 백업)
| 항목 | 값 |
|------|-----|
| **유형** | 개인 NAS |
| **웹 서버** | Apache/2.4.43 (Unix) |
| **PHP** | 7.3.17 |
| **MySQL** | 5.6.30 (Source distribution) |
| **문자셋** | UTF-8 Unicode (utf8) |
| **Collation** | utf8mb4_unicode_ci |
| **phpMyAdmin** | 5.0.2 |
| **PHP 확장** | mysqli, curl, mbstring |
| **FTP** | `admin` / `1830` |
| **웹루트** | `/HDD2/share/` |
| **용량** | 750GB+ (충분) |

### 📁 파일 경로 매핑

| 파일 유형 | dsp114.com (소스) | 타겟 서버 |
|----------|------------------|-----------|
| 교정파일 | `/www/MlangOrder_PrintAuto/upload/{no}/` | `/mlangorder_printauto/upload/{no}/` |
| 원고(스티커) | `/www/shop/data/` | `/shop/data/` |
| 원고(일반) | `/www/ImgFolder/_MlangPrintAuto_*/` | `/ImgFolder/_MlangPrintAuto_*/` |

### ✅ DB 동기화 완료 기록 (2026-02-02)

| 테이블 | 결과 |
|--------|------|
| member | +10건 (중복 19건 제외) |
| MlangOrder_PrintAuto | +9건 |
| 제품 테이블 9개 | 3,398건 INSERT |
| shop_order/list/list01/temp | +7,775건 |
| orderDB/orderDB2 | +613건 |
| ❌ users, qna, BBS | 무시 |

### 🛠️ 유틸리티 API (index.php)

| action | 설명 |
|--------|------|
| `check_permissions` | 디렉토리 쓰기 권한 확인 |
| `disk_usage` | 디스크 용량 확인 |
| `cleanup_upload` | 오래된 교정파일 삭제 (threshold 파라미터) |
| `file_sync` | 파일 동기화 실행 |
| `file_stats` | 파일 현황 조회 |

### ⚡ 호환성 참고

```
dsp114.com (소스)     →  PHP 5.2, mysql_* 함수, EUC-KR
dsp114.co.kr          →  PHP 7.x, mysqli_* 함수, UTF-8  ✅ 동일 코드
dsp1830.ipdisk.co.kr  →  PHP 7.3, mysqli_* 함수, UTF-8  ✅ 동일 코드
```

- 소스 API(export_api.php)만 PHP 5.2 호환 문법 사용
- 타겟 서버 2개는 동일한 MigrationSync.php 사용 (설정값만 다름)

## 📧 이메일 캠페인 시스템 (Email Campaign System)

### 시스템 개요

대시보드에서 회원에게 일괄 이메일을 발송하는 시스템.

| 항목 | 값 |
|------|-----|
| **대시보드 UI** | `/dashboard/email/index.php` |
| **API** | `/dashboard/api/email.php` (12개 action) |
| **이미지 업로드** | `/dashboard/email/uploads/` |
| **사이드바 메뉴** | 📧 이메일 발송 (소통·견적 그룹) |
| **SMTP** | 네이버 (`dsp1830@naver.com`) |

### DB 테이블 (3개)

| 테이블 | 용도 |
|--------|------|
| `email_campaigns` | 캠페인 (제목, 본문, 상태, 수신자수, 성공/실패 카운트) |
| `email_send_log` | 개별 발송 로그 (수신자별 상태, 에러 메시지) |
| `email_templates` | 저장된 템플릿 (이름, 제목, HTML 본문) |

### API 엔드포인트 (`/dashboard/api/email.php`)

| action | Method | 용도 |
|--------|--------|------|
| `get_recipients` | GET | 수신자 목록/카운트 (전체/필터/수동) |
| `send` | POST | 캠페인 생성 + 발송 시작 |
| `send_batch` | POST | 배치 발송 (100명씩) |
| `send_test` | POST | dsp1830@naver.com으로 테스트 발송 |
| `save_draft` | POST | 임시저장 |
| `campaigns` | GET | 캠페인 목록 (페이지네이션) |
| `campaign_detail` | GET | 캠페인 상세 + 발송 로그 |
| `templates` | GET | 템플릿 목록 |
| `load_template` | GET | 템플릿 불러오기 |
| `save_template` | POST | 템플릿 저장/수정 |
| `delete_template` | POST | 템플릿 삭제 |
| `upload_image` | POST | 이미지 업로드 (5MB, JPG/PNG/GIF/WebP) |

### WYSIWYG 에디터 (2026-02-12)

3가지 편집 모드:
- **편집기** (기본): `contenteditable` div + 서식 도구모음
- **HTML편집**: raw textarea (고급 사용자용)
- **미리보기**: 렌더링된 HTML 확인

도구모음: B, I, U, H1, H2, P, 🔗링크, 📷이미지업로드, •목록, 1.목록, ─구분선, 색상, ✕서식제거

```javascript
// 모드 전환 시 콘텐츠 자동 동기화
function getEmailBody() {
    if (currentEditorMode === 'wysiwyg') {
        document.getElementById('email-body').value = 
            document.getElementById('wysiwyg-editor').innerHTML;
    }
    return document.getElementById('email-body').value.trim();
}
```

### 네이버 SMTP 제한 (Critical Rules)

```
1회 최대: 100명
일일 한도: ~500통 (안전 기준)
배치 간격: 3초 대기 (클라이언트 측)
Gmail 수신: ⚠️ 스팸 분류 가능성
앱 비밀번호: 2CP3P5BTS83Y (mailer.lib.php에 설정됨)
```

### 발송 흐름

```
1. UI에서 "이메일 발송" 클릭
2. action=send → email_campaigns INSERT + email_send_log INSERT (수신자별)
3. action=send_batch → 100명씩 mailer() 호출 → 성공/실패 로그 UPDATE
4. 3초 대기 → 다음 배치 반복
5. 전체 완료 → campaign status='completed'
```

### 수신자 필터

- **전체 회원**: `users` 테이블에서 admin/test/봇 제외 (328명, 2026-02-12 기준)
- **조건 필터**: 최근 로그인 기간 + 이메일 도메인
- **직접 입력**: 쉼표 구분 이메일 주소

### `{{name}}` 치환

이메일 본문에서 `{{name}}`은 수신자 이름으로 자동 치환됨. 이름 없으면 '고객'으로 표시.

### 회원 이메일 현황 (2026-02-12 기준)

- 총 328명 (고유 이메일 기준, admin/test 제외)
- naver.com: 193명, hanmail.net: 37명, gmail.com: 28명, daum.net: 14명
- ⚠️ 오타 이메일 4건: `nate.ocm`, `naver.vom`, `naver.coml`, `naver.co.kr`
- 289명 미로그인 (구 사이트에서 마이그레이션된 회원)

### 기본 템플릿 (2개)

1. **설날 인사**: 2026 구정 인사 + 새 홈페이지 안내
2. **새 홈페이지 오픈**: dsp114.co.kr 오픈 안내 (2월 23일)

### 이메일 푸터 (고정)

```
두손기획인쇄 | 서울특별시 영등포구 영등포로 36길9 송호빌딩 1층 두손기획인쇄 | Tel. 02-2632-1830
본 메일은 두손기획인쇄 회원님께 발송됩니다. 수신을 원하지 않으시면 [여기]를 클릭해주세요.
```

### 미완료 작업

- [ ] 프로덕션 배포 (dsp114.co.kr FTP)
- [ ] 오타 이메일 4건 수정 (users 테이블)
- [ ] 실제 회원 발송 (2단계: 2/13 설날 + 2/23 오픈)

## 📦 대시보드 카테고리 관리 (Dashboard Category Management)

### 시스템 개요

대시보드에서 품목(카테고리)별 가격 데이터를 관리하는 시스템.

| 항목 | 값 |
|------|-----|
| **UI** | `/dashboard/products/list.php` |
| **API** | `/dashboard/api/products.php` (4개 action) |
| **DB 테이블** | `catelist` (카테고리 메타) + `mlangprintauto_*` (품목별 가격) |

### API 엔드포인트 (`/dashboard/api/products.php`)

| action | Method | 용도 |
|--------|--------|------|
| `category_list` | GET | 품목별 카테고리 목록 조회 (스타일 필터 지원) |
| `category_create` | POST | 새 카테고리 추가 |
| `category_update` | POST | 카테고리명/설명 수정 |
| `category_delete` | POST | 카테고리 삭제 (가격 데이터 연쇄 삭제) |

### 카테고리 관리 UI 기능

- **스타일 필터**: 전체/대봉투/소봉투 등 드롭다운 필터
- **테이블 형식**: ID, 카테고리명, 설명, 수정/삭제 버튼
- **수정 모달**: 인라인 편집 (카테고리명 + 설명)
- **삭제 확인**: confirm 다이얼로그 + 연쇄 삭제 경고
- **추가 모달**: 카테고리 코드 + 이름 + 설명 입력

### 교정시안 품목명 한글화

**구현 위치**: `dashboard/proofs/index.php`

영문 테이블명 → 한글 품목명 자동 매핑:
```php
$PRODUCT_NAME_MAP = [
    'sticker_new' => '스티커', 'inserted' => '전단지',
    'namecard' => '명함', 'envelope' => '봉투',
    'littleprint' => '포스터', 'merchandisebond' => '상품권',
    'msticker' => '자석스티커', 'cadarok' => '카다록',
    'ncrflambeau' => 'NCR양식지'
];
```

## 🏢 관리자 주문 등록 (Admin Order Registration)

### 시스템 개요

전화/비회원 주문을 관리자가 대시보드에서 직접 등록하는 시스템.

| 항목 | 값 |
|------|-----|
| **UI** | `/dashboard/admin-order/index.php` |
| **API** | `/dashboard/api/admin-order.php` |
| **사이드바** | 📋 주문등록 (주문관리 그룹) |
| **DB 테이블** | `mlangorder_printauto` (기존 주문 테이블) |

### 주요 기능

- 품목 선택 → 카테고리 자동 로드 → 수량/사이즈 입력
- 주문자 정보 (이름, 전화, 이메일, 주소)
- 가격 수동 입력 (공급가액 + VAT 자동 계산)
- 배송방법/결제방법 선택
- 택배비 선불 지원 (운임구분 착불/선불 + 택배비 금액 입력)
- 요청사항 메모

### 택배비 선불 (2026-02-19)

배송방법 "택배" 선택 시 운임구분(착불/선불) 라디오 표시:
- **착불** (기본): 추가 입력 없음
- **선불**: 택배비 금액 입력란 표시 → DB `logen_fee_type`, `logen_delivery_fee` 저장
- 저장된 값은 `OrderFormOrderTree.php`에서 자동 표시 (기존 택배비 표시 로직 연동)

### 택배비 VAT 계산 (2026-02-19)

`dashboard/orders/view.php`에서 택배비 선불 금액을 공급가액으로 처리하여 VAT 10% 합산 표시:

```php
$shipping_supply = $logen_delivery_fee;           // 공급가액 (DB 저장값)
$shipping_vat = round($shipping_supply * 0.1);    // VAT 10%
$shipping_total = $shipping_supply + $shipping_vat; // 합계
```

**표시 형식**: `5,000+VAT 500 = 5,500원` (OrderFormOrderTree.php 패턴 통일)

**적용 위치**: 금액 정보 카드 + 결제 정보 카드 (2곳)

## 🤖 영업시간 외 AI 챗봇 위젯 (After-Hours AI Chatbot)

### 시스템 개요

영업시간(09:00~18:30) 외 시간에 자동으로 표시되는 AI 챗봇 위젯.
기존 v2 ChatbotService를 직접 로드하여 DB 기반 실시간 가격 조회 제공.

| 항목 | 값 |
|------|-----|
| **위젯 파일** | `/includes/ai_chatbot_widget.php` |
| **API 엔드포인트** | `/api/ai_chat.php` |
| **ChatbotService** | `/v2/src/Services/AI/ChatbotService.php` (직접 require) |
| **표시 조건** | 18:30 이후 ~ 09:00 이전 (footer.php 통합 토글) |
| **include 위치** | `/includes/footer.php` (모든 페이지) |
| **테마** | 보라색 그라디언트 (#6366f1) — 주황색 직원 채팅과 구분 |

### 시간 체크 로직 (footer.php 통합 토글)

위젯 시간 제어는 `footer.php`의 통합 스크립트에서 일괄 관리.
`ai_chatbot_widget.php`에는 시간 체크 로직 없음 (순수 UI만).

```javascript
// footer.php — 통합 toggleWidgets() (60초 간격 실행)
function isBusinessHours() {
    var now = new Date();
    var h = now.getHours(), m = now.getMinutes();
    if (h < 9) return false;       // 09:00 이전
    if (h > 18) return false;      // 19:00 이후
    if (h === 18 && m >= 30) return false; // 18:30 이후
    return true;
}
function toggleWidgets() {
    var biz = isBusinessHours();
    var staff = document.querySelector('.chat-widget');   // chat.js가 동적 생성
    var ai = document.getElementById('ai-chatbot-widget'); // 정적 HTML
    if (staff) staff.style.display = biz ? '' : 'none';
    if (ai) ai.style.display = biz ? 'none' : 'block';
}
setInterval(toggleWidgets, 60000);
```

### API 구조 (`/api/ai_chat.php`)

| action | Method | 용도 |
|--------|--------|------|
| `chat` | POST | 메시지 전송 → ChatbotService.chat() 호출 |
| `reset` | POST | 대화 세션 초기화 |

- `V2_ROOT` 상수 정의 후 ChatbotService 직접 require (composer autoloader 불필요)
- `.env` 파일의 `GEMINI_API_KEY` 로드 (없어도 DB 기반 가격 조회는 정상 동작)
- Same-origin Referer 체크 (CSRF 대체)
- 세션 기반 대화 상태 유지 (`$_SESSION['chatbot']`)

### 위젯 UI 구성

- **토글 버튼**: 88×88px 보라색 원형 (사이드바 `.fm-card` 크기 통일), "야간/당번" 라벨
- **채팅 창**: 370×520px, 16px border-radius
- **빠른 선택 버튼**: 명함, 전단지, 스티커, 봉투, 카다록, 포스터, 상품권, 자석스티커, 양식지 (9개 전 품목)
- **메시지 버블**: 사용자(보라색 우측) / 봇(회색 좌측, "야간당번" 아바타)
- **타이핑 인디케이터**: 3-dot 애니메이션
- **모바일 반응형**: ≤768px에서 100% 너비
- **클릭형 선택지**: 번호 입력 대신 클릭으로 옵션 선택 (`.ai-opt-btn` 버튼), 선택 후 이전 버튼 비활성화

### 대화 흐름

```
제품 선택 (빠른 버튼 or 텍스트)
  → 종류 선택 (번호 입력)
    → 용지 선택
      → 수량 선택
        → 인쇄면 선택
          → 디자인 선택
            → ✅ 가격 표시 (VAT 포함)
```

### 직원 채팅 vs AI 챗봇 배타적 전환

| 시간대 | 위젯 | 위치 |
|--------|------|------|
| 09:00~18:30 | 주황색 직원 채팅 (`chat_widget.php`) | bottom-right |
| 18:30~09:00 | 보라색 AI 챗봇 (`ai_chatbot_widget.php`) | bottom:20px, right:80px |

**배타적 전환 메커니즘**:
- 두 위젯 모두 `footer.php`에서 include (DOM에 항상 존재)
- `toggleWidgets()` 함수가 시간대에 따라 `display` 속성으로 한쪽만 표시
- 직원 채팅(`.chat-widget`)은 `chat.js`가 동적 생성 → `querySelector`로 탐색
- AI 챗봇(`#ai-chatbot-widget`)은 정적 HTML → `getElementById`로 탐색
- 60초 간격 `setInterval`로 영업시간 경계에서 실시간 전환

### 한국어 조사 자동 판별 (ChatbotService.php)

`getParticle()` 헬퍼 — 마지막 글자 받침 유무로 을/를 자동 선택:
```php
private function getParticle(string $text, string $withBatchim, string $withoutBatchim): string
{
    $lastChar = mb_substr($text, -1);
    $code = mb_ord($lastChar);
    if ($code >= 0xAC00 && $code <= 0xD7A3) {
        return (($code - 0xAC00) % 28 === 0) ? $withoutBatchim : $withBatchim;
    }
    return $withBatchim;
}
// 사용: "규격을 선택해주세요" vs "수량를→수량을" 자동 처리
```

### NCR양식지 단계 순서 (CRITICAL)

NCR양식지의 챗봇 대화 단계는 제품 페이지 드롭다운 순서와 반드시 일치해야 함:

```php
// ChatbotService.php — NCR 단계 설정
'ncrflambeau' => [
    'steps' => ['style', 'section', 'tree', 'quantity', 'design'],
    'stepLabels' => ['구분', '규격', '색상', '수량', '디자인'],
],
// style(BigNo=0) → section(BigNo=style) → tree(TreeNo=style) → quantity → design
```

**⚠️ 과거 오류**: stepLabels가 `['매수', '규격', '인쇄도수', ...]`로 잘못 설정되어 있었음. 실제 NCR 페이지의 드롭다운 cascade 순서와 라벨명이 일치하지 않으면 사용자 혼란 발생.

### Critical Rules

1. ❌ `.env` 파일 없어도 동작해야 함 — DB 연결만으로 가격 조회 가능
2. ❌ v2 composer autoloader 의존 금지 — 직접 require_once로 로드
3. ✅ 에러 발생 시 "전화 문의" 안내로 graceful fallback
4. ✅ 세션 쿠키로 대화 상태 유지 (페이지 이동해도 대화 계속)
5. ✅ 선택지는 클릭형 버튼으로 제공 (API `options` 배열 → 프론트 `.ai-opt-btn` 렌더링)
6. ✅ stepLabels는 제품 페이지 실제 드롭다운 라벨과 일치시킬 것

## 🌐 영문 버전 (English Version)

### 시스템 개요

해외 고객용 영문 주문 사이트. 한국어 사이트와 동일한 DB/백엔드를 공유하며, 프론트엔드만 영문화.

| 항목 | 값 |
|------|-----|
| **경로** | `/en/` (로컬: `http://localhost/en/`, 프로덕션: `https://dsp114.co.kr/en/`) |
| **대시보드 토글** | 설정 → 영문 버전 표시 (ON/OFF) → `site_settings.en_version_enabled` |
| **환율 API** | `/en/includes/exchange_rate.php` (USD 실시간 환율) |

### 파일 구조

```
/en/
├── includes/
│   ├── nav.php              ← 공유 네비게이션 (탑 네비 + 9개 제품 바)
│   └── exchange_rate.php    ← USD 환율 조회
├── index.php                ← EN 홈페이지 (히어로, 제품, 견적 폼)
├── cart.php                 ← 장바구니
├── checkout.php             ← 주문서 작성
├── order_complete.php       ← 주문 완료
└── products/
    ├── index.php            ← 제품 목록
    ├── order.php            ← 8개 제품 주문 (type 파라미터)
    └── order_sticker.php    ← 스티커 전용 (수식 기반 가격)
```

### 공유 네비게이션 (`en/includes/nav.php`)

모든 EN 페이지에서 `include`하는 자체 포함형 컴포넌트 (CSS + HTML + JS 일체):

- **탑 네비**: 로고, Products, Cart, Why Us, Contact, EN|한국어 전환, Get Free Quote CTA
- **제품 바**: 9개 제품 버튼 + Cart (가로 스크롤, 모바일 반응형)
- **Active 상태**: `$_en_current_page` 변수로 현재 제품 하이라이트

```php
// 사용법 (각 페이지에서)
<?php $_en_current_page = 'namecard'; include __DIR__ . '/../includes/nav.php'; ?>
```

**CSS 클래스 접두어**: `.en-nav-*` (탑 네비), `.en-pbar-*` (제품 바) — 한국어 사이트 CSS와 충돌 방지

### 주문 플로우

```
홈페이지 (/en/) → 제품 선택 (제품 바 또는 카드)
  → 제품 주문 페이지 (order.php?type=namecard)
    → 옵션 cascade 선택 (Type→Paper→PrintSide→Quantity→Design)
    → 가격 표시 (₩ KRW + ≈ $ USD)
    → Add to Cart → 장바구니 (cart.php)
      → Proceed to Order → 체크아웃 (checkout.php)
        → 주문자 정보 + 배송주소 + 결제방법 입력
        → Place Order → 주문 완료 (order_complete.php)
```

### 백엔드 공유 (한국어 사이트와 동일)

| 기능 | 공유 API |
|------|----------|
| 옵션 로드 | `/mlangprintauto/{product}/get_*.php` |
| 가격 계산 | `/mlangprintauto/{product}/calculate_price_ajax.php` |
| 장바구니 | `/mlangprintauto/{product}/add_to_basket.php` |
| 주문 처리 | `/mlangorder_printauto/ProcessOrder_unified.php` |
| DB 테이블 | `shop_temp` (장바구니), `mlangorder_printauto` (주문) |

### 대시보드 EN 토글

| 파일 | 역할 |
|------|------|
| `dashboard/settings/index.php` | 토글 UI (🇰🇷한국어만 / 🌐한국어+영어) |
| `dashboard/api/settings.php` | `en_version_enabled` 키 whitelist |
| `includes/header.php` | `site_settings` 조회 → EN 버튼 조건부 표시 |
| `includes/header-ui.php` | 동일 조건부 표시 |

### Critical Rules

1. ✅ `formData.append('action', 'add_to_basket')` — EN order.php에서 장바구니 API 호출 시 반드시 포함
2. ✅ `$_en_current_page` 변수를 `include nav.php` 앞에 설정
3. ✅ CSS 클래스는 `en-nav-*`, `en-pbar-*` 접두어 사용 (한국어 사이트 충돌 방지)
4. ✅ sticky sidebar `top: 128px` (64px 네비 + 44px 제품 바 + 20px 간격)
5. ❌ 한국어 네비 `/includes/nav.php` 수정 금지 — EN 네비는 별도 파일
6. ❌ 드롭다운 옵션 번역 없음 — "Option labels are shown in Korean" 안내 표시

### 제품 바 버튼 매핑

| 버튼 | key | 링크 |
|------|-----|------|
| Stickers | sticker | `/en/products/order_sticker.php` |
| Flyers | inserted | `/en/products/order.php?type=inserted` |
| Business Cards | namecard | `/en/products/order.php?type=namecard` |
| Envelopes | envelope | `/en/products/order.php?type=envelope` |
| Catalogs | cadarok | `/en/products/order.php?type=cadarok` |
| Posters | littleprint | `/en/products/order.php?type=littleprint` |
| NCR Forms | ncrflambeau | `/en/products/order.php?type=ncrflambeau` |
| Gift Vouchers | merchandisebond | `/en/products/order.php?type=merchandisebond` |
| Magnetic Stickers | msticker | `/en/products/order.php?type=msticker` |

## 📚 Documentation References

- Master Specification: `CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md`
- Data Flow: `CLAUDE_DOCS/DATA_LINEAGE.md`
- CSS Debug Lessons: `CLAUDE_DOCS/CSS_DEBUG_LESSONS.md`
- Email Campaign Details: `CLAUDE_DOCS/EMAIL_CAMPAIGN_SYSTEM.md`
- Change History: `.claude/changelog/CHANGELOG.md`

---

*Last Updated: 2026-02-20 (영문 버전 네비게이션·주문플로우·대시보드 토글, AI챗봇 클릭형 선택지·야간당번 브랜딩·9품목·NCR단계수정·조사판별, 직원채팅/AI챗봇 배타적 전환, 홈페이지 실시간 견적 라이브 데모, 캐로셀 dot 하단 조정, 택배비 VAT 계산, 관리자 주문등록 택배비 선불, 채팅창 팝업 제어, 견적 목록 삭제/일괄삭제)*
*Environment: WSL2 Ubuntu + Windows XAMPP + Production Deployment*