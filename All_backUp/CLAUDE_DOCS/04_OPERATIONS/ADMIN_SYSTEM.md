# 🗄️ Admin Management System

⚠️ **중요: Admin 관련 기능 수정 시 반드시 사전 계획 수립 후 진행**

## 📋 Table of Contents

1. [System Overview](#system-overview)
2. [Core Features](#core-features)
   - [주문 관리 (Order Management)](#order-management)
   - [회원 관리 (Member Management)](#member-management)
   - [상품 관리 (Product Management)](#product-management)
   - [파일 관리 (File Management)](#file-management)
   - [통계 관리 (Statistics)](#statistics-management)
3. [Workflows](#workflows)
4. [Access Control](#access-control)
5. [Missing Features](#missing-features)

---

## 🎯 System Overview

### Directory Structure
```
admin/
├── 📄 Core Files
│   ├── index.php              # Admin dashboard
│   ├── dashboard.php          # Main control panel
│   ├── login.php              # Admin authentication
│   └── logout.php             # Session termination
│
├── 📂 Management Modules
│   ├── member_management/     # 회원 관리
│   ├── order_management/      # 주문 관리
│   ├── product_management/    # 상품 관리
│   ├── file_management/       # 파일 관리
│   └── statistics/            # 통계 & 보고서
│
├── 📂 MlangPrintAuto/         # Print-specific admin
│   ├── LittlePrint/           # 소량인쇄 관리
│   ├── MemberOrderOffice/     # 주문처리실
│   └── upload/                # 업로드 관리
│
└── 📂 Resources
    ├── css/                   # Admin styles
    ├── js/                    # Admin scripts
    └── includes/              # Common components
```

## 🔧 Core Features

### <a id="order-management"></a>1. 주문 관리 (Order Management)

#### Features Matrix
| Function | Description | Status | Priority |
|----------|-------------|--------|----------|
| 주문 목록 | Real-time order list | ✅ Active | Critical |
| 상태 변경 | Status update system | ✅ Active | Critical |
| 교정 관리 | Proofing approval | ⚠️ Partial | High |
| 배송 추적 | Delivery tracking | ✅ Active | High |
| 취소/환불 | Cancellation process | ✅ Active | Medium |
| 재주문 처리 | Reorder management | ❌ Missing | Low |

#### Order Status Workflow
```mermaid
신규주문 → 결제확인 → 교정대기 → 교정승인 → 제작중 → 제작완료 → 배송중 → 배송완료
         ↓           ↓           ↓           ↓
      결제대기    교정수정    제작보류    배송보류
```

### <a id="member-management"></a>2. 회원 관리 (Member Management)

#### Member Types & Permissions
| Type | Description | Features | Discount |
|------|-------------|----------|----------|
| **일반회원** | Individual customers | Basic ordering | 0-5% |
| **사업자회원** | Business accounts | Bulk orders, Tax invoice | 5-10% |
| **VIP회원** | Premium customers | Priority support | 10-15% |
| **관리자** | Admin accounts | Full system access | N/A |

#### Member Management Functions
| Function | Path | Access Level |
|----------|------|--------------|
| 회원 목록 조회 | `/admin/member_management/list.php` | Manager+ |
| 회원 정보 수정 | `/admin/member_management/edit.php` | Manager+ |
| 회원 등급 변경 | `/admin/member_management/grade.php` | Admin |
| 포인트 관리 | `/admin/member_management/points.php` | Manager+ |
| 회원 통계 | `/admin/member_management/stats.php` | All |

### <a id="product-management"></a>3. 상품 관리 (Product Management)

#### Product Categories
| Category | Products | Price Management | Gallery |
|----------|----------|------------------|---------|
| **명함/스티커** | 15+ types | Dynamic pricing | ✅ Active |
| **전단지/포스터** | 10+ sizes | Size-based | ✅ Active |
| **봉투/엽서** | 8+ formats | Template-based | ⚠️ Partial |
| **NCR/어음** | 5+ types | Fixed pricing | ❌ Missing |
| **특수인쇄** | Custom | Quote-based | ❌ Missing |

#### Price Management Workflow
```
1. 기본 가격 설정
   └─ 재료비 + 인건비 + 마진
2. 옵션별 가격 추가
   └─ 용지, 코팅, 후가공
3. 수량별 할인 적용
   └─ 구간별 할인율 설정
4. 회원 등급 할인
   └─ 등급별 추가 할인
5. 최종 가격 계산
   └─ 자동 계산 공식 적용
```

### <a id="file-management"></a>4. 파일 관리 (File Management)

#### File Processing Pipeline
```
Step 1: Upload Reception
├─ Validate file type (AI, PSD, PDF, JPG)
├─ Check file size (<100MB)
└─ Generate unique filename

Step 2: File Storage
├─ Move to secure directory
├─ Create thumbnail preview
└─ Update database record

Step 3: File Processing
├─ Convert to print-ready format
├─ Apply color profile (CMYK)
└─ Generate proof copy

Step 4: Archive Management
├─ Compress completed files
├─ Move to archive after 30 days
└─ Auto-delete after 90 days
```

#### Storage Structure
| Directory | Purpose | Retention | Auto-cleanup |
|-----------|---------|-----------|--------------|
| `/upload/temp/` | Temporary uploads | 24 hours | ✅ Yes |
| `/upload/pending/` | Awaiting approval | 7 days | ✅ Yes |
| `/upload/approved/` | Production files | 30 days | ⚠️ Manual |
| `/upload/archive/` | Completed orders | 90 days | ✅ Yes |

### <a id="statistics-management"></a>5. 통계 관리 (Statistics & Reports)

#### Available Reports
| Report Type | Frequency | Format | Automated |
|-------------|-----------|--------|-----------|
| 일일 매출 | Daily | Chart + Table | ✅ Yes |
| 주간 리포트 | Weekly | PDF | ⚠️ Semi |
| 월간 분석 | Monthly | Excel | ❌ Manual |
| 제품별 통계 | On-demand | Dashboard | ✅ Yes |
| 회원 분석 | Monthly | Chart | ✅ Yes |

## 📊 Workflows

### 주문 처리 Complete Workflow

```
┌─────────────────────────────────────────┐
│         1. 주문 접수 (Order Receipt)     │
├─────────────────────────────────────────┤
│ • Customer places order                 │
│ • System generates order ID             │
│ • Email notification sent               │
│ • Admin dashboard updated               │
└────────────────┬────────────────────────┘
                 ↓
┌─────────────────────────────────────────┐
│      2. 결제 확인 (Payment Verify)       │
├─────────────────────────────────────────┤
│ • Check payment status                  │
│ • Verify amount                         │
│ • Update order status                   │
│ • Send confirmation email               │
└────────────────┬────────────────────────┘
                 ↓
┌─────────────────────────────────────────┐
│      3. 파일 검증 (File Validation)      │
├─────────────────────────────────────────┤
│ • Download customer files               │
│ • Check file format/quality             │
│ • Convert to CMYK if needed            │
│ • Generate preview                      │
└────────────────┬────────────────────────┘
                 ↓
┌─────────────────────────────────────────┐
│       4. 교정 승인 (Proof Approval)      │
├─────────────────────────────────────────┤
│ • Create proof copy                     │
│ • Send to customer                      │
│ • Wait for approval                     │
│ • Apply corrections if needed           │
└────────────────┬────────────────────────┘
                 ↓
┌─────────────────────────────────────────┐
│        5. 제작 (Production)              │
├─────────────────────────────────────────┤
│ • Send to print queue                   │
│ • Update production status              │
│ • Quality check                         │
│ • Packaging                             │
└────────────────┬────────────────────────┘
                 ↓
┌─────────────────────────────────────────┐
│         6. 배송 (Delivery)               │
├─────────────────────────────────────────┤
│ • Generate shipping label               │
│ • Update tracking number                │
│ • Send notification                     │
│ • Complete order                        │
└─────────────────────────────────────────┘
```

### Daily Admin Tasks Workflow

#### Morning (09:00-10:00)
1. **Dashboard Check**
   - [ ] Review overnight orders
   - [ ] Check payment confirmations
   - [ ] Review system alerts

2. **Priority Processing**
   - [ ] Process VIP orders first
   - [ ] Handle urgent requests
   - [ ] Assign tasks to team

#### Midday (12:00-13:00)
3. **Status Updates**
   - [ ] Update production status
   - [ ] Send proofing requests
   - [ ] Process approvals

#### Afternoon (15:00-16:00)
4. **File Management**
   - [ ] Clean temporary files
   - [ ] Archive completed orders
   - [ ] Backup critical data

#### End of Day (17:00-18:00)
5. **Reports & Review**
   - [ ] Generate daily report
   - [ ] Review tomorrow's schedule
   - [ ] Send status emails

## 🔐 Access Control

### Admin Roles & Permissions
| Role | Order | Member | Product | Files | Stats | System |
|------|-------|--------|---------|-------|-------|--------|
| **Super Admin** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **Manager** | ✅ Full | ✅ Edit | ✅ Edit | ✅ Full | ✅ View | ❌ None |
| **Staff** | ✅ Edit | ✅ View | ✅ View | ✅ Edit | ✅ View | ❌ None |
| **Support** | ✅ View | ✅ View | ❌ None | ❌ None | ❌ None | ❌ None |

### Security Measures
- Session timeout: 30 minutes
- IP restriction available
- Activity logging enabled
- Two-factor authentication (planned)

## 🚧 Missing Features

### Priority 1 - Critical (Immediate)
| Feature | Impact | Estimated Time |
|---------|--------|----------------|
| **교정 관리 모듈** | Order delays | 2 weeks |
| **실시간 알림** | Missed updates | 1 week |
| **백업 자동화** | Data loss risk | 3 days |

### Priority 2 - Important (Q1 2025)
| Feature | Impact | Estimated Time |
|---------|--------|----------------|
| **재고 관리** | Stock issues | 3 weeks |
| **API Integration** | Manual work | 4 weeks |
| **모바일 관리자** | Remote management | 6 weeks |

### Priority 3 - Enhancement (Q2 2025)
| Feature | Impact | Estimated Time |
|---------|--------|----------------|
| **AI 가격 추천** | Pricing accuracy | 8 weeks |
| **고객 채팅** | Support efficiency | 4 weeks |
| **대시보드 커스터마이징** | UX improvement | 2 weeks |

## 📝 Implementation Notes

### Database Tables
```sql
-- Core admin tables
admin_users          # Admin accounts
admin_roles          # Role definitions
admin_permissions    # Permission matrix
admin_logs          # Activity tracking
admin_settings      # System configuration
```

### Key Files
- `/admin/includes/auth.php` - Authentication logic
- `/admin/includes/permissions.php` - Access control
- `/admin/includes/functions.php` - Utility functions
- `/admin/config.php` - System configuration

### API Endpoints (Planned)
```
POST   /api/admin/login
GET    /api/admin/orders
PUT    /api/admin/orders/{id}/status
GET    /api/admin/members
POST   /api/admin/products
DELETE /api/admin/files/{id}
```

---
*Last Updated: 2025-01-03*  
*Version: 2.0*  
*Focus: Workflow Optimization*