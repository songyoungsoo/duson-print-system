---
name: verify-product-units
description: 제품별 수량 단위 규정을 검증합니다. QuantityFormatter(SSOT)를 우회하여 단위를 하드코딩하는 코드, 제품-단위 불일치(전단지=연, 스티커=매, 카다록=부, NCR=권+매수), 전단지 연→매수 변환 누락 등을 탐지합니다. 수량 표시 관련 PHP 코드 수정 후 사용.
---

# 제품별 수량 단위 검증

## Purpose

QuantityFormatter.php가 모든 수량 단위의 SSOT(단일 진실 공급원)입니다.
이 스킬은 다음을 검증합니다:

1. **SSOT 우회 탐지** — QuantityFormatter/formatPrintQuantity를 쓰지 않고 직접 단위를 붙이는 코드
2. **제품-단위 불일치** — 전단지를 "매"로, 포스터를 "매"로, NCR을 "매"로 표시하는 등의 오류
3. **전단지 연→매수 변환 누락** — "0.5연"만 표시하고 "(2,000매)" 보조 표시가 없는 경우
4. **NCR 매수 표시 누락** — "10권"만 표시하고 "(1,000매)" 보조 표시가 없는 경우

### 제품별 올바른 단위 규정표

| 제품 | 폴더명 | 단위코드 | 올바른 표시 | ❌ 잘못된 표시 |
|------|--------|---------|------------|--------------|
| 전단지 | inserted | R(연) | "0.5연 (2,000매)" | "2000매", "0.5연" (매수 누락) |
| 스티커 | sticker_new | S(매) | "1,000매" | "1,000개" |
| 자석스티커 | msticker | S(매) | "500매" | "500개" |
| 명함 | namecard | S(매) | "200매" | "200장", "200개" |
| 봉투 | envelope | S(매) | "500매" | "500개", "500장" |
| 포스터 | littleprint | P(장) | "100장" | "100매" |
| 상품권 | merchandisebond | S(매) | "500매" | "500장" |
| 카다록 | cadarok | B(부) | "200부" | "200매", "200권" |
| NCR양식지 | ncrflambeau | V(권) | "10권 (1,000매)" | "10권" (매수 누락), "1000매" |

## When to Run

- 수량 표시 관련 PHP 코드를 수정한 후
- 새로운 제품 페이지나 주문 페이지를 작성한 후
- 견적서(quote) 관련 코드를 수정한 후
- 장바구니, 주문완료, 관리자 페이지에서 수량 표시를 변경한 후
- DataAdapter, ProductSpecFormatter 등 데이터 변환 코드를 수정한 후

## Related Files

| File | Purpose |
|------|---------|
| `includes/QuantityFormatter.php` | **SSOT** — 모든 수량/단위 포맷팅의 유일한 소스 |
| `includes/ProductSpecFormatter.php` | 제품 사양 포맷터 (QuantityFormatter 사용) |
| `includes/DataAdapter.php` | 레거시 데이터 변환 (⚠️ 하드코딩된 단위 다수) |
| `includes/SpecDisplayService.php` | 스펙 표시 서비스 |
| `lib/core_print_logic.php` | 중앙 로직 파사드 |
| `mlangorder_printauto/OrderFormOrderTree.php` | 관리자 주문 트리 (QuantityFormatter 사용) |
| `mlangprintauto/shop/send_cart_quotation.php` | 장바구니 견적 (⚠️ 하드코딩된 단위) |
| `admin/mlangprintauto/quote/includes/adapters/*.php` | 견적서 어댑터 9종 (⚠️ 각각 직접 단위 붙임) |
| `v2/src/Services/Product/PriceCalculator.php` | 가격 계산기 (⚠️ '매' 하드코딩) |

## Workflow

### Step 1: 새 코드에서 하드코딩된 단위 문자열 탐지

**도구:** Grep
**대상:** 최근 변경/신규 PHP 파일

```bash
grep -rn "\. *['\"]매['\"]" --include="*.php" .
grep -rn "\. *['\"]연['\"]" --include="*.php" .
grep -rn "\. *['\"]부['\"]" --include="*.php" .
grep -rn "\. *['\"]권['\"]" --include="*.php" .
grep -rn "\. *['\"]장['\"]" --include="*.php" .
grep -rn "\. *['\"]개['\"]" --include="*.php" .
```

**PASS 기준:** 새로 작성/수정된 코드에서 하드코딩된 단위가 없는 경우
**FAIL 기준:** 새 코드에서 `number_format($x) . '매'` 같은 패턴 사용

**위반 시 수정:**
```php
// ❌ 위반: 직접 단위 붙이기
$display = number_format($amount) . '매';

// ✅ 수정: QuantityFormatter 사용
require_once __DIR__ . '/includes/QuantityFormatter.php';
$display = QuantityFormatter::format($amount, 'S');
// 또는 래퍼 함수:
$display = formatPrintQuantity($amount, 'S');
```

### Step 2: 전단지(inserted)가 "매" 단위로 표시되는 코드 탐지

**도구:** Grep
**대상:** inserted/전단지 관련 PHP 파일

```bash
grep -rn "inserted\|전단지\|leaflet" --include="*.php" -l . | xargs grep -n "'\''매'\''" 2>/dev/null
```

더 정밀하게:
```bash
grep -rn "inserted.*매\|전단지.*매" --include="*.php" .
```

**PASS 기준:** 전단지 수량이 "연" 단위로 표시되는 경우 (보조 매수 포함)
**FAIL 기준:** 전단지 수량이 "매"로만 표시되는 경우

**위반 시 수정:**
```php
// ❌ 위반: 전단지를 매 단위로 표시
$display = number_format($sheets) . '매';  // "4,000매"

// ✅ 수정: 연 단위 + 매수 보조 표시
$display = QuantityFormatter::format(1, 'R', 4000);  // "1연 (4,000매)"
$display = QuantityFormatter::format(0.5, 'R', 2000); // "0.5연 (2,000매)"
```

### Step 3: 포스터(littleprint)가 "매" 단위로 표시되는 코드 탐지

**도구:** Grep
**대상:** littleprint/poster 관련 PHP 파일

```bash
grep -rn "littleprint\|poster\|포스터" --include="*.php" -l . | xargs grep -n "'\''매'\''" 2>/dev/null
```

**PASS 기준:** 포스터 수량이 "장" 단위로 표시되는 경우
**FAIL 기준:** 포스터 수량이 "매"로 표시되는 경우

**위반 시 수정:**
```php
// ❌ 위반: 포스터를 "매"로 표시
$display = number_format($amount) . '매';  // "100매"

// ✅ 수정: "장" 단위 사용
$display = QuantityFormatter::format($amount, 'P');  // "100장"
```

### Step 4: NCR양식지(ncrflambeau) 매수 표시 누락 탐지

**도구:** Grep
**대상:** ncrflambeau 관련 PHP 파일

```bash
grep -rn "ncrflambeau\|NCR\|양식지" --include="*.php" -l . | xargs grep -n "'\''권'\''" 2>/dev/null
```

이어서 해당 라인 주변에 매수(매) 표시가 있는지 확인:

```bash
grep -B2 -A2 "'\''권'\''" admin/mlangprintauto/quote/includes/adapters/NcrflambeauAdapter.php
```

**PASS 기준:** "10권 (1,000매)" 형식으로 매수가 함께 표시되는 경우
**FAIL 기준:** "10권"만 표시되고 실제 매수가 누락된 경우

**위반 시 수정:**
```php
// ❌ 위반: 권만 표시, 매수 누락
$display = number_format($amount) . '권';  // "10권" — 몇 매인지 모름!

// ✅ 수정: 권 + 매수 표시
$multiplier = QuantityFormatter::extractNcrMultiplier($data);
$sheets = QuantityFormatter::calculateNcrSheets($amount, $multiplier);
$display = QuantityFormatter::format($amount, 'V', $sheets);  // "10권 (1,000매)"
```

### Step 5: 스티커가 "개" 단위로 표시되는 코드 탐지

**도구:** Grep
**대상:** sticker 관련 PHP 파일

```bash
grep -rn "sticker.*개\|스티커.*개" --include="*.php" .
```

**PASS 기준:** 스티커 수량이 "매" 단위로 표시되는 경우
**FAIL 기준:** 스티커 수량이 "개"로 표시되는 경우 (AGENTS.md에 기록된 기존 버그)

**위반 시 수정:**
```php
// ❌ 위반: 스티커를 "개"로 표시
$display = number_format($mesu) . '개';  // "1,000개"

// ✅ 수정: "매" 단위 사용
$display = QuantityFormatter::format($mesu, 'S');  // "1,000매"
```

### Step 6: 전단지 연 표시에 매수 보조 정보 누락 탐지

**도구:** Grep
**대상:** 전단지 수량을 표시하는 모든 PHP 파일

```bash
grep -rn "연['\"]" --include="*.php" . | grep -v "(.*매)"
```

**PASS 기준:** "연" 뒤에 "(X매)" 보조 표시가 함께 있는 경우
**FAIL 기준:** "0.5연"만 있고 "(2,000매)" 보조 정보가 없는 경우

## Output Format

| # | 위반 유형 | 파일 | 라인 | 현재 표시 | 올바른 표시 | 수정 방법 |
|---|----------|------|------|----------|------------|-----------|
| 1 | SSOT 우회 | 파일:라인 | 코드 | "1000매" | formatPrintQuantity() 사용 | 코드 예시 |
| 2 | 단위 불일치 | 파일:라인 | 코드 | 포스터 "100매" | "100장" | 코드 예시 |
| 3 | 매수 누락 | 파일:라인 | 코드 | NCR "10권" | "10권 (1,000매)" | 코드 예시 |

## Exceptions

다음은 **위반이 아닙니다**:

1. **QuantityFormatter.php 자체** — SSOT 내부에서 단위 문자열을 정의하는 것은 당연
2. **기존 견적서 어댑터 (quote/includes/adapters/)** — 레거시 코드로 점진적 마이그레이션 대상. 새로 만들 때만 FAIL
3. **DataAdapter.php의 기존 코드** — 레거시 호환 레이어로 현재 동작 중. 신규 코드에서만 위반 판정
4. **send_cart_quotation.php의 기존 코드** — 장바구니 견적 레거시. 수정 시에만 FAIL
5. **에러 로그/디버그 메시지** — `error_log("처리: 5개")` 같은 로그는 사용자에게 표시되지 않으므로 면제
6. **HTML 도움말/설명 텍스트** — "수량은 100매 단위입니다" 같은 안내 문구는 면제
7. **JavaScript 내 변수명/주석** — JS에서 단위를 언급하는 건 PHP 규칙 밖
8. **NCR 복사매수 텍스트** — "2매 복사", "3매 1조" 등은 단위 표시가 아닌 제품 사양 설명

### 🔵 견적서 수동입력 예외 (CRITICAL)

**견적서에서 관리자가 수동으로 입력하는 항목은 단위 규정의 적용 대상이 아닙니다.**

견적서 시스템에는 두 가지 모드가 있습니다:

| 모드 | 설명 | 단위 규정 | 판별 기준 |
|------|------|----------|----------|
| **자동 계산 품목** | 9개 고정 제품 (전단지, 명함 등) | ✅ 적용 — PRODUCT_UNITS 규칙 강제 | `product_type`이 PRODUCT_UNITS에 존재 |
| **수동 입력 품목** | 배너, 현수막, 특수인쇄 등 비규격 | ❌ 면제 — 관리자가 자유롭게 단위 지정 | `product_type`이 비어있거나 PRODUCT_UNITS에 없음 |

**수동 입력 면제 사례:**
- 포스터를 "매"로 쓰든 "장"으로 쓰든 → **면제** (관리자 판단 존중)
- 배너 "3개", 현수막 "2장", X배너 "5개" → **면제** (고정 품목이 아님)
- 관리자가 수량을 직접 타이핑하는 모든 경우 → **면제**

**면제 판별 로직 (QuoteTableRenderer.php 96~127행 참조):**
```php
// 1. product_type이 PRODUCT_UNITS에 있으면 → SSOT 규칙 적용 (자동)
if (!empty($productType) && isset(QuantityFormatter::PRODUCT_UNITS[$productType])) {
    // → 단위 규정 적용 대상
}
// 2. 그 외 (비규격/수동 입력) → 면제
else {
    // → qty_unit 또는 레거시 unit 필드 사용 (관리자 자유 입력)
}
```

9. **비규격 품목의 단위 표시** — `product_type`이 비어있거나 PRODUCT_UNITS에 정의되지 않은 품목(배너, 현수막, X배너, 특수인쇄 등)은 관리자가 수동으로 단위를 지정하므로 어떤 단위든 허용
10. **견적서 수동 수량 입력** — 관리자가 견적서에서 수량과 단위를 직접 타이핑하는 경우, 자동 계산이 아닌 수동 판단이므로 면제. 포스터를 "매"로 쓰든 "장"으로 쓰든 관리자 재량
11. **QuoteTableRenderer의 fallback 경로** — `formatUnitCell()`에서 `product_type` 기반 SSOT를 거치지 않고 `qty_unit`이나 레거시 `unit` 필드로 빠지는 경우는 수동 입력 또는 비규격 품목이므로 면제
