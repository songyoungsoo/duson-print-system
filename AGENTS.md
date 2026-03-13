# Duson Planning Print System - AI 개발 가이드

## 📋 프로젝트 개요
@./README.md

## 🏗️ 핵심 기능 (상세)
- 결제 시스템: @./docs/features/payment.md
- 배송 추정: @./docs/features/shipping.md
- 이메일 시스템: @./docs/features/email.md
- AI 챗봇: @./docs/features/ai-chatbot.md
- 인증 시스템: @./docs/features/auth.md
- AI 서비스 팀: @./docs/features/ai-services.md
- 고객 리뷰: @./docs/features/review-system.md

## 🛠️ 개발 가이드
- 코딩 표준: @./CLAUDE.md
- 배포: @./DEPLOYMENT.md

## 🔗 참조 문서
- 데이터베이스: @./docs/components/database.md
- API: @./docs/components/api.md

---

## 🚨🚨🚨 Plesk URL 보안 — 쉼표(%2C) 차단 (CRITICAL) 🚨🚨🚨

**⚠️ Plesk 서버에서 URL에 `%2C`(쉼표)가 포함되면 무조건 500 에러.**

```
GET /page.php?orders=84664%2C84665   → ❌ 500 Internal Server Error
GET /page.php?orders=84664,84665     → ❌ 500 (브라우저가 자동 인코딩)
GET /page.php?orders=84664_84665     → ✅ 200 OK
GET /page.php?test=a%2Cb              → ❌ 500 (어떤 파라미터든 쉼표면 차단)
GET /page.php?test=a%3Bb              → ❌ 500 (세미콜론도 차단)
GET /page.php?test=a%7Cb              → ❌ 500 (파이프도 차단)
```

**원인**: Plesk의 ModSecurity 또는 nginx 보안 규칙이 URL 인코딩된 특수문자를 SQL Injection/XSS로 판단하여 차단.

**영향받는 기능**: URL GET 파라미터로 여러 값을 전달하는 모든 곳
- `orders=84664,84665,84666` (다건 주문번호)
- 기타 쉼표 구분 목록 파라미터

**해결책**: 쉼표(`,`) 대신 언더스코어(`_`)를 구분자로 사용

```php
// ❌ 절대 금지: URL에 쉼표 사용
$url = "orderlist.php?orders=" . implode(',', $orderNos);

// ✅ 올바른 방법: 언더스코어 구분자
$url = "orderlist.php?orders=" . implode('_', $orderNos);
$orderNos = explode('_', $_GET['orders']);
```

---

## 📦 Product Type Mapping (9 Standard Products)

| # | Product Name | Folder Name (FORCED) | ❌ Forbidden Names | Unit | 가격 방식 |
|---|-------------|---------------------|------------------|-------|----------|
| 1 | 전단지 | `inserted` | leaflet | 연 | DB lookup |
| 2 | **스티커** | **`sticker_new`** | sticker | 매 | **⚠️ 수학공식** |
| 3 | 자석스티커 | `msticker` | - | 매 | DB lookup |
| 4 | 명함 | `namecard` | - | 매 | DB lookup |
| 5 | 봉투 | `envelope` | - | 매 | DB lookup |
| 6 | 포스터 | `littleprint` | poster | 매 | DB lookup |
| 7 | 상품권 | `merchandisebond` | giftcard | 매 | DB lookup |
| 8 | 카다록 | `cadarok` | catalog | 부 | DB lookup |
| 9 | NCR양식지 | `ncrflambeau` | form, ncr | 권 | DB lookup |

---

## 🚨🚨🚨 스티커 가격 계산 — 다른 제품과 완전히 다름 (CRITICAL) 🚨🚨🚨

**⚠️ 이것만 기억해: 스티커는 DB 가격표 조회가 아닌 "수학 공식"으로 가격을 계산한다.**

```
┌───────────────────────────────────────────────────────────────────────┐
│  8개 제품 (전단지, 명함, 봉투, 포스터, 상품권, 카다록, NCR, 자석스티커) │
│  → DB 가격표 lookup: mlangprintauto_* 테이블에서                       │
│    style + Section + quantity → money 조회                            │
│                                                                       │
│  ⚡ 스티커 (sticker_new) 만 예외!                                     │
│  → 수학 공식 계산: 재질(jong) × 가로(garo) × 세로(sero) × 수량(mesu)  │
│    + 도무송비 + 특수용지비 = 가격                                      │
│  → DB 가격표 테이블 없음! (mlangprintauto_sticker 존재하지만 별개용도)  │
│  → 사이즈를 mm 단위로 자유 입력 (드롭다운 선택 아님)                   │
└───────────────────────────────────────────────────────────────────────┘
```

### 스티커 가격 계산 공식 (SSOT: `sticker_new/calculate_price_ajax.php`)

**입력 파라미터 6개:**

| 파라미터 | 설명 | 예시 |
|---------|------|------|
| `jong` (재질) | 코드 3자 + 이름 (예: `jil 아트유광코팅`) | `jil`, `jka`, `jsp`, `cka` |
| `garo` (가로) | mm 단위 자유입력, 최대 590mm | `50`, `90`, `100` |
| `sero` (세로) | mm 단위 자유입력, 최대 590mm | `50`, `55`, `100` |
| `mesu` (수량) | 500~10,000매 (드롭다운 선택) | `500`, `1000`, `5000` |
| `domusong` (모양) | 코드 5자리 + 이름 (예: `00000 사각`) | `08000 원형` |
| `uhyung` (디자인) | 금액 (0/10000/30000) | `0`, `10000` |

**계산 흐름:**
```
1. 재질코드(j1) → shop_d1~d4 테이블에서 수량별 요율(yoyo) 조회
2. 기본가격 = (가로+4) × (세로+4) × 수량 × 요율
3. + 도무송비용 (칼 크기 × 수량 기반)
4. + 특수용지비용 (유포지/강접/초강접)
5. × 사이즈 마진비율 (소형 1.0, 대형 1.25)
6. + 기본관리비(mg) × 수량/1000
7. + 디자인비(uhyung)
8. = 공급가액 → ×1.1 = VAT포함가
```

**재질별 요율 테이블 (4개 DB 테이블):**

| 코드 | 테이블 | 재질 | 비고 |
|------|--------|------|------|
| `jil` | `shop_d1` | 아트유광/무광/비코팅, 모조비코팅 | 기본 재질 |
| `jka` | `shop_d2` | 강접아트유광코팅 | 강접착 |
| `jsp` | `shop_d3` | 유포지, 은데드롱, 투명, 크라프트 | 특수용지 (톰슨비 14) |
| `cka` | `shop_d4` | 초강접아트코팅/비코팅 | 초강접착 (톰슨비 14) |

**재질 11종 (sticker_new/index.php, en/products/order_sticker.php 동일):**
1. `jil 아트유광코팅` — Art Paper Gloss
2. `jil 아트무광코팅` — Art Paper Matte
3. `jil 아트비코팅` — Art Paper Uncoated
4. `jka 강접아트유광코팅` — Strong Adhesive Gloss
5. `cka 초강접아트코팅` — Super Strong Gloss
6. `cka 초강접아트비코팅` — Super Strong Uncoated
7. `jsp 유포지` — Yupo (Waterproof)
8. `jsp 은데드롱` — Silver Deadlong
9. `jsp 투명스티커` — Clear/Transparent
10. `jil 모조비코팅` — Bond Paper Uncoated
11. `jsp 크라프트지` — Kraft Paper

**도무송(모양) 6종:**
1. `00000 사각` — 사각 (도무송 없음, 무료)
2. `08000 사각도무송` — 사각도무송 (+8,000원)
3. `08000 귀돌` — 귀돌이 (+8,000원)
4. `08000 원형` — 원형 (+8,000원)
5. `08000 타원` — 타원 (+8,000원)
6. `19000 복잡` — 복잡한 모양 (+19,000원)

### 스티커 관련 파일 매핑 (절대 혼동 금지!)

| 파일/테이블 | 용도 | 스티커 가격 관련? |
|------------|------|-----------------|
| `sticker_new/calculate_price_ajax.php` | **스티커 가격 계산 SSOT** | ✅ 핵심 |
| `sticker_new/index.php` | 스티커 주문 페이지 (한국어) | ✅ UI |
| `en/products/order_sticker.php` | 스티커 주문 페이지 (영문) | ✅ UI |
| `shop_d1`, `shop_d2`, `shop_d3`, `shop_d4` | 재질별 요율 테이블 | ✅ 요율 |
| `v2/src/Services/AI/ChatbotService.php` | AI 챗봇 (formula 분기) | ✅ 챗봇 |
| `mlangprintauto_sticker` | ❌ **스티커 가격에 사용 안 함!** | ❌ 별개 |
| `mlangprintauto_transactioncate (Ttable='sticker')` | ❌ **스티커 가격에 사용 안 함!** | ❌ 별개 |

### ❌ 절대 하지 말 것 (스티커 관련)

```php
// ❌ 절대 금지: 스티커를 다른 제품처럼 DB lookup으로 가격 조회
$sql = "SELECT money FROM mlangprintauto_sticker WHERE style=? AND Section=? AND quantity=?";
// → 스티커는 이 방식이 아님! 수학 공식으로 계산해야 함

// ❌ 절대 금지: sticker_new를 sticker로 혼동
// sticker_new = 실제 사용하는 스티커 폴더
// sticker = transactioncate에만 존재하는 레거시 키

// ❌ 절대 금지: AI 챗봇에서 스티커를 드롭다운 cascade(style→section→quantity)로 구현
// → 스티커는 재질→가로입력→세로입력→수량→도무송→디자인 (formula 플로우)

// ✅ 올바른 방법: calculateStickerPrice() 함수로 가격 산출
$result = calculateStickerPrice($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect);
```

---

## 🔧 Critical SSOT (Single Source of Truth) Files

### Core Logic Files
- `includes/QuantityFormatter.php` - Quantity/unit formatting SSOT
- `includes/ProductSpecFormatter.php` - Product specification formatter
- `lib/core_print_logic.php` - Central logic facade

### Quote System Files
- `mlangprintauto/quote/includes/QuoteManager.php` - Quote data management
- `mlangprintauto/quote/includes/QuoteTableRenderer.php` - Table rendering SSOT

### Legacy Detection Patterns
```php
// Detect stickers from legacy data (product_type empty)
if (empty($productType) && !empty($tempItem['jong']) && !empty($tempItem['garo'])) {
    $productType = 'sticker';
}

// Or detect from product_name
if (empty($productType) && stripos($productName, '스티커') !== false) {
    $productType = 'sticker';
}
```

---

## 📋 주문 폼 데이터 흐름 (Order Form Data Flow)

### 주문 입력 → DB 저장 → 관리자 표시

```
OnlineOrder_unified.php (폼 입력)
  → form action="ProcessOrder_unified.php" (POST 처리)
    → INSERT INTO mlangorder_printauto (DB 저장)
      → admin.php?mode=OrderView (admin 조회)
        → OrderFormOrderTree.php (화면 렌더링)
```

### 주문자 정보 필드 매핑

| 폼 필드 | POST name | DB 컬럼 | 관리자 라벨 |
|---------|-----------|---------|------------|
| 성명/상호 | `username` | `name` | 이름 |
| 이메일 | `email` | `email` | 이메일 |
| 전화번호 | `phone` | `phone` | 전화 |
| 핸드폰 | `Hendphone` | `Hendphone` | 휴대폰 |
| 우편번호 | `sample6_postcode` | `zip` | 우편번호 |
| 주소 | `sample6_address` | `zip1` | 주소 |
| 상세주소 | `sample6_detailAddress` | `zip2` | 상세주소 |
| 물품수령방법 | `delivery_method` | `delivery` | 배송지 |
| 결제방법 | `payment_method` | `bank` | 입금은행 |
| 입금자명 | `bankname` | `bankname` | 입금자명 |
| 요청사항 | `cont` | `cont` | 비고 |

### 사업자 정보 필드 매핑

| 폼 필드 | POST name | DB 저장 방식 | 관리자 라벨 |
|---------|-----------|-------------|------------|
| 상호(회사명) | `business_name` | `bizname` (상호 + 사업자번호 형식) | 사업자명 |
| 사업자등록번호 | `business_number` | `bizname` + `cont` 텍스트 | 사업자명/비고 |
| 대표자명 | `business_owner` | `bizname` + `cont` 텍스트 | 사업자명/비고 |
| 업태 | `business_type` | `cont` 텍스트 | 비고 |
| 종목 | `business_item` | `cont` 텍스트 | 비고 |
| 사업장주소 | `business_address` (JS hidden) | `cont` 텍스트 | 비고 |
| 세금용메일 | `tax_invoice_email` | `cont` 텍스트 | 비고 |

### 결제방법 UI 동작

```
◉ 계좌이체 (기본값)  → 입금자명 입력란 표시 (필수, 주문자명 자동채움)
                       → 주문자명 ≠ 입금자명 시 confirm 경고
○ 카드결제           → 입금자명 숨김
○ 현금               → 입금자명 숨김
○ 기타               → 입금자명 숨김 (요청사항에 기재)
```

### 사업자 정보 자동 채움 (로그인 회원)

회원가입 시 `users` 테이블에 저장된 사업자 정보가 주문 폼에서 자동 채워짐:
- `users.business_name` → 상호(회사명)
- `users.business_number` → 사업자등록번호
- `users.business_owner` → 대표자명
- `users.business_type` → 업태
- `users.business_item` → 종목
- `users.business_address` → 사업장주소 (우편번호/주소/상세 자동 파싱)
- `users.tax_invoice_email` → 세금용 메일

**구현**: `toggleBusinessInfo()` JS 함수에서 `memberInfo` 객체 활용

### 관리자 주문서 출력 레이아웃 (2026-03-13 업데이트)

**파일**: `mlangorder_printauto/OrderFormOrderTree.php`

**A4 용지 레이아웃 (절취선 124mm)**:
```
┌─────────────────────────────────┐
│  (상단 마진 6mm)                  │
├─────────────────────────────────┤
│  📋 주문서 (관리자용) - 124mm     │
│  주문번호 | 일시 | 주문자 |        │
│  T.일반전화 | H.핸드폰             │
├─────────────────────────────────┤
│  ✂ 절 취 선 (2mm)                │  ← 124mm 지점
├─────────────────────────────────┤
│  📋 주문서 (직원용) - 155mm       │
│  주문번호 | 일시 | 주문자 |        │
│  T.일반전화 | H.핸드폰             │
├─────────────────────────────────┤
│  (하단 마진 6mm)                  │
└─────────────────────────────────┘
```

**헤더 필드 구성 (고정 너비 레이아웃)**:
- 주문번호: 14자 고정 (`width: 14ch`) - 예: `주문번호: 84829`
- 일시: 18자 고정 (`width: 18ch`) - 예: `2026-03-13 14:10` (라벨 없음)
- T.일반전화: 16자 고정 (`width: 16ch`) - 예: `T.02-2632-1830`
- H.핸드폰: 16자 고정 (`width: 16ch`) - 예: `H.010-1234-5678`
- 주문자: 나머지 공간 (`flex: 1`) - 예: `주문자: 토마토약국`

**CSS 설정**:
```css
.print-order:first-child { height: 124mm; }
.print-order.employee-copy { height: calc(285mm - 124mm - 6mm); }

/* 헤더 필드 고정 너비 */
div[style*="14ch"] { width: 14ch; flex-shrink: 0; }  /* 주문번호 */
div[style*="18ch"] { width: 18ch; flex-shrink: 0; }  /* 일시 */
div[style*="16ch"] { width: 16ch; flex-shrink: 0; }  /* 전화/핸드폰 */
```

---

## 🚨 Common Pitfalls to Avoid

### Database & Core Logic
1. ❌ bind_param count mismatch → customer name saved as '0'
2. ❌ Uppercase table names → SELECT failure
3. ❌ Uppercase include paths → file not found on Linux
4. ❌ `getUnitCode($productType)` → sticker "개" unit bug
5. ❌ Direct quantity formatting without unit validation
6. ❌ number_format(0.5) → "1" rounding error
7. ❌ Changing `littleprint` to `poster` → system-wide errors

### CSS & Frontend
8. ❌ CSS !important usage without proper diagnosis

### Quotation System (2026-02-27)
22. ❌ 견적서 프론트 `price`/`vat_price` vs 백엔드 `calculated_price`/`calculated_vat_price` → 가격 0원 저장
23. ❌ 프론트 `premium_options_data` vs 백엔드 `premium_options` → 옵션 null 저장
24. ❌ 견적서(quotation_temp)는 가격을 자체 계산 안 함 — 프론트가 POST로 보낸 값을 그대로 저장 (dumb storage)

### Plesk .htaccess (2026-02-07)
17. ❌ Apache 2.2 구문 사용 (Order, Allow) → Plesk 500 에러 유발
18. ❌ `.htaccess`를 잘못 작성하면 이미지/페이지가 500 에러 발생
19. ❌ Plesk는 nginx + Apache 조합 사용 → .htaccess는 Apache 2.4 호환만 사용
20. ❌ `/mlangorder_printauto/upload/`에 `.htaccess` 파일 생성 시 500 에러 발생 (삭제 후 정상 작동)

### PHP 8.2 호환성 — mysqli_close 순서 (CRITICAL)

**⚠️ 이것만 기억해: `mysqli_close($db)` 뒤에서 `$db`를 쓰면 PHP 8.2에서 Fatal Error로 죽는다.**

```
로컬 PHP 7.4:  mysqli_close($db) → mysqli_query($db, ...) → false 반환 (조용히 넘어감)
프로덕션 PHP 8.2: mysqli_close($db) → mysqli_query($db, ...) → ❌ Fatal Error: mysqli object is already closed
```

**실제 사고 (2026-02-15):**
- `quote_gauge.php`(플로팅 견적 위젯)가 내부에서 `mysqli_query($db, ...)` 사용
- 4개 제품 페이지에서 `mysqli_close($db)`를 include 앞에 배치
- 로컬에서 정상 → 프로덕션에서 위젯 안 보임 (Fatal Error가 display_errors=Off라 숨겨짐)

```php
// ❌ 절대 금지: DB 닫은 뒤에 DB 사용하는 include
mysqli_close($db);
include 'quote_gauge.php';  // 내부에서 $db 사용 → PHP 8.2 Fatal Error

// ✅ 올바른 순서: include 먼저, DB 닫기는 맨 마지막
include 'quote_gauge.php';  // $db 정상 사용
if (isset($db) && $db) { mysqli_close($db); }  // 페이지 끝에서 정리
```

**진단 팁:**
- 프로덕션에서만 안 되고 로컬에서 되면 → PHP 버전 차이 의심 (로컬 7.4 vs 프로덕션 8.2)
- include 결과물이 HTML에 안 나타나면 → include 대상 파일 내부의 Fatal Error 의심

21. ❌ `mysqli_close($db)` 후에 `$db` 사용하는 include → PHP 8.2 Fatal Error (로컬에서 안 잡힘!)

### Payment System
9. ❌ Enabling production mode on localhost → real payments triggered
10. ❌ Hardcoding production URLs → closeUrl domain mismatch error
11. ❌ Forgetting to test with small amounts → accidental large payments
12. ❌ Not checking logs after deployment → silent payment failures

### Authentication
13. ❌ Inconsistent password verification → same user can't login everywhere
14. ❌ Not preserving cart session during login → cart data loss
15. ❌ Only supporting bcrypt → legacy users locked out
16. ❌ Forgetting auto-upgrade → users stuck with plaintext passwords

---

## 📂 운영 문서 (필요 시 참조)

| 문서 | 파일 | 설명 |
|------|------|------|
| NAS 백업 | `docs/operations/nas-backup.md` | NAS FTP 동기화, 백업 스크립트 |
| 교정 관리 | `docs/operations/proof-management.md` | 교정파일 업로드, 이미지 뷰어, 교정확정 2단계 |
| 견적서 시스템 | `docs/operations/quote-system.md` | 견적서 상태 흐름, PDF 생성, 이메일 발송 |
| 견적서 엔진 V2 | `docs/operations/quote-engine-v2.md` | 독립형 견적/거래명세서 엔진 (QE_* 클래스, qe_* 테이블) |
| 데이터 마이그레이션 | `docs/operations/data-migration.md` | dsp114.com→NAS 데이터 이전 |
| 대시보드 카테고리 | `docs/operations/dashboard-categories.md` | 대시보드 카테고리 관리 기능 |
| 관리자 주문 등록 | `docs/operations/admin-orders.md` | 관리자 수동 주문 등록 |
| 영문 버전 | `docs/operations/en-version.md` | 해외 고객용 영문 사이트 |
| KB 시스템 | `docs/operations/kb-system.md` | Knowledge Vault 저장/검색 |
| 전화번호 포맷팅 | `docs/operations/phone-formatting.md` | 전화번호 자동 하이픈 포맷팅 |
| 팝업 관리 | `docs/operations/popup-management.md` | 레이어 팝업 등록/표시/안보기 |

## 📦 아카이브 (완료된 작업)

| 문서 | 파일 | 상태 |
|------|------|------|
| UI/UX 개선 | `docs/archive/ui-improvements-completed-2026-02.md` | ✅ 완료 |
| member→users 마이그레이션 | `docs/archive/member-users-migration-completed-2026-02.md` | ✅ 완료 |

## 📚 상세 참조 문서 (CLAUDE_DOCS/)

| 문서 | 내용 |
|------|------|
| `CLAUDE_DOCS/DB_SCHEMA.md` | 전체 DB 스키마 |
| `CLAUDE_DOCS/API_SPEC.md` | API 상세 스펙 |
| `CLAUDE_DOCS/DIRECTORY_STRUCTURE.md` | 디렉토리 구조 |
| `CLAUDE_DOCS/COMPONENT_REFERENCE.md` | 컴포넌트 참조 |
| `CLAUDE_DOCS/REBUILD_GUIDE.md` | 리빌드 가이드 |
| `CLAUDE_DOCS/인쇄원가계산시스템.md` | 인쇄원가 계산 체계 |

---

## 🔄 문서 관리 (Curator)

### 건강검진 실행
```bash
php scripts/curator.php              # 기본 리포트 (경고/오류만 표시)
php scripts/curator.php --verbose    # 상세 출력 (모든 항목)
php scripts/curator.php --summary    # 요약만 출력
php scripts/curator.php --json       # JSON 출력
```

### 검진 항목 5가지
1. **참조 검증** — AGENTS.md의 `@./` 링크와 테이블 파일 경로가 실제 존재하는지
2. **고아 문서** — `docs/`에 있지만 AGENTS.md에서 참조되지 않는 파일
3. **크기 모니터링** — AGENTS.md < 400줄, 개별 문서 < 300줄
4. **신선도** — 코드가 변경됐는데 대응 문서가 갱신 안 된 경우 (30일 경고, 90일 오류)
5. **CLAUDE_DOCS 감사** — 90일 이상 미갱신 참조 문서

### 문서 관리 규칙
- AGENTS.md에 내용 추가 시 **400줄 한도** 엄수 → 초과 시 하위 문서로 분리
- 코드 변경 시 `docs/curator-config.json`의 매핑 확인 → 대응 문서 갱신
- 새 기능 추가 시 → `docs/features/` 또는 `docs/operations/`에 문서 생성 + AGENTS.md 허브에 링크 추가
- 완료된 작업 → `docs/archive/`로 이동

### 설정 파일
- `docs/curator-config.json` — 코드↔문서 매핑, 크기/신선도 임계값, 무시 경로

---
마지막 업데이트: 2026-03-11
