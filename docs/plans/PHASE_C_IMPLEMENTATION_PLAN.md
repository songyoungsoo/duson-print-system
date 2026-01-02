# Phase C: 관리자 견적 생성 quote_source 추적

**작성일**: 2025-12-26
**목적**: 견적서 생성 출처 추적 (customer/admin_auto/admin_manual)
**의존성**: Phase A (DB 스키마), Phase B (주문 전환 로직)

---

## 📋 구현 목표

1. **quote_source 자동 설정**: 견적서 생성 시 출처 구분
   - `customer`: 고객이 장바구니에서 견적 요청
   - `admin_auto`: 관리자가 장바구니/계산기에서 견적 생성
   - `admin_manual`: 관리자가 수동 입력으로 견적 생성

2. **is_manual_entry 자동 설정**: 품목별 입력 방식 구분
   - `0`: 계산기/장바구니에서 자동 계산된 품목
   - `1`: 관리자가 직접 입력한 품목 (custom 제품)

---

## 🔄 데이터 흐름

### 시나리오 1: 고객 견적 요청
```
shop_temp (장바구니)
  ↓
create.php (?from=cart)
  ↓
save.php (fromCart=true, admin=false)
  ↓
QuoteManager::createFromCart()
  → quote_source = 'customer'
  → items: is_manual_entry = 0
```

### 시나리오 2: 관리자 자동계산 견적
```
shop_temp (관리자가 계산기 사용)
quotation_temp (계산기 모달)
  ↓
create.php (?from=cart, admin=true)
  ↓
save.php (fromCart=true, admin=true)
  ↓
QuoteManager::createFromCart()
  → quote_source = 'admin_auto'
  → items: is_manual_entry = 0
```

### 시나리오 3: 관리자 수동입력 견적
```
create.php (빈 견적서, admin=true)
  ↓
관리자가 품목명/가격 직접 입력
  ↓
save.php (fromCart=false, admin=true)
  ↓
QuoteManager::createEmpty()
  → quote_source = 'admin_manual'
  → items: is_manual_entry = 1
```

---

## 🛠️ 수정 파일 목록

### 1. QuoteManager.php
**파일**: `/var/www/html/mlangprintauto/quote/includes/QuoteManager.php`

#### 수정 1: createFromCart() 메서드 (Line 141-198)
```php
// 🆕 Phase C: quote_source 결정
$quoteSource = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true
    ? 'admin_auto'
    : 'customer';

$query = "INSERT INTO quotes (
    quote_no, quote_type, public_token, session_id,
    customer_name, customer_company, customer_phone, customer_email, recipient_email,
    delivery_type, delivery_address, delivery_price, delivery_vat,
    supply_total, vat_total, discount_amount, discount_reason, grand_total,
    payment_terms, valid_days, valid_until,
    notes, status, created_by, quote_source  -- 🆕 추가
) VALUES (?, 'quotation', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)";  -- 🆕 23개 파라미터

// bind_param: 22개 → 23개
mysqli_stmt_bind_param($stmt, "ssssssssssiiiisissssis",  // 🆕 's' 추가
    $quoteNo, $publicToken, $sessionId,
    $customerName, $customerCompany, $customerPhone, $customerEmail, $recipientEmail,
    $deliveryType, $deliveryAddress,
    $deliveryPrice, $deliveryVat, $supplyTotal, $vatTotal, $discountAmount,
    $discountReason, $grandTotal,
    $paymentTerms, $validDays, $validUntil,
    $notes, $createdBy,
    $quoteSource  // 🆕 추가
);
```

#### 수정 2: createEmpty() 메서드 (Line 284-342)
```php
// 🆕 Phase C: quote_source 결정
$quoteSource = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true
    ? 'admin_manual'  // 빈 견적서 = 수동입력
    : 'customer';

$query = "INSERT INTO quotes (
    quote_no, quote_type, public_token,
    customer_name, customer_company, customer_phone, customer_email, recipient_email,
    delivery_type, delivery_address, delivery_price, delivery_vat,
    supply_total, vat_total, discount_amount, discount_reason, grand_total,
    payment_terms, valid_days, valid_until,
    notes, status, created_by, quote_source  -- 🆕 추가
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)";  -- 🆕 23개 파라미터

// bind_param: 22개 → 23개
mysqli_stmt_bind_param($stmt, "ssssssssssiiiisissssis",  // 🆕 's' 추가
    $quoteNo, $quoteType, $publicToken,
    $customerName, $customerCompany, $customerPhone, $customerEmail, $recipientEmail,
    $deliveryType, $deliveryAddress,
    $deliveryPrice, $deliveryVat, $supplyTotal, $vatTotal, $discountAmount,
    $discountReason, $grandTotal,
    $paymentTerms, $validDays, $validUntil,
    $notes, $createdBy,
    $quoteSource  // 🆕 추가
);
```

#### 수정 3: addManualItem() 메서드 (Line 697-720)
```php
// 🆕 Phase C: is_manual_entry 설정
$isManualEntry = 1;  // 수동입력 품목

$query = "INSERT INTO quote_items (
    quote_id, item_no, product_type, product_name, specification,
    quantity, unit, unit_price, supply_price, vat_amount, total_price,
    source_type, notes, is_manual_entry  -- 🆕 추가
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";  -- 🆕 14개 파라미터

// bind_param: 13개 → 14개
mysqli_stmt_bind_param($stmt, "iisssdsdiiissi",  // 🆕 'i' 추가
    $quoteId, $itemNo,
    $productType, $productName, $specification,
    $quantity, $unit,
    $unitPrice, $supplyPrice, $vatAmount, $totalPrice,
    $sourceType, $notes,
    $isManualEntry  // 🆕 추가
);
```

#### 수정 4: addItemFromCart() 메서드 확인
- is_manual_entry = 0 (자동계산)

#### 수정 5: addItemFromQuoteTemp() 메서드 확인
- is_manual_entry = 0 (자동계산)

---

## 🧪 테스트 시나리오

### 1. 고객 견적 요청 테스트
```sql
-- 장바구니 추가 (고객 세션)
INSERT INTO shop_temp (...) VALUES (...);

-- 견적서 생성
-- Expected: quote_source = 'customer', is_manual_entry = 0
```

### 2. 관리자 자동계산 견적 테스트
```sql
-- quotation_temp 추가 (관리자 세션)
INSERT INTO quotation_temp (...) VALUES (...);

-- 견적서 생성
-- Expected: quote_source = 'admin_auto', is_manual_entry = 0
```

### 3. 관리자 수동입력 견적 테스트
```sql
-- 빈 견적서 생성 후 직접 품목 입력
-- Expected: quote_source = 'admin_manual', is_manual_entry = 1
```

---

## 📊 검증 쿼리

```sql
-- 1. quote_source 분포 확인
SELECT quote_source, COUNT(*) as count
FROM quotes
GROUP BY quote_source;

-- 2. is_manual_entry 분포 확인
SELECT is_manual_entry, COUNT(*) as count
FROM quote_items
GROUP BY is_manual_entry;

-- 3. 관리자 견적 상세 확인
SELECT
    q.id,
    q.quote_no,
    q.quote_source,
    qi.product_name,
    qi.is_manual_entry
FROM quotes q
LEFT JOIN quote_items qi ON q.id = qi.quote_id
WHERE q.quote_source IN ('admin_auto', 'admin_manual')
ORDER BY q.created_at DESC
LIMIT 10;
```

---

## ⚠️ 주의사항

1. **세션 체크**: `$_SESSION['admin_logged_in']`이 제대로 설정되어 있는지 확인
2. **bind_param 개수**: 파라미터 개수 불일치 시 데이터 손실 발생
3. **기존 데이터**: Phase A 마이그레이션 후 기존 견적서는 모두 `quote_source='customer'`
4. **custom 제품**: product_type이 'custom'인 경우 is_manual_entry=1이어야 함

---

## 🔗 관련 문서

- Phase A: `/var/www/html/database/migrations/phase_a_custom_products/README.md`
- Phase B: `/var/www/html/docs/plans/PHASE_B_IMPLEMENTATION_SUMMARY.md`
- 전체 전략: `/var/www/html/docs/plans/STRATEGY_quotation-types-handling.md`

---

**Last Updated**: 2025-12-26
**Status**: 📝 계획 수립 완료, 구현 대기
