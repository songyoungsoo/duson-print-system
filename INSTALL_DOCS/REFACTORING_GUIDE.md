# Duson Print System - Installable Package Refactoring Guide

> **Version**: 1.0.0
> **Date**: 2026-01-18
> **Purpose**: Transform the current codebase into a distributable, installable package

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Current State Analysis](#2-current-state-analysis)
3. [Hardcoded Values Inventory](#3-hardcoded-values-inventory)
4. [Configuration File Architecture](#4-configuration-file-architecture)
5. [Environment Separation Strategy](#5-environment-separation-strategy)
6. [Sensitive Information Removal](#6-sensitive-information-removal)
7. [Database Schema & Migration](#7-database-schema--migration)
8. [Initial Data & Seed Files](#8-initial-data--seed-files)
9. [Installation Wizard Design](#9-installation-wizard-design)
10. [Refactoring Checklist](#10-refactoring-checklist)
11. [CLAUDE.md Critical Rules Compliance](#11-claudemd-critical-rules-compliance)

---

## 1. Executive Summary

### Current State
The Duson Print System is a PHP 7.4-based print order management system with:
- 9 product types with fixed folder names
- Mixed hardcoded values throughout the codebase
- Partially implemented environment detection
- Existing installation wizard (incomplete)
- Sensitive credentials embedded in source files

### Goals
1. Create a distributable package that can be installed on any PHP 7.4+ server
2. Zero hardcoded credentials in distributed code
3. Automated database setup with migration support
4. User-friendly installation wizard
5. Maintain backward compatibility with existing installations

### Estimated Effort
- **Phase 1** (Configuration): 2-3 days
- **Phase 2** (Credential Removal): 1-2 days
- **Phase 3** (Database Migration): 2-3 days
- **Phase 4** (Installation Wizard): 3-5 days
- **Total**: 8-13 days

---

## 2. Current State Analysis

### 2.1 Existing Architecture

```
/var/www/html/
├── config.env.php              # Environment detection (GOOD)
├── db.php                      # Database connection (PARTIAL)
├── install/                    # Existing installer (INCOMPLETE)
├── includes/                   # Shared libraries
├── mlangprintauto/            # Product pages
│   ├── inserted/              # Leaflet/Flyer
│   ├── sticker_new/           # Sticker
│   ├── msticker/              # Magnet Sticker
│   ├── namecard/              # Business Card
│   ├── envelope/              # Envelope
│   ├── littleprint/           # Poster
│   ├── merchandisebond/       # Gift Certificate
│   ├── cadarok/               # Catalog
│   └── ncrflambeau/           # NCR Form
├── mlangorder_printauto/      # Order processing
├── admin/                     # Admin panel
└── shop_admin/                # Legacy admin
```

### 2.2 Positive Aspects (Keep)

| Component | Status | Notes |
|-----------|--------|-------|
| `config.env.php` | GOOD | Environment auto-detection exists |
| `db.php` | PARTIAL | Uses config.env.php, but has hardcoded values |
| Schema files | EXIST | `/install/sql/schema.sql` available |
| Installation guide | EXISTS | `/install/INSTALLATION_GUIDE.md` |

### 2.3 Issues to Address

| Issue | Severity | Files Affected |
|-------|----------|----------------|
| Hardcoded credentials | CRITICAL | 50+ files |
| Hardcoded URLs | HIGH | 45+ files |
| Hardcoded paths | MEDIUM | 40+ files |
| Missing .env support | HIGH | All config files |
| Incomplete installer | MEDIUM | `/install/` directory |

---

## 3. Hardcoded Values Inventory

### 3.1 Credentials (CRITICAL - Must Remove)

| Type | Current Value | Files |
|------|---------------|-------|
| DB User | `dsp1830` | config.env.php, 50+ files |
| DB Password | `ds701018` | config.env.php, 50+ files |
| Admin Password | `du1830` | sub/pw_check.php, sub/db.php |
| SMTP Password | `2CP3P5BTS83Y` | config.env.php |
| SMTP User | `dsp1830` | Various email files |

**Files with hardcoded credentials:**
```
config.env.php (lines 73-78, 86-91)
sub/pw_check.php (line 5)
sub/db.php (line 6)
shop/db.php
mlangprintauto/shop/cart.php
chat/api.php
chat/admin.php
mlangorder_printauto/send_order_email.php
... and 40+ more files
```

### 3.2 Hardcoded URLs

| URL Pattern | Count | Location Examples |
|-------------|-------|-------------------|
| `http://localhost` | 15+ | db.php, shop/db.php, setup files |
| `http://dsp1830.shop` | 30+ | shop_admin/left.php, sub/*.php |
| `http://dsp114.com` | 5+ | includes/upload_path_manager.php |
| `https://dsp1830.shop` | 10+ | payment/inicis_config.php |

**Sample files requiring URL abstraction:**
```
shop_admin/left.php (lines 82-107)
shop_admin/left01.php (lines 85-107)
sub/brochure.php, sub/envelope.php, sub/namecard.php
payment/inicis_config.php (lines 34-39)
includes/upload_path_manager.php (line 147)
```

### 3.3 Hardcoded Paths

| Path Pattern | Count | Issues |
|--------------|-------|--------|
| `/var/www/html/` | 25+ | Not portable |
| `$_SERVER['DOCUMENT_ROOT']` | 40+ | OK - Dynamic |
| Absolute paths in comments | 10+ | Documentation issue |

**Files with hardcoded absolute paths:**
```
change/download_images.php (lines 7, 15)
change/migrate_order.php (lines 7, 9)
public_html/mlangorder_printauto/ProcessOrder_unified.php (line 410)
public_html/includes/UploadPathHelper.php (lines 44, 52)
```

---

## 4. Configuration File Architecture

### 4.1 Proposed Configuration Structure

```
/var/www/html/
├── .env                        # NEW: Environment variables (gitignored)
├── .env.example                # NEW: Template for .env
├── config/
│   ├── app.php                 # NEW: Application settings
│   ├── database.php            # NEW: Database configuration
│   ├── mail.php                # NEW: SMTP settings
│   ├── paths.php               # NEW: Path constants
│   ├── products.php            # NEW: Product folder mapping
│   └── payment.php             # NEW: Payment gateway config
├── config.env.php              # MODIFY: Load from .env
└── db.php                      # MODIFY: Use new config
```

### 4.2 .env.example Template

```ini
# Application
APP_NAME="두손기획인쇄"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_HOST=localhost
DB_NAME=dsp1830
DB_USER=your_db_user
DB_PASS=your_db_password
DB_CHARSET=utf8mb4

# Admin
ADMIN_EMAIL=admin@your-domain.com
ADMIN_NAME=관리자

# SMTP
SMTP_HOST=smtp.naver.com
SMTP_PORT=465
SMTP_SECURE=ssl
SMTP_USER=your_smtp_user
SMTP_PASS=your_smtp_password
SMTP_FROM_EMAIL=your_email@naver.com
SMTP_FROM_NAME=두손기획인쇄

# Payment (INICIS)
INICIS_MID=your_mid
INICIS_SIGN_KEY=your_sign_key
INICIS_RETURN_URL=${APP_URL}/payment/inicis_return.php
INICIS_CLOSE_URL=${APP_URL}/payment/inicis_close.php

# Paths (usually auto-detected)
UPLOAD_PATH=ImgFolder
ORDER_UPLOAD_PATH=mlangorder_printauto/upload

# Session
SESSION_LIFETIME=28800

# Cookie Domain (leave empty for auto-detect)
COOKIE_DOMAIN=
```

### 4.3 config/database.php Implementation

```php
<?php
/**
 * Database Configuration
 * Loads settings from .env file or environment variables
 */

// Load .env if exists
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = array_map('trim', explode('=', $line, 2));
        $value = trim($value, '"\'');
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'name' => getenv('DB_NAME') ?: 'dsp1830',
    'user' => getenv('DB_USER') ?: '',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];
```

### 4.4 config/products.php (Fixed Folder Names)

```php
<?php
/**
 * Product Folder Mapping
 * CRITICAL: These folder names MUST NOT be changed per CLAUDE.md rules
 */

return [
    // Product ID => Folder Name (FIXED - DO NOT MODIFY)
    'leaflet'         => 'inserted',           // Not 'leaflet'
    'sticker'         => 'sticker_new',        // Not 'sticker'
    'magnet_sticker'  => 'msticker',           // Independent path
    'namecard'        => 'namecard',           // Standard
    'envelope'        => 'envelope',           // Standard
    'poster'          => 'littleprint',        // Not 'poster'
    'gift_cert'       => 'merchandisebond',    // Not 'giftcard'
    'catalog'         => 'cadarok',            // Phonetic Korean
    'ncr_form'        => 'ncrflambeau',        // Unique naming

    // Display names (can be localized)
    'display_names' => [
        'inserted'        => '전단지/리플렛',
        'sticker_new'     => '스티커',
        'msticker'        => '자석스티커',
        'namecard'        => '명함',
        'envelope'        => '봉투',
        'littleprint'     => '포스터',
        'merchandisebond' => '상품권',
        'cadarok'         => '카다록',
        'ncrflambeau'     => 'NCR양식지',
    ],

    // Unit codes per product
    'units' => [
        'inserted'        => 'R',  // 연 (Ream)
        'sticker_new'     => 'S',  // 매 (Sheet)
        'msticker'        => 'S',  // 매
        'namecard'        => 'S',  // 매
        'envelope'        => 'S',  // 매
        'littleprint'     => 'S',  // 매
        'merchandisebond' => 'S',  // 매
        'cadarok'         => 'B',  // 부 (Copy)
        'ncrflambeau'     => 'V',  // 권 (Volume)
    ],
];
```

---

## 5. Environment Separation Strategy

### 5.1 Environment Detection (Enhanced)

```php
<?php
/**
 * Enhanced Environment Detection
 * File: config/environment.php
 */

class Environment {
    const LOCAL = 'local';
    const STAGING = 'staging';
    const PRODUCTION = 'production';

    private static $detected = null;

    public static function detect(): string {
        if (self::$detected !== null) {
            return self::$detected;
        }

        // 1. Check explicit environment variable
        $envVar = getenv('APP_ENV');
        if ($envVar && in_array($envVar, [self::LOCAL, self::STAGING, self::PRODUCTION])) {
            return self::$detected = $envVar;
        }

        // 2. Check .env file
        $envFile = dirname(__DIR__) . '/.env';
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            if (preg_match('/APP_ENV=(\w+)/', $content, $matches)) {
                $env = strtolower($matches[1]);
                if (in_array($env, [self::LOCAL, self::STAGING, self::PRODUCTION])) {
                    return self::$detected = $env;
                }
            }
        }

        // 3. Auto-detect from server info
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $serverIP = $_SERVER['SERVER_ADDR'] ?? '';

        // Local indicators
        $localIndicators = [
            strpos($host, 'localhost') !== false,
            strpos($host, '127.0.0.1') !== false,
            strpos($docRoot, 'xampp') !== false,
            strpos($docRoot, 'wamp') !== false,
            $serverIP === '127.0.0.1',
        ];

        if (in_array(true, $localIndicators, true)) {
            return self::$detected = self::LOCAL;
        }

        // Staging indicators
        $stagingIndicators = [
            strpos($host, 'staging.') !== false,
            strpos($host, 'test.') !== false,
            strpos($host, 'dev.') !== false,
        ];

        if (in_array(true, $stagingIndicators, true)) {
            return self::$detected = self::STAGING;
        }

        // Default to production (safest)
        return self::$detected = self::PRODUCTION;
    }

    public static function isLocal(): bool {
        return self::detect() === self::LOCAL;
    }

    public static function isStaging(): bool {
        return self::detect() === self::STAGING;
    }

    public static function isProduction(): bool {
        return self::detect() === self::PRODUCTION;
    }

    public static function isDebug(): bool {
        $debug = getenv('APP_DEBUG');
        if ($debug !== false) {
            return filter_var($debug, FILTER_VALIDATE_BOOLEAN);
        }
        return self::isLocal();
    }
}
```

### 5.2 Environment-Specific Configuration Files

```
/var/www/html/
├── .env                    # Active configuration (gitignored)
├── .env.example            # Template (committed)
├── .env.local              # Local overrides (optional, gitignored)
├── .env.staging            # Staging settings (optional, gitignored)
└── .env.production         # Production settings (optional, gitignored)
```

### 5.3 Configuration Loading Priority

```php
// Load order (later overrides earlier):
// 1. .env
// 2. .env.{APP_ENV}
// 3. .env.local (always last, for personal overrides)
```

---

## 6. Sensitive Information Removal

### 6.1 Files Requiring Immediate Attention

**CRITICAL - Remove before distribution:**

| File | Line(s) | Content Type |
|------|---------|--------------|
| `config.env.php` | 73-78, 86-91 | DB credentials |
| `config.env.php` | 159-161 | SMTP credentials |
| `sub/pw_check.php` | 5 | Admin password |
| `sub/db.php` | 6 | DB password |
| `shop/db.php` | All | Legacy credentials |

### 6.2 Refactoring Pattern

**Before (config.env.php):**
```php
// INSECURE - Hardcoded credentials
self::$config = [
    'host' => 'localhost',
    'user' => 'dsp1830',
    'password' => 'ds701018',
    'database' => 'dsp1830',
];
```

**After:**
```php
// SECURE - Environment variables
self::$config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'user' => getenv('DB_USER') ?: '',
    'password' => getenv('DB_PASS') ?: '',
    'database' => getenv('DB_NAME') ?: '',
];

// Validate required settings
if (empty(self::$config['user']) || empty(self::$config['password'])) {
    throw new RuntimeException(
        'Database credentials not configured. Please set DB_USER and DB_PASS in .env file.'
    );
}
```

### 6.3 .gitignore Updates

```gitignore
# Environment files
.env
.env.local
.env.*.local

# Keep examples
!.env.example

# Sensitive files
config/secrets.php
*.pem
*.key

# Installation artifacts
install_config.json

# Backup files with credentials
*.sql.backup
```

### 6.4 Pre-Distribution Checklist

```bash
#!/bin/bash
# scripts/check_secrets.sh
# Run before creating distribution package

echo "Checking for hardcoded secrets..."

# Check for common patterns
PATTERNS=(
    "dsp1830"
    "ds701018"
    "du1830"
    "2CP3P5BTS83Y"
    "password.*=.*['\"]"
)

for pattern in "${PATTERNS[@]}"; do
    echo "Checking: $pattern"
    grep -r --include="*.php" "$pattern" . | grep -v ".env" | grep -v "vendor/"
done

echo "Done. Review any matches above."
```

---

## 7. Database Schema & Migration

### 7.1 Current Schema Location

```
/var/www/html/install/sql/schema.sql     # Main schema (374 lines)
/var/www/html/install_backup/sql/schema.sql  # Backup copy
```

### 7.2 Migration System Design

**Directory Structure:**
```
/var/www/html/
├── database/
│   ├── migrations/
│   │   ├── 001_initial_schema.sql
│   │   ├── 002_add_product_columns.sql
│   │   ├── 003_add_shipping_columns.sql
│   │   └── ...
│   ├── seeds/
│   │   ├── 001_admin_user.sql
│   │   ├── 002_product_categories.sql
│   │   ├── 003_price_tables.sql
│   │   └── 004_default_options.sql
│   └── migration.php           # Migration runner
```

### 7.3 Migration Table Schema

```sql
-- Track applied migrations
CREATE TABLE IF NOT EXISTS `_migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL,
    `executed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `migration_unique` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 7.4 Migration Runner (PHP)

```php
<?php
/**
 * Simple Migration Runner
 * File: database/migration.php
 */

class Migrator {
    private $db;
    private $migrationsPath;
    private $seedsPath;

    public function __construct(mysqli $db) {
        $this->db = $db;
        $this->migrationsPath = __DIR__ . '/migrations/';
        $this->seedsPath = __DIR__ . '/seeds/';
        $this->ensureMigrationTable();
    }

    private function ensureMigrationTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS `_migrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL,
            `executed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `migration_unique` (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        mysqli_query($this->db, $sql);
    }

    public function migrate(): array {
        $results = [];
        $batch = $this->getNextBatch();
        $pending = $this->getPendingMigrations();

        foreach ($pending as $migration) {
            $sql = file_get_contents($this->migrationsPath . $migration);

            if (mysqli_multi_query($this->db, $sql)) {
                // Clear result sets
                while (mysqli_next_result($this->db)) {;}

                // Record migration
                $stmt = mysqli_prepare($this->db,
                    "INSERT INTO _migrations (migration, batch) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "si", $migration, $batch);
                mysqli_stmt_execute($stmt);

                $results[] = ['migration' => $migration, 'status' => 'success'];
            } else {
                $results[] = [
                    'migration' => $migration,
                    'status' => 'error',
                    'error' => mysqli_error($this->db)
                ];
                break; // Stop on first error
            }
        }

        return $results;
    }

    public function seed(): array {
        $results = [];
        $seedFiles = glob($this->seedsPath . '*.sql');
        sort($seedFiles);

        foreach ($seedFiles as $seedFile) {
            $sql = file_get_contents($seedFile);

            if (mysqli_multi_query($this->db, $sql)) {
                while (mysqli_next_result($this->db)) {;}
                $results[] = ['seed' => basename($seedFile), 'status' => 'success'];
            } else {
                $results[] = [
                    'seed' => basename($seedFile),
                    'status' => 'error',
                    'error' => mysqli_error($this->db)
                ];
            }
        }

        return $results;
    }

    private function getPendingMigrations(): array {
        $executed = [];
        $result = mysqli_query($this->db, "SELECT migration FROM _migrations");
        while ($row = mysqli_fetch_assoc($result)) {
            $executed[] = $row['migration'];
        }

        $all = glob($this->migrationsPath . '*.sql');
        $all = array_map('basename', $all);
        sort($all);

        return array_diff($all, $executed);
    }

    private function getNextBatch(): int {
        $result = mysqli_query($this->db, "SELECT MAX(batch) as batch FROM _migrations");
        $row = mysqli_fetch_assoc($result);
        return ($row['batch'] ?? 0) + 1;
    }
}
```

### 7.5 Core Tables (from current schema)

| Table | Purpose | Rows (Typical) |
|-------|---------|----------------|
| `mlangorder_printauto` | Orders | 80,000+ |
| `mlangprintauto_transactioncate` | Categories | 345 |
| `mlangprintauto_namecard` | Namecard prices | 500+ |
| `mlangprintauto_inserted` | Leaflet prices | 1000+ |
| `mlangprintauto_envelope` | Envelope prices | 200+ |
| `mlangprintauto_sticker` | Sticker prices | 500+ |
| `mlangprintauto_msticker` | Magnet sticker prices | 100+ |
| `mlangprintauto_cadarok` | Catalog prices | 300+ |
| `mlangprintauto_littleprint` | Poster prices | 200+ |
| `mlangprintauto_ncrflambeau` | NCR form prices | 200+ |
| `mlangprintauto_merchandisebond` | Gift cert prices | 100+ |
| `shop_temp` | Cart items | Variable |
| `quotations` | Quotes | Variable |
| `quotation_items` | Quote items | Variable |
| `users` | Admin users | 10 |
| `member` | Customers | 1000+ |

---

## 8. Initial Data & Seed Files

### 8.1 Seed File Organization

```
database/seeds/
├── 001_admin_user.sql          # Default admin account
├── 002_category_inserted.sql   # Leaflet categories
├── 003_category_namecard.sql   # Namecard categories
├── 004_category_envelope.sql   # Envelope categories
├── 005_category_sticker.sql    # Sticker categories
├── 006_category_msticker.sql   # Magnet sticker categories
├── 007_category_cadarok.sql    # Catalog categories
├── 008_category_littleprint.sql # Poster categories
├── 009_category_ncrflambeau.sql # NCR categories
├── 010_category_merchandisebond.sql # Gift cert categories
├── 011_prices_sample.sql       # Sample price data (optional)
├── 012_options_coating.sql     # Coating options
├── 013_options_folding.sql     # Folding options
└── 014_options_creasing.sql    # Creasing options
```

### 8.2 Admin User Seed (001_admin_user.sql)

```sql
-- Default admin user (password should be changed after installation)
-- Password: admin123 (bcrypt hash)
INSERT INTO `users` (`username`, `password`, `name`, `email`, `role`, `created_at`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '관리자', 'admin@example.com', 'admin', NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
```

### 8.3 Category Seed Example (002_category_inserted.sql)

```sql
-- Leaflet/Inserted categories
INSERT INTO `mlangprintauto_transactioncate` (`Ttable`, `BigNo`, `title`, `TreeNo`) VALUES
-- Paper types (top level)
('inserted', '0', '모조지80g', '1'),
('inserted', '0', '모조지100g', '2'),
('inserted', '0', '아트지80g', '3'),
('inserted', '0', '아트지100g', '4'),
('inserted', '0', '아트지120g', '5'),
('inserted', '0', '아트지150g', '6'),
('inserted', '0', '스노우화이트지80g', '7'),
('inserted', '0', '스노우화이트지100g', '8'),
('inserted', '0', '스노우화이트지120g', '9'),
('inserted', '0', '스노우화이트지150g', '10')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- Sizes (sub-categories, BigNo references parent)
-- Note: Actual BigNo values depend on AUTO_INCREMENT of parent inserts
```

### 8.4 Price Data Considerations

**Option A: Empty Price Tables**
- Pro: Clean installation, forces proper configuration
- Con: System non-functional until prices entered

**Option B: Sample Price Data**
- Pro: Immediately functional for demo
- Con: Prices may not be appropriate for all markets

**Recommendation: Provide both**
```sql
-- In separate file: seeds/optional/sample_prices.sql
-- User can choose to import during installation
```

---

## 9. Installation Wizard Design

### 9.1 Wizard Flow

```
Step 1: System Requirements Check
    ├── PHP Version (7.4+)
    ├── Required Extensions (mysqli, gd, mbstring, curl, zip)
    ├── Directory Permissions
    └── [Pass/Fail with details]

Step 2: Database Configuration
    ├── Host, Database, User, Password
    ├── Test Connection
    ├── Create Database Option
    └── [Validate & Save]

Step 3: Site Configuration
    ├── Site Name, Company Name
    ├── Site URL (auto-detected)
    ├── Admin Email
    └── [Validate & Save]

Step 4: Admin Account
    ├── Admin Username
    ├── Admin Password (with strength meter)
    ├── Admin Email
    └── [Create Account]

Step 5: Initial Data
    ├── [x] Create core tables
    ├── [ ] Import sample price data (optional)
    ├── [ ] Import sample categories (optional)
    └── [Execute Migrations]

Step 6: Finalization
    ├── Generate .env file
    ├── Set permissions
    ├── Display admin login URL
    └── Delete install directory warning
```

### 9.2 Requirements Check (Step 1)

```php
<?php
// install/step1_requirements.php

class RequirementsChecker {
    private $requirements = [];
    private $errors = [];

    public function check(): array {
        $this->checkPhpVersion();
        $this->checkExtensions();
        $this->checkDirectories();
        $this->checkFunctions();

        return [
            'requirements' => $this->requirements,
            'errors' => $this->errors,
            'passed' => empty($this->errors)
        ];
    }

    private function checkPhpVersion(): void {
        $required = '7.4.0';
        $current = PHP_VERSION;
        $passed = version_compare($current, $required, '>=');

        $this->requirements['php_version'] = [
            'name' => 'PHP Version',
            'required' => $required . '+',
            'current' => $current,
            'passed' => $passed
        ];

        if (!$passed) {
            $this->errors[] = "PHP version must be {$required} or higher";
        }
    }

    private function checkExtensions(): void {
        $required = ['mysqli', 'gd', 'mbstring', 'curl', 'zip', 'json'];

        foreach ($required as $ext) {
            $loaded = extension_loaded($ext);

            $this->requirements["ext_{$ext}"] = [
                'name' => "PHP Extension: {$ext}",
                'required' => 'Enabled',
                'current' => $loaded ? 'Enabled' : 'Not Found',
                'passed' => $loaded
            ];

            if (!$loaded) {
                $this->errors[] = "PHP extension '{$ext}' is required";
            }
        }
    }

    private function checkDirectories(): void {
        $dirs = [
            '../' => 'Root Directory',
            '../ImgFolder' => 'Upload Directory',
            '../mlangorder_printauto/upload' => 'Order Upload Directory',
            '../sessions' => 'Session Directory'
        ];

        foreach ($dirs as $dir => $name) {
            $path = realpath(__DIR__ . '/' . $dir) ?: __DIR__ . '/' . $dir;
            $writable = is_writable($path) || @mkdir($path, 0755, true);

            $this->requirements["dir_" . md5($dir)] = [
                'name' => "{$name} Writable",
                'required' => 'Writable',
                'current' => $writable ? 'Writable' : 'Not Writable',
                'passed' => $writable
            ];

            if (!$writable) {
                $this->errors[] = "Directory '{$name}' must be writable";
            }
        }
    }

    private function checkFunctions(): void {
        $functions = ['file_get_contents', 'file_put_contents', 'json_encode'];

        foreach ($functions as $func) {
            $exists = function_exists($func);

            $this->requirements["func_{$func}"] = [
                'name' => "Function: {$func}",
                'required' => 'Available',
                'current' => $exists ? 'Available' : 'Disabled',
                'passed' => $exists
            ];
        }
    }
}
```

### 9.3 Installation Wizard Main Controller

```php
<?php
// install/index.php

session_start();

// Prevent re-installation if already installed
if (file_exists('../.env') && !isset($_GET['force'])) {
    die('System appears to be already installed. Delete .env file to reinstall.');
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$steps = [
    1 => 'System Requirements',
    2 => 'Database Configuration',
    3 => 'Site Configuration',
    4 => 'Admin Account',
    5 => 'Initial Data',
    6 => 'Complete'
];

// Include step handler
$stepFile = __DIR__ . "/step{$step}.php";
if (!file_exists($stepFile)) {
    $step = 1;
    $stepFile = __DIR__ . "/step1.php";
}

include __DIR__ . '/templates/header.php';
include $stepFile;
include __DIR__ . '/templates/footer.php';
```

### 9.4 .env Generator (Step 6)

```php
<?php
// install/step6_complete.php

function generateEnvFile($config): bool {
    $template = <<<ENV
# Duson Print System Configuration
# Generated: {$config['generated_at']}

# Application
APP_NAME="{$config['site_name']}"
APP_ENV=production
APP_DEBUG=false
APP_URL={$config['site_url']}

# Database
DB_HOST={$config['db_host']}
DB_NAME={$config['db_name']}
DB_USER={$config['db_user']}
DB_PASS={$config['db_pass']}
DB_CHARSET=utf8mb4

# Admin
ADMIN_EMAIL={$config['admin_email']}
ADMIN_NAME={$config['admin_name']}

# SMTP (configure after installation)
SMTP_HOST=smtp.naver.com
SMTP_PORT=465
SMTP_SECURE=ssl
SMTP_USER=
SMTP_PASS=
SMTP_FROM_EMAIL=
SMTP_FROM_NAME={$config['site_name']}

# Session
SESSION_LIFETIME=28800

ENV;

    return file_put_contents(dirname(__DIR__) . '/.env', $template) !== false;
}
```

---

## 10. Refactoring Checklist

### Phase 1: Configuration Layer (Days 1-3)

- [ ] Create `config/` directory structure
- [ ] Implement `.env` file support
- [ ] Create `.env.example` template
- [ ] Refactor `config.env.php` to use environment variables
- [ ] Refactor `db.php` to use new config
- [ ] Create `config/products.php` with fixed folder mappings
- [ ] Create `config/paths.php` for path constants
- [ ] Update `.gitignore`

### Phase 2: Credential Removal (Days 4-5)

- [ ] Audit all files with `grep -r` for credentials
- [ ] Remove hardcoded DB credentials from 50+ files
- [ ] Remove hardcoded SMTP credentials
- [ ] Remove hardcoded admin passwords
- [ ] Create `scripts/check_secrets.sh`
- [ ] Test all environments work with .env

### Phase 3: URL/Path Abstraction (Days 6-7)

- [ ] Create URL helper functions
- [ ] Replace hardcoded URLs with config values
- [ ] Replace hardcoded paths with `__DIR__` relative paths
- [ ] Update admin menu links (shop_admin/left.php)
- [ ] Update sub/ directory links
- [ ] Update payment configuration URLs

### Phase 4: Database Migration (Days 8-10)

- [ ] Create `database/` directory structure
- [ ] Split current schema into migrations
- [ ] Create seed files for categories
- [ ] Create seed files for options
- [ ] Implement migration runner
- [ ] Test fresh installation
- [ ] Test upgrade from existing installation

### Phase 5: Installation Wizard (Days 11-15)

- [ ] Design UI/UX for wizard
- [ ] Implement Step 1: Requirements
- [ ] Implement Step 2: Database
- [ ] Implement Step 3: Site Config
- [ ] Implement Step 4: Admin Account
- [ ] Implement Step 5: Data Import
- [ ] Implement Step 6: Finalization
- [ ] Add progress tracking
- [ ] Add error recovery
- [ ] Test on clean server

### Phase 6: Documentation & Packaging (Days 16-18)

- [ ] Update INSTALLATION_GUIDE.md
- [ ] Create UPGRADE.md for existing users
- [ ] Create troubleshooting guide
- [ ] Create distribution package script
- [ ] Test package on fresh environments
- [ ] Create version tagging system

---

## 11. CLAUDE.md Critical Rules Compliance

### 11.1 bind_param 3-Way Verification (CRITICAL)

All database queries in refactored code MUST follow:

```php
// ALWAYS: 3-way verification
$query = "INSERT INTO users (username, password, email, phone, name) VALUES (?, ?, ?, ?, ?)";

$placeholder_count = substr_count($query, '?');  // 1st check: 5
$type_string = "sssss";
$type_count = strlen($type_string);              // 2nd check: 5
$var_count = 5;  // Manual count                  // 3rd check: 5

// Only proceed if all three match
if ($placeholder_count === $type_count && $type_count === $var_count) {
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, $type_string, $username, $password, $email, $phone, $name);
    mysqli_stmt_execute($stmt);
}
```

### 11.2 Table Names (Always Lowercase)

```php
// CORRECT
$query = "SELECT * FROM mlangprintauto_namecard WHERE ...";
$query = "SELECT * FROM mlangorder_printauto WHERE ...";

// INCORRECT - Will fail on Linux
$query = "SELECT * FROM MlangPrintAuto_NameCard WHERE ...";
```

### 11.3 CSS !important Prohibition

Installation wizard CSS MUST NOT use `!important`:

```css
/* CORRECT - Use specificity */
.install-wizard .step-content .form-group { display: flex; }
.install-wizard .step-content .form-group.vertical { flex-direction: column; }

/* INCORRECT - Never do this */
.form-group { display: flex !important; }
```

### 11.4 Product Folder Names (Fixed - Never Change)

```php
// These folder names are IMMUTABLE
$PRODUCT_FOLDERS = [
    'inserted',          // NOT 'leaflet'
    'sticker_new',       // NOT 'sticker'
    'msticker',          // Independent
    'namecard',          // Standard
    'envelope',          // Standard
    'littleprint',       // NOT 'poster'
    'merchandisebond',   // NOT 'giftcard'
    'cadarok',           // Phonetic
    'ncrflambeau',       // Unique
];
```

### 11.5 quantity_display Validation

```php
// ALWAYS check for unit before display
$quantity_display = $item['quantity_display'] ?? '';

if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $quantity_display = formatQuantity($item);
}
```

---

## Appendix A: File Change Summary

### Files to Modify

| File | Changes |
|------|---------|
| `config.env.php` | Remove hardcoded credentials, use getenv() |
| `db.php` | Use new config system |
| `includes/auth.php` | Use environment variables |
| `shop_admin/left.php` | Abstract URLs |
| `shop_admin/left01.php` | Abstract URLs |
| `sub/*.php` (10 files) | Abstract URLs |
| `payment/inicis_config.php` | Use APP_URL |
| `mlangorder_printauto/*.php` | Use config for emails |

### Files to Create

| File | Purpose |
|------|---------|
| `.env.example` | Environment template |
| `config/app.php` | Application config |
| `config/database.php` | Database config |
| `config/mail.php` | SMTP config |
| `config/products.php` | Product mappings |
| `config/environment.php` | Environment detection |
| `database/migration.php` | Migration runner |
| `database/migrations/*.sql` | Schema migrations |
| `database/seeds/*.sql` | Initial data |
| `scripts/check_secrets.sh` | Security audit |

### Files to Delete Before Distribution

| File | Reason |
|------|--------|
| `.env` | Contains secrets |
| `install_config.json` | Contains secrets |
| `*.sql.backup` | May contain data |
| `change/*.php` | Migration utilities |
| `sql_114/*.sql` | Production data |

---

## Appendix B: Quick Reference Commands

### Development Setup
```bash
# Copy environment template
cp .env.example .env

# Edit configuration
nano .env

# Run migrations
php database/migration.php migrate

# Seed initial data
php database/migration.php seed
```

### Pre-Distribution Check
```bash
# Check for secrets
./scripts/check_secrets.sh

# Verify no .env files
find . -name ".env*" -not -name ".env.example"

# Check file permissions
find . -type f -perm /111 -name "*.php"
```

### Create Distribution Package
```bash
# Clean build
rm -rf dist/
mkdir -p dist/

# Copy files (excluding secrets)
rsync -av --exclude='.env' --exclude='*.sql.backup' \
    --exclude='node_modules' --exclude='vendor' \
    --exclude='.git' --exclude='sql_114' \
    ./ dist/duson-print/

# Create archive
cd dist && tar -czf duson-print-v1.0.0.tar.gz duson-print/
```

---

*Document Version: 1.0.0*
*Last Updated: 2026-01-18*
*Author: Claude Code Assistant*
