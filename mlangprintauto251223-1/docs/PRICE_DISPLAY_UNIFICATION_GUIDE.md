# MlangPrintAuto 가격 표시 통합 가이드

## 📋 개요
이 문서는 MlangPrintAuto의 모든 제품 페이지에서 가격 표시 시스템을 통합하는 과정과 방법을 설명합니다.

**작성일**: 2025년 1월  
**참조 구현**: 스티커 페이지 (`sticker_new/index.php`)  
**통합 CSS**: `/css/unified-price-display.css`

---

## 🎯 통합 목표
- 모든 제품 페이지의 가격 표시를 스티커 방식으로 통일
- 중복 CSS 코드 제거 및 중앙 관리
- 일관된 사용자 경험 제공

---

## 📐 통합 가격 표시 사양

### 1. **레이아웃**
- **한 줄 표시**: 인쇄비, 디자인비, 부가세 포함 금액을 한 줄에 표시
- **중앙 정렬**: flexbox를 사용한 중앙 정렬
- **간격**: 각 항목 간 15px gap

### 2. **스타일**
- **큰 금액 크기**: 0.98rem (기존 2.2rem에서 축소)
- **세부 정보 크기**: 0.8rem
- **부가세 강조**: 빨간색(#dc3545) 1rem 크기
- **배경**: 그라데이션 배경 with 녹색 테두리

### 3. **HTML 구조**
```html
<div class="price-display" id="priceDisplay">
    <div class="price-label">견적 금액</div>
    <div class="price-amount" id="priceAmount">0원</div>
    <div class="price-details" id="priceDetails">
        <span>인쇄비: 0원</span>
        <span>디자인비: 0원</span>
        <span>부가세 포함: <span class="vat-amount">0원</span></span>
    </div>
</div>
```

---

## 🔧 통합 작업 단계

### Step 1: 공통 CSS 파일 생성
**파일**: `/css/unified-price-display.css`

```css
/* 통합 가격 표시 시스템 */
.price-display {
    margin-bottom: 5px !important;
    padding: 8px 5px !important;
    border-radius: 8px !important;
    background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%) !important;
    border: 2px solid #28a745 !important;
    text-align: center !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.1) !important;
}

.price-display .price-amount {
    font-size: 0.98rem !important;  /* 스티커 방식: 작은 크기 */
    font-weight: 700 !important;
    color: #28a745 !important;
}

.price-display .price-details {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 15px !important;
    flex-wrap: nowrap !important;
}

.price-display .price-details .vat-amount {
    color: #dc3545 !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
}
```

### Step 2: 각 제품 페이지에 공통 CSS 추가
각 제품의 `index.php` 파일 `<head>` 섹션에 추가:

```html
<!-- 통합 가격 표시 시스템 -->
<link rel="stylesheet" href="../../css/unified-price-display.css">
```

### Step 3: 인라인 CSS 제거
각 제품 페이지의 `<style>` 태그에서 `.price-display` 관련 CSS 블록 제거:

**제거 대상**:
```css
/* 3단계: 통일된 가격 표시 - 녹색 큰 글씨 (인쇄비+편집비=공급가) */
.price-display { ... }
.price-display.calculated { ... }
.price-display .price-label { ... }
.price-display .price-amount { ... }
.price-display .price-details { ... }
.price-display:hover { ... }
```

**대체**:
```css
/* 가격 표시는 공통 CSS (../../css/unified-price-display.css) 사용 */
```

### Step 4: JavaScript 업데이트
각 제품의 JavaScript 파일에서 `updatePriceDisplay` 함수 수정:

```javascript
function updatePriceDisplay(priceData) {
    const priceDetails = document.getElementById('priceDetails');
    
    if (priceDetails) {
        priceDetails.innerHTML = `
            <span>인쇄비: ${printCost.toLocaleString()}원</span>
            <span>디자인비: ${designCost.toLocaleString()}원</span>
            <span>부가세 포함: <span class="vat-amount">${total.toLocaleString()}원</span></span>
        `;
    }
}
```

---

## 📂 파일 구조 통일

### JavaScript 경로 통일
전단지 방식의 로컬 경로 구조 사용:

**이전 (공통 경로)**:
```html
<script src="../../js/cadarok.js"></script>
```

**현재 (로컬 경로)**:
```html
<script src="js/cadarok.js"></script>
```

**디렉토리 구조**:
```
MlangPrintAuto/
├── inserted/
│   ├── index.php
│   └── js/
│       └── leaflet-compact.js
├── cadarok/
│   ├── index.php
│   └── js/
│       └── cadarok.js
└── [기타 제품들...]
```

### 작업 명령어:
```bash
# JavaScript 디렉토리 생성
mkdir -p "C:\xampp\htdocs\MlangPrintAuto\[제품명]\js"

# JavaScript 파일 복사
cp "C:\xampp\htdocs\js\[제품명].js" "C:\xampp\htdocs\MlangPrintAuto\[제품명]\js\[제품명].js"
```

---

## ✅ 적용 완료 현황

### 공통 CSS 적용 및 중복 제거 완료 (8개 품목)

| 제품 | 경로 | CSS 적용 | 중복 제거 | JS 수정 |
|------|------|----------|-----------|---------|
| 전단지 | `inserted/index.php` | ✅ | ✅ | ✅ |
| 명함 | `namecard/index.php` | ✅ | ✅ | ✅ |
| 봉투 | `envelope/index.php` | ✅ | ✅ | ✅ |
| 상품권 | `merchandisebond/index.php` | ✅ | ✅ | ✅ |
| 양식지 | `ncrflambeau/index.php` | ✅ | ✅ | ✅ |
| 카다록 | `cadarok/index.php` | ✅ | ✅ | ✅ |
| 포스터 | `littleprint/index.php` | ✅ | ✅ | ✅ |
| 자석스티커 | `msticker/index.php` | ✅ | ✅ | ✅ |

### 기준 페이지
- **스티커** (`sticker_new/index.php`) - 원본 구현체로 인라인 CSS 유지

---

## 🔍 검증 방법

### 1. CSS 적용 확인
```bash
# unified-price-display.css 포함 확인
grep -r "unified-price-display.css" MlangPrintAuto/
```

### 2. 중복 CSS 확인
```bash
# price-display 인라인 CSS 잔존 확인
grep -r "\.price-display.*{" MlangPrintAuto/*/index.php
```

### 3. JavaScript 구조 확인
```bash
# vat-amount 클래스 사용 확인
grep -r "vat-amount" MlangPrintAuto/*/js/
```

---

## 📊 효과

### 코드 최적화
- **중복 제거**: 141줄의 중복 CSS 코드 제거
- **파일 크기**: 각 제품당 약 3KB 감소
- **로딩 속도**: CSS 캐싱으로 페이지 로딩 속도 향상

### 유지보수성
- **중앙 관리**: 한 곳에서 모든 가격 표시 스타일 관리
- **일관성**: 모든 제품이 동일한 가격 표시 UI 제공
- **확장성**: 새 제품 추가 시 간단한 CSS 링크만 필요

### 사용자 경험
- **일관된 UI**: 모든 제품에서 동일한 가격 표시
- **가독성**: 한 줄 표시로 정보 파악 용이
- **시각적 강조**: VAT 빨간색 강조로 중요 정보 부각

---

## 🚀 향후 적용 가이드

### 새 제품 추가 시:
1. `index.php`에 공통 CSS 링크 추가
2. HTML 구조를 표준 형식으로 작성
3. JavaScript에서 `vat-amount` 클래스 사용
4. 제품 폴더 내 `js/` 디렉토리 생성

### 스타일 변경 시:
1. `/css/unified-price-display.css` 파일만 수정
2. 모든 제품에 자동 반영
3. 브라우저 캐시 갱신 필요

---

## 📝 주의사항

1. **!important 사용**: 기존 스타일 오버라이드를 위해 필수
2. **flexbox 호환성**: IE11 이상에서만 정상 작동
3. **JavaScript 동기화**: HTML 구조 변경 시 JS 함수도 함께 수정
4. **캐시 관리**: CSS 변경 후 브라우저 캐시 삭제 필요

---

## 🔗 관련 문서
- `CLAUDE.md` - 프로젝트 전체 구조
- `PROJECT_SUCCESS_REPORT.md` - 각 제품별 구현 사양
- `Frontend-Compact-Design-Guide.md` - 컴팩트 디자인 가이드

---

*Last Updated: 2025년 1월*  
*Author: Claude AI Assistant*  
*Version: 1.0*