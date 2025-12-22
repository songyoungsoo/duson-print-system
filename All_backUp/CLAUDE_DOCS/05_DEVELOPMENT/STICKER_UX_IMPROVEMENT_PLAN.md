# 스티커 페이지 UI/UX 개선 계획서

**작성일**: 2025-12-06
**대상 페이지**: http://dsp1830.shop/mlangprintauto/sticker_new/
**분석 파일**: `/var/www/html/mlangprintauto/sticker_new/index.php`

---

## 1. 현재 상태 분석

### 1.1 페이지 구조
```
┌─────────────────────────────────────────────────────────────┐
│  Header (top-header)                                        │
├─────────────────────────────────────────────────────────────┤
│  Page Title: 스티커 인쇄                                     │
├───────────────────────────┬─────────────────────────────────┤
│  Gallery Section (50%)    │  Calculator Section (50%)       │
│  ┌─────────────────────┐  │  ┌─────────────────────────┐   │
│  │ Main Gallery        │  │  │ Form Fields:            │   │
│  │ (4 thumbnails)      │  │  │ - 재질 (jong)           │   │
│  └─────────────────────┘  │  │ - 가로 (garo)           │   │
│  ┌─────────────────────┐  │  │ - 세로 (sero)           │   │
│  │ Size Preview Canvas │  │  │ - 수량 (mesu)           │   │
│  │ (500×400)           │  │  │ - 편집 (uhyung)         │   │
│  └─────────────────────┘  │  │ - 모양 (domusong)       │   │
│  [템플릿 다운로드 버튼]    │  └─────────────────────────┘   │
│                           │  ┌─────────────────────────┐   │
│                           │  │ Price Display           │   │
│                           │  │ 공급가 + VAT = 합계     │   │
│                           │  └─────────────────────────┘   │
│                           │  [주문하기] [장바구니]         │
└───────────────────────────┴─────────────────────────────────┘
```

### 1.2 현재 기능 목록

| 기능 | 구현 상태 | 위치 |
|------|----------|------|
| 실시간 가격 계산 | ✅ 완료 | debounced (150ms) |
| 크기 미리보기 캔버스 | ✅ 완료 | 500×400px |
| 템플릿 다운로드 (SVG) | ✅ 완료 | 동적 생성 |
| 템플릿 다운로드 (AI) | ✅ 완료 | download_ai.php |
| 49mm 이하 자동 사각도무송 | ✅ 완료 | checkSizeAndAutoSelect() |
| 파일 업로드 모달 | ✅ 완료 | drag-and-drop 지원 |
| 재질 안내 모달 | ✅ 완료 | 이미지 가이드 |
| 견적 모드 | ✅ 완료 | ?mode=quotation |
| 갤러리 팝업 | ✅ 완료 | UnifiedGalleryPopup |

---

## 2. 발견된 UI/UX 문제점

### 2.1 🔴 Critical (즉시 개선 필요)

#### 문제 1: 크기 입력 → 미리보기 연결 불명확
- **현상**: 사용자가 가로/세로 입력 후 미리보기 캔버스와의 관계를 인지하기 어려움
- **원인**: 캔버스가 갤러리 섹션 하단에 위치하여 시각적 연결성 부족
- **영향**: 사용자가 자신이 주문하는 스티커 형태를 직관적으로 파악하기 어려움

#### 문제 2: 귀돌이 반경 입력 UI 부재
- **현상**: 도무송에서 "귀돌이"를 선택해도 귀돌이 반경(mm) 입력 필드가 없음
- **원인**: 캔버스에는 `cornerRadius` 계산 로직이 있지만 (`Math.min(w, h) / 6`) 사용자 입력 UI 없음
- **영향**: 사용자가 원하는 귀돌이 반경을 지정할 수 없음

#### 문제 3: 템플릿 다운로드 버튼 가시성 부족
- **현상**: 크기 입력 전에는 버튼이 숨겨져 있어 기능 인지 불가
- **원인**: `display: none` → 크기 입력 후에만 표시
- **영향**: 신규 사용자가 템플릿 제공 기능을 모를 수 있음

### 2.2 🟡 Important (우선 개선)

#### 문제 4: 폼 필드 레이블 불명확
- **현상**: "편집" 필드가 무엇을 의미하는지 불명확
- **원인**: 레이블이 "편집"으로만 표시 (사용자는 디자인 편집이라 오해 가능)
- **개선안**: "편집 옵션" 또는 "디자인 서비스" + 툴팁 추가

#### 문제 5: 가격 표시 영역 분리
- **현상**: 공급가, VAT, 합계가 한 줄로 표시되어 가독성 저하
- **원인**: 컴팩트한 레이아웃 우선
- **개선안**: 3행 구조로 변경 (공급가 / VAT / **합계**)

#### 문제 6: 도무송 옵션 설명 부족
- **현상**: 각 도무송 모양(원형, 타원형, 귀돌이 등)의 차이점 설명 없음
- **원인**: select option에 텍스트만 표시
- **개선안**: 아이콘 또는 미니 프리뷰 추가

### 2.3 🟢 Nice-to-Have (선택 개선)

#### 문제 7: 모바일 반응형 미흡
- **현상**: 768px 이하에서 캔버스 크기 조정 불충분
- **원인**: 고정 캔버스 크기 (500×400)
- **개선안**: 뷰포트 기반 동적 크기 조정

#### 문제 8: 진행 상태 표시 부재
- **현상**: 가격 계산 중 로딩 표시 없음
- **원인**: debounce만 적용, 로딩 UI 미구현
- **개선안**: 계산 중 스피너 또는 스켈레톤 UI

#### 문제 9: 입력 검증 메시지 개선
- **현상**: alert()으로 오류 표시 (구식 UX)
- **원인**: 레거시 검증 코드
- **개선안**: inline 에러 메시지로 전환

---

## 3. 개선 계획

### Phase 1: 크리티컬 이슈 해결 (1-2일)

#### 3.1.1 크기 미리보기 위치 재배치

**Before**:
```
[갤러리] [미리보기캔버스]
                        [폼 필드]
```

**After**:
```
[갤러리]                [폼 필드]
                        [미리보기캔버스] ← 폼 옆으로 이동
```

**구현 방법**:
```css
/* 캔버스를 calculator-section 내부로 이동 */
.size-preview-container {
    position: relative;
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

/* 크기 입력 필드 바로 아래 배치 */
.calculator-section .size-preview-container {
    order: 2; /* 가로/세로 입력 후 표시 */
}
```

**JavaScript 수정** (index.php lines 2048-2310):
```javascript
// 캔버스 크기 조정 (calculator 섹션 너비에 맞춤)
function updateSizePreview() {
    const containerWidth = document.querySelector('.product-calculator').offsetWidth - 40;
    canvas.width = Math.min(containerWidth, 400);
    canvas.height = canvas.width * 0.8; // 5:4 비율 유지
    // ... 기존 그리기 로직
}
```

#### 3.1.2 귀돌이 반경 입력 필드 추가

**HTML 추가** (index.php line ~250 이후):
```html
<div class="option-row corner-radius-row" id="cornerRadiusRow" style="display:none;">
    <label for="cornerRadius">귀돌이 반경</label>
    <div class="input-with-unit">
        <input type="number" id="cornerRadius" name="cornerRadius"
               min="1" max="50" value="5" step="1">
        <span class="unit">mm</span>
    </div>
    <small class="hint">1~50mm (기본값: 5mm)</small>
</div>
```

**JavaScript 추가**:
```javascript
// 도무송 변경 시 귀돌이 입력 필드 표시/숨김
document.getElementById('domusong').addEventListener('change', function() {
    const cornerRow = document.getElementById('cornerRadiusRow');
    if (this.value.includes('귀돌')) {
        cornerRow.style.display = 'flex';
    } else {
        cornerRow.style.display = 'none';
    }
    updateSizePreview();
});

// 캔버스 그리기에 사용자 귀돌이 반영
function updateSizePreview() {
    const domusongValue = document.getElementById('domusong').value;
    let cornerRadius = 0;

    if (domusongValue.includes('귀돌')) {
        cornerRadius = parseFloat(document.getElementById('cornerRadius').value) || 5;
    }
    // ... cornerRadius를 scale에 맞게 적용
}
```

#### 3.1.3 템플릿 다운로드 버튼 상시 표시

**현재 코드** (index.php line ~365):
```html
<div class="template-download" id="templateDownload" style="display:none;">
```

**개선안**:
```html
<div class="template-download" id="templateDownload">
    <p class="template-notice">
        <span class="icon">📐</span>
        크기를 입력하면 작업용 템플릿을 다운로드할 수 있습니다
    </p>
    <div class="download-buttons" style="display:none;">
        <button type="button" id="downloadSvgBtn">SVG 템플릿</button>
        <button type="button" id="downloadAiBtn">AI 템플릿</button>
    </div>
</div>
```

**CSS 추가**:
```css
.template-notice {
    color: #6c757d;
    font-size: 13px;
    text-align: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.template-download .download-buttons.active + .template-notice {
    display: none;
}
```

---

### Phase 2: UX 개선 (2-3일)

#### 3.2.1 도무송 옵션 시각화

**구현**: 각 옵션에 아이콘 또는 미니 SVG 추가

```html
<select id="domusong" name="domusong">
    <option value="01000 칼선없음">칼선없음</option>
    <option value="02000 원형도무송" data-icon="⬤">원형도무송</option>
    <option value="03000 타원형도무송" data-icon="⬭">타원형도무송</option>
    <option value="04000 귀돌이도무송" data-icon="▢">귀돌이도무송</option>
    <option value="08000 사각도무송" data-icon="▭">사각도무송</option>
</select>
```

**JavaScript (select2 또는 커스텀 드롭다운)**:
```javascript
// 커스텀 드롭다운으로 아이콘 표시
function initDomusongSelect() {
    const select = document.getElementById('domusong');
    const options = select.querySelectorAll('option');

    options.forEach(opt => {
        const icon = opt.dataset.icon;
        if (icon) {
            opt.textContent = `${icon} ${opt.textContent}`;
        }
    });
}
```

#### 3.2.2 가격 표시 영역 개선

**현재**: `공급가: 50,000원 + VAT: 5,000원 = 55,000원`

**개선안**:
```html
<div class="price-display-modern">
    <div class="price-row supply">
        <span class="label">공급가액</span>
        <span class="value" id="supplyPrice">50,000원</span>
    </div>
    <div class="price-row vat">
        <span class="label">부가세 (10%)</span>
        <span class="value" id="vatAmount">5,000원</span>
    </div>
    <div class="price-row total">
        <span class="label">결제금액</span>
        <span class="value" id="totalPrice">55,000원</span>
    </div>
</div>
```

**CSS**:
```css
.price-display-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    margin: 20px 0;
}

.price-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.price-row.total {
    border-top: 1px solid rgba(255,255,255,0.3);
    margin-top: 10px;
    padding-top: 15px;
    font-size: 1.3em;
    font-weight: bold;
}
```

#### 3.2.3 입력 검증 개선 (alert → inline)

**현재 코드** (index.php line ~1708):
```javascript
if (tooSmallTarget && allowAlert) {
    alert('별도견적을 요청하세요 문의 1688-2384');
}
```

**개선안**:
```javascript
function showInlineError(field, message) {
    const input = document.getElementById(field);
    const errorDiv = input.parentElement.querySelector('.field-error')
        || document.createElement('div');

    errorDiv.className = 'field-error';
    errorDiv.innerHTML = `<span class="error-icon">⚠️</span> ${message}`;

    if (!input.parentElement.querySelector('.field-error')) {
        input.parentElement.appendChild(errorDiv);
    }

    input.classList.add('has-error');
}

function clearInlineError(field) {
    const input = document.getElementById(field);
    const errorDiv = input.parentElement.querySelector('.field-error');
    if (errorDiv) errorDiv.remove();
    input.classList.remove('has-error');
}

// 사용 예
if (tooSmallTarget) {
    showInlineError('garo', '별도견적이 필요합니다. 문의: 1688-2384');
} else {
    clearInlineError('garo');
}
```

---

### Phase 3: 모바일 최적화 (1-2일)

#### 3.3.1 반응형 캔버스

```javascript
function getResponsiveCanvasSize() {
    const container = document.querySelector('.size-preview-container');
    const containerWidth = container.offsetWidth;

    if (window.innerWidth < 576) {
        return { width: containerWidth - 20, height: (containerWidth - 20) * 0.8 };
    } else if (window.innerWidth < 768) {
        return { width: Math.min(350, containerWidth), height: 280 };
    } else {
        return { width: 400, height: 320 };
    }
}

window.addEventListener('resize', debounce(function() {
    const size = getResponsiveCanvasSize();
    canvas.width = size.width;
    canvas.height = size.height;
    updateSizePreview();
}, 250));
```

#### 3.3.2 모바일 터치 최적화

```css
@media (max-width: 768px) {
    /* 터치 타겟 크기 증가 */
    .option-row input[type="number"],
    .option-row select {
        min-height: 48px;
        font-size: 16px; /* iOS 자동 줌 방지 */
    }

    /* 버튼 크기 증가 */
    .btn-primary, .btn-secondary {
        min-height: 52px;
        font-size: 16px;
    }

    /* 캔버스 터치 영역 */
    .size-preview-container {
        touch-action: none; /* 스크롤 방지 */
    }
}
```

---

## 4. 구현 우선순위

| 순서 | 작업 | 예상 시간 | 영향도 | 난이도 |
|------|------|----------|--------|--------|
| 1 | 귀돌이 반경 입력 필드 추가 | 2시간 | 🔴 높음 | 🟢 낮음 |
| 2 | 템플릿 다운로드 가시성 개선 | 1시간 | 🔴 높음 | 🟢 낮음 |
| 3 | 크기 미리보기 위치 재배치 | 3시간 | 🟡 중간 | 🟡 중간 |
| 4 | 가격 표시 영역 리디자인 | 2시간 | 🟡 중간 | 🟢 낮음 |
| 5 | 입력 검증 inline 전환 | 2시간 | 🟡 중간 | 🟢 낮음 |
| 6 | 도무송 옵션 시각화 | 3시간 | 🟢 낮음 | 🟡 중간 |
| 7 | 모바일 반응형 개선 | 4시간 | 🟢 낮음 | 🟡 중간 |

**총 예상 시간**: 약 17시간 (2-3일)

---

## 5. 테스트 체크리스트

### 기능 테스트
- [ ] 가로/세로 입력 → 캔버스 미리보기 업데이트
- [ ] 도무송 "귀돌이" 선택 → 귀돌이 반경 입력 필드 표시
- [ ] 귀돌이 반경 변경 → 캔버스 반영
- [ ] SVG/AI 템플릿 다운로드 (각 모양별)
- [ ] 49mm 이하 입력 → 자동 사각도무송 선택
- [ ] 가격 계산 정확성
- [ ] 파일 업로드 (drag-and-drop, 클릭)
- [ ] 장바구니 추가
- [ ] 견적 모드 (?mode=quotation)

### 브라우저 테스트
- [ ] Chrome (최신)
- [ ] Firefox (최신)
- [ ] Safari (macOS/iOS)
- [ ] Edge (최신)
- [ ] 모바일 Chrome (Android)
- [ ] 모바일 Safari (iOS)

### 접근성 테스트
- [ ] 키보드 네비게이션
- [ ] 스크린 리더 호환성
- [ ] 색상 대비 (WCAG 2.1 AA)

---

## 6. 롤백 계획

**백업 파일**:
```bash
# 개선 작업 전 백업
cp /var/www/html/mlangprintauto/sticker_new/index.php \
   /var/www/html/mlangprintauto/sticker_new/index.php.backup_$(date +%Y%m%d)
```

**롤백 명령**:
```bash
# 문제 발생 시 롤백
cp /var/www/html/mlangprintauto/sticker_new/index.php.backup_YYYYMMDD \
   /var/www/html/mlangprintauto/sticker_new/index.php
```

---

## 7. 결론

스티커 페이지는 기능적으로 완성도가 높으나, **사용자 경험 관점에서 3가지 핵심 개선이 필요**합니다:

1. **귀돌이 반경 입력 UI** - 사용자가 원하는 모서리 곡률 지정 불가
2. **미리보기 캔버스 위치** - 입력 필드와의 시각적 연결성 부족
3. **템플릿 다운로드 가시성** - 기능 인지 어려움

이 3가지를 우선 개선하면 사용자 만족도가 크게 향상될 것으로 예상됩니다.

---

*작성자: Claude*
*최종 수정: 2025-12-06*
