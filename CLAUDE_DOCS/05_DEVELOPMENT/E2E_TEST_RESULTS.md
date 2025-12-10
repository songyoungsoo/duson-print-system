# E2E 테스트 결과 리포트

**테스트 날짜**: 2025-01-31
**환경**: http://localhost (WSL2 Ubuntu)
**브라우저**: Chromium (Headless)
**테스트 파일**: tests/e2e-inserted.spec.js

---

## 📊 테스트 결과 요약

| 항목 | 결과 |
|------|------|
| **전체 테스트** | 10개 |
| **통과** | 6개 (60%) ✅ |
| **실패** | 4개 (40%) ❌ |
| **총 실행 시간** | 1분 48초 |

---

## ✅ 통과한 테스트 (6개)

### 1. 페이지 로딩 및 기본 요소 확인 ✅
- **소요 시간**: 2.7초
- **검증 항목**:
  - [x] 페이지 타이틀 확인
  - [x] 제품 갤러리 표시
  - [x] 견적 계산기 표시
  - [x] 옵션 선택 요소 존재

### 2. 추가 옵션 표시 ✅
- **소요 시간**: 2.2초
- **검증 항목**:
  - [x] 추가 옵션 섹션 확인
  - [x] 코팅 옵션 체크 가능
- **비고**: 전단지에는 추가 옵션 섹션이 없음 (정상)

### 3. 반응형 레이아웃 (모바일) ✅
- **소요 시간**: 2.8초
- **검증 항목**:
  - [x] 모바일 뷰포트 (375x667)
  - [x] 레이아웃 정상 표시
  - [x] 스크린샷 캡처
- **생성 파일**: `tests/screenshots/inserted-mobile.png` (1.1MB)

### 4. 콘솔 에러 확인 ✅
- **소요 시간**: 4.0초
- **검증 항목**:
  - [x] JavaScript 에러 없음
  - [x] Uncaught 에러 없음
  - [x] TypeError/ReferenceError 없음
- **결과**: ✅ 콘솔 에러 없음

### 5. 네트워크 요청 확인 ✅
- **소요 시간**: 3.7초
- **검증 항목**:
  - [x] AJAX 요청 확인
  - [x] 가격 계산 API 호출 확인
- **발견된 AJAX 요청**:
  ```
  http://localhost/mlangprintauto/inserted/calculate_price_ajax.php?
  MY_type=802&PN_type=821&MY_Fsd=626&MY_amount=0.5&ordertype=print&POtype=1&additional_options_total=0
  ```
- **총 네트워크 요청**: 49개

### 6. 페이지 성능 측정 ✅
- **소요 시간**: 2.4초
- **성능 지표**:
  - **페이지 로딩 시간**: 2,190ms (목표: < 3,000ms) ✅
  - **DOM Content Loaded**: 1,429ms
  - **Load Complete**: 2,188ms
- **평가**: 성능 목표 달성 ✅

---

## ❌ 실패한 테스트 (4개)

### 1. 견적 계산 기능 ❌
- **소요 시간**: 12.6초 (재시도: 13.0초)
- **실패 원인**: `TimeoutError: page.selectOption`
- **오류 메시지**:
  ```
  Timeout 10000ms exceeded.
  - did not find some options
  ```
- **문제점**:
  - 용지 종류 옵션값 `100`이 존재하지 않음
  - 실제 select의 option value가 다름
- **생성 파일**:
  - 스크린샷: `test-results/.../test-failed-1.png`
  - 비디오: `test-results/.../video.webm`

**해결 방법**:
```javascript
// 잘못된 코드
await page.selectOption('select#PN_type', '100'); // ❌

// 올바른 코드 (실제 option value 확인 후)
await page.selectOption('select#PN_type', '821'); // ✅ (예시)
```

### 2. 장바구니 담기 기능 ❌
- **소요 시간**: 13.0초 (재시도: 13.3초)
- **실패 원인**: 견적 계산 실패로 인한 연쇄 실패
- **문제점**: 옵션 선택이 안되어 가격 계산이 안됨

### 3. 갤러리 이미지 표시 ❌
- **소요 시간**: 2.2초 (재시도: 2.6초)
- **실패 원인**: 갤러리 이미지 개수가 0개
- **오류 메시지**:
  ```
  갤러리 이미지 개수: 0
  expect(galleryImages).toBeGreaterThan(0)
  ```
- **문제점**:
  - 갤러리 데이터가 없거나
  - 이미지 로딩 실패
  - selector가 잘못됨

**해결 방법**:
```javascript
// selector 확인 필요
const galleryImages = await page.locator('.gallery-image img').count();
// 또는
const galleryImages = await page.locator('.lightbox-viewer img').count();
```

### 4. 전체 플로우: 견적 → 장바구니 → 스크린샷 ❌
- **소요 시간**: 13.3초 (재시도: 13.1초)
- **실패 원인**: 견적 계산 단계에서 실패
- **문제점**: 옵션값 불일치로 인한 실패
- **생성 파일**:
  - 01-page-loaded.png ✅ (421KB)
  - 02-options-selected.png ❌ (생성 안됨)
  - 03-price-calculated.png ❌ (생성 안됨)

---

## 🔍 주요 발견사항

### 1. 성능 분석
✅ **모든 성능 지표가 목표치 내**
- 페이지 로딩: 2.19초 (목표: < 3초)
- DOM 로딩: 1.43초
- JavaScript 실행: 정상
- 네트워크 요청: 49개 (적정 수준)

### 2. 안정성 분석
✅ **JavaScript 에러 없음**
- 콘솔 에러: 0개
- Uncaught 예외: 0개
- 타입 에러: 0개

### 3. 반응형 지원
✅ **모바일 레이아웃 정상**
- 375x667 뷰포트에서 정상 표시
- 레이아웃 깨짐 없음
- 스크린샷 정상 생성

### 4. 기능 문제
❌ **데이터 관련 이슈**
- 갤러리 이미지 데이터 없음
- 옵션값이 테스트 코드와 불일치

---

## 📋 수정 필요 사항

### 우선순위 1: 옵션값 확인 및 수정
```javascript
// 현재 코드 (tests/e2e-inserted.spec.js:35)
await page.selectOption('select#PN_type', '100'); // ❌ 존재하지 않는 값

// 수정 방법
// 1. 브라우저에서 실제 option value 확인
const options = await page.$$eval('select#PN_type option',
  opts => opts.map(o => ({ value: o.value, text: o.textContent }))
);
console.log('Available options:', options);

// 2. 실제 값으로 수정
await page.selectOption('select#PN_type', '실제값');
```

### 우선순위 2: 갤러리 selector 확인
```javascript
// 현재 코드 (tests/e2e-inserted.spec.js:98)
const galleryImages = await page.locator('.gallery-container img').count(); // ❌ 0개

// 수정 방법
// 1. 실제 갤러리 구조 확인
const galleryStructure = await page.evaluate(() => {
  const gallery = document.querySelector('.product-gallery');
  return gallery ? gallery.outerHTML : 'Not found';
});
console.log('Gallery structure:', galleryStructure);

// 2. 올바른 selector 사용
const galleryImages = await page.locator('.product-gallery img').count();
```

### 우선순위 3: 갤러리 데이터 추가
- 전단지 제품에 갤러리 이미지 데이터 추가
- 또는 갤러리 이미지가 없는 제품은 테스트 생략

---

## 📸 생성된 파일

### 스크린샷
```
tests/screenshots/
├── 01-page-loaded.png        (421KB) ✅
├── inserted-mobile.png        (1.1MB) ✅
└── (실패한 테스트의 스크린샷은 test-results/ 에 저장)
```

### 비디오 (실패한 테스트만)
```
test-results/
├── e2e-inserted-전단지-페이지-E2E-테스트-견적-계산-기능-chromium/
│   └── video.webm
├── e2e-inserted-전단지-페이지-E2E-테스트-장바구니-담기-기능-chromium/
│   └── video.webm
└── (기타 실패한 테스트 비디오)
```

---

## 🎯 다음 단계

### 1단계: 테스트 수정
- [ ] 옵션값 확인 및 수정
- [ ] 갤러리 selector 확인 및 수정
- [ ] 테스트 재실행

### 2단계: 다른 제품 테스트
- [ ] 명함 (namecard) E2E 테스트
- [ ] 봉투 (envelope) E2E 테스트
- [ ] 스티커 (sticker_new) E2E 테스트

### 3단계: 통합 시나리오 테스트
- [ ] 회원가입 → 로그인 → 주문
- [ ] 비회원 주문
- [ ] 교정확인 검증

---

## 🛠️ 테스트 실행 방법

### 전체 테스트 실행
```bash
cd /var/www/html
npx playwright test tests/e2e-inserted.spec.js
```

### 특정 테스트만 실행
```bash
npx playwright test tests/e2e-inserted.spec.js --grep "페이지 로딩"
```

### 디버그 모드 (UI 표시)
```bash
npx playwright test tests/e2e-inserted.spec.js --headed --debug
```

### HTML 리포트 확인
```bash
npx playwright show-report
```

---

## 📚 관련 문서

- [E2E_TESTING_GUIDE.md](./E2E_TESTING_GUIDE.md) - E2E 테스팅 가이드
- [FRONTEND_UI.md](./FRONTEND_UI.md) - 프론트엔드 UI 구조
- [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) - 문제 해결 가이드

---

**작성일**: 2025-01-31
**테스트 환경**: WSL2 Ubuntu + Chromium Headless
**Playwright 버전**: Latest (@playwright/test)
