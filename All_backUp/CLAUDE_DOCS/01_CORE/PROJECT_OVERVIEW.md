# 🏢 Project Overview

**Duson Planning Print System (두손기획인쇄)**  
Enterprise-grade web-based printing service management system built in PHP for comprehensive print order processing, pricing automation, and business operations.

## 📌 Core Purpose

### Business Functions
- **자동 견적 시스템** - Automated quotation system for all print products
- **파일 업로드 & 교정 시스템** - File upload and proofing management  
- **회원 & 주문 관리** - Customer and order lifecycle management
- **관리자 패널 기반 운영** - Administrative dashboard for operations

### Service Categories
- **명함/스티커** - Business cards and stickers
- **전단지/포스터** - Flyers and posters  
- **봉투/엽서** - Envelopes and postcards
- **NCR/어음** - NCR forms and promissory notes
- **기타 인쇄물** - Other custom print products

## 🌐 Environment & Infrastructure

### Domain Migration Strategy

**⚠️ Critical: Domain Transition in Progress**

The system is undergoing a strategic domain transition to modernize infrastructure while preserving customer familiarity:

#### Current State (2025-11)
```
Legacy Server (dsp1830.shop)
├─ PHP 5.2 (deprecated, read-only)
├─ Legacy codebase (frozen)
└─ Status: Planned for retirement

Development Server (dsp1830.shop) ← Active Development
├─ PHP 7.4+ (modern)
├─ New codebase with PHP 7.4 features
├─ Status: Testing & development
└─ Future: Will serve dsp1830.shop domain

Local Environment (localhost)
├─ WSL2 Ubuntu + XAMPP Windows
├─ PHP 7.4+
└─ Status: Development workspace
```

#### Migration Timeline
1. **Phase 1 (Current)**: Develop on dsp1830.shop with PHP 7.4
2. **Phase 2 (Testing)**: Complete feature parity and testing
3. **Phase 3 (Cutover)**: Point dsp1830.shop DNS to dsp1830.shop server
4. **Phase 4 (Complete)**: Legacy PHP 5.2 server retired

**Why This Approach:**
- ✅ Customers continue using familiar **dsp1830.shop** domain
- ✅ Zero downtime migration with DNS switch
- ✅ No code changes needed (automatic domain detection)
- ✅ Modern PHP 7.4 features and security

### Production Environment

**Current Target Server (dsp1830.shop)**
- **Domain**: dsp1830.shop (temporary staging)
- **Final Domain**: www.dsp1830.shop (after DNS cutover)
- **Server**: Apache/MySQL on Linux
- **PHP Version**: 7.4+
- **Database**: MySQL 5.7+ with utf8mb4 charset
- **Auto-Detection**: Domain automatically detected via `$_SERVER['HTTP_HOST']`

**Legacy Server (dsp1830.shop - PHP 5.2)**
- **Status**: Read-only, no new deployments
- **Retirement**: After DNS cutover to new server

### Development Environment

**Local (WSL2 Ubuntu)**
- **Path**: `/var/www/html`
- **URL**: http://localhost
- **PHP**: 7.4+
- **Database**: dsp1830 (matches production schema)

**Local (XAMPP Windows)**
- **Path**: `C:\xampp\htdocs`
- **URL**: http://localhost
- **Sync**: Mirrors WSL environment
- **Use**: Windows-based testing

## 🏭 Business Information

### Company Details
- **Company Name**: 두손기획인쇄 (Duson Planning Print)
- **Registration**: Business License #201-10-69847
- **Industry**: Commercial Printing Services

### Contact Information
- **Main Phone**: 02-2632-1830
- **Free Call**: 1688-2384  
- **Fax**: 02-2632-1831
- **Address**: 서울 영등포구 영등포로 36길 9, 송호빌딩 1F

### Business Hours
- **Weekdays**: 09:00 - 18:00 KST
- **Saturday**: 09:00 - 13:00 KST
- **Sunday/Holidays**: Closed

## 💼 Key Business Processes

### 1. Order Processing Flow
```
Customer → Quote Request → Price Calculation → Order Placement 
→ File Upload → Proofing → Production → Delivery
```

### 2. Pricing Automation
- Dynamic pricing based on specifications
- Quantity-based discounts
- Material cost calculations
- Delivery fee automation

### 3. Member Management
- Registration and authentication
- Order history tracking
- Loyalty points system
- Corporate account support

### 4. Admin Operations
- Order status management
- Production scheduling
- Inventory tracking
- Financial reporting

## 📊 System Statistics

### Scale
- **Active Products**: 15+ print categories
- **Daily Orders**: 50-100 average
- **Registered Members**: 5,000+
- **File Storage**: 500GB+ managed

### Performance Targets
- **Page Load**: < 2 seconds
- **Quote Generation**: < 500ms
- **File Upload**: Up to 100MB per file
- **Concurrent Users**: 100+ supported

## 🔄 Recent Updates & Focus Areas

### Current Sprint (auth-system-fix)
- Enhanced authentication security
- Session management improvements
- Password encryption updates
- Access control refinement

### Upcoming Priorities
- Mobile responsiveness optimization
- Payment gateway integration
- API development for partners
- Performance optimization

## 📈 Business Impact

### Revenue Streams
- **Online Orders**: 70% of total revenue
- **Corporate Clients**: 30% bulk orders
- **Repeat Customers**: 60% retention rate
- **Average Order Value**: ₩50,000 - ₩200,000

### Competitive Advantages
- Instant automated quotations
- Real-time order tracking
- Professional proofing system
- Competitive pricing algorithm

## 🎯 Project Goals

### Short-term (3 months)
- Complete security enhancements
- Improve mobile experience
- Streamline checkout process
- Add new payment methods

### Mid-term (6 months)
- Launch mobile app
- Implement AI-based pricing
- Add design templates
- Expand product catalog

### Long-term (12 months)
- National expansion
- B2B portal development
- Integration with ERP systems
- International shipping support

## 🛠️ Technical Highlights

### Architecture
- **Pattern**: MVC-inspired structure
- **Database**: Normalized with 50+ tables
- **Sessions**: PHP-based authentication
- **Files**: Organized upload system

### Key Technologies
- **Backend**: PHP 7.4+, MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Libraries**: PHPMailer, jQuery
- **Tools**: XAMPP, Git, VS Code

## 📚 Related Documentation

For detailed information, refer to:
- [기술 스택 및 디렉토리 구조](CLAUDE_TechStack.md) - Technical architecture
- [관리자 시스템](CLAUDE_AdminSystem.md) - Admin panel details
- [프론트엔드 & UI/UX](CLAUDE_FrontendUI.md) - Frontend implementation
- [보안 & 성능](CLAUDE_Security.md) - Security measures
- [트러블슈팅](CLAUDE_Troubleshooting.md) - Common issues

## 🔒 Critical Notes

### Database Convention
- **Tables**: ALWAYS lowercase (e.g., `mlangprintauto_littleprint`)
- **Files**: PRESERVE original case (e.g., `MlangPrintAuto/`)
- **Migration**: Critical for XAMPP → Production deployment

### Security Considerations
- All user inputs sanitized
- SQL injection prevention
- XSS protection implemented
- File upload validation strict

---
*Last Updated: 2025-01-03*  
*Version: 2.0*  
*Maintained by: Development Team*