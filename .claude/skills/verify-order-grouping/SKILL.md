---
name: verify-order-grouping
description: 건수(order_count)/그룹(order_group_id) 파이프라인 정합성을 검증합니다. 품목계산→장바구니→견적→주문→관리자→결제→배송까지 끊김 없이 데이터가 흐르는지 확인합니다. 건수/그룹 관련 PHP/JS 수정 후 사용.
---

# 건수/그룹 파이프라인 정합성 검증 (Order Grouping Pipeline Verification)

## Purpose

건수(order_count)와 그룹(order_group_id)이 전체 주문 파이프라인에서 끊김 없이 흐르는지 검증합니다:

1. **건수 파이프라인 연속성** — 품목계산 UI → 장바구니(shop_temp) → 견적위젯(quote_gauge) → 견적 API(quote_requests) → 주문(mlangorder_printauto)까지 order_count가 유실 없이 전달되는지
2. **그룹 키 정합성** — item_group_id(장바구니) → order_group_id(주문) 전환이 올바르게 수행되는지
3. **관리자 표시 일관성** — 관리자 주문 목록/상세에서 order_group_id를 조회하고, 건수 축약(×N건)과 그룹 묶음이 올바르게 렌더링되는지
4. **결제/배송 그룹 일괄 처리** — 결제 성공 시 그룹 전체 상태 업데이트, 배송 시 그룹 일괄 처리가 정상 작동하는지
5. **DB 스키마 정합성** — 필수 컬럼(order_group_id, order_group_seq, order_count, item_group_id 등)이 올바른 테이블에 존재하는지

## When to Run

- 건수(order_count) 관련 PHP/JS 코드를 수정한 후
- order_group_id 또는 item_group_id를 읽거나 쓰는 코드를 수정한 후
- 장바구니(cart.php, add_to_basket.php) 그룹 로직을 수정한 후
- 주문 처리(ProcessOrder_unified.php) 코드를 수정한 후
- 관리자 주문 목록(orderlist.php, dashboard/orders) 표시 로직을 수정한 후
- 결제/배송 그룹 일괄 처리 코드를 수정한 후

## Related Files

### 건수 파이프라인 (order_count)

| File | Purpose | 역할 |
|------|---------|------|
| `js/quote-gauge.js` | 견적 위젯 JS — 건수 UI 표시/가격 곱셈/전송/polling | 건수 SSOT (프론트) |
| `includes/quote_gauge.php` | 견적 위젯 PHP — 건수 행 HTML 렌더링 | 건수 UI |
| `includes/quote_request_api.php` | 견적 API — order_count INSERT + 이메일 건수 표시 | 건수 DB 저장 |
| `mlangprintauto/sticker_new/index.php` | 스티커 주문 페이지 — 건수 UI label | 건수 입력 |
| `css/sticker-inline-styles.css` | 스티커 인라인 스타일 — label-compact | 건수 스타일 |

### 그룹 키 파이프라인 (item_group_id → order_group_id)

| File | Purpose | 역할 |
|------|---------|------|
| `mlangprintauto/namecard/add_to_basket.php` | 명함 장바구니 — item_group_id 생성 | IG 생성 |
| `mlangprintauto/sticker_new/add_to_basket.php` | 스티커 장바구니 — item_group_id 생성 | IG 생성 |
| `mlangprintauto/cart.php` | 장바구니 — item_group_id별 그룹 표시 | IG 표시 |
| `includes/ensure_shop_temp_columns.php` | shop_temp 스키마 — item_group_id/item_group_seq 컬럼 | IG 스키마 |
| `mlangorder_printauto/ProcessOrder_unified.php` | 주문 처리 — GRP-xxx 생성, order_group_id/seq INSERT | IG→OG 전환 |
| `includes/ensure_order_table_columns.php` | mlangorder_printauto 스키마 — order_group_id/seq 컬럼 | OG 스키마 |

### 관리자 표시 (order_group_id 소비)

| File | Purpose | 역할 |
|------|---------|------|
| `dashboard/api/orders.php` | 대시보드 API — 주문 목록 데이터 | OG 조회 |
| `dashboard/orders/index.php` | 대시보드 UI — 주문 목록 렌더링 | OG 표시 |
| `admin/mlangprintauto/orderlist.php` | 레거시 관리자 목록 | OG 표시 |
| `admin/mlangprintauto/orderlist_improved.php` | 개선 관리자 목록 | OG 표시 |
| `mlangorder_printauto/OrderFormOrderTree.php` | 관리자 상세 — 그룹 주문 표시 | OG 상세 |
| `dashboard/orders/view.php` | 대시보드 상세 | OG 상세 |

### 결제/배송 그룹 일괄 처리

| File | Purpose | 역할 |
|------|---------|------|
| `payment/inicis_return.php` | 결제 완료 — 그룹 전체 상태 업데이트 | OG 일괄 |
| `payment/inicis_request.php` | 결제 요청 — 그룹 금액 합산 | OG 합산 |
| `includes/shipping_api.php` | 배송 API — 그룹 일괄 배송 업데이트 | OG 배송 |
| `mypage/order_detail.php` | 마이페이지 — 그룹 주문 표시 | OG 고객 |
| `mlangprintauto/quote/api/convert_to_order.php` | 견적→주문 변환 — GRP 생성 | OG 생성 |

## Workflow

### Step 1: DB 스키마 검증

**도구:** Bash, Read

shop_temp 테이블에 item_group_id 관련 컬럼이 정의되어 있는지 확인합니다:

```bash
grep -n "item_group_id\|item_group_seq" includes/ensure_shop_temp_columns.php
```

**PASS:** `item_group_id VARCHAR(50)`, `item_group_seq INT` 정의가 존재
**FAIL:** 컬럼 정의 누락 → 장바구니 그룹 기능 작동 불가

mlangorder_printauto 테이블에 order_group 관련 컬럼이 정의되어 있는지 확인합니다:

```bash
grep -n "order_group_id\|order_group_seq\|order_count" includes/ensure_order_table_columns.php
```

**PASS:** `order_group_id VARCHAR(50)`, `order_group_seq INT` 정의가 존재
**FAIL:** 컬럼 정의 누락 → 주문 그룹 기능 작동 불가

### Step 2: 건수 파이프라인 연속성 검증

**도구:** Grep

건수(order_count)가 프론트→API→DB까지 끊김 없이 흐르는지 각 터치포인트를 검증합니다.

#### 2a. 견적 위젯 JS — 건수 전송

```bash
grep -n "order_count" js/quote-gauge.js
```

**PASS:** 다음이 모두 존재해야 함:
- `formData.append('order_count', ...)` — 전송
- 건수 × 가격 곱셈 로직 — 가격 반영
- 건수 polling/이벤트 — 실시간 업데이트

**FAIL:** 위 중 하나라도 누락 → 건수가 견적에 반영 안 됨

#### 2b. 견적 API — order_count DB 저장

```bash
grep -n "order_count" includes/quote_request_api.php
```

**PASS:** INSERT 문에 `order_count` 컬럼이 포함되고, `$_POST['order_count']`를 읽음
**FAIL:** INSERT에 order_count 누락 → DB에 건수 미저장

#### 2c. 견적 위젯 PHP — 건수 UI

```bash
grep -n "order_count\|건수\|order-count" includes/quote_gauge.php
```

**PASS:** 건수 표시 행(HTML)이 존재하고, order_count > 1일 때만 표시하는 조건문 존재
**FAIL:** 건수 UI 행 누락 → 사용자에게 건수 미표시

### Step 3: 그룹 키 전환 검증 (item_group_id → order_group_id)

**도구:** Read, Grep

#### 3a. 장바구니에서 item_group_id 생성

```bash
grep -rn "item_group_id" mlangprintauto/*/add_to_basket.php
```

**PASS:** 최소 1개 이상의 제품에서 `IG-` 접두사로 item_group_id를 생성하고 shop_temp에 INSERT
**FAIL:** item_group_id 생성 코드 없음 → 건수 그룹 불가

#### 3b. 장바구니 그룹 표시

```bash
grep -n "item_group_id\|item_group_seq" mlangprintauto/cart.php
```

**PASS:** cart.php에서 item_group_id를 GROUP BY 또는 조건문으로 사용하여 그룹 표시
**FAIL:** cart.php에서 item_group_id 미사용 → 장바구니에서 그룹 미표시

#### 3c. 주문 처리에서 order_group_id 생성

```bash
grep -n "order_group_id\|GRP-\|order_group_seq" mlangorder_printauto/ProcessOrder_unified.php
```

**PASS:** 다음이 모두 존재:
- `GRP-` 접두사로 order_group_id 생성
- order_group_seq를 각 아이템에 순차 부여
- INSERT 문에 order_group_id, order_group_seq 포함

**FAIL:** 위 중 하나라도 누락 → 주문 그룹 미저장

### Step 4: 관리자 목록 order_group_id 조회 검증

**도구:** Grep

#### 4a. 대시보드 API

```bash
grep -n "order_group_id" dashboard/api/orders.php
```

**PASS:** SELECT 쿼리에 `order_group_id` 컬럼이 포함
**FAIL:** SELECT에 order_group_id 미포함 → 대시보드에서 그룹 표시 불가

#### 4b. 대시보드 UI

```bash
grep -n "order_group_id\|group-bar\|badge--count\|×.*건" dashboard/orders/index.php
```

**PASS:** order_group_id 기반 그룹 렌더링 로직 존재
**FAIL:** 그룹 렌더링 로직 없음 → 건수/그룹 미표시 (Phase 1 구현 필요)

#### 4c. 레거시 관리자 목록

```bash
grep -n "order_group_id\|group-bar\|badge--count\|×.*건" admin/mlangprintauto/orderlist.php
```

**PASS:** order_group_id 기반 건수 축약/그룹 바 로직 존재
**FAIL:** 그룹 로직 없음 → 건수/그룹 미표시 (Phase 1 구현 필요)

#### 4d. 관리자 상세

```bash
grep -n "order_group_id" mlangorder_printauto/OrderFormOrderTree.php
```

**PASS:** 같은 order_group_id를 가진 주문을 조회하여 그룹 표시
**FAIL:** order_group_id 조회 없음 → 상세에서 그룹 미표시

### Step 5: 결제/배송 그룹 일괄 처리 검증

**도구:** Grep

#### 5a. 결제 완료 — 그룹 전체 상태

```bash
grep -n "order_group_id" payment/inicis_return.php
```

**PASS:** 결제 성공 시 같은 order_group_id의 모든 주문 상태를 일괄 UPDATE
**FAIL:** 개별 주문만 상태 변경 → 그룹 내 나머지 주문 미결제 상태

#### 5b. 결제 요청 — 그룹 금액 합산

```bash
grep -n "order_group_id" payment/inicis_request.php
```

**PASS:** 같은 order_group_id의 전체 금액을 합산하여 결제 요청
**FAIL:** 단건 금액만 결제 → 그룹 결제 금액 불일치

#### 5c. 배송 — 그룹 일괄

```bash
grep -n "order_group_id" includes/shipping_api.php
```

**PASS:** 같은 order_group_id의 모든 주문에 동일 송장번호/배송상태 일괄 UPDATE
**FAIL:** 개별 주문만 배송 처리 → 그룹 내 나머지 미배송

### Step 6: 건수 판별 프록시 조건 검증

**도구:** Read

주문 테이블에 item_group_id가 저장되지 않으므로, 관리자 페이지에서 건수를 판별할 때
`order_group_id + Type + money_5` 프록시 조건을 사용해야 합니다.

관리자 목록/상세 페이지에서 건수 판별 시 **절대 하지 말 것**:

```php
// ❌ 절대 금지: item_group_id를 mlangorder_printauto에서 조회 (컬럼 없음!)
$sql = "SELECT item_group_id FROM mlangorder_printauto WHERE ...";

// ❌ 절대 금지: order_group_id만으로 건수 판별 (다른 사양도 같은 그룹에 포함됨!)
$count = "SELECT COUNT(*) FROM mlangorder_printauto WHERE order_group_id = ?";
// → 이것은 "건수"가 아니라 "그룹 전체 수"
```

**올바른 건수 판별:**

```php
// ✅ 올바른 방법: 같은 그룹 + 같은 Type + 같은 금액 = 같은 사양 건수
SELECT order_group_id, Type, money_5, COUNT(*) as cnt
FROM mlangorder_printauto
WHERE order_group_id = ?
GROUP BY order_group_id, Type, money_5
HAVING cnt > 1
```

**검증:** 관리자 목록 파일에서 건수 판별 로직이 위 조건을 따르는지 확인:

```bash
grep -n "Type.*money_5\|money_5.*Type\|GROUP BY.*order_group_id" dashboard/orders/index.php dashboard/api/orders.php admin/mlangprintauto/orderlist.php 2>/dev/null
```

**PASS:** `order_group_id + Type + money_5` 3중 조건으로 건수 판별
**FAIL:** 잘못된 조건 사용 또는 건수 판별 로직 미구현

### Step 7: cont 필드 건수 텍스트 검증

**도구:** Grep

ProcessOrder_unified.php에서 cont 필드에 건수 정보를 기록하는지 확인합니다:

```bash
grep -n "같은 스펙\|동일 사양\|건 중\|번째" mlangorder_printauto/ProcessOrder_unified.php
```

**PASS:** `[같은 스펙 N건 중 M번째]` 형식의 텍스트가 cont에 추가되는 로직 존재
**FAIL (WARNING):** cont 텍스트 미기록 — 기능 자체는 작동하지만 관리자가 상세에서 건수를 텍스트로 확인 불가

## Output Format

```markdown
## 건수/그룹 파이프라인 검증 보고서

### 요약

| 검증 항목 | 상태 | 상세 |
|-----------|------|------|
| DB 스키마 (shop_temp) | ✅/❌ | item_group_id, item_group_seq |
| DB 스키마 (order) | ✅/❌ | order_group_id, order_group_seq |
| 건수 파이프라인 (JS→API→DB) | ✅/❌ | N/3 터치포인트 정상 |
| 그룹 키 전환 (IG→OG) | ✅/❌ | 생성/표시/INSERT |
| 관리자 목록 그룹 표시 | ✅/❌ | dashboard/orderlist |
| 관리자 상세 그룹 표시 | ✅/❌ | OrderFormOrderTree |
| 결제 그룹 일괄 | ✅/❌ | inicis_return/request |
| 배송 그룹 일괄 | ✅/❌ | shipping_api |
| 건수 판별 프록시 | ✅/❌ | Type+money_5 조건 |

### 발견된 이슈

| # | 단계 | 파일 | 문제 | 수정 방법 |
|---|------|------|------|-----------|
| 1 | ... | ... | ... | ... |
```

## Exceptions

다음은 **위반이 아닙니다**:

1. **item_group_id가 mlangorder_printauto에 없음** — 이것은 의도된 설계입니다. item_group_id는 shop_temp(장바구니)에서만 사용되며, 주문 후에는 order_group_id로 대체됩니다. 이것이 "프록시 조건(Type + money_5)"이 필요한 이유입니다.
2. **order_count가 mlangorder_printauto에 없음** — order_count는 quote_requests(견적) 테이블에만 존재합니다. 주문 테이블에서는 같은 order_group_id의 행 수가 곧 건수입니다.
3. **단일 주문(order_group_id 없음)에 그룹 표시 없음** — order_group_id가 NULL이거나 해당 그룹에 1건만 있으면 기존 단건 표시가 정상입니다.
4. **견적→주문 변환 시 별도 GRP 생성** — `quote/api/convert_to_order.php`에서 생성하는 GRP-xxx는 ProcessOrder_unified.php와 독립적이며, 두 경로가 각각 유효한 order_group_id를 생성하면 정상입니다.
5. **대시보드 vs 레거시 목록의 표시 차이** — dashboard/orders/index.php(JS 렌더링)와 admin/orderlist.php(PHP 렌더링)는 구현 방식이 다를 수 있으나, 두 곳 모두 order_group_id를 기반으로 그룹을 인식하면 정상입니다.
6. **cont 필드 건수 텍스트 미기록** — cont 필드의 `[같은 스펙 N건 중 M번째]` 텍스트는 보조 정보이며, 없어도 건수/그룹 기능 자체는 order_group_id 기반으로 작동합니다.
