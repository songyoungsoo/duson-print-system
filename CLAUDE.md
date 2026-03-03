# CLAUDE.md

한글을 사용해주세요.

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
- **Web Server**: Apache 2.4+ (로컬) / **Plesk: nginx + Apache** (프로덕션)
- **PHP**: 7.4+ (로컬) / 8.2 (프로덕션)
- **Database**: MySQL 5.7+ (utf8mb4)
- **Local Document Root**: `/var/www/html` (개발 환경)
- **Production Web Root**: `/httpdocs/` (FTP 기준, Plesk 표준 경로)
- **Domains**: localhost (dev) / dsp114.co.kr (prod)

### 🚨 프로덕션 서버 = Plesk (nginx + Apache) — .htaccess 금지!

프로덕션은 **Plesk 호스팅 패널** 환경입니다:
- **nginx**가 프록시로 앞단에서 정적 파일(이미지, CSS, JS) 직접 서빙
- **Apache**가 뒷단에서 PHP만 처리
- **FTP**: ProFTPD (SSH 접근 불가)

```
⚠️ .htaccess 절대 사용 금지!

이유:
1. nginx는 .htaccess를 완전히 무시함
2. php_flag, php_value 등 Apache 모듈 지시자 → 500 에러 유발
3. 보안 효과 = 0 (nginx가 정적 파일 요청 시 Apache를 거치지 않음)

실제 사고 (2026-02-10):
- upload/.htaccess의 "php_flag engine off" → 이미지 500 에러 → 교정관리 이미지 깨짐
- ImgFolder/.htaccess 동일 문제

보안 대안:
- 업로드 시 확장자 제한 (코드 레벨) ← 이미 적용됨
- 파일명 난수화 ← 이미 적용됨
- 경로 검증 (realpath + strpos) ← 이미 적용됨
```

### 접속 정보

| 구분 | 접속 정보 |
|------|----------|
| 관리자 (로컬/프로덕션) | admin / ds701018 |
| 로컬 DB | dsp1830 / ds701018 |
| 프로덕션 DB (dsp114.co.kr) | dsp1830 / t3zn?5R56 |
| FTP (dsp114.co.kr) | dsp1830 / cH*j@yzj093BeTtc (웹루트: /httpdocs/) |
| FTP (새 서버 dsp1830.ipdisk.co.kr) | admin / 1830 (웹루트: /HDD2/share/) |
| FTP (구 서버 dsp114.com) | duson1830 / du1830 (웹루트: /www/) |
| 구 서버 DB (dsp114.com) | duson1830 / du1830 |
| 마이그레이션 페이지 | /system/migration/index.php 비번: duson2026!migration |
| WSL sudo | 3305 |
| GitHub | songyoungsoo / yeongsu32@gmail.com |

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

**🎯 갤러리 관리: 드래그 앤 드롭으로 이미지 교체**
- dashboard/gallery/에서 샘플/안전 갤러리 이미지 위에 **마우스로 파일을 드래그하세요**
- 드래그하면 자동으로 기존 이미지와 교체됩니다
- "📁 파일을 드래그하세요" 오버레이 표시 안됨 (드래그 오버레이 표시됨)
- 파일명 유지하며 자동으로 새 파일명 생성 (타임스탬프 방지)
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

## 📚 문서 참조

| 주제 | 파일 |
|------|------|
| 마스터 명세서 | `CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md` |
| 데이터 흐름 | `CLAUDE_DOCS/DATA_LINEAGE.md` |
| 변경 이력 | `.claude/changelog/CHANGELOG.md` |
| 스킬 가이드 | `~/.claude/skills/duson-print-system/SKILL.md` |
| 레거시 아카이브 | `CLAUDE_DOCS/00_Legacy_Archive/` |

---

## 🛡️ 검증 스킬 (Verify Skills)

코드 수정 후 자동 검증을 위한 스킬 목록입니다. `/verify-implementation`으로 전체 실행 가능.

| 스킬 | 설명 | 트리거 |
|------|------|--------|
| `verify-bind-param` | bind_param 3요소 카운트 불일치 탐지 | PHP 파일 수정 후 |
| `verify-folder-names` | 제품 폴더명 혼동 탐지 (sticker↔sticker_new 등) | PHP 파일 수정 후 |
| `verify-sticker-logic` | 스티커 DB lookup 대신 수학공식 검증 | 스티커 관련 PHP 수정 후 |
| `verify-url-safety` | URL 쉼표 사용 탐지 (Plesk 500 에러 방지) | PHP 파일 수정 후 |
| `verify-css-important` | CSS !important 사용 탐지 | CSS/인라인 스타일 수정 후 |
| `verify-product-units` | 제품별 수량 단위 규정 검증 | 수량 표시 관련 PHP 수정 후 |
| `verify-proof-viewer` | 교정/원고 파일 관리 + 이미지 뷰어 디자인 통일 | 교정/원고 관련 PHP 수정 후 |

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

## 🎨 프리미엄 옵션 DB 시스템 (2026-02-13)

6개 품목(명함/상품권/전단지/포스터/카다록/봉투)의 후가공 옵션 가격을 DB로 관리.

### 아키텍처
```
[고객 페이지 JS] → fetch(/api/premium_options.php) → DB 가격 로드
                    ↓ 실패 시
                   하드코딩 fallback (기존 값 그대로 사용)
```

### 핵심 파일
| 파일 | 역할 |
|------|------|
| `dashboard/api/premium_options.php` | 관리자 CRUD API + 주문 재계산 |
| `api/premium_options.php` | 고객용 가격 API (캐시 5분, 인증 불필요) |
| `dashboard/premium-options/index.php` | 관리자 대시보드 UI |
| `js/premium-options-loader.js` | 공통 DB 로더 |

### DB 테이블
- `premium_options` — 옵션 마스터 (product_type, option_name, sort_order, is_active)
- `premium_option_variants` — 옵션 상세 (variant_name, pricing_config JSON)

### 3가지 가격 패턴
| 패턴 | 품목 | pricing_config.type |
|------|------|-------------------|
| A | 명함, 상품권 | `base_perunit` |
| B | 전단지, 포스터, 카다록 | `multiplier` |
| C | 봉투 | `tiered` |

### 제외 품목 (프리미엄 옵션 없음)
- 스티커(sticker_new), 자석스티커(msticker), NCR양식지(ncrflambeau)

### 견적서 시스템과의 관계
- **완전 분리**: `PriceCalculationService.php`, `option_prices.php` 변경 없음
- 프리미엄 옵션은 기본 가격 위에 추가되는 후가공 비용

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

## ✅ member → users 마이그레이션 완료 (2026-02-02)

**상태: 6단계 완료 (7단계 member DROP은 의도적 보류)**

모든 활성 PHP 코드가 `users` 테이블을 primary로 사용하도록 전환 완료.
`member` 테이블은 backward compatibility를 위해 유지 (이중 쓰기).

### 마이그레이션 결과

| 단계 | 범위 | 상태 |
|------|------|------|
| 1단계 | 회원가입/관리자 (`register_process`, `admin/member/`) | ✅ 완료 |
| 2단계 | 로그인 (`login_unified`, `session/loginProc`) | ✅ 완료 |
| 3단계 | session/ 디렉토리 (7개 파일) | ✅ 완료 |
| 4단계 | 주문 시스템 (`OnlineOrder`, `OrderFormOrderOne`, `WindowSian`) | ✅ 완료 |
| 5단계 | 관리자 (`admin/config`, `AdminConfig`, `MlangPoll/admin`) | ✅ 완료 |
| 6단계 | 나머지 전체 (BBS 23개 skin, member/, lib/, shop/, sub/ 등) | ✅ 완료 |
| 7단계 | member 테이블 DROP | ⏸️ 의도적 보류 |

### 의도적으로 member 참조를 유지하는 파일

| 파일 | 이유 |
|------|------|
| `member/register_process.php` | users INSERT + member 이중 INSERT |
| `member/change_password.php` | users UPDATE + member sync UPDATE |
| `member/password_reset.php` | users UPDATE + member sync UPDATE |
| `admin/AdminConfig.php` | users UPDATE + member sync UPDATE |
| `bbs/PointChick.php` | member.money (포인트 시스템, users에 컬럼 없음) |

### Admin 패턴
```php
// 이전: SELECT * FROM member WHERE no='1'
// 현재: SELECT username AS id, password AS pass FROM users WHERE is_admin = 1 LIMIT 1
```

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

### 시스템 도구 현황

| 시스템 | 파일 | 상태 |
|--------|------|------|
| InstallerEngine | `system/install/InstallerEngine.php` | ✅ users에 admin INSERT |
| schema.sql | `system/install/sql/schema.sql` | ⚠️ member + users 둘 다 CREATE (7단계에서 제거) |
| BackupManager | `system/backup/BackupManager.php` | ✅ DB 전체 덤프/복구 |

### 완료된 정리 작업 (2026-02-02)
- ✅ 스팸 계정 11건 삭제 (로컬+운영 member 테이블)
- ✅ 회원가입 폼 autocomplete 방지 (form.php → 운영 배포 완료)
- ✅ 회원가입 페이지 제목: '두손기획인쇄 회원가입'

---

### 📊 Frontend Implementation Notes: Dashboard Number Animation

대시보드 요약 카드(오늘 주문, 이번달 주문, 미처리 주문, 미답변 문의 등)에 숫자가 0부터 최종 값까지 부드럽게 증가하는 애니메이션을 적용했습니다.

*   **구현 기법**: 커스텀 JavaScript 함수 `animateNumber`를 사용하여 구현되었습니다.
*   **애니메이션 원리**: 브라우저에 최적화된 부드러운 애니메이션을 위해 `window.requestAnimationFrame` API를 활용합니다.
*   **함수 `animateNumber` 로직**:
    *   대상 HTML 요소의 ID, 최종 숫자 값, 선택적 애니메이션 지속 시간, 그리고 숫자 뒤에 붙는 접미사('건', '원' 등)를 인자로 받습니다.
    *   애니메이션 시작 시점을 기록하고, 경과 시간과 총 지속 시간을 기준으로 애니메이션 진행률(`progress`)을 계산합니다.
    *   `progress`에 따라 현재 표시될 숫자 값을 계산하고, `toLocaleString()`으로 천 단위 구분자를 적용한 후 접미사와 함께 대상 요소의 `innerHTML`을 업데이트합니다.
    *   `progress`가 100%에 도달할 때까지 `requestAnimationFrame`을 재귀적으로 호출하여 애니메이션을 지속합니다.
    *   애니메이션 완료 후에는 최종 값이 정확하게 표시되도록 보장합니다.
*   **통합**: `DOMContentLoaded` 이벤트 리스너 내에서 `animateNumber` 함수를 호출하여 각 대상 요소에 애니메이션을 적용했습니다. 최종 숫자 값은 PHP 변수에서 가져와 JavaScript로 전달됩니다.
*   **적용 파일**: `/var/www/html/dashboard/index.php`
    *   애니메이션 대상이 되는 `div` 요소에 고유 ID를 추가 (`id="today-order-count"`, `id="pending-order-count"` 등).
    *   기존 `<script>` 블록에 `animateNumber` 함수 정의 및 호출 로직 추가.

---

*Core Version - Last Updated: 2026-02-02*
*Environment: WSL2 Ubuntu + Windows XAMPP*
*SSOT Docs: CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md*