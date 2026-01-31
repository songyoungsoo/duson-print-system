# AGENTS.md - Duson Planning Print System

## 🚨 CRITICAL - PRODUCTION SERVER INFO (배포 필수 확인!)

**⚠️ 운영 서버 FTP/웹 루트 구조 - 배포 시 반드시 확인!**

**서버 변경 내역:**
- ❌ 구 서버: `dsp1830.shop` (마이그레이션 완료, 더 이상 사용 안 함)
- ✅ 현재 운영: `dsp114.co.kr` (2026년 1월 현재)

```
FTP 접속 정보 (dsp114.co.kr):
├─ Host: dsp114.co.kr
├─ User: dsp1830
├─ Pass: cH*j@yzj093BeTtc
└─ Protocol: FTP (plain, port 21)

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

## 📚 Documentation References

- Master Specification: `CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md`
- Data Flow: `CLAUDE_DOCS/DATA_LINEAGE.md`
- CSS Debug Lessons: `CLAUDE_DOCS/CSS_DEBUG_LESSONS.md`
- Change History: `.claude/changelog/CHANGELOG.md`

---

*Last Updated: 2026-01-31*
*Environment: WSL2 Ubuntu + Windows XAMPP + Production Deployment*