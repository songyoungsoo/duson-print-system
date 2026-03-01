---
name: verify-quote-widget
description: 실시간 견적받기 위젯(quote_gauge.php)이 전체 9개 제품 페이지에 올바르게 포함되어 있는지 검증합니다. 제품 페이지 PHP 파일 수정 후 사용.
---

# 실시간 견적받기 위젯 검증

## Purpose

1. **위젯 include 존재** — 9개 제품 페이지 모두에 `quote_gauge.php` include가 있는지 확인
2. **CSS 링크 존재** — `<head>`에 `quote-gauge.css` 링크가 있는지 확인
3. **JS 스크립트 존재** — `quote-gauge.js` 스크립트 태그가 있는지 확인
4. **견적서 모드 체크** — `isQuotationMode` / `isAdminQuoteMode` 조건 래핑 확인
5. **mysqli_close 순서** — `quote_gauge.php` include가 `mysqli_close($db)` 보다 앞에 있는지 확인 (PHP 8.2 호환)

## When to Run

- 제품 페이지(`mlangprintauto/*/index.php`) PHP 파일 수정 후
- `includes/quote_gauge.php` 수정 후
- `css/quote-gauge.css` 또는 `js/quote-gauge.js` 수정 후
- 새 제품 페이지 추가 시
- 배포 전 최종 점검 시

## Related Files

| File | Purpose |
|------|---------|
| `includes/quote_gauge.php` | 위젯 PHP 소스 (SSOT) — DB 연결 필요 |
| `css/quote-gauge.css` | 위젯 스타일시트 |
| `js/quote-gauge.js` | 위젯 JavaScript |
| `mlangprintauto/inserted/index.php` | 전단지 제품 페이지 |
| `mlangprintauto/sticker_new/index.php` | 스티커 제품 페이지 |
| `mlangprintauto/envelope/index.php` | 봉투 제품 페이지 |
| `mlangprintauto/namecard/index.php` | 명함 제품 페이지 |
| `mlangprintauto/msticker/index.php` | 자석스티커 제품 페이지 |
| `mlangprintauto/littleprint/index.php` | 포스터 제품 페이지 |
| `mlangprintauto/merchandisebond/index.php` | 상품권 제품 페이지 |
| `mlangprintauto/cadarok/index.php` | 카다록 제품 페이지 |
| `mlangprintauto/ncrflambeau/index.php` | NCR양식지 제품 페이지 |

## 9개 제품 페이지 목록 (SSOT)

```
inserted sticker_new envelope namecard msticker littleprint merchandisebond cadarok ncrflambeau
```

## Workflow

### Step 1: quote_gauge.php include 존재 확인

9개 제품 페이지 모두에 `quote_gauge.php` include가 있는지 확인합니다.

```bash
for p in inserted sticker_new envelope namecard msticker littleprint merchandisebond cadarok ncrflambeau; do
  count=$(grep -c "quote_gauge.php" mlangprintauto/$p/index.php 2>/dev/null || echo 0)
  if [ "$count" -eq 0 ]; then
    echo "❌ FAIL: $p — quote_gauge.php include 누락"
  else
    echo "✅ PASS: $p — quote_gauge.php include 존재"
  fi
done
```

**PASS:** 모든 9개 페이지에서 count ≥ 1
**FAIL:** 하나라도 count = 0이면 위젯이 해당 페이지에 나타나지 않음

**수정 방법:** 누락된 페이지의 footer include 뒤, `mysqli_close` 앞에 다음 코드 추가:
```php
<?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
<?php include __DIR__ . '/../../includes/quote_gauge.php'; ?>
<script src="/js/quote-gauge.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>
```

### Step 2: quote-gauge.css 링크 확인

`<head>` 섹션에 CSS 링크가 있는지 확인합니다.

```bash
for p in inserted sticker_new envelope namecard msticker littleprint merchandisebond cadarok ncrflambeau; do
  count=$(grep -c "quote-gauge.css" mlangprintauto/$p/index.php 2>/dev/null || echo 0)
  if [ "$count" -eq 0 ]; then
    echo "❌ FAIL: $p — quote-gauge.css 링크 누락"
  else
    echo "✅ PASS: $p — quote-gauge.css 링크 존재"
  fi
done
```

**PASS:** 모든 9개 페이지에서 count ≥ 1
**FAIL:** CSS 누락 시 위젯 HTML은 존재하지만 스타일이 적용되지 않아 보이지 않음 (이번 사고의 원인)

**수정 방법:** 누락된 페이지의 `</head>` 바로 앞에 다음 추가:
```html
    <link rel="stylesheet" href="../../css/quote-gauge.css">
```

### Step 3: quote-gauge.js 스크립트 확인

```bash
for p in inserted sticker_new envelope namecard msticker littleprint merchandisebond cadarok ncrflambeau; do
  count=$(grep -c "quote-gauge.js" mlangprintauto/$p/index.php 2>/dev/null || echo 0)
  if [ "$count" -eq 0 ]; then
    echo "❌ FAIL: $p — quote-gauge.js 스크립트 누락"
  else
    echo "✅ PASS: $p — quote-gauge.js 스크립트 존재"
  fi
done
```

**PASS:** 모든 9개 페이지에서 count ≥ 1
**FAIL:** JS 누락 시 위젯이 보이지만 가격 연동/견적 기능이 작동하지 않음

### Step 4: 견적서 모드 체크 래핑 확인

`quote_gauge.php` include가 `isQuotationMode` 조건으로 감싸져 있는지 확인합니다.

```bash
for p in inserted sticker_new envelope namecard msticker littleprint merchandisebond cadarok ncrflambeau; do
  has_check=$(grep -B2 "quote_gauge.php" mlangprintauto/$p/index.php 2>/dev/null | grep -c "isQuotationMode" || echo 0)
  if [ "$has_check" -eq 0 ]; then
    echo "❌ FAIL: $p — isQuotationMode 체크 누락 (견적서 모달에서도 위젯이 표시됨)"
  else
    echo "✅ PASS: $p — isQuotationMode 체크 존재"
  fi
done
```

**PASS:** include 앞 2줄 이내에 `isQuotationMode` 조건이 존재
**FAIL:** 견적서 모달 모드에서 플로팅 위젯이 겹쳐 표시됨

### Step 5: mysqli_close 순서 확인 (PHP 8.2 호환)

`quote_gauge.php`는 `$db` 연결이 필요합니다. `mysqli_close($db)` 뒤에 include하면 PHP 8.2에서 Fatal Error 발생.

```bash
for p in inserted sticker_new envelope namecard msticker littleprint merchandisebond cadarok ncrflambeau; do
  gauge_line=$(grep -n "quote_gauge.php" mlangprintauto/$p/index.php 2>/dev/null | head -1 | cut -d: -f1)
  close_line=$(grep -n "mysqli_close" mlangprintauto/$p/index.php 2>/dev/null | head -1 | cut -d: -f1)
  if [ -z "$gauge_line" ]; then
    echo "❌ FAIL: $p — quote_gauge.php 자체가 없음"
  elif [ -z "$close_line" ]; then
    echo "✅ PASS: $p — mysqli_close 없음 (안전)"
  elif [ "$gauge_line" -lt "$close_line" ]; then
    echo "✅ PASS: $p — gauge(L$gauge_line) < close(L$close_line)"
  else
    echo "❌ FAIL: $p — gauge(L$gauge_line) > close(L$close_line) ← PHP 8.2 Fatal Error 위험!"
  fi
done
```

**PASS:** `quote_gauge.php` 라인 번호 < `mysqli_close` 라인 번호 (또는 close 없음)
**FAIL:** 순서 역전 시 프로덕션(PHP 8.2)에서 위젯이 사라지고 에러 로그만 남음

### Step 6: 소스 파일 존재 확인

위젯의 3개 소스 파일이 모두 존재하는지 확인합니다.

```bash
for f in includes/quote_gauge.php css/quote-gauge.css js/quote-gauge.js; do
  if [ -f "$f" ]; then
    echo "✅ PASS: $f 존재"
  else
    echo "❌ FAIL: $f 누락!"
  fi
done
```

## Output Format

```markdown
## 실시간 견적받기 위젯 검증 결과

| 제품 | include | CSS | JS | 모드체크 | DB순서 |
|------|---------|-----|-----|---------|--------|
| 전단지 (inserted) | ✅/❌ | ✅/❌ | ✅/❌ | ✅/❌ | ✅/❌ |
| 스티커 (sticker_new) | ✅/❌ | ✅/❌ | ✅/❌ | ✅/❌ | ✅/❌ |
| ... | ... | ... | ... | ... | ... |

**소스 파일:** quote_gauge.php ✅ | quote-gauge.css ✅ | quote-gauge.js ✅
**결과:** 전체 PASS / N개 FAIL
```

## Exceptions

다음은 **위반이 아닙니다**:

1. **견적서 모달 모드** — `?quotation_mode=1` 또는 `?admin_quote_mode=1`로 접근 시 위젯이 안 보이는 것은 정상 (의도된 동작)
2. **site_settings에서 비활성화** — `quote_widget_enabled = '0'`으로 설정된 경우 위젯이 안 보이는 것은 정상 (관리자가 끈 것)
3. **모바일 CSS 숨김** — CSS 미디어 쿼리로 모바일에서 위젯을 숨기는 것은 정상 (반응형 디자인)
