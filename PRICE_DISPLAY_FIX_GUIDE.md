# 가격 표시 한줄 통일 수정 가이드

이 문서는 MlangPrintAuto 시스템에서 가격 표시가 한줄로 나타나지 않는 문제의 해결 방법을 정리합니다.

## 🔍 문제 진단 과정

### 1. 문제 증상
- 가격 표시가 여러 줄로 나뉘어 표시됨
- 부가세 포함 금액이 빨간색으로 표시되지 않음
- 인쇄비, 디자인비, 부가세 포함이 세로로 배치됨

### 2. 원인 분석
주로 **CSS 우선순위 충돌**로 인한 flex 레이아웃 방해:

#### A. 인라인 스타일 문제
```html
<!-- 문제: -->
<div class="price-amount" id="priceAmount" style="margin: 0 0.2rem 0 0;">

<!-- 해결: -->
<div class="price-amount" id="priceAmount">
```

#### B. JavaScript textContent vs innerHTML 문제
```javascript
// 문제:
if (priceDetails) priceDetails.textContent = '기본 메시지';

// 해결:
if (priceDetails) priceDetails.innerHTML = '<span>기본 메시지</span>';
```

#### C. CSS 파일간 충돌 문제
- **namecard-compact.css**의 `.price-details` 스타일이 flex 레이아웃을 차단
- **통합 CSS**와 **개별 페이지 CSS**의 우선순위 충돌

## ⚡ 해결 방법 체크리스트

### 1단계: HTML 인라인 스타일 제거
각 제품 페이지의 `index.php`에서 price-display 관련 인라인 스타일 제거:

```php
<!-- 수정 전 -->
<div class="price-amount" id="priceAmount" style="margin: 0 0.2rem 0 0;">견적 계산 필요</div>

<!-- 수정 후 -->
<div class="price-amount" id="priceAmount">견적 계산 필요</div>
```

### 2단계: JavaScript 수정
각 제품의 JavaScript 파일 (`js/제품명.js`) 수정:

#### A. resetPrice 함수
```javascript
// 수정 전
if (priceDetails) priceDetails.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';

// 수정 후
if (priceDetails) priceDetails.innerHTML = '<span>모든 옵션을 선택하면 자동으로 계산됩니다</span>';
```

#### B. updatePriceDisplay 함수
```javascript
if (priceDetails) {
    priceDetails.innerHTML = `
        <span>인쇄비: ${formatNumber(priceData.base_price)}원</span>
        <span>디자인비: ${formatNumber(priceData.design_price)}원</span>
        <span>부가세 포함: <strong style="color: #dc3545;">${formatNumber(Math.round(priceData.total_with_vat))}원</strong></span>
    `;
}
```

### 3단계: CSS 충돌 해결

#### A. 개별 페이지 내장 CSS 수정
각 제품 페이지의 `<style>` 태그 내에서:

```css
.price-display .price-details {
    font-size: 0.8rem !important;
    color: #6c757d !important;
    line-height: 1.4 !important;
    margin-top: 8px !important;
    
    /* 한 줄 표시 강제 */
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 15px !important;
    flex-wrap: nowrap !important;
    white-space: nowrap !important;
    overflow-x: auto !important;
}
```

#### B. namecard-compact.css 수정
**가장 중요**: 이 파일이 모든 제품에 영향을 주므로 반드시 수정 필요

```css
.price-details {
    font-size: 0.85rem;
    color: #6c757d;
    line-height: 1.5;
    
    /* 한 줄 표시 강제 - 모든 제품에서 통일 */
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 15px !important;
    flex-wrap: nowrap !important;
    white-space: nowrap !important;
    overflow-x: auto !important;
}
```

## 🔧 진단 도구

### 1. 인라인 스타일 검색
```bash
# 특정 제품 페이지에서 인라인 스타일 검색
grep -n "style=" 제품폴더/index.php
```

### 2. CSS 충돌 검색
```bash
# price-details 관련 CSS 규칙 검색
grep -n "price-details" css/*.css
```

### 3. JavaScript 함수 검색
```bash
# resetPrice 함수에서 textContent 사용 검색
grep -n "textContent" js/제품명.js
```

## 📋 수정된 제품 목록

### ✅ 완료된 제품
1. **msticker** (자석스티커) - 2025년 완료
   - HTML 인라인 스타일 제거
   - JavaScript innerHTML 방식으로 변경
   - 빨간색 VAT 표시 적용

2. **envelope** (봉투) - 2025년 완료
   - HTML 인라인 스타일 제거
   - JavaScript innerHTML 방식으로 변경
   - namecard-compact.css 충돌 해결
   - 빨간색 VAT 표시 적용

### 📝 향후 수정 예정 제품
- **inserted** (전단지)
- **NameCard** (명함)
- **sticker** (일반스티커)
- **LittlePrint** (포스터)
- **cadarok** (카다록)
- **MerchandiseBond** (상품권)
- **NcrFlambeau** (NCR지)

## 🎯 표준 결과물

수정 완료 후 예상 결과:
```
인쇄비: 10,000원   디자인비: 5,000원   부가세 포함: 16,500원
                                              ^^^^^^^^
                                              빨간색으로 표시
```

- 모든 가격 요소가 **한 줄**에 표시
- 중앙 정렬
- 15px 간격
- 부가세 포함 금액만 **빨간색 (#dc3545)**
- 반응형 지원 (모바일에서는 가로 스크롤)

## ⚠️ 주의사항

1. **CSS 우선순위**: `!important` 사용으로 강제 적용
2. **호환성**: 기존 기능 유지하면서 표시만 개선
3. **반응형**: 모바일에서 가로 스크롤로 대응
4. **일관성**: 모든 제품에서 동일한 형태 유지

---
*문서 작성일: 2025년*  
*마지막 업데이트: envelope 제품 수정 완료*