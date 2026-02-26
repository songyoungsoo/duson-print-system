---
name: verify-url-safety
description: PHP 코드에서 URL에 쉼표(,)를 사용하는 패턴을 탐지합니다. Plesk 서버에서 500 에러를 유발하는 코드를 배포 전에 잡습니다. PHP 파일 수정 후 사용.
---

# URL 안전성 검증

## Purpose

Plesk 서버에서 URL에 `%2C`(쉼표)가 포함되면 ModSecurity가 차단하여 500 에러가 발생합니다.
이 스킬은 다음을 검증합니다:

1. **implode(',')로 URL 파라미터 생성** — 쉼표 구분자를 URL에 사용하는 코드
2. **URL 문자열에 직접 쉼표 삽입** — 하드코딩된 쉼표 포함 URL

## When to Run

- PHP 파일에서 URL을 생성하거나 수정한 후
- GET 파라미터로 여러 값을 전달하는 로직을 작성한 후
- 주문번호, ID 목록 등을 URL로 전달하는 기능을 구현한 후

## Related Files

| File | Purpose |
|------|---------|
| `admin/mlangprintauto/orderlist.php` | 다건 주문 처리 — URL로 주문번호 목록 전달 |
| `mlangprintauto/shop/send_cart_quotation.php` | 장바구니 견적 — 여러 항목 ID 전달 |
| `shop_admin/export_logen_excel74.php` | 택배 엑셀 — 다건 선택 처리 |
| `shop_admin/post_list74.php` | 배송 목록 — 다건 선택 처리 |

## Workflow

### Step 1: implode + 쉼표 패턴 탐지

**도구:** Grep
**대상:** 모든 PHP 파일

```bash
grep -rn "implode\s*(\s*['\"]," --include="*.php" .
```

**PASS 기준:** 결과가 0건이거나, 결과가 모두 URL이 아닌 곳(예: 로그, DB 쿼리)에서 사용된 경우
**FAIL 기준:** URL 생성 컨텍스트에서 `implode(',', ...)` 사용

**위반 시 수정:**
```php
// ❌ 위반
$url = "page.php?orders=" . implode(',', $orderNos);

// ✅ 수정
$url = "page.php?orders=" . implode('_', $orderNos);
```

### Step 2: URL 문자열 내 쉼표 직접 사용 탐지

**도구:** Grep
**대상:** 모든 PHP 파일

```bash
grep -rn "\.php?\?.*=.*," --include="*.php" .
```

**PASS 기준:** URL 파라미터 값에 쉼표가 포함되지 않은 경우
**FAIL 기준:** URL 파라미터 값에 리터럴 쉼표가 포함된 경우

## Output Format

| # | 파일 | 라인 | 문제 | 수정 방법 |
|---|------|------|------|-----------|
| 1 | `파일경로:라인번호` | 코드 | 설명 | 수정 코드 |

## Exceptions

다음은 **위반이 아닙니다**:

1. **SQL 쿼리 내 implode(',')** — `WHERE id IN (1,2,3)` 같은 DB 쿼리는 URL이 아니므로 안전
2. **로그/디버그 메시지** — `error_log()`, `var_dump()` 내의 쉼표 구분은 URL이 아님
3. **CSV/파일 출력** — `fputcsv()`, 파일 쓰기에서의 쉼표는 URL이 아님
4. **JavaScript 배열** — PHP가 출력하는 JS 코드 내의 쉼표는 URL 파라미터가 아님
