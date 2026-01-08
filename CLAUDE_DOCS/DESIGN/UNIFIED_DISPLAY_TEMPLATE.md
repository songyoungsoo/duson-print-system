# 통합 표시 양식 설계: 11개 품목 고정 포맷

**작성일**: 2026-01-07
**목적**: 모든 제품을 동일한 양식으로 표시하여 일관성 확보

---

## 🎯 고정 양식표 (Universal Display Template)

모든 제품은 **동일한 4줄 구조**로 표시됩니다:

```
┌─────────────────────────────────────────────────────┐
│ 1줄: [제품종류] / [재질/용지] / [규격/크기]         │
│ 2줄: [인쇄옵션] / [수량 + 단위] / [디자인]          │
│ 3줄: [추가옵션] (있는 경우만)                        │
│ 4줄: [특수옵션] (프리미엄 옵션 또는 양면테이프 등)    │
└─────────────────────────────────────────────────────┘
```

---

## 📋 품목별 매핑 규칙

### **1. 명함 (namecard)**

| 줄 | 슬롯 | 표준 필드 | 레거시 필드 | 예시 값 |
|----|------|----------|----------|---------|
| 1줄-1 | 제품종류 | spec_type | MY_type_name | "일반명함" |
| 1줄-2 | 재질/용지 | spec_material | Section_name | "스노우지 250g" |
| 1줄-3 | 규격/크기 | spec_size | (고정값) | "90mm x 50mm" |
| 2줄-1 | 인쇄옵션 | spec_sides | POtype == '1' ? '단면' : '양면' | "단면칼라" |
| 2줄-2 | 수량+단위 | quantity_display | MY_amount < 10 ? ×1000 : 그대로 + '매' | "1,000매" |
| 2줄-3 | 디자인 | spec_design | ordertype === 'total' ? '디자인+인쇄' : '인쇄만' | "인쇄만" |
| 4줄 | 프리미엄 | premium_options | foil, numbering, perforation, rounding, creasing | "박(금박무광) 30,000원" |

**출력 예시**:
```
일반명함 / 스노우지 250g / 90mm x 50mm
단면칼라 / 1,000매 / 인쇄만
✨ 프리미엄 옵션: 박(금박무광) 30,000원
```

---

### **2. 전단지 (inserted) / 리플렛 (leaflet)**

| 줄 | 슬롯 | 표준 필드 | 레거시 필드 | 예시 값 |
|----|------|----------|----------|---------|
| 1줄-1 | 제품종류 | spec_type | MY_type_name (도수) | "일반4도" |
| 1줄-2 | 재질/용지 | spec_material | MY_Fsd_name | "모조지 80g" |
| 1줄-3 | 규격/크기 | spec_size | PN_type_name | "A4" |
| 2줄-1 | 인쇄옵션 | spec_sides | POtype == '1' ? '단면' : '양면' | "단면" |
| 2줄-2 | 수량+단위 | quantity_display | **우선: dropdown 값** → 계산: "X연 (Y매)" | "1연 (4,000매)" |
| 2줄-3 | 디자인 | spec_design | ordertype === 'total' ? '디자인+인쇄' : '인쇄만' | "인쇄만" |
| 3줄 | 추가옵션 | additional_options | coating, folding, creasing | "코팅:단면유광 / 접지:2단접지" |

**출력 예시**:
```
일반4도 / 모조지 80g / A4
단면 / 1연 (4,000매) / 인쇄만
추가 옵션: 코팅:단면유광 / 접지:2단접지
```

---

### **3. 봉투 (envelope)**

| 줄 | 슬롯 | 표준 필드 | 레거시 필드 | 예시 값 |
|----|------|----------|----------|---------|
| 1줄-1 | 제품종류 | spec_type | MY_type_name | "소봉투" |
| 1줄-2 | 재질/용지 | spec_material | Section_name | "소봉투(100모조 220*105)" |
| 1줄-3 | 규격/크기 | spec_size | (빈값) | "" |
| 2줄-1 | 인쇄옵션 | spec_sides | **POtype_name** (색상!) | "마스터1도" |
| 2줄-2 | 수량+단위 | quantity_display | MY_amount < 10 ? ×1000 : 그대로 + '매' | "1,000매" |
| 2줄-3 | 디자인 | spec_design | ordertype === 'total' ? '디자인+인쇄' : '인쇄만' | "인쇄만" |
| 4줄 | 특수옵션 | envelope_tape | envelope_tape_enabled, envelope_tape_quantity | "양면테이프: 500개" |

**출력 예시**:
```
소봉투 / 소봉투(100모조 220*105)
마스터1도 / 1,000매 / 인쇄만
양면테이프: 500개
```

**⚠️ 중요**: 봉투의 spec_sides는 **인쇄면이 아니라 인쇄 색상**입니다!

---

### **4. 스티커 (sticker)**

| 줄 | 슬롯 | 표준 필드 | 레거시 필드 | 예시 값 |
|----|------|----------|----------|---------|
| 1줄-1 | 제품종류 | spec_type | domusong (앞의 0 제거) | "사각" |
| 1줄-2 | 재질/용지 | spec_material | jong ("jil " 제거) | "아트유광코팅" |
| 1줄-3 | 규격/크기 | spec_size | garo + "mm x " + sero + "mm" | "90mm x 50mm" |
| 2줄-1 | 인쇄옵션 | spec_sides | (빈값) | "" |
| 2줄-2 | 수량+단위 | quantity_display | mesu + '매' (×1000 아님!) | "500매" |
| 2줄-3 | 디자인 | spec_design | ordertype === 'total' ? '디자인+인쇄' : '인쇄만' | "인쇄만" |

**출력 예시**:
```
사각 / 아트유광코팅 / 90mm x 50mm
500매 / 인쇄만
```

**⚠️ 중요**: 스티커의 mesu는 **직접 매수**입니다 (천 단위 변환 없음)!

---

### **5. 자석스티커 (msticker)**

| 줄 | 슬롯 | 표준 필드 | 레거시 필드 | 예시 값 |
|----|------|----------|----------|---------|
| 1줄-1 | 제품종류 | spec_type | MY_type_name | "사각" |
| 1줄-2 | 재질/용지 | spec_material | (빈값) | "" |
| 1줄-3 | 규격/크기 | spec_size | Section_name | "자석 0.3mm" |
| 2줄-1 | 인쇄옵션 | spec_sides | POtype == '1' ? '단면' : '양면' | "단면" |
| 2줄-2 | 수량+단위 | quantity_display | MY_amount + '매' | "100매" |
| 2줄-3 | 디자인 | spec_design | ordertype === 'total' ? '디자인+인쇄' : '인쇄만' | "인쇄만" |

**출력 예시**:
```
사각 / 자석 0.3mm
단면 / 100매 / 인쇄만
```

---

### **6. 카다록 (cadarok)**

| 줄 | 슬롯 | 표준 필드 | 레거시 필드 | 예시 값 |
|----|------|----------|----------|---------|
| 1줄-1 | 제품종류 | spec_type | MY_type_name | "중철" |
| 1줄-2 | 재질/용지 | spec_material | (빈값) | "" |
| 1줄-3 | 규격/크기 | spec_size | Section_name | "A4" |
| 2줄-1 | 인쇄옵션 | spec_sides | POtype_name | "4도4도" |
| 2줄-2 | 수량+단위 | quantity_display | **우선: dropdown** → 계산: MY_amount + '부' | "500부" |
| 2줄-3 | 디자인 | spec_design | ordertype === 'total' ? '디자인+인쇄' : '인쇄만' | "인쇄만" |
| 3줄 | 추가옵션 | additional_options | coating, folding, creasing | "코팅:양면유광" |

**출력 예시**:
```
중철 / A4
4도4도 / 500부 / 인쇄만
추가 옵션: 코팅:양면유광
```

---

### **7. 포스터 (littleprint)**

| 줄 | 슬롯 | 표준 필드 | 레거시 필드 | 예시 값 |
|----|------|----------|----------|---------|
| 1줄-1 | 제품종류 | spec_type | MY_type_name | "일반포스터" |
| 1줄-2 | 재질/용지 | spec_material | Section_name 또는 MY_Fsd_name | "아트지 200g" |
| 1줄-3 | 규격/크기 | spec_size | PN_type_name | "A2" |
| 2줄-1 | 인쇄옵션 | spec_sides | (빈값) | "" |
| 2줄-2 | 수량+단위 | quantity_display | MY_amount + '장' | "100장" |
| 2줄-3 | 디자인 | spec_design | ordertype === 'total' ? '디자인+인쇄' : '인쇄만' | "인쇄만" |
| 3줄 | 추가옵션 | additional_options | coating, folding, creasing | "코팅:단면유광" |

**출력 예시**:
```
일반포스터 / 아트지 200g / A2
100장 / 인쇄만
추가 옵션: 코팅:단면유광
```

---

### **8. 상품권 (merchandisebond)**

| 줄 | 슬롯 | 표준 필드 | 레거시 필드 | 예시 값 |
|----|------|----------|----------|---------|
| 1줄-1 | 제품종류 | spec_type | MY_type_name | "일반상품권" |
| 1줄-2 | 재질/용지 | spec_material | Section_name | "고급지 250g" |
| 1줄-3 | 규격/크기 | spec_size | (고정값) | "90mm x 50mm" |
| 2줄-1 | 인쇄옵션 | spec_sides | POtype == '1' ? '단면칼라' : '양면칼라' | "단면칼라" |
| 2줄-2 | 수량+단위 | quantity_display | MY_amount + '매' | "500매" |
| 2줄-3 | 디자인 | spec_design | ordertype === 'total' ? '디자인+인쇄' : '인쇄만' | "인쇄만" |
| 4줄 | 프리미엄 | premium_options | foil, numbering, perforation, rounding, creasing | "박(금박무광) 30,000원" |

**출력 예시**:
```
일반상품권 / 고급지 250g / 90mm x 50mm
단면칼라 / 500매 / 인쇄만
✨ 프리미엄 옵션: 박(금박무광) 30,000원
```

---

### **9. NCR양식지 (ncrflambeau)**

| 줄 | 슬롯 | 표준 필드 | 레거시 필드 | 예시 값 |
|----|------|----------|----------|---------|
| 1줄-1 | 제품종류 | spec_type | **PN_type_name** (타입) | "2도" |
| 1줄-2 | 재질/용지 | spec_material | MY_Fsd_name | "NCR 2도" |
| 1줄-3 | 규격/크기 | spec_size | (빈값) | "" |
| 2줄-1 | 인쇄옵션 | spec_sides | **MY_type_name** (도수) | "4도" |
| 2줄-2 | 수량+단위 | quantity_display | MY_amount + '권' | "500권" |
| 2줄-3 | 디자인 | spec_design | ordertype === 'total' ? '디자인+인쇄' : '인쇄만' | "인쇄만" |
| 4줄 | 프리미엄 | premium_options | foil, numbering, perforation | "넘버링:2개" |

**출력 예시**:
```
2도 / NCR 2도
4도 / 500권 / 인쇄만
프리미엄 옵션: 넘버링:2개
```

**⚠️ 중요**: NCR은 **필드 매핑이 다릅니다**!
- spec_type ← PN_type (다른 제품과 반대)
- spec_sides ← MY_type (다른 제품과 반대)

---

## 🔧 구현: 통합 매핑 함수

### **방법 1: ProductSpecFormatter에 통합 함수 추가**

```php
/**
 * 고정 양식에 맞춰 4줄로 표시
 * @param array $item 상품 데이터
 * @return array ['line1' => '', 'line2' => '', 'line3' => '', 'line4' => '']
 */
public function formatUnified($item) {
    $productType = $item['product_type'] ?? '';

    // 1줄: 제품종류 / 재질 / 규격
    $line1 = $this->buildLine1($item, $productType);

    // 2줄: 인쇄옵션 / 수량+단위 / 디자인
    $line2 = $this->buildLine2($item, $productType);

    // 3줄: 추가옵션 (코팅, 접지, 오시)
    $line3 = $this->buildLine3($item, $productType);

    // 4줄: 특수옵션 (프리미엄 옵션, 양면테이프)
    $line4 = $this->buildLine4($item, $productType);

    return [
        'line1' => $line1,
        'line2' => $line2,
        'line3' => $line3,
        'line4' => $line4
    ];
}

private function buildLine1($item, $productType) {
    $slot1 = $item['spec_type'] ?? '';
    $slot2 = $item['spec_material'] ?? '';
    $slot3 = $item['spec_size'] ?? '';

    return implode(' / ', array_filter([$slot1, $slot2, $slot3]));
}

private function buildLine2($item, $productType) {
    $slot1 = $item['spec_sides'] ?? '';  // 인쇄옵션
    $slot2 = $item['quantity_display'] ?? $this->calculateQuantity($item);  // 수량+단위
    $slot3 = $item['spec_design'] ?? '';  // 디자인

    return implode(' / ', array_filter([$slot1, $slot2, $slot3]));
}

private function buildLine3($item, $productType) {
    // 추가옵션 제품만
    if (!in_array($productType, ['inserted', 'leaflet', 'cadarok', 'littleprint', 'poster'])) {
        return '';
    }

    return $this->formatAdditionalOptions($item);
}

private function buildLine4($item, $productType) {
    // 프리미엄 옵션 제품
    if (in_array($productType, ['namecard', 'merchandisebond', 'ncrflambeau'])) {
        return $this->formatPremiumOptions($item);
    }

    // 봉투 양면테이프
    if ($productType === 'envelope') {
        if (!empty($item['envelope_tape_enabled']) && $item['envelope_tape_enabled'] == 1) {
            $qty = intval($item['envelope_tape_quantity'] ?? 0);
            return $qty > 0 ? "양면테이프: " . number_format($qty) . "개" : "양면테이프";
        }
    }

    return '';
}

private function calculateQuantity($item) {
    // 기존 formatQuantity() 또는 getQuantityDisplay() 사용
    return self::getQuantityDisplay($item);
}
```

---

### **방법 2: 슬롯 기반 매핑 테이블**

```php
/**
 * 제품별 슬롯 매핑 정의
 */
private static $slotMappings = [
    'namecard' => [
        'line1_slot1' => ['field' => 'spec_type', 'fallback' => 'MY_type_name'],
        'line1_slot2' => ['field' => 'spec_material', 'fallback' => 'Section_name'],
        'line1_slot3' => ['field' => 'spec_size', 'default' => '90mm x 50mm'],
        'line2_slot1' => ['field' => 'spec_sides', 'fallback' => 'POtype', 'transform' => 'sides'],
        'line2_slot2' => ['field' => 'quantity_display', 'calculate' => true],
        'line2_slot3' => ['field' => 'spec_design', 'fallback' => 'ordertype', 'transform' => 'design'],
        'line4' => ['type' => 'premium_options']
    ],
    'envelope' => [
        'line1_slot1' => ['field' => 'spec_type', 'fallback' => 'MY_type_name'],
        'line1_slot2' => ['field' => 'spec_material', 'fallback' => 'Section_name'],
        'line1_slot3' => ['field' => 'spec_size', 'default' => ''],
        'line2_slot1' => ['field' => 'spec_sides', 'fallback' => 'POtype_name'],  // ⚠️ 인쇄 색상!
        'line2_slot2' => ['field' => 'quantity_display', 'calculate' => true, 'multiply_1000' => true],
        'line2_slot3' => ['field' => 'spec_design', 'fallback' => 'ordertype', 'transform' => 'design'],
        'line4' => ['type' => 'envelope_tape']
    ],
    'inserted' => [
        'line1_slot1' => ['field' => 'spec_type', 'fallback' => 'MY_type_name'],
        'line1_slot2' => ['field' => 'spec_material', 'fallback' => 'MY_Fsd_name'],
        'line1_slot3' => ['field' => 'spec_size', 'fallback' => 'PN_type_name'],
        'line2_slot1' => ['field' => 'spec_sides', 'fallback' => 'POtype', 'transform' => 'sides'],
        'line2_slot2' => ['field' => 'quantity_display', 'priority' => 'dropdown', 'format' => 'ream'],
        'line2_slot3' => ['field' => 'spec_design', 'fallback' => 'ordertype', 'transform' => 'design'],
        'line3' => ['type' => 'additional_options']
    ],
    'sticker' => [
        'line1_slot1' => ['field' => 'spec_type', 'fallback' => 'domusong', 'transform' => 'remove_zero'],
        'line1_slot2' => ['field' => 'spec_material', 'fallback' => 'jong', 'transform' => 'remove_jil'],
        'line1_slot3' => ['field' => 'spec_size', 'calculate' => 'garo_sero'],
        'line2_slot1' => ['field' => 'spec_sides', 'default' => ''],
        'line2_slot2' => ['field' => 'quantity_display', 'fallback' => 'mesu', 'no_multiply' => true],  // ⚠️ ×1000 금지
        'line2_slot3' => ['field' => 'spec_design', 'fallback' => 'ordertype', 'transform' => 'design']
    ],
    'ncrflambeau' => [
        'line1_slot1' => ['field' => 'spec_type', 'fallback' => 'PN_type_name'],  // ⚠️ PN_type!
        'line1_slot2' => ['field' => 'spec_material', 'fallback' => 'MY_Fsd_name'],
        'line1_slot3' => ['field' => 'spec_size', 'default' => ''],
        'line2_slot1' => ['field' => 'spec_sides', 'fallback' => 'MY_type_name'],  // ⚠️ MY_type!
        'line2_slot2' => ['field' => 'quantity_display', 'calculate' => true, 'unit' => '권'],
        'line2_slot3' => ['field' => 'spec_design', 'fallback' => 'ordertype', 'transform' => 'design'],
        'line4' => ['type' => 'premium_options']
    ]
    // ... 나머지 제품들
];

/**
 * 슬롯 기반 렌더링
 */
public function renderBySlots($item) {
    $productType = $item['product_type'] ?? '';
    $mapping = self::$slotMappings[$productType] ?? [];

    $output = [];

    foreach (['line1', 'line2', 'line3', 'line4'] as $line) {
        $parts = [];

        for ($i = 1; $i <= 3; $i++) {
            $slotKey = "{$line}_slot{$i}";
            if (isset($mapping[$slotKey])) {
                $value = $this->extractSlotValue($item, $mapping[$slotKey]);
                if (!empty($value)) {
                    $parts[] = $value;
                }
            }
        }

        // 특수 라인 (3줄, 4줄)
        if (isset($mapping[$line]) && isset($mapping[$line]['type'])) {
            $specialValue = $this->renderSpecialLine($item, $mapping[$line]['type']);
            if (!empty($specialValue)) {
                $output[$line] = $specialValue;
            }
        } else {
            $output[$line] = implode(' / ', $parts);
        }
    }

    return $output;
}

private function extractSlotValue($item, $slotConfig) {
    // 1순위: 표준 필드
    if (isset($item[$slotConfig['field']]) && !empty($item[$slotConfig['field']])) {
        return $item[$slotConfig['field']];
    }

    // 2순위: fallback 필드
    if (isset($slotConfig['fallback'])) {
        $value = $item[$slotConfig['fallback']] ?? '';

        // transform 적용
        if (!empty($slotConfig['transform'])) {
            $value = $this->transformValue($value, $slotConfig['transform'], $item);
        }

        return $value;
    }

    // 3순위: 기본값
    if (isset($slotConfig['default'])) {
        return $slotConfig['default'];
    }

    // 4순위: 계산
    if (isset($slotConfig['calculate']) && $slotConfig['calculate'] === true) {
        return $this->calculateQuantity($item, $slotConfig);
    }

    return '';
}

private function transformValue($value, $transformType, $item) {
    switch ($transformType) {
        case 'sides':
            return $value == '1' ? '단면' : '양면';
        case 'design':
            return $value === 'total' ? '디자인+인쇄' : '인쇄만';
        case 'remove_jil':
            return preg_replace('/^jil\s*/i', '', $value);
        case 'remove_zero':
            return preg_replace('/^[0\s]+/', '', $value);
        default:
            return $value;
    }
}
```

---

## 🎨 HTML 출력 템플릿

### **장바구니 / 주문서 표시**

```php
<div class="product-display">
    <?php
    $unified = $specFormatter->formatUnified($item);
    ?>

    <!-- 1줄: 규격 -->
    <div class="spec-line spec-line-1">
        <?= htmlspecialchars($unified['line1']) ?>
    </div>

    <!-- 2줄: 옵션 -->
    <div class="spec-line spec-line-2">
        <?= htmlspecialchars($unified['line2']) ?>
    </div>

    <!-- 3줄: 추가옵션 (있는 경우만) -->
    <?php if (!empty($unified['line3'])): ?>
    <div class="spec-line spec-line-3 additional">
        <span class="icon">➕</span>
        <?= htmlspecialchars($unified['line3']) ?>
    </div>
    <?php endif; ?>

    <!-- 4줄: 특수옵션 (있는 경우만) -->
    <?php if (!empty($unified['line4'])): ?>
    <div class="spec-line spec-line-4 special">
        <span class="icon">✨</span>
        <?= htmlspecialchars($unified['line4']) ?>
    </div>
    <?php endif; ?>
</div>
```

### **CSS 스타일**

```css
.product-display {
    line-height: 1.6;
    font-size: 14px;
}

.spec-line {
    padding: 4px 0;
}

.spec-line-1 {
    font-weight: 600;
    color: #333;
}

.spec-line-2 {
    color: #666;
}

.spec-line-3.additional {
    color: #2e7d32;
    background: #e8f5e9;
    padding: 6px 10px;
    border-radius: 4px;
    margin-top: 4px;
}

.spec-line-4.special {
    color: #e65100;
    background: #fff3e0;
    padding: 6px 10px;
    border-radius: 4px;
    margin-top: 4px;
}

.icon {
    margin-right: 4px;
}
```

---

## ✅ 검증 체크리스트

각 제품을 고정 양식으로 변환할 때 확인 사항:

| 항목 | 확인 내용 |
|------|----------|
| **1줄 완성도** | 제품종류/재질/규격 3개 중 최소 2개 이상 있는가? |
| **2줄 수량** | quantity_display에 단위가 포함되어 있는가? ("500매", "1연") |
| **천 단위 변환** | 명함/봉투의 MY_amount < 10 → ×1000 적용했는가? |
| **스티커 수량** | mesu를 ×1000 하지 않았는가? |
| **전단지 우선순위** | dropdown의 quantity_display를 최우선 사용했는가? |
| **봉투 spec_sides** | POtype를 "단면/양면"이 아니라 "마스터1도/칼라4도"로 표시하는가? |
| **NCR 필드 매핑** | PN_type→spec_type, MY_type→spec_sides로 올바르게 매핑했는가? |
| **빈 줄 제거** | 3줄/4줄이 빈 경우 표시하지 않는가? |
| **특수문자 이스케이프** | htmlspecialchars() 적용했는가? |

---

## 📊 마이그레이션 전략

### **Phase 1: 신규 데이터 (data_version=2)**
→ 이미 표준 필드가 있으므로 `formatUnified()` 바로 사용 가능

### **Phase 2: 레거시 데이터 (data_version IS NULL)**
→ `formatUnified()`가 자동으로 fallback 필드 사용

### **Phase 3: 혼합 데이터**
→ 슬롯 매핑 테이블이 자동으로 우선순위 처리:
1. 표준 필드 (spec_type, spec_material 등)
2. 레거시 필드 (MY_type_name, Section_name 등)
3. 기본값 또는 계산값

---

## 🎯 기대 효과

1. **일관성**: 모든 제품이 동일한 4줄 구조
2. **가독성**: 슬롯 기반 정렬로 정보 파악 용이
3. **유지보수성**: 중앙 매핑 테이블로 변경 간편
4. **확장성**: 신규 제품 추가 시 매핑만 추가
5. **호환성**: 레거시 데이터도 자동 변환

---

**작성자**: Claude Code
**문서 버전**: 1.0
**적용 대상**: 11개 전체 제품
