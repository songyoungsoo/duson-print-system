---
name: verify-sticker-logic
description: 스티커(sticker_new) 가격을 DB lookup으로 조회하는 코드를 탐지합니다. 스티커는 수학 공식(formula)으로만 계산해야 합니다. 스티커 관련 PHP 코드 수정 후 사용.
---

# 스티커 가격 로직 검증

## Purpose

스티커(sticker_new)는 다른 8개 제품과 달리 DB 가격표 조회(table_lookup)가 아닌 **수학 공식(formula)**으로 가격을 계산합니다.
이 스킬은 다음 위반을 탐지합니다:

1. **DB lookup으로 스티커 가격 조회** — `SELECT money FROM mlangprintauto_sticker WHERE ...` 패턴
2. **스티커를 cascade 드롭다운(style→section→quantity)으로 구현** — 스티커는 재질→가로입력→세로입력→수량→도무송→디자인 플로우
3. **sticker_new와 sticker 폴더명 혼동** — `sticker_new`가 실제 사용 폴더, `sticker`는 레거시
4. **스티커에 table_lookup 타입 지정** — PriceCalculationService에서 sticker_new는 반드시 `'type' => 'formula'`

## When to Run

- 스티커 가격 계산 관련 PHP 코드를 수정한 후
- AI 챗봇의 가격 안내 로직을 수정한 후
- PriceCalculationService.php의 PRODUCT_CONFIGS를 수정한 후
- 새로운 가격 계산 기능을 추가한 후
- 견적서 시스템에서 스티커 가격을 다루는 코드를 수정한 후

## Related Files

| File | Purpose |
|------|---------|
| `mlangprintauto/sticker_new/calculate_price_ajax.php` | 스티커 가격 계산 SSOT (수학 공식) |
| `includes/PriceCalculationService.php` | 가격 계산 중앙 서비스 (sticker_new → formula 설정) |
| `v2/src/Services/AI/ChatbotService.php` | AI 챗봇 스티커 가격 계산 (calculateStickerPrice 메서드) |
| `en/products/order_sticker.php` | 영문 스티커 주문 페이지 (formula-based pricing) |
| `mlangprintauto/sticker_new/index.php` | 한국어 스티커 주문 페이지 |
| `v2/config/products.php` | 제품 설정 (sticker → ui_type: formula_input) |
| `dashboard/includes/config.php` | 대시보드 설정 (sticker 테이블 참조 — 가격용 아님 주의) |

## Workflow

### Step 1: 스티커 가격을 DB에서 조회하는 코드 탐지

**도구:** Grep
**대상:** 모든 PHP 파일

```bash
grep -rn "SELECT.*FROM.*mlangprintauto_sticker\b" --include="*.php" .
```

**PASS 기준:** 결과가 0건이거나, 가격 조회(money/price)가 아닌 용도(마이그레이션, 스키마 확인 등)인 경우
**FAIL 기준:** `SELECT money` 또는 `SELECT ... price` 패턴으로 `mlangprintauto_sticker`에서 가격을 조회하는 경우

**위반 시 수정:**
```php
// ❌ 위반: DB에서 스티커 가격 조회
$sql = "SELECT money FROM mlangprintauto_sticker WHERE style=? AND Section=? AND quantity=?";

// ✅ 수정: 수학 공식으로 계산
$result = calculateStickerPrice($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect);
$price = $result['st_price'];
```

### Step 2: sticker_new에 table_lookup 타입 지정 탐지

**도구:** Grep
**대상:** 가격 계산 관련 PHP 파일

```bash
grep -n "sticker_new" includes/PriceCalculationService.php v2/config/products.php
```

이어서 해당 블록 내에서 type 확인:

```bash
grep -A5 "sticker_new" includes/PriceCalculationService.php | grep "type"
```

**PASS 기준:** `sticker_new`의 type이 `'formula'`인 경우
**FAIL 기준:** `sticker_new`의 type이 `'table_lookup'`인 경우

**위반 시 수정:**
```php
// ❌ 위반
'sticker_new' => [
    'type' => 'table_lookup',
    'table' => 'mlangprintauto_sticker',
],

// ✅ 수정
'sticker_new' => [
    'type' => 'formula',
    'calculator' => 'calculateStickerPrice'
],
```

### Step 3: 스티커를 cascade 드롭다운으로 구현한 코드 탐지

**도구:** Grep
**대상:** 모든 PHP 파일

```bash
grep -rn "sticker.*style.*Section\|sticker.*cascade\|sticker.*dropdown" --include="*.php" .
```

**PASS 기준:** 결과가 0건이거나, 레거시 `sticker` 폴더(sticker_new가 아닌)의 코드인 경우
**FAIL 기준:** `sticker_new` 관련 코드에서 style→Section→quantity cascade 패턴 사용

**위반 시 수정:**
스티커는 cascade(style→section→quantity)가 아닌 formula 플로우를 사용:
- 재질(jong) 선택 → 가로(garo) mm 입력 → 세로(sero) mm 입력 → 수량(mesu) 선택 → 도무송(domusong) 선택 → 디자인(uhyung) 선택

### Step 4: sticker_new/sticker 폴더명 혼동 탐지

**도구:** Grep
**대상:** 새로 작성되거나 수정된 PHP 파일

```bash
grep -rn "mlangprintauto/sticker/" --include="*.php" . | grep -v "sticker_new" | grep -v "msticker"
```

**PASS 기준:** 결과가 레거시 파일(mlangprintauto/sticker/ 폴더 자체)이거나 마이그레이션 코드인 경우
**FAIL 기준:** 새 코드에서 `mlangprintauto/sticker/`를 현행 스티커 경로로 사용하는 경우

**위반 시 수정:**
```php
// ❌ 위반: 레거시 폴더 참조
include "mlangprintauto/sticker/calculate_price_ajax.php";

// ✅ 수정: 현행 폴더 사용
include "mlangprintauto/sticker_new/calculate_price_ajax.php";
```

### Step 5: 스티커 요율 테이블(shop_d1~d4) 오용 탐지

**도구:** Grep
**대상:** 모든 PHP 파일 (sticker_new 폴더 및 ChatbotService 제외)

```bash
grep -rn "shop_d[1-4]" --include="*.php" . | grep -v "sticker_new/" | grep -v "ChatbotService"
```

**PASS 기준:** 결과가 0건 (shop_d1~d4는 스티커 전용 요율 테이블)
**FAIL 기준:** 스티커가 아닌 제품의 코드에서 shop_d1~d4를 참조하는 경우

## Output Format

| # | 위반 유형 | 파일 | 라인 | 문제 | 수정 방법 |
|---|----------|------|------|------|-----------|
| 1 | DB lookup | 파일:라인 | 코드 | 설명 | 수정 코드 |

## Exceptions

다음은 **위반이 아닙니다**:

1. **dashboard/includes/config.php의 sticker 테이블 참조** — 대시보드 카테고리 관리용이며 가격 조회가 아님
2. **system/migration/MigrationSync.php의 mlangprintauto_sticker** — 데이터 마이그레이션 대상 테이블 목록이며 가격 계산 아님
3. **레거시 mlangprintauto/sticker/ 폴더 내부의 코드** — 구 버전 코드로 현재 사용되지 않음
4. **PriceCalculationService.php의 'sticker' 설정 (133-144행)** — 레거시 `sticker` 키의 table_lookup은 의도된 것 (sticker_new와 별개)
5. **ChatbotService.php의 경고 주석** — `⚠️ 스티커는 DB 가격표를 조회하지 않음!` 같은 주석은 올바른 안내
6. **tmp_db_check.php 같은 임시 파일** — DB 스키마 확인용 일회성 스크립트
