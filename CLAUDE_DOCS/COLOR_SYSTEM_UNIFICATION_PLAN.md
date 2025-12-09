# 두손기획인쇄 컬러 시스템 통일 계획

**작성일**: 2025-10-11
**목표**: 전체 시스템의 컬러 사용을 브랜드 가이드라인에 맞춰 통일하고 유지보수성 향상

---

## 📊 현황 분석 (Current State Analysis)

### 1. 기존 컬러 시스템 현황

#### 발견된 주요 문제점:
- **3개의 독립적인 디자인 토큰 시스템 존재**
  - `design-tokens.css` - 일반 디자인 토큰
  - `brand-design-system.css` - 브랜드 디자인 시스템
  - `mlang-design-system.css` - 제품별 디자인 시스템

- **Hardcoded 컬러 값 대량 사용** (500+ 인스턴스)
  - CSS 파일 전체에 걸쳐 `#4CAF50`, `#667eea`, `rgba()` 등 직접 입력
  - 제품별 CSS에 각기 다른 컬러 값 사용
  - 일관성 없는 hover/active 상태 컬러

- **제품별 브랜드 컬러 불일치**
  ```css
  /* design-tokens.css */
  --color-leaflet: #4caf50;       /* 전단지 - 녹색 */
  --color-namecard: #667eea;      /* 명함 - 보라 */
  --color-envelope: #ff9800;      /* 봉투 - 오렌지 */

  /* 실제 사용 시 다른 값들 */
  .btn-primary { background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); }
  .namecard-btn { background: #667eea; } /* 때로는 #7c4dff */
  .envelope-header { background: #ff9800; } /* 때로는 #ffa726 */
  ```

### 2. 브랜드 컬러 정의 (Brand Color System)

#### 공식 브랜드 컬러 (from brand-design-system.css)
```css
/* 메인 컬러 - Deep Navy */
--brand-primary: #1E4E79;
--brand-primary-dark: #153A5A;
--brand-primary-light: #2D6FA8;
--brand-primary-lighter: #E8F0F7;

/* 포인트 컬러 - Bright Yellow */
--brand-accent: #FFD500;
--brand-accent-dark: #E6C000;
--brand-accent-light: #FFE14D;
--brand-accent-lighter: #FFF9CC;

/* 보조 컬러 - Gray Scale */
--brand-gray-100: #FFFFFF;
--brand-gray-200: #F4F4F4;
--brand-gray-300: #E0E0E0;
--brand-gray-400: #BDBDBD;
--brand-gray-500: #9E9E9E;
--brand-gray-600: #757575;
--brand-gray-700: #616161;
--brand-gray-800: #424242;
--brand-gray-900: #212121;
```

#### 시맨틱 컬러
```css
--brand-success: #4CAF50;   /* 성공/확인 */
--brand-warning: #FF9800;   /* 경고 */
--brand-error: #F44336;     /* 에러 */
--brand-info: #2196F3;      /* 정보 */
```

### 3. 제품별 브랜드 컬러 매핑

| 제품명 | 한글명 | 현재 컬러 (design-tokens.css) | 제안 컬러 | 의미 |
|--------|--------|-------------------------------|-----------|------|
| inserted/leaflet | 전단지 | `#4caf50` (녹색) | `#4CAF50` → `--brand-success` | 성장/신선 |
| namecard | 명함 | `#667eea` (보라) | `#667EEA` → `--product-namecard` | 전문성/신뢰 |
| envelope | 봉투 | `#ff9800` (오렌지) | `#FF9800` → `--brand-warning` | 주목/활력 |
| sticker | 스티커 | `#e91e63` (핑크) | `#E91E63` → `--product-sticker` | 창의/재미 |
| msticker | 자석스티커 | `#9c27b0` (진보라) | `#9C27B0` → `--product-msticker` | 고급/강력 |
| poster/littleprint | 포스터 | `#2196f3` (파랑) | `#2196F3` → `--brand-info` | 명확/안정 |
| cadarok | 카다록 | `#ff5722` (딥오렌지) | `#FF5722` → `--product-cadarok` | 열정/독특 |
| merchandisebond | 상품권 | `#ffa726` (금색) | `#FFD500` → `--brand-accent` | 가치/프리미엄 |
| ncrflambeau | NCR양식 | 미정의 | `#607D8B` → `--product-ncr` | 업무/공식 |

---

## 🎯 통일 계획 (Unification Plan)

### Phase 1: 통합 컬러 토큰 시스템 구축 ⚡ (3-5일)

#### 목표
- 단일 소스 진실 (Single Source of Truth) 생성
- 모든 CSS 변수를 하나의 시스템으로 통합

#### 작업 내용
1. **신규 파일 생성**: `css/color-system-unified.css`
   ```css
   /* ===== 두손기획인쇄 통합 컬러 시스템 ===== */
   /* Single Source of Truth for All Colors */
   /* 생성일: 2025-10-11 */

   :root {
     /* ===== 브랜드 코어 컬러 ===== */
     --dsp-primary: #1E4E79;          /* Deep Navy - 메인 */
     --dsp-primary-dark: #153A5A;
     --dsp-primary-light: #2D6FA8;
     --dsp-primary-lighter: #E8F0F7;

     --dsp-accent: #FFD500;           /* Bright Yellow - 포인트 */
     --dsp-accent-dark: #E6C000;
     --dsp-accent-light: #FFE14D;
     --dsp-accent-lighter: #FFF9CC;

     /* ===== 제품 브랜드 컬러 ===== */
     --dsp-product-leaflet: #4CAF50;       /* 전단지 - 녹색 */
     --dsp-product-leaflet-dark: #2E7D32;
     --dsp-product-leaflet-light: #81C784;

     --dsp-product-namecard: #667EEA;      /* 명함 - 보라 */
     --dsp-product-namecard-dark: #5563D1;
     --dsp-product-namecard-light: #9BA8F5;

     --dsp-product-envelope: #FF9800;      /* 봉투 - 오렌지 */
     --dsp-product-envelope-dark: #F57C00;
     --dsp-product-envelope-light: #FFB74D;

     --dsp-product-sticker: #E91E63;       /* 스티커 - 핑크 */
     --dsp-product-sticker-dark: #C2185B;
     --dsp-product-sticker-light: #F48FB1;

     --dsp-product-msticker: #9C27B0;      /* 자석스티커 - 진보라 */
     --dsp-product-msticker-dark: #7B1FA2;
     --dsp-product-msticker-light: #BA68C8;

     --dsp-product-poster: #2196F3;        /* 포스터 - 파랑 */
     --dsp-product-poster-dark: #1976D2;
     --dsp-product-poster-light: #64B5F6;

     --dsp-product-cadarok: #FF5722;       /* 카다록 - 딥오렌지 */
     --dsp-product-cadarok-dark: #E64A19;
     --dsp-product-cadarok-light: #FF8A65;

     --dsp-product-merchandisebond: #FFA726; /* 상품권 - 금색 */
     --dsp-product-merchandisebond-dark: #F57C00;
     --dsp-product-merchandisebond-light: #FFD54F;

     --dsp-product-ncr: #607D8B;           /* NCR양식 - 블루그레이 */
     --dsp-product-ncr-dark: #455A64;
     --dsp-product-ncr-light: #90A4AE;

     /* ===== 시맨틱 컬러 ===== */
     --dsp-success: #4CAF50;
     --dsp-success-light: #C8E6C9;
     --dsp-success-dark: #2E7D32;

     --dsp-warning: #FF9800;
     --dsp-warning-light: #FFE0B2;
     --dsp-warning-dark: #F57C00;

     --dsp-error: #F44336;
     --dsp-error-light: #FFCDD2;
     --dsp-error-dark: #D32F2F;

     --dsp-info: #2196F3;
     --dsp-info-light: #BBDEFB;
     --dsp-info-dark: #1976D2;

     /* ===== 그레이 스케일 ===== */
     --dsp-gray-50: #FAFAFA;
     --dsp-gray-100: #F5F5F5;
     --dsp-gray-200: #EEEEEE;
     --dsp-gray-300: #E0E0E0;
     --dsp-gray-400: #BDBDBD;
     --dsp-gray-500: #9E9E9E;
     --dsp-gray-600: #757575;
     --dsp-gray-700: #616161;
     --dsp-gray-800: #424242;
     --dsp-gray-900: #212121;
     --dsp-white: #FFFFFF;
     --dsp-black: #000000;

     /* ===== 텍스트 컬러 ===== */
     --dsp-text-primary: #2D3748;
     --dsp-text-secondary: #4A5568;
     --dsp-text-muted: #718096;
     --dsp-text-light: #A0AEC0;
     --dsp-text-white: #FFFFFF;

     /* ===== 배경 컬러 ===== */
     --dsp-bg-primary: #FFFFFF;
     --dsp-bg-secondary: #F8F9FA;
     --dsp-bg-tertiary: #E9ECEF;
     --dsp-bg-dark: #2D3748;

     /* ===== 테두리 컬러 ===== */
     --dsp-border-light: #E2E8F0;
     --dsp-border-medium: #CBD5E0;
     --dsp-border-dark: #A0AEC0;

     /* ===== 투명도 적용 컬러 ===== */
     --dsp-shadow-sm: rgba(0, 0, 0, 0.05);
     --dsp-shadow-md: rgba(0, 0, 0, 0.1);
     --dsp-shadow-lg: rgba(0, 0, 0, 0.15);
     --dsp-shadow-xl: rgba(0, 0, 0, 0.25);

     --dsp-overlay-light: rgba(0, 0, 0, 0.3);
     --dsp-overlay-medium: rgba(0, 0, 0, 0.5);
     --dsp-overlay-dark: rgba(0, 0, 0, 0.7);
   }
   ```

2. **하위 호환성 Alias 생성**
   ```css
   /* 기존 변수명과의 호환성 유지 */
   :root {
     /* design-tokens.css 호환 */
     --color-primary: var(--dsp-primary);
     --color-success: var(--dsp-success);
     --color-leaflet: var(--dsp-product-leaflet);
     --color-namecard: var(--dsp-product-namecard);

     /* brand-design-system.css 호환 */
     --brand-primary: var(--dsp-primary);
     --brand-accent: var(--dsp-accent);
     --brand-success: var(--dsp-success);

     /* mlang-design-system.css 호환 */
     --mlang-primary: var(--dsp-primary);
     --mlang-success: var(--dsp-success);
   }
   ```

3. **CSS 로딩 순서 재구성**
   ```html
   <!-- 모든 제품 페이지에 적용 -->
   <link rel="stylesheet" href="/css/color-system-unified.css">  <!-- 1. 통합 컬러 시스템 -->
   <link rel="stylesheet" href="/css/design-tokens.css">         <!-- 2. 기타 디자인 토큰 (간격, 타이포 등) -->
   <link rel="stylesheet" href="/css/product-layout.css">        <!-- 3. 레이아웃 -->
   <link rel="stylesheet" href="/css/common-styles.css">         <!-- 4. 공통 스타일 (최종) -->
   ```

---

### Phase 2: 제품별 CSS 마이그레이션 🔄 (7-10일)

#### 목표
- Hardcoded 컬러 값을 통합 변수로 교체
- 제품별 브랜드 컬러 일관성 확보

#### 우선순위 제품 (사용 빈도 높은 순서)
1. **전단지 (inserted/leaflet)** - 가장 많이 사용
2. **명함 (namecard)**
3. **봉투 (envelope)**
4. **스티커 (sticker_new)**
5. **포스터 (littleprint)**
6. 나머지 제품 순차 진행

#### 각 제품별 작업 프로세스
```yaml
product_migration_process:
  step_1_analysis:
    - 제품별 CSS 파일 목록 작성
    - 사용 중인 컬러 값 추출 (Grep 활용)
    - 매핑 테이블 생성

  step_2_replacement:
    - Hardcoded 값 → CSS 변수로 교체
    - 예시:
      - `background: #4CAF50` → `background: var(--dsp-product-leaflet)`
      - `color: rgba(76, 175, 80, 0.1)` → `background: var(--dsp-product-leaflet-light)`

  step_3_testing:
    - 로컬 환경에서 각 제품 페이지 시각 검증
    - 브라우저 개발자 도구로 변수 적용 확인
    - hover/active 상태 동작 테스트

  step_4_validation:
    - 전/후 스크린샷 비교
    - 브랜드 가이드라인 준수 검증
```

#### 제품별 컬러 매핑 예시 (전단지)
```css
/* === BEFORE (mlangprintauto/inserted/) === */
.btn-primary {
  background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.25);
}
.btn-primary:hover {
  background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.35);
}

/* === AFTER === */
.btn-primary {
  background: linear-gradient(135deg,
    var(--dsp-product-leaflet) 0%,
    var(--dsp-product-leaflet-dark) 100%);
  box-shadow: 0 4px 12px var(--dsp-shadow-md);
}
.btn-primary:hover {
  background: linear-gradient(135deg,
    var(--dsp-product-leaflet-dark) 0%,
    var(--dsp-success-dark) 100%);
  box-shadow: 0 6px 20px var(--dsp-shadow-lg);
}
```

---

### Phase 3: 공통 컴포넌트 통일 🧩 (5-7일)

#### 목표
- 모든 제품에서 공통으로 사용하는 컴포넌트 스타일 통일

#### 대상 컴포넌트
1. **Primary 버튼** (`btn-primary`)
   - 현재: 제품별로 다른 컬러 사용 (녹색, 보라, 오렌지 등)
   - 통일 방안:
     ```css
     /* 기본 버튼 - 브랜드 포인트 컬러 (Yellow) */
     .btn-primary {
       background: linear-gradient(135deg,
         var(--dsp-accent) 0%,
         var(--dsp-accent-dark) 100%);
       color: var(--dsp-gray-900);
     }

     /* 제품별 버튼 - 제품 브랜드 컬러 */
     .btn-product {
       background: linear-gradient(135deg,
         var(--product-color) 0%,
         var(--product-color-dark) 100%);
       color: var(--dsp-white);
     }

     /* 사용 예시 */
     <button class="btn-product" style="--product-color: var(--dsp-product-leaflet); --product-color-dark: var(--dsp-product-leaflet-dark);">
       장바구니에 담기
     </button>
     ```

2. **가격 표시** (`.price-display`)
   - 현재: 녹색 계열 혼용 (`#28a745`, `#4CAF50`)
   - 통일: `var(--dsp-success)` 사용

3. **갤러리 컴포넌트** (`.gallery-container`)
   - 현재: `--brand-color` 변수 사용 (제품마다 다름)
   - 통일: 동적 CSS 변수 유지하되 기본값 표준화

4. **폼 요소** (`input`, `select`, `textarea`)
   - Focus 상태: `var(--dsp-primary)` (Deep Navy)
   - Error 상태: `var(--dsp-error)`
   - Success 상태: `var(--dsp-success)`

---

### Phase 4: 레거시 CSS 정리 🗑️ (3-5일)

#### 목표
- 사용하지 않는 CSS 파일 및 중복 정의 제거
- 파일 크기 최적화

#### 작업 내용
1. **중복 디자인 토큰 파일 통합**
   ```bash
   # 삭제 대상 (백업 후)
   css/design-tokens.css         → color-system-unified.css로 통합
   css/mlang-design-system.css   → 필요한 부분만 추출 후 삭제

   # 유지 (다른 용도)
   css/brand-design-system.css   → 타이포그래피, 간격, 그림자 등 유지
   ```

2. **Phase2 백업 파일 제거**
   ```bash
   css/*.css.phase2              → 모두 삭제
   css/*.css.backup*             → 필요 시 git history로 복구 가능
   ```

3. **Inline 스타일 제거**
   - 제품별 `*-inline-extracted.css` 파일 검토
   - 공통 스타일로 추출 가능한 부분 이동

---

### Phase 5: 문서화 및 유지보수 가이드 📚 (2-3일)

#### 생성 문서
1. **컬러 시스템 가이드** (`CLAUDE_DOCS/COLOR_SYSTEM_GUIDE.md`)
   - CSS 변수 사용법
   - 제품별 브랜드 컬러 가이드
   - 예제 코드

2. **개발자 온보딩 문서** (`CLAUDE_DOCS/DEVELOPER_COLOR_GUIDE.md`)
   - 새로운 제품 추가 시 컬러 정의 방법
   - 컬러 변수 네이밍 규칙
   - 금지 사항 (hardcoded 컬러 사용 금지)

3. **비주얼 스타일 가이드** (`CLAUDE_DOCS/VISUAL_STYLE_GUIDE.md`)
   - 브랜드 컬러 팔레트 이미지
   - 제품별 컬러 조합 예시
   - 접근성 가이드라인 (WCAG 대비율)

---

## 🛠️ 구현 전략 (Implementation Strategy)

### 리스크 관리
1. **점진적 적용 (Incremental Rollout)**
   - 한 번에 모든 제품 변경 X
   - 제품별 순차 적용 → 각 단계마다 검증
   - 문제 발생 시 즉시 롤백 가능하도록 git 브랜치 활용

2. **하위 호환성 유지**
   - 기존 CSS 변수명 Alias 유지
   - 3개월 deprecated 기간 후 제거

3. **시각 회귀 테스트**
   - 변경 전후 스크린샷 비교
   - 주요 페이지 체크리스트 작성

### 품질 보증
```yaml
quality_checklist:
  visual_consistency:
    - 제품 페이지 브랜드 컬러 일관성
    - 버튼 hover/active 상태 동작
    - 가격 표시 컬러 통일

  code_quality:
    - Hardcoded 컬러 값 0개
    - CSS 변수 100% 활용
    - 중복 정의 제거

  performance:
    - CSS 파일 크기 30% 감소 목표
    - 렌더링 성능 동일 유지

  accessibility:
    - WCAG AA 대비율 준수 (4.5:1 이상)
    - 색맹 사용자 고려 (컬러만으로 정보 전달 X)
```

---

## 📅 타임라인 (Timeline)

### 총 예상 기간: 3-4주

| Phase | 작업 내용 | 예상 기간 | 의존성 |
|-------|----------|----------|--------|
| **Phase 1** | 통합 컬러 시스템 구축 | 3-5일 | - |
| **Phase 2** | 제품별 CSS 마이그레이션 | 7-10일 | Phase 1 완료 |
| **Phase 3** | 공통 컴포넌트 통일 | 5-7일 | Phase 2 병행 가능 |
| **Phase 4** | 레거시 CSS 정리 | 3-5일 | Phase 2-3 완료 |
| **Phase 5** | 문서화 | 2-3일 | 전체 완료 후 |

### 주간 마일스톤
```
Week 1:
  ✓ Phase 1 완료
  → 통합 컬러 시스템 파일 생성 및 테스트

Week 2-3:
  ✓ Phase 2 진행 (제품 1-5)
  ✓ Phase 3 병행 (공통 컴포넌트)

Week 3-4:
  ✓ Phase 2 완료 (나머지 제품)
  ✓ Phase 4 진행 (레거시 정리)

Week 4:
  ✓ Phase 5 완료 (문서화)
  → 전체 시스템 검증 및 배포
```

---

## ✅ 완료 기준 (Definition of Done)

### 기술적 기준
- [ ] `color-system-unified.css` 파일 생성 완료
- [ ] 모든 제품 페이지에서 통합 변수 사용
- [ ] Hardcoded 컬러 값 0개 (검증: `grep -r "#[0-9a-fA-F]\{6\}" css/`)
- [ ] 레거시 파일 제거 (백업 유지)
- [ ] CSS 파일 크기 30% 이상 감소

### 품질 기준
- [ ] 브랜드 가이드라인 100% 준수
- [ ] WCAG AA 접근성 기준 충족
- [ ] 모든 제품 페이지 시각 검증 통과
- [ ] 크로스 브라우저 테스트 (Chrome, Firefox, Safari, Edge)

### 문서화 기준
- [ ] 컬러 시스템 가이드 작성
- [ ] 개발자 온보딩 문서 작성
- [ ] 비주얼 스타일 가이드 작성
- [ ] CLAUDE.md 업데이트

---

## 🚀 즉시 시작 가능한 작업 (Quick Wins)

### 1주차 빠른 성과
1. **통합 컬러 시스템 파일 생성** (1-2일)
   - `css/color-system-unified.css` 작성
   - 모든 변수 정의 완료

2. **전단지 페이지 마이그레이션** (2-3일)
   - 가장 많이 사용되는 제품
   - 성공 시 나머지 제품 템플릿으로 활용

3. **공통 버튼 컴포넌트 통일** (1-2일)
   - `.btn-primary` 스타일 표준화
   - 모든 제품에 즉시 적용 가능

---

## 📞 담당자 및 승인

**기획**: Claude AI (SuperClaude Framework)
**검토 필요**: 두손기획인쇄 개발팀
**최종 승인**: 프로젝트 매니저/디자인 책임자

**다음 단계**: Phase 1 착수 승인 후 `color-system-unified.css` 파일 생성 시작

---

*이 문서는 `/sc:design` 명령으로 생성되었습니다.*
