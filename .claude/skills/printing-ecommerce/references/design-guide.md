# 디자인 리뉴얼 가이드

## 1. 디자인 방향

### 현재 → 목표
| 구분 | 현재 | 리뉴얼 |
|------|------|--------|
| 스타일 | 다채로운 색상, 복잡한 레이아웃 | 미니멀, 기업/금융 스타일 |
| 색상 | 다양한 원색 사용 | 절제된 컬러 팔레트 |
| 폰트 | 혼합 사용 | 일관된 서체 시스템 |
| 여백 | 불규칙 | 체계적인 스페이싱 |
| 컴포넌트 | 페이지마다 다름 | 통일된 디자인 시스템 |

### 핵심 원칙
1. **신뢰감** - 깔끔하고 전문적인 인상
2. **일관성** - 모든 페이지에서 동일한 경험
3. **명확성** - 정보 계층 구조가 분명
4. **접근성** - 모든 사용자가 쉽게 사용

---

## 2. 컬러 시스템

### Primary Colors
```css
:root {
    /* 메인 컬러 - 네이비 */
    --primary-900: #1a237e;
    --primary-800: #283593;
    --primary-700: #303f9f;
    --primary-600: #3949ab;
    --primary-500: #3f51b5;  /* 기본 */
    
    /* 액센트 - 레드 (CTA용) */
    --accent-500: #d32f2f;
    --accent-600: #c62828;
    --accent-700: #b71c1c;
}
```

### Neutral Colors (그레이스케일)
```css
:root {
    --gray-50: #fafafa;
    --gray-100: #f5f5f5;
    --gray-200: #eeeeee;
    --gray-300: #e0e0e0;
    --gray-400: #bdbdbd;
    --gray-500: #9e9e9e;
    --gray-600: #757575;
    --gray-700: #616161;
    --gray-800: #424242;
    --gray-900: #212121;
}
```

### Semantic Colors (상태)
```css
:root {
    --success: #2e7d32;
    --warning: #f57c00;
    --error: #d32f2f;
    --info: #1976d2;
}
```

### 사용 가이드
| 용도 | 컬러 |
|------|------|
| 헤더/네비게이션 | `--primary-800` |
| 주요 버튼 | `--primary-500` |
| 강조 버튼 (결제, 주문) | `--accent-500` |
| 본문 텍스트 | `--gray-800` |
| 보조 텍스트 | `--gray-600` |
| 배경 | `--gray-50` 또는 `white` |
| 테두리 | `--gray-200` |
| 가격 표시 | `--accent-600` |

---

## 3. 타이포그래피

### 폰트 패밀리
```css
:root {
    /* 한글: Pretendard, 영문/숫자: Inter */
    --font-sans: 'Pretendard', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-mono: 'JetBrains Mono', 'Consolas', monospace;
}

body {
    font-family: var(--font-sans);
    font-size: 16px;
    line-height: 1.6;
    color: var(--gray-800);
    -webkit-font-smoothing: antialiased;
}
```

### 폰트 스케일
```css
:root {
    --text-xs: 0.75rem;    /* 12px - 캡션 */
    --text-sm: 0.875rem;   /* 14px - 보조 텍스트 */
    --text-base: 1rem;     /* 16px - 본문 */
    --text-lg: 1.125rem;   /* 18px - 강조 */
    --text-xl: 1.25rem;    /* 20px - 소제목 */
    --text-2xl: 1.5rem;    /* 24px - 섹션 제목 */
    --text-3xl: 1.875rem;  /* 30px - 페이지 제목 */
    --text-4xl: 2.25rem;   /* 36px - 히어로 */
}
```

### 폰트 웨이트
```css
:root {
    --font-normal: 400;
    --font-medium: 500;
    --font-semibold: 600;
    --font-bold: 700;
}
```

### 사용 예시
```css
/* 페이지 제목 */
.page-title {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--gray-900);
}

/* 섹션 제목 */
.section-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-semibold);
    color: var(--gray-800);
}

/* 가격 */
.price {
    font-size: var(--text-xl);
    font-weight: var(--font-bold);
    color: var(--accent-600);
}

/* 캡션/도움말 */
.caption {
    font-size: var(--text-sm);
    color: var(--gray-600);
}
```

---

## 4. 스페이싱 시스템

### 기본 단위 (4px 기반)
```css
:root {
    --space-1: 0.25rem;   /* 4px */
    --space-2: 0.5rem;    /* 8px */
    --space-3: 0.75rem;   /* 12px */
    --space-4: 1rem;      /* 16px */
    --space-5: 1.25rem;   /* 20px */
    --space-6: 1.5rem;    /* 24px */
    --space-8: 2rem;      /* 32px */
    --space-10: 2.5rem;   /* 40px */
    --space-12: 3rem;     /* 48px */
    --space-16: 4rem;     /* 64px */
}
```

### 적용 가이드
| 용도 | 스페이싱 |
|------|----------|
| 인라인 요소 간격 | `--space-2` |
| 폼 필드 내부 패딩 | `--space-3` |
| 카드 내부 패딩 | `--space-4` ~ `--space-6` |
| 섹션 간 간격 | `--space-8` ~ `--space-12` |
| 페이지 상하 여백 | `--space-12` ~ `--space-16` |

---

## 5. 컴포넌트

### 버튼
```css
/* 기본 버튼 스타일 */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-3) var(--space-6);
    font-size: var(--text-base);
    font-weight: var(--font-medium);
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

/* Primary 버튼 */
.btn-primary {
    background: var(--primary-500);
    color: white;
}
.btn-primary:hover {
    background: var(--primary-600);
}

/* 강조 버튼 (결제, 장바구니) */
.btn-accent {
    background: var(--accent-500);
    color: white;
}
.btn-accent:hover {
    background: var(--accent-600);
}

/* 아웃라인 버튼 */
.btn-outline {
    background: transparent;
    border: 1px solid var(--gray-300);
    color: var(--gray-700);
}
.btn-outline:hover {
    border-color: var(--gray-400);
    background: var(--gray-50);
}

/* 버튼 크기 */
.btn-sm { padding: var(--space-2) var(--space-4); font-size: var(--text-sm); }
.btn-lg { padding: var(--space-4) var(--space-8); font-size: var(--text-lg); }
```

### 입력 필드
```css
.form-input {
    width: 100%;
    padding: var(--space-3) var(--space-4);
    font-size: var(--text-base);
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    background: white;
    transition: border-color 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.1);
}

.form-input::placeholder {
    color: var(--gray-400);
}

/* 에러 상태 */
.form-input.error {
    border-color: var(--error);
}

/* 레이블 */
.form-label {
    display: block;
    margin-bottom: var(--space-2);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: var(--gray-700);
}

/* 도움말 텍스트 */
.form-help {
    margin-top: var(--space-1);
    font-size: var(--text-xs);
    color: var(--gray-500);
}
```

### 카드
```css
.card {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    overflow: hidden;
}

.card-body {
    padding: var(--space-6);
}

.card-header {
    padding: var(--space-4) var(--space-6);
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-50);
}

/* 호버 효과 (상품 카드 등) */
.card-hover:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
    transition: all 0.2s ease;
}
```

### 테이블
```css
.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: var(--space-3) var(--space-4);
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
}

.table th {
    font-weight: var(--font-semibold);
    color: var(--gray-700);
    background: var(--gray-50);
}

.table tbody tr:hover {
    background: var(--gray-50);
}

/* 숫자/가격 정렬 */
.table .text-right {
    text-align: right;
}
```

### 배지
```css
.badge {
    display: inline-flex;
    padding: var(--space-1) var(--space-2);
    font-size: var(--text-xs);
    font-weight: var(--font-medium);
    border-radius: 4px;
}

.badge-pending { background: #fff3e0; color: #e65100; }
.badge-paid { background: #e3f2fd; color: #1565c0; }
.badge-printing { background: #f3e5f5; color: #7b1fa2; }
.badge-shipping { background: #e0f2f1; color: #00695c; }
.badge-completed { background: #e8f5e9; color: #2e7d32; }
```

---

## 6. 레이아웃

### 컨테이너
```css
.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--space-4);
}

.container-sm { max-width: 800px; }
.container-lg { max-width: 1400px; }
```

### 그리드
```css
.grid {
    display: grid;
    gap: var(--space-6);
}

.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
.grid-cols-4 { grid-template-columns: repeat(4, 1fr); }

/* 반응형 */
@media (max-width: 768px) {
    .grid-cols-2,
    .grid-cols-3,
    .grid-cols-4 {
        grid-template-columns: 1fr;
    }
}
```

### 헤더
```css
.header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: var(--primary-800);
    color: white;
}

.header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
}

.header-logo {
    font-size: var(--text-xl);
    font-weight: var(--font-bold);
    color: white;
    text-decoration: none;
}

.header-nav {
    display: flex;
    gap: var(--space-6);
}

.header-nav a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-weight: var(--font-medium);
}

.header-nav a:hover {
    color: white;
}
```

### 푸터
```css
.footer {
    background: var(--gray-900);
    color: var(--gray-400);
    padding: var(--space-12) 0;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: var(--space-8);
}

.footer-title {
    color: white;
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-4);
}
```

---

## 7. 페이지별 가이드

### 메인 페이지
```
┌─────────────────────────────────────┐
│ Header (네비게이션)                  │
├─────────────────────────────────────┤
│ Hero Section                        │
│ - 슬로건 + 주요 CTA                  │
├─────────────────────────────────────┤
│ 제품 카테고리 (3~4열 그리드)          │
│ ┌───┐ ┌───┐ ┌───┐ ┌───┐           │
│ │   │ │   │ │   │ │   │           │
│ └───┘ └───┘ └───┘ └───┘           │
├─────────────────────────────────────┤
│ 공지사항 / 이벤트                    │
├─────────────────────────────────────┤
│ Footer                              │
└─────────────────────────────────────┘
```

### 제품 상세 페이지
```
┌─────────────────────────────────────┐
│ Breadcrumb (홈 > 전단지 > 일반전단지) │
├─────────────────────────────────────┤
│ ┌─────────────┬───────────────────┐ │
│ │ 제품 이미지  │ 옵션 선택 영역     │ │
│ │             │ - 사이즈          │ │
│ │             │ - 용지            │ │
│ │             │ - 수량            │ │
│ │             │ - 추가옵션        │ │
│ │             ├───────────────────┤ │
│ │             │ 가격: 00,000원    │ │
│ │             │ [장바구니] [주문]  │ │
│ └─────────────┴───────────────────┘ │
├─────────────────────────────────────┤
│ 상세 설명 탭                         │
└─────────────────────────────────────┘
```

### 장바구니
```
┌─────────────────────────────────────┐
│ 장바구니 (3개 상품)                   │
├─────────────────────────────────────┤
│ ┌─────────────────────────────────┐ │
│ │ □ 상품명       수량  가격  삭제  │ │
│ ├─────────────────────────────────┤ │
│ │ ☑ 일반스티커   1,000  15,000  X │ │
│ │ ☑ 전단지       2,000  25,000  X │ │
│ └─────────────────────────────────┘ │
├─────────────────────────────────────┤
│            상품금액: 40,000원        │
│            배송비:    3,000원        │
│            ─────────────────        │
│            합계:     43,000원        │
│                                     │
│        [선택삭제]  [주문하기]         │
└─────────────────────────────────────┘
```

---

## 8. 반응형 브레이크포인트

```css
/* Mobile First */
:root {
    --breakpoint-sm: 640px;
    --breakpoint-md: 768px;
    --breakpoint-lg: 1024px;
    --breakpoint-xl: 1280px;
}

/* 태블릿 */
@media (min-width: 768px) {
    .container { padding: 0 var(--space-6); }
}

/* 데스크톱 */
@media (min-width: 1024px) {
    .container { padding: 0 var(--space-8); }
}
```

---

## 9. 아이콘

### 추천: Lucide Icons
```html
<!-- CDN -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- 사용 -->
<i data-lucide="shopping-cart"></i>
<i data-lucide="user"></i>
<i data-lucide="search"></i>

<script>lucide.createIcons();</script>
```

### 주요 아이콘 매핑
| 용도 | 아이콘명 |
|------|----------|
| 장바구니 | shopping-cart |
| 검색 | search |
| 사용자 | user |
| 주문내역 | package |
| 설정 | settings |
| 전화 | phone |
| 이메일 | mail |
| 위치 | map-pin |
| 파일 업로드 | upload |
| 다운로드 | download |

---

## 10. 마이그레이션 체크리스트

### Phase 1: 기반 작업
- [ ] CSS 변수 시스템 적용
- [ ] 폰트 (Pretendard) 적용
- [ ] 기본 레이아웃 구조 변경
- [ ] 공통 컴포넌트 CSS 작성

### Phase 2: 주요 페이지
- [ ] 헤더/푸터 리뉴얼
- [ ] 메인 페이지
- [ ] 제품 목록/상세 페이지
- [ ] 장바구니/주문서

### Phase 3: 서브 페이지
- [ ] 로그인/회원가입
- [ ] 마이페이지
- [ ] 공지사항/FAQ
- [ ] 관리자 페이지

### Phase 4: 마무리
- [ ] 반응형 테스트
- [ ] 브라우저 호환성 테스트
- [ ] 성능 최적화
