# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

# ⚠️ 🔴 CRITICAL: bind_param 검증 규칙 🔴 ⚠️

**#1 MOST COMMON CAUSE OF DATA CORRUPTION / SAVE FAILURES**

### 🔴 3번 검증 (MANDATORY - NO EXCEPTIONS!)

1. **Query의 `?` 개수**: `substr_count($query, '?')`
2. **타입 문자열 길이**: `strlen($type_string)`
3. **변수 개수**: 손가락으로 하나씩 세기

**ALL THREE MUST MATCH EXACTLY! 하나라도 다르면 커밋 금지!**

```php
// ✅ CORRECT PATTERN - 주석으로 개수 명시
// 7 parameters: session_id(s) + product_type(s) + price(i) + vat_price(i) + name(s) + email(s) + phone(s)
$query = "INSERT INTO shop_temp (session_id, product_type, st_price, st_price_vat, name, email, phone)
          VALUES (?, ?, ?, ?, ?, ?, ?)";  // 7 placeholders

$type_string = "ssiisss";  // 7 chars
mysqli_stmt_bind_param($stmt, $type_string,
    $session_id,    // 1: s
    $product_type,  // 2: s
    $price,         // 3: i
    $vat_price,     // 4: i
    $name,          // 5: s
    $email,         // 6: s
    $phone          // 7: s
);
```

**타입 참조**: `i` = integer, `s` = string, `d` = double, `b` = BLOB

**증상 (bind_param 문제일 때)**:
- FormData에는 데이터가 있는데 DB에 저장 안 됨
- 일부 필드만 '0' 또는 NULL로 저장
- `mysqli_stmt_execute()` 실패 (return false)

---

## 📦 Git 저장소 규칙

**GitHub**: https://github.com/songyoungsoo/duson-print-system

### Git 계정
| 항목 | 값 |
|------|-----|
| **사용자명** | `songyoungsoo` |
| **이메일** | `yeongsu32@gmail.com` |

### .gitignore 규칙
- ✅ **포함**: PHP, JS, CSS, 설정파일, 문서(md)
- ❌ **제외**: 이미지, 업로드폴더, SQL덤프, PDF, vendor/, node_modules/, .env

### 자동 Git 규칙
```bash
# 모든 코딩 작업 완료 시 자동 수행
git add .

# 커밋 (사용자 요청 시)
git commit -m "설명"

# 푸시 (수초 이내 완료되어야 함)
git push origin main
```

---

## 🏢 Project Context

**Duson Planning Print System (두손기획인쇄)** - PHP 기반 인쇄 서비스 관리 시스템

### 환경 구성
```
Local Development (localhost)
├─ WSL2 Ubuntu + XAMPP Windows
├─ Path: /var/www/html
├─ PHP 7.4+
└─ Database: dsp1830

Modern Staging (dsp1830.shop)
├─ PHP 7.4+
├─ 현재 개발 중
└─ 자동 감지: $_SERVER['HTTP_HOST'] 기반

Legacy Production (dsp1830.shop)
├─ PHP 5.2 (deprecated)
└─ 수정 금지, 폐기 예정
```

### 환경 자동 감지
`config.env.php` + `db.php`에서 `$_SERVER['HTTP_HOST']` 기반 자동 설정:
```php
// ❌ 잘못된 방법 - 하드코딩
$url = "http://dsp1830.shop/login.php";

// ✅ 올바른 방법 - 자동 감지
$url = $admin_url . "/login.php";
```

---

## 🚨 Critical Conventions

### Database
- **테이블명 소문자**: `mlangprintauto_namecard` (NOT `MlangPrintAuto_NameCard`)
- **연결 변수**: `$db` (primary), 레거시 호환: `$conn = $db;`
- **Charset**: `utf8mb4`, `mysqli_set_charset($db, 'utf8')`

### 파일/디렉토리 (Linux case-sensitive!)
- **모두 소문자**: `admin/mlangprintauto/` (NOT `admin/MlangPrintAuto/`)
- **Include**: `include "cateadmin_title.php";` (NOT `CateAdmin_title.php`)
- **심볼릭 링크 금지**: 실제 디렉토리 경로만 사용

### PHP 7.4+
- 변수 초기화: `$var = $var ?? '';`
- Prepared statements 필수: SQL 문자열 연결 금지

---

## 🛠️ Development Commands

### 서비스 시작
```bash
# WSL2 Ubuntu
sudo service apache2 start && sudo service mysql start

# XAMPP Windows
C:\xampp\xampp-control.exe
```

### 접속
```bash
# 로컬 사이트
http://localhost/mlangprintauto/[product]/

# 디버그 모드
http://localhost/mlangprintauto/inserted/?debug=1
http://localhost/?debug_db=1  # 환경 감지 확인

# phpMyAdmin
http://localhost/phpmyadmin/
```

### FTP 배포 (dsp1830.shop)
```bash
# 단일 파일 업로드
curl -T "/var/www/html/path/file.php" \
  -u "dsp1830:PASSWORD" \
  "ftp://dsp1830.shop/path/file.php"

# 디렉토리 생성 포함
curl -T "file.php" --ftp-create-dirs \
  -u "dsp1830:PASSWORD" \
  "ftp://dsp1830.shop/path/file.php"
```

**⚠️ FTP 경로**: `/` (FTP 루트) = 웹루트 (동일함)

---

## 📁 Architecture

### 디렉토리 구조
```
mlangprintauto/[product]/     # 제품 페이지 (9개 제품)
├── index.php                 # 메인 페이지 + 계산기
├── add_to_basket.php         # 장바구니 API
├── calculate_price_ajax.php  # 가격 계산 API
└── calculator.js             # 클라이언트 로직

mlangorder_printauto/         # 주문 처리
├── OnlineOrder_unified.php   # 주문 제출
├── ProcessOrder_unified.php  # 주문 처리
└── OrderComplete_*.php       # 주문 완료

admin/mlangprintauto/         # 관리자 시스템 (소문자!)
includes/                     # 공유 PHP 컴포넌트
db.php                        # DB 연결
config.env.php               # 환경 감지
```

### Key Files
| 용도 | 파일 |
|------|------|
| DB 연결 | `db.php`, `config.env.php` |
| 제품 설정 | `admin/MlangPrintAuto/includes/ProductConfig.php` |
| 인증 | `includes/auth.php` |
| 파일 업로드 | `includes/StandardUploadHandler.php`, `includes/UploadPathHelper.php` |
| 갤러리 | `includes/gallery_data_adapter.php` |

---

## 🎯 Product Types (9개 제품)

| Code | Name | Directory | Database Table |
|------|------|-----------|----------------|
| `inserted` | 전단지 | `mlangprintauto/inserted/` | `mlangprintauto_inserted` |
| `namecard` | 명함 | `mlangprintauto/namecard/` | `mlangprintauto_namecard` |
| `envelope` | 봉투 | `mlangprintauto/envelope/` | `mlangprintauto_envelope` |
| `sticker` | 스티커 | `mlangprintauto/sticker_new/` | `mlangprintauto_sticker` |
| `msticker` | 자석스티커 | `mlangprintauto/msticker/` | `mlangprintauto_msticker` |
| `cadarok` | 카다록 | `mlangprintauto/cadarok/` | `mlangprintauto_cadarok` |
| `littleprint` | **포스터** ⚠️ | `mlangprintauto/littleprint/` | `mlangprintauto_littleprint` |
| `merchandisebond` | 상품권 | `mlangprintauto/merchandisebond/` | `mlangprintauto_merchandisebond` |
| `ncrflambeau` | NCR양식 | `mlangprintauto/ncrflambeau/` | `mlangprintauto_ncrflambeau` |

⚠️ **주의**: `littleprint` = 포스터 (레거시 코드명, 변경 불가)

---

## 💰 Price Calculation Flow

```
1. User selects options → calculator.js
2. AJAX → calculate_price_ajax.php → {total_price, vat_price}
3. window.currentPriceData 설정
4. 장바구니 버튼 → add_to_basket.php
   POST: calculated_price, calculated_vat_price, product_type
5. shop_temp 테이블 저장 (st_price, st_price_vat)
6. 주문 → ProcessOrder_unified.php → mlangorder_printauto 테이블
```

### Cart Addition (JavaScript)
```javascript
formData.append("calculated_price", Math.round(window.currentPriceData.total_price));
formData.append("calculated_vat_price", Math.round(window.currentPriceData.vat_price));
formData.append("product_type", "inserted"); // 제품 코드
```

---

## 📤 File Upload System

### StandardUploadHandler 사용 (전체 품목 표준화)
```php
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';

$upload_result = StandardUploadHandler::processUpload('product_name', $_FILES);
$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

// DB 저장 (단일 INSERT)
$sql = "INSERT INTO shop_temp (..., uploaded_files, ImgFolder, ThingCate)
        VALUES (?, ..., ?, ?, ?)";
```

### 경로 구조
```
/ImgFolder/_MlangPrintAuto_{product}_index.php/{YYYY}/{MMDD}/{IP}/{timestamp}/{filename}
```

### JSON Metadata
```json
[{
  "original_name": "file.png",
  "saved_name": "file.png",
  "path": "/var/www/html/ImgFolder/...",
  "size": 12345,
  "web_url": "/ImgFolder/..."
}]
```

---

## 🎨 Frontend Layout

### 제품 페이지 구조
```
.product-container (max-width: 1200px)
├── .top-header (navigation)
├── .page-title (product title)
└── .product-content (grid: 1fr 1fr)
    ├── .product-gallery (left 50%)
    └── .product-calculator (right 50%)
```

### CSS 로딩 순서 (중요!)
1. `product-layout.css`
2. `unified-price-display.css`
3. `compact-form.css`
4. `unified-gallery.css`
5. `btn-primary.css`
6. `[product]-inline-styles.css`
7. **`common-styles.css`** - ⚠️ 마지막에 로드 (최우선)

### Responsive
- Mobile (< 768px): `.product-content` 세로 스택
- Desktop (≥ 768px): `.product-content` 좌우 배치

---

## 🖼️ Gallery System

### Two-Tier System
1. **Main Gallery** (4 thumbnails): 제품 페이지 좌측
2. **Modal Gallery** ("샘플 더보기"): 팝업, 페이지네이션

### Main Gallery (4개 썸네일)
**우선순위 (gallery_data_adapter.php)**:
1. `/ImgFolder/sample/{product}/` - 일반 샘플 이미지
2. `mlangorder_printauto` DB - 실제 주문 (샘플 부족 시)

### Modal Gallery ("샘플 더보기")
**우선순위 (get_real_orders_portfolio.php API)**:
1. `/ImgFolder/samplegallery/{product}/` - 큐레이티드 고품질 샘플 (최우선)
2. `mlangorder_printauto` DB - 실제 주문 (2022-01-01 ~ 2024-12-31)
   - Type 필드로 제품 필터링
   - 테스트 주문 자동 제외
   - 0바이트 파일 필터링
   - 개인정보 민감 제품(명함/봉투/양식지)은 실제 주문 제외

### Key Files
- `includes/gallery_data_adapter.php` - 메인 갤러리 데이터 로더
- `api/get_real_orders_portfolio.php` - "샘플 더보기" API
- `includes/new_gallery_wrapper.php` - 갤러리 렌더러
- `includes/unified_gallery_modal.php` - 모달 컴포넌트
- `js/common-gallery-popup.js` - JS 트리거

### Image Storage Hierarchy
```
/ImgFolder/
├── sample/{product}/           # 메인 갤러리용 (4개 썸네일)
├── samplegallery/{product}/    # 모달용 큐레이티드 샘플
└── {product}/gallery/          # 레거시 (사용 안 함)

/mlangorder_printauto/upload/{orderNo}/  # 실제 주문 파일
```

---

## 🔐 Security

### SQL Injection Prevention
```php
// ALWAYS use prepared statements
$stmt = mysqli_prepare($db, "SELECT * FROM shop_temp WHERE session_id = ?");
mysqli_stmt_bind_param($stmt, "s", $session_id);
mysqli_stmt_execute($stmt);
```

### XSS Prevention
```php
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

### File Upload Validation
```php
$allowed = ['.jpg', '.png', '.pdf', '.ai'];
$max_size = 15 * 1024 * 1024; // 15MB
```

---

## 🔧 Common Error Patterns

### "Undefined variable: $shop_data"
- `$item['product_type']` 사용 (NOT `$shop_data['ThingCate']`)

### "Price showing as 0 in cart"
- 파라미터명 확인: `calculated_price` (NOT just `price`)
- `window.currentPriceData` 설정 확인

### "No data supplied for parameters"
- bind_param 타입 문자열 개수 vs 실제 파라미터 개수 확인

### JavaScript 함수 미정의
```javascript
// HTML에서 호출하는 함수명과 JS 함수명이 다를 때
<select onchange="calculatePrice()">  // HTML
function autoCalculatePrice() { }      // JS - 불일치!

// 해결: alias 함수 추가
function calculatePrice() { debouncedCalculatePrice(); }
```

---

## 📚 Documentation

상세 문서는 `CLAUDE_DOCS/` 디렉토리:
- `01_CORE/` - 프로젝트 개요
- `02_ARCHITECTURE/` - 기술 아키텍처, 환경 설정
- `03_PRODUCTS/` - 제품별 가이드
- `04_OPERATIONS/` - 배포, 관리자 시스템
- `05_DEVELOPMENT/` - 프론트엔드, MCP, 트러블슈팅
- `06_ARCHIVE/` - 완료된 프로젝트

전체 색인: [CLAUDE_DOCS/INDEX.md](CLAUDE_DOCS/INDEX.md)

---

## 🔄 Key Fixes Reference

### 갤러리 시스템 이미지 소스 분리 (2025-12-26)
- **메인 갤러리 4개 썸네일**: `/ImgFolder/sample/{product}/` 사용
- **샘플 더보기 모달**: `/ImgFolder/samplegallery/{product}/` 최우선 표시
- **모달 DB 필터**: 2022-01-01 ~ 2024-12-31 기간 한정
- **개인정보 보호**: 명함/봉투/양식지는 실제 주문 이미지 제외
- 파일: `gallery_data_adapter.php`, `get_real_orders_portfolio.php`

### 전단지 매수(mesu) E2E 수정 (2025-12-17)
- Form name 속성 추가: `<form id="orderForm" name="choiceForm">`
- `document.forms[]`는 **name** 속성으로 접근 (NOT id)

### 세션 8시간 연장 (2025-12-11)
- 세션 유효시간: 24분 → 8시간
- 자동 로그인: 30일 토큰 기반 (`remember_tokens` 테이블)

### 주문 완료 unit 필드 (2025-12-10)
- 하드코딩 '매' → DB unit 필드 사용
- 전단지: '연', 기타: '매'

---

## 🚚 LOGEN 택배 API

### 자격증명
| 항목 | 값 |
|------|-----|
| 고객사 코드 | `53058114` |
| 사용자명 | `du1830` |
| API 엔드포인트 | `https://openapi.ilogen.com/lrm02b-edi/edi/getSlipNo` |

### IP 화이트리스트 필요
- 개발: `124.195.240.61`
- 운영: `220.73.160.27`

### 구현 파일
- `shop_admin/logen_api_config.php` - 설정
- `shop_admin/logen_api_handler.php` - API 클래스
- `shop_admin/logen_auto_register.php` - AJAX 엔드포인트

---

## 🔌 MCP Integration

### Quick Reference
- 설치 가이드: `CLAUDE_DOCS/05_DEVELOPMENT/MCP_Installation_Guide.md`
- 설정 위치: `~/.claude/` (User) | `./.claude/` (Project)

### 설치 흐름
```bash
mcp-installer                    # 기본 설치
claude mcp list                  # 확인
claude --debug                   # 디버그 모드
echo "/mcp" | claude --debug     # MCP 작동 확인
```

---

*Last Updated: 2025-12-26*
*Environment: WSL2 Ubuntu (supports XAMPP)*
*Working Directory: /var/www/html*
