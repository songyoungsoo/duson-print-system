---
name: verify-bind-param
description: PHP 코드에서 bind_param 호출의 3요소(플레이스홀더/타입문자/변수) 카운트 불일치를 탐지합니다. 데이터 손상(고객명 '0' 저장)을 배포 전에 잡습니다. PHP 파일 수정 후 사용.
---

# bind_param 카운트 검증

## Purpose

PHP의 `bind_param` (또는 `mysqli_stmt_bind_param`)은 SQL 인젝션을 방어하는 핵심 메커니즘입니다.
그런데 **3가지 숫자가 정확히 일치하지 않으면** 심각한 버그가 발생합니다:

1. **`?` 플레이스홀더 개수** — SQL 쿼리 안의 물음표 수
2. **타입 문자열 길이** — `"sssis"` 같은 타입 지정자의 글자 수
3. **바인딩 변수 개수** — bind_param에 전달하는 실제 변수 수

불일치 시 증상:
- PHP Fatal Error (프로덕션에서 빈 화면)
- 값이 한 칸씩 밀려서 고객명이 `'0'`으로 저장됨
- 잘못된 컬럼에 잘못된 값이 들어감 (데이터 오염)

## When to Run

- `bind_param` 또는 `mysqli_stmt_bind_param`이 포함된 PHP 파일을 수정한 후
- INSERT/UPDATE/DELETE 쿼리를 작성하거나 수정한 후
- 기존 쿼리에 컬럼을 추가/삭제한 후 (이때 가장 많이 발생!)

## Related Files

| File | Purpose |
|------|---------|
| `dashboard/api/email.php` | bind_param 31건 — 가장 많은 사용 |
| `mlangprintauto/quote/includes/QuoteManager.php` | bind_param 26건 — 견적서 핵심 |
| `chat/api.php` | bind_param 24건 — AI 챗봇 |
| `mlangorder_printauto/ProcessOrder_unified.php` | bind_param 12건 — 주문 처리 핵심 |
| `member/login_unified.php` | bind_param 11건 — 로그인 인증 |

## Workflow

### Step 1: 최근 수정된 파일에서 bind_param 호출 찾기

**도구:** Grep
**대상:** 수정된 PHP 파일 (또는 전체 PHP 파일)

```bash
# OOP 스타일
grep -rn "->bind_param\s*(" --include="*.php" .

# 프로시저 스타일
grep -rn "mysqli_stmt_bind_param\s*(" --include="*.php" .
```

**다음 단계:** 각 결과에 대해 Step 2~4를 수행

### Step 2: SQL 쿼리에서 `?` 플레이스홀더 세기

bind_param 호출을 찾았으면, 해당 SQL 쿼리를 역추적하여 `?` 개수를 셉니다.

```php
// 예시: 이 쿼리에서 ? 는 4개
$sql = "INSERT INTO orders (name, phone, email, amount) VALUES (?, ?, ?, ?)";
//                                                              1  2  3  4
```

**주의사항:**
- `NOW()`, `NULL`, 숫자 리터럴 `0`은 `?`가 아니므로 세지 않음
- 서브쿼리 안의 `?`도 포함해서 셈
- 여러 줄에 걸친 SQL은 전체를 확인

**PASS 기준:** 다음 Step의 타입문자 수와 일치
**FAIL 기준:** 타입문자 수와 불일치

### Step 3: 타입 문자열 길이 세기

```php
$stmt->bind_param("sssis", ...);
//                 ^^^^^
//                 s=string, i=integer, d=double, b=blob
//                 이 경우 5글자
```

**주의사항:**
- 문자열 연결 주의: `"ssssssssssss" . "s"` = 12 + 1 = 13글자
- 변수에 담긴 경우: `$types = "ssi"; $stmt->bind_param($types, ...)` → 3글자
- `str_repeat("s", $count)` 같은 동적 생성은 런타임 확인 필요 (예외 처리)

**PASS 기준:** `?` 개수와 일치
**FAIL 기준:** `?` 개수와 불일치

### Step 4: 바인딩 변수 개수 세기

```php
$stmt->bind_param("sss",
    $name,      // 1
    $phone,     // 2
    $email      // 3
);
```

**주의사항:**
- 여러 줄에 걸쳐 있을 수 있음 — `)` 닫힘까지 모든 변수 카운트
- 리터럴 값도 변수로 카운트: `$stmt->bind_param("si", $name, 1)` → 2개
- 배열 언팩 `...$params`는 동적이므로 예외 처리
- 타입문자열은 변수가 아님! 첫 번째 인자 제외하고 셈

**PASS 기준:** `?` 개수 = 타입문자 수 = 변수 수 (3개 모두 일치)
**FAIL 기준:** 하나라도 불일치

### Step 5: 컬럼 목록과 VALUES 대응 확인 (INSERT 전용)

INSERT 문의 경우, 추가로 컬럼-VALUES 대응도 확인합니다.

```php
// 컬럼 7개 vs VALUES 6개 = ❌ 불일치!
INSERT INTO table (col1, col2, col3, col4, col5, col6, col7)
VALUES (?, ?, ?, ?, NOW(), NOW())
//      1  2  3  4  5     6       = 6개 값 (? 4개 + 리터럴 2개)
```

**PASS 기준:** 컬럼 수 = VALUES 안의 값 수 (? + 리터럴 합계)
**FAIL 기준:** 컬럼 수 ≠ VALUES 값 수

## Output Format

| # | 파일:라인 | ?개수 | 타입문자 | 변수수 | 판정 | 설명 |
|---|----------|-------|---------|--------|------|------|
| 1 | `파일:행` | 4 | 5 | 5 | ❌ FAIL | ?가 1개 부족 |
| 2 | `파일:행` | 3 | 3 | 3 | ✅ PASS | 정상 |

## Exceptions

다음은 **위반이 아닙니다** (자동 검증에서 제외):

1. **동적 타입 문자열** — `str_repeat("s", count($arr))` 또는 `$types .= "s"` 루프는 런타임에 결정되므로 정적 검증 불가. 대신 "동적 bind_param 발견 — 수동 확인 필요"로 보고
2. **배열 언팩 (spread)** — `$stmt->bind_param($types, ...$values)` 패턴은 타입과 변수가 동적으로 일치하므로 예외
3. **레거시 테스트/마이그레이션 스크립트** — `admin/db_schema/`, `scripts/`, `system/migration/`, `system/install/` 폴더의 일회성 스크립트는 WARNING으로 보고 (FAIL이 아닌 INFO)
4. **`m/` 모바일 백업 폴더** — `m/mlangprintauto260104/`, `m/mlangorder_printauto260104/` 등 날짜 붙은 백업 폴더는 제외
5. **주석 처리된 코드** — `//` 또는 `/* */` 안의 bind_param은 무시

## Known Violations (2026-02-26 기준)

| 파일 | 문제 | 심각도 |
|------|------|--------|
| `admin/db_schema/final_phase1.php:169` | `?` 4개 vs 타입 `"sssis"` 5글자 — `is_active` 컬럼에 `?` 누락 | ⚠️ 마이그레이션 스크립트 (WARNING) |

## Prevention Tips

bind_param 작성 시 **3단계 검증 습관**:

```php
// ✅ 올바른 작성법: 주석으로 카운트 명시
$sql = "INSERT INTO orders (name, phone, email, amount)
        VALUES (?, ?, ?, ?)";  // ? = 4개

$stmt->bind_param("sssd",    // 타입 = 4글자 (s,s,s,d)
    $name,                    // 1
    $phone,                   // 2  
    $email,                   // 3
    $amount                   // 4  → 총 4개 ✓
);
```
