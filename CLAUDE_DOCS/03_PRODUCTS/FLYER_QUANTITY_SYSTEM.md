# 전단지 수량 표기 시스템 (연/매수)

**작성일**: 2025-12-03
**상태**: ✅ 검증 완료

## 1. 개요

전단지(inserted) 제품은 인쇄업계 표준 단위인 **"연"**을 사용합니다.
일반 소비자의 이해를 돕기 위해 **"0.5연 (2,000매)"** 형식으로 표시합니다.

### 핵심 공식
```
매수 = 500 × 절수 × 연수

예시 (A4 = 8절):
- 0.5연 = 500 × 8 × 0.5 = 2,000매
- 1연 = 500 × 8 × 1 = 4,000매
- 2연 = 500 × 8 × 2 = 8,000매
```

### 절수(Jeolsu) 참조표
| 규격 | 절수 | 1연당 매수 |
|------|------|-----------|
| A1 (전지) | 1 | 500매 |
| A2 | 2 | 1,000매 |
| A3 | 4 | 2,000매 |
| A4 | 8 | 4,000매 |
| A5 | 16 | 8,000매 |
| A6 | 32 | 16,000매 |

---

## 2. 데이터 흐름도

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           고객 주문 흐름                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐                 │
│  │ 제품 페이지   │     │  장바구니     │     │  주문 완료    │                 │
│  │ index.php    │ ──▶ │  cart.php    │ ──▶ │ ProcessOrder │                 │
│  │              │     │              │     │  _unified    │                 │
│  │ MY_amount    │     │ shop_temp    │     │              │                 │
│  │ = 0.5 (연수) │     │ 저장         │     │ mlangorder_  │                 │
│  │              │     │              │     │ printauto    │                 │
│  └──────────────┘     └──────────────┘     └──────────────┘                 │
│         │                                          │                         │
│         ▼                                          ▼                         │
│  ┌──────────────┐                         ┌──────────────┐                  │
│  │ calculate_   │                         │ Type_1 JSON  │                  │
│  │ price_ajax   │                         │ 저장         │                  │
│  │              │                         │              │                  │
│  │ quantityTwo  │                         │ MY_amount:   │                  │
│  │ = 2000 (매수)│                         │ "0.50"       │                  │
│  └──────────────┘                         │ quantityTwo: │                  │
│                                           │ 2000         │                  │
│                                           └──────────────┘                  │
│                                                    │                         │
└────────────────────────────────────────────────────┼─────────────────────────┘
                                                     │
┌────────────────────────────────────────────────────┼─────────────────────────┐
│                           견적서 흐름               │                         │
├────────────────────────────────────────────────────┼─────────────────────────┤
│                                                     │                         │
│  ┌──────────────┐     ┌──────────────┐             │                         │
│  │ 견적서 모달   │     │ 견적서 저장   │             │                         │
│  │ quotation-   │ ──▶ │ save_        │             │                         │
│  │ modal.js     │     │ quotation.php│             │                         │
│  │              │     │              │             │                         │
│  │ myAmount:    │     │ quotations   │             │                         │
│  │ "0.5"        │     │ 테이블 저장   │             │                         │
│  │ quantityTwo: │     │              │             │                         │
│  │ 2000         │     └──────────────┘             │                         │
│  └──────────────┘            │                     │                         │
│                              │                     │                         │
│                              ▼                     │                         │
│                     ┌──────────────┐               │                         │
│                     │ 주문 변환     │               │                         │
│                     │ convert_to_  │ ─────────────▶│                         │
│                     │ order.php    │               │                         │
│                     └──────────────┘               │                         │
│                                                     │                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                                     │
                                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           관리자 확인 (공통 출력)                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐                 │
│  │ 주문 목록     │     │ 주문서 출력   │     │ 주문서 인쇄   │                 │
│  │ orderlist    │ ──▶ │ OrderForm    │ ──▶ │ 관리자용     │                 │
│  │ .php         │     │ OrderTree    │     │ 직원용       │                 │
│  │              │     │ .php         │     │              │                 │
│  │ 수량: 0.5연  │     │              │     │ 수량: 0.5    │                 │
│  │              │     │ 수량: 0.5연  │     │ 단위: 연     │                 │
│  └──────────────┘     └──────────────┘     └──────────────┘                 │
│                                                                              │
│  ※ 모든 출력에서 동일하게 "0.5연" 표시 (일관성 유지)                          │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. 핵심 파일 및 로직

### 3.1 가격 계산 API
**파일**: `mlangprintauto/inserted/calculate_price_ajax.php`

```php
// DB에서 가격 조회
$query = "SELECT * FROM mlangprintauto_inserted
          WHERE style='$MY_type' AND Section='$PN_type'
          AND quantity='$MY_amount' AND TreeSelect='$MY_Fsd' AND POtype='$POtype'";

// 응답에 quantityTwo 포함 (매수 정보)
$response = [
    'success' => true,
    'data' => [
        'MY_amountRight' => $ViewquantityTwo . '장',  // "2000장"
        // ... 기타 가격 정보
    ]
];
```

### 3.2 JavaScript 가격 데이터
**파일**: `mlangprintauto/inserted/calculator.js`

```javascript
// MY_amountRight에서 숫자만 추출 (예: "4000장" -> 4000)
var quantityTwo = parseInt((data.MY_amountRight || '').replace(/[^0-9]/g, '')) || 0;

window.currentPriceData = {
    // ... 기타 가격 정보
    myAmount: form.MY_amount.value,      // "0.5" (연수)
    quantityTwo: quantityTwo              // 2000 (매수)
};
```

### 3.3 주문서 출력 (전단지 감지 로직)
**파일**: `mlangorder_printauto/OrderFormOrderTree.php`

```php
// 전단지 감지 (3가지 위치에서 동일 로직)
$product_type = $json_data['product_type'] ?? '';
$item_type_str = $summary_item['Type'] ?? '';

$is_flyer = ($product_type === 'inserted' || $product_type === 'leaflet' ||
             strpos($item_type_str, '전단지') !== false ||
             strpos($item_type_str, '리플렛') !== false);

// 전단지: MY_amount(연수)를 수량으로, 단위는 "연"
if ($is_flyer && isset($json_data['MY_amount']) && floatval($json_data['MY_amount']) > 0) {
    $quantity_num = floatval($json_data['MY_amount']);  // 0.5
    $unit = '연';
}
```

### 3.4 수량 표시 (소수점 처리)
```php
// 정수면 정수로, 소수면 소수점 1자리로 표시
<?= $quantity_num
    ? (floor($quantity_num) == $quantity_num
        ? number_format($quantity_num)      // 정수: "1", "2"
        : number_format($quantity_num, 1))  // 소수: "0.5", "1.5"
    : '-'
?>
```

---

## 4. DB 저장 구조

### 4.1 Type_1 JSON 구조 (mlangorder_printauto)
```json
{
    "product_type": "inserted",
    "MY_amount": "0.50",
    "quantityTwo": 2000,
    "formatted_display": "품명: 전단지\n사양: A4 / 90g아트지 / 2000매 (0.5연)\n수량: 0.5연",
    "quote_no": "QT-20251203-002",
    "quote_id": 47
}
```

### 4.2 필드 설명
| 필드 | 타입 | 설명 | 예시 |
|------|------|------|------|
| MY_amount | string | 연수 (주문 수량) | "0.50" |
| quantityTwo | int | 매수 (실제 장수) | 2000 |
| formatted_display | string | 화면 표시용 텍스트 | "0.5연 (2,000매)" |

---

## 5. 검증 결과 (2025-12-03)

### 테스트 주문: #103861
```
Type: 전단지
MY_amount: 0.50
quantityTwo: 2000
is_flyer: TRUE
```

### HTML 출력 확인
```html
<!-- 관리자용 -->
<td>0.5</td>  <!-- 수량 -->
<td>연</td>   <!-- 단위 -->

<!-- 직원용 -->
<td>0.5</td>  <!-- 수량 -->
<td>연</td>   <!-- 단위 -->

<!-- Excel용 -->
<td>0.5</td>  <!-- 수량 -->
<td>연</td>   <!-- 단위 -->
```

✅ **모든 출력에서 "0.5연" 정확히 표시**

---

## 6. 견적서 → 주문 변환 시 수량 유지

### 견적서 저장 시
```javascript
// quotation-modal.js
const itemData = {
    quantity: window.currentPriceData.myAmount,      // "0.5"
    quantityTwo: window.currentPriceData.quantityTwo // 2000
};
```

### 주문 변환 시
```php
// convert_to_order.php
$type1_data = [
    'MY_amount' => $item['quantity'],        // "0.5"
    'quantityTwo' => $item['quantity_two'],  // 2000
    // ...
];
```

---

## 7. 관련 파일 목록

| 파일 | 역할 |
|------|------|
| `mlangprintauto/inserted/index.php` | 전단지 제품 페이지 |
| `mlangprintauto/inserted/calculator.js` | 가격 계산 JS |
| `mlangprintauto/inserted/calculate_price_ajax.php` | 가격 API |
| `mlangprintauto/shop/cart.php` | 장바구니 |
| `mlangorder_printauto/ProcessOrder_unified.php` | 주문 처리 |
| `mlangorder_printauto/OrderFormOrderTree.php` | 주문서 출력 |
| `admin/mlangprintauto/orderlist.php` | 관리자 주문 목록 |
| `mlangprintauto/quote/save_quotation.php` | 견적서 저장 |
| `mlangprintauto/shop/convert_to_order.php` | 견적→주문 변환 |

---

## 8. 주의사항

### ❌ 잘못된 패턴
```php
// number_format()은 0.5를 1로 반올림함!
number_format(0.5);  // "1" ← 잘못됨!
```

### ✅ 올바른 패턴
```php
// 소수점 여부 확인 후 처리
if (floor($qty) == $qty) {
    echo number_format($qty);      // 정수
} else {
    echo number_format($qty, 1);   // 소수점 1자리
}
```

---

*Last Updated: 2025-12-03*
