# 상품권(Merchandisebond) 프리미엄 옵션 스타일 가이드

**문서 작성일**: 2026-03-06  
**스크린샷 캡처**: Playwright 자동화  
**대상 페이지**: `/mlangprintauto/merchandisebond/index.php`

---

## 📋 개요

상품권 주문 페이지의 프리미염 옵션(박, 넘버링, 미싱, 귀돌이, 오시) 섹션의 UI/UX 스타일 및 레이아웃을 문서화합니다.

---

## 🎨 프리미엄 옵션 섹션 구조

### HTML 구조

```html
<div class="namecard-premium-options-section" id="premiumOptionsSection" style="margin-top: 15px;">
    <!-- 한 줄 체크박스 헤더 -->
    <div class="option-headers-row">
        <div class="option-checkbox-group">
            <input type="checkbox" id="foil_enabled" name="foil_enabled" class="option-toggle" value="1">
            <label for="foil_enabled" class="toggle-label">박</label>
        </div>
        <div class="option-checkbox-group">
            <input type="checkbox" id="numbering_enabled" name="numbering_enabled" class="option-toggle" value="1">
            <label for="numbering_enabled" class="toggle-label">넘버링</label>
        </div>
        <div class="option-checkbox-group">
            <input type="checkbox" id="perforation_enabled" name="perforation_enabled" class="option-toggle" value="1">
            <label for="perforation_enabled" class="toggle-label">미싱</label>
        </div>
        <div class="option-checkbox-group">
            <input type="checkbox" id="rounding_enabled" name="rounding_enabled" class="option-toggle" value="1">
            <label for="rounding_enabled" class="toggle-label">귀돌이</label>
        </div>
        <div class="option-checkbox-group">
            <input type="checkbox" id="creasing_enabled" name="creasing_enabled" class="option-toggle" value="1">
            <label for="creasing_enabled" class="toggle-label">오시</label>
        </div>
        <div class="option-price-display">
            <span class="option-price-total" id="premiumPriceTotal">(+0원)</span>
        </div>
    </div>

    <!-- 옵션별 상세 드롭다운 (체크 시 표시) -->
    <div class="option-details" id="foil_options" style="display: none;">
        <select name="foil_type" id="foil_type" class="option-select">
            <option value="">선택하세요</option>
            <option value="gold_matte">금박무광 (500매 이하 30,000원, 초과시 매수×60원)</option>
            <option value="gold_gloss">금박유광 (500매 이하 30,000원, 초과시 매수×60원)</option>
            <!-- ... 8개 옵션 -->
        </select>
        <div class="option-note" style="font-size: 11px; color: #666; margin-top: 4px;">* 박(20mm×20mm 이하)</div>
    </div>
    <!-- 다른 옵션들도 동일한 구조 -->
</div>
```

---

## 🎯 레이아웃 분석

### 1. 컨테이너 레이아웃 (`.namecard-premium-options-section`)

| 속성 | 값 | 설명 |
|------|-----|------|
| **margin-top** | 15px | 위쪽 여백 |
| **display** | block | 블록 레이아웃 |

### 2. 체크박스 행 레이아웃 (`.option-headers-row`)

| 속성 | 값 | 설명 |
|------|-----|------|
| **display** | flex | 플렉스 레이아웃 |
| **flex-wrap** | nowrap | 한 줄 유지 (줄바꿈 없음) |
| **gap** | 8px | 항목 간 간격 |
| **align-items** | center | 수직 중앙 정렬 |

### 3. 개별 체크박스 그룹 (`.option-checkbox-group`)

| 속성 | 값 | 설명 |
|------|-----|------|
| **display** | flex | 플렉스 레이아웃 |
| **align-items** | center | 수직 중앙 정렬 |
| **gap** | 3px | 체크박스와 라벨 간 간격 |

### 4. 가격 표시 (`.option-price-display`)

| 속성 | 값 | 설명 |
|------|-----|------|
| **display** | inline-block | 인라인 블록 |
| **margin-left** | auto | 우측 정렬 |
| **font-size** | 14px | 기본 폰트 크기 |
| **color** | #e91e63 (핑크) | 상품권 브랜드 색상 |

---

## 📐 드롭다운 스타일 (`.option-select`)

### 기본 스타일

| 속성 | 값 | 설명 |
|------|-----|------|
| **font-size** | 14px | 폰트 크기 |
| **padding** | 0px 8px | 좌우 패딩 8px |
| **border** | 1px solid #dde2e6 | 라이트 그레이 테두리 |
| **border-radius** | 4px | 모서리 둥글기 |
| **background** | white | 흰색 배경 |
| **width** | 473.5px | 거의 전체 너비 |
| **margin** | 4px 0px | 상하 마진 4px |

### 드롭다운 컨테이너 (`.option-details`)

| 속성 | 값 | 설명 |
|------|-----|------|
| **display** | flex (체크 시) | 플렉스 레이아웃 |
| **flex-direction** | column | 세로 방향 |
| **margin-top** | 4px | 위쪽 여백 |
| **display** | none (기본) | 숨김 상태 |

---

## 🎨 색상 체계

### 브랜드 색상 (상품권)

| 요소 | 색상 | HEX | RGB |
|------|------|-----|-----|
| 가격 표시 | 핑크 | #e91e63 | rgb(233, 30, 99) |
| 가격 표시 (밝음) | 라이트 핑크 | #ff6b9d | rgb(255, 107, 157) |
| 테두리 | 라이트 그레이 | #dde2e6 | rgb(221, 226, 230) |
| 배경 | 흰색 | #ffffff | rgb(255, 255, 255) |
| 텍스트 | 다크 그레이 | #333333 | rgb(51, 51, 51) |
| 주석 텍스트 | 중간 그레이 | #666666 | rgb(102, 102, 102) |

---

## 📊 옵션 항목 (5가지)

### 1. 박 (Foil Stamping)

**ID**: `foil_enabled` / `foil_type`  
**라벨**: 박  
**드롭다운 옵션**: 8가지
- 금박무광 (30,000원)
- 금박유광 (30,000원)
- 은박무광 (30,000원)
- 은박유광 (30,000원)
- 청박유광 (30,000원)
- 적박유광 (30,000원)
- 녹박유광 (30,000원)
- 먹박유광 (30,000원)

**주석**: * 박(20mm×20mm 이하)

### 2. 넘버링 (Numbering)

**ID**: `numbering_enabled` / `numbering_type`  
**라벨**: 넘버링  
**드롭다운 옵션**: 2가지
- 1개 (60,000원)
- 2개 (60,000원 + 1000매당 15,000원)

**주석**: * 넘버링(1~9999)

### 3. 미싱 (Perforation)

**ID**: `perforation_enabled` / `perforation_type`  
**라벨**: 미싱  
**드롭다운 옵션**: 3가지
- 가로미싱 (20,000원)
- 세로미싱 (20,000원)
- 십자미싱 (30,000원)

**주석**: * 미싱선 1줄 기준

### 4. 귀돌이 (Corner Rounding)

**ID**: `rounding_enabled` / `rounding_type`  
**라벨**: 귀돌이  
**드롭다운 옵션**: 2가지
- 네귀돌이 (15,000원)
- 두귀돌이 (12,000원)

**주석**: * R값 3mm 기준

### 5. 오시 (Creasing)

**ID**: `creasing_enabled` / `creasing_type`  
**라벨**: 오시  
**드롭다운 옵션**: 2가지
- 1줄 오시 (18,000원)
- 2줄 오시 (25,000원)

**주석**: * 접는 선 가공

---

## 💰 가격 표시 시스템

### 가격 표시 위치

**위치**: 체크박스 행의 우측 끝  
**클래스**: `.option-price-display` > `.option-price-total`  
**ID**: `premiumPriceTotal`

### 가격 표시 형식

```
(+0원)           // 기본값 (옵션 미선택)
(+30,000원)      // 박 옵션 선택 시
(+60,000원)      // 넘버링 옵션 선택 시
(+30,000원)      // 미싱 옵션 선택 시
(+15,000원)      // 귀돌이 옵션 선택 시
(+18,000원)      // 오시 옵션 선택 시
```

### 가격 계산 로직

**파일**: `/mlangprintauto/merchandisebond/js/merchandisebond-premium-options.js`

```javascript
// 프리미엄 옵션 총액 계산
function calculatePremiumOptionsPrice() {
    let totalPrice = 0;
    
    // 각 옵션별 가격 합산
    if (foil_enabled) totalPrice += foilPrice;
    if (numbering_enabled) totalPrice += numberingPrice;
    if (perforation_enabled) totalPrice += perforationPrice;
    if (rounding_enabled) totalPrice += roundingPrice;
    if (creasing_enabled) totalPrice += creasingPrice;
    
    // 수량에 따른 추가 계산
    if (quantity > 500) {
        // 초과 수량에 대한 추가 요금 계산
    }
    
    return totalPrice;
}
```

---

## 🔄 상호작용 흐름

### 1. 체크박스 클릭 시

```
사용자가 "박" 체크박스 클릭
    ↓
JavaScript 이벤트 리스너 트리거
    ↓
#foil_options 드롭다운 표시 (display: block)
    ↓
가격 계산 함수 호출
    ↓
#premiumPriceTotal 업데이트 (+30,000원)
    ↓
전체 주문 가격 재계산
```

### 2. 드롭다운 선택 시

```
사용자가 드롭다운에서 옵션 선택 (예: "금박무광")
    ↓
선택 값 저장 (hidden input)
    ↓
가격 계산 함수 호출
    ↓
#premiumPriceTotal 업데이트
    ↓
전체 주문 가격 재계산
```

### 3. 체크박스 해제 시

```
사용자가 "박" 체크박스 해제
    ↓
#foil_options 드롭다운 숨김 (display: none)
    ↓
해당 옵션 가격 제거
    ↓
#premiumPriceTotal 업데이트 (감소)
    ↓
전체 주문 가격 재계산
```

---

## 📱 반응형 디자인

### 데스크톱 (1200px 이상)

- 체크박스 5개 + 가격 표시: **한 줄 배치**
- 드롭다운 너비: **473.5px** (거의 전체)
- 간격: **8px** (체크박스 간)

### 태블릿 (768px ~ 1199px)

- 체크박스 3개 + 가격: **첫 번째 줄**
- 체크박스 2개: **두 번째 줄**
- 드롭다운 너비: **조정됨**

### 모바일 (767px 이하)

- 체크박스: **세로 배치** (각 1개씩)
- 가격 표시: **별도 줄**
- 드롭다운 너비: **100%**

---

## 🔧 CSS 클래스 참조

| 클래스 | 용도 | 위치 |
|--------|------|------|
| `.namecard-premium-options-section` | 전체 컨테이너 | 최상위 |
| `.option-headers-row` | 체크박스 행 | 첫 번째 자식 |
| `.option-checkbox-group` | 개별 체크박스 그룹 | 행 내부 |
| `.option-toggle` | 체크박스 입력 | 그룹 내부 |
| `.toggle-label` | 체크박스 라벨 | 그룹 내부 |
| `.option-price-display` | 가격 표시 컨테이너 | 행 우측 |
| `.option-price-total` | 가격 텍스트 | 가격 컨테이너 내부 |
| `.option-details` | 드롭다운 컨테이너 | 행 아래 |
| `.option-select` | 드롭다운 선택 | 상세 내부 |
| `.option-note` | 주석 텍스트 | 드롭다운 아래 |

---

## 📸 스크린샷 참조

### 1. 초기 상태 (옵션 미선택)

**파일**: `merchandisebond-01-full-page.png`

- 5개 체크박스 모두 미선택
- 가격 표시: `(+0원)`
- 드롭다운 모두 숨김

### 2. "박" 옵션 선택 후

**파일**: `merchandisebond-02-with-dropdown.png`

- "박" 체크박스 선택됨 (체크 표시)
- 드롭다운 표시됨 (8개 옵션)
- 가격 표시: `(+30,000원)`
- 전체 가격: 65,000원 → 71,500원 (VAT 포함)

### 3. 옵션 섹션 확대

**파일**: `merchandisebond-03-options-section.png`

- 체크박스 행 전체 보기
- 드롭다운 상세 보기
- 가격 계산 결과 표시

---

## 🎯 개발 가이드

### 새로운 옵션 추가 시

1. **HTML 추가** (index.php)
   ```html
   <div class="option-checkbox-group">
       <input type="checkbox" id="new_option_enabled" name="new_option_enabled" class="option-toggle" value="1">
       <label for="new_option_enabled" class="toggle-label">새옵션</label>
   </div>
   ```

2. **드롭다운 추가** (index.php)
   ```html
   <div class="option-details" id="new_option_options" style="display: none;">
       <select name="new_option_type" id="new_option_type" class="option-select">
           <option value="">선택하세요</option>
           <option value="opt1">옵션1 (가격)</option>
       </select>
       <div class="option-note">* 주석</div>
   </div>
   ```

3. **JavaScript 이벤트 추가** (merchandisebond-premium-options.js)
   ```javascript
   document.getElementById('new_option_enabled').addEventListener('change', function() {
       document.getElementById('new_option_options').style.display = this.checked ? 'block' : 'none';
       calculatePrice();
   });
   ```

4. **가격 계산 로직 추가**
   ```javascript
   if (document.getElementById('new_option_enabled').checked) {
       totalPrice += getNewOptionPrice();
   }
   ```

### 스타일 커스터마이징

**CSS 파일**: `/mlangprintauto/merchandisebond/css/merchandisebond-inline-extracted.css`

```css
/* 체크박스 간격 조정 */
.option-headers-row {
    gap: 12px; /* 기본값: 8px */
}

/* 드롭다운 너비 조정 */
.option-select {
    width: 100%; /* 기본값: 473.5px */
}

/* 가격 색상 변경 */
.option-price-total {
    color: #ff6b9d; /* 기본값: #e91e63 */
}
```

---

## ✅ 테스트 체크리스트

- [ ] 모든 5개 체크박스 클릭 시 드롭다운 표시 확인
- [ ] 드롭다운 선택 시 가격 업데이트 확인
- [ ] 체크박스 해제 시 드롭다운 숨김 확인
- [ ] 가격 계산 정확성 확인 (수량별)
- [ ] 모바일 반응형 레이아웃 확인
- [ ] 브라우저 호환성 확인 (Chrome, Firefox, Safari, Edge)
- [ ] 접근성 확인 (키보드 네비게이션, 스크린 리더)

---

## 📝 관련 파일

| 파일 | 설명 |
|------|------|
| `/mlangprintauto/merchandisebond/index.php` | 메인 페이지 (HTML 구조) |
| `/mlangprintauto/merchandisebond/js/merchandisebond-premium-options.js` | 프리미엄 옵션 로직 |
| `/mlangprintauto/merchandisebond/css/merchandisebond-inline-extracted.css` | 스타일시트 |
| `/js/merchandisebond.js` | 통합 로직 |
| `/css/common-styles.css` | 공통 스타일 |

---

**마지막 업데이트**: 2026-03-06  
**작성자**: Claude Code (Playwright 자동화)  
**상태**: ✅ 완료
