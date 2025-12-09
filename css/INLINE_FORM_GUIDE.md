# 🎨 통일 인라인 폼 스타일 적용 가이드

## 🚀 빠른 적용 방법

### 1️⃣ **CSS 링크 추가**
```html
<!-- 기존 CSS 뒤에 추가 -->
<link rel="stylesheet" href="../../css/unified-inline-form.css">
```

### 2️⃣ **HTML 구조 변경**
```html
<!-- 기존 그리드 방식 -->
<div class="form-grid">
    <div class="form-group">
        <label>라벨</label>
        <select>...</select>
    </div>
</div>

<!-- 새로운 인라인 방식 -->
<div class="inline-form-container">
    <div class="inline-form-row">
        <span class="inline-label">라벨</span>
        <select class="inline-select">...</select>
        <span class="inline-note">설명 텍스트</span>
    </div>
</div>
```

### 3️⃣ **클래스 매핑**
| 기존 클래스 | 새 클래스 | 용도 |
|------------|-----------|------|
| `.form-grid`, `.options-grid` | `.inline-form-container` | 폼 컨테이너 |
| `.form-group` | `.inline-form-row` | 각 옵션 행 |
| `label` | `.inline-label` | 라벨 (우측정렬) |
| `select`, `input` | `.inline-select`, `.inline-input` | 입력 필드 |
| `.help-text` | `.inline-note` | 설명 텍스트 |

## 🔧 **제품별 커스터마이징**

### 스티커 페이지
```html
<body class="sticker-page">
<!-- 자동으로 130px 폭 적용 -->
```

### 명함 페이지
```html
<body class="namecard-page">
<!-- 자동으로 180px 폭 적용 (긴 옵션명) -->
```

### 봉투 페이지
```html
<body class="envelope-page">
<!-- 자동으로 160px 폭 적용 -->
```

## 📐 **크기 조정 옵션**

### 라벨 크기
```html
<span class="inline-label">기본</span>        <!-- 50px -->
<span class="inline-label wide">넓은</span>    <!-- 70px -->
<span class="inline-label narrow">좁은</span>  <!-- 40px -->
```

### 셀렉트박스 크기
```html
<select class="inline-select">기본</select>        <!-- 150px -->
<select class="inline-select wide">넓은</select>    <!-- 180px -->
<select class="inline-select narrow">좁은</select>  <!-- 120px -->
```

## 🎨 **스타일 변형**

### 설명 텍스트 색상
```html
<span class="inline-note">일반</span>           <!-- 회색 -->
<span class="inline-note warning">경고</span>    <!-- 빨간색 -->
<span class="inline-note info">정보</span>       <!-- 파란색 -->
```

### 컴팩트 모드
```html
<div class="inline-form-container compact-mode">
<!-- 더 작은 패딩과 높이 -->
```

## 🔄 **기존 페이지 변환 단계**

### Step 1: HTML 구조 변경
```bash
# 찾기-바꾸기로 일괄 변경
<div class="form-group"> → <div class="inline-form-row">
<label> → <span class="inline-label">
</label> → </span>
class="form-control" → class="inline-select"
```

### Step 2: CSS 링크 추가
```html
<link rel="stylesheet" href="../../css/unified-inline-form.css">
```

### Step 3: 기존 CSS 제거
```css
/* 제거할 기존 스타일들 */
.form-grid { ... }
.form-group { ... }
.form-control { ... }
```

## 📋 **체크리스트**

- [ ] CSS 링크 추가
- [ ] HTML 클래스 변경
- [ ] 라벨을 `<span>`으로 변경
- [ ] 설명 텍스트 추가
- [ ] 기존 CSS 정리
- [ ] 반응형 테스트
- [ ] 계산 로직 동작 확인

## 🎯 **완성된 예시**

### 전체 폼 구조 (2025-01-14 업데이트)
```html
<!-- 통일 인라인 폼 시스템 -->
<div class="inline-form-container">
    <div class="inline-form-row">
        <span class="inline-label">종류</span>
        <select class="inline-select wide" onchange="calculatePrice()">
            <option>선택해주세요</option>
        </select>
        <span class="inline-note">기본 옵션을 선택하세요</span>
    </div>

    <div class="inline-form-row">
        <span class="inline-label">편집비</span>
        <select class="inline-select" onchange="calculatePrice()">
            <option value="print">인쇄만 의뢰</option>
            <option value="total">디자인+인쇄</option>
        </select>
        <span class="inline-note">디자인 작업 포함 여부</span>
    </div>
</div>

<!-- 통일된 가격 표시 (라벨 없는 깔끔한 스타일) -->
<div class="price-display calculated">
    <div class="price-amount">26,000원</div>
    <div class="price-details">
        <span>인쇄비: 26,000원</span>
        <span>부가세포함: <span class="vat-amount">28,600원</span></span>
    </div>
</div>
```

### 최근 업데이트 사항 (2025-01-14)
- ✅ **라벨 제거**: "견적 금액" 등의 불필요한 라벨 제거
- ✅ **깔끔한 표시**: 가격만 중앙 정렬로 깔끔하게 표시
- ✅ **통일성**: 모든 페이지에서 동일한 가격 표시 방식 사용
- ✅ **용어 통일**: "편집디자인" → "편집비"로 변경 (자동 변환 포함)
- ✅ **편집비 최적화**: 편집비 라벨과 셀렉트박스 기본 크기 사용으로 정렬 개선

## 📋 **특별 규칙**

### 편집비 필드 최적화
- **라벨**: `inline-label` (기본 50px, wide 클래스 사용 안함)
- **셀렉트**: `inline-select` (기본 150px, wide 클래스 사용 안함)
- **자동 변환**: "편집디자인" → "편집비" 자동 치환
- **정렬**: 다른 필드와 완벽한 수평 정렬 보장

```html
<!-- 편집비 표준 코드 -->
<div class="inline-form-row">
    <span class="inline-label">편집비</span>
    <select class="inline-select" onchange="calculatePrice()">
        <option value="print">인쇄만 의뢰</option>
        <option value="total">디자인+인쇄</option>
    </select>
    <span class="inline-note">디자인 작업 포함 여부</span>
</div>
```

## 🚨 **주의사항**

1. **계산 로직 보존**: `onchange` 이벤트는 반드시 유지
2. **클래스 우선순위**: `!important` 사용으로 기존 스타일 오버라이드
3. **반응형 대응**: 768px 이하에서 세로 배치로 자동 전환
4. **브라우저 호환**: IE11+ 지원

---
*Updated: 2025-01-14*
*Version: 2.0*
*Framework: SuperClaude Unified Design System*