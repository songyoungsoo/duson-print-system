# 🏗️ Technical Architecture & Stack

## 🎯 System Architecture Overview

### Architecture Pattern
- **Type**: Modular Monolithic Architecture
- **Pattern**: MVC-inspired with Module Segregation
- **Session**: Server-side PHP Session Management
- **Database**: Shared Database with Namespace Isolation

### System Components
```
┌─────────────────────────────────────────────────┐
│                  Frontend Layer                  │
│         HTML5 + CSS3 + JavaScript/jQuery         │
├─────────────────────────────────────────────────┤
│               Application Layer                  │
│         PHP 7.4+ Business Logic                 │
├─────────────────────────────────────────────────┤
│                Data Access Layer                 │
│          MySQLi with Prepared Statements         │
├─────────────────────────────────────────────────┤
│                 Database Layer                   │
│            MySQL 5.7+ (utf8mb4)                 │
└─────────────────────────────────────────────────┘
```

## 📦 Technology Stack

### Core Infrastructure
| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| **Runtime** | PHP | 7.4+ | Server-side processing |
| **Database** | MySQL | 5.7+ | Data persistence |
| **Web Server** | Apache | 2.4+ | HTTP server (via XAMPP) |
| **Charset** | UTF-8/UTF8MB4 | - | Korean language support |

### Frontend Technologies
| Technology | Version | Usage |
|------------|---------|-------|
| **HTML5** | Standard | Semantic markup structure |
| **CSS3** | Standard | Styling & responsive design |
| **JavaScript** | ES5 | Client-side interactions |
| **jQuery** | 3.x | DOM manipulation & AJAX |
| **Font** | Noto Sans KR | - | Korean typography |

### Backend Libraries
| Library | Purpose | Integration |
|---------|---------|-------------|
| **PHPMailer** | Email notifications | Order confirmations |
| **MySQLi** | Database interface | Prepared statements |
| **Session** | User state | Cart & authentication |

## 📁 Project Structure

### Root Level Organization
```
C:\xampp\htdocs\
├── 📄 Core Configuration
│   ├── index.php              # Application entry point
│   ├── db.php                 # Database connection & mapping
│   ├── config.php             # Global configuration
│   └── config.env.php         # Environment-specific settings
│
├── 📂 MlangPrintAuto/         # Product Modules
│   ├── sticker_new/           # 스티커 인쇄
│   ├── inserted/              # 전단지 인쇄
│   ├── NameCard/              # 명함 인쇄
│   ├── envelope/              # 봉투 인쇄
│   ├── cadarok/               # 카다록 인쇄
│   ├── littleprint/           # 포스터 인쇄
│   ├── ncrflambeau/           # NCR양식 인쇄
│   ├── MerchandiseBond/       # 상품권 인쇄
│   └── msticker/              # 자석스티커 인쇄
│
├── 📂 admin/                  # Administrative System
│   ├── index.php              # Admin dashboard
│   ├── login.php              # Admin authentication
│   ├── MlangPrintAuto/        # Product management
│   └── includes/              # Admin utilities
│
├── 📂 includes/               # Shared Components
│   ├── auth.php               # Authentication logic
│   ├── functions.php          # Global utilities
│   ├── gallery_helper.php     # Gallery system
│   ├── header.php             # Global header
│   ├── footer.php             # Global footer
│   └── nav.php                # Navigation component
│
├── 📂 css/                    # Global Stylesheets
│   ├── common-styles.css      # Site-wide styles
│   ├── unified-calculator-layout.css  # Calculator UI
│   └── [product]-compact.css  # Product-specific
│
└── 📂 CLAUDE/                 # Documentation
    └── *.md                   # Technical documentation
```

### Product Module Architecture
```
MlangPrintAuto/[product]/
├── index.php                  # Product main page
├── add_to_basket.php          # Cart integration
├── calculate_price_ajax.php   # Price API endpoint
├── get_[product]_images.php   # Gallery API
├── js/
│   └── [product].js           # Product logic
├── css/
│   └── [product]-compact.css  # Product styles
└── images/
    └── gallery/               # Product images
```

## 🗄️ Database Design

### Schema Conventions
```sql
-- Naming Convention Rules
-- 1. ALL table names MUST be lowercase
-- 2. Use underscore for word separation
-- 3. Prefix with module namespace

-- Product Tables
mlangprintauto_[product]       -- Product-specific data
mlangprintauto_transactioncate -- Transaction categories

-- System Tables
shop_temp                      -- Shopping cart
shop_order                     -- Order management
member_user                    -- User accounts
admin_config                   -- Admin settings
```

### Table Categories
| Namespace | Purpose | Example Tables |
|-----------|---------|----------------|
| `mlangprintauto_*` | Product data | `mlangprintauto_namecard` |
| `shop_*` | Commerce | `shop_order`, `shop_cart` |
| `member_*` | Users | `member_user`, `member_session` |
| `admin_*` | Administration | `admin_user`, `admin_log` |

### Database Connection Strategy
```php
// Auto-mapping for case compatibility
// db.php handles uppercase to lowercase conversion
$query = "SELECT * FROM MlangPrintAuto_NameCard";
// Automatically converted to: mlangprintauto_namecard
```

## 🔄 Request Processing Flow

### Standard Request Lifecycle
```
1. User Request
   └─> index.php or product/index.php

2. Session Initialization
   └─> session_start() + authentication check

3. Database Connection
   └─> db.php with environment detection

4. Business Logic
   └─> Product modules or shared functions

5. Data Processing
   └─> AJAX endpoints for dynamic content

6. Response Generation
   └─> HTML rendering with included components
```

### AJAX Request Pattern
```
JavaScript (jQuery)
    ↓
calculate_price_ajax.php
    ↓
Database Query (MySQLi)
    ↓
JSON Response
    ↓
DOM Update
```

## 🔐 Security Architecture

### Defense Layers
1. **Input Layer**
   - Type validation
   - Length restrictions
   - Character whitelisting

2. **Application Layer**
   - Prepared statements (SQL injection prevention)
   - htmlspecialchars() (XSS prevention)
   - Session validation

3. **Data Layer**
   - Parameterized queries
   - Escaped output
   - Encrypted passwords

### File Upload Security
- Extension whitelist: `jpg, png, pdf, ai, psd`
- MIME type verification
- Size limit: 50MB
- Unique filename generation
- Quarantine directory

## 🚀 Performance Strategy

### Optimization Techniques
| Layer | Technique | Implementation |
|-------|-----------|----------------|
| **Database** | Indexing | Foreign keys, search fields |
| **Database** | Query Cache | MySQLi result caching |
| **PHP** | OpCode Cache | OPcache enabled |
| **Frontend** | Minification | CSS/JS compression |
| **Frontend** | Lazy Loading | Image defer loading |
| **Server** | Compression | GZIP enabled |

### Caching Strategy
```
Browser Cache (1hr)
    ↓
CDN Ready Structure
    ↓
Database Query Cache
    ↓
PHP Session Cache
```

## 📊 Development & Deployment

### Environment Configuration
| Environment | Detection | Database | Debug |
|-------------|-----------|----------|-------|
| **Local** | `localhost` | `dsp1830` | Enabled |
| **Staging** | `test.dsp1830.shop` | `dsp1830_test` | Limited |
| **Production** | `www.dsp1830.shop` | `dsp1830_prod` | Disabled |

### Deployment Checklist
```bash
# Pre-deployment
□ Run case sensitivity check
□ Validate table names (lowercase)
□ Test file paths (case-sensitive)
□ Clear cache directories

# Database Migration
□ Export with lowercase tables
□ Update connection strings
□ Verify charset (utf8mb4)

# Post-deployment
□ Test all product modules
□ Verify admin functions
□ Check error logs
□ Monitor performance
```

### Critical Migration Rules
⚠️ **XAMPP to Linux Production**
1. Database tables: Convert to lowercase
2. File paths: Maintain exact case
3. Include statements: Case-sensitive
4. Session path: Verify permissions

## 🔧 Development Tools

### Required Software
- **XAMPP** 7.4+ (Apache, MySQL, PHP)
- **phpMyAdmin** for database management
- **Git** for version control
- **VS Code** or similar IDE

### Useful Commands
```bash
# Start services (Windows)
net start Apache2.4
net start MySQL

# Error logs
tail -f C:\xampp\apache\logs\error.log
tail -f C:\xampp\mysql\data\*.err

# Database backup
mysqldump -u root -p dsp1830 > backup.sql
```

---
*Architecture Version: 2.1*
*Last Updated: 2025-09-19*
*Maintained by: System Architecture Team*