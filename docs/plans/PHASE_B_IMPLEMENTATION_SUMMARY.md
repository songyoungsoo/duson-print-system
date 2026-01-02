# Phase B: 견적→주문 전환 로직 개선

**작성일**: 2025-12-26  
**목적**: 견적서 주문 전환 시 product_type 및 is_custom_product 저장 로직 추가  
**완료일**: 2025-12-26

---

## 📋 변경 사항 요약

### 1. convert_to_order.php (견적서→주문 전환)

**파일**: `/var/www/html/mlangprintauto/quote/api/convert_to_order.php`

#### 변경 내용:
1. **is_custom_product 자동 결정 로직 추가** (Line 118-119)
   ```php
   // is_custom_product 자동 결정 (Phase B)
   $isCustomProduct = ($type === 'custom') ? 1 : 0;
   ```

2. **INSERT 쿼리에 필드 추가** (Line 209-227)
   - `product_type` 필드 추가
   - `is_custom_product` 필드 추가
   - 26개 파라미터 (24개 → 26개)

3. **bind_param 수정** (Line 244-254)
   - 타입 문자열: `"sssiiiiiisssssssssisissdssi"` (26개)
   - product_type과 is_custom_product 값 바인딩

---

### 2. ProcessOrder_unified.php (일반 주문 생성)

**파일**: `/var/www/html/mlangorder_printauto/ProcessOrder_unified.php`

#### 변경 내용:
1. **product_type 및 is_custom_product 변수 준비** (Line 530-532)
   ```php
   // 🆕 Phase B: product_type과 is_custom_product 설정
   $product_type = $item['product_type'] ?? 'custom';
   $is_custom_product = ($product_type === 'custom') ? 1 : 0;
   ```

2. **INSERT 쿼리에 필드 추가** (Line 439-449)
   - `product_type` 필드 추가
   - `is_custom_product` 필드 추가
   - 37개 파라미터 (35개 → 37개)

3. **bind_param 수정** (Line 603-615)
   - 타입 문자열: `"isssssssssssssssssiisisissiiiiisiiiiis"` (37개)
   - product_type과 is_custom_product 값 바인딩

---

## 🔄 데이터 흐름

### 견적서 → 주문 전환 흐름

```
quote_items.product_type
    ↓
convert_to_order.php
    ↓ (자동 결정)
$type (namecard/inserted/sticker/envelope/msticker/cadarok/littleprint/merchandisebond/ncrflambeau/custom)
    ↓
$isCustomProduct = ($type === 'custom') ? 1 : 0
    ↓
INSERT INTO mlangorder_printauto
    (product_type, is_custom_product)
```

### 일반 주문 생성 흐름

```
shop_temp.product_type
    ↓
ProcessOrder_unified.php
    ↓
$product_type = $item['product_type'] ?? 'custom'
    ↓
$is_custom_product = ($product_type === 'custom') ? 1 : 0
    ↓
INSERT INTO mlangorder_printauto
    (product_type, is_custom_product)
```

---

## 🧪 검증 방법

### 1. 견적서→주문 전환 테스트

```sql
-- 견적서에서 전환된 주문 확인
SELECT 
    no,
    quote_no,
    product_type,
    is_custom_product,
    custom_product_name,
    custom_specification
FROM mlangorder_printauto
WHERE quote_no IS NOT NULL
ORDER BY date DESC
LIMIT 10;
```

### 2. 일반 주문 확인

```sql
-- 최근 주문의 product_type 확인
SELECT 
    no,
    product_type,
    is_custom_product,
    Type,
    name,
    date
FROM mlangorder_printauto
WHERE date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY date DESC
LIMIT 10;
```

### 3. custom 제품 확인

```sql
-- custom 제품 주문 확인
SELECT 
    no,
    product_type,
    is_custom_product,
    custom_product_name,
    custom_specification,
    name,
    date
FROM mlangorder_printauto
WHERE is_custom_product = 1
ORDER BY date DESC
LIMIT 10;
```

---

## ⚠️ 주의사항

### 1. 기존 주문 데이터
- Phase A 마이그레이션 시 모든 기존 주문은 `product_type=NULL`, `is_custom_product=0`으로 설정됨
- 이후 생성되는 주문부터만 정확한 product_type이 저장됨

### 2. custom 제품 처리
- `product_type='custom'`인 경우 자동으로 `is_custom_product=1`로 설정
- `custom_product_name`과 `custom_specification` 필드에 제품 정보 저장

### 3. 9개 표준 제품
- namecard, inserted, envelope, sticker, msticker, cadarok, littleprint, merchandisebond, ncrflambeau
- 이들 제품은 자동으로 `is_custom_product=0`으로 설정

---

## 📊 예상 효과

### 긍정적 영향
- ✅ 제품 타입별 통계 분석 가능
- ✅ 표준 제품과 수동입력 제품 구분 가능
- ✅ 견적서 출처 추적 가능 (quote_no, quote_item_id)
- ✅ 주문 조회 UI에서 제품 타입별 필터링 가능

### 다음 단계 (Phase C)
- **관리자 견적 생성 UI**: 자동계산/수동입력 모드 지원
- **견적서 관리**: quote_source 기반 필터링 및 통계
- **주문 조회 개선**: product_type 기반 필터링

---

## 🔗 관련 문서

- Phase A: `/var/www/html/database/migrations/phase_a_custom_products/README.md`
- 전체 전략: `/var/www/html/docs/plans/STRATEGY_quotation-types-handling.md`

---

**Last Updated**: 2025-12-26  
**Status**: ✅ 완료
