# 견적서 모드 통합 프레임워크 (Quotation Mode Integration Framework)

**작성일**: 2025-11-30
**목적**: 모든 제품 페이지에서 견적서 모드가 정상 작동하도록 하는 체계적인 가이드
**범위**: 11개 제품 (inserted, namecard, envelope, sticker_new, msticker, cadarok, littleprint, merchandisebond, ncrflambeau, leaflet)

---

## 1. 시스템 아키텍처 개요

### 1.1 견적서 모드란?

견적서 모드는 제품 페이지를 **iframe 내에서 계산기만 표시**하여 견적서에 품목을 추가할 수 있게 하는 기능입니다.

```
┌─────────────────────────────────────────────────────────────┐
│ quote/create.php (부모 창)                                   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ <iframe src="mlangprintauto/[product]/?mode=quotation"> │
│  │                                                       │   │
│  │  ┌─────────────────────────────────────────────┐    │   │
│  │  │ product-calculator 섹션만 표시              │    │   │
│  │  │ (header, gallery, footer 숨김)              │    │   │
│  │  │                                             │    │   │
│  │  │ [옵션 선택] → [견적 계산] → [견적서에 적용] │    │   │
│  │  └─────────────────────────────────────────────┘    │   │
│  │                                                       │   │
│  │  postMessage() ────────────────────────────────────────┼──┤
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ← 가격 데이터 수신 후 견적서 테이블에 행 추가              │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 핵심 컴포넌트

| 컴포넌트 | 역할 | 필수 여부 |
|---------|------|----------|
| `mode=quotation` 감지 | URL 파라미터로 모드 판별 | ✅ 필수 |
| `quotation-modal-mode` CSS 클래스 | 레이아웃 최적화 | ✅ 필수 |
| `window.currentPriceData` | 가격 데이터 전역 저장 | ✅ 필수 |
| `sendToQuotation()` | 부모 창에 데이터 전송 | ✅ 필수 |
| `postMessage()` | iframe ↔ 부모 통신 | ✅ 필수 |
| 2단계 버튼 시스템 | 계산 → 적용 흐름 | ✅ 필수 |

---

## 2. 필수 구현 요소 체크리스트

### 2.1 PHP 백엔드 (index.php)

```php
// ✅ 1. 모드 감지 (파일 상단)
$is_quotation_mode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';

// ✅ 2. Body 클래스 추가
<body class="[product]-page<?php echo $is_quotation_mode ? ' quotation-modal-mode' : ''; ?>">

// ✅ 3. 조건부 렌더링 - Header
<?php if (!$is_quotation_mode): ?>
    <?php include "../../includes/header-ui.php"; ?>
    <?php include "../../includes/nav.php"; ?>
<?php endif; ?>

// ✅ 4. 조건부 렌더링 - Gallery
<?php if (!$is_quotation_mode): ?>
    <section class="product-gallery">
        <!-- 갤러리 코드 -->
    </section>
<?php endif; ?>

// ✅ 5. 조건부 렌더링 - Footer
<?php if (!$is_quotation_mode): ?>
    <?php include "../../includes/footer.php"; ?>
<?php endif; ?>

// ✅ 6. 조건부 렌더링 - Description
<?php if (!$is_quotation_mode): ?>
    <?php include "./explane_[product].php"; ?>
<?php endif; ?>
```

### 2.2 CSS 스타일 (공통 CSS 파일 로드)

```html
<!-- ✅ 7. 공통 CSS 파일 로드 (head 섹션) -->
<link rel="stylesheet" href="/css/quotation-modal-common.css">
```

**공통 CSS 내용** (`/css/quotation-modal-common.css`):
```css
/* 컨테이너 최적화 */
.quotation-modal-mode .product-container {
    max-width: 100% !important;
    padding: 10px !important;
    margin: 0 !important;
}

/* 1컬럼 레이아웃 */
.quotation-modal-mode .product-content {
    grid-template-columns: 1fr !important;
    gap: 0 !important;
}

/* 불필요한 요소 숨김 */
.quotation-modal-mode .page-title,
.quotation-modal-mode .product-gallery,
.quotation-modal-mode .footer,
.quotation-modal-mode header,
.quotation-modal-mode nav,
.quotation-modal-mode .product-description,
.quotation-modal-mode .explane-section {
    display: none !important;
}
```

### 2.3 JavaScript 필수 요소

```javascript
// ✅ 8. 전역 가격 데이터 변수 (window 객체에 저장)
window.currentPriceData = null;

// ✅ 9. 가격 계산 후 전역 변수 설정
function calculatePrice() {
    // ... 가격 계산 로직 ...

    // 🔴 핵심: window.currentPriceData 설정 필수!
    window.currentPriceData = {
        total_price: calculatedPrice,      // 공급가 (VAT 제외)
        vat_price: calculatedPriceWithVat, // 합계 (VAT 포함)
        vat_amount: vatAmount              // VAT 금액
    };
}

// ✅ 10. sendToQuotation 함수
window.sendToQuotation = function() {
    // 가격 데이터 검증
    if (!window.currentPriceData || !window.currentPriceData.total_price) {
        console.error('❌ 가격 데이터 없음');
        alert('먼저 견적 계산을 해주세요. "견적 계산" 버튼을 눌러주세요.');
        return;
    }

    // 옵션 데이터 수집
    const productData = {
        product_type: '[product_code]',
        product_name: '[제품명]',
        specification: getSpecificationString(),
        quantity: getQuantity(),
        unit_price: window.currentPriceData.total_price / getQuantity(),
        supply_price: window.currentPriceData.total_price,
        vat_amount: window.currentPriceData.vat_amount,
        total_price: window.currentPriceData.vat_price
    };

    // 부모 창에 전송
    window.parent.postMessage({
        type: 'quotation_item_selected',
        data: productData
    }, '*');

    console.log('✅ 견적 데이터 전송:', productData);
};

// ✅ 11. 2단계 버튼 시스템
window.quotationCalculated = false;

function onCalculateComplete() {
    window.quotationCalculated = true;
    updateQuotationButton();
}

function updateQuotationButton() {
    const btn = document.getElementById('quotation-btn');
    if (!btn) return;

    if (window.quotationCalculated && window.currentPriceData) {
        btn.innerHTML = '✅ 견적서에 적용';
        btn.onclick = window.sendToQuotation;
        btn.classList.add('ready');
    } else {
        btn.innerHTML = '💰 견적 계산';
        btn.onclick = () => document.getElementById('calculate-btn')?.click();
        btn.classList.remove('ready');
    }
}
```

### 2.4 HTML 버튼 구조

```html
<!-- ✅ 12. 견적 버튼 섹션 (견적 모드에서만 표시) -->
<?php if ($is_quotation_mode): ?>
<div class="quotation-button-section">
    <button type="button" id="quotation-btn" class="btn-quotation" onclick="document.getElementById('calculate-btn')?.click();">
        💰 견적 계산
    </button>
</div>
<?php endif; ?>
```

---

## 3. 흔한 문제와 해결 방법

### 3.1 문제: "먼저 견적 계산을 해주세요" 오류

**증상**:
- 가격이 정상적으로 표시됨
- "견적서에 적용" 버튼 클릭 시 "먼저 견적 계산을 해주세요" 메시지

**원인 분석 순서**:

```javascript
// 1단계: window.currentPriceData 확인
console.log('window.currentPriceData:', window.currentPriceData);
// 결과: undefined 또는 null이면 문제!
```

**근본 원인**:
- 가격 계산 함수가 **지역 변수**에만 저장하고 **전역 변수**에 저장하지 않음
- 특히 외부 JS 파일(예: `poster.js`)에서 `defer`로 로드될 때 발생

**해결 방법**:

```javascript
// ❌ 잘못된 코드 (지역 변수만 설정)
function calculatePrice() {
    // ...
    currentPriceData = priceData;  // 지역 변수!
}

// ✅ 올바른 코드 (전역 변수도 설정)
function calculatePrice() {
    // ...
    currentPriceData = priceData;  // 지역 변수 (내부 사용용)

    // 🔧 FIX: window.currentPriceData도 설정 (견적서 모드 호환용)
    window.currentPriceData = {
        total_price: priceData.total_price,
        vat_price: priceData.total_with_vat,
        vat_amount: priceData.vat || (priceData.total_with_vat - priceData.total_price)
    };
}
```

### 3.2 문제: "견적서에 적용" 버튼이 안 보임

**증상**:
- 견적 모드로 열었는데 버튼이 없음

**원인**:
- `$is_quotation_mode` 변수가 설정되지 않음
- 버튼 섹션이 조건부 렌더링에서 제외됨

**확인 방법**:
```bash
# URL에 mode=quotation 파라미터 확인
http://localhost/mlangprintauto/[product]/?mode=quotation

# PHP 변수 확인
<?php var_dump($is_quotation_mode); ?>
```

**해결 방법**:
```php
// 파일 상단에 추가
$is_quotation_mode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';

// 버튼 섹션 추가
<?php if ($is_quotation_mode): ?>
<div class="quotation-button-section">
    <button type="button" id="quotation-btn" class="btn-quotation">
        💰 견적 계산
    </button>
</div>
<?php endif; ?>
```

### 3.3 문제: 갤러리/헤더/푸터가 여전히 보임

**증상**:
- 견적 모드인데 전체 페이지 레이아웃이 표시됨

**확인 방법**:
```bash
# Body 클래스 확인
curl -s "http://localhost/mlangprintauto/[product]/?mode=quotation" | grep "quotation-modal-mode"
```

**해결 방법**:
1. Body 클래스에 `quotation-modal-mode` 추가 확인
2. 공통 CSS 파일 로드 확인
3. 조건부 렌더링 (`<?php if (!$is_quotation_mode): ?>`) 적용

### 3.4 문제: 가격 계산 API 오류

**증상**:
- 가격이 0으로 표시됨
- 콘솔에 AJAX 오류

**확인 방법**:
```bash
# API 직접 테스트
curl "http://localhost/mlangprintauto/[product]/calculate_price_ajax.php?MY_type=590&PN_type=604&MY_Fsd=100&MY_amount=1000"
```

**해결 방법**:
1. API 엔드포인트 경로 확인
2. 필수 파라미터 확인
3. DB 연결 확인

### 3.5 문제: postMessage가 부모에게 전달되지 않음

**증상**:
- 버튼 클릭해도 부모 창에 데이터가 안 들어감

**확인 방법**:
```javascript
// 자식 iframe에서
console.log('Sending postMessage...');
window.parent.postMessage({ type: 'test' }, '*');

// 부모 창에서 (quote/create.php)
window.addEventListener('message', function(e) {
    console.log('Received message:', e.data);
});
```

**해결 방법**:
```javascript
// 1. postMessage 타입 확인
window.parent.postMessage({
    type: 'quotation_item_selected',  // 정확한 타입명
    data: productData
}, '*');

// 2. 부모 창 리스너 확인 (quote/create.php)
window.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'quotation_item_selected') {
        handleQuotationItem(event.data.data);
    }
});
```

---

## 4. 제품별 특이사항

### 4.1 Cascade Dropdown 제품 (littleprint, cadarok, leaflet)

이 제품들은 **연속 드롭다운** 시스템을 사용합니다:
- MY_type → Section → PN_type → MY_amount

**주의사항**:
- 각 드롭다운 변경 시 **자동 가격 계산**이 트리거되어야 함
- 외부 JS 파일(예: `poster.js`, `cadarok.js`)에서 `window.currentPriceData` 설정 필수

**패턴**:
```javascript
// poster.js 또는 cadarok.js에서
.then(response => {
    if (response.success) {
        const priceData = response.data;
        currentPriceData = priceData;  // 내부 사용

        // 🔧 견적서 모드 호환
        window.currentPriceData = {
            total_price: priceData.total_price,
            vat_price: priceData.total_with_vat,
            vat_amount: priceData.vat || (priceData.total_with_vat - priceData.total_price)
        };
    }
});
```

### 4.2 프리미엄 옵션 제품 (cadarok, merchandisebond, ncrflambeau)

프리미엄 옵션이 있는 제품은 **추가 금액**을 포함해야 합니다.

**주의사항**:
```javascript
window.currentPriceData = {
    total_price: basePrice + premiumOptionsTotal,  // 기본가 + 추가옵션
    vat_price: (basePrice + premiumOptionsTotal) * 1.1,
    vat_amount: (basePrice + premiumOptionsTotal) * 0.1
};
```

### 4.3 단순 계산 제품 (namecard, envelope, sticker_new, msticker)

대부분 **인라인 스크립트**로 처리되어 문제가 적습니다.

**확인 포인트**:
- `window.currentPriceData` 설정 여부만 확인

---

## 5. 디버깅 가이드

### 5.1 단계별 진단

```javascript
// Step 1: 모드 확인
console.log('Is quotation mode:', document.body.classList.contains('quotation-modal-mode'));

// Step 2: 버튼 존재 확인
console.log('Quotation button:', document.getElementById('quotation-btn'));

// Step 3: 가격 데이터 확인
console.log('window.currentPriceData:', window.currentPriceData);

// Step 4: calculatePrice 함수 확인
console.log('calculatePrice function:', typeof calculatePrice);

// Step 5: sendToQuotation 함수 확인
console.log('sendToQuotation function:', typeof window.sendToQuotation);
```

### 5.2 네트워크 탭 확인

```
1. 브라우저 개발자 도구 열기 (F12)
2. Network 탭 선택
3. 페이지 새로고침
4. calculate_price_ajax.php 요청 확인
5. Response 탭에서 JSON 응답 확인
```

### 5.3 콘솔 로그 패턴

```javascript
// 가격 계산 함수에 추가
function calculatePrice() {
    console.log('=== calculatePrice 호출 ===');
    console.log('입력 파라미터:', {
        MY_type: document.getElementById('MY_type')?.value,
        Section: document.getElementById('Section')?.value,
        // ...
    });

    // AJAX 응답 후
    console.log('API 응답:', response);
    console.log('설정된 window.currentPriceData:', window.currentPriceData);
}
```

---

## 6. 신규 제품 적용 절차

### 6.1 체크리스트

```
□ 1. $is_quotation_mode 변수 추가 (파일 상단)
□ 2. Body 클래스에 조건부 'quotation-modal-mode' 추가
□ 3. Header/Nav 조건부 렌더링
□ 4. Gallery 조건부 렌더링
□ 5. Description 조건부 렌더링
□ 6. Footer 조건부 렌더링
□ 7. 공통 CSS 파일 로드
□ 8. window.currentPriceData 설정 확인 (calculatePrice 함수)
□ 9. sendToQuotation 함수 추가
□ 10. 견적 버튼 섹션 추가
□ 11. 테스트: ?mode=quotation 파라미터로 접속
□ 12. 테스트: 가격 계산 후 window.currentPriceData 확인
□ 13. 테스트: "견적서에 적용" 버튼 동작 확인
```

### 6.2 복사할 코드 템플릿

**PHP 상단 (모드 감지)**:
```php
<?php
// 견적 모드 감지
$is_quotation_mode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';
?>
```

**HTML Head (CSS)**:
```html
<link rel="stylesheet" href="/css/quotation-modal-common.css">
```

**HTML Body 태그**:
```html
<body class="[product]-page<?php echo $is_quotation_mode ? ' quotation-modal-mode' : ''; ?>">
```

**JS 가격 데이터 설정**:
```javascript
// calculatePrice() 함수 내에서
window.currentPriceData = {
    total_price: [공급가],
    vat_price: [VAT포함가],
    vat_amount: [VAT금액]
};
```

**JS sendToQuotation 함수**:
```javascript
window.sendToQuotation = function() {
    if (!window.currentPriceData || !window.currentPriceData.total_price) {
        alert('먼저 견적 계산을 해주세요. "견적 계산" 버튼을 눌러주세요.');
        return;
    }

    const productData = {
        product_type: '[product_code]',
        product_name: '[제품명]',
        specification: '[규격 문자열]',
        quantity: parseInt(document.getElementById('[수량필드ID]').value) || 0,
        unit_price: Math.round(window.currentPriceData.total_price / quantity),
        supply_price: window.currentPriceData.total_price,
        vat_amount: window.currentPriceData.vat_amount,
        total_price: window.currentPriceData.vat_price
    };

    window.parent.postMessage({
        type: 'quotation_item_selected',
        data: productData
    }, '*');
};
```

**HTML 버튼 섹션**:
```html
<?php if ($is_quotation_mode): ?>
<div class="quotation-button-section">
    <button type="button" id="quotation-btn" class="btn-quotation"
            onclick="document.getElementById('calculate-btn')?.click();">
        💰 견적 계산
    </button>
</div>
<?php endif; ?>
```

---

## 7. 참조 구현

### 7.1 완벽한 구현 예시: inserted (전단지)

**파일**: `/var/www/html/mlangprintauto/inserted/index.php`

**특징**:
- PHP 사전 로딩으로 드롭다운 초기화
- 인라인 JavaScript로 모든 로직 처리
- `window.currentPriceData` 정상 설정

### 7.2 외부 JS 파일 사용 예시: littleprint (포스터)

**파일**:
- `/var/www/html/mlangprintauto/littleprint/index.php`
- `/var/www/html/js/poster.js`

**특징**:
- `poster.js`가 cascade dropdown과 가격 계산 담당
- `defer` 로딩으로 인한 함수 덮어쓰기 주의
- **수정 필요 부분**: poster.js에 `window.currentPriceData` 설정 추가

---

## 8. 버전 히스토리

| 날짜 | 버전 | 변경 내용 |
|------|------|----------|
| 2025-11-30 | 1.0 | 최초 작성 - 포스터 문제 해결 후 문서화 |

---

## 9. 문의 및 지원

**문제 발생 시 확인 순서**:
1. 이 문서의 "3. 흔한 문제와 해결 방법" 섹션 참조
2. 브라우저 콘솔에서 `window.currentPriceData` 확인
3. Network 탭에서 API 응답 확인
4. "5. 디버깅 가이드" 단계별 진단 실행

---

*이 문서는 견적서 모드 관련 모든 문제의 해결 가이드입니다. 새로운 문제 발생 시 이 문서에 추가해주세요.*
