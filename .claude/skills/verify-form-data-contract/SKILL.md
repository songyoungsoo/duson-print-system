---
name: verify-form-data-contract
description: JS formData.append 필드와 PHP $_POST 필드의 일치를 검증합니다. 프론트엔드/백엔드 데이터 계약 불일치로 인한 빈값 저장·데이터 누락을 배포 전에 잡습니다. 제품 페이지 또는 장바구니/견적서 PHP 수정 후 사용.
---

# 폼 데이터 계약 검증 (Form Data Contract Verification)

## Purpose

제품 주문 페이지(index.php)의 JavaScript가 `formData.append`로 보내는 필드와,
백엔드 PHP(add_to_basket.php, add_to_quotation_temp.php 등)가 `$_POST`로 읽는 필드 사이의
**데이터 계약(contract)** 일치를 검증합니다.

불일치 시 증상:
1. **백엔드가 읽는데 프론트가 안 보냄** → DB에 빈값/0 저장 (데이터 손실) — **CRITICAL**
2. **프론트가 보내는데 백엔드가 안 읽음** → 데이터 무시됨 (기능 누락) — **WARNING**
3. **필드명 오타/대소문자 불일치** → 양쪽 다 있지만 연결 안 됨 — **CRITICAL**
4. **데이터 형식 변경** → JSON vs 개별필드 전환 시 한쪽만 업데이트 — **CRITICAL**

실제 사례 (2026-02-27):
- 명함(namecard) 프리미엄 옵션을 PremiumOptionsGeneric으로 전환
- 프론트: `formData.append('premium_options_data', JSON)` (새 방식)
- 백엔드: `$_POST['foil_enabled']`, `$_POST['foil_type']` ... (옛 방식 그대로)
- 결과: 프리미엄 옵션 총액은 맞지만 **상세 내역이 전부 빈값으로 DB 저장**

## When to Run

- 제품 페이지(index.php)의 `formData.append` 관련 JavaScript를 수정한 후
- `add_to_basket.php` 또는 `add_to_quotation_temp.php`의 `$_POST` 읽기를 수정한 후
- PremiumOptionsGeneric 등 공통 모듈로 프론트엔드를 전환한 후
- 새 필드를 추가하거나 기존 필드를 제거한 후
- 장바구니/견적서 데이터 흐름에 버그가 의심될 때

## Related Files

### 프론트엔드 (JS formData 전송)

| File | Product | 비고 |
|------|---------|------|
| `mlangprintauto/namecard/index.php` | 명함 | PremiumOptionsGeneric 전환됨 |
| `mlangprintauto/inserted/index.php` | 전단지 | PremiumOptionsGeneric 전환됨 |
| `mlangprintauto/envelope/index.php` | 봉투 | PremiumOptionsGeneric 전환됨 |
| `mlangprintauto/merchandisebond/index.php` | 상품권 | PremiumOptionsGeneric 전환됨 |
| `mlangprintauto/littleprint/index.php` | 포스터 | PremiumOptionsGeneric 전환됨 |
| `mlangprintauto/cadarok/index.php` | 카다록 | PremiumOptionsGeneric 전환됨 |
| `mlangprintauto/sticker_new/index.php` | 스티커 | 별도 구조 (formula) |
| `mlangprintauto/ncrflambeau/index.php` | NCR양식지 | |
| `mlangprintauto/msticker/index.php` | 자석스티커 | |

### 백엔드 (PHP $_POST 수신)

| File | 용도 |
|------|------|
| `mlangprintauto/<product>/add_to_basket.php` | 장바구니 추가 (제품별) |
| `mlangprintauto/<product>/calculate_price_ajax.php` | 가격 계산 AJAX (제품별) |
| `mlangprintauto/quote/add_to_quotation_temp.php` | 견적서 임시 추가 (공통) |

### 공통 모듈

| File | 용도 |
|------|------|
| `js/premium-options-loader.js` | PremiumOptionsGeneric 클래스 — `getSelectedOptions()` 반환 |
| `api/premium_options.php` | 프리미엄 옵션 API (DB → JSON) |

## Workflow

### Step 1: 수정된 파일에서 프론트-백엔드 쌍 식별

**도구:** Bash (git diff)

수정된 파일 목록에서 프론트엔드/백엔드 쌍을 식별합니다:

```bash
# 수정된 파일 중 해당 제품의 index.php 또는 add_to_basket.php 찾기
git diff HEAD --name-only | grep -E "mlangprintauto/.+/(index|add_to_basket|calculate_price_ajax)\.php"
```

각 제품에 대해 프론트-백엔드 쌍을 구성합니다:
- 프론트엔드: `mlangprintauto/<product>/index.php`
- 백엔드 1: `mlangprintauto/<product>/add_to_basket.php`
- 백엔드 2: `mlangprintauto/quote/add_to_quotation_temp.php` (견적서 경로가 있는 경우)

**주의:** index.php만 수정됐어도 대응하는 add_to_basket.php도 반드시 확인.
add_to_basket.php만 수정됐어도 대응하는 index.php도 반드시 확인.

### Step 2: 프론트엔드 formData 필드 추출

**도구:** Bash (grep)

```bash
# 장바구니 경로 (handleModalBasketAdd 또는 직접 fetch)
grep -oP "formData\.append\(\s*['\"]([^'\"]+)['\"]" mlangprintauto/<product>/index.php \
  | sed "s/formData\.append(['\"]//;s/['\"]$//" | sort -u

# 견적서 경로 (addToQuotation)
# addToQuotation 함수 내부의 formData.append도 별도 추출
```

**결과물:** 프론트엔드가 보내는 필드 목록 (장바구니용 / 견적서용 분리)

### Step 3: 백엔드 $_POST 필드 추출

**도구:** Bash (grep)

```bash
# add_to_basket.php
grep -oP "\\\$_POST\[['\"]([^'\"]+)['\"]\]" mlangprintauto/<product>/add_to_basket.php \
  | sed "s/\\\$_POST\[['\"]/  /;s/['\"]\]$//" | sort -u

# add_to_quotation_temp.php (공통)
grep -oP "\\\$_POST\[['\"]([^'\"]+)['\"]\]" mlangprintauto/quote/add_to_quotation_temp.php \
  | sed "s/\\\$_POST\[['\"]/  /;s/['\"]\]$//" | sort -u
```

**추가 확인:** `$params['필드명']` 패턴도 확인 (일부 파일은 $_POST를 $params로 복사 후 사용)

```bash
grep -oP "\\\$params\[['\"]([^'\"]+)['\"]\]" mlangprintauto/<product>/calculate_price_ajax.php \
  | sed "s/\\\$params\[['\"]/  /;s/['\"]\]$//" | sort -u
```

### Step 4: 필드 비교 및 불일치 탐지

Step 2와 Step 3의 결과를 비교합니다:

```bash
# 프론트 전용 (보내지만 안 읽음) — WARNING
comm -23 <(sort frontend_fields.txt) <(sort backend_fields.txt)

# 백엔드 전용 (읽지만 안 보냄) — CRITICAL
comm -13 <(sort frontend_fields.txt) <(sort backend_fields.txt)
```

**판정 기준:**

| 상황 | 심각도 | 설명 |
|------|--------|------|
| 백엔드가 읽는데 프론트가 안 보냄 | ❌ CRITICAL | DB에 빈값 저장 |
| 프론트가 보내는데 백엔드가 안 읽음 | ⚠️ WARNING | 데이터 무시 (의도적일 수 있음) |
| 양쪽에 비슷한 필드명 (오타 의심) | ❌ CRITICAL | `uploaded_files[]` vs `uploaded_files_info` 등 |

### Step 5: PremiumOptionsGeneric 전환 정합성 검증

PremiumOptionsGeneric을 사용하는 제품에 대해 추가 검증합니다:

**도구:** Grep

```bash
# 프론트엔드가 PremiumOptionsGeneric을 사용하는지
grep -l "PremiumOptionsGeneric" mlangprintauto/<product>/index.php

# 백엔드가 아직 개별 필드를 읽는지 (OLD 방식)
grep -n "foil_enabled\|numbering_enabled\|perforation_enabled\|rounding_enabled\|creasing_enabled" \
  mlangprintauto/<product>/add_to_basket.php
```

**FAIL 조건:**
- 프론트: PremiumOptionsGeneric 사용 (→ `premium_options_data` JSON 전송)
- 백엔드: 아직 `$_POST['foil_enabled']` 등 개별 필드 읽기
- → 상세 옵션 정보가 DB에 빈값으로 저장됨

**PASS 조건 (아래 중 하나):**
- 프론트와 백엔드 모두 OLD 방식 (개별 필드) — 일관됨
- 프론트가 `premium_options_data` JSON 전송 + 백엔드가 `$_POST['premium_options_data']`를 `json_decode`하여 처리
- 프론트와 백엔드 모두 PremiumOptionsGeneric을 지원하는 새 인터페이스 사용

### Step 6: 견적서 경로 별도 검증

`addToQuotation` 함수에서 `add_to_quotation_temp.php`로 보내는 필드도 별도 검증합니다.
장바구니 경로와 견적서 경로는 같은 제품이라도 보내는 필드가 다를 수 있습니다.

**도구:** Read

addToQuotation 함수 내부의 formData.append를 찾아 견적서 백엔드와 비교합니다.

**PASS 기준:** 견적서 백엔드가 읽는 필드를 견적서 프론트가 모두 보냄
**FAIL 기준:** 견적서 백엔드가 읽는데 견적서 프론트가 안 보내는 필드 존재

## Output Format

### 제품별 필드 매칭 결과

| # | 제품 | 경로 | 불일치 유형 | 필드명 | 심각도 | 설명 |
|---|------|------|-----------|--------|--------|------|
| 1 | namecard | basket | 백엔드만 | `foil_enabled` | ❌ CRITICAL | 프론트 PremiumOptionsGeneric 전환, 백엔드 미전환 |
| 2 | namecard | basket | 프론트만 | `premium_options_data` | ⚠️ WARNING | 백엔드에서 안 읽음 |
| 3 | inserted | basket | - | - | ✅ PASS | 일치 |

### PremiumOptionsGeneric 전환 정합성

| # | 제품 | 프론트 방식 | 백엔드 방식 | 판정 |
|---|------|-----------|-----------|------|
| 1 | namecard | NEW (Generic) | OLD (개별필드) | ❌ MISMATCH |
| 2 | inserted | NEW (Generic) | NEW (JSON) | ✅ MATCH |

## Exceptions

다음은 **위반이 아닙니다** (자동 검증에서 제외):

1. **의도적 비대칭 필드** — `uploaded_files[]` (프론트)와 `uploaded_files_info` (백엔드)는 파일 업로드 미들웨어가 중간 변환하므로 이름이 달라도 정상. `action` 필드도 URL 파라미터 또는 기본값으로 설정될 수 있음
2. **calculate_price_ajax.php의 추가 필드** — 가격 계산 AJAX는 프론트엔드가 직접 fetch하지 않고 내부 로직에서 호출하는 경우가 있으므로, $_POST와 formData가 다를 수 있음
3. **`m/` 모바일 백업 폴더** — `m/mlangprintauto260104/` 등 날짜 붙은 백업 폴더는 검증 제외
4. **견적서 API 파일** — `quote/api/save.php`, `quote/api/update.php` 등은 관리자 대시보드에서 호출하므로 제품 index.php와 직접 매핑 안 됨
5. **`$_POST` 기본값 패턴** — `$_POST['field'] ?? 0` 또는 `?? ''`는 해당 필드가 없어도 에러 없이 기본값 사용. 단, 데이터 누락은 여전히 문제이므로 WARNING은 유지
6. **동적 필드명** — `$_POST["{$option}_enabled"]` 같은 변수 기반 필드명은 정적 분석 불가. "동적 $_POST 발견 — 수동 확인 필요"로 보고
7. **레거시/샘플 파일** — `index_table_sample.php`, `index_02.php`, `sticker.php` 등 현재 사용하지 않는 파일은 제외

## Known Violations (2026-02-27 기준)

모든 알려진 위반사항이 해결되었습니다. ✅

### 해결됨 (2026-02-27)
| 제품 | 파일 | 문제 | 해결 |
|------|------|------|------|
| namecard | `add_to_basket.php:40-83` | 프론트 PremiumOptionsGeneric → 백엔드 개별필드 읽기 | ✅ NEW→OLD 변환 로직 추가 (JSON decode → foil_enabled 등 OLD 키로 변환) |

## Prevention Tips

프론트엔드를 수정할 때 항상 대응 백엔드를 함께 수정하세요:

```
index.php (formData.append 변경)
  ↕ 반드시 동시에
add_to_basket.php ($_POST 읽기 변경)
add_to_quotation_temp.php (견적서 경로도 확인)
```

PremiumOptionsGeneric으로 전환 시 체크리스트:
```
□ index.php — PremiumOptionsGeneric 초기화 + formData 전송 방식 변경
□ add_to_basket.php — $_POST['premium_options_data'] JSON 파싱으로 전환
□ calculate_price_ajax.php — premium_options_total 수신 확인
□ add_to_quotation_temp.php — 견적서 경로도 새 방식 지원 확인
```
