---
name: verify-option-pricing
description: 옵션 가격 시스템의 데이터 소스 통일성과 파이프라인 정합성을 검증합니다. 옵션 가격 관련 PHP 코드 수정 후 사용.
---

# 옵션 가격 시스템 정합성 검증

## 목적

옵션 가격 데이터가 단일 소스(SSOT)에서 관리되고, 전체 파이프라인에 올바르게 연결되어 있는지 검증합니다:

1. **SSOT 위반 탐지** — `premium_options`가 아닌 다른 소스(`additional_options_config`, `AdditionalOptions.php` 하드코딩)에서 가격을 읽는 코드
2. **파이프라인 단절 탐지** — 관리자 수정 → DB 저장 → 위젯/고객페이지 반영 → 주문 저장까지의 흐름에서 끊어진 구간
3. **가격 불일치 탐지** — 서로 다른 소스에 동일 옵션의 가격이 다르게 저장된 경우
4. **고아 데이터 탐지** — `premium_option_variants`에서 `premium_options`에 없는 option_id를 참조하는 레코드

## 실행 시점

- 견적 위젯(quote/widgets/*.php) 수정 후
- 품목옵션 관리페이지(dashboard/premium-options/) 수정 후
- 고객 주문페이지의 가격 계산 로직 수정 후
- `additional_options_config` 테이블 관련 코드 수정 후
- `AdditionalOptions.php` 수정 후
- 새 제품의 옵션 가격 기능 추가 시

## 가격 시스템 아키텍처 (목표 상태)

```
SSOT: premium_options + premium_option_variants
  ├── 관리 UI: /dashboard/premium-options/
  ├── Admin API: /dashboard/api/premium_options.php (CRUD)
  ├── Public API: /api/premium_options.php (읽기 전용)
  ├── 견적 위젯 6개: admin/mlangprintauto/quote/widgets/*.php
  ├── 고객 주문페이지: mlangprintauto/leaflet/calculate_price_ajax.php 등
  └── 주문 저장: mlangorder_printauto/ProcessOrder*.php

폐기 대상:
  ✗ additional_options_config (DB 테이블) — premium_options로 대체
  ✗ AdditionalOptions.php (하드코딩) — premium_options API로 대체
```

## 제품-옵션-패턴 매핑

| 제품 | product_type | 옵션 | 가격 패턴 |
|------|-------------|------|----------|
| 명함 | namecard | 박, 넘버링, 미싱, 귀돌이, 오시 | A (base_500 + per_unit + additional_fee) |
| 상품권 | merchandisebond | 박, 넘버링, 미싱, 귀돌이, 오시 | A |
| 전단지 | inserted | 코팅, 접지, 오시 | B (base_price + per_unit) |
| 포스터 | littleprint | 코팅, 접지, 오시 | B |
| 카다록 | cadarok | 코팅, 접지, 오시 | B |
| 봉투 | envelope | 양면테이프 | C (tiers) |

## Related Files

| File | Purpose |
|------|---------|
| `dashboard/premium-options/index.php` | 품목옵션 관리 UI (SSOT 관리자 페이지) |
| `dashboard/api/premium_options.php` | Admin CRUD API (premium_options 읽기/쓰기) |
| `api/premium_options.php` | Public 읽기 전용 API |
| `admin/mlangprintauto/quote/widgets/namecard.php` | 명함 견적 위젯 |
| `admin/mlangprintauto/quote/widgets/merchandisebond.php` | 상품권 견적 위젯 |
| `admin/mlangprintauto/quote/widgets/inserted.php` | 전단지 견적 위젯 |
| `admin/mlangprintauto/quote/widgets/littleprint.php` | 포스터 견적 위젯 |
| `admin/mlangprintauto/quote/widgets/cadarok.php` | 카다록 견적 위젯 |
| `admin/mlangprintauto/quote/widgets/envelope.php` | 봉투 견적 위젯 |
| `admin/mlangprintauto/quote/option_prices.php` | 견적옵션 관리 (폐기 대상) |
| `mlangprintauto/leaflet/calculate_price_ajax.php` | 전단지 고객 가격 계산 |
| `mlangprintauto/leaflet/get_coating_types.php` | 전단지 코팅 드롭다운 API |
| `mlangprintauto/leaflet/get_creasing_types.php` | 전단지 오시 드롭다운 API |
| `m/mlangprintauto260104/leaflet/calculate_price_ajax.php` | 모바일 전단지 가격 계산 |
| `m/mlangprintauto260104/leaflet/get_coating_types.php` | 모바일 코팅 드롭다운 API |
| `m/mlangprintauto260104/leaflet/get_creasing_types.php` | 모바일 오시 드롭다운 API |
| `includes/AdditionalOptions.php` | 하드코딩 가격 (폐기 대상) |
| `includes/AdditionalOptionsDisplay.php` | 옵션 표시 헬퍼 |
| `includes/PriceCalculationService.php` | 중앙 가격 계산 서비스 |

## Workflow

### Step 1: SSOT 위반 탐지 — `additional_options_config` 직접 조회

**목표:** `premium_options`가 아닌 `additional_options_config`를 직접 읽는 코드를 찾습니다.

```bash
# additional_options_config를 SQL 쿼리하는 파일 찾기
grep -rn "additional_options_config" --include="*.php" \
  admin/mlangprintauto/quote/widgets/ \
  mlangprintauto/ \
  m/mlangprintauto260104/ \
  includes/ \
  api/ | grep -v "option_prices.php" | grep -v ".bak" | grep -v "_test"
```

**PASS:** 결과 0건 (모든 소비자가 premium_options를 읽음)
**FAIL:** 결과가 있으면 해당 파일이 아직 `additional_options_config`를 직접 읽고 있음

**수정 방법:** 해당 파일의 SQL 쿼리를:
- 위젯: `premium_options` + `premium_option_variants` JOIN 쿼리로 교체
- 고객페이지: `/api/premium_options.php` 호출 또는 직접 JOIN 쿼리로 교체

### Step 2: SSOT 위반 탐지 — `AdditionalOptions.php` 하드코딩 참조

**목표:** `AdditionalOptions.php`의 하드코딩 가격을 사용하는 코드를 찾습니다.

```bash
# AdditionalOptions 클래스를 사용하는 파일 찾기
grep -rn "AdditionalOptions" --include="*.php" \
  admin/ mlangprintauto/ mlangorder_printauto/ \
  includes/ shop/ mypage/ m/ \
  | grep -v "AdditionalOptions.php$" \
  | grep -v "AdditionalOptionsDisplay.php" \
  | grep -v ".bak" | grep -v "_test"
```

**PASS:** 결과 0건 (하드코딩 가격 미사용)
**FAIL:** 결과가 있으면 해당 파일이 하드코딩 가격을 사용 중

**참고:** AdditionalOptions.php 폐기는 영향 범위가 넓어 (장바구니, 주문서, 마이페이지 등 ~25개 파일) 단계적 전환 필요

### Step 3: DB 정합성 검증 — premium_options 테이블

**목표:** `premium_options` ↔ `premium_option_variants` 간 데이터 정합성 확인

```bash
# Public API로 전 제품 검증
for pt in namecard merchandisebond inserted littleprint cadarok envelope; do
  curl -s "http://localhost/api/premium_options.php?product_type=$pt" | \
    python3 -c "
import sys, json
data = json.load(sys.stdin)
pt = '$pt'
if not data['success']:
    print(f'FAIL: {pt} API error - {data[\"message\"]}')
    sys.exit(1)
for opt in data['options']:
    if not opt['variants']:
        print(f'FAIL: {pt}/{opt[\"option_name\"]}: 빈 옵션 (variants 없음)')
    for v in opt['variants']:
        pc = v.get('pricing_config', {})
        if not pc:
            print(f'FAIL: {pt}/{opt[\"option_name\"]}/{v[\"variant_name\"]}: pricing_config 비어있음')
print(f'OK: {pt} ({len(data[\"options\"])} options)')
"
done
```

**PASS:** 6개 제품 모두 "OK" 출력, FAIL 없음
**FAIL:** 빈 옵션이나 빈 pricing_config가 있으면 DB 데이터 손상

### Step 4: 가격 불일치 탐지 — 소스 간 교차 비교

**목표:** `premium_options`와 `additional_options_config`의 동일 옵션 가격이 다른 경우 탐지

```bash
php -r "
require '/var/www/html/db.php';

// additional_options_config 가격
\$aoc = [];
\$r = mysqli_query(\$db, 'SELECT option_category, option_type, option_name, base_price FROM additional_options_config');
while(\$row = mysqli_fetch_assoc(\$r)) { \$aoc[\$row['option_category'].'/'.\$row['option_type']] = \$row; }

// premium_option_variants 가격 (inserted 기준 - Pattern B)
\$r = mysqli_query(\$db, \"SELECT o.option_name, v.variant_name, v.pricing_config FROM premium_options o JOIN premium_option_variants v ON o.id = v.option_id WHERE o.product_type = 'inserted'\");
\$mismatches = 0;
while(\$row = mysqli_fetch_assoc(\$r)) {
    \$pc = json_decode(\$row['pricing_config'], true);
    \$prem_price = \$pc['base_price'] ?? \$pc['base_500'] ?? null;
    // Map to AOC key
    \$map = ['코팅/단면유광'=>'coating/single','코팅/양면유광'=>'coating/double','코팅/단면무광'=>'coating/single_matte','코팅/양면무광'=>'coating/double_matte','접지/2단'=>'folding/2fold','접지/3단'=>'folding/3fold','접지/병풍'=>'folding/accordion','접지/대문'=>'folding/gate','오시/1줄'=>'creasing/1line','오시/2줄'=>'creasing/2line','오시/3줄'=>'creasing/3line'];
    \$key = \$row['option_name'].'/'.\$row['variant_name'];
    \$aoc_key = \$map[\$key] ?? null;
    if (\$aoc_key && isset(\$aoc[\$aoc_key])) {
        \$aoc_price = intval(\$aoc[\$aoc_key]['base_price']);
        if (\$prem_price != \$aoc_price) {
            echo 'MISMATCH: '.\$key.' → premium='.\$prem_price.' vs AOC='.\$aoc_price.\"\n\";
            \$mismatches++;
        }
    }
}
echo \$mismatches == 0 ? 'OK: 가격 불일치 없음' : 'FAIL: '.\$mismatches.'건 불일치';
echo \"\n\";
mysqli_close(\$db);
"
```

**PASS:** "OK: 가격 불일치 없음"
**FAIL:** 불일치 건수와 상세가 표시됨 → 어느 쪽이 맞는지 확인 후 통일 필요

### Step 5: 파이프라인 연결 검증 — 위젯 → calculate_price → 주문 저장

**목표:** 위젯에서 계산한 옵션 가격이 calculate_price_ajax.php를 거쳐 주문 DB에 올바르게 저장되는지 확인

```bash
# 위젯이 premium_options_total 파라미터를 전송하는지 확인
for widget in namecard merchandisebond inserted littleprint cadarok envelope; do
  count=$(grep -c "premium_options_total" "admin/mlangprintauto/quote/widgets/$widget.php" 2>/dev/null)
  echo "$widget: premium_options_total 전송 = ${count}건"
done

# calculate_price_ajax.php가 premium_options_total을 받는지 확인
for product in namecard inserted littleprint cadarok envelope merchandisebond; do
  file="mlangprintauto/$product/calculate_price_ajax.php"
  if [ -f "$file" ]; then
    count=$(grep -c "premium_options_total" "$file" 2>/dev/null)
    echo "$product calc: premium_options_total 수신 = ${count}건"
  fi
done
```

**PASS:** 모든 위젯과 calculate_price 파일에서 `premium_options_total`이 1건 이상
**FAIL:** 0건인 파일이 있으면 파이프라인 단절

### Step 6: 프로덕션 API 동기화 검증

**목표:** localhost와 production의 premium_options 데이터가 일치하는지 확인

```bash
for pt in namecard merchandisebond inserted littleprint cadarok envelope; do
  local=$(curl -s "http://localhost/api/premium_options.php?product_type=$pt")
  prod=$(curl -sk "https://dsp114.com/api/premium_options.php?product_type=$pt")
  
  local_count=$(echo "$local" | python3 -c "import sys,json; d=json.load(sys.stdin); print(sum(len(o['variants']) for o in d.get('options',[])))" 2>/dev/null)
  prod_count=$(echo "$prod" | python3 -c "import sys,json; d=json.load(sys.stdin); print(sum(len(o['variants']) for o in d.get('options',[])))" 2>/dev/null)
  
  if [ "$local_count" = "$prod_count" ]; then
    echo "OK: $pt ($local_count variants)"
  else
    echo "MISMATCH: $pt local=$local_count prod=$prod_count"
  fi
done
```

**PASS:** 모든 제품의 variant 수가 localhost = production
**FAIL:** 수가 다르면 배포 누락 또는 DB 동기화 문제

## Output Format

```markdown
## 옵션 가격 시스템 검증 결과

| # | 검증 항목 | 결과 | 상세 |
|---|----------|------|------|
| 1 | SSOT 위반 (AOC 직접 조회) | ✅/❌ | N건 발견 |
| 2 | SSOT 위반 (하드코딩 참조) | ✅/❌ | N개 파일 |
| 3 | DB 정합성 (premium_options) | ✅/❌ | 6개 제품 OK / N건 오류 |
| 4 | 가격 불일치 (소스 간) | ✅/❌ | N건 불일치 |
| 5 | 파이프라인 연결 | ✅/❌ | N개 단절 |
| 6 | 프로덕션 동기화 | ✅/❌ | 일치/불일치 |

### 발견된 이슈

(이슈별 파일 경로, 현재 상태, 수정 방법 기술)
```

## Exceptions

다음은 **위반이 아닙니다:**

1. **`option_prices.php` 자체** — 이 파일은 `additional_options_config`의 관리 UI이므로 해당 테이블을 직접 읽는 것이 정상. 이 파일이 폐기 완료되면 이 예외도 제거.
2. **주석/로그의 `additional_options_config` 언급** — 실제 SQL 쿼리가 아닌 주석이나 로그 문자열에서의 참조는 문제 없음.
3. **마이그레이션/백업 스크립트** — `scripts/` 디렉토리의 데이터 이관 스크립트에서 양쪽 테이블을 모두 읽는 것은 정상.
4. **테스트 파일** — `_test`, `.bak`, `_debug` 접미사가 붙은 파일은 검증 대상 제외.
5. **premium_options_total = 0** — 옵션 미선택 주문에서 총액이 0인 것은 정상.
6. **localhost ↔ production 봉투 tier 키 순서 차이** — `tiers` 배열의 가격값이 동일하면 JSON 키 순서 차이는 무시.
