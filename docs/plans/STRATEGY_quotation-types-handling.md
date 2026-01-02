# 견적서 유형별 처리 전략

**작성일**: 2025-12-26
**목적**: 일반인/관리자 자동계산/관리자 수동입력 견적서의 주문 전환 처리 방안

---

## 📊 1. 견적서 유형 분석

### 유형 A: 일반인 자동계산 견적서 ✅
```
플로우:
제품 페이지(?mode=quotation)
→ 옵션 선택
→ 계산기 API 호출
→ quotation_temp 저장
→ 견적서 생성
```

**특징**:
- ✅ `product_type`: 명확 (inserted, namecard 등 9개 중 하나)
- ✅ `MY_type`, `Section`, `quantity` 등 모든 필드 완전
- ✅ 가격 출처: `mlangprintauto_[product]` 테이블
- ✅ 주문 전환: **문제 없음** (모든 데이터 완비)

**DB 예시**:
```sql
quote_items 테이블:
- product_type: 'namecard'
- product_name: '명함'
- MY_type: '123' (일반명함 코드)
- Section: '456' (재질 코드)
- quantity: 500
- supply_price: 45000 (계산기에서 자동 계산)
```

---

### 유형 B: 관리자 자동계산 견적서 ✅
```
플로우:
관리자 견적 생성 페이지
→ 품목 추가 버튼
→ 제품 선택 모달 (계산기 포함)
→ 자동 계산
→ 견적서 저장
```

**특징**:
- ✅ 일반인과 동일한 계산기 사용
- ✅ 모든 필드 완전
- ✅ 주문 전환: **문제 없음**

**DB 예시**:
```sql
quote_items 테이블:
(유형 A와 동일)
```

---

### 유형 C: 관리자 수동입력 견적서 ⚠️
```
플로우:
관리자 견적 생성 페이지
→ "직접 입력" 품목 추가
→ 품목명, 수량, 단가 수동 입력
→ 견적서 저장
```

**특징**:
- ⚠️ `product_type`: **'custom'** (9개 제품 아님)
- ⚠️ 제품 스펙 필드 없음 (MY_type, Section 등 NULL)
- ⚠️ 가격: 관리자가 직접 입력한 값
- ❌ **주문 전환 시 문제**: 어떤 주문 테이블로 넣을지 불명확

**DB 예시**:
```sql
quote_items 테이블:
- product_type: 'custom'
- product_name: '특수 배너 인쇄' (자유 텍스트)
- specification: '3m x 2m, 실사출력' (자유 텍스트)
- quantity: 1
- supply_price: 150000 (관리자 직접 입력)
- MY_type: NULL
- Section: NULL
- mesu: NULL
```

---

## ⚠️ 2. 핵심 문제점

### 문제: 수동입력 견적서의 주문 전환
**현재 주문 시스템**:
```
mlangorder_printauto 테이블:
- ThingCate: 'NameCard', 'inserted' 등 고정된 9개 값만 가능
- Type_1: JSON 필드 (제품 스펙 저장)
```

**충돌**:
1. **ThingCate 불일치**: 'custom' 제품은 어떤 값으로 저장?
2. **Type_1 데이터 부족**: MY_type, Section 등 필수 필드가 NULL
3. **주문 처리 로직 미지원**: 기존 ProcessOrder_unified.php는 9개 제품만 처리

---

## 🎯 3. 해결 전략 (3가지 옵션)

### 옵션 1: 수동입력 품목을 특정 제품으로 강제 매핑 ❌ 비추천
```
전략:
모든 custom 제품을 'inserted'(전단지)로 매핑
주문 테이블에 ThingCate='inserted'로 저장
```

**장점**:
- 기존 시스템 수정 최소화

**단점**:
- ❌ 데이터 의미 왜곡 (배너가 전단지로 표시)
- ❌ 통계 부정확 (전단지 매출 뻥튀기)
- ❌ 주문 처리 시 혼란

---

### 옵션 2: 별도 주문 테이블 생성 ⚠️ 중간
```
전략:
custom_orders 테이블 신규 생성
수동입력 견적서는 별도 테이블로 주문 전환
```

**장점**:
- ✅ 데이터 의미 명확
- ✅ 기존 시스템 영향 없음

**단점**:
- ⚠️ 주문 통합 조회 복잡 (JOIN 필요)
- ⚠️ 주문 처리 로직 2벌 유지
- ⚠️ 관리자 페이지 2곳 관리

---

### 옵션 3: 주문 테이블 확장 (ThingCate 유연화) ✅ **권장**
```
전략:
mlangorder_printauto 테이블:
- ThingCate ENUM 확장: 기존 9개 + 'custom'
- is_custom_product TINYINT(1) 추가 (0=표준, 1=수동입력)
- custom_product_name VARCHAR(255) 추가
- custom_specification TEXT 추가
```

**장점**:
- ✅ **단일 테이블 유지** (통합 조회 용이)
- ✅ 기존 제품 처리 방식 유지
- ✅ 확장성 (향후 추가 제품 대응)
- ✅ 통계 정확성 (custom 품목 구분)

**단점**:
- ⚠️ DB 스키마 변경 필요
- ⚠️ 기존 쿼리 일부 수정 (WHERE ThingCate != 'custom')

---

## 🏗️ 4. 권장 아키텍처 (옵션 3 상세)

### 4.1. DB 스키마 변경

#### mlangorder_printauto 테이블 수정
```sql
-- 1. ThingCate ENUM 확장
ALTER TABLE mlangorder_printauto
MODIFY COLUMN ThingCate ENUM(
    'NameCard', 'inserted', 'envelope', 'sticker',
    'msticker', 'cadarok', 'LittlePrint',
    'MerchandiseBond', 'NcrFlambeau',
    'custom'  -- 신규 추가
) DEFAULT 'NameCard';

-- 2. 수동입력 구분 플래그
ALTER TABLE mlangorder_printauto
ADD COLUMN is_custom_product TINYINT(1) DEFAULT 0
COMMENT '0=표준제품, 1=수동입력제품'
AFTER ThingCate;

-- 3. 수동입력 품목 정보
ALTER TABLE mlangorder_printauto
ADD COLUMN custom_product_name VARCHAR(255) NULL
COMMENT '수동입력 품목명'
AFTER is_custom_product;

ALTER TABLE mlangorder_printauto
ADD COLUMN custom_specification TEXT NULL
COMMENT '수동입력 규격/사양'
AFTER custom_product_name;

-- 4. 인덱스 추가
CREATE INDEX idx_is_custom ON mlangorder_printauto(is_custom_product);
```

#### quotes 테이블 수정 (견적서 유형 구분)
```sql
-- 견적서 생성 방식 구분
ALTER TABLE quotes
ADD COLUMN quote_source ENUM('customer', 'admin_auto', 'admin_manual')
DEFAULT 'customer'
COMMENT 'customer=고객, admin_auto=관리자자동, admin_manual=관리자수동'
AFTER quote_type;

-- 인덱스
CREATE INDEX idx_quote_source ON quotes(quote_source);
```

#### quote_items 테이블 수정
```sql
-- product_type에 'custom' 명시적 추가 (이미 있을 수 있음)
ALTER TABLE quote_items
MODIFY COLUMN product_type VARCHAR(50) DEFAULT 'custom';

-- 수동입력 여부 플래그
ALTER TABLE quote_items
ADD COLUMN is_manual_entry TINYINT(1) DEFAULT 0
COMMENT '0=자동계산, 1=수동입력'
AFTER product_type;
```

---

### 4.2. 주문 전환 로직 개선

#### convert_to_order.php 수정
```php
foreach ($items as $item) {
    // 제품 타입 판단
    $isCustomProduct = false;
    $thingCate = '';

    if (!empty($item['product_type']) && $item['product_type'] !== 'custom') {
        // 표준 제품 (9개 중 하나)
        $thingCate = mapProductTypeToThingCate($item['product_type']);
        $isCustomProduct = false;
    } else {
        // 수동입력 제품
        $thingCate = 'custom';
        $isCustomProduct = true;
    }

    // Type_1 JSON 생성
    if ($isCustomProduct) {
        // 수동입력 제품: 최소 데이터
        $type1Data = [
            'product_type' => 'custom',
            'product_name' => $item['product_name'],
            'specification' => $item['specification'] ?? '',
            'quantity' => floatval($item['quantity']),
            'unit' => $item['unit'] ?? '개',
            'source' => 'quote_manual'
        ];
    } else {
        // 표준 제품: 전체 데이터 (기존 로직)
        $type1Data = [
            'product_type' => $item['product_type'],
            'MY_type' => $item['MY_type'],
            'Section' => $item['Section'],
            // ... (모든 필드)
        ];
    }

    // mlangorder_printauto INSERT
    $query = "INSERT INTO mlangorder_printauto (
        ThingCate,
        is_custom_product,
        custom_product_name,
        custom_specification,
        Type_1,
        St_Price,
        St_PriceVat,
        -- ... 기타 필드
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ...)";

    $stmt = mysqli_prepare($db, $query);

    if ($isCustomProduct) {
        // 수동입력 제품
        mysqli_stmt_bind_param($stmt, 'sissiii',
            'custom',  // ThingCate
            1,         // is_custom_product
            $item['product_name'],      // custom_product_name
            $item['specification'],     // custom_specification
            json_encode($type1Data),
            $item['supply_price'],
            $item['total_price']
        );
    } else {
        // 표준 제품
        mysqli_stmt_bind_param($stmt, 'sissiii',
            $thingCate,
            0,         // is_custom_product
            null,      // custom_product_name
            null,      // custom_specification
            json_encode($type1Data),
            $item['supply_price'],
            $item['total_price']
        );
    }

    mysqli_stmt_execute($stmt);
}
```

---

### 4.3. 관리자 견적 생성 UI

#### 품목 추가 모달 (2가지 탭)
```html
<div class="quote-item-add-modal">
    <ul class="tab-menu">
        <li class="active" data-tab="auto">자동 계산</li>
        <li data-tab="manual">직접 입력</li>
    </ul>

    <!-- 탭 1: 자동 계산 -->
    <div class="tab-content" id="tab-auto">
        <select name="product_type">
            <option value="namecard">명함</option>
            <option value="inserted">전단지</option>
            <!-- ... 9개 제품 -->
        </select>

        <!-- 선택한 제품의 계산기 동적 로드 -->
        <div id="calculator-container"></div>

        <button class="btn-add-calculated">계산 결과 추가</button>
    </div>

    <!-- 탭 2: 직접 입력 -->
    <div class="tab-content hidden" id="tab-manual">
        <input type="text" name="product_name" placeholder="품목명 (예: 특수 배너 인쇄)">
        <textarea name="specification" placeholder="규격/사양 (예: 3m x 2m, 실사출력)"></textarea>
        <input type="number" name="quantity" placeholder="수량">
        <input type="text" name="unit" placeholder="단위 (예: 개, 매)">
        <input type="number" name="supply_price" placeholder="공급가액">

        <button class="btn-add-manual">직접 입력 품목 추가</button>
    </div>
</div>
```

#### JavaScript 처리
```javascript
// 자동 계산 품목 추가
document.querySelector('.btn-add-calculated').addEventListener('click', () => {
    const priceData = window.currentPriceData;
    const productType = document.querySelector('[name="product_type"]').value;

    addQuoteItem({
        product_type: productType,
        is_manual_entry: 0,  // 자동 계산
        // ... 계산기 데이터
    });
});

// 수동 입력 품목 추가
document.querySelector('.btn-add-manual').addEventListener('click', () => {
    const formData = {
        product_type: 'custom',
        is_manual_entry: 1,  // 수동 입력
        product_name: document.querySelector('[name="product_name"]').value,
        specification: document.querySelector('[name="specification"]').value,
        quantity: document.querySelector('[name="quantity"]').value,
        unit: document.querySelector('[name="unit"]').value,
        supply_price: document.querySelector('[name="supply_price"]').value,
        vat_amount: Math.round(supply_price * 0.1),
        total_price: supply_price + vat_amount
    };

    addQuoteItem(formData);
});
```

---

### 4.4. 주문 조회 UI 개선

#### 관리자 주문 목록
```php
// 기존: ThingCate만 표시
// 개선: 수동입력 여부 표시
<?php foreach ($orders as $order): ?>
    <tr>
        <td><?php echo $order['No']; ?></td>
        <td>
            <?php if ($order['is_custom_product']): ?>
                <span class="badge badge-custom">수동입력</span>
                <?php echo htmlspecialchars($order['custom_product_name']); ?>
            <?php else: ?>
                <?php echo getProductName($order['ThingCate']); ?>
            <?php endif; ?>
        </td>
        <td><?php echo number_format($order['St_PriceVat']); ?>원</td>
    </tr>
<?php endforeach; ?>
```

---

## 📈 5. 데이터 플로우 비교

### Before (현재)
```
[일반인 견적] → quotation_temp → quotes/quote_items → ❌ 주문 전환 불가능 (수동입력)
[관리자 견적] → 수동 입력 → quotes/quote_items → ❌ 주문 전환 불가능
```

### After (개선)
```
[일반인 자동] → quotation_temp → quotes/quote_items (is_manual=0)
               → mlangorder_printauto (ThingCate='namecard', is_custom=0) ✅

[관리자 자동] → 계산기 → quotes/quote_items (is_manual=0)
               → mlangorder_printauto (ThingCate='inserted', is_custom=0) ✅

[관리자 수동] → 직접입력 → quotes/quote_items (is_manual=1, product_type='custom')
               → mlangorder_printauto (ThingCate='custom', is_custom=1) ✅
```

---

## 🔧 6. 구현 단계

### Phase A: DB 스키마 확장 (1일)
- [ ] mlangorder_printauto 테이블 수정 (ThingCate, is_custom_product 등)
- [ ] quotes/quote_items 테이블 수정 (quote_source, is_manual_entry)
- [ ] 마이그레이션 스크립트 작성 (기존 데이터 is_custom=0 설정)
- [ ] 백업 및 롤백 계획

### Phase B: 주문 전환 로직 개선 (2일)
- [ ] convert_to_order.php 수정 (custom 제품 처리)
- [ ] mapProductTypeToThingCate() 함수 작성
- [ ] 주문 생성 로직 분기 (표준 vs 수동)
- [ ] 단위 테스트 (표준/수동 각각 테스트)

### Phase C: 관리자 견적 생성 UI (2-3일)
- [ ] 품목 추가 모달 2탭 구조 (자동/수동)
- [ ] 계산기 동적 로드 기능
- [ ] 수동 입력 폼 검증
- [ ] AJAX 처리 로직

### Phase D: 주문 조회 UI 개선 (1일)
- [ ] 수동입력 주문 표시 개선
- [ ] 필터 추가 (표준/수동 구분)
- [ ] 통계 분리 (표준 제품 매출 vs 수동입력 매출)

### Phase E: 통합 테스트 (1일)
- [ ] E2E 테스트: 일반인 자동 견적 → 주문
- [ ] E2E 테스트: 관리자 자동 견적 → 주문
- [ ] E2E 테스트: 관리자 수동 견적 → 주문
- [ ] 회귀 테스트: 기존 주문 처리 정상 작동

---

## ⚖️ 7. 장단점 종합 비교

| 항목 | 옵션 1 (강제 매핑) | 옵션 2 (별도 테이블) | 옵션 3 (테이블 확장) ✅ |
|------|-------------------|---------------------|------------------------|
| **구현 난이도** | 🟢 쉬움 | 🟡 중간 | 🟡 중간 |
| **데이터 정확성** | 🔴 낮음 | 🟢 높음 | 🟢 높음 |
| **통합 조회** | 🟢 쉬움 | 🔴 어려움 (JOIN) | 🟢 쉬움 |
| **확장성** | 🔴 없음 | 🟡 제한적 | 🟢 우수 |
| **유지보수성** | 🔴 낮음 | 🔴 낮음 (2벌 로직) | 🟢 높음 |
| **통계 정확성** | 🔴 부정확 | 🟢 정확 | 🟢 정확 |

---

## 💡 8. 추가 개선 아이디어

### 8.1. 수동입력 품목 템플릿
```
관리자가 자주 사용하는 수동입력 품목을 템플릿으로 저장
예: "현수막 3mx2m", "배너 거치대" 등

custom_product_templates 테이블:
- template_name: '현수막 표준형'
- default_specification: '3m x 2m, 실사출력'
- default_unit: '개'
- estimated_price: 150000
```

### 8.2. 수동입력 가격 가이드
```
관리자가 수동입력 시 참고할 수 있는 가격 가이드
- 과거 유사 품목 평균가
- 원가 계산 도우미
```

### 8.3. 주문 전환 승인 프로세스
```
수동입력 견적서는 주문 전환 시 관리자 승인 필요
- 가격 재확인
- 제작 가능 여부 확인
```

---

## ✅ 9. 권장사항 요약

### 최종 권장: **옵션 3 (주문 테이블 확장)**

**이유**:
1. ✅ **단일 테이블**: 통합 조회/통계 용이
2. ✅ **데이터 무결성**: custom 품목 명확히 구분
3. ✅ **확장성**: 향후 제품 추가 대응
4. ✅ **유지보수**: 로직 단일화

**예상 효과**:
- 관리자 견적 작성 시간 **50% 감소** (자동/수동 선택 가능)
- 주문 전환율 **100%** (모든 견적서 전환 가능)
- 매출 통계 정확도 **향상** (custom 품목 분리)

**예상 소요**:
- 총 **7-8일** (1주일)
- Phase A(1일) → Phase B(2일) → Phase C(2-3일) → Phase D(1일) → Phase E(1일)

---

## 🚀 10. 다음 단계

이 전략에 동의하시면:
1. Phase A부터 시작 (DB 스키마 확장)
2. 상세 구현 계획 문서 생성
3. 단계별 체크리스트 작성

---

**이 전략이 합리적인지 승인을 요청드립니다.** ✅
