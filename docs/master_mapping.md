# 데이터 매핑 마스터 테이블

> **용도**: AI 및 개발자가 Excel ↔ DB ↔ UI 데이터 흐름을 참조하는 SSOT(Single Source of Truth)
> **적용 범위**: 9개 품목 공통
> **최종 수정**: 2026-01-14

---

## I. 데이터 매핑 테이블 (Excel ↔ DB ↔ UI)

| 엑셀 원천 항목 | DB 컬럼명 | 타입 | UI 출력 로직 (PHP) | 비고 |
|---------------|----------|------|-------------------|------|
| 품목명 | `product_name` | VARCHAR | `$row['product_name']` | 9대 품목 고유명칭 사용 |
| 규격/옵션 전체 | `Type_1` | TEXT | `$row['Type_1']` | 전체 옵션 텍스트 보존 |
| 수량 숫자 | `qty_val` | DECIMAL(float) | `$row['qty_val']` | `.00` 제거 필수 |
| 단위 코드 | `qty_unit` | VARCHAR | `formatPrintQuantity()` | R, S, B 등 코드화 |
| 공급가액 | `money_5` | VARCHAR | `number_format($row['money_5'])` | 최종 합계 금액 |
| 인쇄 매수 | `mesu` | VARCHAR | `formatPrintQuantity()` 내부 | 0.5연 → 2,000매 환산용 |

---

## II. 단위 코드 정의

| 코드 | 한글명 | 환산 로직 | 예시 |
|------|-------|----------|------|
| `R` | 연 | 1연 = 500매, 0.5연 = 2,000매 (특수) | `0.5R` → "0.5연 (2,000매)" |
| `S` | 매 | 직접 표시 | `1000S` → "1,000매" |
| `B` | 부 | 직접 표시 | `500B` → "500부" |
| `K` | 권 | 직접 표시 | `100K` → "100권" |
| `G` | 개 | 직접 표시 | `50G` → "50개" |
| `J` | 장 | 직접 표시 | `200J` → "200장" |

---

## III. 품목별 수량 표시 규칙

### 3.1 일반 품목 (매/부 단위)

```php
// 적용: 전단지, 포스터, 스티커, 상품권 등
$display = number_format($qty_val) . $unit_korean;
// 예: 1000 + "매" → "1,000매"
```

### 3.2 연 단위 품목 (봉투, NCR)

```php
// 적용: 봉투, NCR양식
if ($qty_unit === 'R') {
    $sheets = calculateSheets($qty_val); // 연 → 매 환산
    $display = "{$qty_val}연 ({$sheets}매)";
}
// 예: 0.5연 → "0.5연 (2,000매)"
```

### 3.3 천 단위 변환 품목 (명함, 봉투)

```php
// qty_val < 10일 때 ×1000 적용
if ($qty_val < 10 && in_array($product, ['namecard', 'envelope'])) {
    $actual_qty = $qty_val * 1000;
}
// 예: MY_amount=1 → 1,000매
```

---

## IV. formatPrintQuantity() 핵심 로직

```php
/**
 * 수량을 사용자 친화적 형식으로 변환
 *
 * @param array $item 주문 항목 배열
 * @return string 포맷된 수량 문자열
 */
function formatPrintQuantity($item) {
    $qty_val = floatval($item['qty_val'] ?? $item['MY_amount'] ?? 0);
    $qty_unit = $item['qty_unit'] ?? 'S';
    $mesu = $item['mesu'] ?? '';

    // 단위별 한글 매핑
    $unit_map = [
        'R' => '연', 'S' => '매', 'B' => '부',
        'K' => '권', 'G' => '개', 'J' => '장'
    ];

    $unit_korean = $unit_map[$qty_unit] ?? '매';

    // 연 단위: 매수 환산 표시
    if ($qty_unit === 'R' && !empty($mesu)) {
        $sheets = number_format(floatval($mesu));
        return "{$qty_val}{$unit_korean} ({$sheets}매)";
    }

    // .00 제거
    $formatted_qty = ($qty_val == intval($qty_val))
        ? number_format(intval($qty_val))
        : $qty_val;

    return "{$formatted_qty}{$unit_korean}";
}
```

---

## V. 품목별 DB 컬럼 매핑

| 품목 | 테이블명 | 수량 컬럼 | 가격 컬럼 | 특이사항 |
|------|---------|----------|----------|---------|
| 전단지 | `mlangprintauto_inserted` | `MY_amount` | `money_5` | 표준 |
| 명함 | `mlangprintauto_namecard` | `MY_amount` | `money_5` | ×1000 변환 |
| 봉투 | `mlangprintauto_envelope` | `MY_amount` | `money_5` | 연 단위 |
| 스티커 | `mlangprintauto_sticker` | `MY_amount` | `money_5` | 표준 |
| 자석스티커 | `mlangprintauto_msticker` | `MY_amount` | `money_5` | 표준 |
| 카다록 | `mlangprintauto_cadarok` | `MY_amount` | `money_5` | 부 단위 |
| 포스터 | `mlangprintauto_littleprint` | `MY_amount` | `money_5` | 표준 |
| 상품권 | `mlangprintauto_merchandisebond` | `MY_amount` | `money_5` | 표준 |
| NCR양식 | `mlangprintauto_ncrflambeau` | `MY_amount` | `money_5` | 연 단위 |

---

## VI. UI 출력 일관성 규칙

### 6.1 동일성 원칙

> **"사용자 주문 완료 페이지 = 관리자 상세 페이지"**

두 화면에서 표시되는 텍스트가 100% 일치해야 함.

### 6.2 가독성 규칙

| 원본 값 | ❌ 잘못된 표시 | ✅ 올바른 표시 |
|--------|--------------|--------------|
| 500.00 | 500.00매 | 500매 |
| 0.50 | 0.50연 | 0.5연 (2,000매) |
| 1000 | 1000매 | 1,000매 |

### 6.3 quantity_display 검증 규칙

```php
// ❌ NEVER: 단위 없이 그대로 사용
$line2 = $item['quantity_display'];

// ✅ ALWAYS: 단위 체크 후 formatQuantity 호출
$quantity_display = $item['quantity_display'] ?? '';
if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $quantity_display = formatPrintQuantity($item);
}
```

---

## VII. 참조 파일 위치

| 파일 | 경로 | 용도 |
|------|------|------|
| QuantityFormatter | `/includes/QuantityFormatter.php` | 수량 포맷팅 SSOT |
| ProductSpecFormatter | `/includes/ProductSpecFormatter.php` | 규격 표시 통합 |
| DataAdapter | `/includes/DataAdapter.php` | DB → 표준 구조 변환 |

---

*Document Version: 1.0*
*Created: 2026-01-14*
*Maintainer: AI & Development Team*
