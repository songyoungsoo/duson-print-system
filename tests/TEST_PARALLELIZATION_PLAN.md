# dsp1830.shop 테스트 병렬화 계획

## 병렬화 원칙

### 병렬 실행 가능 조건
- ✅ 상태를 공유하지 않는 테스트
- ✅ 서로 다른 리소스/데이터를 사용하는 테스트
- ✅ 읽기 전용 작업 (DB/파일 변경 없음)
- ✅ 독립적인 페이지/컴포넌트 테스트

### 순차 실행 필요 조건
- ❌ 이전 단계의 결과에 의존하는 테스트
- ❌ 동일한 DB 레코드를 변경하는 테스트
- ❌ 공유 세션/쿠키를 사용하는 테스트
- ❌ 파일 시스템의 동일 경로를 사용하는 테스트

---

## 테스트 그룹 분류

### 🟢 Group A: 독립 읽기 전용 테스트 (병렬도: 최대)
**특징:** DB/파일 변경 없음, 완전 독립적, 동시 실행 안전

#### A-1: 제품 페이지 로딩 테스트 (11개 병렬)
```yaml
병렬_실행: true
최대_동시_실행: 11
의존성: 없음
상태_변경: 없음

테스트_케이스:
  - 전단지_페이지_로딩:
      URL: /mlangprintauto/inserted/
      검증: 제목, 가격표, 옵션 폼 표시

  - 명함_페이지_로딩:
      URL: /mlangprintauto/namecard/
      검증: 제목, 가격표, 옵션 폼 표시

  - 봉투_페이지_로딩:
      URL: /mlangprintauto/envelope/
      검증: 제목, 가격표, 옵션 폼 표시

  - 스티커_페이지_로딩:
      URL: /mlangprintauto/sticker_new/
      검증: 제목, 가격표, 옵션 폼 표시

  - 자석스티커_페이지_로딩:
      URL: /mlangprintauto/msticker/
      검증: 제목, 가격표, 옵션 폼 표시

  - 카다록_페이지_로딩:
      URL: /mlangprintauto/cadarok/
      검증: 제목, 가격표, 옵션 폼 표시

  - 포스터_페이지_로딩:
      URL: /mlangprintauto/littleprint/
      검증: 제목, 가격표, 옵션 폼 표시

  - 상품권_페이지_로딩:
      URL: /mlangprintauto/merchandisebond/
      검증: 제목, 가격표, 옵션 폼 표시

  - 양식지_페이지_로딩:
      URL: /mlangprintauto/ncrflambeau/
      검증: 제목, 가격표, 옵션 폼 표시

  - 리플렛_페이지_로딩:
      URL: /mlangprintauto/leaflet/
      검증: 제목, 가격표, 옵션 폼 표시
```

#### A-2: 갤러리 표시 테스트 (11개 병렬)
```yaml
병렬_실행: true
최대_동시_실행: 11
의존성: 없음
상태_변경: 없음

테스트_케이스:
  - 전단지_갤러리_표시:
      페이지: /mlangprintauto/inserted/
      검증: 4개 썸네일, "샘플 더보기" 버튼, 라이트박스

  - 명함_갤러리_표시:
      페이지: /mlangprintauto/namecard/
      검증: 4개 썸네일, "샘플 더보기" 버튼, 라이트박스

  # ... 나머지 9개 제품 동일 패턴
```

#### A-3: 옵션 목록 표시 테스트 (11개 병렬)
```yaml
병렬_실행: true
최대_동시_실행: 11
의존성: 없음
상태_변경: 없음

테스트_케이스:
  - 전단지_옵션_목록:
      페이지: /mlangprintauto/inserted/
      검증: 용지, 규격, 수량, 인쇄색상 드롭다운

  - 명함_옵션_목록:
      페이지: /mlangprintauto/namecard/
      검증: 재질, 수량, 규격 드롭다운

  # ... 나머지 9개 제품 동일 패턴
```

---

### 🟡 Group B: 제품별 가격 계산 테스트 (제품별 병렬)
**특징:** 상태 변경 없음, 제품별 독립적, 동시 실행 안전

#### B-1: 기본 가격 계산 (11개 병렬)
```yaml
병렬_실행: true
최대_동시_실행: 11
의존성: 없음 (각 제품별 독립적)
상태_변경: 없음

테스트_케이스:
  - 전단지_기본_가격_계산:
      페이지: /mlangprintauto/inserted/
      입력: 용지=90g아트지, 규격=A4, 수량=0.5연, 색상=컬러
      검증: 총액, VAT 포함가, 0.5연 (2,000매) 표시

  - 명함_기본_가격_계산:
      페이지: /mlangprintauto/namecard/
      입력: 재질=아트지, 수량=500매, 규격=일반명함
      검증: 총액, VAT 포함가

  # ... 나머지 9개 제품
```

#### B-2: 추가 옵션 가격 계산 (11개 병렬)
```yaml
병렬_실행: true
최대_동시_실행: 11
의존성: 없음
상태_변경: 없음

테스트_케이스:
  - 전단지_코팅_가격:
      페이지: /mlangprintauto/inserted/
      입력: 기본옵션 + 양면유광코팅
      검증: 코팅비 +160,000원, 총액 반영

  - 전단지_오시_가격:
      페이지: /mlangprintauto/inserted/
      입력: 기본옵션 + 1줄 오시
      검증: 오시비 +32,000원, 총액 반영

  - 리플렛_접지_가격:
      페이지: /mlangprintauto/leaflet/
      입력: 기본옵션 + 2단접지
      검증: 접지비 +40,000원, 총액 반영

  - 봉투_테이프_가격:
      페이지: /mlangprintauto/envelope/
      입력: 기본옵션 + 테이프 200개
      검증: 테이프비 계산, 총액 반영

  # ... 나머지 제품
```

---

### 🟠 Group C: 단일 기능 테스트 (제품별 병렬)
**특징:** 일부 상태 변경, 제품별 독립 세션, 격리된 실행

#### C-1: 파일 업로드 테스트 (제품별 병렬, 세션 격리)
```yaml
병렬_실행: true
최대_동시_실행: 5 (리소스 고려)
의존성: 독립 세션 필요
상태_변경: 세션 스토리지만 (DB 변경 없음)

테스트_케이스:
  - 전단지_단일_파일_업로드:
      페이지: /mlangprintauto/inserted/
      세션: session_flyer_upload_1
      파일: test_flyer.pdf (1MB)
      검증: 업로드 성공, 파일명 표시, 미리보기

  - 명함_다중_파일_업로드:
      페이지: /mlangprintauto/namecard/
      세션: session_namecard_upload_1
      파일: [test_namecard1.jpg, test_namecard2.jpg]
      검증: 2개 파일 업로드, 목록 표시

  - 봉투_대용량_파일_업로드:
      페이지: /mlangprintauto/envelope/
      세션: session_envelope_upload_1
      파일: test_envelope.ai (10MB)
      검증: 15MB 제한 내 업로드 성공

  # ... 나머지 제품 (각각 독립 세션)
```

#### C-2: 옵션 변경 인터랙션 (11개 병렬)
```yaml
병렬_실행: true
최대_동시_실행: 11
의존성: 없음
상태_변경: 없음 (클라이언트 상태만)

테스트_케이스:
  - 전단지_수량_변경_시_매수_업데이트:
      페이지: /mlangprintauto/inserted/
      동작: 0.5연 → 1.0연 변경
      검증: 2,000매 → 4,000매 자동 업데이트

  - 명함_재질_변경_시_가격_변경:
      페이지: /mlangprintauto/namecard/
      동작: 아트지 → 스노우지 변경
      검증: 가격 자동 재계산

  # ... 나머지 제품
```

---

### 🔴 Group D: E2E 플로우 테스트 (제품별 순차, 제품 간 병렬)
**특징:** 순차적 의존성, DB 쓰기, 제품별 독립 격리

#### D-1: 전단지 E2E (순차 실행)
```yaml
병렬_실행: false (시나리오 내부는 순차)
다른_제품과_병렬: true (다른 E2E와는 병렬 가능)
의존성: 단계별 순차 의존
상태_변경: DB (shop_temp, mlangorder_printauto)

테스트_단계:
  1. 전단지_제품_선택:
      URL: /mlangprintauto/inserted/
      동작: 용지, 규격, 수량 선택
      검증: 가격 표시

  2. 전단지_옵션_추가:
      동작: 양면유광코팅 선택
      검증: 총액 업데이트

  3. 전단지_파일_업로드:
      파일: test_flyer_e2e.pdf
      검증: 업로드 완료 메시지

  4. 전단지_장바구니_추가:
      동작: "장바구니 담기" 클릭
      검증: 성공 알림, shop_temp 테이블 INSERT

  5. 전단지_장바구니_확인:
      URL: /mlangprintauto/shop/cart.php
      검증: 제품명, 가격, 옵션, 파일명 표시

  6. 전단지_주문서_작성:
      URL: /mlangorder_printauto/OnlineOrder_unified.php
      입력: 이름, 이메일, 주소, 전화번호
      검증: 입력 필드 표시, 주문 요약

  7. 전단지_주문_제출:
      동작: "주문 완료하기" 클릭
      검증: mlangorder_printauto 테이블 INSERT

  8. 전단지_주문_완료_확인:
      URL: /mlangorder_printauto/OrderComplete_universal.php
      검증: 주문번호, 상세정보, "0.5연 (2,000매)" 표시
```

#### D-2: 명함 E2E (순차 실행, D-1과 병렬)
```yaml
병렬_실행: false (시나리오 내부는 순차)
다른_제품과_병렬: true
의존성: 단계별 순차 의존
상태_변경: DB (독립 레코드)

테스트_단계:
  1-8. [전단지와 동일한 플로우, 명함 제품 기준]
```

#### D-3 ~ D-11: 나머지 9개 제품 E2E
```yaml
병렬_실행: 각 제품 내부는 순차, 제품 간은 병렬
최대_동시_E2E: 5개 (리소스 고려)
```

---

### 🔵 Group E: 관리자 기능 테스트 (순차 실행)
**특징:** 순차적 의존성, 인증 세션 공유

#### E-1: 관리자 로그인 및 주문 조회 (순차)
```yaml
병렬_실행: false
의존성: 로그인 → 주문 조회 → 상세 확인
상태_변경: 세션 생성

테스트_단계:
  1. 관리자_로그인:
      URL: /admin/mlangprintauto/
      입력: duson1830 / du1830
      검증: 로그인 성공, 세션 생성

  2. 주문_목록_조회:
      URL: /admin/mlangprintauto/admin.php
      검증: 최근 주문 목록 표시

  3. 주문_상세_확인:
      동작: 첫 번째 주문 클릭
      검증: 주문 상세 정보, 파일 목록

  4. 파일_다운로드:
      동작: 파일 다운로드 링크 클릭
      검증: HTTP 200, Content-Type 올바름
```

---

## 병렬 실행 전략

### 전략 1: 최대 병렬화 (빠른 피드백)
```yaml
동시_실행_그룹:
  - Group_A (A-1, A-2, A-3 동시): 33개 테스트 병렬
  - Group_B (B-1, B-2 동시): 22개 테스트 병렬
  - Group_C (C-1, C-2 동시): 22개 테스트 병렬
  - Group_D (5개 제품 E2E 병렬): 5개 플로우 동시
  - Group_E (순차): 1개 플로우

총_병렬도: 82개 테스트 동시 실행
예상_시간: ~5분 (리소스 충분 시)
```

### 전략 2: 제한적 병렬화 (안정성 우선)
```yaml
동시_실행_그룹:
  - Phase_1: Group_A (읽기 전용) - 11개 병렬
  - Phase_2: Group_B (계산 테스트) - 11개 병렬
  - Phase_3: Group_C (단일 기능) - 5개 병렬
  - Phase_4: Group_D (E2E) - 3개 병렬
  - Phase_5: Group_E (관리자) - 순차

총_병렬도: 최대 11개
예상_시간: ~15분
```

### 전략 3: 하이브리드 (권장)
```yaml
동시_실행_그룹:
  - 읽기_전용 (Group_A + Group_B): 22개 병렬
  - 기능_테스트 (Group_C): 5개 병렬
  - E2E_플로우 (Group_D): 3개 병렬
  - 관리자 (Group_E): 순차

총_병렬도: 최대 22개
예상_시간: ~8분
리소스_균형: ✅ 적정
```

---

## 의존성 매트릭스

| 테스트 그룹 | DB 읽기 | DB 쓰기 | 파일 읽기 | 파일 쓰기 | 세션 | 병렬 가능 |
|------------|---------|---------|-----------|-----------|------|----------|
| Group A-1  | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ 최대 |
| Group A-2  | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ 최대 |
| Group A-3  | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ 최대 |
| Group B-1  | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ 최대 |
| Group B-2  | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ 최대 |
| Group C-1  | ❌ | ❌ | ❌ | ✅ | ✅ | ⚠️ 제한 |
| Group C-2  | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ 최대 |
| Group D    | ✅ | ✅ | ✅ | ✅ | ✅ | ⚠️ 제품별 |
| Group E    | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ 순차 |

---

## Playwright 설정 예시

### playwright.config.ts
```typescript
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',

  // 병렬 실행 설정
  fullyParallel: true,
  workers: process.env.CI ? 5 : 10, // 로컬: 10, CI: 5

  // 재시도 설정
  retries: process.env.CI ? 2 : 0,

  // 타임아웃
  timeout: 30 * 1000, // 30초
  expect: {
    timeout: 5 * 1000, // 5초
  },

  use: {
    baseURL: 'http://dsp1830.shop',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    // 읽기 전용 테스트 (최대 병렬)
    {
      name: 'group-a-readonly',
      testMatch: /.*\.group-a\.spec\.ts/,
      fullyParallel: true,
      workers: 11,
    },

    // 가격 계산 테스트 (최대 병렬)
    {
      name: 'group-b-calculation',
      testMatch: /.*\.group-b\.spec\.ts/,
      fullyParallel: true,
      workers: 11,
    },

    // 단일 기능 테스트 (제한 병렬)
    {
      name: 'group-c-features',
      testMatch: /.*\.group-c\.spec\.ts/,
      fullyParallel: true,
      workers: 5,
    },

    // E2E 플로우 (제한 병렬)
    {
      name: 'group-d-e2e',
      testMatch: /.*\.group-d\.spec\.ts/,
      fullyParallel: true,
      workers: 3,
    },

    // 관리자 기능 (순차)
    {
      name: 'group-e-admin',
      testMatch: /.*\.group-e\.spec\.ts/,
      fullyParallel: false,
      workers: 1,
    },
  ],
});
```

---

## 파일 구조

```
tests/
├── group-a-readonly/
│   ├── page-loading.group-a.spec.ts      # 11개 페이지 로딩 (병렬)
│   ├── gallery-display.group-a.spec.ts   # 11개 갤러리 (병렬)
│   └── option-lists.group-a.spec.ts      # 11개 옵션 (병렬)
│
├── group-b-calculation/
│   ├── basic-price.group-b.spec.ts       # 11개 기본 가격 (병렬)
│   └── option-price.group-b.spec.ts      # 추가 옵션 가격 (병렬)
│
├── group-c-features/
│   ├── file-upload.group-c.spec.ts       # 파일 업로드 (제한 병렬)
│   └── option-interaction.group-c.spec.ts # 옵션 변경 (병렬)
│
├── group-d-e2e/
│   ├── flyer-e2e.group-d.spec.ts         # 전단지 E2E (순차)
│   ├── namecard-e2e.group-d.spec.ts      # 명함 E2E (순차)
│   ├── envelope-e2e.group-d.spec.ts      # 봉투 E2E (순차)
│   └── ...                                # 나머지 8개 제품
│
└── group-e-admin/
    └── admin-workflow.group-e.spec.ts    # 관리자 플로우 (순차)
```

---

## 실행 예시

### 전체 병렬 실행
```bash
npx playwright test --workers=10
```

### 그룹별 실행
```bash
# 읽기 전용만
npx playwright test --grep="group-a" --workers=11

# E2E만 (3개 동시)
npx playwright test --grep="group-d" --workers=3

# 순차 실행 (디버깅)
npx playwright test --workers=1
```

### CI/CD 최적화
```bash
# GitHub Actions에서 5개 워커로 실행
CI=true npx playwright test --workers=5
```

---

## 주의사항

### ⚠️ 세션 격리
- 각 E2E 테스트는 독립적인 브라우저 컨텍스트 사용
- 쿠키/로컬스토리지 충돌 방지

### ⚠️ DB 격리
- 각 제품 E2E는 독립적인 주문 레코드 생성
- 테스트 데이터는 고유 식별자 사용 (타임스탬프, UUID)

### ⚠️ 파일 격리
- 업로드 파일명에 테스트 ID 포함 (test_flyer_12345.pdf)
- 테스트 완료 후 클린업 필요

### ⚠️ 리소스 제한
- 로컬 환경: 최대 10-11개 워커
- CI 환경: 최대 5개 워커 (메모리/CPU 고려)
- E2E 테스트: 최대 3-5개 동시 실행

---

## 성공 기준

✅ **병렬화 효율성**
- 전체 실행 시간 < 10분 (순차 대비 70% 감소)
- 읽기 전용 테스트 < 2분
- E2E 테스트 < 5분

✅ **안정성**
- Flaky 테스트 < 5%
- 병렬 실행 시 충돌 없음
- 재시도 성공률 > 95%

✅ **커버리지**
- 11개 제품 모두 테스트
- 주요 E2E 플로우 3개 이상
- 핵심 기능 100% 커버
