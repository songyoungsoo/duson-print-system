# 브랜드 디자인 시스템 (Brand Design System)

**두손기획인쇄 디자인 톤앤매너 가이드**

마지막 업데이트: 2025-10-11

---

## 📋 목차
1. [브랜드 컬러 시스템](#브랜드-컬러-시스템)
2. [타이포그래피](#타이포그래피)
3. [버튼 디자인](#버튼-디자인)
4. [컴포넌트 스타일](#컴포넌트-스타일)
5. [사용 방법](#사용-방법)

---

## 🎨 브랜드 컬러 시스템

### 메인 컬러 - Deep Navy
```css
Primary: #1E4E79
Dark: #153A5A
Light: #2D6FA8
Lighter: #E8F0F7
```

**사용 예시:**
- 헤더, 푸터 배경
- 주요 버튼 (CTA)
- 제목, 강조 텍스트
- 네비게이션 요소

### 포인트 컬러 - Bright Yellow
```css
Accent: #FFD500
Dark: #E6C000
Light: #FFE14D
Lighter: #FFF9CC
```

**사용 예시:**
- 강조 버튼
- 배지, 라벨
- 특별 프로모션
- 호버 효과

### 보조 컬러 - Grayscale
```css
White: #FFFFFF
Light Gray: #F4F4F4
Medium Gray: #E0E0E0
Dark Gray: #757575
Black: #212121
```

**사용 예시:**
- 배경 (#F4F4F4)
- 카드 배경 (White)
- 테두리, 구분선 (Light/Medium Gray)
- 본문 텍스트 (Dark Gray, Black)

---

## ✍️ 타이포그래피

### 폰트 패밀리

**한글 폰트:**
```css
font-family: 'Pretendard', 'Noto Sans KR', sans-serif;
```

**영문/숫자 폰트:**
```css
font-family: 'Poppins', 'Pretendard', sans-serif;
```

### 폰트 크기 체계

| 용도 | 크기 | CSS Variable |
|------|------|--------------|
| 대형 제목 | 36px | `--font-size-4xl` |
| 큰 제목 | 30px | `--font-size-3xl` |
| 중형 제목 | 24px | `--font-size-2xl` |
| 소형 제목 | 20px | `--font-size-xl` |
| 본문 강조 | 18px | `--font-size-lg` |
| 기본 본문 | 16px | `--font-size-base` |
| 작은 텍스트 | 14px | `--font-size-sm` |
| 매우 작은 텍스트 | 12px | `--font-size-xs` |

### 폰트 굵기

| 용도 | 굵기 | CSS Variable |
|------|------|--------------|
| Light | 300 | `--font-weight-light` |
| Regular | 400 | `--font-weight-normal` |
| Medium | 500 | `--font-weight-medium` |
| SemiBold | 600 | `--font-weight-semibold` |
| Bold | 700 | `--font-weight-bold` |

---

## 🔘 버튼 디자인

### 디자인 원칙
✅ **둥근 모서리** (border-radius: 8px)
✅ **그림자 효과** (명확한 클릭 유도)
✅ **호버 애니메이션** (살짝 위로 이동)

### 버튼 스타일

#### Primary 버튼 (Deep Navy)
```html
<button class="btn btn-primary">주문하기</button>
```
- 배경: #1E4E79
- 텍스트: White
- 그림자: 적용
- 용도: 주요 액션 (주문, 견적, 확인)

#### Accent 버튼 (Bright Yellow)
```html
<button class="btn btn-accent">특가 상품 보기</button>
```
- 배경: #FFD500
- 텍스트: Dark Gray
- 그림자: 적용
- 용도: 프로모션, 특별 이벤트

#### Outline 버튼
```html
<button class="btn btn-outline-primary">자세히 보기</button>
```
- 배경: 투명
- 테두리: 2px solid
- 용도: 보조 액션

### 버튼 크기

```html
<button class="btn btn-sm">작은 버튼</button>
<button class="btn">기본 버튼</button>
<button class="btn btn-lg">큰 버튼</button>
```

---

## 🧱 컴포넌트 스타일

### 카드 컴포넌트

```html
<div class="card">
    <h3 class="card-title">스티커 인쇄</h3>
    <p class="card-text">다양한 크기와 재질의 스티커 제작</p>
</div>
```

**특징:**
- 둥근 모서리 (12px)
- 그림자 효과
- 호버 시 살짝 위로 이동 (transform: translateY(-4px))

### 배지 컴포넌트

```html
<span class="badge badge-primary">신규</span>
<span class="badge badge-accent">특가</span>
<span class="badge badge-success">완료</span>
```

### 폼 요소

```html
<input type="text" placeholder="입력하세요">
<textarea placeholder="메시지를 입력하세요"></textarea>
<select>
    <option>옵션 선택</option>
</select>
```

**특징:**
- 테두리: 2px solid
- Focus 시 브랜드 컬러 강조
- 둥근 모서리 (4px)

---

## 🚀 사용 방법

### 1. CSS 파일 로드

모든 페이지의 `<head>` 섹션에 추가:

```html
<!-- 브랜드 디자인 시스템 (최우선 로드) -->
<link rel="stylesheet" href="/css/brand-design-system.css">

<!-- 브랜드 폰트 -->
<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

### 2. CSS 변수 사용

#### 컬러 사용
```css
.my-element {
    background-color: var(--brand-primary);
    color: var(--brand-gray-100);
}
```

#### 폰트 사용
```css
.my-text {
    font-family: var(--font-primary);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
}
```

#### 간격 사용
```css
.my-container {
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-md);
}
```

### 3. 유틸리티 클래스 사용

```html
<!-- 텍스트 컬러 -->
<p class="text-primary">브랜드 메인 컬러</p>
<p class="text-accent">포인트 컬러</p>

<!-- 배경 컬러 -->
<div class="bg-primary">배경 Deep Navy</div>
<div class="bg-accent">배경 Bright Yellow</div>

<!-- 그림자 -->
<div class="shadow-md">중간 그림자</div>

<!-- 둥근 모서리 -->
<div class="rounded-lg">큰 둥근 모서리</div>

<!-- 간격 -->
<div class="p-lg m-md">패딩 큰, 마진 중간</div>
```

---

## 📐 레이아웃 가이드

### 사진 스타일

**제품 실사 중심:**
- 배경: 흰색 또는 라이트 톤
- 조명: 자연광 느낌
- 구도: 깔끔하고 정돈된 배치

**예시:**
```html
<img src="product.jpg" alt="스티커 샘플" style="background: white; border-radius: 8px;">
```

### 반응형 디자인

```css
/* 모바일 우선 */
.container {
    padding: var(--spacing-md);
}

/* 태블릿 이상 */
@media (min-width: 768px) {
    .container {
        padding: var(--spacing-lg);
    }
}

/* 데스크톱 */
@media (min-width: 1024px) {
    .container {
        padding: var(--spacing-xl);
    }
}
```

---

## ✅ 체크리스트

새로운 페이지나 컴포넌트 제작 시 확인:

- [ ] 브랜드 컬러 시스템 사용 (#1E4E79, #FFD500)
- [ ] Pretendard / Poppins 폰트 적용
- [ ] 버튼은 둥근 모서리 + 그림자 효과
- [ ] 카드 컴포넌트는 호버 애니메이션
- [ ] 제품 사진은 흰색/라이트톤 배경
- [ ] CSS 변수 활용 (커스텀 값 최소화)
- [ ] 모바일 반응형 확인

---

## 🎯 브랜드 아이덴티티

**핵심 가치:**
- **신뢰성**: Deep Navy로 전문성과 안정감 표현
- **활력**: Bright Yellow로 밝고 적극적인 이미지
- **명확성**: 깔끔한 레이아웃과 명확한 버튼 디자인

**디자인 철학:**
> "클릭하고 싶게 만드는 명확한 UI, 신뢰할 수 있는 전문성"

---

## 📞 문의

디자인 시스템 관련 문의:
- 위치: `/var/www/html/css/brand-design-system.css`
- 문서: `/var/www/html/CLAUDE_DOCS/BRAND_DESIGN_SYSTEM.md`

---

**Last Updated:** 2025-10-11
**Version:** 1.0.0
