# quantity_display 검증 규칙 수정

**날짜**: 2026-01-07
**작업**: quantity_display 필드 단위 검증 로직 추가
**상태**: ✅ 완료

---

## 📌 문제 상황

### 증상
- 장바구니/주문 페이지에서 수량이 "1"로 표시됨
- 봉투: "마스터1도 / **1** / 인쇄만" (기대: "마스터1도 / **1,000매** / 인쇄만")
- 스티커: "**1** / 인쇄만" (기대: "**1,000매** / 인쇄만")

### 근본 원인
1. **DB 저장 문제**: `shop_temp.quantity_display` 필드에 단위 없이 "1"만 저장됨
2. **검증 누락**: ProductSpecFormatter가 `quantity_display`를 단위 체크 없이 그대로 사용
3. **실제 데이터는 정확**: `MY_amount=1000.00`, `mesu=1000`은 올바르게 저장됨

### 디버그 결과
```
No    제품       quantity_display    MY_amount    mesu    Line2 (format)
1370  envelope   1                   1000.00      NULL    마스터1도 / 1 / 인쇄만  ❌
1369  sticker    1                   NULL         1000    1 / 인쇄만             ❌
```

---

## 🔧 해결 방법

### 수정된 로직
```php
// ✅ quantity_display가 비어있거나 단위가 없는 경우 formatQuantity() 호출
$quantity_display = $item['quantity_display'] ?? '';

// 단위 체크: 매, 연, 부, 권, 개, 장
if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $quantity_display = $this->formatQuantity($item);
}
```

### 수정 위치

#### 1. `ProductSpecFormatter::formatStandardized()`
**파일**: `includes/ProductSpecFormatter.php` (lines 71-83)
**사용처**: `format()` 메서드 호출 시

```php
// 2줄: 옵션 정보 (spec_sides / quantity_display / spec_design)
$quantity_display = $item['quantity_display'] ?? '';

// ✅ 수정: quantity_display가 비어있거나 단위가 없는 경우 formatQuantity() 호출
if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $quantity_display = $this->formatQuantity($item);
}

$line2_parts = array_filter([
    $item['spec_sides'] ?? '',
    $quantity_display,
    $item['spec_design'] ?? ''
]);
```

#### 2. `ProductSpecFormatter::buildLine2()`
**파일**: `includes/ProductSpecFormatter.php` (lines 323-331)
**사용처**: `formatUnified()` 메서드 호출 시

```php
// 수량+단위 (quantity_display)
$slot2 = $item['quantity_display'] ?? '';

// ✅ 수정: quantity_display가 비어있거나 단위가 없는 경우 formatQuantity() 호출
// 단위: 매, 연, 부, 권, 개, 장 등
if (empty($slot2) || !preg_match('/[매연부권개장]/u', $slot2)) {
    // formatQuantity() 호출 (레거시 로직 + 천 단위 변환)
    $slot2 = $this->formatQuantity($item);
}
```

---

## ✅ 테스트 결과

### Before (수정 전)
```
1370  envelope   1        1000.00   NULL   마스터1도 / 1 / 인쇄만         ❌
1369  sticker    1        NULL      1000   1 / 인쇄만                    ❌
```

### After (수정 후)
```
1370  envelope   1        1000.00   NULL   마스터1도 / 1,000매 / 인쇄만  ✅
1369  sticker    1        NULL      1000   1,000매 / 인쇄만              ✅
```

### formatQuantity() 처리 로직
- **봉투/명함**: `MY_amount < 10` → ×1000 변환
  - `MY_amount = 1` → `1,000매`
  - `MY_amount = 1000` → `1,000매`
- **스티커**: `mesu` 직접 사용
  - `mesu = 1000` → `1,000매`
- **전단지**: 연 단위 변환
  - `MY_amount = 1, mesu = 4000` → `1연 (4,000매)`

---

## 📊 영향 범위

### ✅ 영향 받는 페이지 (자동 수정)
- 장바구니 (`mlangprintauto/shop/cart.php`)
- 주문 완료 (`mlangorder_printauto/OrderComplete_universal.php`)
- 관리자 주문 목록 (`admin/mlangprintauto/admin.php`)
- 견적서 (`mlangprintauto/quote/create.php`)
- 마이페이지 주문 내역

### 🔒 하위 호환성
- ✅ 기존 데이터 정상 작동
- ✅ `quantity_display`가 올바른 경우 그대로 사용
- ✅ `quantity_display`가 잘못된 경우만 자동 수정

---

## 🔴 CRITICAL RULES 추가

**CLAUDE.md**에 새로운 절대 규칙 추가:

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

---

## 🚀 다음 단계 (예방 조치)

### 1. add_to_basket.php 수정 (근본 해결)
DB 저장 시 quantity_display를 올바르게 저장:

```php
// ❌ Before
$quantity_display = $_POST['quantity_display'] ?? '';  // "1" 저장될 수 있음

// ✅ After
$quantity_display = $_POST['quantity_display'] ?? '';

// 단위가 없으면 자동 추가
if (!empty($quantity_display) && !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $unit = ($product_type === 'ncrflambeau') ? '권' : '매';

    if ($product_type === 'envelope' || $product_type === 'namecard') {
        // 천 단위 변환
        $qty = floatval($quantity_display);
        $qty_value = $qty > 0 && $qty < 10 ? $qty * 1000 : intval($qty);
        $quantity_display = number_format($qty_value) . $unit;
    } else {
        $quantity_display = number_format($quantity_display) . $unit;
    }
}
```

### 2. 데이터 마이그레이션 (선택 사항)
기존 잘못된 데이터 일괄 수정:

```sql
-- 단위 없는 quantity_display 찾기
SELECT no, product_type, quantity_display, MY_amount, mesu
FROM shop_temp
WHERE quantity_display REGEXP '^[0-9]+$'
LIMIT 10;

-- 수정 스크립트 작성 (PHP)
-- fix_quantity_display.php
```

---

## 📝 참고 사항

### formatQuantity() 메서드 로직
**파일**: `includes/ProductSpecFormatter.php` (lines 733-783)

```php
private function formatQuantity($item) {
    $productType = $item['product_type'] ?? '';

    // 1. 스티커: mesu 최우선
    if (in_array($productType, ['sticker', 'msticker', 'msticker_01'])) {
        if (!empty($item['mesu'])) {
            return number_format(intval($item['mesu'])) . '매';
        }
    }

    // 2. 전단지/리플렛: 연 단위
    if (in_array($productType, ['inserted', 'leaflet'])) {
        $reams = floatval($item['MY_amount'] ?? 0);
        $sheets = intval($item['mesu'] ?? $item['quantityTwo'] ?? 0);

        if ($reams > 0) {
            $qty = number_format($reams, $reams == intval($reams) ? 0 : 1) . '연';
            if ($sheets > 0) {
                $qty .= ' (' . number_format($sheets) . '매)';
            }
            return $qty;
        }
    }

    // 3. 봉투/명함: 천 단위 변환
    if (in_array($productType, ['envelope', 'namecard'])) {
        if (!empty($item['MY_amount'])) {
            $amount = floatval($item['MY_amount']);
            $qty_value = $amount > 0 && $amount < 10 ? $amount * 1000 : intval($amount);
            return number_format($qty_value) . '매';
        }
    }

    // 4. 기타
    if (!empty($item['MY_amount'])) {
        $amount = floatval($item['MY_amount']);
        $unit = $item['unit'] ?? '매';
        return number_format(intval($amount)) . $unit;
    }

    return '';
}
```

---

**작성자**: Claude Code
**검증**: ✅ 디버그 스크립트로 11개 품목 테스트 완료
**하위 호환성**: ✅ 기존 코드 영향 없음
**적용 상태**: ✅ ProductSpecFormatter.php 수정 완료
