# Duson Planning Print System - AI 개발 가이드

## 🔑 접속 정보 총정리

| 구분 | 접속 주소 | 아이디 | 비밀번호 | 비고 |
|------|----------|--------|---------|------|
| 홈페이지 (주) | https://dsp114.com | - | - | 주 도메인 |
| 홈페이지 (보조) | https://dsp114.co.kr | - | - | 보조 (동일 서버) |
| 관리자 대시보드 | https://dsp114.com/dashboard/ | `admin` | `admin123` | 두 도메인 모두 가능 |
| DB (프로덕션) | localhost:3306 | `dsp1830` | `t3zn?5R56` | DB명: `dsp1830` (로컬 비번: `ds701018`) |
| FTP (운영서버) | ftp://dsp114.co.kr | `dsp1830` | `cH*j@yzj093BeTtc` | 웹루트: `/httpdocs/` |
| Plesk 관리패널 | https://cmshom.co.kr:8443 | `두손기획` | `h%42D9u2m` | 서버/SSL/도메인 관리 |
| GitHub | github.com/songyoungsoo | `songyoungsoo` | `yeongsu32@gmail.com` | |
| KB 지식관리 | https://dsp114.com/kb/ | - | `duson2026!kb` | localhost는 비밀번호 없음 |
| NAS 1차 | dsp1830.ipdisk.co.kr:8000 | `admin` | `1830` | 백업 서버 |
| NAS 2차 | sknas205.ipdisk.co.kr | `sknas205` | `sknas205204203` | 추가 백업 |
| 고객센터 | 02-2632-1830 | - | - | |

---

## 📋 프로젝트 개요
@./README.md

## 🏗️ 핵심 기능 (상세)
- 결제 시스템: @./docs/features/payment.md
- 배송 추정: @./docs/features/shipping.md
- 이메일 시스템: @./docs/features/email.md
- AI 챗봇: @./docs/features/ai-chatbot.md
- 인증 시스템: @./docs/features/auth.md

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
$order_list = implode('_', $order_numbers);
header("Location: page.php?orders=" . urlencode($order_list));
// → 정상 동작

// ✅ 수신 측: 언더스코어로 분리 (레거시 쉼표도 호환)
$orders_normalized = str_replace(',', '_', $_GET['orders']);
$order_numbers = explode('_', $orders_normalized);
```

**적용 완료 파일 (2026-02-25):**

| 파일 | 역할 | 구분자 |
|------|------|--------|
| `ProcessOrder_unified.php` | 주문 완료 리다이렉트 (orders 파라미터 생성) | `_` |
| `OrderComplete_universal.php` | 주문 완료 페이지 (orders 파라미터 파싱 + JS 전달) | `_` (레거시 `,` 호환) |
| `payment/inicis_request.php` | 결제 요청 (orders 파라미터 파싱) | `_` (레거시 `,` 호환) |
| `mypage/order_detail.php` | 마이페이지 결제 링크 (orders 파라미터 생성) | `_` |

**안전한 URL 구분자 목록:**
- ✅ `_` (언더스코어) — 추천, 현재 사용 중
- ✅ `-` (하이픈) — 사용 가능
- ❌ `,` (쉼표) — 차단됨
- ❌ `;` (세미콜론) — 차단됨
- ❌ `|` (파이프) — 차단됨
- ❌ `.` (점) — 숫자 구분자로 혼동 가능, 비추천


```

**Plesk 관리 패널 (서버 관리):**
```
Plesk 접속 정보:
├─ URL: https://cmshom.co.kr:8443/login_up.php
├─ 아이디: 두손기획
├─ 비밀번호: h%42D9u2m
├─ 용도: 서버 관리, phpMyAdmin, SSL, 도메인 설정
└─ phpMyAdmin: Plesk → 데이터베이스 → phpMyAdmin 접속
```

**프로덕션 DB 접속 정보 (CRITICAL):**
```
DB 접속 정보 (dsp114.com / dsp114.co.kr 공용):
├─ Host: localhost
├─ User: dsp1830
├─ Pass: t3zn?5R56
├─ Database: dsp1830
├─ Charset: utf8mb4
└─ 용도: 프로덕션 웹사이트 DB (MySQL) — 두 도메인 동일 DB 사용

⚠️ 주의사항:
- 로컬 개발 DB와 비밀번호가 다름!
- 로컬: dsp1830 / ds701018 / dsp1830
- 프로덕션: dsp1830 / t3zn?5R56 / dsp1830
- config.env.php에서 환경별 자동 전환 (dsp114.com, dsp114.co.kr 모두 production 인식)
```

```
NAS 접속 정보 (백업 서버):
┌──────────────────────────────────────────────────────────────┐
│  🏠 1차 NAS: dsp1830.ipdisk.co.kr:8000                       │
│     ├─ User: admin                                           │
│     ├─ Pass: 1830                                            │
│     └─ 용도: 전체 데이터 백업 (마이그레이션)                   │
├──────────────────────────────────────────────────────────────┤
│  🏠 2차 NAS: sknas205.ipdisk.co.kr                           │
│     ├─ User: sknas205                                        │
│     ├─ Pass: sknas205204203                                  │
│     └─ 용도: 추가 백업                                        │
└──────────────────────────────────────────────────────────────┘
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
10. ❌ Hardcoding production URLs → closeUrl domain mismatch error (SITE_URL 상수 사용 필수!)
11. ❌ Forgetting to test with small amounts → accidental large payments
12. ❌ Not checking logs after deployment → silent payment failures

### 듀얼 도메인 (2026-02-26)
22. ❌ URL 하드코딩 (`https://dsp114.com/...`) → 반드시 `SITE_URL . "/..."` 사용
23. ❌ 이메일 본문에 도메인 하드코딩 → 접속 도메인과 불일치
24. ❌ KB에스크로 mHValue를 도메인 확인 없이 변경 → 인증마크 오류

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
| 데이터 마이그레이션 | `docs/operations/data-migration.md` | dsp114.com→NAS/dsp114.co.kr 데이터 이전 |
| 대시보드 카테고리 | `docs/operations/dashboard-categories.md` | 대시보드 카테고리 관리 기능 |
| 관리자 주문 등록 | `docs/operations/admin-orders.md` | 관리자 수동 주문 등록 |
| 영문 버전 | `docs/operations/en-version.md` | 해외 고객용 영문 사이트 |
| KB 시스템 | `docs/operations/kb-system.md` | Knowledge Vault 저장/검색 |
| 전화번호 포맷팅 | `docs/operations/phone-formatting.md` | 전화번호 자동 하이픈 포맷팅 |

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
| `docs/두손기획인쇄_기술매뉴얼_Notion.md` | **기술 매뉴얼 V2** (듀얼 도메인 하이브리드 체계) |
| `docs/두손기획인쇄_기술매뉴얼_V2.docx` | 기술 매뉴얼 V2 (Word 버전) |
| `docs/두손기획인쇄_관리자매뉴얼_Notion.md` | 관리자 매뉴얼 |

## 🌐 듀얼 도메인 하이브리드 운영 체계

> **dsp114.com(주) + dsp114.co.kr(보조) — 같은 서버, 같은 코드, 같은 DB**

### 아키텍처 개요

```
dsp114.com (주 도메인) ──┐
                          ├──▶ 175.119.156.249 (Plesk + nginx + PHP 8.2)
dsp114.co.kr (보조) ─────┘    └── /httpdocs/ → config.env.php → SITE_URL 자동 감지

localhost (개발) ─────────▶ Apache + PHP 7.4 → /var/www/html/
```

### 도메인 자동 감지 (config.env.php)

```php
// 접속 도메인에 따라 자동 전환
define('SITE_DOMAIN', get_site_domain());  // dsp114.com 또는 dsp114.co.kr
define('SITE_URL', get_site_url());        // https://dsp114.com 또는 https://dsp114.co.kr
```

**⚠️ 새 파일 작성 시 반드시 `SITE_URL` 상수 사용 — URL 하드코딩 절대 금지!**

```php
// ❌ $url = "https://dsp114.com/payment/...";
// ✅ $url = SITE_URL . "/payment/...";
```

### 구현 상태 (2026-02-26)

| 항목 | 상태 | 비고 |
|------|------|------|
| 코드 — SITE_URL 동적 감지 | ✅ 완료 | `config.env.php`에서 접속 도메인 자동 감지 |
| 코드 — 하드코딩 도메인 제거 | ✅ 완료 | 11개 파일에서 SITE_URL/SITE_DOMAIN으로 교체 |
| 코드 — KG이니시스 returnUrl/closeUrl | ✅ 완료 | SITE_URL 동적 감지, 같은 사인키 사용 가능 (이니시스 확인) |
| KB에스크로 mHValue | ✅ 완료 | dsp114.com용 `eb30fbb0...` 적용 (롤백값 주석 보존) |
| 프로덕션 배포 | ✅ 완료 | 30개 파일 FTP 업로드 (2026-02-26) |
| 기술 매뉴얼 V2 | ✅ 완료 | `docs/두손기획인쇄_기술매뉴얼_Notion.md` + DOCX |
| DNS — A 레코드 변경 | ⏳ 대기 | dsp114.com: `175.119.156.230` → `175.119.156.249` 변경 필요 |
| Plesk — 도메인 별칭 추가 | ⏳ 대기 | dsp114.com을 Plesk에 alias로 추가 + SSL |
| dsp114.com 접속 테스트 | ⏳ 대기 | DNS 전파 후 결제/에스크로/회원가입 테스트 |

### 하드코딩 → SITE_URL 변환 완료 파일 (11개)

| # | 파일 | 변경 내용 |
|---|------|----------|
| 1 | `config.env.php` | SITE_URL/SITE_DOMAIN 동적 감지 함수 추가 |
| 2 | `db.php` | 환경 감지에 dsp114.com 추가 |
| 3 | `payment/inicis_config.production.php` | returnUrl/closeUrl에 SITE_URL 사용 |
| 4 | `payment/request.php` | returnUrl에 SITE_URL 사용 |
| 5 | `dashboard/api/email.php` | 이메일 링크에 SITE_URL 사용 |
| 6 | `en/index.php` | 영문 사이트 baseUrl에 SITE_URL 사용 |
| 7 | `includes/quote_request_api.php` | 견적 링크에 SITE_URL 사용 |
| 8 | `includes/shipping_api.php` | 배송 알림 링크에 SITE_URL 사용 |
| 9 | `member/password_reset_simple_fixed.php` | 비밀번호 재설정 링크에 SITE_URL 사용 |
| 10 | `mlangorder_printauto/OrderComplete_universal.php` | 주문완료 URL에 SITE_URL 사용 |
| 11 | `mlangprintauto/shop/send_cart_quotation.php` | 장바구니 견적 링크에 SITE_URL 사용 |

### KG이니시스 (듀얼 도메인 대응)

- MID: `dsp1147479` (사업자번호 귀속, 도메인 무관)
- Sign Key: `cEdnbCtISFZ1QUNpNm5hbG1JY1RlQT09` (두 도메인 공용)
- returnUrl/closeUrl: `SITE_URL` 동적 감지로 자동 대응
- 이니시스 기술지원 확인 완료: 같은 MID + Sign Key로 여러 도메인 결제 가능

### KB에스크로 (도메인별 mHValue)

| 도메인 | mHValue | 등록일 | 적용 상태 |
|--------|---------|--------|----------|
| dsp114.com (주) | `eb30fbb0bc1da7fdcaf800c0bceebbff201111241043905` | 2011.11.24 | ✅ **현재 적용** |
| dsp114.co.kr (보조) | `ef04cec95f1a7298f1f686bfe3159ade` | 2026.02.06 | 주석에 보존 |

- 가맹점 코드(cc): `b034066:b035526` (양쪽 동일)
- 적용 파일: `right.htm` (라인 111), `includes/footer.php` (라인 85)

### 롤백 방법

```bash
# KB에스크로 mHValue를 dsp114.co.kr용으로 되돌리기
git checkout e6554898 -- right.htm includes/footer.php

# 또는 수동으로:
# right.htm, includes/footer.php에서:
#   현재: eb30fbb0bc1da7fdcaf800c0bceebbff201111241043905 (dsp114.com)
#   롤백: ef04cec95f1a7298f1f686bfe3159ade (dsp114.co.kr)
```

### DNS 전환 후 체크리스트

- [ ] dsp114.com 접속 확인 (HTTPS)
- [ ] dsp114.com에서 KB에스크로 인증마크 팝업 정상 확인
- [ ] dsp114.com에서 카드결제 테스트 (소액)
- [ ] dsp114.com에서 회원가입/로그인 테스트
- [ ] dsp114.com에서 이메일 발송 링크 확인
- [ ] dsp114.co.kr에서도 동일 기능 정상 확인

---
마지막 업데이트: 2026-02-26
