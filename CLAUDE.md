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

## 🗄️ Database Naming Convention (Critical)

### ⚠️ XAMPP to Web Server Migration Rule

**Database Tables: ALWAYS LOWERCASE**
```sql
-- ✅ CORRECT (Web Server Compatible)
mlangprintauto_littleprint
mlangprintauto_transactioncate  
mlangprintauto_merchandisebond
shop_temp
mlangorder_printauto

-- ❌ INCORRECT (XAMPP Only)
MlangPrintAuto_littleprint
MlangPrintAuto_transactionCate
MlangPrintAuto_MerchandiseBond
```

**File/Directory Names: PRESERVE CASE**
```bash
# ✅ MAINTAIN ORIGINAL CASE
MlangPrintAuto/inserted/
MlangPrintAuto/NameCard/
MlangOrder_PrintAuto/

# ❌ DO NOT CHANGE FILE PATHS
mlangprintauto/inserted/  # This breaks includes!
```

**PHP Code Database References**
```php
// ✅ Always use lowercase in SQL queries
$query = "SELECT * FROM mlangprintauto_littleprint WHERE category = ?";

// ✅ But maintain case in file includes
include "MlangPrintAuto/inserted/index.php";
```

**Migration Checklist**
- [ ] All database table names converted to lowercase
- [ ] All SQL queries updated to use lowercase table names  
- [ ] File paths and directory names preserved as-is
- [ ] Include statements maintain original case sensitivity
- [ ] Test all file includes work on case-sensitive systems

**🚨 Critical Warning**
NEVER change directory/file names to match database convention:
- Database: `mlangprintauto_*` (lowercase)  
- Files: `MlangPrintAuto/` (original case)
- This prevents broken includes and 404 errors

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
│   ├── sticker/               # General sticker ordering (수식기반 계산)
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

## 🛠 Backend Management System

### Admin Panel Structure:
```
admin/
├── dashboard.php              # 관리자 대시보드
├── member_management/         # 회원 관리
│   ├── member_list.php           # 회원 목록
│   ├── member_edit.php           # 회원 정보 수정
│   ├── member_stats.php          # 회원 통계
│   └── business_members.php      # 사업자 회원 관리
├── order_management/          # 주문 관리  
│   ├── order_list.php            # 주문 목록
│   ├── order_detail.php          # 주문 상세보기
│   ├── order_status.php          # 주문 상태 관리
│   ├── order_export.php          # 주문 내역 출력
│   └── proof_management.php      # 교정 관리
├── product_management/        # 품목별 관리
│   ├── price_management.php      # 가격 관리
│   ├── category_management.php   # 카테고리 관리
│   ├── product_add_edit.php      # 제품 추가/수정
│   └── gallery_management.php    # 제품 갤러리 관리
├── file_management/           # 파일 관리
│   ├── uploaded_files.php        # 업로드된 파일 관리
│   ├── file_cleanup.php          # 파일 정리
│   └── storage_stats.php         # 용량 통계
├── database_management/       # DB 관리
│   ├── backup.php                # DB 백업
│   ├── optimize.php              # DB 최적화
│   └── migration.php             # 스키마 업데이트
├── payment_shipping/          # 결제/배송 관리
│   ├── payment_methods.php       # 결제 수단 관리
│   ├── shipping_management.php   # 배송 관리
│   └── invoice_management.php    # 세금계산서 관리
├── email_management/          # 이메일 관리
│   ├── email_templates.php       # 템플릿 관리
│   ├── email_logs.php            # 발송 내역
│   └── email_settings.php        # SMTP 설정
├── system_settings/           # 시스템 설정
│   ├── site_settings.php         # 사이트 설정
│   ├── user_permissions.php      # 권한 관리
│   └── system_logs.php           # 시스템 로그
└── statistics/                # 통계 및 리포트
    ├── sales_report.php          # 매출 통계
    ├── product_stats.php         # 제품별 통계
    └── customer_analysis.php     # 고객 분석
```

### Key Admin Functions:

#### **Order Processing Workflow**:
1. **주문 접수**: 자동 주문 알림, 관리자 확인
2. **제작 지시**: 제작팀에 작업 할당
3. **교정 관리**: 시안 업로드 → 고객 확인 → 승인/수정
4. **제작 완료**: 완료 확인 및 배송 준비
5. **배송 관리**: 택배 발송, 송장 번호 등록
6. **완료 처리**: 고객 확인, 피드백 수집

#### **Price Management System**:
- 품목별 가격 테이블 실시간 수정
- 수량별 할인율 설정
- 시즌별/이벤트 가격 적용
- 가격 변경 히스토리 관리

#### **File Management System**:
- 업로드된 파일 분류/정리
- 용량 관리 및 자동 정리
- 파일 다운로드 통계
- 백업 및 복원 기능

#### **Email Template Management**:
- 주문확인 이메일 템플릿
- 교정 발송 이메일
- 배송 알림 이메일  
- 완료 확인 이메일

#### **Statistics & Reports**:
- 일/월/년 매출 통계
- 제품별 주문 현황
- 고객 분석 리포트
- 재방문율 분석

### Missing Admin Components (구현 필요):
1. **교정 관리 시스템**: 시안 업로드, 승인 워크플로우
2. **실시간 알림**: 신규 주문, 교정 승인 등
3. **고객 소통 관리**: 문의 답변, 교정 커뮤니케이션
4. **재고 관리**: 용지/자재 재고 추적
5. **API 연동**: 결제사, 택배사, 회계 프로그램

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
- **Main config**: `db.php` (database: dsp1830)
- **Price calculation**: Each product has individual calculation logic
- **File uploads**: Organized by date/IP in `uploads/` directory
- **Common functions**: `includes/functions.php`
- **Authentication**: `includes/auth.php`, `includes/auth_functions.php`
- **Session handling**: PHP native sessions with enhanced cleanup

## 🪙 Product Systems

### 1. **Leaflet/Flyer System** (`MlangPrintAuto/inserted/`)
- Dynamic price calculation based on paper type, size, quantity
- File upload with preview functionality
- Uses common file structure with AJAX integration

### 2. **Business Cards** (`MlangPrintAuto/NameCard/`)
- Specialized pricing for different card types
- Design options and file upload support
- Real-time price updates

### 3. **General Stickers** (`MlangPrintAuto/sticker_new/index.php`)
- **Complex formula-based pricing** calculation (수식계산, not table-based)
- **Material options**: 아트지유광, 아트지무광코팅, 아트지비코팅, 투명스티커, 강접아트유광코팅(90g), 초강접아트유광코팅, 초강접아트비코팅, 유포지(80g), 은데드롱(25g),투명스티커(25g),모조지비코팅(80g), 크라프트스티커(57g),금지스티커-전화문의, 금박스티커-전화문의,롤스티커-전화문의 등
- **Custom sizing**: 가로(garo) x 세로(sero) 직접 입력 (10mm~560mm)
- **Quantity tiers**: 500매~100,000매 with bulk pricing (mesu)
- **Design fees**: 편집비 선택 (인쇄만/기본편집+10,000원/고급편집+30,000원)
- **Shape options**: 기본사각형, 사각도무송, 귀둘이(라운드),원형, 타원형, 모양도무송 (domusong)
- **Modern interface**: AJAX price calculation, drag-drop file upload
- **Integration**: Common header/footer applied, shop_temp cart system

### 4. **Magnetic Stickers** (`MlangPrintAuto/msticker/`)
- Specialized magnetic material stickers
- Standard size options with quantity-based pricing
- AJAX price calculation system
- Separate from general stickers

### 5. **Coupon/Voucher System** (`MlangPrintAuto/MerchandiseBond/`)
- **상품권/쿠폰 시스템**: Dynamic transaction categories
- **Database tables**: `mlangprintauto_transactioncate`, `mlangprintauto_merchandisebond`
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

## 🎨 Frontend Architecture & UI/UX

### Global Layout Structure:
```
전체 레이아웃
├── 공통 CSS/헤더/네비게이션 (header.php, css/styles.css)
├── 메인 콘텐츠 영역
│   ├── 좌측: 제품 갤러리 (이미지 슬라이더, 더보기 팝업)
│   └── 우측: 계산 시스템 (실시간 가격계산)
├── 제품별 설명 및 유의사항
├── 하단 푸터 (footer.php)
└── 사이드바 요소
    ├── 카카오톡 상담 위젯
    ├── TocPlus 상담창
    └── 고정 액션 버튼들
```

### Common Frontend Components:
- **Gallery Component**: 제품 이미지 갤러리 시스템
  - 이미지 슬라이더 with 썸네일 네비게이션
  - 더보기 팝업 갤러리 (라이트박스 효과)
  - 반응형 디자인 (모바일 최적화)
  
- **Calculator Component**: 실시간 가격 계산기
  - AJAX 기반 동적 계산
  - 수량/옵션 변경 시 즉시 업데이트
  - VAT 포함/불포함 토글
  
- **File Upload Component**: 다중 파일 업로드
  - Drag & Drop 지원
  - 파일 타입 검증 (PDF, AI, PSD, JPG, PNG)
  - 업로드 진행률 표시
  - 파일 미리보기 기능
  
- **Chat Integration**: 실시간 상담 시스템
  - 카카오톡 플러스친구 연동
  - TocPlus 상담창 위젯
  - 고정 위치 상담 버튼
  
- **Modal System**: 팝업 관리
  - 갤러리 확대보기
  - 교정보기 팝업
  - 주문완료 확인창
  - 로그인 모달 (login_modal.php)

### Missing Frontend Components (구현 필요):
- **교정보기 시스템**: 주문 후 시안 확인 프로세스
- **교정확인창**: 고객 승인/수정 요청 인터페이스  
- **진행상황 트래커**: 주문 진행 단계 표시
- **알림 시스템**: 실시간 주문 상태 알림

### UI/UX Design System:
- **Noto Sans KR** font throughout
- Responsive grid layouts  
- Color-coded product categories
- Hover animations and transitions
- Mobile-friendly design
- Accessibility considerations (contrast, semantic markup)

### Left Navigation Menu:
- Product-specific active states
- Color-coded hover effects per product type
- Order button prominence

### File Upload Components:
- Drag-and-drop support in modern browsers
- Multiple file selection
- File type validation
- Progress indicators

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
- **Leaflets/Posters**: `mlangprintauto_littleprint` table lookup
- **Coupons**: `mlangprintauto_merchandisebond` table lookup
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

## 🗄 Database Configuration

### Connection Details:
```php
// Primary database connection (db.php)
$host = "localhost";
$user = "dsp1830"; 
$password = "ds701018";
$dataname = "dsp1830";
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
- `mlangprintauto_*` - Product-specific pricing tables
  - `mlangprintauto_littleprint` - Poster/leaflet pricing
  - `mlangprintauto_transactioncate` - Coupon categories
  - `mlangprintauto_merchandisebond` - Coupon pricing
  - `shop_d1`, `shop_d2`, `shop_d3`, `shop_d4` - Sticker calculation tables
- `shop_temp` - Shopping cart storage with product-specific fields
- `mlangorder_printauto` - Unified order storage
- `quote_log` - Quote generation history and customer details
- `quote_items` - Detailed quote item specifications
- `page` - CMS content management

## 🚀 Development Workflow

### Local Development Setup:
1. Install XAMPP (Apache + MySQL + PHP)
2. Clone repository to `C:\xampp\htdocs\`
3. Import database: `dsp1830.sql` via phpMyAdmin
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

## 📝 Recent Major Updates

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

## 🔒 Enhanced Security & Performance

### Security Hardening:

#### **File Upload Security**:
```php
// Enhanced file validation
function validateUploadFile($file) {
    $allowedTypes = ['pdf', 'ai', 'psd', 'jpg', 'jpeg', 'png', 'gif'];
    $allowedMimeTypes = [
        'application/pdf',
        'application/postscript', 
        'image/jpeg', 
        'image/png'
    ];
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileMime = mime_content_type($file['tmp_name']);
    
    return in_array($fileExt, $allowedTypes) && 
           in_array($fileMime, $allowedMimeTypes) &&
           $file['size'] <= 50 * 1024 * 1024; // 50MB limit
}
```

#### **CSRF Protection**:
```php
// Generate CSRF token for forms
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}
```

#### **Rate Limiting**:
```php
// API call rate limiting for price calculations
function checkRateLimit($ip, $endpoint) {
    $key = "rate_limit:{$endpoint}:{$ip}";
    $requests = $_SESSION[$key] ?? 0;
    
    if ($requests > 100) { // 100 requests per session
        http_response_code(429);
        die('Rate limit exceeded');
    }
    
    $_SESSION[$key] = $requests + 1;
}
```

#### **Admin Access Control**:
- IP 화이트리스트 설정
- 2단계 인증 (2FA) 도입
- 관리자 세션 타임아웃 단축
- 로그인 시도 제한

### Performance Optimization:

#### **Database Indexing**:
```sql
-- 주문 조회 성능 향상
CREATE INDEX idx_order_date ON mlangorder_printauto(order_date);
CREATE INDEX idx_member_orders ON mlangorder_printauto(member_id, order_date);
CREATE INDEX idx_product_type ON mlangorder_printauto(product_type);

-- 가격 계산 성능 향상  
CREATE INDEX idx_price_lookup ON mlangprintauto_littleprint(paper_type, size, quantity);
CREATE INDEX idx_sticker_calc ON shop_d1(jong, mesu);
```

#### **Image Optimization**:
```php
// Auto image compression on upload
function compressImage($source, $destination, $quality = 80) {
    $info = getimagesize($source);
    
    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            imagejpeg($image, $destination, $quality);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagepng($image, $destination, 8);
            break;
    }
    imagedestroy($image);
}
```

#### **Cache Strategy**:
```php
// Price calculation result caching
function getCachedPrice($cacheKey, $callback) {
    if (isset($_SESSION['price_cache'][$cacheKey])) {
        return $_SESSION['price_cache'][$cacheKey];
    }
    
    $result = $callback();
    $_SESSION['price_cache'][$cacheKey] = $result;
    return $result;
}
```

#### **CDN Integration**:
- 정적 파일 (CSS, JS, 이미지) CDN 배포
- 제품 갤러리 이미지 최적화
- 브라우저 캐싱 헤더 설정

#### **Database Connection Pooling**:
```php
// Connection pool management
class DatabasePool {
    private static $connections = [];
    private static $maxConnections = 10;
    
    public static function getConnection() {
        if (count(self::$connections) < self::$maxConnections) {
            $conn = new mysqli($host, $user, $pass, $db);
            self::$connections[] = $conn;
            return $conn;
        }
        return array_pop(self::$connections);
    }
}
```

### Code Quality Standards:
- **PHP Version**: Target PHP 7.4+ compatibility
- **Error Reporting**: Enable in development, disable in production
- **Logging**: Use `error_log()` for debugging
- **Comments**: Write in Korean for business logic, English for technical notes

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
📄 상품 상세 정보
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

## 📋 Missing System Components & Implementation Plan

### 🚨 Critical Missing Components:

#### **Frontend Components (구현 필요)**:
1. **교정보기 시스템**
   - Location: `proof_viewer/`
   - 주문 후 시안 확인 프로세스
   - 고객 승인/수정 요청 워크플로우

2. **교정확인창**  
   - Modal popup for proof approval
   - 수정 요청 코멘트 기능
   - 승인/거부 버튼 액션

3. **TocPlus 상담 연동**
   - 실시간 상담 위젯 integration
   - 카카오톡과 통합 상담 인터페이스

4. **반응형 갤러리**
   - 제품 이미지 뷰어 고도화
   - 라이트박스 효과
   - 썸네일 네비게이션

5. **진행상황 트래커**
   - 주문 진행 단계 시각화
   - 실시간 상태 업데이트
   - 예상 완료일 표시

#### **Backend Management (구현 필요)**:
1. **교정 관리 시스템**
   - 시안 파일 업로드/관리
   - 승인 상태 추적
   - 고객 피드백 관리

2. **파일 관리자**
   - 업로드된 파일 정리/삭제
   - 용량 모니터링
   - 자동 백업 시스템

3. **통계 대시보드**
   - 실시간 매출 현황
   - 제품별 주문 통계
   - 고객 행동 분석

4. **고객 소통 관리**
   - 문의 관리 시스템
   - 이메일 자동 발송
   - SMS 알림 기능

5. **배송 추적 연동**
   - 택배사 API 연동
   - 송장 번호 자동 등록
   - 배송 상태 알림

#### **Integration Points (연동 필요)**:
1. **결제 모듈 다양화**
   - 카드결제 PG 연동
   - 실시간 계좌이체
   - 간편결제 (카카오페이, 네이버페이)

2. **SMS 알림 서비스**
   - 주문 접수 알림
   - 교정 발송 알림  
   - 배송 완료 알림

3. **재고 관리 시스템**
   - 용지/자재 재고 추적
   - 자동 발주 알림
   - 재고 부족 경고

4. **회계 연동**
   - 세금계산서 자동 발행
   - 매출 데이터 연동
   - 부가세 신고 데이터

### 🎯 Implementation Priority:

#### **Phase 1: Core Missing Features (1-2개월)**
- [ ] 교정보기/확인 시스템 구현
- [ ] 백엔드 주문 관리 패널 완성
- [ ] 파일 관리 시스템 구축
- [ ] 기본 통계 대시보드

#### **Phase 2: User Experience Enhancement (2-3개월)**
- [ ] 갤러리 시스템 고도화
- [ ] 실시간 상담 연동 (TocPlus)
- [ ] 주문 진행 상황 추적
- [ ] 모바일 최적화

#### **Phase 3: System Stabilization (3-4개월)**
- [ ] 보안 강화 (CSRF, Rate Limiting)
- [ ] 성능 최적화 (캐싱, DB 인덱싱)
- [ ] 모니터링 시스템 도입
- [ ] 자동화 백업 시스템

#### **Phase 4: Business Expansion (4-6개월)**
- [ ] 결제 모듈 다양화
- [ ] SMS/이메일 자동화
- [ ] 재고 관리 시스템
- [ ] 모바일 앱 개발
- [ ] API 외부 제공

### 📊 Success Metrics:
- 주문 처리 시간 50% 단축
- 고객 문의 응답 시간 24시간 → 2시간
- 교정 승인 프로세스 자동화 90%
- 모바일 주문 비율 40% 달성
- 관리자 업무 효율성 70% 향상

## 🗂 Critical Architecture Notes

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

## 🔧 Recent Updates (September 2025)

### Leaflet System Refactoring & UI Improvements (September 2, 2025)

#### **1. Code Structure Optimization**
**Issue**: 전단지 시스템에 1000+줄의 인라인 CSS와 중복 코드 존재
**Location**: `C:\xampp\htdocs\MlangPrintAuto\inserted\`

**Solutions Applied**:
- **인라인 CSS 분리**: `css/leaflet-inline.css` 생성 (322줄 → 별도 파일)
- **설정 파일 생성**: `config/leaflet.config.php` (중앙화된 설정)
- **공통 함수 라이브러리**: `includes/leaflet_functions.php` (재사용 가능한 함수들)
- **파일 크기 감소**: 1500줄 → 475줄로 대폭 축소

#### **2. UI Layout Improvements**
**Issue**: 페이지 타이틀과 헤더가 너무 크고 여백이 과도함
**Location**: `css/leaflet-compact.css`

**Changes Applied**:
```css
/* 페이지 타이틀 크기 50% 축소 */
.page-title {
    padding: 25px → 12px;
    margin-bottom: 30px → 10px;
}
.page-title h1 { font-size: 2.2rem → 1.1rem; }
.page-title p { font-size: 1rem → 0.5rem; }

/* Calculator-header 디자인 변경 */
.calculator-header {
    padding: 18px → 9px (상하 패딩 50% 축소);
    margin-bottom: 25px → 12px;
    background: 화려한 그라데이션 → rgba(204, 204, 204, 0.5);
    color: white → #333;
}

/* 그리드 여백 1/3로 축소 */
.leaflet-grid { margin: 30px → 10px; }
```

#### **3. Dropdown Auto-Selection Fix**
**Issue**: 드롭다운에서 "선택해주세요" 상태로 남아있어 사용자가 수동으로 선택해야 함
**Location**: `index.php`, `js/leaflet-compact.js`

**Solutions Applied**:
- **PHP 기본값 강화**: 첫 번째 옵션 자동 `selected` 설정
- **JavaScript 자동 선택**: 빈 값 감지 시 첫 번째 실제 값 자동 선택
- **이벤트 연동**: `change` 이벤트 자동 발생으로 후속 동작 실행
- **타이밍 최적화**: 1.5초 대기 후 실행으로 안정성 확보

```javascript
// 자동 선택 로직
const selects = ['MY_Fsd', 'PN_type', 'POtype'];
selects.forEach(selectName => {
    const selectElement = document.querySelector(`select[name="${selectName}"]`);
    const firstValidIndex = selectElement.options[0].value === '' ? 1 : 0;
    selectElement.selectedIndex = firstValidIndex;
    selectElement.dispatchEvent(new Event('change'));
});
```

### Files Created/Modified:
- ✅ **신규 생성**: `css/leaflet-inline.css` (인라인 CSS 분리)
- ✅ **신규 생성**: `config/leaflet.config.php` (설정 중앙화)
- ✅ **신규 생성**: `includes/leaflet_functions.php` (공통 함수)
- ✅ **수정**: `index.php` (인라인 코드 제거, 드롭다운 개선)
- ✅ **수정**: `css/leaflet-compact.css` (UI 레이아웃 최적화)
- ✅ **수정**: `js/leaflet-compact.js` (자동 선택 로직 추가)

### Impact:
- **유지보수성 향상**: 모듈화된 구조로 코드 관리 용이
- **사용자 경험 개선**: 자동 드롭다운 선택으로 편의성 증대
- **디자인 최적화**: 컴팩트한 레이아웃으로 공간 효율성 증가
- **재사용성 증가**: 공통 함수를 다른 제품 페이지에서도 활용 가능

### Leaflet System Architecture Documentation (September 3, 2025)

#### **완성된 모듈화 구조**:
```php
MlangPrintAuto/inserted/
├── index.php              # 메인 페이지 (475줄로 최적화, 1500줄→475줄 70% 감소)
├── css/
│   ├── leaflet-compact.css    # 컴팩트 레이아웃 스타일
│   └── leaflet-inline.css     # 분리된 인라인 CSS (322줄)
├── config/
│   └── leaflet.config.php     # 중앙화된 설정 (87줄)
├── includes/
│   └── leaflet_functions.php  # 공통 함수 라이브러리 (226줄)
└── js/
    └── leaflet-compact.js     # 자동 선택 로직 포함
```

#### **주요 기술적 개선사항**:
- **코드 분리**: 1500줄 단일 파일 → 4개 모듈로 분리
- **함수 라이브러리**: 재사용 가능한 공통 함수 집중화
- **설정 중앙화**: 페이지 설정 및 상수를 config 파일로 통합
- **자동화**: 드롭다운 자동 선택 기능으로 UX 향상
- **UI 최적화**: 페이지 타이틀 50% 축소, 여백 최적화

#### **검증된 기능**:
- ✅ **Database Integration**: mlangprintauto_transactioncate 테이블 연동
- ✅ **Dynamic Options**: 실시간 드롭다운 옵션 로딩
- ✅ **Auto Selection**: 첫 번째 유효 옵션 자동 선택
- ✅ **Price Calculation**: AJAX 기반 실시간 가격 계산
- ✅ **File Upload**: 다중 파일 업로드 지원
- ✅ **Shopping Cart**: 장바구니 시스템 통합
- ✅ **Gallery System**: 통합 갤러리 컴포넌트
- ✅ **Mobile Responsive**: 반응형 디자인 완성

#### **Production Ready Status**:
모든 기능 테스트 완료 및 실서버 배포 준비 상태

## 🔧 Recent Updates (September 3, 2025)

### Git Repository Setup & Synchronization
**Location**: GitHub - https://github.com/songyoungsoo/duson-print-system.git
**Branch**: auth-system-fix

#### **1. Repository Cleanup**
- **Large Files Removed**: SQL dumps, ZIP files, uploaded images excluded from Git
- **Git History Cleaned**: Used git filter-branch to remove large files from history
- **.gitignore Enhanced**: Added patterns for SQL, ZIP, backup folders, and large files

#### **2. Synchronization Setup**
- **GitHub Repository**: Successfully pushed to remote repository
- **Code Only**: Only PHP code and configuration files are tracked
- **Images/Uploads**: Excluded from version control (managed locally)

#### **3. Work Synchronization Commands**
```bash
# At home - first time
git clone https://github.com/songyoungsoo/duson-print-system.git
cd duson-print-system
git checkout auth-system-fix

# At home - update existing
git fetch origin
git pull origin auth-system-fix

# After making changes
git add .
git commit -m "description"
git push origin auth-system-fix
```

---

*Last Updated: September 3, 2025*
*System Status: Production Ready*  
*Development Environment: Windows XAMPP*
*Git Branch: auth-system-fix*
*Implementation Status: Phase 1 - Leaflet System Optimized & Git Synchronized*
*Next Review: October 2025*
특히 admin 관련부분은 수정할려면 반드시 사전계획을 세우고 물어보고 수정 할것