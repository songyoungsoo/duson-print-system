# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

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

**모든 `add_to_basket.php` 파일에 동일한 3단계 구현**:

```php
// 1. 업로드 경로 생성 (UploadPathHelper 사용)
require_once __DIR__ . '/../../includes/UploadPathHelper.php';
$paths = UploadPathHelper::generateUploadPath('product_name');
$upload_directory = $paths['full_path'];      // 실제 파일 저장 경로
$upload_directory_db = $paths['db_path'];     // DB 저장용 상대 경로

// 2. 파일 업로드 시 배열 생성
$uploaded_files = [];
foreach ($_FILES['uploaded_files']['name'] as $key => $filename) {
    if ($_FILES['uploaded_files']['error'][$key] == UPLOAD_ERR_OK) {
        $uploaded_files[] = [
            'original_name' => $filename,
            'saved_name' => $filename,
            'path' => $upload_directory . '/' . $filename,
            'size' => $_FILES['uploaded_files']['size'][$key],
            'web_url' => '/ImgFolder/' . $upload_directory_db . '/' . $filename
        ];
    }
}

// 3. JSON 변환 및 DB 저장
$files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);
$insert_query = "INSERT INTO shop_temp (..., uploaded_files, ImgFolder) VALUES (?, ..., ?, ?)";
mysqli_stmt_bind_param($stmt, "...ss", ..., $files_json, $upload_directory_db);
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

### FTP Deployment (Production)
```bash
# FTP 접속 정보
Host: ftp://dsp1830.shop
User: dsp1830
Pass: ds701018

# curl을 사용한 파일 업로드
curl -T "local/file.php" --ftp-create-dirs -u "dsp1830:ds701018" \
  "ftp://dsp1830.shop/public_html/path/to/file.php"

# 예시: 여러 파일 업로드
curl -T "shop/calculator.php" --ftp-create-dirs -u "dsp1830:ds701018" \
  "ftp://dsp1830.shop/public_html/shop/calculator.php"

# FileZilla 사용 시
# Host: dsp1830.shop
# Protocol: FTP
# Port: 21
# Remote path: /public_html/
```

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

## 🔄 Recent Critical Fixes (2025-11-15)

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

*Last Updated: 2025-11-19*
*Environment: WSL2 Ubuntu (supports XAMPP)*
*Working Directory: /var/www/html*
*WSL sudo password: 3305*
