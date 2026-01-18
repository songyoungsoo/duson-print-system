# Duson Print System - Backend Architecture Documentation

**Version**: 1.0
**Last Updated**: 2026-01-18
**System**: PHP 7.4+ / MySQL 5.7+ / Apache 2.4+

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Core Configuration](#2-core-configuration)
3. [Product Modules Structure](#3-product-modules-structure)
4. [Order Processing System](#4-order-processing-system)
5. [Admin System](#5-admin-system)
6. [Database Schema](#6-database-schema)
7. [Key Service Classes](#7-key-service-classes)
8. [SSOT Architecture](#8-ssot-architecture)
9. [Installation File List](#9-installation-file-list)

---

## 1. System Overview

### Architecture Pattern
The Duson Print System follows a **procedural PHP architecture** with service classes for core business logic. The system uses a **hybrid approach** combining legacy code with modern service-oriented patterns.

### Technology Stack
| Component | Technology |
|-----------|------------|
| Backend | PHP 7.4+ |
| Database | MySQL 5.7+ (utf8mb4) |
| Web Server | Apache 2.4+ |
| Session | File-based (8-hour lifetime) |
| Authentication | Custom session + Remember Me tokens |

### Directory Structure
```
/var/www/html/
├── db.php                              # Database connection (auto environment detection)
├── config.env.php                      # Environment configuration class
├── includes/                           # Core service classes and helpers
├── mlangprintauto/                     # Product modules (9 products)
├── mlangorder_printauto/               # Order processing system
├── admin/                              # Admin panel
├── css/                                # Stylesheets
├── js/                                 # JavaScript files
├── ImgFolder/                          # Uploaded files storage
└── INSTALL_DOCS/                       # Installation documentation
```

---

## 2. Core Configuration

### 2.1 Database Connection (`db.php`)

The database connection system provides automatic environment detection:

```php
// Key variables after including db.php:
$db         // mysqli connection object
$conn       // Alias for $db (legacy compatibility)
$admin_url  // Auto-detected base URL
$Homedir    // Same as $admin_url
```

**Features**:
- Automatic local/production environment detection
- UTF-8 (utf8mb4) charset enforcement
- Table name mapping for legacy compatibility
- Safe query wrapper functions (`safe_mysqli_query`, `safe_mysqli_prepare`)

### 2.2 Environment Detection (`config.env.php`)

The `EnvironmentDetector` class handles environment-specific configuration:

```php
class EnvironmentDetector {
    public static function detectEnvironment();      // Returns 'local' or 'production'
    public static function getDatabaseConfig();      // Returns DB config array
    public static function isLocal();                // Boolean check
    public static function isProduction();           // Boolean check
    public static function getSmtpConfig();          // Email configuration
}

// Convenience functions:
get_db_config()           // Returns database configuration array
is_local_environment()    // Boolean - true if localhost
is_production_environment() // Boolean - true if production
get_current_environment() // Returns 'local' or 'production'
```

**Environment Detection Criteria**:
- Local: `localhost`, `127.0.0.1`, `xampp/wamp/mamp` in path
- Production: `dsp114.com`, `dsp1830.shop` domains

### 2.3 Authentication System (`includes/auth.php`)

Session-based authentication with Remember Me functionality:

| Feature | Value |
|---------|-------|
| Session Lifetime | 8 hours (28,800 seconds) |
| Remember Me | 30 days |
| Token Storage | `remember_tokens` table |
| Password Hash | `password_hash()` (bcrypt) |

**Key Variables Set by auth.php**:
```php
$is_logged_in   // Boolean - user login status
$user_name      // Current user's name
$login_message  // Login error/success message
```

---

## 3. Product Modules Structure

### 3.1 Product Folder Mapping (MANDATORY)

The following folder names are **immutable** - never change them:

| # | Product Name | Folder Name | Description |
|---|--------------|-------------|-------------|
| 1 | 전단지 (Flyer) | `inserted` | Standard flyers |
| 2 | 스티커 | `sticker_new` | Current sticker system |
| 3 | 자석스티커 | `msticker` | Magnet stickers |
| 4 | 명함 | `namecard` | Business cards |
| 5 | 봉투 | `envelope` | Envelopes |
| 6 | 포스터 | `littleprint` | Posters (small print) |
| 7 | 상품권 | `merchandisebond` | Gift vouchers |
| 8 | 카다록 | `cadarok` | Catalogs |
| 9 | NCR양식지 | `ncrflambeau` | NCR forms |

### 3.2 Standard Product Module Structure

Each product folder follows this pattern:

```
/mlangprintauto/{product}/
├── index.php                    # Product ordering page
├── add_to_basket.php            # Cart addition API (JSON response)
├── calculate_price_ajax.php     # Price calculation API (JSON response)
├── inc.php                      # Product-specific includes
├── config/                      # Product configuration files
├── css/                         # Product-specific styles
├── js/                          # Product-specific JavaScript
├── includes/                    # Product-specific helpers
└── upload/                      # Temporary file uploads
```

### 3.3 Key Product Files

#### `add_to_basket.php` Pattern
```php
// Required includes
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
require_once __DIR__ . '/../../includes/DataAdapter.php';

// Standard flow:
// 1. Validate POST data
// 2. Process file uploads (StandardUploadHandler)
// 3. Convert to standard format (DataAdapter)
// 4. Insert into shop_temp table
// 5. Return JSON response
```

#### `calculate_price_ajax.php` Pattern
```php
// Returns JSON with price data
// Uses product-specific price tables (mlangprintauto_{product})
// Response format:
{
    "success": true,
    "price": 50000,        // Supply price (VAT excluded)
    "vat_price": 55000     // VAT included price
}
```

---

## 4. Order Processing System

### 4.1 Order Flow

```
Product Page (index.php)
        ↓
add_to_basket.php → shop_temp (cart table)
        ↓
Cart Page (mlangprintauto/cart.php)
        ↓
ProcessOrder_unified.php → mlangorder_printauto (orders table)
        ↓
OrderComplete_universal.php (confirmation page)
```

### 4.2 Key Order Processing Files

| File | Purpose |
|------|---------|
| `ProcessOrder_unified.php` | Main order processor - converts cart to order |
| `OrderComplete_universal.php` | Order confirmation display |
| `OnlineOrder_unified.php` | Order form page |
| `send_order_email.php` | Email notification sender |
| `WindowSian.php` | Order status management |

### 4.3 ProcessOrder_unified.php Flow

```php
// 1. Receive POST data (customer info, cart items)
// 2. Validate required fields
// 3. Handle member vs guest checkout
// 4. Process each cart item:
//    - Convert via DataAdapter::legacyToStandard()
//    - Copy uploaded files
//    - Insert into mlangorder_printauto
// 5. Clear cart (shop_temp)
// 6. Send email notification
// 7. Redirect to OrderComplete
```

---

## 5. Admin System

### 5.1 Admin Directory Structure

```
/admin/
├── index.php                    # Admin login page
├── dashboard.php                # Main dashboard
├── config.php                   # Admin configuration
├── top.php                      # Admin header
├── title.php                    # Page title component
│
├── mlangprintauto/              # Product management
│   ├── cadarok/
│   ├── envelope/
│   ├── inserted/
│   ├── littleprint/
│   ├── merchandisebond/
│   ├── msticker/
│   ├── namecard/
│   ├── ncrflambeau/
│   └── sticker_new/
│
├── MlangOrder_PrintAuto/        # Order management
├── member/                      # User management
├── tax_invoice_manager.php      # Tax invoice handling
├── customer_inquiries.php       # Customer inquiry management
└── visitors.php                 # Visitor statistics
```

### 5.2 Admin Authentication

Admin uses the same `includes/auth.php` system with the `users` table.

---

## 6. Database Schema

### 6.1 Core Tables

#### `shop_temp` (Cart Table)
```sql
-- Primary cart storage for pending orders
CREATE TABLE shop_temp (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    product_type VARCHAR(50) NOT NULL DEFAULT 'sticker',

    -- Legacy fields (for backward compatibility)
    jong VARCHAR(200),           -- Material (sticker)
    garo VARCHAR(50),            -- Width (sticker)
    sero VARCHAR(50),            -- Height (sticker)
    mesu VARCHAR(50),            -- Quantity
    domusong VARCHAR(200),       -- Shape (sticker)
    MY_type VARCHAR(50),         -- Product type code
    MY_Fsd VARCHAR(50),          -- Paper type code
    PN_type VARCHAR(50),         -- Size code
    MY_amount DECIMAL(10,2),     -- Quantity value
    POtype VARCHAR(10),          -- Print sides
    ordertype VARCHAR(50),       -- Order type (print/design)

    -- Standard fields (new schema)
    spec_type VARCHAR(50),       -- Standardized type
    spec_material VARCHAR(50),   -- Standardized material
    spec_size VARCHAR(100),      -- Standardized size
    spec_sides VARCHAR(20),      -- Print sides (text)
    spec_design VARCHAR(20),     -- Design option
    quantity_value DECIMAL(10,2),
    quantity_unit VARCHAR(10) DEFAULT '매',
    quantity_sheets INT,
    quantity_display VARCHAR(100),

    -- Price fields
    st_price DECIMAL(10,2),      -- Supply price
    st_price_vat DECIMAL(10,2),  -- VAT included price
    price_supply INT DEFAULT 0,
    price_vat INT DEFAULT 0,
    price_vat_amount INT DEFAULT 0,

    -- Options (JSON)
    additional_options TEXT,
    premium_options TEXT,

    -- File handling
    uploaded_files TEXT,         -- JSON array of uploaded files
    ImgFolder VARCHAR(255),
    ThingCate VARCHAR(255),

    -- Metadata
    data_version TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_session (session_id),
    INDEX idx_product_type (product_type)
);
```

#### `mlangorder_printauto` (Orders Table)
```sql
-- Confirmed orders storage
CREATE TABLE mlangorder_printauto (
    no MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Customer info
    name VARCHAR(250),
    email TEXT,
    phone VARCHAR(20),
    Hendphone VARCHAR(20),
    zip VARCHAR(10),
    zip1 VARCHAR(250),           -- Address line 1
    zip2 VARCHAR(250),           -- Address line 2

    -- Order details
    Type VARCHAR(250),           -- Product description
    Type_1 TEXT,                 -- JSON order details
    product_type VARCHAR(50),
    quantity DECIMAL(10,2) DEFAULT 1.00,
    unit VARCHAR(10) DEFAULT '개',

    -- Standard fields (Phase 3)
    spec_type VARCHAR(50),
    spec_material VARCHAR(50),
    spec_size VARCHAR(100),
    spec_sides VARCHAR(20),
    spec_design VARCHAR(20),
    quantity_value DECIMAL(10,2),
    quantity_unit VARCHAR(10) DEFAULT '매',
    quantity_sheets INT,
    quantity_display VARCHAR(100),

    -- Price
    money_1 VARCHAR(20),         -- Legacy: supply price
    money_2 VARCHAR(20),         -- Legacy: VAT
    money_3 VARCHAR(20),         -- Legacy: VAT included
    price_supply INT DEFAULT 0,
    price_vat INT DEFAULT 0,

    -- Options (JSON)
    premium_options TEXT,
    additional_options TEXT,

    -- File handling
    uploaded_files TEXT,
    ImgFolder TEXT,
    ThingCate VARCHAR(250),

    -- Status
    OrderStyle VARCHAR(100) DEFAULT 'no',  -- Order status
    proofreading_confirmed TINYINT(1) DEFAULT 0,

    -- Timestamps
    date DATETIME NOT NULL,
    data_version TINYINT DEFAULT 1,

    INDEX idx_date (date),
    INDEX idx_email (email(100)),
    INDEX idx_product_type (product_type)
);
```

#### `users` (User Accounts)
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    postcode VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 6.2 Product Price Tables

Each product has its own price table:

| Table Name | Product |
|------------|---------|
| `mlangprintauto_inserted` | Flyers/Leaflets |
| `mlangprintauto_sticker` | Stickers |
| `mlangprintauto_namecard` | Business cards |
| `mlangprintauto_envelope` | Envelopes |
| `mlangprintauto_littleprint` | Posters |
| `mlangprintauto_merchandisebond` | Gift vouchers |
| `mlangprintauto_cadarok` | Catalogs |
| `mlangprintauto_ncrflambeau` | NCR forms |
| `mlangprintauto_msticker` | Magnet stickers |

### 6.3 Reference Tables

| Table | Purpose |
|-------|---------|
| `mlangprintauto_transactioncate` | Category/option name lookups |
| `unit_codes` | Unit code definitions (R/S/B/V) |
| `paper_standard_master` | Paper size standards (A4, B4, etc.) |
| `product_unit_config` | Product-specific unit configuration |

---

## 7. Key Service Classes

### 7.1 QuantityFormatter (SSOT for Quantities)

**Location**: `includes/QuantityFormatter.php`

The single source of truth for all quantity formatting:

```php
// Unit codes
const UNIT_CODES = [
    'R' => '연',  // Ream - flyers
    'S' => '매',  // Sheet - stickers, cards
    'B' => '부',  // Bundle - catalogs
    'V' => '권',  // Volume - NCR forms
    'P' => '장',  // Piece - posters
    'E' => '개',  // Each - miscellaneous
];

// Product defaults
const PRODUCT_UNITS = [
    'inserted' => 'R',
    'sticker_new' => 'S',
    'namecard' => 'S',
    'envelope' => 'S',
    'cadarok' => 'B',
    'ncrflambeau' => 'V',
    'littleprint' => 'P',
];

// Usage
QuantityFormatter::format(0.5, 'R', 2000);  // "0.5연 (2,000매)"
QuantityFormatter::format(1000, 'S');        // "1,000매"
```

### 7.2 ProductSpecFormatter

**Location**: `includes/ProductSpecFormatter.php`

Formats product specifications for display:

```php
$formatter = new ProductSpecFormatter($db);

// Returns standardized 2-line format
$result = $formatter->format($item);
// ['line1' => '규격', 'line2' => '옵션', 'additional' => '추가옵션']

// HTML output
$html = $formatter->formatHtml($item);

// Unified 4-line format
$unified = $formatter->formatUnified($item);
// ['line1', 'line2', 'line3', 'line4']
```

### 7.3 DataAdapter

**Location**: `includes/DataAdapter.php`

Converts legacy field names to standardized format:

```php
// Legacy to standard conversion
$standard = DataAdapter::legacyToStandard($legacyData, 'namecard');
// Returns: [spec_type, spec_material, spec_size, quantity_value, etc.]

// Validation
$valid = DataAdapter::validateStandardData($standardData, 'namecard');

// Price sanitization
$price = DataAdapter::sanitizePrice("1,000,000");  // Returns 1000000
```

### 7.4 StandardUploadHandler

**Location**: `includes/StandardUploadHandler.php`

Unified file upload handling:

```php
// Process upload
$result = StandardUploadHandler::processUpload('namecard', $_FILES);

// Result structure:
[
    'success' => true,
    'files' => [
        [
            'original_name' => 'file.jpg',
            'saved_name' => 'file.jpg',
            'path' => '/full/path/to/file.jpg',
            'web_url' => '/ImgFolder/path/file.jpg',
            'size' => 12345
        ]
    ],
    'img_folder' => 'path/to/folder',
    'error' => ''
]

// Copy files for order
StandardUploadHandler::copyFilesForOrder($orderNo, $imgFolder, $uploadedFiles);
```

### 7.5 ImagePathResolver

**Location**: `includes/ImagePathResolver.php`

Resolves image paths across legacy and new systems:

```php
// Resolve file path
$path = ImagePathResolver::resolve($orderNo, $filename, $row);

// Get web URL
$url = ImagePathResolver::getWebUrl($orderNo, $row);

// Check if legacy order
$isLegacy = ImagePathResolver::isLegacyOrder($orderNo);
// Legacy cutoff: order_no < 103700
```

### 7.6 PriceCalculationService

**Location**: `includes/PriceCalculationService.php`

Centralized price calculation for all products:

```php
$priceService = new PriceCalculationService($db);

$result = $priceService->calculate('namecard', [
    'style_id' => 1,
    'section_id' => 2,
    'quantity' => 1000,
    'po_type' => 2
]);
// Returns: ['price' => 50000, 'vat_price' => 55000]
```

---

## 8. SSOT Architecture

### 8.1 Single Source of Truth Principle

The system follows SSOT for critical business logic:

| Domain | SSOT Location | Description |
|--------|---------------|-------------|
| Quantity Formatting | `QuantityFormatter.php` | All quantity display strings |
| Product Specs | `ProductSpecFormatter.php` | All specification formatting |
| Data Conversion | `DataAdapter.php` | Legacy to standard conversion |
| File Uploads | `StandardUploadHandler.php` | All file handling |
| Price Calculation | `PriceCalculationService.php` | All price lookups |

### 8.2 New Schema Fields

The system uses dual-write strategy (legacy + standard fields):

**Legacy Fields** (backward compatible):
- `MY_type`, `MY_Fsd`, `PN_type`, `POtype`
- `st_price`, `st_price_vat`
- `mesu`, `MY_amount`

**Standard Fields** (new schema):
- `spec_type`, `spec_material`, `spec_size`, `spec_sides`, `spec_design`
- `quantity_value`, `quantity_unit`, `quantity_sheets`, `quantity_display`
- `price_supply`, `price_vat`, `price_vat_amount`
- `data_version` (1 = legacy, 2 = standard)

---

## 9. Installation File List

### 9.1 Core Files (Required)

```
/
├── db.php
├── config.env.php
├── index.php
│
├── includes/
│   ├── auth.php
│   ├── auth_functions.php
│   ├── QuantityFormatter.php
│   ├── ProductSpecFormatter.php
│   ├── DataAdapter.php
│   ├── StandardUploadHandler.php
│   ├── UploadPathHelper.php
│   ├── ImagePathResolver.php
│   ├── PriceCalculationService.php
│   ├── SpecDisplayService.php
│   ├── OrderDataService.php
│   ├── AdditionalOptions.php
│   ├── AdditionalOptionsDisplay.php
│   ├── functions.php
│   ├── safe_json_response.php
│   ├── db_constants.php
│   ├── table_mapper.php
│   ├── upload_config.php
│   ├── header.php
│   ├── header-ui.php
│   ├── footer.php
│   ├── nav.php
│   └── quantity_formatter.php
```

### 9.2 Product Modules (per product)

```
mlangprintauto/{product}/
├── index.php
├── add_to_basket.php
├── calculate_price_ajax.php
├── inc.php
├── config/
│   └── *.php
├── css/
│   └── *.css
└── js/
    └── *.js
```

### 9.3 Order Processing

```
mlangorder_printauto/
├── ProcessOrder_unified.php
├── OrderComplete_universal.php
├── OnlineOrder_unified.php
├── OrderFormOrderTree.php
├── WindowSian.php
├── send_order_email.php
├── index.php
├── PHPMailer/
│   └── (PHPMailer library files)
└── upload/
```

### 9.4 Admin Panel

```
admin/
├── index.php
├── dashboard.php
├── config.php
├── top.php
├── title.php
├── func.php
├── css/
├── js/
├── includes/
├── mlangprintauto/
│   └── (product management subdirs)
├── MlangOrder_PrintAuto/
├── member/
├── api/
└── migrations/
```

### 9.5 Database Tables (Required)

**Core Tables**:
- `shop_temp`
- `mlangorder_printauto`
- `users`
- `remember_tokens`

**Product Price Tables**:
- `mlangprintauto_inserted`
- `mlangprintauto_sticker`
- `mlangprintauto_namecard`
- `mlangprintauto_envelope`
- `mlangprintauto_littleprint`
- `mlangprintauto_merchandisebond`
- `mlangprintauto_cadarok`
- `mlangprintauto_ncrflambeau`
- `mlangprintauto_msticker`

**Reference Tables**:
- `mlangprintauto_transactioncate`
- `unit_codes`
- `paper_standard_master`
- `product_unit_config`

---

## Appendix A: Critical Rules

### A.1 bind_param Triple Verification

Always verify parameter counts match:
```php
$placeholder_count = substr_count($query, '?');
$type_count = strlen($type_string);
$var_count = 7;  // Count manually

if ($placeholder_count === $type_count && $type_count === $var_count) {
    mysqli_stmt_bind_param($stmt, $type_string, ...);
}
```

### A.2 Table Names

All table names must be **lowercase**:
- Correct: `mlangprintauto_namecard`
- Wrong: `MlangPrintAuto_Namecard`

### A.3 Quantity Display Verification

Always check for unit presence before using `quantity_display`:
```php
$quantity_display = $item['quantity_display'] ?? '';
if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $quantity_display = QuantityFormatter::format($qty_value, $qty_unit_code, $qty_sheets);
}
```

---

## Appendix B: Environment Variables

### Local Development
```
Host: localhost
Database: dsp1830
User: dsp1830
Charset: utf8mb4
Debug: enabled
```

### Production
```
Host: localhost
Database: dsp1830
User: dsp1830
Charset: utf8mb4
Debug: disabled
```

---

*Document Version: 1.0*
*Generated: 2026-01-18*
*For installation support, refer to CLAUDE.md in project root*
