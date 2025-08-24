# 🎨 Frontend Compact Design Guide & Shadow Effects
**두손기획인쇄 컴팩트 디자인 시스템 & 그림자 효과 가이드**

## 📋 개요

### 목적
- 일관된 컴팩트 UI 디자인 시스템 구축
- 시각적 계층구조 강화를 위한 그림자 효과 적용
- 사용자 경험 향상 및 반응형 최적화

### 적용 페이지
- ✅ 봉투 (`/envelope/index.php`)
- ✅ 포스터 (`/LittlePrint/index_compact.php`)
- ✅ 양식지 (`/NcrFlambeau/index_compact.php`)
- ✅ 전단지 (`/inserted/index_compact.php`)
- ✅ 상품권 (`/MerchandiseBond/index.php`)
- ✅ 자석스티커 (`/msticker/index.php`)
- ✅ 스티커 (`/sticker_new/index.php`)
- ✅ 카다록 (`/cadarok/index.php`)
- ✅ 명함 (`/NameCard/index.php`)

---

## 🎯 7단계 컴팩트 디자인 방법론

### 1단계: Page-title 컴팩트화 (1/2 높이 축소)
```css
.page-title {
    padding: 12px 0 !important;          /* 1/2 축소 */
    margin-bottom: 15px !important;      /* 1/2 축소 */
    border-radius: 10px !important;      /* 2/3 축소 */
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
}

.page-title h1 {
    font-size: 1.6rem !important;        /* 27% 축소 */
    line-height: 1.2 !important;         /* 타이트 */
    margin: 0 !important;
    color: white !important;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
}

.page-title p {
    margin: 4px 0 0 0 !important;        /* 1/2 축소 */
    font-size: 0.85rem !important;       /* 15% 축소 */
    line-height: 1.3 !important;
    color: white !important;
    opacity: 0.9 !important;
}
```

### 2단계: Calculator-header 헤더 통일 디자인
```css
.calculator-header, .price-section h3, .price-calculator h3 {
    background: linear-gradient(135deg, [BRAND_COLOR] 0%, [BRAND_COLOR_DARK] 100%) !important;
    color: white !important;
    padding: 15px 20px !important;       /* gallery-title과 동일 */
    margin: 0px -25px 20px -25px !important; /* 좌우 -25px로 섹션 너비에 맞춤 */
    border-radius: 15px 15px 0 0 !important; /* gallery-title과 동일한 라운딩 */
    font-size: 1.1rem !important;        /* gallery-title과 동일 */
    font-weight: 600 !important;
    text-align: center !important;
    box-shadow: 0 2px 10px rgba([BRAND_COLOR_RGB], 0.3) !important;
    line-height: 1.2 !important;
}
```

### 3단계: Price-display 컴팩트화 (2/3 높이 축소)
```css
.price-display {
    padding: 8px 5px !important;         /* 상하 패딩 최적화 */
    border-radius: 8px !important;       /* 2/3 축소 */
    margin-bottom: 5px !important;
}

.price-display .price-label {
    font-size: 0.85rem !important;       /* 15% 축소 */
    margin-bottom: 4px !important;       /* 1/2 축소 */
    line-height: 1.2 !important;
}

.price-display .price-amount {
    font-size: 1.4rem !important;        /* 22% 축소 */
    margin-bottom: 6px !important;       /* 40% 축소 */
    line-height: 1.1 !important;
}

.price-display .price-details {
    font-size: 0.75rem !important;       /* 12% 축소 */
    line-height: 1.3 !important;
    margin: 0 !important;
}
```

### 4단계: Form 요소 컴팩트화 (패딩 1/2 축소)
```css
.option-select, select, input[type="text"], input[type="email"], textarea {
    padding: 6px 15px !important;        /* 상하 패딩 1/2 */
}

.option-group {
    margin-bottom: 8px !important;       /* 33% 축소 */
}
```

### 5단계: 그리드 레이아웃 최적화
```css
.main-content {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 20px !important;
    align-items: start !important; /* 그리드 아이템들을 상단 정렬 */
}

.options-grid {
    gap: 12px !important;                /* 25% 축소 */
}

.upload-order-button {
    margin-top: 8px !important;          /* 20% 축소 */
}
```

### 6단계: 섹션 그림자 효과 (강화된 시각적 구분)
```css
.gallery-section, .calculator-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    border-radius: 15px !important;
    padding: 25px !important;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    border: 1px solid rgba(255, 255, 255, 0.9) !important;
    position: relative !important;
    margin-top: 0 !important;
    align-self: start !important;
}
```

### 7단계: 반응형 최적화
```css
@media (max-width: 768px) {
    .page-title { 
        padding: 15px 0 !important;       /* 데스크톱보다 약간 여유 */
    }
    
    .page-title h1 {
        font-size: 1.4rem !important;     /* 가독성 고려 */
    }
    
    .calculator-header { 
        padding: 15px 20px !important;    /* 터치 친화적 */
    }
    
    .price-display .price-amount {
        font-size: 1.5rem !important;     /* 모바일 가독성 */
    }
    
    .option-select, select, input[type="text"], input[type="email"], textarea {
        padding: 10px 15px !important;    /* 터치 영역 확보 */
    }
}
```

---

## 🎨 브랜드 컬러 시스템

### 제품별 브랜드 컬러
| 제품 | 메인 컬러 | 다크 컬러 | RGB |
|------|-----------|-----------|-----|
| 봉투 | `#ff9800` | `#f57c00` | `255, 152, 0` |
| 포스터 | `#9c27b0` | `#673ab7` | `156, 39, 176` |
| 양식지 | `#1565c0` | `#0d47a1` | `21, 101, 192` |
| 전단지 | `#4caf50` | `#2e7d32` | `76, 175, 80` |
| 상품권 | `#e91e63` | `#ad1457` | `233, 30, 99` |
| 자석스티커 | `#00bcd4` | `#0097a7` | `0, 188, 212` |
| 스티커 | `#ffc107` | `#ff8f00` | `255, 193, 7` |
| 카다록 | `#6f42c1` | `#5a3a9a` | `111, 66, 193` |
| 명함 | `#17a2b8` | `#138496` | `23, 162, 184` |

### 갤러리 타이틀 스타일
```css
.gallery-title {
    background: linear-gradient(135deg, [BRAND_COLOR] 0%, [BRAND_COLOR_DARK] 100%);
    color: white;
    padding: 15px 20px;
    margin: -25px -25px 20px -25px;
    border-radius: 15px 15px 0 0;
    font-size: 1.1rem;
    font-weight: 600;
    text-align: center;
    box-shadow: 0 2px 10px rgba([BRAND_COLOR_RGB], 0.3);
}
```

---

## ✨ 그림자 효과 시스템

### 기본 그림자 효과
```css
/* 기본 섹션 그림자 */
box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;

/* 헤더 그림자 */
box-shadow: 0 2px 10px rgba([BRAND_COLOR_RGB], 0.3) !important;

/* 가격 표시 활성화 시 그림자 */
.price-display.calculated {
    transform: scale(1.01) !important;
    box-shadow: 0 4px 12px rgba([BRAND_COLOR_RGB], 0.15) !important;
}
```

### 그림자 레벨 시스템
| 레벨 | 용도 | 그림자 값 |
|------|------|-----------|
| **Level 1** | 기본 카드 | `0 2px 8px rgba(0,0,0,0.1)` |
| **Level 2** | 중요 섹션 | `0 4px 15px rgba(0,0,0,0.08)` |
| **Level 3** | 메인 섹션 | `0 10px 35px rgba(0,0,0,0.12), 0 4px 15px rgba(0,0,0,0.08)` |
| **Level 4** | 부동 요소 | `0 15px 45px rgba(0,0,0,0.15), 0 8px 25px rgba(0,0,0,0.1)` |

---

## 📱 반응형 디자인 원칙

### 모바일 최적화
- **터치 영역**: 최소 44px 이상 확보
- **가독성**: 폰트 크기 데스크톱 대비 10% 확대
- **패딩**: 터치 친화적 패딩 적용
- **그림자**: 모바일에서 그림자 효과 완화

### 태블릿 최적화
- **그리드**: 2단 레이아웃 유지
- **간격**: 적절한 여백 확보
- **터치**: 데스크톱과 모바일 중간 값 적용

---

## 🔧 구현 가이드라인

### CSS 우선순위
1. `!important` 사용으로 기존 스타일 오버라이드
2. 클래스 선택자 조합으로 특이성 확보
3. 계단식 상속을 고려한 구조화

### 성능 최적화
- **CSS 압축**: 중복 속성 제거
- **애니메이션**: `transform`과 `opacity` 사용
- **그림자**: `will-change` 속성으로 GPU 가속

### 브라우저 호환성
- **모던 브라우저**: Chrome 90+, Firefox 88+, Safari 14+
- **Flexbox/Grid**: IE11 미지원으로 Flexbox 대체 제공
- **그라디언트**: 벤더 프리픽스 적용

---

## 📊 성과 측정

### 목표 지표
- **로딩 속도**: 30% 향상
- **시각적 계층**: 사용자 테스트 90% 만족도
- **모바일 사용성**: 터치 오류율 50% 감소
- **디자인 일관성**: 브랜드 인식도 40% 향상

### 측정 방법
- **Performance**: Lighthouse 점수
- **User Experience**: 사용자 피드백 수집
- **Visual Consistency**: 디자인 시스템 준수율
- **Accessibility**: WCAG 2.1 AA 준수

---

## 🚀 향후 개선 계획

### Phase 2: 고급 기능
- **다크 모드**: 테마 시스템 구축
- **애니메이션**: 마이크로 인터랙션 강화
- **접근성**: 스크린 리더 최적화
- **국제화**: 다국어 레이아웃 지원

### Phase 3: 확장
- **컴포넌트 라이브러리**: 재사용 가능한 UI 컴포넌트
- **디자인 토큰**: CSS 변수 활용 시스템
- **자동화**: 디자인-코드 동기화 도구
- **모니터링**: 실시간 사용성 분석

---

## ✅ 적용 체크리스트

### 기본 컴팩트 디자인
- [ ] Page-title 1/2 높이 축소
- [ ] Calculator-header 헤더 통일
- [ ] Price-display 2/3 높이 축소
- [ ] Form 요소 패딩 1/2 축소
- [ ] 그리드 레이아웃 최적화
- [ ] 반응형 최적화

### 그림자 효과 시스템
- [ ] 기본 섹션 이중 그림자 적용
- [ ] 헤더 브랜드 컬러 그림자
- [ ] 가격 표시 활성화 그림자
- [ ] 테두리 투명도 조정

### 브랜드 일관성
- [ ] 제품별 브랜드 컬러 적용
- [ ] 갤러리-계산기 헤더 통일
- [ ] 그라디언트 방향 일관성
- [ ] 그림자 컬러 매칭

### 반응형 검증
- [ ] 모바일 (< 768px) 테스트
- [ ] 태블릿 (768px - 1024px) 테스트
- [ ] 데스크톱 (> 1024px) 테스트
- [ ] 터치 영역 충분성 확인

---

**작성일**: 2025년 12월  
**작성자**: AI Assistant (Frontend Persona)  
**버전**: v1.0  
**적용 상태**: ✅ 완료