# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Duson Planning Print System

## 🏢 Project Overview
**Duson Planning Print** (두손기획인쇄) - A comprehensive web-based printing service management system built in PHP for a Korean printing company. The system handles print ordering, price calculation, file uploads, member management, and business transactions.

**Production URL**: www.dsp114.com  
**Development Environment**: XAMPP on Windows (C:\xampp\htdocs)  
**Current Branch**: auth-system-fix

## 🛠 Technology Stack
- **Backend**: PHP 7+ with MySQL database
- **Frontend**: HTML, CSS (Noto Sans KR fonts), JavaScript (ES5)  
- **Server**: Apache (XAMPP development environment)
- **Database**: MySQL with utf8 charset
- **File Uploads**: Multi-file support with organized directory structure
- **Session Management**: PHP sessions for user authentication
- **Email**: PHPMailer for order notifications

## 📁 Core Directory Structure

```
C:\xampp\htdocs\
├── db.php                      # Main database configuration
├── index.php                   # Homepage with product navigation
├── header.php, footer.php      # Common layout components
├── left.php                    # Left navigation menu
├── css/styles.css              # Common styles with Noto fonts
├── js/common.js                # Shared JavaScript functions
├── includes/                   # Common PHP components
│   ├── functions.php           # Utility functions
│   ├── auth.php               # Authentication handling
│   └── login_modal.php        # Login modal component
├── MlangPrintAuto/            # Main product ordering system
│   ├── inserted/              # Leaflet/Flyer ordering
│   ├── NameCard/              # Business card ordering
│   ├── sticker/               # General sticker ordering (수식 기반 계산)
│   ├── msticker/              # Magnetic sticker ordering
│   ├── envelope/              # Envelope ordering  
│   ├── LittlePrint/           # Poster ordering
│   ├── cadarok/               # Catalog ordering
│   ├── MerchandiseBond/       # Coupon/voucher ordering (상품권)
│   └── NcrFlambeau/           # Form/NCR paper ordering
├── MlangOrder_PrintAuto/      # Order processing system
│   ├── OnlineOrder_unified.php      # Unified order form
│   ├── ProcessOrder_unified.php     # Order processing logic
│   └── OrderComplete_unified.php    # Order confirmation
├── shop/                      # Shopping cart system
│   ├── cart.php              # Cart management
│   ├── add_to_basket.php     # Add items to cart
│   └── calculate_price.php    # Price calculations
├── admin/                     # Admin management panel
├── member/                    # Member registration/login
├── bbs/                       # Board system for Q&A
└── uploads/                   # File upload storage
```

## 🔧 Development Setup & Commands

### Local Development
```bash
# Start XAMPP services
xampp-control.exe  # Start Apache and MySQL

# Access development site
http://localhost/

# Database access (phpMyAdmin)
http://localhost/phpmyadmin/
```

### Testing & Validation
```bash
# Test database connection
php db.php

# Check PHP configuration
php -m  # List installed modules
php -v  # PHP version

# Validate syntax
php -l filename.php
```

### Common File Locations
- **Main config**: `db.php` (database: duson1830)
- **Price calculation**: Each product has individual calculation logic
- **File uploads**: Organized by date/IP in `uploads/` directory
- **Common functions**: `includes/functions.php`
- **Authentication**: `includes/auth.php`, `includes/auth_functions.php`
- **Session handling**: PHP native sessions with enhanced cleanup

## 🏪 Product Systems

### 1. **Leaflet/Flyer System** (`MlangPrintAuto/inserted/`)
- Dynamic price calculation based on paper type, size, quantity
- File upload with preview functionality
- Uses common file structure with AJAX integration

### 2. **Business Cards** (`MlangPrintAuto/NameCard/`)
- Specialized pricing for different card types
- Design options and file upload support
- Real-time price updates

### 3. **General Stickers** (`MlangPrintAuto/sticker_new/index.php`)
- **Complex formula-based pricing** calculation (수식 계산, not table-based)
- **Material options**: 아트지유광, 아트지무광코팅, 아트지비코팅, 투명스티커, 강접아트유광코팅(90g), 초강접아트유광코팅, 초강접아트비코팅, 유포지(80g), 은데드롱(25g),투명스티커(25g),모조지비코팅(80g), 크라프트스티커(57g),금지스티커-전화문의, 금박스티커-전화문의,롤스티커-전화문의 등
- **Custom sizing**: 가로(garo) x 세로(sero) 직접 입력 (10mm~560mm)
- **Quantity tiers**: 500매~100,000매 with bulk pricing (mesu)
- **Design fees**: 편집비 선택 (인쇄만/기본편집+10,000원/고급편집+30,000원)
- **Shape options**: 기본사각형, 사각도무송, 귀돌이(라운드),원형, 타원형, 모양도무송 (domusong)
- **Modern interface**: AJAX price calculation, drag-drop file upload
- **Integration**: Common header/footer applied, shop_temp cart system

### 4. **Magnetic Stickers** (`MlangPrintAuto/msticker/`)
- Specialized magnetic material stickers
- Standard size options with quantity-based pricing
- AJAX price calculation system
- Separate from general stickers

### 5. **Coupon/Voucher System** (`MlangPrintAuto/MerchandiseBond/`)
- **상품권/쿠폰 시스템**: Dynamic transaction categories
- **Database tables**: `MlangPrintAuto_transactionCate`, `MlangPrintAuto_MerchandiseBond`
- **Dynamic dropdowns**: 구분(MY_type) → 종류(PN_type) → 후가공 → 수량/인쇄면
- **Recently integrated** (August 2025): Common file structure applied
- **Button system**: 🛒 장바구니에 담기 + 📋 바로 주문하기
- **AJAX features**: Real-time option loading, price calculation

### 6. **Envelopes** (`MlangPrintAuto/envelope/`)
- Standard and custom size options
- Paper type selection
- Bulk ordering support

### 7. **Shopping Cart System** (`shop/`)
- Multi-item cart management
- AJAX-based add/remove functionality
- Continue shopping with smart navigation
- Unified order processing

### 8. **Quote Generation System** (`mlangprintauto/shop/generate_quote_pdf.php`)
- PDF quote generation with HTML fallback
- Customer information collection and validation
- Email delivery with PHPMailer integration
- Quote logging in `quote_log` and `quote_items` tables
- Supports wkhtmltopdf for professional PDF output
- Admin notification system for quote requests

## 💳 Order Processing Flow

### Standard Order Process:
1. **Product Selection** → Individual product pages
2. **Price Calculation** → Real-time AJAX calculations
3. **Add to Cart** → Shopping cart system
4. **Checkout** → Unified order form (`OnlineOrder_unified.php`)
5. **Processing** → Order processing (`ProcessOrder_unified.php`)
6. **Confirmation** → Order complete page (`OrderComplete_unified.php`)

### Direct Order Process:
- Skip cart: Product page → Direct order → Order processing
- Same unified processing system

### Business Orders:
- Business registration number input
- Tax invoice email collection
- Structured business info storage

## 🔐 Authentication & Members

### Member System Features:
- Registration with business information support
- Login/logout with session management  
- Member information auto-fill for orders
- Address management (member vs different address)

### Session Management:
```php
// Enhanced logout with complete cleanup
$_SESSION = array();
session_destroy();
session_start();
```

### Database Schema Extensions:
```sql
-- Business fields
ALTER TABLE users ADD COLUMN is_business TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN business_number VARCHAR(20);
ALTER TABLE users ADD COLUMN business_owner VARCHAR(100);

-- Address fields  
ALTER TABLE users ADD COLUMN postcode VARCHAR(10);
ALTER TABLE users ADD COLUMN address VARCHAR(255);
```

## 💰 Pricing & Calculations

### Price Calculation Logic:

#### **Table-based Calculation** (Most products):
- **Leaflets/Posters**: `MlangPrintAuto_littleprint` table lookup
- **Coupons**: `MlangPrintAuto_MerchandiseBond` table lookup
- **Base Price + Design Fee + VAT (10%)**

#### **Formula-based Calculation** (Stickers only):
- **General Stickers**: Complex mathematical formulas in `view_modern.php`
- Variables: 재질(jong), 가로/세로(garo/sero), 수량(mesu), 편집비(uhyung), 모양(domusong)
- Uses `shop_d1`, `shop_d2`, `shop_d3`, `shop_d4` reference tables
- Real-time AJAX calculation without database queries

#### **Pricing Structure**:
- **Base Price**: Product-specific pricing tables or formulas
- **Design Fee**: Optional design charges (편집비)
- **VAT**: 10% tax calculation  
- **Bulk Discounts**: Quantity-based pricing tiers

### Common Functions (`includes/functions.php`):
```php
calculateProductPrice($db, $table, $conditions, $ordertype)
format_price($price)  // Adds "원" suffix
format_number($number)  // Thousand separators
```

## 📧 Email & Notifications

### PHPMailer Integration:
- Order confirmation emails
- Admin notifications
- Business tax invoice requests

### SMTP Configuration:
- Configured in individual mailer implementations
- Uses company email: dsp1830@naver.com

## 🎨 UI/UX Features

### Common Design System:
- **Noto Sans KR** font throughout
- Responsive grid layouts
- Color-coded product categories
- Hover animations and transitions
- Mobile-friendly design

### Left Navigation Menu:
- Product-specific active states
- Color-coded hover effects per product type
- Order button prominence

### File Upload Components:
- Drag-and-drop support in modern browsers
- Multiple file selection
- File type validation
- Progress indicators

## 🗄 Database Configuration

### Connection Details:
```php
// Primary database connection (db.php)
$host = "localhost";
$user = "duson1830"; 
$password = "du1830";
$dataname = "duson1830";
$db = mysqli_connect($host, $user, $password, $dataname);
mysqli_set_charset($db, "utf8");
```

### Connection Patterns:
```php
// Standard connection check
if (!$db) {
    die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
}

// Admin panel connection (admin/ConDb.php)
$connect = mysqli_connect($host, $user, $password, $dataname);
```

### Key Tables:
- `users` - Member information with business fields
- `MlangPrintAuto_*` - Product-specific pricing tables
  - `MlangPrintAuto_littleprint` - Poster/leaflet pricing
  - `MlangPrintAuto_transactionCate` - Coupon categories
  - `MlangPrintAuto_MerchandiseBond` - Coupon pricing
  - `shop_d1`, `shop_d2`, `shop_d3`, `shop_d4` - Sticker calculation tables
- `shop_temp` - Shopping cart storage with product-specific fields
- `MlangOrder_PrintAuto` - Unified order storage
- `quote_log` - Quote generation history and customer details
- `quote_items` - Detailed quote item specifications
- `page` - CMS content management

## 🚀 Development Workflow

### Local Development Setup:
1. Install XAMPP (Apache + MySQL + PHP)
2. Clone repository to `C:\xampp\htdocs\`
3. Import database: `duson1830.sql` via phpMyAdmin
4. Start Apache and MySQL services
5. Access: `http://localhost/`

### File Organization:
- **Common files**: Root level (header.php, footer.php, etc.)
- **Product-specific**: Individual subdirectories under `MlangPrintAuto/`
- **Shared resources**: `/css/`, `/js/`, `/includes/`
- **Uploads**: Organized by URL/date/IP/timestamp in `uploads/`
- **Admin panel**: `admin/` directory with separate authentication

### Key Development Patterns:
```php
// Standard page structure
include "../../db.php";
include "../../includes/auth.php"; 
include "../../includes/header.php";
// Page content
include "../../includes/footer.php";
```

### AJAX Endpoints Pattern:
```php
// Standard AJAX response
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'data' => $result,
    'message' => '성공'
]);
```

### Error Handling:
```php
// Database error handling
if (!$result) {
    error_log("Query failed: " . mysqli_error($db));
    die("오류가 발생했습니다.");
}
```

## 📞 Company Information

### Business Details:
- **Company**: 두손기획인쇄 (Duson Planning Print)
- **Phone**: 02-2632-1830, 1688-2384
- **Address**: 서울 영등포구 영등포로 36길 9, 송호빌딩 1F
- **Website**: www.dsp114.com

### Payment Information:
- **Account Holder**: 두손기획인쇄 차경선
- **Banks**: 국민은행(999-1688-2384), 신한은행(110-342-543507), 농협(301-2632-1829)
- **Card Payment**: Call 1688-2384

## 🎯 Sticker Integration System Requirements

**📋 스티커 통합 시스템 계획** (from `.kiro\specs\sticker-integration-system`):

### Integration Goals:
1. **Navigation Integration**: Dropdown menu for 일반스티커 vs 자석스티커
2. **Unified Price Engine**: Formula-based calculation for stickers + table-based for others  
3. **Cart Integration**: Support sticker-specific fields in shop_temp table
4. **Order Processing**: JSON storage for detailed product options
5. **Admin Management**: Enhanced order management with product-specific displays

### Key Technical Requirements:
- **shop_temp table enhancement**: Add sticker fields (jong, garo, sero, mesu, uhyung, domusong)
- **Unified price calculator**: Handle both formula and table-based pricing
- **Order data structure**: JSON format in Type_1 field for detailed options
- **File management**: ImgFolder for upload paths, ThingCate for main image

### Current Status (August 2025):
- ✅ **General stickers**: Fully functional at `/sticker_new/index.php` - NameCard 디자인 통합 완료
- ✅ **Magnetic stickers**: Available at `/msticker/index.php` - NameCard 디자인 기본 적용
- ✅ **Design Integration**: CSS-Only 오버레이로 수식 계산 100% 보존하며 통합 완료
- ✅ **Performance Optimized**: CSS 압축 및 프로덕션 배포 완료
- 📋 **Future Enhancement**: Navigation dropdown, unified cart integration for better UX

## 🔍 Recent Major Updates

### Completed Enhancements (August 2025):
- ✅ **Common File Architecture**: Centralized header/footer/CSS
- ✅ **Business Order Integration**: B2B order processing
- ✅ **Direct Order System**: Skip-cart ordering
- ✅ **Enhanced Shopping Cart**: Continue shopping features
- ✅ **Unified Order Processing**: Single order system for all products
- ✅ **Session Management**: Improved login/logout
- ✅ **Payment System**: Multi-bank payment options

### MlangPrintAuto 통합 디자인 시스템 완료 (August 2025):
- ✅ **스티커 시스템 통합**: CSS-Only 오버레이로 NameCard 디자인 적용 (`sticker_new/index.php`)
- ✅ **수식 계산 로직 100% 보존**: 기존 JavaScript/PHP 로직 변경 없이 디자인만 통합
- ✅ **성능 최적화**: CSS 압축 적용 (`unified-sticker-overlay.min.css`)
- ✅ **전체 품목 통합**: 11개 품목 모두 NameCard 디자인으로 통일
- ✅ **envelope 갤러리 기술**: 고급 이미지 애니메이션 및 라이트박스 적용
- ✅ **반응형 디자인**: 모바일 완벽 대응

### Current System Status:
**🎉 FULLY OPERATIONAL** - All systems tested and production-ready
**🎨 DESIGN UNIFIED** - All 11 products with consistent NameCard design system

## 🏗 Critical Architecture Notes

### Character Encoding:
- **Database**: UTF-8 charset throughout
- **PHP Files**: Must be saved as UTF-8 without BOM
- **HTML Meta**: `<meta charset="UTF-8">`
- **MySQL Connection**: Always set `mysqli_set_charset($db, "utf8")`

### Session Management:
- Sessions stored in PHP default location
- Session ID used for cart management (`shop_temp` table)
- Enhanced logout with complete session cleanup
- Member login state checked via `$_SESSION['duson_member_id']`

### File Upload Architecture:
- Base directory: `uploads/`
- Structure: `uploads/[URL]/[DATE]/[IP]/[TIMESTAMP]/`
- Supported formats: PDF, JPG, PNG, GIF, AI, PSD
- Max file size: Configured in PHP.ini
- Multiple file support via FileUploadComponent

### Price Calculation Architecture:
1. **Database-driven** (Most products):
   - Query pricing tables with conditions
   - Apply quantity discounts
   - Add design fees if applicable
   
2. **Formula-driven** (Stickers only):
   - JavaScript calculations in frontend
   - Complex mathematical formulas
   - Real-time AJAX updates

### Cart System Architecture:
- Table: `shop_temp`
- Session-based cart management
- Product-specific fields stored as JSON in `Type_1`
- Multi-step checkout process
- Direct order bypass available

## 🛠 Troubleshooting Common Issues

### API Error Debugging:
When encountering API errors like "model: http://localhost/..." with 404 not_found_error:
- This indicates a URL is being incorrectly passed as a model parameter to an AI API
- Check browser Network tab in Developer Tools to identify the source request
- Look for JavaScript fetch/XMLHttpRequest calls that may be misconfigured
- Common cause: Frontend code incorrectly formatting API requests

### Database Connection:
```php
// Check connection in db.php
if (!$db) {
    die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
}
```

### Session Issues:
```php
// Debug session status
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
```

### File Upload Problems:
- Check directory permissions (0755)
- Verify `uploads/` directory exists
- Check PHP `upload_max_filesize` setting
- FileUploadComponent requires proper initialization

### Price Calculation Issues:
- **Table-based products**: Verify database table structure matches expected fields
- **Stickers (formula-based)**: Check JavaScript calculation functions in `view_modern.php`
- **AJAX endpoints**: Validate response format and error handling
- **Common error**: Missing `jong`, `garo`, `sero`, `mesu` parameters for stickers

### Sticker-Specific Issues:
- **Formula calculation**: Located in `MlangPrintAuto/sticker_new/index.php` JavaScript
- **Cart integration**: Requires sticker fields in `shop_temp` table
- **Price display**: Uses `st_price` and `st_price_vat` fields
- **Material codes**: Must match predefined jong values (e.g., "jil 아트유광")

## 📋 Development Notes

### Code Standards:
- PHP 7+ compatible
- UTF-8 encoding throughout
- MySQL prepared statements for security
- Responsive design principles
- Korean language support optimized

### Performance Considerations:
- File caching for common resources
- Optimized image handling
- Database query optimization
- AJAX for dynamic content loading

## 🔧 Recent Major Updates (December 2025)

### Cart Order Processing Fix (December 10, 2025)
**Issue**: 장바구니에서 주문 시 주문요약과 상품목록이 빈 상태로 표시
**Location**: `C:\xampp\htdocs\MlangOrder_PrintAuto\OnlineOrder_unified.php`

**Root Cause**: 
- Cart form sends array data (`product_type[]`) but unified order form only handled single product orders
- Missing detection logic for cart POST vs direct order POST

**Solution Applied**:
```php
// 주문 타입 확인 개선
$is_cart_post_order = !empty($_POST['product_type']) && is_array($_POST['product_type']);

// 장바구니 POST 데이터 처리 추가
if ($is_cart_post_order) {
    $cart_result = getCartItems($connect, $session_id);
    // 실제 세션 데이터 사용하여 상세 정보 표시
}
```

**Files Modified**:
- `MlangOrder_PrintAuto/OnlineOrder_unified.php`: Cart POST handling logic added (lines 24, 147-186)

### Print Output Enhancements (December 10, 2025)

#### 1. **Print Layout: Added Missing Information**
**Issue**: 프린트 출력에서 기타사항과 사업자정보가 누락
**Location**: `C:\xampp\htdocs\MlangOrder_PrintAuto\OrderFormOrderTree.php`

**Solution**:
- Added "기타사항" section to both admin and staff print layouts
- Business information automatically included (stored in `cont` field)
- Conditional display (only shows when content exists)

```php
<!-- 기타 사항 및 사업자 정보 -->
<?php if (!empty($View_cont) && trim($View_cont) != '') { ?>
<div class="print-info-section">
    <div class="print-info-title">기타사항</div>
    <div style="padding: 2mm; border: 1px solid #333; min-height: 10mm; font-size: 8pt;">
        <?php echo nl2br(htmlspecialchars($View_cont)); ?>
    </div>
</div>
<?php } ?>
```

#### 2. **PDF File Naming Enhancement**
**Issue**: PDF 저장 시 기본 파일명 사용
**Solution**: 자동으로 "주문자명_주문번호.pdf" 형식으로 설정

```javascript
function printOrder() {
    const customerName = "<?=htmlspecialchars($View_name)?>";
    const orderNumber = "<?=$View_No?>";
    const fileName = sanitizeName(customerName) + '_' + orderNumber + '.pdf';
    
    document.title = fileName.replace('.pdf', '');
    window.print();
}
```

#### 3. **Senior-Friendly Print Layout**
**Issue**: 60세 이상 관리자가 주문상세 글씨를 잘 못 읽음
**Location**: `OrderFormOrderTree.php` - `.print-order-details` CSS class

**Improvements Applied**:
- **Font size**: 8pt → 11pt (37% increase)
- **Layout**: Single column → 2-column layout
- **Typography**: Added font-weight: 600 for better readability
- **Spacing**: Increased padding and line-height
- **Content formatting**: Added extra line breaks for better separation

```css
.print-order-details {
    font-size: 11pt;           /* Increased from 8pt */
    line-height: 1.4;          /* Increased from 1.2 */
    font-weight: 600;          /* Added bold weight */
    columns: 2;                /* NEW: 2-column layout */
    column-gap: 5mm;           /* NEW: Gap between columns */
    column-rule: 1px solid #ddd; /* NEW: Divider line */
    padding: 3mm;              /* Increased from 2mm */
    min-height: 15mm;          /* Increased from 12mm */
}
```

### Order Completion Page Enhancement (December 10, 2025)

#### **JSON Data Display Fix**
**Issue**: 주문완료 페이지에서 상품 정보가 JSON 원본 데이터로 표시
**Location**: `C:\xampp\htdocs\MlangOrder_PrintAuto\OrderComplete_unified.php`

**Before**:
```
상품 정보: {"no":30,"session_id":"le3q1vva3bh7p1g9crk8pt8ku6","product_type":"envelope","jong":null...
```

**After**:
```
📝 상품 상세 정보
✉️ 봉투 주문
• 타입: 중봉투
• 용지: 모조지  
• 수량: 1,000매
• 인쇄면: 단면
• 주문타입: 인쇄만
```

**Solution Applied**:
- Added `getCategoryName()` function for database category lookup
- Implemented product-type-specific display logic
- Added support for all product types: envelope, sticker, namecard, merchandisebond, cadarok, littleprint, msticker
- Fallback handling for non-JSON and unknown product types

### User Experience Improvements (December 10, 2025)

#### **Delivery/Pickup Instructions**
**Issue**: 사용자가 퀵/다마스/방문수령 요청을 명확히 표시하지 않음
**Location**: `MlangOrder_PrintAuto/OnlineOrder_unified.php`

**Solution**: Added prominent red notice above request field:
```html
<div style="background: #ffebee; border: 1px solid #f8bbd9; padding: 1rem; margin-bottom: 1rem;">
    <p style="color: #d32f2f; font-size: 1.1rem; font-weight: bold;">
        🚚 퀵이나 다마스로 받거나 방문수령 시 아래 요청사항에 적어주세요
    </p>
</div>
```

### Technical Architecture Updates

#### **Database Schema Enhancements**:
- Business information integrated into `cont` field via ProcessOrder_unified.php
- Cart data properly structured with product-specific fields
- Session management improved for cart-to-order flow

#### **File Structure Maintained**:
- All modifications made to existing files without breaking changes
- Backward compatibility preserved
- Common file architecture maintained

## 🔑 Important Security & Best Practices

### Security Considerations:
- **SQL Injection Prevention**: Use prepared statements or `mysqli_real_escape_string()`
- **XSS Prevention**: Use `htmlspecialchars()` for output
- **File Upload Security**: Validate file types and sanitize filenames
- **Session Security**: Regenerate session IDs on login
- **Password Storage**: Never store plain text passwords

### Performance Optimization:
- **Database Queries**: Use indexes on frequently queried columns
- **Image Optimization**: Compress images before upload
- **Caching**: Implement browser caching for static resources
- **AJAX Loading**: Load heavy content asynchronously

### Code Quality Standards:
- **PHP Version**: Target PHP 7.4+ compatibility
- **Error Reporting**: Enable in development, disable in production
- **Logging**: Use `error_log()` for debugging
- **Comments**: Write in Korean for business logic, English for technical notes

## 📊 Database Migration Notes

### When Working with Database:
- Always backup before structural changes
- Test migrations on development first
- Use lowercase table names for cross-platform compatibility
- Maintain referential integrity

### Common SQL Patterns:
```sql
-- Check if table exists
SHOW TABLES LIKE 'table_name';

-- Safe column addition
ALTER TABLE table_name ADD COLUMN IF NOT EXISTS column_name VARCHAR(255);

-- Index creation for performance
CREATE INDEX idx_name ON table_name(column_name);
```

---

*Last Updated: September 2025*
*System Status: Production Ready*  
*Development Environment: Windows XAMPP*
*Git Branch: auth-system-fix*