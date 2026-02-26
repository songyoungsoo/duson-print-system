---
name: verify-css-important
description: CSS/PHP 코드에서 새로 추가되는 !important 사용을 탐지합니다. 명시도(specificity) 전쟁을 방지하여 유지보수성을 보호합니다. CSS 또는 인라인 스타일 수정 후 사용.
---

# CSS !important 사용 금지 검증

## Purpose

CSS `!important`는 명시도(specificity) 규칙을 무시하고 스타일을 강제 적용합니다.
한 번 쓰면 그걸 덮기 위해 또 `!important`가 필요하고, 결국 "접착제 탑"이 됩니다:

1. **명시도 전쟁** — !important 위에 !important, 끝없는 에스컬레이션
2. **디버깅 불가** — 왜 이 스타일이 적용되는지 추적 불가능
3. **테마/반응형 파괴** — 미디어쿼리나 테마 전환이 !important에 막혀서 작동 안 함
4. **유지보수 비용** — 다른 개발자(또는 AI)가 수정하려면 또 !important를 써야 함

이 스킬은 새로 작성하는 코드에서 `!important` 사용을 탐지합니다.

## When to Run

- CSS 파일을 새로 만들거나 수정한 후
- PHP 파일에 인라인 `style="..."` 을 추가한 후
- UI/레이아웃 관련 코드를 수정한 후
- AI에게 스타일링 작업을 시킨 후 (AI가 !important를 남용하는 경향)

## Related Files

| File | Purpose |
|------|---------|
| `css/quotation-modal-common.css` | 견적서 모달 오버라이드 (42건 !important — 구조적 예외) |
| `css/brand-design-system.css` | 디자인 시스템 (0건 — 모범 사례) |
| `css/product-layout.css` | 레이아웃 (0건 — 모범 사례) |
| `css/color-system-unified.css` | 색상 변수 (0건 — 모범 사례) |
| `mlangorder_printauto/OrderComplete_universal.php` | 주문완료 인라인 스타일 (기존 레거시) |
| `mlangorder_printauto/OrderFormOrderTree.php` | 주문서 인라인 스타일 284건 (기존 레거시) |

## Workflow

### Step 1: CSS 파일에서 !important 탐지

**도구:** Grep
**대상:** 모든 CSS 파일 (벤더/아카이브 제외)

```bash
grep -rn "!important" --include="*.css" \
  --exclude-dir="archive" \
  --exclude-dir="vendor" \
  --exclude-dir="node_modules" \
  --exclude-dir="kginicis" \
  --exclude-dir="PHPMailer" \
  .
```

**PASS 기준:** 결과가 예외 목록(Exceptions)에 해당하는 파일만 나오는 경우
**FAIL 기준:** 예외에 해당하지 않는 CSS 파일에서 새로운 !important 발견

**위반 시 수정:**
```css
/* ❌ 위반 */
.product-nav { display: grid !important; }

/* ✅ 수정 방법 1: 명시도 높이기 */
.mobile-view .product-nav { display: grid; }

/* ✅ 수정 방법 2: CSS 변수 사용 */
:root { --nav-display: grid; }
.product-nav { display: var(--nav-display); }

/* ✅ 수정 방법 3: 더 구체적인 선택자 */
body .content .product-nav { display: grid; }
```

### Step 2: PHP 인라인 스타일에서 !important 탐지

**도구:** Grep
**대상:** 모든 PHP 파일

```bash
grep -rn 'style="[^"]*!important' --include="*.php" \
  --exclude-dir="m/" \
  --exclude-dir="vendor" \
  .
```

**PASS 기준:** 새로 추가된 인라인 !important가 없는 경우
**FAIL 기준:** 새로 작성한 PHP 코드에 `style="...!important..."` 발견

**위반 시 수정:**
```php
<!-- ❌ 위반: 인라인 !important -->
<div style="display: none !important;">

<!-- ✅ 수정: CSS 클래스 사용 -->
<div class="hidden">

<!-- ✅ 수정: data 속성 + CSS -->
<div data-state="hidden">
```

```css
/* 외부 CSS 파일에서 */
.hidden { display: none; }
[data-state="hidden"] { display: none; }
```

### Step 3: JavaScript 동적 !important 탐지

**도구:** Grep
**대상:** 모든 JS/PHP 파일

```bash
grep -rn "\.style\..*important\|cssText.*important\|setProperty.*important" \
  --include="*.js" --include="*.php" .
```

**PASS 기준:** 결과 0건
**FAIL 기준:** JavaScript에서 `element.style.setProperty('prop', 'value', 'important')` 사용

## Output Format

| # | 파일 | 라인 | 문제 | 수정 방법 |
|---|------|------|------|-----------|
| 1 | `파일경로:라인번호` | 코드 | !important 사용 | 명시도로 해결하는 코드 |

## Exceptions

다음은 **위반이 아닙니다**:

1. **`css/quotation-modal-common.css`** — 견적서 모달 오버라이드 시스템. `.quotation-modal-mode` 선택자 내에서 9개 제품의 스타일을 덮어써야 하므로 구조적으로 !important 필요. 단, 이 파일에 새 !important를 추가할 때는 기존 패턴(`.quotation-modal-mode` 접두사)을 반드시 따를 것
2. **벤더/서드파티 CSS** — `kginicis/`, `PHPMailer/`, `payment/css/` 등 외부 라이브러리의 CSS는 우리가 수정할 대상이 아님
3. **`css/archive/` 폴더** — 아카이브된 죽은 코드 (theme-ms.css 등)
4. **`[x-cloak]` 패턴** — Alpine.js 표준 패턴 `[x-cloak] { display: none !important; }`은 프레임워크 관례
5. **`m/` 모바일 백업 폴더** — `m/mlangprintauto260104/` 등 날짜 붙은 백업 폴더
6. **기존 레거시 코드** — 이미 존재하는 !important는 INFO로 보고 (FAIL이 아님). "과거는 용서, 미래는 엄격" 원칙
7. **`@media print` 내부** — 인쇄 스타일에서 브라우저 기본값을 덮기 위한 !important는 허용. 단, 새로 추가 시 주석으로 사유 명시 필요: `/* print override: 브라우저 기본 마진 제거 */`

## Prevention Tips

!important를 쓰고 싶을 때 순서대로 시도:

```
1단계: CSS 로드 순서 확인
  → 내 CSS가 다른 CSS 뒤에 로드되는지? 순서만 바꿔도 해결될 수 있음

2단계: 선택자 명시도 높이기
  → .btn 대신 .product-page .btn 으로 한 단계만 올리면 이김

3단계: CSS 변수(:root) 활용
  → 값을 변수로 빼면 한 곳에서 제어 가능

4단계: 그래도 안 되면 → 구조 문제. !important가 아니라 CSS 아키텍처를 수정해야 함
```
