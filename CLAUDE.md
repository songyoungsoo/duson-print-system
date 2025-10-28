# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 🏢 Project Context

**Duson Planning Print System (두손기획인쇄)** - Enterprise printing service management system built in PHP for comprehensive print order processing, automated pricing, and business operations.

### 🌐 Domain & Migration Strategy

**Current Production (현재 운영 중)**:
- **Domain**: www.dsp114.com
- **PHP Version**: 5.2 (Legacy)
- **Status**: ⚠️ 레거시 시스템 운영 중
- **Description**: 구형 PHP 5.2 기반, 점진적 단계적 폐기 예정

**Modernization Target (현대화 작업 중)**:
- **Temporary Domain**: dsp1830.shop (임시 개발/테스트 도메인)
- **Final Domain**: www.dsp114.com (← 최종 교체 목표)
- **PHP Version**: 7.4.3
- **Status**: 🚧 현대화 및 기능 마이그레이션 진행 중
- **Strategy**:
  1. dsp1830.shop에서 모든 기능 개발/테스트 완료
  2. 충분한 테스트 후 dsp114.com 도메인으로 교체
  3. 구형 시스템 폐기

**Development Environment (로컬 개발)**:
- **Platform**: WSL2 Ubuntu on Windows
- **Location**: `/var/www/html`
- **Access**: http://localhost
- **Database**: dsp1830 (프로덕션과 동일한 구조)
- **PHP Version**: 7.4+ (신규 시스템과 동일)

**⚠️ Important Notes**:
- dsp1830.shop은 영구 도메인이 아닌 임시 개발/스테이징 환경
- 모든 개발은 dsp114.com 최종 배포를 목표로 진행
- 환경 설정(`config.env.php`)은 dsp114.com과 dsp1830.shop 모두 지원

## 🚨 Critical Conventions

### Database Table Names
- **ALWAYS use lowercase** in SQL queries: `mlangprintauto_namecard`, `shop_temp`, `member_user`
- **NEVER** use uppercase: ~~`MlangPrintAuto_NameCard`~~
- Files/directories preserve original case, but tables are lowercase
- `db.php` provides auto-mapping for compatibility but always write lowercase

### Directory Naming Inconsistency
- **Order Processing**: Use `mlangorder_printauto/` (lowercase) for actual directory path
- Historical naming: `MlangOrder_PrintAuto/` appears in documentation but directory is lowercase
- **Product Admin**: `admin/MlangPrintAuto/` (mixed case) for admin files

### Character Encoding
- Database charset: `utf8mb4` (Korean language support)
- PHP files: UTF-8 without BOM
- Always use `mysqli_set_charset($db, 'utf8')`

### Session Management
- Session-based authentication via `includes/auth.php`
- Session data stored in `session/` directory
- Cart uses PHP sessions via `$_SESSION['session_id']`




### Environment Detection
- **Automatic**: `config.env.php` auto-detects local vs production
- **Local triggers**: localhost, 127.0.0.1, XAMPP path, ::1
- **Production**: dsp114.com, dsp1830.shop domains
- **Database config**: Different credentials per environment
- **Debug mode**: Enabled locally, disabled in production
- Manual override: `EnvironmentDetector::forceEnvironment('local')`

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

## 🎯 Product Types Reference (10개 제품)

⚠️ **AI 주의사항**
- `littleprint` = 포스터 제품 (레거시 코드명, 변경 불가)
- `poster` 디렉토리는 별도 프로젝트로 사용하지 않음
- 포스터 제품은 항상 **`littleprint`** 코드명을 사용할 것

| Code | Name (Korean) | Module Directory | Database Table |
|------|---------------|------------------|----------------|
| `inserted` | 전단지 | `mlangprintauto/inserted/` | `mlangprintauto_inserted` |
| `envelope` | 봉투 | `mlangprintauto/envelope/` | `mlangprintauto_envelope` |
| `namecard` | 명함 | `mlangprintauto/namecard/` | `mlangprintauto_namecard` |
| `sticker` | 스티커 | `mlangprintauto/sticker_new/` | `mlangprintauto_sticker` |
| `msticker` | 자석스티커 | `mlangprintauto/msticker/` | `mlangprintauto_msticker` |
| `cadarok` | 카다록 | `mlangprintauto/cadarok/` | `mlangprintauto_cadarok` |
| `littleprint` | **포스터** ⚠️ | `mlangprintauto/littleprint/` | `mlangprintauto_littleprint` |
| `merchandisebond` | 상품권 | `mlangprintauto/merchandisebond/` | `mlangprintauto_merchandisebond` |
| `ncrflambeau` | NCR양식 | `mlangprintauto/ncrflambeau/` | `mlangprintauto_ncrflambeau` |
| `leaflet` | 리플렛 | `mlangprintauto/inserted/` | Same as inserted |

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

## 🔄 Recent Critical Fixes (2025-10)

### Cart Price Calculation Fix
**Problem**: Cart showed 0원 for all prices
**Root Cause**: Parameter name mismatch (`calculated_price` vs `price`)
**Files Modified**:
- `mlangprintauto/inserted/add_to_basket.php` (lines 23-24)
- `mlangprintauto/inserted/calculator.js` (lines 86-92)

### ProcessOrder Variable Fix
**Problem**: Undefined variable `$shop_data`
**Root Cause**: Used wrong variable for product type check
**File Modified**: `mlangorder_printauto/OnlineOrder_unified.php`
**Fix**: Changed `$shop_data['ThingCate']` to `$item['product_type']`

### CSS Unification (2025-10-05)
**Achievement**: Unified all 10 product pages to consistent layout system
**Changes**:
- Standardized HTML structure across all products
- Unified class names (`.product-container`, `.product-content`, `.product-gallery`, `.product-calculator`)
- Removed duplicate CSS (~205 lines)
- Eliminated `!important` usage through proper CSS specificity
- Simplified page titles (removed "견적안내", development version notes)
- Consolidated gallery system to use shared `assets/css/gallery.css`

**Key Files Modified**:
- All 10 product `index.php` files (structure unification)
- `css/common-styles.css` (final authority, loads last)
- `css/product-layout.css` (base structure)
- `css/gallery-common.css` (shared gallery styles)
- `assets/css/gallery.css` (gallery system core)

## 🔧 Development Environment

### Current Setup (WSL2 Ubuntu on Windows)
- **OS**: Linux 6.6.87.2-microsoft-standard-WSL2
- **Web Server**: Apache 2.4+
- **PHP**: 7.x+ (check with `php -v`)
- **Database**: MySQL 5.7+ with utf8mb4
- **Document Root**: `/var/www/html`
- **Production**: www.dsp114.com, dsp1830.shop
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

*Last Updated: 2025-10-08*
*Environment: WSL2 Ubuntu (supports XAMPP)*
*Working Directory: /var/www/html*
*WSL sudo password: 3305*
