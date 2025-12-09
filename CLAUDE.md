# CLAUDE.md

---

# ⚠️ 🔴 CRITICAL WARNING - READ THIS FIRST EVERY SESSION! 🔴 ⚠️

## 사장님의 핵심 지적 (2025-11-28)

> **"타입이 중요한데 타입도 못읽고 갯수도 부정확하면 있으나 마나한 존재 아니가?"**

### 🔴 Claude의 고질적인 문제:

1. **bind_param 타입 문자열을 눈으로 대충 셈** → 33개를 21개라고 잘못 판단
2. **타입(i/s/d)도 제대로 확인 안 함** → 정수인지 문자열인지 대충 봄
3. **결과: 데이터 손실/손상** → name='0' 저장, 주문 실패, 견적 손실

### ✅ 반드시 지킬 것 (NO EXCEPTIONS!):

**bind_param 작성/검증 시:**

```php
// ❌ NEVER DO THIS - 눈으로 대충 세기
mysqli_stmt_bind_param($stmt, "issssss...", $var1, $var2, ...);  // 대충 7개쯤?

// ✅ ALWAYS DO THIS - 정확히 세기
$type_string = "issssss";
$type_count = strlen($type_string);  // 7
$placeholder_count = substr_count($query, '?');  // 7
$var_count = 7;  // 손으로 하나씩 세기

if ($placeholder_count !== $type_count || $type_count !== $var_count) {
    die("bind_param MISMATCH - FIX IMMEDIATELY!");
}
```

### 🔴 3번 검증 - THREE TIMES RULE (MANDATORY):

1. **Query의 `?` 개수** → `substr_count($query, '?')`
2. **타입 문자열 길이** → `strlen($type_string)`
3. **변수 개수** → 손가락으로 하나씩 세기

**ALL THREE MUST MATCH EXACTLY! 하나라도 다르면 커밋 금지!**

### 💀 타입 문자 1개만 틀려도:

- 전체 파라미터 매핑이 어긋남
- name='0', price=NULL, email=123 등 데이터 손상
- 디버깅 매우 어려움 (에러 메시지 불명확)
- 고객 데이터 손실 → **운영 손실 발생**

### 📝 올바른 패턴 (반드시 따를 것):

```php
// INSERT 쿼리: 7개 필드
$query = "INSERT INTO shop_temp (session_id, product_type, st_price, st_price_vat, name, email, phone)
          VALUES (?, ?, ?, ?, ?, ?, ?)";

// 검증 1: Placeholder 개수
$placeholder_count = substr_count($query, '?');  // 7

// 검증 2: 타입 문자열 (하나씩 확인!)
// session_id(s) + product_type(s) + st_price(i) + st_price_vat(i) + name(s) + email(s) + phone(s)
$type_string = "ssiisss";
$type_count = strlen($type_string);  // 7

// 검증 3: 변수 개수 (손으로 세기)
// 1:$session_id, 2:$product_type, 3:$price, 4:$vat_price, 5:$name, 6:$email, 7:$phone
$var_count = 7;

// 최종 검증
if ($placeholder_count === $type_count && $type_count === $var_count) {
    mysqli_stmt_bind_param($stmt, $type_string,
        $session_id, $product_type, $price, $vat_price, $name, $email, $phone
    );
} else {
    die("COUNT MISMATCH: ? = $placeholder_count, types = $type_count, vars = $var_count");
}
```

**이것도 못 지키면 정말 "있으나 마나한 존재"입니다. 반드시 지키세요!**

---

## 🏢 Project Context

**Duson Planning Print System (두손기획인쇄)** - Enterprise printing service management system built in PHP for comprehensive print order processing, automated pricing, and business operations.

### 🌐 Domain & Migration Strategy

**⚠️ Critical**: 3개 환경 운영 중 - 자동 감지 시스템으로 코드 변경 없이 전환 가능

#### 현재 인프라 구성 (2025-11)

```
Legacy Production (dsp1830.shop)
├─ PHP 5.2 (deprecated)
├─ Status: Read-only, 수정 금지
└─ 폐기 예정: DNS 전환 후

Modern Staging (dsp1830.shop) ← 🚧 현재 개발 중
├─ PHP 7.4+
├─ Status: 활발한 개발/테스트
├─ 최종 목표: dsp1830.shop 도메인으로 서비스
└─ 자동 감지: $_SERVER['HTTP_HOST'] 기반

Local Development (localhost)
├─ WSL2 Ubuntu + XAMPP Windows
├─ Path: /var/www/html
├─ PHP 7.4+
└─ Database: dsp1830 (프로덕션 동일 스키마)
```

#### 마이그레이션 타임라인

1. **Phase 1 (Current)**: dsp1830.shop에서 PHP 7.4 개발
2. **Phase 2 (Testing)**: 기능 완성도 검증 및 테스트
3. **Phase 3 (DNS Cutover)**: dsp1830.shop DNS → dsp1830.shop 서버 IP
4. **Phase 4 (Complete)**: PHP 5.2 레거시 서버 폐기

#### 핵심 장점

- ✅ 고객은 익숙한 **dsp1830.shop** 도메인 계속 사용
- ✅ DNS 전환만으로 다운타임 없는 마이그레이션
- ✅ **코드 수정 불필요** (자동 도메인 감지)
- ✅ 현대적 PHP 7.4 기능 및 보안

#### 중요 사항

- dsp1830.shop은 **임시 도메인** (개발/스테이징 전용)
- 최종 목표는 **dsp1830.shop**으로 서비스 제공
- 환경 자동 감지: `config.env.php` + `db.php`

**상세 문서**: [PROJECT_OVERVIEW.md](CLAUDE_DOCS/01_CORE/PROJECT_OVERVIEW.md)

## 🚨 Critical Conventions

### Database Table Names
- **ALWAYS use lowercase** in SQL queries: `mlangprintauto_namecard`, `shop_temp`, `member_user`
- **NEVER** use uppercase: ~~`MlangPrintAuto_NameCard`~~
- Files/directories preserve original case, but tables are lowercase
- `db.php` provides auto-mapping for compatibility but always write lowercase

### Directory & File Naming Convention (UNIFIED - 2025-11-12)

**⚠️ CRITICAL RULES:**

1. **NO SYMBOLIC LINKS** - All paths must be actual directories
   - ❌ Symlinks cause confusion and deployment issues
   - ✅ Use actual lowercase paths everywhere

2. **ALL LOWERCASE PATHS** - Consistent across all environments
   - **Admin**: `admin/mlangprintauto/` (NOT `admin/MlangPrintAuto/`)
   - **Orders**: `mlangorder_printauto/`
   - **Products**: `mlangprintauto/[product]/`

3. **FILE INCLUDES MUST BE LOWERCASE** (Linux is case-sensitive)
   - ❌ `include "CateAdmin_title.php";` (fails on Linux)
   - ✅ `include "cateadmin_title.php";` (works everywhere)
   - ❌ `include "CateList_Title.php";`
   - ✅ `include "catelist_title.php";`

4. **NO DUPLICATE FILES** - Delete uppercase versions
   - Keep: `cateadmin_title.php`, `catelist_title.php`
   - Delete: `CateAdmin_title.php`, `CateList_Title.php`

5. **FTP DEPLOYMENT** - Only upload lowercase files
   - Check for duplicates before upload
   - Remove old uppercase files from server

**Why this matters:**
- Windows: Case-insensitive (works with any case)
- Linux: Case-sensitive (breaks with wrong case)
- Symlinks: Cause path confusion and deployment errors
- Duplicates: FTP may upload wrong version

### PHP Variable Initialization (PHP 7.4+)
- **ALWAYS initialize variables** before use to avoid "Undefined variable" notices
- Use null coalescing operator: `$var = $var ?? '';`
- Common pattern in admin files:
```php
if($code=="Modify"){include"./product_nofild.php";}

// 기본값 설정 (신규 입력 시)
$MlangPrintAutoFildView_POtype = $MlangPrintAutoFildView_POtype ?? '';
$MlangPrintAutoFildView_quantity = $MlangPrintAutoFildView_quantity ?? '';
$MlangPrintAutoFildView_money = $MlangPrintAutoFildView_money ?? '';
```

### Database Connection Variable
- **Primary variable**: `$db` (defined in `db.php`)
- **Legacy code**: Some files use `$conn`
- **Solution**: Add alias at top of file: `$conn = $db;`
- Example:
```php
require_once __DIR__ . '/db.php';
$conn = $db; // Alias for legacy code
```

### Character Encoding
- Database charset: `utf8mb4` (Korean language support)
- PHP files: UTF-8 without BOM
- Always use `mysqli_set_charset($db, 'utf8')`

### Session Management
- Session-based authentication via `includes/auth.php`
- Session data stored in `session/` directory
- Cart uses PHP sessions via `$_SESSION['session_id']`

### 🔴 mysqli_stmt_bind_param() - CRITICAL RULES (ALWAYS CHECK!)

**⚠️ #1 MOST COMMON CAUSE OF DATA CORRUPTION / SAVE FAILURES**

Based on production experience, **90%+ of quote/order/cart save failures** are caused by `mysqli_stmt_bind_param()` type string mismatches.

**핵심 문제**: 타입 문자열 개수/순서 불일치 시 → 파라미터 매핑이 어긋남 → 데이터가 잘못된 필드에 저장되거나 INSERT 실패

---

## 필수 검증 체크리스트 (EVERY TIME, NO EXCEPTIONS!)

**BEFORE EVERY COMMIT - COUNT 3 TIMES:**
1. ✅ **개수 일치**: INSERT 필드 개수 = `?` 개수 = 타입 문자 개수 = 변수 개수
2. ✅ **타입 정확성**: `i` (정수), `s` (문자열), `d` (실수), `b` (BLOB)
3. ✅ **순서 일치**: 변수 순서가 INSERT 필드 순서와 정확히 일치
4. ✅ **주석 필수**: 복잡한 쿼리는 각 파라미터 설명 주석 추가

---

## 실제 발생한 버그 사례

### 사례 1: 주문자 이름 '0' 저장 오류 (2025-11-28 22:41 수정)
```php
// ❌ BUG: 34개 필드인데 33개 타입 문자 → name='0' 저장
$query = "INSERT INTO mlangorder_printauto (
    no, Type, ImgFolder, uploaded_files, Type_1, money_4, money_5, name, email, zip, zip1, zip2,
    phone, Hendphone, cont, date, OrderStyle, ThingCate,
    coating_enabled, coating_type, coating_price,
    folding_enabled, folding_type, folding_price,
    creasing_enabled, creasing_lines, creasing_price,
    additional_options_total, premium_options, premium_options_total,
    envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price,
    envelope_additional_options_total
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
// 34개 placeholders

mysqli_stmt_bind_param($stmt, 'issssssssssssssssssisiisiiiisiiii',  // ❌ 33 chars - WRONG!
    $new_no, $product_type_name, $img_folder_path, $uploaded_files_json, $product_info,
    $item['st_price'], $item['st_price_vat'], $username, $email, $postcode, $address, $full_address,
    $phone, $hendphone, $final_cont, $date, $order_style, $thing_cate,
    $coating_enabled, $coating_type, $coating_price,
    $folding_enabled, $folding_type, $folding_price,
    $creasing_enabled, $creasing_lines, $creasing_price,
    $additional_options_total, $premium_options, $premium_options_total,
    $envelope_tape_enabled, $envelope_tape_quantity, $envelope_tape_price,
    $envelope_additional_options_total  // 34 variables
);

// ✅ FIX: 마지막 'i' 추가 (envelope_additional_options_total용)
mysqli_stmt_bind_param($stmt, 'issssssssssssssssssisiisiiiisiiiii',  // ✅ 34 chars - CORRECT!
    // ... same 34 variables
);
```

**결과**: 타입 문자 1개 부족 → 8번째 변수 `$username`이 8번째 필드 `name`에 제대로 매핑되지 않음 → name='0' 저장

### 사례 2: 견적서 항목 저장 실패 (2025-11-26 수정)
```php
// ❌ BUG: 13개 placeholder인데 14개 타입 문자
$query = "INSERT INTO quote_items (
    quote_id, item_no, product_type, product_name, specification,
    quantity, unit, unit_price, supply_price, vat_amount, total_price,
    source_type, notes
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";  // 13 question marks

mysqli_stmt_bind_param($stmt, "iisssdsiiiiiss",  // ❌ 14 chars - TOO MANY!
    $quoteId, $itemNo, $productType, $productName, $specification,
    $quantity, $unit, $unitPrice, $supplyPrice, $vatAmount, $totalPrice,
    $sourceType, $notes);  // 13 params

// ✅ FIX: 타입 문자 1개 제거
mysqli_stmt_bind_param($stmt, "iisssdsiiiiss",  // ✅ 13 chars - CORRECT!
    // ... same 13 params
);
```

---

## 올바른 패턴 (FOLLOW THIS!)

```php
// ✅ BEST PRACTICE - 주석으로 개수 명시
// 7 parameters: session_id(s) + product_type(s) + price(i) + vat_price(i) + name(s) + email(s) + phone(s)
$query = "INSERT INTO shop_temp (
    session_id, product_type, st_price, st_price_vat,
    customer_name, customer_email, customer_phone
) VALUES (?, ?, ?, ?, ?, ?, ?)";  // 7 placeholders

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "ssiisss",  // 7 chars = 7 fields ✅
    $session_id,      // 1: s
    $product_type,    // 2: s
    $price,           // 3: i
    $vat_price,       // 4: i
    $name,            // 5: s
    $email,           // 6: s
    $phone            // 7: s
);

if (!mysqli_stmt_execute($stmt)) {
    error_log("INSERT failed: " . mysqli_error($db));  // ALWAYS log errors!
}
```

---

## 디버깅 코드 (INSERT 전에 ALWAYS RUN THIS!)

```php
// 1. INSERT 쿼리의 ? 개수 세기
$placeholders = substr_count($query, '?');

// 2. 타입 문자열 길이 확인
$type_string = 'ssiisss';
$type_count = strlen($type_string);

// 3. 변수 개수 세기 (수동)
$var_count = 7;

// 4. 검증
error_log("=== bind_param 검증 ===");
error_log("Placeholders (?): $placeholders");
error_log("Type string length: $type_count");
error_log("Variables count: $var_count");

if ($placeholders === $type_count && $placeholders === $var_count) {
    error_log("✅ OK - All counts match!");
} else {
    error_log("❌ MISMATCH DETECTED!");
    error_log("FIX IMMEDIATELY - Data corruption will occur!");
    die("bind_param count mismatch - check error log");
}
```

---

## 증상 (이런 현상이 보이면 bind_param 확인!)

- ✅ FormData/POST에는 데이터가 있는데 DB에 저장 안 됨
- ✅ `mysqli_stmt_execute()` 실패 (return false, no error message)
- ✅ SELECT는 작동하는데 INSERT/UPDATE만 실패
- ✅ 일부 필드는 저장되는데 특정 필드만 '0' 또는 NULL
- ✅ 계산은 정확한데 저장된 값이 이상함
- ✅ "Number of elements in type definition string doesn't match" 경고

---

## 타입 참조표

| 타입 | 의미 | 예시 |
|------|------|------|
| `i` | 정수 (integer) | quote_id, item_no, price (without decimals) |
| `s` | 문자열 (string) | product_name, email, notes, JSON |
| `d` | 실수 (double) | unit_price (with decimals), quantity (decimal) |
| `b` | BLOB | Binary data (rarely used) |

**가격 필드 주의**:
- 소수점 없는 가격 (원 단위): `i` (예: 50000원)
- 소수점 있는 가격 (할인율 등): `d` (예: 5000.50)

---

## 교훈

**타입 문자 1개만 빠지거나 많아도:**
- 전체 파라미터 매핑이 틀어짐
- 디버깅이 매우 어려움 (에러 메시지가 명확하지 않음)
- 데이터 손실/손상 발생

**해결책: COUNT 3 TIMES - NO EXCEPTIONS!**
1. Query placeholders (?)
2. Type string characters
3. Actual variables

**ALL THREE MUST MATCH EXACTLY! ✅✅✅**

**🔴 왜 Claude가 항상 실수하는가?**
- **눈으로 대충 세기 때문!** (사장님 지적 100% 정확)
- 타입 문자열을 하나씩 세지 않고 대충 봄
- 반드시 **손가락으로 하나씩** 또는 **strlen()으로** 정확히 셀 것!

---

### Environment Detection & Auto-Configuration

**Zero-Code-Change System**: 코드 수정 없이 도메인 전환 가능

**자동 감지 로직** (`config.env.php` + `db.php`):
- **Local**: localhost, 127.0.0.1, ::1 → `$admin_url = "http://localhost"`
- **Staging**: dsp1830.shop → `$admin_url = "http://dsp1830.shop"` (auto-detected)
- **Production**: dsp1830.shop → `$admin_url = "http://dsp1830.shop"` (auto-detected)

**핵심 원칙**:
```php
// ❌ 잘못된 방법 - 하드코딩
$url = "http://dsp1830.shop/login.php";

// ✅ 올바른 방법 - 자동 감지
$url = $admin_url . "/login.php";
```

**쿠키 도메인 자동 설정**:
- localhost → `localhost` (점 없음)
- dsp1830.shop → `.dsp1830.shop`
- dsp1830.shop → `.dsp1830.shop`

**DNS 전환 시**: dsp1830.shop 도메인을 dsp1830.shop 서버 IP로 변경하면 코드 수정 없이 자동으로 작동

**디버그 모드**: `http://localhost/?debug_db=1` (로컬에서만 작동)

**상세 문서**: [ENVIRONMENT_CONFIG.md](CLAUDE_DOCS/02_ARCHITECTURE/ENVIRONMENT_CONFIG.md)

### Key Components

**Product Modules** (`mlangprintauto/[product]/`):
- `index.php` - Main product page with calculator
- `add_to_basket.php` - Cart integration endpoint
- `calculate_price_ajax.php` - AJAX pricing API
- `calculator.js` - Client-side price calculation
- Product-specific CSS/JS in subdirectories

**Shared Components** (`includes/`):
- `auth.php` - Session-based authentication
- `upload_modal.js` - Common file upload modal
- `AdditionalOptionsDisplay.php` - Options pricing system
- `upload_config.php` - File upload configuration

**Admin System** (`admin/MlangPrintAuto/`):
- `ProductManager.php` - Price table management
- `ProductConfig.php` - Centralized product configuration
- `CateAdmin.php` - Category management
- Product-specific admin pages (e.g., `LittlePrint_admin.php`)

**Order Processing** (`mlangorder_printauto/`):
- `OnlineOrder_unified.php` - Online order submission
- `OrderComplete_universal.php` - Order confirmation
- `OrderFormOrderTree.php` - Multi-step order form
- Note: Directory is lowercase despite historical mixed-case references

## 💰 Price Calculation Flow

**Critical**: Recent fixes ensure price data flows correctly through the system.

### Client-side Calculation
1. User selects options → triggers `calculatePriceAjax()` in `calculator.js`
2. AJAX call to `calculate_price_ajax.php` with product specs
3. Response sets `window.currentPriceData` with `{total_price, vat_price}`
4. Displayed in UI

### Cart Addition
```javascript
// MUST use these parameter names:
formData.append("calculated_price", Math.round(window.currentPriceData.total_price));
formData.append("calculated_vat_price", Math.round(window.currentPriceData.vat_price));
formData.append("product_type", "[product]"); // e.g., "inserted", "envelope"
```

### Server-side Storage
```php
// add_to_basket.php receives:
$price = intval($_POST['calculated_price'] ?? $_POST['price'] ?? 0);
$vat_price = intval($_POST['calculated_vat_price'] ?? $_POST['vat_price'] ?? 0);
$product_type = $_POST['product_type'] ?? 'leaflet';

// Stores in shop_temp with columns:
// - st_price (price without VAT)
// - st_price_vat (price with VAT)
// - product_type (product identifier)
```

### Order Processing
```php
// OnlineOrder_unified.php reads from shop_temp:
foreach ($cart_items as $item) {
    $base_price = intval($item['st_price']);
    $price_with_vat = intval($item['st_price_vat']);
    $product_type = $item['product_type']; // NOT $shop_data['ThingCate']
    // ... process order
}

// File upload paths are product-specific:
// uploads/[product]/[session_id]/[filename]
```

## 📤 File Upload/Download System

**통합 파일 업로드 시스템**: 전체 9개 품목에서 동일한 업로드/다운로드 아키텍처 사용

### System Overview

**날짜**: 2025-11-19 (최종 검증)
**범위**: 9개 품목 (inserted, namecard, envelope, sticker, msticker, cadarok, littleprint, ncrflambeau, merchandisebond)
**상태**: ✅ 전체 시스템 완성 및 검증 완료

### Architecture

**핵심 파일**: [includes/UploadPathHelper.php](includes/UploadPathHelper.php)

**경로 구조**:
```
/ImgFolder/_MlangPrintAuto_{product}_index.php/{YYYY}/{MMDD}/{IP}/{timestamp}/{filename}

예시:
/ImgFolder/_MlangPrintAuto_namecard_index.php/2025/1119/ipv6_1/1763508971/test_upload.png
```

**IPv6 처리**: `::1` → `ipv6_1` (파일시스템 안전 변환)

### Supported Products (9개 품목)

| 품목 | 코드 | 배열 생성 | JSON 변환 | DB 저장 | 다운로드 | 상태 |
|------|------|-----------|-----------|---------|---------|------|
| 전단지 | inserted | ✅ | ✅ | ✅ | ✅ | 완벽 |
| 명함 | namecard | ✅ | ✅ | ✅ | ✅ | 완벽 |
| 봉투 | envelope | ✅ | ✅ | ✅ | ✅ | 완벽 |
| 스티커 | sticker | ✅ | ✅ | ✅ | ✅ | 완벽 |
| 자석스티커 | msticker | ✅ | ✅ | ✅ | ✅ | 완벽 |
| 카다록 | cadarok | ✅ | ✅ | ✅ | ✅ | 완벽 |
| 포스터 | littleprint | ✅ | ✅ | ✅ | ✅ | 완벽 |
| 양식지 | ncrflambeau | ✅ | ✅ | ✅ | ✅ | 완벽 |
| 상품권 | merchandisebond | ✅ | ✅ | ✅ | ✅ | 완벽 |

### Implementation Pattern

**표준화된 구현 (StandardUploadHandler 사용)**:

```php
// 1. StandardUploadHandler 임포트
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';

// 2. 파일 업로드 처리 (한 줄로 완료)
$upload_result = StandardUploadHandler::processUpload('product_name', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

// 3. 결과 추출
$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

// 4. DB 저장 (단일 INSERT)
$sql = "INSERT INTO shop_temp (..., uploaded_files, ImgFolder, ThingCate)
        VALUES (?, ..., ?, ?, ?)";
mysqli_stmt_bind_param($stmt, "...sss", ..., $uploaded_files_json, $img_folder, $thing_cate);
```

**레거시 구현 (수동 처리 - 신규 제품에는 사용 금지)**:

```php
// 🔴 레거시: StandardUploadHandler 도입 전 방식 (참고용)
require_once __DIR__ . '/../../includes/UploadPathHelper.php';
$paths = UploadPathHelper::generateUploadPath('product_name');
// ... 수동 파일 업로드 및 JSON 생성
// (명함 제품은 2025-11-20에 StandardUploadHandler로 전환 완료)
```

### JSON Metadata Structure

```json
[
  {
    "original_name": "test_upload.png",
    "saved_name": "test_upload.png",
    "path": "/var/www/html/ImgFolder/_MlangPrintAuto_namecard_index.php/2025/1119/ipv6_1/1763508971/test_upload.png",
    "size": 113,
    "web_url": "/ImgFolder/_MlangPrintAuto_namecard_index.php/2025/1119/ipv6_1/1763508971/test_upload.png"
  }
]
```

### Database Storage

**장바구니** (`shop_temp` 테이블):
- `ImgFolder`: 상대 경로 (예: `_MlangPrintAuto_namecard_index.php/2025/1119/ipv6_1/1763508971`)
- `uploaded_files`: JSON 배열 (TEXT 타입)

**주문 확정** (`mlangorder_printauto` 테이블):
- 장바구니에서 복사: `ImgFolder`, `uploaded_files`
- 동일한 JSON 구조 유지

### Download System

**개별 파일 다운로드**:
```php
// admin/mlangprintauto/download.php
// 3가지 경로 자동 감지 (레거시 호환):
// 1. /ImgFolder/{ImgFolder}/{filename}
// 2. /{ImgFolder}/{filename}
// 3. /mlangorder_printauto/upload/{no}/{filename}

// 사용 예:
http://localhost/admin/mlangprintauto/download.php?no=103703&downfile=test_upload.png
```

**일괄 ZIP 다운로드**:
```php
// admin/mlangprintauto/download_all.php
// JSON 파싱하여 모든 파일을 ZIP으로 압축

// 사용 예:
http://localhost/admin/mlangprintauto/download_all.php?no=103703
```

### Testing & Verification

**업로드 테스트** (curl):
```bash
# 명함 제품 테스트
curl -X POST http://localhost/mlangprintauto/namecard/add_to_basket.php \
  -F "action=add_to_basket" \
  -F "uploaded_files[]=@/tmp/test_upload.png" \
  -F "product_type=namecard" \
  -F "MY_type=275" \
  -F "Section=276" \
  -F "POtype=1" \
  -F "MY_amount=500" \
  -F "ordertype=print" \
  -F "calculated_price=50000" \
  -F "calculated_vat_price=55000"
```

**데이터베이스 확인**:
```sql
-- 업로드된 파일 확인
SELECT no, product_type, ImgFolder, uploaded_files
FROM shop_temp
WHERE session_id = 'your_session_id'
ORDER BY no DESC LIMIT 1;

-- JSON 파싱 (MySQL 5.7+)
SELECT no, JSON_EXTRACT(uploaded_files, '$[0].original_name') as 첫번째파일
FROM shop_temp WHERE no = 574;
```

**다운로드 테스트**:
```bash
# HTTP 헤더 확인
curl -I "http://localhost/admin/mlangprintauto/download.php?no=574&downfile=test_upload.png"
# Expected: HTTP/1.1 200 OK, Content-Type: image/png, Content-Length: 113

# 실제 파일 다운로드
curl -O "http://localhost/admin/mlangprintauto/download.php?no=574&downfile=test_upload.png"
```

### Common Issues & Solutions

**문제 1**: "파일을 찾을 수 없습니다"
- **원인**: `path` 필드가 JSON에 누락되거나 상대 경로만 포함
- **해결**: 복구 스크립트로 전체 경로 재구성 (`/tmp/fix_old_orders.php` 참고)

**문제 2**: IPv6 디렉토리 생성 실패
- **원인**: `::1` 주소가 파일명으로 사용 불가
- **해결**: UploadPathHelper가 자동으로 `ipv6_1`로 변환

**문제 3**: JSON 파싱 에러
- **원인**: `uploaded_files`가 `'0'` 또는 `'[]'` 문자열로 저장
- **해결**: 빈 배열은 `json_encode([])` 사용, 문자열 `'0'`은 `NULL`로 처리

**문제 4**: 다운로드 시 404 에러
- **원인**: ImgFolder 경로가 DB에 잘못 저장 (중복 경로 등)
- **해결**: `download.php`가 3가지 경로 패턴 자동 시도

**문제 5**: 관리자 페이지에서 파일 목록 안 보임
- **원인**: `uploaded_files` 컬럼이 NULL이거나 빈 문자열
- **해결**: `add_to_basket.php`가 빈 배열이라도 `json_encode([])`로 저장

### 상세 문서

**완전한 가이드**: [업로드다운로드251118.md](업로드다운로드251118.md)
- 전체 시스템 아키텍처
- 9개 품목별 구현 세부사항
- 복구 스크립트 상세 가이드
- 테스트 시나리오 및 결과
- 디버깅 가이드

**검증 스크립트**:
- [verify_upload_code.ps1](verify_upload_code.ps1) - PowerShell 자동 검증
- [verify_upload_code_README.md](verify_upload_code_README.md) - 사용법

## 🛠️ Common Development Tasks

### Local Development Setup
```bash
# Start XAMPP services (Windows)
C:\xampp\xampp-control.exe
# Or via command line:
C:\xampp\apache_start.bat
C:\xampp\mysql_start.bat

# Linux/WSL environment (current setup)
# Apache and MySQL run via system services
sudo service apache2 start
sudo service mysql start

# Access site
http://localhost/mlangprintauto/[product]/

# Database admin
http://localhost/phpmyadmin/

# Check environment detection
http://localhost/?debug_db=1
```

### FTP Deployment (Production) - dsp1830.shop

**🔴 중요: FTP 업로드 규칙 (2024-11-24 검증됨)**

| 항목 | 값 |
|------|------|
| **FTP 서버** | `ftp://dsp1830.shop` |
| **User** | `dsp1830` |
| **Password** | `ds701018` |
| **Root Path** | `/` (FTP 루트 = 웹루트) |

**⚠️ 경로 주의사항:**
- ✅ FTP 루트(`/`) = 웹 루트 (동일함)
- ❌ `/www/` 폴더는 웹루트가 아님 (별도 폴더)
- ❌ `/public_html/` 경로 - 존재하지 않음
- ❌ `dsp114.com` - 레거시 서버, 업로드 금지

**✅ 올바른 업로드 예시:**
```bash
# 단일 파일 업로드 (FTP 루트 = 웹루트)
curl -T "/var/www/html/mlangprintauto/shop/cart.php" \
  -u "dsp1830:ds701018" \
  "ftp://dsp1830.shop/mlangprintauto/shop/cart.php"

# 새 폴더 생성이 필요한 경우 (--ftp-create-dirs)
curl -T "/var/www/html/mlangprintauto/quote/index.php" \
  --ftp-create-dirs \
  -u "dsp1830:ds701018" \
  "ftp://dsp1830.shop/mlangprintauto/quote/index.php"

# admin 파일 업로드
curl -T "/var/www/html/admin/mlangprintauto/admin.php" \
  -u "dsp1830:ds701018" \
  "ftp://dsp1830.shop/admin/mlangprintauto/admin.php"

# 여러 파일 일괄 업로드 (for 루프)
for file in /var/www/html/mlangprintauto/quote/includes/*; do
    filename=$(basename "$file")
    curl -s -T "$file" --ftp-create-dirs \
      -u "dsp1830:ds701018" \
      "ftp://dsp1830.shop/mlangprintauto/quote/includes/$filename"
done
```

**디렉토리 구조 (dsp1830.shop FTP 루트):**
```
/                              ← FTP 루트 = 웹루트
├── mlangprintauto/            ← 제품 페이지
│   ├── shop/                  ← 장바구니
│   ├── quote/                 ← 견적서 시스템
│   ├── inserted/              ← 전단지
│   ├── namecard/              ← 명함
│   └── ...
├── mlangorder_printauto/      ← 주문 처리
├── admin/                     ← 관리자
│   └── mlangprintauto/        ← 제품 관리
├── includes/                  ← 공통 모듈
├── db.php                     ← DB 연결
└── www/                       ← (별도 폴더, 웹루트 아님!)
```

**업로드 확인:**
```bash
# FTP 디렉토리 목록 확인
curl -s --list-only -u "dsp1830:ds701018" "ftp://dsp1830.shop/mlangprintauto/quote/"

# HTTP 접근 테스트 (200이면 성공)
curl -s -o /dev/null -w "%{http_code}" "http://dsp1830.shop/mlangprintauto/quote/index.php"
```

**FileZilla 설정:**
- Host: `dsp1830.shop`
- Protocol: FTP
- Port: 21
- Remote path: `/www/`

### Testing
```bash
# No formal test suite - manual testing only
# Test files exist in root (test-*.html, test-*.js)

# Manual test pages
http://localhost/test-additional-options.html
http://localhost/test_sticker_gallery.html

# Debug specific product
http://localhost/mlangprintauto/inserted/?debug=1
```

### Testing Price Calculations
```bash
# Test with debug mode
http://localhost/mlangprintauto/inserted/?debug=1

# Check database connection
http://localhost/mlangprintauto/inserted/?debug_db=1

# Verify calculator response
# Open browser console and check:
console.log(window.currentPriceData);
```

### Debugging Cart Issues
1. Check browser console for JavaScript errors
2. Verify `window.currentPriceData` is set after calculation
3. Check Network tab for `add_to_basket.php` POST data
4. Verify `shop_temp` table has correct `st_price` values:
```sql
SELECT session_id, product_type, st_price, st_price_vat
FROM shop_temp
WHERE session_id = '[your_session_id]';
```

### Common Error Patterns

**"Undefined variable: $shop_data"**
- Use `$item['product_type']` not `$shop_data['ThingCate']`
- Each cart item has its own product_type

**"Price showing as 0 in cart"**
- Verify parameter names: `calculated_price` not just `price`
- Check `add_to_basket.php` receives correct POST data
- Ensure `window.currentPriceData` is set before cart addition

**"No data supplied for parameters in prepared statement"**
- Count bind_param type string characters vs actual parameters
- Type string: 'i' for int, 's' for string, 'd' for decimal
- Example: `mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $price)`

## 📁 Key File Locations

### Configuration
- `db.php` - Database connection with environment detection
- `config.env.php` - Environment-specific settings (EnvironmentDetector class)
- `.env` - Environment variables (not tracked in git)
- `admin/MlangPrintAuto/includes/ProductConfig.php` - Product definitions

### Product Files
- Frontend: `mlangprintauto/[product]/index.php`
- Calculator: `mlangprintauto/[product]/calculator.js`
- Cart API: `mlangprintauto/[product]/add_to_basket.php`
- Price API: `mlangprintauto/[product]/calculate_price_ajax.php`

### Shared Resources
- Upload modal: `includes/upload_modal.js`
- Auth system: `includes/auth.php`
- Additional options: `includes/AdditionalOptionsDisplay.php`
- Gallery adapter: `includes/gallery_data_adapter.php`
- Common styles: `css/common-styles.css`
- Calculator layout: `css/unified-calculator-layout.css`

### Admin
- Price management: `admin/MlangPrintAuto/ProductManager.php`
- Category admin: `admin/MlangPrintAuto/CateAdmin.php`
- Product config: `admin/MlangPrintAuto/includes/ProductConfig.php`

## 🔐 Security Practices

### SQL Injection Prevention
```php
// ALWAYS use prepared statements
$stmt = mysqli_prepare($db, "SELECT * FROM shop_temp WHERE session_id = ?");
mysqli_stmt_bind_param($stmt, "s", $session_id);
mysqli_stmt_execute($stmt);
```

### XSS Prevention
```php
// ALWAYS escape output
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

### File Upload Validation
```php
// Check file type, size, and use safe paths
$allowed = ['.jpg', '.png', '.pdf', '.ai'];
$max_size = 15 * 1024 * 1024; // 15MB
```

## 🎯 Product Types Reference (11개 제품)

⚠️ **AI 주의사항**
- `littleprint` = 포스터 제품 (레거시 코드명, 변경 불가)
- `poster` 디렉토리는 별도 프로젝트로 사용하지 않음
- 포스터 제품은 항상 **`littleprint`** 코드명을 사용할 것
- `leaflet` = 리플렛 제품 (전단지 가격 + 접지방식 추가금으로 계산)

| Code | Name (Korean) | Module Directory | Database Table | Notes |
|------|---------------|------------------|----------------|-------|
| `inserted` | 전단지 | `mlangprintauto/inserted/` | `mlangprintauto_inserted` | - |
| `envelope` | 봉투 | `mlangprintauto/envelope/` | `mlangprintauto_envelope` | - |
| `namecard` | 명함 | `mlangprintauto/namecard/` | `mlangprintauto_namecard` | - |
| `sticker` | 스티커 | `mlangprintauto/sticker_new/` | `mlangprintauto_sticker` | - |
| `msticker` | 자석스티커 | `mlangprintauto/msticker/` | `mlangprintauto_msticker` | - |
| `cadarok` | 카다록 | `mlangprintauto/cadarok/` | `mlangprintauto_cadarok` | - |
| `littleprint` | **포스터** ⚠️ | `mlangprintauto/littleprint/` | `mlangprintauto_littleprint` | - |
| `merchandisebond` | 상품권 | `mlangprintauto/merchandisebond/` | `mlangprintauto_merchandisebond` | - |
| `ncrflambeau` | NCR양식 | `mlangprintauto/ncrflambeau/` | `mlangprintauto_ncrflambeau` | - |
| `leaflet` | **리플렛** 🆕 | `mlangprintauto/leaflet/` | `mlangprintauto_inserted` + `mlangprintauto_leaflet_fold` | 전단지 가격 + 접지 추가금 |

### 리플렛 제품 특징 (New Module - 2025-11-03)

**가격 계산 방식:**
- **기본 가격**: `mlangprintauto_inserted` 테이블 사용 (전단지와 동일)
- **접지 추가금**: `mlangprintauto_leaflet_fold` 테이블에서 접지방식별 추가 금액
- **코팅 추가금**: `additional_options_config` 테이블에서 코팅 옵션 금액 (전단지와 동일)
- **오시 추가금**: `additional_options_config` 테이블에서 오시 옵션 금액 (전단지와 동일)
- **최종 가격**: 기본 가격 + 접지 추가금 + 코팅 추가금 + 오시 추가금 + 기타 옵션

**접지방식 옵션 (6가지):**
| 접지방식 | 추가 금액 | 설명 |
|---------|---------|------|
| 2단접지 | +40,000원 | 반으로 접는 기본 접지 |
| 3단접지 | +40,000원 | 3등분으로 접는 접지 |
| 4단접지 | +80,000원 | 4등분으로 접는 접지 |
| 병풍접지 | +80,000원 | 지그재그로 접는 병풍형 |
| 대문접지 | +100,000원 | 양쪽을 안으로 접는 형태 |
| Z접지 | +60,000원 | Z자 형태로 접는 접지 |

**코팅 옵션 (4가지, 전단지와 동일):**
| 코팅 종류 | 추가 금액 |
|---------|---------|
| 단면유광코팅 | +80,000원 |
| 양면유광코팅 | +160,000원 |
| 단면무광코팅 | +80,000원 |
| 양면무광코팅 | +160,000원 |

**오시 옵션 (3가지, 전단지와 동일):**
| 오시 종류 | 추가 금액 |
|---------|---------|
| 1줄 | +32,000원 |
| 2줄 | +32,000원 |
| 3줄 | +40,000원 |

**핵심 파일:**
- [mlangprintauto/leaflet/calculate_price_ajax.php](mlangprintauto/leaflet/calculate_price_ajax.php) - 가격 계산 (inserted + fold + coating + creasing)
- [mlangprintauto/leaflet/get_fold_types.php](mlangprintauto/leaflet/get_fold_types.php) - 접지방식 옵션 API
- [mlangprintauto/leaflet/get_coating_types.php](mlangprintauto/leaflet/get_coating_types.php) - 코팅 옵션 API
- [mlangprintauto/leaflet/get_creasing_types.php](mlangprintauto/leaflet/get_creasing_types.php) - 오시 옵션 API
- [admin/MlangPrintAuto/includes/ProductConfig.php](admin/MlangPrintAuto/includes/ProductConfig.php) - 리플렛 설정 (lines 165-186)

**장점:**
- ✅ 기존 749개 전단지 가격 데이터 재활용
- ✅ 관리자는 접지방식 6개 옵션만 관리
- ✅ 전단지와 완전히 독립된 모듈
- ✅ 전단지 가격 변경 시 리플렛도 자동 반영

## 📚 Additional Documentation

Comprehensive documentation exists in `CLAUDE_DOCS/` directory:
- `01_CORE/` - Project overview & core rules
- `02_ARCHITECTURE/` - Technical architecture details
- `03_PRODUCTS/` - Product system & design guides
- `04_OPERATIONS/` - Admin system & security
- `05_DEVELOPMENT/` - Frontend UI/UX & troubleshooting
  - `MCP_Installation_Guide.md` - **MCP (Model Context Protocol) 설치 및 설정 가이드**
- `06_ARCHIVE/` - Completed projects & reference guides

See [CLAUDE_DOCS/INDEX.md](CLAUDE_DOCS/INDEX.md) for full documentation structure.

## 🎨 Frontend Layout System

### Unified Product Layout Structure

All 10 product pages use a consistent layout pattern:

```
.product-container (max-width: 1200px)
├── .top-header (navigation)
├── .page-title (product title)
└── .product-content (grid: 1fr 1fr)
    ├── .product-gallery (left 50%)
    │   └── .gallery-container
    │       └── .lightbox-viewer
    └── .product-calculator (right 50%)
        └── <form> with .options-grid
```

**Visual Structure**: See [layout_structure.txt](layout_structure.txt) for detailed ASCII diagram.

### CSS Loading Order (Critical)

CSS files load in this order for proper cascade:

1. `product-layout.css` - Base structure
2. `unified-price-display.css` - Price display
3. `compact-form.css` - Form grids
4. `unified-gallery.css` - Gallery system
5. `btn-primary.css` - Buttons
6. `[product]-inline-styles.css` - Product-specific (if exists)
7. **`common-styles.css`** - ⚠️ MUST load last (highest priority)

### CSS Specificity Without !important

To override styles without `!important`, use:

```css
/* ❌ Wrong - requires !important */
.gallery-container { background: transparent !important; }

/* ✅ Right - specific selector wins naturally */
.product-content .product-gallery .gallery-container {
    background: transparent;
}
```

**Rule**: More specific selectors (longer chains) override general ones when loaded in same order.

### Responsive Breakpoints

- **Mobile** (< 768px): `.product-content` stacks vertically (grid: 1fr)
- **Desktop** (≥ 768px): `.product-content` side-by-side (grid: 1fr 1fr)

## 🖼️ Gallery System Rules

### Architecture Overview

**Two-Tier Gallery System**:
1. **Main Gallery** (4 thumbnails): Displayed on product page left side with zoom
2. **Modal Gallery** ("샘플 더보기"): Popup window with paginated images

### Critical Files

**Backend (PHP)**:
- [includes/gallery_data_adapter.php](includes/gallery_data_adapter.php): Central data loader for all products
- [includes/new_gallery_wrapper.php](includes/new_gallery_wrapper.php): Main gallery renderer with zoom
- [popup/proof_gallery.php](popup/proof_gallery.php): Modal popup gallery with pagination

**Frontend (JavaScript)**:
- [js/common-gallery-popup.js](js/common-gallery-popup.js): Modal popup trigger and category mapping
- Product-specific calculators load gallery via `simple_gallery_include.php`

**Image Storage**:
- `/ImgFolder/sample/{product}/`: Static sample images (priority 1)
- `/mlangorder_printauto/upload/{orderNo}/{filename}`: Real order images (priority 2)
- Product-specific galleries: `/ImgFolder/{product}/gallery/` (e.g., leaflet, sticker)

### Implementation Pattern

**Step 1: Add Product Gallery**
```php
// In product index.php (around line 200)
$gallery_product = 'product_name'; // e.g., 'inserted', 'leaflet'
if (file_exists('../../includes/simple_gallery_include.php')) {
    include '../../includes/simple_gallery_include.php';
}
```

**Step 2: Add Data Loader Function** (if special handling needed)
```php
// In includes/gallery_data_adapter.php
function load_{product}_gallery_unified($thumbCount = 4, $modalPerPage = 12) {
    $items = [];
    $galleryPath = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/{product}/gallery/';
    $webPath = '/ImgFolder/{product}/gallery/';

    if (is_dir($galleryPath)) {
        $files = scandir($galleryPath);
        foreach ($files as $file) {
            if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $items[] = [
                    'src' => $webPath . $file,
                    'alt' => pathinfo($file, PATHINFO_FILENAME),
                    'title' => pathinfo($file, PATHINFO_FILENAME),
                    'orderNo' => null,
                    'type' => 'gallery'
                ];
            }
        }
    }

    return array_slice($items, 0, $thumbCount);
}
```

**Step 3: Register Loader in gallery_data_adapter.php**
```php
// In load_gallery_items() function
if ($product === 'product_name') {
    return load_product_gallery_unified($thumbCount, $modalPerPage);
}
```

**Step 4: Add Category Mapping for Modal**
```javascript
// In js/common-gallery-popup.js
const categoryMap = {
    'product_name': '한글카테고리명',  // e.g., 'leaflet': '전단지'
    // ...
};
```

### SQL Query Pattern for Modal (proof_gallery.php)

**⚠️ CRITICAL: Always use prepared statements correctly**

```php
// LIKE 검색 (스티커, 자석스티커 등)
if ($db_types === 'LIKE') {
    // 직접 실행 (placeholder 없음)
    $result = mysqli_query($connect, $sql);
}
// 정확한 매칭 (명함, 전단지 등)
else {
    // Prepared statement 사용
    $stmt = mysqli_prepare($connect, $sql);
    if ($stmt && !empty($type_params)) {
        $types = str_repeat('s', count($type_params));
        mysqli_stmt_bind_param($stmt, $types, ...$type_params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }
}
```

**Common Error**: Using `mysqli_query()` with placeholder(`?`) → SQL syntax error
**Solution**: Use prepared statement with `mysqli_prepare()` + `mysqli_stmt_bind_param()`

### Testing Gallery Implementation

```bash
# Test main gallery data loading
php -r "
include 'db.php';
include 'includes/gallery_data_adapter.php';
\$items = load_gallery_items('product_name', null, 4, 12);
echo count(\$items) . ' images loaded\n';
"

# Test modal popup
curl -s 'http://localhost/popup/proof_gallery.php?cate=카테고리명&debug=1' | grep "Total found"

# Verify category mapping
curl -s 'http://localhost/mlangprintauto/product/' | grep 'data-product='
```

### Function Declaration Order

**⚠️ CRITICAL**: PHP requires functions to be declared before use

```php
// ❌ Wrong - function called before definition
function load_gallery_items() {
    return load_leaflet_gallery_unified(); // ← Called here
}

function load_leaflet_gallery_unified() { // ← Defined later (ERROR!)
    // ...
}

// ✅ Right - function defined before use
function load_leaflet_gallery_unified() { // ← Defined first
    // ...
}

function load_gallery_items() {
    return load_leaflet_gallery_unified(); // ← Called after definition (OK)
}
```

**Best Practice**: Place all helper functions at the top of the file before main functions

### Dual-Source Gallery System (2025-11-26)

**목적**: 교정게시판 모달 갤러리에서 큐레이티드 갤러리 이미지와 2023-2024년 고객 주문 이미지를 함께 표시

**구현 파일**: [popup/proof_gallery.php](popup/proof_gallery.php)

#### 시스템 아키텍처

**3단계 로딩 프로세스**:
1. **갤러리 폴더 이미지 로드** - 수작업으로 선별한 샘플 이미지
2. **DB 주문 이미지 로드** - 2023-01-01 ~ 2024-12-31 기간의 실제 고객 작업물
3. **병합 및 페이지네이션** - 두 소스를 통합하여 24개/페이지로 표시

#### 갤러리 폴더 매핑 (9개 카테고리)

```php
$gallery_folders = [
    '명함' => ['/ImgFolder/namecard/gallery/'],
    '스티커' => ['/ImgFolder/sticker/gallery/'],
    '봉투' => ['/ImgFolder/envelope/gallery/'],
    '전단지' => ['/ImgFolder/inserted/gallery/'],
    '포스터' => ['/ImgFolder/littleprint/gallery/'],
    '카탈로그' => ['/ImgFolder/cadarok/gallery/', '/ImgFolder/leaflet/gallery/'], // 다중 폴더 지원
    '상품권' => ['/ImgFolder/merchandisebond/gallery/'],
    '자석스티커' => ['/ImgFolder/msticker/gallery/'],
    '양식지' => ['/ImgFolder/ncrflambeau/gallery/'],
];
```

**특징**: 카탈로그는 cadarok + leaflet 2개 폴더 병합 지원

#### DB 주문 이미지 로드 규칙

**날짜 필터** (고정):
```php
$date_filter = "date >= '2023-01-01' AND date <= '2024-12-31'";
```

**타입 매핑**:
```php
$type_mapping = [
    '명함' => ['NameCard'],
    '전단지' => ['전단지'],
    '스티커' => 'LIKE', // LIKE 검색: 투명스티커, 유포지스티커 등 모든 변형 대응
    '상품권' => ['쿠폰', '상품권', '금액쿠폰'],
    '봉투' => ['봉투', '소봉투', '대봉투', '자켓봉투', '자켓소봉투', '중봉투', '창봉투'],
    '양식지' => ['NCR 양식지', '양식지', '거래명세서'],
    '카탈로그' => ['카다록', '카다로그', 'leaflet', 'cadarok'],
    '포스터' => ['포스터', 'LittlePrint', 'littleprint', 'poster', 'Poster'],
    '자석스티커' => 'LIKE' // LIKE 검색: 37가지 변형 대응
];
```

**스티커 타입 특수 처리** (스티카 오타 포함):
```php
if ($cate === '스티커') {
    // 스티커 + 스티카 모두 포함, 자석 제외 (자석스티커는 별도 카테고리)
    $type_where = "((Type LIKE '%스티커%' OR Type LIKE '%스티카%') AND Type NOT LIKE '%자석%')";
}
```

#### 이미지 배열 구조

**갤러리 이미지**:
```php
[
    'type' => 'gallery',
    'url' => '/ImgFolder/namecard/gallery/sample01.jpg',
    'filename' => 'sample01.jpg'
]
```

**주문 이미지**:
```php
[
    'type' => 'order',
    'order_no' => 84180,
    'url' => '/mlangorder_printauto/upload/84180/9820251125170417.jpg'
]
```

**병합 및 페이지네이션**:
```php
// 1. 갤러리 이미지 로드
$all_images = [...gallery_images];

// 2. DB 주문 이미지 추가
$all_images = array_merge($all_images, $db_images);

// 3. 총 개수 계산
$total = count($all_images);
$pages = max(1, ceil($total / 24));

// 4. 페이지네이션 적용
$offset = ($page - 1) * 24;
$paged_images = array_slice($all_images, $offset, 24);
```

#### 실제 테스트 결과 (2025-11-26)

| 카테고리 | 갤러리 | 2023-2024 주문 | 총합 | 페이지 |
|---------|-------|---------------|------|--------|
| 명함 | 44 | 13 | 57 | 3 |
| 스티커 | 155 | 1,478 | 1,633 | 69 |
| 봉투 | 6 | 58 | 64 | 3 |
| 전단지 | 76 | 506 | 582 | 25 |
| 포스터 | 1 | 8 | 9 | 1 |
| 카탈로그 | 20 | 11 | 31 | 2 |
| 상품권 | 8 | 14 | 22 | 1 |
| 자석스티커 | 5 | 10 | 15 | 1 |
| 양식지 | 17 | 38 | 55 | 3 |
| **전체** | **332** | **2,136** | **2,468** | - |

**주요 발견사항**:
- 스티커: 1,633개 (최다) - "스티카" 오타 포함으로 정확도 향상
- 전단지: 582개 (2위) - 가장 많이 주문되는 품목
- 카탈로그: 2개 폴더 병합 (cadarok 3개 + leaflet 17개)

#### 디버그 모드

**디버그 URL**:
```
http://dsp1830.shop/popup/proof_gallery.php?cate=스티커&debug=1
```

**디버그 출력** (HTML 주석):
```html
<!-- DEBUG: Category = 스티커, Type WHERE = ((Type LIKE '%스티커%' OR Type LIKE '%스티카%') AND Type NOT LIKE '%자석%') -->
<!-- DEBUG: Gallery images = 155 -->
<!-- DEBUG: Order images = 1478 -->
<!-- DEBUG: Total = 1633, Pages = 69 -->
```

#### 핵심 원칙

1. **갤러리 우선**: 갤러리 폴더 이미지를 먼저 표시 (큐레이티드 품질)
2. **2023-2024 기간 고정**: 최근 2년 작업물만 표시 (프라이버시 고려)
3. **실제 파일 검증**: `file_exists()` 체크로 깨진 링크 방지
4. **LIKE vs 정확 매칭**: 스티커/자석스티커는 LIKE, 나머지는 배열 정확 매칭
5. **다중 폴더 지원**: 카탈로그처럼 여러 제품 폴더 병합 가능

#### 유지보수 가이드

**갤러리 이미지 추가**:
1. 파일을 `/ImgFolder/{product}/gallery/` 폴더에 업로드
2. 자동 감지 - 별도 코드 수정 불필요
3. JPG, JPEG, PNG, GIF, WEBP 지원

**새 카테고리 추가**:
1. `$gallery_folders` 배열에 카테고리 추가
2. `$type_mapping`에 DB Type 매핑 추가
3. `js/common-gallery-popup.js`의 `categoryMap`에 영문명 추가

**날짜 범위 변경** (필요시):
```php
// 현재: 2023-2024 고정
$date_filter = "date >= '2023-01-01' AND date <= '2024-12-31'";

// 예시: 최근 1년
$date_filter = "date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
```

## 🔄 Recent Critical Fixes (2025-12-10)

### 주문 완료 페이지 unit 필드 적용 ✅ COMPLETED
**날짜**: 2025-12-10
**목적**: 하드코딩된 '매' 단위를 DB unit 필드로 전환하여 제품별 올바른 단위 표시

**문제점**:
- 주문 완료 페이지에서 전단지가 "0.5매"로 표시 (올바른 표시: "0.5연")
- 3개 파일에서 총 14곳이 하드코딩된 '매' 사용

**수정된 파일 (3개)**:

| 파일 | 수정 개소 | 배포 |
|------|---------|------|
| `mlangorder_printauto/ProcessOrder_unified.php` | 5곳 | ✅ FTP 완료 |
| `mlangorder_printauto/OrderComplete_universal.php` | 5곳 | ✅ FTP 완료 |
| `mlangorder_printauto/OrderComplete_unified.php` | 4곳 | ✅ FTP 완료 |

**수정 패턴**:
```php
// 이전: 하드코딩
"수량: " . number_format($amount) . "매\n"

// 변경: DB unit 필드 사용
"수량: " . number_format($amount) . ($order['unit'] ?? '매') . "\n"
// 또는 shop_temp에서
"수량: " . number_format($amount) . ($item['unit'] ?? '매') . "\n"
```

**제품별 unit 값**:

| 제품 | unit 값 | add_to_basket.php |
|------|---------|-------------------|
| 전단지/리플렛 | 연 | ✅ 적용됨 |
| 명함/봉투/스티커/상품권/포스터/자석스티커 | 매 | ✅ 적용됨 |
| 양식지 | 매/권 | ✅ 적용됨 |
| 카다록 | 부 | ✅ 적용됨 |

**데이터 흐름**:
```
① add_to_basket.php에서 unit 저장 (shop_temp.unit)
② ProcessOrder_unified.php에서 Type_1 JSON에 unit 포함
③ OrderComplete_*.php에서 $order['unit'] 또는 JSON에서 표시
```

**핵심 수정 위치**:

**ProcessOrder_unified.php** (5곳):
- Line 306: namecard `$item['unit'] ?? '매'`
- Line 332: envelope `$item['unit'] ?? '매'`
- Line 347: msticker `$item['unit'] ?? '매'`
- Line 369: merchandisebond `$item['unit'] ?? '매'`
- Line 394: littleprint `$item['unit'] ?? '매'`

**OrderComplete_universal.php** (5곳):
- Line 282: envelope `$order['unit'] ?? '매'`
- Line 290: namecard `$order['unit'] ?? '매'`
- Line 297: merchandisebond `$order['unit'] ?? '매'`
- Line 319: littleprint `$order['unit'] ?? '매'`
- Line 330: inserted/leaflet `$order['unit'] ?? '연'` (전단지 기본값)

**OrderComplete_unified.php** (4곳):
- Line 190: envelope `$order['unit'] ?? '매'`
- Line 210: namecard `$order['unit'] ?? '매'`
- Line 219: merchandisebond `$order['unit'] ?? '매'`
- Line 246: msticker `$order['unit'] ?? '매'`

**결과**: 전단지 주문 시 "0.5연", "1연" 등 올바른 단위 표시

---

## 🔄 Recent Critical Fixes (2025-12-07)

### Playwright E2E 테스트 - JavaScript 오류 수정 ✅ COMPLETED
**날짜**: 2025-12-07
**목적**: 전체 사이트 E2E 테스트를 통해 발견된 JavaScript 오류 수정

**테스트 범위**:
- 9개 제품 페이지 (inserted, namecard, envelope, sticker_new, msticker, cadarok, littleprint, ncrflambeau, merchandisebond)
- 가격 계산기 기능
- 장바구니 추가 기능
- 로그인/회원가입 기능
- 주문 프로세스 (장바구니 → 주문서 → 주문 완료)

---

### 1. calculatePrice 함수 미정의 오류 ✅ FIXED
**파일**: `mlangprintauto/sticker_new/index.php`
**증상**: 콘솔에 "ReferenceError: calculatePrice is not defined" 오류
**원인**: HTML onchange 핸들러에서 `calculatePrice()` 호출하지만, 실제 함수명은 `autoCalculatePrice()`

**수정 내용** (라인 ~2030):
```javascript
// Debounce 함수 - 연속 이벤트 제어
let calculationTimeout = null;
let isCalculating = false;

// calculatePrice alias - onchange 핸들러 호환성을 위해
function calculatePrice() {
    debouncedCalculatePrice();
}

function debouncedCalculatePrice(event) {
    // ... 기존 코드
}
```

**영향받는 onchange 핸들러** (6개):
- 라인 351: `<select name="sticker_type" onchange="calculatePrice()">`
- 라인 376: `<select name="sticker_material" onchange="calculatePrice()">`
- 라인 387: `<select name="printing_type" onchange="calculatePrice()">`
- 라인 395: `<select name="cutting_type" onchange="calculatePrice()">`
- 라인 418: `<select name="quantity" onchange="calculatePrice()">`
- 라인 2042: 기타 이벤트 핸들러

---

### 2. gallery-system.js 404 오류 ✅ FIXED (이전 세션)
**증상**: 콘솔에 "GET /js/gallery-system.js 404 Not Found"
**원인**: 잘못된 스크립트 경로 참조
**해결**: 올바른 경로로 수정 또는 해당 스크립트 제거

---

### 3. onSuccess 콜백 오류 ✅ FIXED (이전 세션)
**파일**: `mlangprintauto/inserted/index.php`, `mlangprintauto/namecard/index.php`
**증상**: "ReferenceError: onSuccess is not defined"
**원인**: 장바구니 추가 성공 시 호출되는 콜백 함수 미정의
**해결**: onSuccess 함수 정의 추가

---

### 4. PrintNet 미정의 오류 ✅ FIXED (이전 세션)
**파일**: `mlangprintauto/envelope/index.php`
**증상**: "ReferenceError: PrintNet is not defined"
**원인**: 레거시 PrintNet 객체 참조
**해결**: PrintNet 참조 제거 또는 대체 코드 적용

---

### 5. 스티커 jQuery/isCalculating 오류 ✅ FIXED (이전 세션)
**파일**: `mlangprintauto/sticker_new/index.php`
**증상**: jQuery 관련 오류 및 isCalculating 미정의
**원인**: jQuery 의존성 및 전역 변수 스코프 문제
**해결**: 변수 선언 위치 조정 및 의존성 정리

---

### E2E 테스트 결과 요약

| 테스트 항목 | 상태 | 비고 |
|------------|------|------|
| 메인 페이지 네비게이션 | ✅ 정상 | - |
| 9개 제품 페이지 로딩 | ✅ 정상 | - |
| 가격 계산기 (9개 제품) | ✅ 정상 | calculatePrice 수정 후 |
| 장바구니 추가 | ✅ 정상 | - |
| 로그인/회원가입 | ✅ 정상 | - |
| 주문 프로세스 | ✅ 정상 | 주문번호 #103871, #103872 생성 확인 |

**검증된 주문 프로세스**:
1. 장바구니 추가 (스티커 + 전단지)
2. 장바구니 확인 (총액: 78,100원)
3. 주문서 작성 (고객 정보, 배송지 입력)
4. 주문 완료 (주문번호 발급, 입금대기 상태)

---

### 🔴 향후 E2E 테스트 시 주의사항

**JavaScript 함수명 불일치 패턴**:
```javascript
// ❌ 문제 패턴: HTML과 JS 함수명 불일치
<select onchange="calculatePrice()">  // HTML에서 호출
function autoCalculatePrice() { }      // 실제 함수명이 다름

// ✅ 해결 패턴: alias 함수 추가
function calculatePrice() {
    debouncedCalculatePrice();  // 또는 autoCalculatePrice()
}
```

**확인 체크리스트**:
1. ✅ 콘솔에서 "ReferenceError: XXX is not defined" 확인
2. ✅ HTML onchange/onclick 핸들러의 함수명 확인
3. ✅ JS 파일에서 실제 함수명 확인
4. ✅ 불일치 시 alias 함수 추가

**Playwright MCP 테스트 명령어**:
```bash
# 브라우저 열기
mcp__playwright__browser_navigate → http://localhost/

# 콘솔 오류 확인
mcp__playwright__browser_console_messages → onlyErrors: true

# 페이지 스냅샷 (접근성 트리)
mcp__playwright__browser_snapshot
```

---

## 🔄 Recent Critical Fixes (2025-12-06)

### ImagePathResolver 날짜 필터링 시스템 구축 ✅ COMPLETED
**날짜**: 2025-12-06
**목적**: 관리자 페이지에서 교정용 이미지(2018년 이전)와 고객 원고 파일(2024년 이전)을 표시에서 제외

**구현된 파일**:
- [includes/ImagePathResolver.php](includes/ImagePathResolver.php) - 통합 경로 해석 및 날짜 필터링 클래스
- [admin/mlangprintauto/download.php](admin/mlangprintauto/download.php) - ImagePathResolver 폴백 추가
- [admin/mlangprintauto/admin.php](admin/mlangprintauto/admin.php) - 파일 표시 로직에 날짜 필터 적용

**날짜 필터링 규칙**:

| 파일 유형 | 기준 날짜 | 필터 조건 | 제외 개수 |
|---------|----------|----------|----------|
| **교정용 이미지** (ThingCate) | 2018-01-01 | `date >= '2018-01-01'` | 28,577개 |
| **고객 원고 파일** (uploaded_files) | 2024-01-01 | `date >= '2024-01-01'` | 해당 없음 |

**ImagePathResolver 핵심 메서드**:

```php
class ImagePathResolver {
    const LEGACY_CUTOFF_NO = 103700;
    const PROOF_IMAGE_CUTOFF = '2018-01-01';      // 교정용 이미지: 2018년 이후만
    const CUSTOMER_FILE_CUTOFF = '2024-01-01';   // 고객 원고: 2024년 이후만

    // 경로 해석 (레거시/신규 자동 판단)
    public static function resolve($order_no, $filename, $row = [])

    // 날짜 필터링 적용 여부 확인
    public static function shouldDisplay($file_type, $date_str)

    // ImgFolder 경로에서 연도 추출
    public static function extractYearFromPath($img_folder)

    // 파일 목록 필터링 적용
    public static function filterFilesByDate($files, $order_date, $file_type)

    // DB 레코드에서 파일 목록 가져오기 (필터링 적용)
    public static function getFilesFromRow($row, $apply_date_filter = true)

    // 필터 제외 메시지 생성
    public static function getFilterMessage($filter_result)
}
```

**경로 해석 우선순위 (폴백 체인)**:
1. ✅ 신규 시스템 JSON 메타데이터 (uploaded_files)
2. ✅ 신규 ImgFolder 경로 (_MlangPrintAuto_*)
3. ✅ 레거시 소문자 경로 (/mlangorder_printauto/upload/{no}/)
4. ✅ 레거시 대문자 경로 (/MlangOrder_PrintAuto/upload/{no}/)
5. ✅ 대소문자 무시 검색

**admin.php 파일 표시 동작**:
- 날짜 필터 적용된 파일 목록 표시
- 제외된 파일 개수 안내 메시지 표시
- 파일 유형별 그룹화 (교정/고객원고/레거시)

**핵심 원칙**:
- ✅ **표시만 제한**: 다운로드는 모든 파일 허용 (기존 링크 유지)
- ✅ **안전 모드**: 날짜 없으면 표시 (데이터 손실 방지)
- ✅ **레거시 호환**: 기존 경로 구조 완벽 지원

---

## 🔄 Recent Critical Fixes (2025-12-03)

### 1. 인쇄 규격 테이블 생성 및 관리 시스템 ✅ COMPLETED
**날짜**: 2025-12-03
**목적**: A/B 시리즈 인쇄 규격을 데이터베이스로 관리하여 동적 계산 지원

**생성된 파일**:
- [admin/create_print_sizes_table.php](admin/create_print_sizes_table.php) - 테이블 생성 및 초기 데이터 입력
- [admin/print_sizes.php](admin/print_sizes.php) - 관리자 CRUD 인터페이스
- [api/get_print_sizes.php](api/get_print_sizes.php) - REST API 엔드포인트
- [js/print-size-detector.js](js/print-size-detector.js) - 클라이언트 자동 감지 엔진

**데이터베이스 테이블** (`print_sizes`):
```sql
CREATE TABLE print_sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL,           -- 규격명 (A4, B5 등)
    width INT NOT NULL,                  -- 가로 크기 (mm)
    height INT NOT NULL,                 -- 세로 크기 (mm)
    jeolsu INT NOT NULL,                 -- 절수 (2, 4, 8 등)
    series CHAR(1) NOT NULL DEFAULT 'A', -- 시리즈 (A 또는 B)
    sheets_per_yeon INT NOT NULL,        -- 1연당 매수 (500 * jeolsu)
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
);
```

**초기 데이터** (12개 규격):
| 시리즈 | 규격 | 크기 (mm) | 절수 | 1연당 매수 |
|--------|------|-----------|------|------------|
| A | A1 | 594×841 | 1 | 500 |
| A | A2 | 420×594 | 2 | 1,000 |
| A | A3 | 297×420 | 4 | 2,000 |
| A | A4 | 210×297 | 8 | 4,000 |
| A | A5 | 148×210 | 16 | 8,000 |
| A | A6 | 105×148 | 32 | 16,000 |
| B | B1 | 728×1030 | 1 | 500 |
| B | B2 | 515×728 | 2 | 1,000 |
| B | B3 | 364×515 | 4 | 2,000 |
| B | B4 | 257×364 | 8 | 4,000 |
| B | B5 | 182×257 | 16 | 8,000 |
| B | B6 | 128×182 | 32 | 16,000 |

**장점**:
- ✅ 관리자가 규격 추가/수정 가능 (코드 변경 불필요)
- ✅ REST API로 프론트엔드 연동
- ✅ 자동 규격 감지 (입력 크기 → 가장 가까운 규격)
- ✅ 폴백 지원 (DB 연결 실패 시 기본 데이터 사용)

---

### 2. 전단지 연/매수 표기 시스템 문서화 ✅ COMPLETED
**날짜**: 2025-12-03
**문서**: [CLAUDE_DOCS/03_PRODUCTS/FLYER_QUANTITY_SYSTEM.md](CLAUDE_DOCS/03_PRODUCTS/FLYER_QUANTITY_SYSTEM.md)

**핵심 공식**:
```
매수 = 500 × 절수 × 연수
예: A4 0.5연 = 500 × 8 × 0.5 = 2,000매
```

**number_format() 소수점 처리 주의**:
```php
// ❌ 잘못된 코드 - 0.5가 1로 반올림됨
number_format($quantity_num)  // 0.5 → "1"

// ✅ 올바른 코드 - 정수/소수 구분 처리
floor($quantity_num) == $quantity_num
    ? number_format($quantity_num)      // 정수: 1 → "1"
    : number_format($quantity_num, 1)   // 소수: 0.5 → "0.5"
```

**데이터 흐름**:
```
① 고객 선택 (0.5연) → ② JSON 저장 (MY_amount: "0.50")
→ ③ 주문서 표시 (0.5연) → ④ 관리자 확인
```

**핵심 파일**:
- `OrderFormOrderTree.php` - 주문서 표시 (lines 770-820)
- `add_to_basket.php` - 장바구니 저장
- `ProcessOrder_unified.php` - 주문 처리

**검증 결과**: ✅ 주문 #103861에서 "0.5연" 정상 표시 확인

---

## 🔄 Recent Critical Fixes (2025-12-03)

### 로젠택배 주소 추출 시스템 구축
**날짜**: 2025-12-03
**목적**: 관리자 페이지에서 로젠택배 배송 양식으로 주소 데이터 내보내기

**구현된 파일 (shop_admin/):**

| 파일 | 설명 |
|------|------|
| `post_list52.php` | 로젠 주소 추출 페이지 (메인 UI) |
| `export_logen_excel.php` | XLS 엑셀 내보내기 (11개 컬럼) |
| `export_logen_format.php` | CSV 내보내기 (로젠 iLOGEN 양식) |
| `delivery_calculator.php` | 택배비/박스수량 자동 계산 헬퍼 |
| `delivery_rules_config.php` | 제품별 택배비 규칙 설정 |
| `left01.php` | 관리자 메뉴에 "로젠주소추출" 항목 추가 |

**주요 기능:**

1. **편집 가능한 필드**:
   - 박스수량: 제품 타입에 따라 자동 계산 (수동 편집 가능)
   - 택배비: 제품 타입에 따라 자동 계산 (수동 편집 가능)
   - 운임구분: 착불/선불 선택 (기본값: 착불)

2. **택배비 자동 계산 규칙** (`delivery_rules_config.php`):
   ```php
   // 제품별 규칙
   'namecard' => 박스1, 택배비 2,500원
   'merchandisebond' => 박스1, 택배비 2,500원
   'sticker' => 박스1, 택배비 2,500원
   'envelope' => 박스1, 택배비 3,000원
   'inserted_b5_16' => 박스2, 택배비 3,000원  // 16절/B5
   'inserted_90g_a4' => 박스1, 택배비 4,000원 // A4/A5
   'default' => 박스1, 택배비 3,000원
   ```

3. **Excel 내보내기 컬럼 (11개)**:
   - 수하인명, 우편번호, 주소, 전화, 핸드폰
   - 박스수량, 택배비, 운임구분, 품목명, 기타(빈값), 배송메세지

4. **Type_1 JSON 파싱**:
   - `formatted_display` 필드 자동 추출
   - 예: `{"formatted_display":"90g아트지 A4 1연"}` → "90g아트지 A4 1연"

5. **체크박스 선택 내보내기**:
   - 전체 내보내기 또는 선택한 항목만 내보내기 지원
   - JavaScript로 선택 데이터 + 수정값 JSON 전달

**배포 상태**: ✅ dsp1830.shop 웹 서버에 업로드 완료

**접속 경로**:
- 관리자 → 스티커주문 → **로젠주소추출** (`post_list52.php`)
- 또는 직접 URL: `http://dsp1830.shop/shop_admin/post_list52.php`

---

## 🔄 Recent Critical Fixes (2025-11-28 저녁)

### 1. bind_param 파라미터 개수 불일치 수정 ✅ SOLVED
**날짜**: 2025-11-28 22:39
**문제**: E2E 테스트 중 "Number of elements in type definition string doesn't match" 오류 발생

**근본 원인**:
- INSERT 쿼리: 34개 필드 (no, Type, ... envelope_additional_options_total)
- bind_param 타입 문자열: 33개 문자 (마지막 'i' 누락)
- bind_param 변수: 34개 변수

**수정 내용** ([ProcessOrder_unified.php](mlangorder_printauto/ProcessOrder_unified.php):525):
```php
// 수정 전 (33 characters)
mysqli_stmt_bind_param($stmt, 'issssssssssssssssssisiisiiiisiiii',

// 수정 후 (34 characters - 마지막 'i' 추가)
mysqli_stmt_bind_param($stmt, 'issssssssssssssssssisiisiiiisiiiii',
```

**테스트 결과**: ✅ **완전 해결**
```
주문번호  주문자이름    이메일                제품      주문일시              상태
103834   김철수       kimcs@example.com     전단지    2025-11-28 22:41:31   ✅ 정상
103833   김철수       kimcs@example.com     전단지    2025-11-28 22:41:31   ✅ 정상
103832   테스트고객   test@example.com      명함      2025-11-28 22:39:21   ✅ 정상
103831   관리자       dsp1830@naver.com     전단지    2025-11-28 22:39:09   ✅ 정상
103830   0           dsp1830@naver.com     전단지    2025-11-28 19:19:17   ❌ 오류 (수정 전)
103829   0           dsp1830@naver.com     자석스티커 2025-11-24 23:15:00   ❌ 오류 (수정 전)
```

**검증 방법**: curl을 이용한 E2E 테스트로 실제 주문 제출 및 DB 확인

---

### 2. public_html 디렉토리 오해 해소 ⚠️ LESSON LEARNED
**날짜**: 2025-11-28 22:11
**초기 문제**: ProcessOrder_unified.php 수정했는데도 name='0' 계속 발생

**초기 오해**:
```
/var/www/html/mlangorder_printauto/ProcessOrder_unified.php
└─ 수정됨: 2025-11-28 20:34 ✅ 올바른 파일

/var/www/html/public_html/mlangorder_printauto/ProcessOrder_unified.php
└─ 구버전: 2025-11-19 21:51
└─ ❌ 잘못된 추측: 이것이 웹루트라고 가정
```

**실제 확인** (apache2ctl -S):
```bash
Main DocumentRoot: "/var/www/html" (NOT public_html!)
```

**결론**:
- `/var/www/html/` = 웹루트 ✅ (처음부터 올바른 파일을 수정했음)
- `public_html/` = 단순 백업 디렉토리 (2025-11-19 자)
- **문제 원인**: 파일은 올바르게 수정되었으나, 수정 시각(20:34)이 최신 주문(19:19)보다 이후였음
- 즉, 아직 새 주문이 없어서 검증이 안 된 상태였음

**해결 방법**:
```bash
# 1. 파일 중복 검색으로 발견
find /var/www/html -name "*ProcessOrder*.php" -type f
# 결과: 2개 파일 발견

# 2. public_html 버전 확인
grep -n "bind_param.*issss" /var/www/html/public_html/mlangorder_printauto/ProcessOrder_unified.php
# 결과: 라인 483에 구버전 bind_param ('isssiiissssssssssisissiiiiisiiiii')

# 3. 올바른 버전 복사
cp /var/www/html/mlangorder_printauto/ProcessOrder_unified.php \
   /var/www/html/public_html/mlangorder_printauto/ProcessOrder_unified.php

# 4. 검증
ls -lh /var/www/html/public_html/mlangorder_printauto/ProcessOrder_unified.php
# -rw-r--r-- 1 ysung ysung 38K Nov 28 22:11
```

**🔴 CRITICAL LESSONS (반드시 지킬 것)**:
1. **파일 수정 전 DocumentRoot 확인 필수**
   ```bash
   grep -r "DocumentRoot" /etc/apache2/sites-enabled/
   ```

2. **파일 중복 검색 필수**
   ```bash
   find /var/www/html -name "*파일명*.php" -type f
   ```

3. **수정 후 타임스탬프 확인**
   ```bash
   ls -lh /path/to/file.php  # 수정 시간 확인
   ```

4. **가정하지 말고 확인할 것**
   - ❌ "/var/www/html/이 웹루트일 것이다"
   - ✅ "Apache 설정을 확인해서 DocumentRoot를 찾자"

**결과**:
- ✅ DocumentRoot 확인: `/var/www/html` (NOT public_html)
- ✅ public_html은 백업 디렉토리일 뿐
- ✅ 처음부터 올바른 파일을 수정했음
- ⏰ 타이밍 이슈: 수정(20:34) 후 아직 새 주문이 없었음

**사용자 피드백**: "정확히봐 그전엔 왜 확인을 못하고 인제 본거야?" - 정당한 질문. DocumentRoot를 먼저 확인했어야 했음.

---

### 3. 최종 검증 완료 ✅ 2025-11-28 22:41
**방법**: curl E2E 테스트로 실제 주문 4건 제출
**결과**: 모든 신규 주문에서 주문자 이름 정상 저장 확인

**수정 사항 요약**:
1. ✅ 로그인 시스템 통합 ([member/login_unified.php](member/login_unified.php))
2. ✅ bind_param 타입 문자열 수정 (33 → 34 characters)
3. ✅ E2E 테스트로 검증 완료

**사용자 지시**: "나한테 묻지말고 주문자 이름이 제대로 나올때까지 수정해서 다되면 멈춰주고 나를 불러"
**완료 시각**: 2025-11-28 22:41:31
**상태**: ✅ **완전 해결** - 주문자 이름 정상 저장 확인

---

### 2. 견적서 필드 순서 및 라벨 변경 완료
**날짜**: 2025-11-28
**목적**: 견적서 상세 페이지, 공개 페이지, PDF에서 고객명/회사명 필드 순서 변경 및 라벨 수정

**변경된 파일 (3개)**:
1. **detail.php** (관리자 상세 페이지)
2. **public/view.php** (고객용 공개 페이지)
3. **generate_pdf.php** (PDF 생성)

**변경 내용**:
- **필드 순서**: "회사명" 필드를 "담당자" 앞으로 이동
- **라벨 변경**: "고객명" → "담당자"로 변경 (상단 정보 + 하단 footer)
- **일관성**: 3개 페이지 모두 동일한 구조로 통일

**수정 예시** (detail.php):
```php
// 이전:
<tr>
    <th>고객명</th>
    <td><?php echo htmlspecialchars($quote['customer_name']); ?></td>
    <th>회사명</th>
    <td><?php echo htmlspecialchars($quote['customer_company'] ?: '-'); ?></td>
</tr>

// 수정 후:
<tr>
    <th>회사명</th>
    <td><?php echo htmlspecialchars($quote['customer_company'] ?: '-'); ?></td>
    <th>담당자</th>
    <td><?php echo htmlspecialchars($quote['customer_name']); ?></td>
</tr>
```

**테스트 결과**: ✅ 3개 파일 모두 프로덕션 배포 완료

---

### 2. 견적서 개정판 가격 반올림 버그 수정
**날짜**: 2025-11-28
**파일**: `mlangprintauto/quote/api/create_revision.php`
**문제**: 견적서 개정판 생성 시 공급가액 손실 (83,300원 → 83,000원)

**원인 분석**:
```php
// 원래 공급가: 83,300원
// 수량: 500개
// 원래 단가: 83,300 ÷ 500 = 166.6원

// 문제 발생:
$unitPrice = intval($item['unit_price'] ?? 0);  // 166.6 → 166
$supplyPrice = intval($quantity * $unitPrice);   // 166 × 500 = 83,000 (300원 손실!)
```

**해결 방법** (lines 87-98):
```php
// 금액 계산 (공급가 우선 - 사용자 입력값 그대로 사용)
$supplyTotal = 0;
$vatTotal = 0;

foreach ($data['items'] as $item) {
    // 공급가를 직접 사용 (재계산하지 않음)
    $supplyPrice = intval($item['supply_price'] ?? 0);
    $vat = intval(round($supplyPrice * 0.1));

    $supplyTotal += $supplyPrice;
    $vatTotal += $vat;
}
```

**핵심 개선**:
- ❌ 이전: 단가 × 수량으로 재계산 (precision loss)
- ✅ 수정: 사용자 입력 공급가를 직접 사용 (정확한 금액 유지)

**테스트 결과**: ✅ 83,300원 → 83,300원 정확히 유지

---

### 3. 견적서 수동 편집 보호 기능 추가
**날짜**: 2025-11-28
**파일**: `mlangprintauto/quote/revise.php`
**목적**: 사용자가 공급가를 수동 편집한 경우 자동 재계산으로부터 보호

**구현 내용** (lines 452-489):

**기능 1: 공급가 자동 계산 (수량/단가 변경 시)**
```javascript
function calculateSupplyPrice(element) {
    const row = element.closest('tr');
    const supplyPriceInput = row.querySelector('.supply-price-input');

    // 공급가가 수동 수정되었으면 자동 계산 건너뜀
    if (supplyPriceInput.dataset.manualEdit === 'true') {
        return;
    }

    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    const supplyPrice = Math.floor(quantity * unitPrice);

    supplyPriceInput.value = supplyPrice;
}
```

**기능 2: 단가 역계산 (공급가 수동 변경 시)**
```javascript
function calculateUnitPrice(element) {
    const row = element.closest('tr');
    const supplyPriceInput = row.querySelector('.supply-price-input');

    // 공급가가 수동으로 수정되었음을 표시
    supplyPriceInput.dataset.manualEdit = 'true';

    const quantity = parseFloat(quantityInput.value) || 0;
    const supplyPrice = parseFloat(supplyPriceInput.value) || 0;

    if (quantity > 0) {
        const unitPrice = Math.floor(supplyPrice / quantity);
        unitPriceInput.value = unitPrice;
    }
}
```

**동작 흐름**:
1. 사용자가 공급가 수정 (예: 83,300 → 90,000)
2. `calculateUnitPrice()` 실행 → 단가 자동 역산 (166 → 180)
3. `dataset.manualEdit = 'true'` 플래그 설정
4. 이후 수량/단가 변경 시 → `calculateSupplyPrice()`가 플래그 확인 후 건너뜀
5. **결과**: 수동 편집한 공급가 90,000원이 계속 유지됨

**장점**:
- ✅ 공급가 = 단가 × 수량 관계 유지
- ✅ 수동 편집한 공급가는 보호됨
- ✅ 단가는 공급가 기준으로 자동 역산

**테스트 결과**: ✅ 프로덕션 배포 완료

---

## 🔄 Recent Critical Fixes (2025-11-26)

### 듀얼 소스 갤러리 시스템 구축
**날짜**: 2025-11-26
**파일**: `popup/proof_gallery.php`
**목적**: 큐레이티드 갤러리 + 2023-2024 고객 주문 이미지 통합 표시

**구현 내용**:
- 갤러리 폴더 이미지 로드 (332개)
- 2023-2024 DB 주문 이미지 로드 (2,136개)
- 스티커 타입 매핑 업데이트: "스티카" 오타 포함
- 통합 배열 병합 및 페이지네이션
- 9개 카테고리 테스트 완료 (총 2,468개 이미지)

**테스트 결과**: 전체 9개 카테고리 정상 작동 확인

---

## 🔄 Recent Critical Fixes (2025-11-21)

### 1. 6개 제품 StandardUploadHandler Phase 2 완료 및 bind_param 오류 수정
**날짜**: 2025-11-21
**목적**: envelope, sticker_new, cadarok, msticker, merchandisebond, littleprint 제품의 StandardUploadHandler 전환 완료 및 장바구니 저장 오류 수정

**수정된 제품 (6개)**:
1. **envelope (봉투)** - StandardUploadHandler 적용 완료
2. **sticker_new (스티커)** - bind_param 오류 수정 (16개 → 17개)
3. **cadarok (카다록)** - StandardUploadHandler 적용 완료
4. **msticker (자석스티커)** - bind_param 타입 오류 수정 ('s' → 'i')
5. **merchandisebond (상품권)** - bind_param 오류 수정 (17개 → 16개)
6. **littleprint (포스터)** - StandardUploadHandler 적용 완료

**bind_param 오류 패턴 및 수정**:

#### 오류 1: sticker_new (타입 문자 개수 부족)
```php
// 문제: 17개 파라미터인데 16개 타입 문자만 제공
// 오류 메시지: "장바구니 저장 중 오류가 발생했습니다"

// 이전 (라인 181): ❌ WRONG
mysqli_stmt_bind_param($stmt, "sssssssiiissssss", ...); // 16 chars

// 수정 (라인 181): ✅ CORRECT
mysqli_stmt_bind_param($stmt, "ssssssssiisssssss", ...); // 17 chars
    $session_id, $product_type, $jong, $garo, $sero, $mesu, $uhyung, $domusong,
    $st_price, $st_price_vat, $customer_name, $customer_phone,
    $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder);
```

#### 오류 2: merchandisebond (타입 문자 개수 초과)
```php
// 문제: 16개 파라미터인데 17개 타입 문자 제공
// 오류 메시지: "장바구니 저장에 실패했습니다"

// 이전: ❌ WRONG (17 chars)
mysqli_stmt_bind_param($stmt, "ssssssssiisisssss", ...);

// 수정 (라인 170-188): ✅ CORRECT (16 chars)
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype, st_price, st_price_vat, premium_options, premium_options_total, work_memo, upload_method, uploaded_files, ThingCate, ImgFolder)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

mysqli_stmt_bind_param($stmt, "sssssssiisisssss",
    $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype,
    $price, $vat_price, $premium_options_json, $premium_total,
    $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder);
```

#### 오류 3: msticker (잘못된 타입 지정)
```php
// 문제: $vat_price (정수)를 's' (문자열)로 바인딩
// 오류 메시지: "파일 업로드 및 주문하기 버튼이 작동하지않아"

// 이전 (라인 111): ❌ WRONG - 9th position is 's'
mysqli_stmt_bind_param($stmt, "ssssssssissssss", ...);
//                                         ^
//                                    9th: 's' (WRONG for integer)

// 수정 (라인 112): ✅ CORRECT - 9th position is 'i'
// 15개 파라미터: 7 strings (session~ordertype) + 2 ints (price, vat_price) + 6 strings (options~json)
mysqli_stmt_bind_param($stmt, "sssssssiissssss",
//                                     ^^
//                               8th, 9th: 'ii' (CORRECT for integers)
    $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype,
    $price, $vat_price, $selected_options, $work_memo, $upload_method,
    $upload_folder_db, $thing_cate, $files_json);
```

**핵심 교훈 - bind_param 검증 체크리스트**:
1. ✅ **파라미터 개수 세기**: INSERT 쿼리의 `?` 개수 = 타입 문자 개수
2. ✅ **타입 정확성**: 정수는 'i', 문자열은 's', 실수는 'd', BLOB은 'b'
3. ✅ **주석 추가**: 복잡한 쿼리는 파라미터 설명 주석 필수
4. ✅ **일관성 유지**: price, vat_price는 항상 정수(i)로 처리

**테스트 결과**:
- ✅ sticker_new: 장바구니 추가 성공
- ✅ merchandisebond: 장바구니 추가 성공
- ✅ msticker: 파일 업로드 및 주문 버튼 정상 작동
- ✅ envelope, cadarok, littleprint: 기존 정상 작동 유지
- ✅ **전체 6개 제품 테스트 통과**

### 2. 자석스티커 갤러리 이미지 표시 수정
**날짜**: 2025-11-21
**문제**: 자석스티커 제품 페이지에서 갤러리 섹션의 썸네일과 메인 이미지가 표시되지 않음

**원인**:
- `gallery_data_adapter.php`에 msticker 전용 로더가 없음
- 기본 로직이 `/ImgFolder/sample/msticker/` 경로를 찾으려 했으나, 실제 이미지는 `/ImgFolder/msticker/gallery/`에 위치
- 5개 이미지 파일 존재:
  - 경남소방본부통자석_자동제세동기사용법_소방안전자가진단_2종 각13000매OL.jpg
  - 더이음부동산종이자석.jpg
  - 만사성_종이자석.jpg
  - 순희네분식종이자석.jpg
  - 치킨신드롬(전체자석).jpg

**해결 방법** - `/var/www/html/includes/gallery_data_adapter.php`:

1. **새 함수 추가** (lines 43-72):
```php
/**
 * 자석스티커 갤러리 통합 로드 함수
 * ImgFolder/msticker/gallery/ 폴더의 이미지를 로드
 */
function load_msticker_gallery_unified($thumbCount = 4, $modalPerPage = 12) {
    $items = [];
    $galleryPath = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/msticker/gallery/';
    $webPath = '/ImgFolder/msticker/gallery/';

    if (is_dir($galleryPath)) {
        $files = scandir($galleryPath);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $items[] = [
                        'src' => $webPath . $file,
                        'alt' => pathinfo($file, PATHINFO_FILENAME),
                        'title' => pathinfo($file, PATHINFO_FILENAME),
                        'orderNo' => null,
                        'type' => 'gallery'
                    ];
                }
            }
        }
    }

    // 썸네일 개수만큼 반환 (메인갤러리용)
    return array_slice($items, 0, $thumbCount);
}
```

2. **특수 케이스 처리 추가** (lines 97-100):
```php
// 자석스티커는 전용 ImgFolder 갤러리 사용
if ($product === 'msticker') {
    return load_msticker_gallery_unified($thumbCount, $modalPerPage);
}
```

**검증**:
- ✅ 5개 이미지 파일 정상 로드
- ✅ HTML에 올바른 경로 포함 (`/ImgFolder/msticker/gallery/...`)
- ✅ HTTP 200 응답 (이미지 접근 가능)
- ✅ 갤러리 섹션 정상 렌더링

**패턴**: 리플렛(leaflet)과 동일한 구조 적용

### 3. 관리자 파일 표시 로직 간소화
**날짜**: 2025-11-21
**파일**: `/var/www/html/admin/mlangprintauto/admin.php`
**목적**: 파일 표시 로직 단순화 및 성능 개선

**변경 사항**:
- **코드 길이**: ~200 lines → ~133 lines (**33% 감소**)
- **JSON 파싱**: 한 번만 수행 (이전: 여러 번)
- **4단계 폴백 시스템 명확화**:
  1. ✅ JSON uploaded_files (StandardUploadHandler 표준화된 주문)
  2. ✅ ImgFolder 디렉토리 스캔 (레거시 주문)
  3. ✅ mlangorder_printauto/upload/{no} (초기 레거시 경로)
  4. ⚠️ 파일 없음 경고 표시

**핵심 개선** (lines 710-843):
```php
// ✅ Step 1: uploaded_files JSON 파싱 (한 번만)
$uploaded_files = [];
if (!empty($row['uploaded_files']) && $row['uploaded_files'] !== '0') {
    $decoded = json_decode($row['uploaded_files'], true);
    if (is_array($decoded)) {
        $uploaded_files = $decoded;
    }
}

// ✅ Step 2: JSON에서 파일 표시 (StandardUploadHandler 표준화된 주문)
if (count($uploaded_files) > 0) {
    echo "<div style='margin-top: 10px; color: #28a745; font-weight: bold;'>✅ 표준화된 파일 정보:</div>";
    foreach ($uploaded_files as $file_info) {
        // 파일 정보 표시 (다운로드 링크, 크기, 대표 파일 표시)
    }
}

// ✅ Step 3: 폴백 - ImgFolder 디렉토리 스캔
if ($total_file_count == 0 && !empty($row['ImgFolder'])) {
    // 레거시 경로 스캔
}

// ✅ Step 4: 추가 폴백 - mlangorder_printauto/upload/{no}
if ($total_file_count == 0) {
    // 초기 업로드 폴더 스캔
}
```

**장점**:
- ✅ 가독성 향상 (명확한 단계 구분)
- ✅ 성능 개선 (중복 JSON 파싱 제거)
- ✅ 유지보수 용이 (4단계 폴백 명확화)
- ✅ 디버깅 편의성 (단계별 구분 메시지)

### 4. 다운로드 절대 경로 지원 추가
**날짜**: 2025-11-21
**파일**: `/var/www/html/admin/mlangprintauto/download.php`
**목적**: StandardUploadHandler 표준화된 주문의 JSON 절대 경로 우선 사용

**추가 기능** (lines 67-107):
```php
// 6. 📋 JSON 기반 절대 경로 우선 확인 (StandardUploadHandler 표준화된 주문)
$json_path_found = false;
if (!empty($no)) {
    // mlangorder_printauto 테이블에서 uploaded_files JSON 조회
    $query = "SELECT uploaded_files FROM mlangorder_printauto WHERE no = ? LIMIT 1";
    $stmt = mysqli_prepare($db, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row && !empty($row['uploaded_files']) && $row['uploaded_files'] !== '0') {
            $uploaded_files = json_decode($row['uploaded_files'], true);
            if (is_array($uploaded_files)) {
                foreach ($uploaded_files as $file_info) {
                    // saved_name과 일치하는 파일 찾기
                    if (isset($file_info['saved_name']) && $file_info['saved_name'] === $downfile) {
                        // 절대 경로 확인
                        if (isset($file_info['path']) && file_exists($file_info['path'])) {
                            // 보안: 경로가 서버 루트 아래인지 확인
                            $real_path = realpath($file_info['path']);
                            $document_root = realpath($_SERVER['DOCUMENT_ROOT']);
                            if ($real_path && strpos($real_path, $document_root) === 0) {
                                $full_path = $real_path;
                                $json_path_found = true;
                                error_log("Download: JSON 절대 경로 사용 - $full_path");
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
}
```

**다운로드 경로 우선순위**:
1. **JSON 절대 경로** (StandardUploadHandler) - 최우선
2. ImgFolder 상대 경로 (레거시)
3. mlangorder_printauto/upload/{no} (초기 레거시)
4. 대체 경로 탐색 (IPv6 변환 등)

**보안 강화**:
- ✅ `realpath()` 검증 (심볼릭 링크 차단)
- ✅ `$_SERVER['DOCUMENT_ROOT']` 기준 경로 검증
- ✅ Path Traversal Attack 방지

**장점**:
- ✅ StandardUploadHandler 주문의 정확한 파일 위치 확인
- ✅ 레거시 주문 호환성 유지 (폴백 시스템)
- ✅ 보안 강화 (경로 검증)
- ✅ 성능 개선 (파일 탐색 최소화)

---

## 🔄 Previous Critical Fixes (2025-11-20)

### 1. 명함 파일 업로드 StandardUploadHandler 표준화 완료
**날짜**: 2025-11-20
**목적**: 명함 제품의 파일 업로드 시스템을 StandardUploadHandler로 전환하여 9개 제품 표준화 완료

**변경 내용**:
- **이전**: 수동 파일 처리 루프, 2단계 DB 저장 (INSERT + UPDATE)
- **변경 후**: StandardUploadHandler 사용, 1단계 통합 INSERT
- **파일**: `mlangprintauto/namecard/add_to_basket.php`
- **추가 수정**: `includes/StandardUploadHandler.php` (경로 구분자 버그 수정)

**핵심 변경 사항**:
```php
// 이전 (라인 92-130): 수동 파일 처리
require_once __DIR__ . '/../../includes/UploadPathHelper.php';
$paths = UploadPathHelper::generateUploadPath('namecard');
// ... 40줄의 수동 파일 업로드 코드 ...

// 변경 후 (라인 93-108): StandardUploadHandler 사용
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
$upload_result = StandardUploadHandler::processUpload('namecard', $_FILES);
$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);
```

**StandardUploadHandler 버그 수정**:
- **문제**: 파일 경로에 슬래시 누락 (`1763589460test.txt` 대신 `1763589460/test.txt`)
- **원인**: `$upload_dir . $safe_filename` → 슬래시 없이 연결
- **해결**: `$upload_dir . '/' . $safe_filename` (라인 106)
- **영향**: 모든 9개 제품의 향후 파일 업로드

**INSERT/UPDATE 통합**:
```php
// 이전: 2단계 프로세스
INSERT INTO shop_temp (...) VALUES (...);  // 기본 데이터만
UPDATE shop_temp SET uploaded_files = ?, ImgFolder = ? WHERE no = ?;  // 파일 정보 별도

// 변경 후: 1단계 통합
INSERT INTO shop_temp (..., uploaded_files, ImgFolder, ThingCate)
VALUES (?, ..., ?, ?, ?);  // 모든 데이터 한 번에
```

**테스트 결과**:
- ✅ 파일 업로드 성공 (단일/다중 파일)
- ✅ JSON 구조 올바르게 저장 (path, web_url, size, saved_name, original_name)
- ✅ 파일 다운로드 정상 작동 (download.php)
- ✅ 데이터베이스 무결성 유지 (ImgFolder, ThingCate, uploaded_files)

**9개 제품 표준화 완료**:
| 제품 | StandardUploadHandler | 단일 INSERT | 상태 |
|------|----------------------|------------|------|
| inserted (전단지) | ✅ | ✅ | Phase 2 완료 |
| **namecard (명함)** | ✅ | ✅ | **신규 완료** |
| envelope (봉투) | ✅ | ✅ | Phase 2 완료 |
| sticker (스티커) | ✅ | ✅ | Phase 2 완료 |
| msticker (자석스티커) | ✅ | ✅ | Phase 2 완료 |
| cadarok (카다록) | ✅ | ✅ | Phase 2 완료 |
| littleprint (포스터) | ✅ | ✅ | Phase 2 완료 |
| ncrflambeau (양식지) | ✅ | ✅ | Phase 2 완료 |
| merchandisebond (상품권) | ✅ | ✅ | Phase 2 완료 |

**장점**:
- ✅ 코드 일관성 향상 (9개 제품 100% 동일한 패턴)
- ✅ 유지보수성 개선 (중복 코드 제거, 단일 업로드 로직)
- ✅ 버그 감소 (검증된 표준 패턴 사용)
- ✅ 신규 제품 추가 용이 (StandardUploadHandler 재사용)

---

## 🔄 Previous Critical Fixes (2025-11-15)

### 1. 데이터베이스 계정 통일 (Database Account Unification)
**날짜**: 2025-11-15
**목적**: 로컬과 운영 환경의 데이터베이스 계정을 동일하게 설정

**변경 내용**:
- **이전**: 로컬 환경에서 `root` / (비밀번호 없음) 사용
- **변경 후**: 로컬 환경에서도 `dsp1830` / `ds701018` 사용
- **파일**: `config.env.php` (72-81번 라인)

**MySQL 사용자 생성**:
```sql
CREATE USER 'dsp1830'@'localhost' IDENTIFIED BY 'ds701018';
GRANT ALL PRIVILEGES ON dsp1830.* TO 'dsp1830'@'localhost';
FLUSH PRIVILEGES;
```

**장점**:
- ✅ 로컬/운영 100% 동일한 DB 설정
- ✅ 마이그레이션 용이 (코드/데이터 이동 시 설정 변경 불필요)
- ✅ 테스트 정확성 향상
- ✅ 보안 강화 (root 대신 전용 계정 사용)

### 2. mysqli_query() 3-매개변수 오류 전체 수정
**날짜**: 2025-11-15
**문제**: `mysqli_query($db, $query, $db)` 형태의 잘못된 함수 호출
**원인**: mysqli_query()는 2개 매개변수만 허용 (연결, 쿼리)

**수정 범위**:
- `admin/bbs/AdminModify.php` (21, 681번 라인)
- `admin/member/*.php` (다수 파일)
- `admin/mlangprintauto/*.php` (다수 파일)
- `admin/func.php` (특수 처리 - $connect 변수 사용)

**수정 패턴**:
```php
// 잘못된 코드
mysqli_query($db, $query, $db);

// 수정된 코드
mysqli_query($db, $query);
```

**결과**: ✅ 전체 프로젝트에서 0개의 3-매개변수 오류 남음

### 3. BBS 게시판 컬럼명 대소문자 불일치 수정
**날짜**: 2025-11-15
**파일**: `bbs/skin/board/list.php`
**문제**: DB 컬럼명(`Mlang_bbs_no`)과 PHP 배열 키(`mlang_bbs_no`) 불일치

**해결 방법**:
- SELECT 쿼리에 alias 추가하여 소문자로 변환
```php
$select_cols = "Mlang_bbs_no as mlang_bbs_no,
                Mlang_bbs_member as mlang_bbs_member, ...";
```
- WHERE 절의 컬럼명을 대문자로 변경 (`Mlang_bbs_reply`)
- ORDER BY 절 컬럼명 대문자 변경 (`Mlang_bbs_no desc`)

**결과**: ✅ 게시판 목록 정상 표시 (번호, 제목, 작성자, 날짜 등)

### 4. mysqli_affected_rows() 오용 수정
**날짜**: 2025-11-15
**파일**: `admin/bbs/list.php`
**문제**: SELECT 쿼리 후 `mysqli_affected_rows()` 호출 시 -1 반환

**원인**:
- `mysqli_affected_rows()`: INSERT/UPDATE/DELETE용
- `mysqli_num_rows()`: SELECT 결과 개수용

**수정**:
```php
// 잘못된 코드
$total_bbs = mysqli_affected_rows($db);  // -1 반환

// 수정된 코드
$total_bbs = mysqli_num_rows($total_query);  // 실제 개수
```

**결과**: ✅ "자료수" 필드에 실제 게시물 개수 표시

### 5. 정의되지 않은 변수 초기화
**날짜**: 2025-11-15
**파일**: `admin/bbs/BbsAdminCate.php`
**문제**: PHP 7.4+에서 "Undefined variable" Notice 발생

**수정**:
```php
// 추가된 초기화
$BbsAdminCateUrl = $BbsAdminCateUrl ?? '../..';
$BBS_ADMIN_skin = $BBS_ADMIN_skin ?? '';
```

**결과**: ✅ 게시판 관리자 페이지 Notice 없이 작동

### 6. 파일 업로드 경로 표준화 시스템 구축 (Upload Path Standardization)
**날짜**: 2025-11-16 (최종 업데이트)
**목적**: 9개 품목의 파일 업로드 경로를 통일된 규칙으로 관리

**구현 내용**:
- [includes/UploadPathHelper.php](includes/UploadPathHelper.php) 생성 - 통합 파일 업로드 헬퍼 클래스
- 경로 구조: `/ImgFolder/_MlangPrintAuto_{product}_index.php/{year}/{mmdd}/{ip}/{timestamp}/{filename}`
- **지원 품목 (9개)**: inserted, namecard, envelope, sticker, msticker, cadarok, littleprint, ncrflambeau, merchandisebond

**9개 품목 파일 업로드 시스템 완성 (2025-11-16)**:

모든 품목에 다음 3가지 코드가 완벽하게 구현되어 있습니다:

1. **파일 업로드 시 배열 생성**:
```php
$uploaded_files[] = [
    'original_name' => $filename,
    'saved_name' => $target_filename,
    'path' => $target_path,
    'size' => $_FILES['uploaded_files']['size'][$key],
    'web_url' => '/ImgFolder/' . $upload_folder_db . $target_filename
];
```

2. **JSON 변환**:
```php
$files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);
```

3. **DB 저장**:
```php
// INSERT 또는 UPDATE 쿼리에 uploaded_files 컬럼 포함
INSERT INTO shop_temp (..., uploaded_files) VALUES (?, ..., ?)
// 또는
UPDATE shop_temp SET ..., uploaded_files = ? WHERE no = ?
```

**품목별 구현 상태**:

| 품목 | 코드 | 배열 생성 | JSON 변환 | DB 저장 | 상태 |
|------|------|-----------|-----------|---------|------|
| 전단지 | inserted | ✅ | ✅ | ✅ | 완벽 |
| 명함 | namecard | ✅ | ✅ | ✅ | 완벽 |
| 봉투 | envelope | ✅ | ✅ | ✅ | 완벽 |
| 스티커 | sticker | ✅ | ✅ | ✅ | 완벽 |
| 자석스티커 | msticker | ✅ | ✅ | ✅ | 완벽 |
| 카다록 | cadarok | ✅ | ✅ | ✅ | 완벽 |
| 포스터 | littleprint | ✅ | ✅ | ✅ | 완벽 |
| 양식지 | ncrflambeau | ✅ | ✅ | ✅ | 완벽 |
| 상품권 | merchandisebond | ✅ | ✅ | ✅ | 완벽 |

**검증 스크립트**:
- [verify_upload_code.ps1](verify_upload_code.ps1) - 자동 검증 스크립트
- [verify_upload_code_README.md](verify_upload_code_README.md) - 사용 설명서

**주요 기능**:
```php
// 1. 업로드 경로 생성
$paths = UploadPathHelper::generateUploadPath('inserted');

// 2. 파일 업로드 처리 (디렉토리 자동 생성)
$result = UploadPathHelper::uploadFile('namecard', $_FILES['file']);
// Returns: ['success', 'db_img_folder', 'db_thing_cate', 'web_path']

// 3. DB에서 파일 경로 복원
$fileInfo = UploadPathHelper::getFilePathFromDB($imgFolder, $filename);
// Returns: ['full_path', 'web_path', 'exists', 'url']
```

**데이터베이스 저장 구조**:
- `shop_temp.ImgFolder`: 디렉토리 경로 (상대 경로)
- `shop_temp.uploaded_files`: 파일 정보 JSON 배열
- `mlangorder_printauto.ImgFolder`: 주문 확정 시 복사
- `mlangorder_printauto.uploaded_files`: 주문 확정 시 복사

**다운로드 시스템**:
- `admin/mlangprintauto/download.php` - 개별 파일 다운로드
- `admin/mlangprintauto/download_all.php` - ZIP 일괄 다운로드
- 경로 자동 감지: 레거시 경로 ↔ 신버전 경로

**장점**:
- ✅ 9개 품목 모두 동일한 경로 구조 (100% 통일)
- ✅ 유지보수 용이 (경로 변경 시 헬퍼만 수정)
- ✅ 디렉토리 자동 생성, 에러 처리 내장
- ✅ IP와 타임스탬프로 업로드 추적 가능
- ✅ 새 품목 추가 시 배열에만 추가
- ✅ 파일 정보 JSON 저장으로 메타데이터 관리
- ✅ 관리자 페이지에서 다운로드 완벽 지원

**상세 문서**: 
- [upload-system-complete.md](.kiro/steering/upload-system-complete.md) - 전체 시스템 가이드
- [upload-path-system.md](.kiro/steering/upload-path-system.md) - 경로 구조 문서

### 7. 파일명 대소문자 오류 수정
**날짜**: 2025-11-15
**파일**: `admin/mlangprintauto/catelist.php`
**문제**: 대문자 파일명 include 시도 (Linux에서 실패)

**수정**:
```php
// 잘못된 코드
include"CateAdmin_title.php";  // Linux에서 파일을 찾을 수 없음

// 수정된 코드
include"cateadmin_title.php";  // 소문자 파일명으로 통일
```

**결과**: ✅ 카테고리 관리 페이지 정상 작동

---

## 🔄 Previous Critical Fixes (2025-11-11)

### 롤스티커 계산기 시스템 구축 (Roll Sticker Calculator System)
**날짜**: 2025-11-11
**목적**: 롤스티커 자동 견적 계산 시스템 구축

**구현 내용**:
1. **설정 관리 시스템** (`admin/roll_sticker_settings.php`)
   - 재질 단가 (8종): 아트지, 유포지, 은데드롱, 투명데드롱, 금지, 은지, 크라프트, 홀로그램
   - 편집비 (도안비): 도당 단가, 최소 금액
   - 필름비, 수지판비, 도무송비, 인쇄비, 백색인쇄비
   - 코팅비 (유광/무광/UV), 박비, 동판비, 형압비, 부분코팅비
   - **UI 개선**: 라벨과 입력 필드를 한 줄로 배치, 패딩/마진 최소화로 컴팩트한 레이아웃

2. **계산기 페이지** (`shop/roll_sticker_calculator.php`)
   - 실시간 가격 계산 (AJAX)
   - 견적서 저장 및 PDF 생성
   - 견적 리스트 조회 (`shop/quote_list.php`)

3. **데이터베이스 테이블**
   - `roll_sticker_settings`: 단가 설정 저장
   - `roll_sticker_quotes`: 견적서 저장

4. **DB 연결 수정**
   - `db.php`에서 `$db` 변수 사용 → `$conn` 별칭 추가
   - 영향받은 파일: `admin/roll_sticker_settings.php`, `admin/create_settings_table.php`, `shop/quote_list.php`
   - 로컬 테이블 생성: `create_table_local.php` 스크립트 사용

**배포**:
- ✅ FTP 업로드 완료 (dsp1830.shop)
- ✅ 로컬 DB 테이블 생성 완료

**파일 목록**:
- `admin/roll_sticker_settings.php` - 설정 관리 페이지
- `admin/create_settings_table.php` - 테이블 생성 스크립트
- `shop/roll_sticker_calculator.php` - 계산기 페이지
- `shop/quote_list.php` - 견적 리스트
- `create_table_local.php` - 로컬 테이블 생성 헬퍼

---

## 🔄 Previous Critical Fixes (2025-11-04)

### 1. 갤러리 모달 이미지 연결 수정 (Gallery Modal Image Loading Fix)
**문제**: 스티커 외 모든 제품의 "샘플 더보기" 모달에서 이미지가 표시되지 않음
**원인**: `proof_gallery.php`의 SQL prepared statement 오류
- Prepared statement placeholder(`?`)가 포함된 SQL을 `mysqli_query()`로 직접 실행하여 문법 오류 발생
- 리플렛 카테고리가 JavaScript 매핑에 누락

**해결**:
- [popup/proof_gallery.php](popup/proof_gallery.php) (lines 90-128): Count 쿼리를 LIKE/prepared statement로 분기 처리
- [popup/proof_gallery.php](popup/proof_gallery.php) (lines 152-194): 데이터 쿼리도 동일한 패턴 적용
- [js/common-gallery-popup.js](js/common-gallery-popup.js) (line 46): `'leaflet': '전단지'` 매핑 추가

**테스트 결과**:
- ✅ 명함: 16개 이미지
- ✅ 전단지: 358개 이미지
- ✅ 봉투: 40개 이미지
- ✅ 포스터: 7개 이미지
- ✅ 모든 10개 제품 정상 작동

### 2. 리플렛 갤러리 시스템 구축 (Leaflet Gallery Implementation)
**목적**: 리플렛 제품에 샘플 이미지 갤러리 추가
**구현**:
- [ImgFolder/leaflet/gallery/](ImgFolder/leaflet/gallery/): 샘플 이미지 15개 저장
- [mlangprintauto/leaflet/get_leaflet_images.php](mlangprintauto/leaflet/get_leaflet_images.php): REST API 엔드포인트 (페이지네이션 지원)
- [includes/gallery_data_adapter.php](includes/gallery_data_adapter.php) (lines 12-41): `load_leaflet_gallery_unified()` 함수 추가
- [includes/gallery_data_adapter.php](includes/gallery_data_adapter.php) (lines 61-64): 리플렛 전용 로더 연결

**API 응답**:
```json
{
  "success": true,
  "total_items": 15,
  "images": [...],
  "pagination": { "per_page": 4, "total_pages": 4 }
}
```

### 3. 주문자 이름 표시 수정 (Order Name Display Fix)
**문제**: checkboard.php에서 주문자 이름이 "0"으로 표시
**원인**: 데이터베이스 60,498개 주문의 name 필드가 '0' (레거시 데이터 문제)
**해결**:
- [sub/checkboard.php](sub/checkboard.php) (lines 247-261): name이 '0'이면 email 앞부분 표시
- [mlangorder_printauto/OnlineOrder_unified.php](mlangorder_printauto/OnlineOrder_unified.php) (lines 565-589): 주문 폼 name 필드 자동 채움 강화
- [mlangorder_printauto/ProcessOrder_unified.php](mlangorder_printauto/ProcessOrder_unified.php) (lines 31-33): 디버그 로깅 추가
**배포**: ✅ FTP 업로드 완료 (dsp1830.shop)

### 4. 스티커 갤러리 수정 (Sticker Gallery Fix)
**문제**: proof_gallery.php에서 "샘플이 준비되지 않았습니다" 표시
**원인**: 정확한 매칭만 검색 (Type = '스티커'), 변형 무시 (투명스티커, 유포지스티커 등)
**해결**:
- [popup/proof_gallery.php](popup/proof_gallery.php) (line 35): `'스티커' => 'LIKE'` 패턴 매칭으로 변경
- [popup/proof_gallery.php](popup/proof_gallery.php) (lines 55-57): '스티카' 오타도 포함, 자석스티커 제외
- **결과**: 0개 → 1,900개 아이템 표시
**배포**: ✅ FTP 업로드 완료 (dsp1830.shop)

### 5. 도메인 자동 감지 시스템 구현 (Domain Auto-Detection)
**목적**: dsp1830.shop ↔ dsp1830.shop 전환 시 코드 수정 불필요
**구현**:
- [db.php](db.php) (lines 101-118): 현재 접속 도메인 자동 감지 (`$_SERVER['HTTP_HOST']`)
- 환경별 자동 URL 설정: localhost / dsp1830.shop / dsp1830.shop
- 쿠키 도메인 자동 설정: localhost / .dsp1830.shop / .dsp1830.shop
**장점**: DNS 전환만으로 코드 변경 없이 도메인 교체 가능
**테스트**: `http://localhost/?debug_db=1`

### 6. 문서화 완료 (Documentation Update)
**생성/업데이트된 문서**:
- [PROJECT_OVERVIEW.md](CLAUDE_DOCS/01_CORE/PROJECT_OVERVIEW.md) (lines 21-86): 도메인 마이그레이션 전략
- [ENVIRONMENT_CONFIG.md](CLAUDE_DOCS/02_ARCHITECTURE/ENVIRONMENT_CONFIG.md) (신규 생성, 350+ lines): 환경 설정 상세 가이드
- [DEPLOYMENT.md](CLAUDE_DOCS/04_OPERATIONS/DEPLOYMENT.md) (lines 435-661): DNS 절차 및 배포 가이드
- [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md): FTP 배포 가이드 (업데이트 체크리스트)
- **CLAUDE.md** (이 파일): 핵심 내용 요약 및 문서 구조 안내

## 🔧 Development Environment

### Current Setup (WSL2 Ubuntu on Windows)
- **OS**: Linux 6.6.87.2-microsoft-standard-WSL2
- **Web Server**: Apache 2.4+
- **PHP**: 7.x+ (check with `php -v`)
- **Database**: MySQL 5.7+ with utf8mb4
- **Document Root**: `/var/www/html`
- **Production**: www.dsp1830.shop, dsp1830.shop
- **Local Access**: http://localhost

### Alternative Setup (Windows XAMPP)
- **Install**: XAMPP for Windows
- **Document Root**: `C:\xampp\htdocs`
- **Control Panel**: `C:\xampp\xampp-control.exe`
- **Same codebase** works on both environments via environment detection

## 🔌 MCP (Model Context Protocol) Integration

**Claude Code MCP 서버 설치 및 관리**

### Quick Reference
- **설치 가이드**: [CLAUDE_DOCS/05_DEVELOPMENT/MCP_Installation_Guide.md](CLAUDE_DOCS/05_DEVELOPMENT/MCP_Installation_Guide.md)
- **현재 환경**: WSL2 Ubuntu
- **설정 위치**: `~/.claude/` (User) | `./.claude/` (Project)

### 핵심 원칙
1. **환경 확인 우선**: OS 및 터미널 환경 파악 후 진행
2. **공식 문서 우선**: WebSearch → Context7 → 공식 설치법 확인
3. **User 스코프**: User 스코프로 설치 및 적용
4. **검증 필수**: `claude mcp list` → `claude --debug` → `/mcp` 확인
5. **요청받은 것만**: 요청된 MCP만 설치, 기존 에러 무시

### 설치 흐름
```bash
# 1. mcp-installer로 기본 설치
mcp-installer

# 2. 설치 확인
claude mcp list

# 3. 디버그 모드 검증 (2분 관찰)
claude --debug

# 4. MCP 작동 확인
echo "/mcp" | claude --debug

# 5. 문제 시 직접 설치
claude mcp add --scope user [mcp-name] \
  -e API_KEY=$YOUR_KEY \
  -- npx -y [package-name]
```

### 주의사항
- **Windows 경로**: JSON에서 백슬래시 이스케이프 (`C:\\path\\to\\file`)
- **Node.js**: v18 이상 필요, PATH 등록 확인
- **API 키**: 가상 키로 설치 후 실제 키 입력 안내
- **서버 의존성**: MySQL MCP 등은 서버 구동 필요, 재설치하지 말 것

**상세 가이드**: [MCP_Installation_Guide.md](CLAUDE_DOCS/05_DEVELOPMENT/MCP_Installation_Guide.md)

---

## 📚 Comprehensive Documentation

이 `CLAUDE.md`는 빠른 참조용입니다. 상세한 기술 문서는 `CLAUDE_DOCS/` 디렉토리에 체계적으로 정리되어 있습니다.

### 핵심 문서 구조

**01_CORE/** - 프로젝트 핵심 개요
- [PROJECT_OVERVIEW.md](CLAUDE_DOCS/01_CORE/PROJECT_OVERVIEW.md) - 프로젝트 전체 개요, 비즈니스 정보, 도메인 마이그레이션 전략

**02_ARCHITECTURE/** - 기술 아키텍처
- [ENVIRONMENT_CONFIG.md](CLAUDE_DOCS/02_ARCHITECTURE/ENVIRONMENT_CONFIG.md) - 환경 자동 감지 시스템, 도메인 설정, DNS 전환 상세 가이드
- [DATABASE_SETUP.md](CLAUDE_DOCS/02_ARCHITECTURE/DATABASE_SETUP.md) - 데이터베이스 스키마 및 설정

**03_PRODUCTS/** - 제품 시스템
- 10개 제품별 상세 가이드 및 구현 방법

**04_OPERATIONS/** - 운영 및 배포
- [DEPLOYMENT.md](CLAUDE_DOCS/04_OPERATIONS/DEPLOYMENT.md) - 배포 전략, DNS 절차, 프로덕션 체크리스트
- [ADMIN_SYSTEM.md](CLAUDE_DOCS/04_OPERATIONS/ADMIN_SYSTEM.md) - 관리자 시스템 가이드

**05_DEVELOPMENT/** - 개발 가이드
- [MCP_Installation_Guide.md](CLAUDE_DOCS/05_DEVELOPMENT/MCP_Installation_Guide.md) - MCP 서버 설치
- [FRONTEND_UI.md](CLAUDE_DOCS/05_DEVELOPMENT/FRONTEND_UI.md) - UI/UX 시스템
- [TROUBLESHOOTING.md](CLAUDE_DOCS/05_DEVELOPMENT/TROUBLESHOOTING.md) - 문제 해결

**06_ARCHIVE/** - 완료된 프로젝트 및 참고 자료

### 문서 사용 방법

**빠른 참조**: 이 `CLAUDE.md` 파일 (세션 시작 시 자동 로드)

**상세 기술 문서**: 필요시 `CLAUDE_DOCS/` 디렉토리 문서 참조
```
예: "CLAUDE_DOCS/02_ARCHITECTURE/ENVIRONMENT_CONFIG.md를 읽고
     환경 설정 시스템을 확인해줘"
```

**전체 색인**: [CLAUDE_DOCS/INDEX.md](CLAUDE_DOCS/INDEX.md)

---

*Last Updated: 2025-12-10*
*Environment: WSL2 Ubuntu (supports XAMPP)*
*Working Directory: /var/www/html*
*WSL sudo password: 3305*
