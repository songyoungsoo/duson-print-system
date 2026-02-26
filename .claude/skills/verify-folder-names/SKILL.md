---
name: verify-folder-names
description: 제품 폴더명 혼동(sticker↔sticker_new, poster↔littleprint, leaflet↔inserted)을 탐지합니다. 잘못된 경로 참조로 인한 404/500 에러를 방지합니다. PHP 파일 수정 후 사용.
---

# 제품 폴더명 혼동 방지 검증

## Purpose

이 사이트의 9개 제품 중 3개는 **역사적 이름**과 **실제 폴더명**이 다릅니다.
AI나 개발자가 직관적인 이름(sticker, poster, leaflet)을 사용하면 존재하지 않는 경로를 참조하게 됩니다:

| 제품 | ✅ 실제 폴더 | ❌ 금지 이름 | 왜 틀리나 |
|------|-------------|------------|----------|
| 스티커 | `sticker_new` | `sticker` | 레거시 폴더가 아직 존재하여 혼동 유발 |
| 포스터 | `littleprint` | `poster` | 직관적이지만 틀린 이름 |
| 전단지 | `inserted` | `leaflet` | 영문명이지만 실제 폴더는 inserted |

잘못된 폴더명 사용 시 증상:
- `include` / `require` → **Fatal Error** (파일 없음)
- `href` / `src` → **404 에러** (페이지 없음)
- 대부분의 경우 레거시 폴더에 오래된 파일이 있어서 **잘못된 버전이 실행됨** (더 위험)

## When to Run

- 제품 관련 PHP 파일을 새로 만들거나 수정한 후
- include/require 경로를 추가한 후
- 링크(href)나 AJAX URL을 작성한 후
- AI에게 제품 관련 코드를 시킨 후 (AI가 직관적 이름을 쓰는 경향)

## Related Files

| File | Purpose |
|------|---------|
| `mlangprintauto/sticker_new/` | ✅ 스티커 올바른 폴더 (calculate_price_ajax.php 등) |
| `mlangprintauto/sticker/` | ❌ 레거시 유령 폴더 (8파일 — 오래된 버전) |
| `mlangprintauto/littleprint/` | ✅ 포스터 올바른 폴더 |
| `mlangprintauto/poster/` | ❌ 레거시 유령 폴더 (3파일 — 오래된 버전) |
| `mlangprintauto/inserted/` | ✅ 전단지 올바른 폴더 |
| `mlangprintauto/leaflet/` | ❌ 레거시 유령 폴더 (5파일 — 오래된 버전) |
| `includes/nav.php` | 네비게이션 링크 (sticker_new 사용 중 — 정상) |
| `includes/DataAdapter.php` | ⚠️ product_type에 'leaflet', 'poster' 사용 (레거시 호환) |

## Workflow

### Step 1: 경로에서 금지 폴더명 탐지

**도구:** Grep
**대상:** 모든 PHP 파일

```bash
# sticker/ (sticker_new가 아닌) 경로 참조
grep -rn "mlangprintauto/sticker/" --include="*.php" \
  --exclude-dir="m/" --exclude-dir="sticker/" .

# poster/ (littleprint가 아닌) 경로 참조
grep -rn "mlangprintauto/poster/" --include="*.php" \
  --exclude-dir="m/" --exclude-dir="poster/" .

# leaflet/ (inserted가 아닌) 경로 참조
grep -rn "mlangprintauto/leaflet/" --include="*.php" \
  --exclude-dir="m/" --exclude-dir="leaflet/" .
```

**PASS 기준:** 결과 0건 (외부 파일에서 금지 폴더를 참조하지 않음)
**FAIL 기준:** 새 코드에서 금지 폴더 경로를 참조

**위반 시 수정:**
```php
// ❌ 위반
include 'mlangprintauto/sticker/calculate_price_ajax.php';
$url = '/mlangprintauto/poster/index.php';
require_once 'mlangprintauto/leaflet/add_to_basket.php';

// ✅ 수정
include 'mlangprintauto/sticker_new/calculate_price_ajax.php';
$url = '/mlangprintauto/littleprint/index.php';
require_once 'mlangprintauto/inserted/add_to_basket.php';
```

### Step 2: href/src 링크에서 금지 폴더명 탐지

**도구:** Grep
**대상:** 모든 PHP 파일

```bash
# HTML 링크에서 금지 폴더명
grep -rn 'href=.*"/mlangprintauto/sticker/"' --include="*.php" --exclude-dir="m/" .
grep -rn 'href=.*"/mlangprintauto/poster/"' --include="*.php" --exclude-dir="m/" .
grep -rn 'href=.*"/mlangprintauto/leaflet/"' --include="*.php" --exclude-dir="m/" .
```

**PASS 기준:** 결과 0건
**FAIL 기준:** 네비게이션이나 링크가 금지 폴더를 가리킴

### Step 3: AJAX/API URL에서 금지 폴더명 탐지

**도구:** Grep
**대상:** 모든 PHP, JS 파일

```bash
# AJAX URL에서 금지 폴더명
grep -rn "url.*:.*['\"].*sticker/\|fetch.*sticker/" --include="*.js" --include="*.php" \
  --exclude-dir="m/" --exclude-dir="sticker/" .
grep -rn "url.*:.*['\"].*poster/\|fetch.*poster/" --include="*.js" --include="*.php" \
  --exclude-dir="m/" --exclude-dir="poster/" .
grep -rn "url.*:.*['\"].*leaflet/\|fetch.*leaflet/" --include="*.js" --include="*.php" \
  --exclude-dir="m/" --exclude-dir="leaflet/" .
```

**PASS 기준:** 결과 0건
**FAIL 기준:** AJAX 호출이 금지 폴더를 대상으로 함

### Step 4: Ttable 값에서 폴더명 혼동 탐지

**도구:** Grep
**대상:** 모든 PHP 파일

DB의 `Ttable` 컬럼이나 `product_type` 값에서 혼동을 탐지합니다.

```bash
# 새 코드에서 Ttable이나 product_type에 금지값 사용
grep -rn "Ttable.*=.*'poster'\|product_type.*=.*'poster'" --include="*.php" \
  --exclude-dir="m/" .
grep -rn "Ttable.*=.*'leaflet'\|product_type.*=.*'leaflet'" --include="*.php" \
  --exclude-dir="m/" .
```

**PASS 기준:** 새 코드에서 결과 0건 (레거시 호환 코드는 예외)
**FAIL 기준:** 새로 작성한 코드에서 `product_type = 'poster'` 또는 `product_type = 'leaflet'` 사용

**위반 시 수정:**
```php
// ❌ 위반
$result['product_type'] = 'poster';
$result['product_type'] = 'leaflet';

// ✅ 수정
$result['product_type'] = 'littleprint';
$result['product_type'] = 'inserted';
```

## Output Format

| # | 파일 | 라인 | 금지 이름 | 올바른 이름 | 유형 |
|---|------|------|----------|------------|------|
| 1 | `파일:행` | 코드 | sticker | sticker_new | 경로 참조 |
| 2 | `파일:행` | 코드 | poster | littleprint | product_type |

## Exceptions

다음은 **위반이 아닙니다**:

1. **레거시 폴더 내부 파일** — `mlangprintauto/sticker/`, `poster/`, `leaflet/` 폴더 안의 파일 자체는 탐지 대상이 아님 (그 폴더 안에서 자기 자신을 참조하는 건 당연함)
2. **`m/` 모바일 백업 폴더** — `m/mlangprintauto260104/` 등 날짜 붙은 백업 폴더는 레거시
3. **문자열 매칭용 정규표현식** — `preg_match("/sticker/i", $type)` 같은 패턴은 폴더 참조가 아니라 product_type 문자열 매칭이므로 허용. 폴더 경로(`/`)를 포함하지 않으면 예외
4. **DataAdapter.php 레거시 호환** — 기존 DB 데이터와의 호환성을 위해 `'leaflet'`, `'poster'` 값을 읽어오는 코드는 허용. 단, **새로 INSERT하는 코드**에서 금지값 사용은 FAIL
5. **OrderFormOrderTree.php 레거시 분기** — `$product_type === 'leaflet'` 같은 레거시 데이터 분기 처리는 INFO (과거 데이터 호환)
6. **DB 컬럼 product_type = 'sticker'** — 스티커만 예외! 폴더는 `sticker_new`이지만 DB product_type은 `'sticker'`가 정상. 이 값 자체는 위반이 아님. **폴더 경로**에서 `sticker/`를 쓰는 것만 위반
7. **주석이나 문서** — `// poster → littleprint으로 변경됨` 같은 주석/설명은 실제 코드가 아님
8. **영문 사이트 키** — `en/products/` 에서 `'key' => 'sticker'`는 라우팅 키이며 폴더 경로가 아님

## Quick Reference (전체 9개 제품 매핑)

| # | 제품 | ✅ 폴더명 | ❌ 금지 | product_type |
|---|------|----------|--------|-------------|
| 1 | 전단지 | `inserted` | leaflet | inserted |
| 2 | 스티커 | `sticker_new` | sticker | sticker (예외!) |
| 3 | 자석스티커 | `msticker` | — | msticker |
| 4 | 명함 | `namecard` | — | namecard |
| 5 | 봉투 | `envelope` | — | envelope |
| 6 | 포스터 | `littleprint` | poster | littleprint |
| 7 | 상품권 | `merchandisebond` | giftcard | merchandisebond |
| 8 | 카다록 | `cadarok` | catalog | cadarok |
| 9 | NCR양식지 | `ncrflambeau` | form, ncr | ncrflambeau |
