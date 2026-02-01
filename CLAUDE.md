# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## 🚨 PRODUCTION SERVER - FTP 웹 루트 구조 (배포 시 필수!)

**⚠️ 서버 마이그레이션 완료:**
- ❌ 구 서버: dsp1830.shop (사용 중단)
- ✅ 현재 운영: dsp114.co.kr

**⚠️ 운영 서버는 FTP 루트 ≠ 웹 루트입니다!**

```
FTP: dsp114.co.kr (dsp1830 / cH*j@yzj093BeTtc)

/ (FTP 루트)
└─ httpdocs/ ← ✅ 실제 웹 루트 (https://dsp114.co.kr/)

🎯 배포 경로:
✅ /httpdocs/payment/inicis_return.php
❌ /payment/inicis_return.php (잘못된 경로!)
```

**curl 업로드 예시:**
```bash
curl -T local_file.php \
  ftp://dsp114.co.kr/httpdocs/payment/file.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

---

## 🏢 Project Identity

**Duson Planning Print System (두손기획인쇄)** - PHP 7.4 기반 인쇄 주문 관리 시스템

### 환경 정보
- **OS**: Linux (WSL2 Ubuntu) / Windows XAMPP
- **Web Server**: Apache 2.4+
- **PHP**: 7.4+
- **Database**: MySQL 5.7+ (utf8mb4)
- **Local Document Root**: `/var/www/html` (개발 환경)
- **Production Web Root**: `/httpdocs/` (FTP 기준)
- **Domains**: localhost (dev) / dsp114.co.kr (prod)

### 긴급 접속 정보
```
관리자: duson1830 / du1830
DB: dsp1830 / ds701018
FTP: dsp1830 / ds701018
WSL sudo: 3305
GitHub: songyoungsoo / yeongsu32@gmail.com
```

---

## 🔴 CRITICAL RULES (절대 규칙)

### 1. bind_param 검증 (3번 검증 필수)
```php
// ❌ NEVER: 눈으로 대충 세기
mysqli_stmt_bind_param($stmt, "issss...", ...);

// ✅ ALWAYS: 3번 검증
$placeholder_count = substr_count($query, '?');  // 1
$type_count = strlen($type_string);             // 2
$var_count = 7; // 손으로 세기                   // 3

if ($placeholder_count === $type_count && $type_count === $var_count) {
    mysqli_stmt_bind_param($stmt, $type_string, ...);
}
```

### 2. Database 규칙
- **테이블명**: 항상 소문자 (`mlangprintauto_namecard`)
- **연결 변수**: `$db` (legacy는 `$conn = $db;` alias)
- **Character Set**: utf8mb4

### 3. quantity_display 검증 규칙 (필수)
```php
// ❌ NEVER: quantity_display를 단위 체크 없이 그대로 사용
$line2 = implode(' / ', [$spec_sides, $item['quantity_display'], $spec_design]);

// ✅ ALWAYS: 단위가 없으면 formatQuantity() 호출
$quantity_display = $item['quantity_display'] ?? '';

// 단위 체크: 매, 연, 부, 권, 개, 장
if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $quantity_display = $this->formatQuantity($item);
}

$line2 = implode(' / ', [$spec_sides, $quantity_display, $spec_design]);
```

**이유**:
- DB에 `quantity_display = "1"`처럼 단위 없이 저장될 수 있음
- `formatQuantity()`는 `MY_amount=1000` → "1,000매" 자동 변환
- 천 단위 변환 로직 포함 (봉투/명함: `MY_amount < 10`이면 ×1000)

**적용 위치**:
- `ProductSpecFormatter::formatStandardized()` (lines 71-83)
- `ProductSpecFormatter::buildLine2()` (lines 323-331)
- 모든 수량 표시 로직

### 4. 파일명 규칙
- **All lowercase**: `cateadmin_title.php` (NOT `CateAdmin_title.php`)
- **Includes**: 소문자 경로만 사용 (Linux case-sensitive)
- **No symlinks**: 실제 디렉토리만 사용

### 5. CSS !important 사용 금지 ⚠️
```css
/* ❌ NEVER: !important 사용 금지 - 임시방편 코드 */
.product-nav {
    display: grid !important;  /* 절대 금지 */
}

/* ✅ ALWAYS: 명시도(specificity) 계층으로 해결 */
/* 레벨 1: 기본 스타일 (클래스 1개) */
.product-nav { display: flex; }

/* 레벨 2: 상태/컨텍스트 (클래스 2개) */
.mobile-view .product-nav { display: grid; }

/* 레벨 3: 구체적 선택자 (클래스 3개 또는 부모 포함) */
body.cart-page .mobile-view .product-nav { display: grid; }
```

**🚨 CSS 문제 발생 시 필수 행동 (작업 진행 전 반드시 수행)**:
```
1. "왜 안 되는지" 먼저 답하기
   - 개발자도구로 어떤 규칙이 덮어쓰는지 확인
   - 답 못 하면 → 작업 진행 금지

2. 컨테이너부터 점검 (내용물 정렬 전에)
   - margin, padding, width 확인
   - 부모 요소의 display, position 확인

3. !important 쓰기 전 자문
   - "근본 원인을 찾았는가?" → No면 금지
   - "명시도로 해결 가능한가?" → Yes면 그렇게 해결
```

**!important 사용 시 체크리스트** (위 행동 수행 후에만):
1. ⚠️ **정말 필요한가?** - 명시도로 해결 가능한지 먼저 확인
2. ⚠️ **임시 코드인가?** - 임시라면 TODO 주석 필수
3. ⚠️ **부작용은?** - 다른 페이지에 영향 없는지 확인
4. ⚠️ **문서화했나?** - 사용 사유를 주석으로 기록

**참조**:
- `css/common-styles.css` 상단 주석 "명시도 우선순위 설계"
- `CLAUDE_DOCS/CSS_DEBUG_LESSONS.md` - CSS 디버깅 교훈록

### 6. 임기응변 금지 - 전체 설계 우선 🎯
```
❌ NEVER: 임기응변식 코딩
- 당장 동작하게 !important 추가
- 한 곳만 고치고 다른 곳 영향 무시
- 빠른 수정 위해 인라인 스타일 남발
- 기존 구조 무시하고 새 패턴 도입

✅ ALWAYS: 전체 설계 후 구현
1. 현재 시스템 구조 파악 (파일, CSS, JS 연관관계)
2. 영향 범위 분석 (이 변경이 어디에 영향을 주는가?)
3. 기존 패턴 확인 (프로젝트에서 이미 사용 중인 방식은?)
4. 확장성 고려 (나중에 비슷한 요청이 오면 어떻게 되나?)
5. 계획 수립 후 구현
```

**핵심 원칙**:
- **기본에 충실 → 확장성 확보**: 올바른 기초 위에서만 확장 가능
- **임시 코드 = 기술 부채**: 나중에 반드시 문제 발생
- **전체 그림 먼저**: 부분 최적화보다 전체 일관성 우선

### 7. 환경 자동 감지
```php
// db.php가 자동 감지
- localhost → $admin_url = "http://localhost"
- dsp1830.shop → $admin_url = "http://dsp1830.shop"
- dsp1830.shop → $admin_url = "http://dsp1830.shop"
```

---

## 📦 전사 표준 품목 매핑 사전 (9개 제품)

> **[공표] 최상위 법전**: 아래 폴더명은 절대 변경 금지. AI가 임의로 명칭 변경 불가.

| # | 품목명 | 폴더명 (강제) | ❌ 금지 명칭 | 작명 유래 |
|---|--------|--------------|-------------|----------|
| 1 | 전단지 | `inserted` | leaflet | 신문 삽입 홍보물 |
| 2 | 스티커 | `sticker_new` | sticker | 구형 폴더와 혼동 방지 |
| 3 | 자석스티커 | `msticker` | - | 독립 전용 경로 |
| 4 | 명함 | `namecard` | - | 표준 명칭 |
| 5 | 봉투 | `envelope` | - | 표준 명칭 |
| 6 | 포스터 | `littleprint` | poster | 대량 대비 소량 인쇄 |
| 7 | 상품권 | `merchandisebond` | giftcard | 고유 작명 |
| 8 | 카다록 | `cadarok` | catalog | 발음 기반 고유 작명 |
| 9 | NCR양식지 | `ncrflambeau` | form, ncr | 고유 작명 |

**시각물 규칙**: UI/디자인에서는 '리플렛', '포스터' 사용 가능. 단, **코드/경로에서는 위 폴더명 100% 일치 필수**

---

## 🚀 빠른 시작

### 서버 시작
```bash
sudo service apache2 start
sudo service mysql start
http://localhost/
```

### Git 워크플로우 (자동 스테이징)
```bash
# Claude가 작업 완료 시 자동 수행
git add .

# 사용자 확인 후
git status
git commit -m "메시지"
git push origin main
```

### FTP 배포 (프로덕션)
```bash
curl -T "file.php" -u "dsp1830:ds701018" \
  "ftp://dsp1830.shop/path/file.php"
```

### 핵심 파일 위치
```
/var/www/html/
├── db.php                              # DB 연결 & 환경 자동 감지
├── config.env.php                      # 환경 설정
├── includes/
│   ├── auth.php                        # 인증 (8시간 세션)
│   ├── StandardUploadHandler.php      # 파일 업로드 표준
│   └── ImagePathResolver.php          # 파일 경로 해석
├── mlangprintauto/[product]/
│   ├── index.php                       # 제품 페이지
│   ├── add_to_basket.php              # 장바구니 API
│   └── calculate_price_ajax.php       # 가격 API
└── mlangorder_printauto/
    ├── ProcessOrder_unified.php        # 주문 처리
    └── OrderComplete_universal.php     # 주문 완료
```

---

## 🎯 SSOT (Single Source of Truth) 체계

### 수량 포맷팅 - 유일한 진입점
```php
// ✅ 모든 수량 출력은 반드시 이 함수를 거침
QuantityFormatter::format($value, $unitCode, $sheets);
// 예: format(0.5, 'R', 2000) → "0.5연 (2,000매)"
```

### 단위 코드 체계
| 코드 | 단위 | 제품 |
|------|------|------|
| **R** | 연 | inserted, leaflet (전단지/리플렛) |
| **S** | 매 | sticker_new, namecard, envelope, littleprint, msticker, merchandisebond |
| **B** | 부 | cadarok (카다록) |
| **V** | 권 | ncrflambeau (NCR양식지) |

### 데이터 구조 (신규 스키마)
```
qty_value: DECIMAL(10,2) - 수량 값 (0.5, 1000 등)
qty_unit_code: CHAR(1) - 단위 코드 (R/S/B/V)
qty_sheets: INT - 매수 (전단지용, DB 조회만)
```

### 핵심 SSOT 파일
```
/var/www/html/
├── includes/
│   ├── QuantityFormatter.php      ← 수량/단위 SSOT
│   └── ProductSpecFormatter.php   ← 제품 사양 포맷터
├── mlangprintauto/quote/includes/
│   ├── QuoteManager.php           ← 견적서 데이터 관리
│   ├── QuoteTableRenderer.php     ← 견적서 테이블 렌더링 SSOT
│   └── ProductSpecFormatter.php   ← 견적서 사양 포맷터
└── lib/
    └── core_print_logic.php       ← 중앙 로직 파사드
```

### 절대 금지 사항
```php
// ❌ 매수 계산 금지 (반드시 DB 조회)
$sheets = $reams * 4000;

// ❌ 직접 포맷팅 금지
$display = number_format($amount) . '매';

// ✅ 필수: SSOT 함수 사용
$display = QuantityFormatter::format($value, $unitCode, $sheets);
$sheets = PrintCore::lookupInsertedSheets($reams);  // DB 조회만
```

### 8. getUnitCode vs getProductUnitCode 구분 (필수) 🔴
```php
// ❌ NEVER: product_type으로 getUnitCode 호출 (버그 발생!)
$unitCode = QuantityFormatter::getUnitCode($productType);  // 'sticker' → 'E' (오류)

// ✅ ALWAYS: product_type에는 getProductUnitCode 사용
$unitCode = QuantityFormatter::getProductUnitCode($productType);  // 'sticker' → 'S' (정확)
```

**메서드 구분**:
| 메서드 | 입력 | 출력 | 용도 |
|--------|------|------|------|
| `getUnitCode($name)` | 한글 단위명 ("매", "연") | 코드 (S, R) | 한글→코드 변환 |
| `getProductUnitCode($productType)` | 품목 타입 ("sticker", "inserted") | 코드 (S, R) | 품목→단위 매핑 |

**발생한 버그 (2026-01-17)**:
- `QuoteManager.php`에서 `getUnitCode('msticker')` 호출
- 'msticker'가 UNIT_CODES에 없어 기본값 'E' 반환
- 스티커가 "개" 단위로 잘못 표시됨

### 9. 레거시 스티커 감지 패턴 (필수) 🟡
```php
// product_type이 비어있을 때 스티커 감지 방법:

// 방법 1: jong/garo/sero 필드로 감지 (QuoteManager에서)
if (empty($productType) && !empty($tempItem['jong']) && !empty($tempItem['garo'])) {
    $productType = 'sticker';
}

// 방법 2: product_name으로 감지 (QuoteTableRenderer에서)
if (empty($productType)) {
    $productName = $item['product_name'] ?? '';
    if (stripos($productName, '스티커') !== false) {
        $productType = 'sticker';
    }
}
```

**이유**: 레거시 데이터에서 `product_type`이 비어있는 경우가 많음. 스티커는 "개"가 아닌 "매" 단위 사용

---

## 📚 문서 참조

| 주제 | 파일 |
|------|------|
| 마스터 명세서 | `CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md` |
| 데이터 흐름 | `CLAUDE_DOCS/DATA_LINEAGE.md` |
| 변경 이력 | `.claude/changelog/CHANGELOG.md` |
| 스킬 가이드 | `~/.claude/skills/duson-print-system/SKILL.md` |
| 레거시 아카이브 | `CLAUDE_DOCS/00_Legacy_Archive/` |

---

## ⚠️ Common Pitfalls (자주 하는 실수)

1. ❌ bind_param 개수 불일치 → 주문자 이름 '0' 저장
2. ❌ 대문자 테이블명 사용 → SELECT 실패
3. ❌ 대문자 include 경로 → Linux에서 파일 못 찾음
4. ❌ number_format(0.5) → "1" 반올림 오류
5. ❌ `littleprint`를 `poster`로 변경 → 시스템 전체 오류
6. ❌ colgroup 개수 ≠ 실제 컬럼 개수 → 오른쪽 빈 공란 발생
7. ❌ `getUnitCode($productType)` 호출 → 스티커 "개" 단위 버그 (2026-01-17)
8. ❌ product_type 없이 단위 결정 → 레거시 데이터 감지 로직 필수
9. ❌ unit_price=0일 때 그대로 표시 → supply_price/quantity로 계산 필요

---

## 🏗️ 견적서 시스템 (Quote System)

### QuoteTableRenderer SSOT 원칙
```
"데이터는 하나로, 출력은 표준 렌더러로"
견적서/주문서/PDF/이메일 모두 동일한 포맷 출력
```

### 표준 7개 컬럼
| NO | 품목 | 규격/옵션 | 수량 | 단위 | 단가 | 공급가액 |
|----|------|----------|------|------|------|---------|

### 핵심 메서드 (QuoteTableRenderer)
```php
// 수량 셀 포맷팅 (매수 자동 조회)
$renderer->formatQuantityCell($item);  // "1,000" 또는 "0.5<br>(2,000매)"

// 단위 셀 포맷팅 (SSOT: product_type 기반)
$renderer->formatUnitCell($item);  // "매", "연", "권"

// 단가 셀 포맷팅 (0이면 자동 계산)
$renderer->formatUnitPriceCell($item);  // supply_price / quantity

// 공급가액 셀 포맷팅
$renderer->formatSupplyPriceCell($item);  // number_format 적용
```

### 단위 결정 우선순위 (formatUnitCell SSOT)
```
1. product_type → QuantityFormatter::getProductUnitCode() (최우선)
2. 레거시 스티커 감지: product_name에 '스티커' 포함
3. qty_unit → QuantityFormatter::getUnitName()
4. 최후 fallback: 레거시 unit 필드 또는 '개'
```

---

---

## 🔜 보류 작업: member → users 완전 마이그레이션 (예정: 2026년 2월 중순)

### 배경
- 2026-02-02: 회원가입/관리자 회원목록은 `users` 테이블로 전환 완료
- 하지만 **50+개 PHP 파일**이 아직 `member` 테이블을 직접 참조 중
- `register_process.php`가 users + member 양쪽에 이중 INSERT 중 (호환용)
- `/system/migration/` 도구를 활용하여 체계적으로 전환 예정

### 현재 상태

| 영역 | 상태 | 테이블 |
|------|------|--------|
| 회원가입 (`register_process.php`) | ✅ users 저장 + ⚠️ member 이중 저장 | users (주) + member (호환) |
| 로그인 (`member/login_unified.php`) | ✅ users 우선 조회 + member fallback (자동 마이그레이션) | users (주) + member (fallback) |
| 로그인 (`session/loginProc.php`) | ✅ users 우선 조회 + member fallback (자동 마이그레이션) | users (주) + member (fallback) |
| 관리자 회원목록 (`admin/member/index.php`) | ✅ users 전환 완료 | users |
| 관리자 회원상세 (`admin/member/admin.php`) | ✅ users 전환 완료 | users |
| 관리자 인증 (`admin/config.php`) | ⚠️ 현재 주석 처리됨, member 참조 코드 존재 | member (비활성) |
| 세션 헤더 (`session/index.php`) | ✅ users 전환 완료 | users |
| 내 정보 (`session/my_info.php`) | ✅ users 전환 완료 | users |
| 프로필 수정 (`session/edit_profile.php`) | ✅ users 전환 + member 이중 쓰기 | users (주) + member (호환) |
| 비밀번호 변경 (`session/change_password.php`) | ✅ users 전환 + bcrypt + member 이중 쓰기 | users (주) + member (호환) |
| 주문 내역 (`session/orderhistory.php`) | ✅ users 전환 완료 | users |
| 주문 상세 (`session/order_view_my.php`) | ✅ users 전환 완료 | users |
| 주문 페이지 (`OnlineOrder_unified.php`) | ⚠️ member 참조 | member |
| 비밀번호 초기화 | ⚠️ member 참조 | member |

### member 참조 파일 목록 (활성 코드만, backup/scripts 제외)

**핵심 (우선순위 1):**
- `member/login_unified.php` — 로그인
- `member/change_password.php` — 비밀번호 변경
- `member/password_reset.php` — 비밀번호 초기화
- `member/password_reset_request.php` — 초기화 요청
- `mlangorder_printauto/OnlineOrder_unified.php` — 주문 페이지

**세션/마이페이지 (우선순위 2):**
- `session/loginProc.php` — 로그인 처리
- `session/index.php` — 세션 관리
- `session/my_info.php` — 내 정보
- `session/edit_profile.php` — 프로필 수정
- `session/change_password.php` — 비밀번호 변경
- `session/orderhistory.php` — 주문 내역
- `session/order_view_my.php` — 주문 조회
- `mypage/auth_required.php` — 마이페이지 인증

**관리자 (우선순위 3):**
- `admin/config.php` — 관리자 인증 (현재 주석 처리)
- `admin/MlangPoll/admin.php` — 설문 관리

**기타:**
- `member/member_fild.php`, `member_fild_member.php`, `member_fild_id.php` — 회원 조회
- `lib/func.php` — 공통 함수
- `mlangorder_printauto/session/` — 주문 세션
- `mlangorder_printauto/OrderFormOrderOne.php`, `WindowSian.php`
- `sub/pw_check.php` — 비밀번호 확인
- `shop/search_company.php` — 업체 검색
- `bbs/` — 게시판

### 시스템 도구 현황 (인스톨러/백업/복구)

| 시스템 | 파일 | 현재 상태 | 마이그레이션 후 할 일 |
|--------|------|-----------|---------------------|
| InstallerEngine | `system/install/InstallerEngine.php` | ✅ users에 admin INSERT | 변경 불필요 |
| schema.sql | `system/install/sql/schema.sql` | ⚠️ member + users 둘 다 CREATE | member CREATE TABLE 제거 |
| seed.sql | `system/install/sql/seed.sql` | ✅ member/users 데이터 없음 | 변경 불필요 |
| BackupManager | `system/backup/BackupManager.php` | ✅ DB 전체 덤프/복구 | 변경 불필요 (DB 통째 처리) |
| restore.php | `system/backup/restore.php` | ✅ SQL 파일 그대로 실행 | 변경 불필요 |

**schema.sql 수정 시점**: 7단계(member 테이블 폐기) 시점에 member CREATE TABLE 제거

### 작업 순서 (진행 상황)

1. ~~**register_process.php**: member INSERT 코드 제거 (이중 저장 중단)~~ → 이중 저장 유지 중 (호환)
2. ~~**login_unified.php + session/loginProc.php**: `SELECT FROM member` → `SELECT FROM users` 전환~~ ✅ 완료 (2026-02-02)
3. ~~**session/ 디렉토리**: 전체 users 전환~~ ✅ 완료 (2026-02-02) - 7개 파일 전환
4. **OnlineOrder_unified.php**: 주문 페이지 회원 조회 전환
5. **admin/config.php**: 관리자 인증 활성화 + users 전환
6. **나머지 파일들**: 순차 전환 (member_fild.php, lib/func.php, bbs/ 등)
7. **member 테이블 폐기**: 백업 → schema.sql에서 member 제거 → DROP TABLE member

### 컬럼 매핑 참조 (member → users)

| member | users | 비고 |
|--------|-------|------|
| no | id | PK (auto_increment) |
| id | username | UNIQUE |
| pass | password | bcrypt ($2y$10$...) |
| name | name | |
| phone1-2-3 | phone | "010-1234-5678" 통합 형식 |
| hendphone1-2-3 | phone | 일반전화 없으면 핸드폰 사용 |
| email | email | |
| sample6_postcode | postcode | |
| sample6_address | address | |
| sample6_detailAddress | detail_address | |
| sample6_extraAddress | extra_address | |
| po1 | business_number | 사업자등록번호 |
| po2 | business_name | 상호 |
| po3 | business_owner | 대표자 |
| po4 | business_type | 업태 |
| po5 | business_item | 종목 |
| po6 | business_address | 사업장주소 |
| po7 | tax_invoice_email | 세금계산서 이메일 |
| date | created_at | |
| Logincount | login_count | |
| EndLogin | last_login | |
| level | level | 기본값 '5' |
| money | (제거됨) | 포인트 기능 폐기 |

### 주의사항
- 비밀번호: users는 bcrypt 전용, member는 평문+bcrypt 혼재 → 로그인 시 양쪽 지원 필요
- 전화번호: member는 phone1/2/3 분리, users는 통합 → 전환 시 통합 로직 필요
- `original_member_no` 컬럼: 마이그레이션된 회원의 원래 member.no 추적용

### 완료된 정리 작업 (2026-02-02)
- ✅ 스팸 계정 11건 삭제 (로컬+운영 member 테이블)
  - `* * *` 스팸 8건 (pazapz@mailbox.in.ua)
  - XSS 공격 1건 (박희선<sCRiPt...>)
  - 중복 계정 2건 (88952634)
- ✅ 회원가입 폼 autocomplete 방지 (form.php → 운영 배포 완료)

---

*Core Version - Last Updated: 2026-02-02*
*Environment: WSL2 Ubuntu + Windows XAMPP*
*SSOT Docs: CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md*
