# AGENTS.md - Duson Print System

> Agent instructions for AI coding assistants working in this repository.

## Project Overview

**Duson Planning Print System (두손기획인쇄)** - PHP 7.4 print order management system.

| Aspect | Details |
|--------|---------|
| Language | PHP 7.4+ |
| Database | MySQL 5.7+ / MariaDB 10.3 (utf8mb4) |
| Web Server | Apache 2.4+ |
| Document Root | `/var/www/html` |
| Dependencies | composer (TCPDF, DomPDF, mPDF, PHPMailer) |

---

## Build & Server Commands

```bash
# Start development servers
sudo service apache2 start
sudo service mysql start

# Access local site
http://localhost/

# Install PHP dependencies
composer install

# Install Node dependencies (Playwright for testing)
npm install
```

### No Automated Testing Framework

This project does NOT have PHPUnit or automated tests configured.
Manual testing via browser is the primary verification method.

---

## Critical Rules (MUST FOLLOW)

### 1. MySQL/MariaDB Collation (HIGHEST PRIORITY)

```sql
-- NEVER use MySQL 8.0+ collation
COLLATE=utf8mb4_0900_ai_ci  -- FORBIDDEN!

-- ALWAYS use MySQL 5.7 / MariaDB 10.3 compatible
COLLATE=utf8mb4_general_ci  -- REQUIRED
COLLATE=utf8mb4_unicode_ci  -- ALLOWED
```

### 2. bind_param Triple Verification

```php
// ALWAYS verify counts match before binding
$placeholder_count = substr_count($query, '?');  // Step 1
$type_count = strlen($type_string);              // Step 2
$var_count = 7; // Count manually                // Step 3

// All three MUST match
mysqli_stmt_bind_param($stmt, $type_string, ...);
```

### 3. File & Table Naming

```
ALL LOWERCASE - Linux is case-sensitive!

Tables:  mlangprintauto_namecard (NOT MlangPrintAuto_Namecard)
Files:   cateadmin_title.php (NOT CateAdmin_title.php)
Includes: lowercase paths only
```

### 4. CSS !important Ban

```css
/* NEVER use !important - it's a code smell */
.product-nav { display: grid !important; }  /* FORBIDDEN */

/* ALWAYS use specificity hierarchy */
.product-nav { display: flex; }              /* Level 1 */
.mobile-view .product-nav { display: grid; } /* Level 2 */
```

---

## Code Style Guidelines

### PHP Formatting

```php
<?php
/**
 * Class/Function DocBlock (required for public APIs)
 * 
 * @param type $param Description
 * @return type Description
 */
class ClassName {
    // 4-space indentation
    // Opening brace on same line
    public function methodName($param): string {
        // Early return pattern preferred
        if (!$condition) {
            return '';
        }
        
        return $result;
    }
}
```

### Variable Naming

```php
$db           // Database connection (primary)
$conn = $db;  // Legacy alias (for compatibility)
$stmt         // Prepared statement
$result       // Query result
$row          // Fetched row
```

### Database Conventions

```php
// ALWAYS use prepared statements for user input
$stmt = mysqli_prepare($db, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
```

---

## SSOT (Single Source of Truth) Classes

### QuantityFormatter (includes/QuantityFormatter.php)

**THE ONLY way to format quantities.**

```php
// CORRECT: Use SSOT
$display = QuantityFormatter::format($value, $unitCode, $sheets);
$unitCode = QuantityFormatter::getProductUnitCode($productType);

// WRONG: Direct formatting
$display = number_format($amount) . '매';  // FORBIDDEN
```

### Unit Code System

| Code | Unit | Products |
|------|------|----------|
| R | 연 (Ream) | inserted (전단지) |
| S | 매 (Sheet) | sticker_new, namecard, envelope, msticker |
| B | 부 (Bundle) | cadarok (카다록) |
| V | 권 (Volume) | ncrflambeau (NCR양식지) |
| P | 장 (Piece) | littleprint (포스터) |
| E | 개 (Each) | Default/Custom |

### Method Distinction (CRITICAL)

```php
// getUnitCode: Korean unit name → Code
QuantityFormatter::getUnitCode('매');      // → 'S'

// getProductUnitCode: Product type → Code  
QuantityFormatter::getProductUnitCode('sticker');  // → 'S'

// NEVER mix these up!
```

---

## Product Folder Mapping (IMMUTABLE)

| Product | Folder (REQUIRED) | FORBIDDEN |
|---------|-------------------|-----------|
| 전단지 | `inserted` | leaflet |
| 스티커 | `sticker_new` | sticker |
| 자석스티커 | `msticker` | - |
| 명함 | `namecard` | - |
| 봉투 | `envelope` | - |
| 포스터 | `littleprint` | poster |
| 상품권 | `merchandisebond` | giftcard |
| 카다록 | `cadarok` | catalog |
| NCR양식지 | `ncrflambeau` | form, ncr |

**Code paths MUST use these exact folder names.**

---

## Directory Structure

```
/var/www/html/
├── db.php                    # DB connection & env detection
├── config.env.php            # Environment config
├── includes/
│   ├── QuantityFormatter.php # Quantity SSOT
│   ├── auth.php              # Authentication (8hr session)
│   └── functions.php         # Common utilities
├── mlangprintauto/[product]/ # Product pages
│   ├── index.php             # Product page
│   ├── add_to_basket.php     # Cart API
│   └── calculate_price_ajax.php
└── mlangorder_printauto/     # Order processing
    ├── ProcessOrder_unified.php
    └── OrderComplete_universal.php
```

---

## Error Handling

```php
// Database errors
if (!$db) {
    die("DB connection failed: " . mysqli_connect_error());
}

// Query errors
$result = mysqli_query($db, $query);
if (!$result) {
    error_log("Query failed: " . mysqli_error($db));
    return false;
}

// Prepared statement errors
$stmt = mysqli_prepare($db, $query);
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($db));
    return false;
}
```

---

## Common Pitfalls

1. ❌ `utf8mb4_0900_ai_ci` → Use `utf8mb4_general_ci`
2. ❌ bind_param count mismatch → Triple verify
3. ❌ Uppercase table names → Use lowercase
4. ❌ `number_format(0.5)` → Rounds to "1" (use rtrim for decimals)
5. ❌ `getUnitCode($productType)` → Use `getProductUnitCode()`
6. ❌ Direct `quantity_display` → Use `QuantityFormatter::format()`

---

## Git Workflow

```bash
# Check status
git status
git diff

# Commit (only when requested)
git add .
git commit -m "feat: description"
git push origin main
```

**NEVER commit unless explicitly requested by user.**

---

## Documentation References

| Topic | File |
|-------|------|
| Master Spec | `CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md` |
| Data Lineage | `CLAUDE_DOCS/DATA_LINEAGE.md` |
| Changelog | `.claude/changelog/CHANGELOG.md` |
