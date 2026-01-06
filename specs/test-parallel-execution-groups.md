# 테스트 병렬 실행 그룹화 전략

## 📋 의존성 분석 기준

### 🔑 핵심 원칙

**같은 페이지/컴포넌트를 테스트하더라도 서로 다른 기능을 테스트하는 경우 병렬 처리 가능**

```yaml
예시_같은_페이지_병렬_가능:
  전단지_페이지_(/mlangprintauto/inserted/):
    - 테스트_A: 페이지 로딩 검증 (제목, 폼, 버튼)
    - 테스트_B: 가격 계산 기능 (AJAX 호출)
    - 테스트_C: 파일 업로드 기능 (세션 저장)
    - 테스트_D: 옵션 변경 UI (클라이언트 상태)

  병렬_가능_이유:
    - 각 테스트가 서로 다른 기능 검증
    - 상태 공유 없음 (독립 브라우저 컨텍스트)
    - 실행 순서 무관
```

**상태를 공유하거나 순차적으로 실행해야 하는 테스트는 같은 그룹으로 분리**

```yaml
예시_순차_그룹:
  전단지_E2E_플로우:
    단계_1: 옵션 선택 → 가격 확인
    단계_2: 장바구니 담기 → DB INSERT
    단계_3: 장바구니 확인 → 단계2 결과 필요
    단계_4: 주문 제출 → 단계3 세션 필요

  순차_필요_이유:
    - 상태 공유 (세션, 장바구니)
    - 단계별 의존성 (이전 결과 필요)
    - 실행 순서 중요
```

### 병렬 실행 가능 조건 ✅
- ✅ **기능 독립성**: 서로 다른 기능/검증 항목 (같은 페이지여도 OK)
- ✅ **상태 독립성**: 각 테스트가 독립적인 브라우저 컨텍스트/세션 사용
- ✅ **리소스 독립성**: DB 레코드, 파일 경로, 세션이 겹치지 않음
- ✅ **순서 무관**: 실행 순서가 결과에 영향을 주지 않음

### 순차 실행 필요 조건 (같은 그룹 분리) ❌
- ❌ **상태 공유**: 세션, 장바구니, 로그인 상태 등을 공유
- ❌ **단계별 의존성**: 이전 단계 결과가 다음 단계 입력으로 필요
- ❌ **동일 리소스 변경**: 같은 DB 레코드나 파일을 여러 단계에서 수정
- ❌ **순서 의존**: 특정 순서로 실행해야 결과가 올바름

---

## 🎯 병렬 실행 그룹 정의

### 📊 그룹 요약

| 그룹 | 테스트 수 | 최대 병렬도 | 의존성 | 예상 시간 | 우선순위 |
|------|-----------|-------------|---------|-----------|----------|
| **🟢 Tier 1** | 11 | 11 | 없음 | ~30초 | 1 (최우선) |
| **🟢 Tier 2** | 6 | 6 | 없음 | ~40초 | 2 |
| **🟡 Tier 3** | 4 | 4 | 세션만 | ~60초 | 3 |
| **🔴 Tier 4A** | 12 단계 | 1 | 순차 | ~180초 | 4 |
| **🔴 Tier 4B** | 12 단계 | 1 | 순차 | ~180초 | 4 (4A와 병렬) |
| **🔴 Tier 4C** | 12 단계 | 1 | 순차 | ~180초 | 4 (4A,4B와 병렬) |

**총 예상 시간**: ~6분 (순차 실행 시 ~20분 대비 70% 단축)

---

## 🟢 Tier 1: 완전 독립 읽기 전용 테스트 (최대 병렬)

### 특징
- **의존성**: 없음
- **리소스 사용**: DB 읽기만, 파일 읽기만
- **병렬도**: 최대 11개 동시 실행
- **실행 시간**: ~30초

### 테스트 목록 (11개)

| # | 테스트명 | URL | 검증 항목 | 의존성 |
|---|---------|-----|----------|--------|
| 1.1 | 전단지 페이지 로딩 | /inserted/ | 제목, 폼, 가격표 | ❌ 없음 |
| 1.2 | 명함 페이지 로딩 | /namecard/ | 제목, 옵션, 버튼 | ❌ 없음 |
| 1.3 | 봉투 페이지 로딩 | /envelope/ | 폼, 규격 옵션 | ❌ 없음 |
| 1.4 | 스티커 페이지 로딩 | /sticker_new/ | 폼, 옵션 | ❌ 없음 |
| 1.5 | 자석스티커 페이지 로딩 | /msticker/ | 제목, 폼 | ❌ 없음 |
| 1.6 | 카다록 페이지 로딩 | /cadarok/ | 제목, 옵션 | ❌ 없음 |
| 1.7 | 포스터 페이지 로딩 | /littleprint/ | 제목 (레거시) | ❌ 없음 |
| 1.8 | 상품권 페이지 로딩 | /merchandisebond/ | 폼, 옵션 | ❌ 없음 |
| 1.9 | NCR양식 페이지 로딩 | /ncrflambeau/ | 제목, 폼 | ❌ 없음 |
| 1.10 | 리플렛 페이지 로딩 | /leaflet/ | 접지 옵션 | ❌ 없음 |
| 1.11 | 공통 요소 확인 | 3개 제품 샘플 | 로고, 메뉴, 푸터 | ❌ 없음 |

### 병렬 실행 가능 이유
```yaml
이유_분석:
  기능_독립성: 각 테스트가 서로 다른 기능 검증 (페이지 로딩)
  URL_독립성: 각 테스트가 서로 다른 URL 접근
  상태_변경: 없음 (페이지 읽기만)
  DB_작업: SELECT만 수행 (INSERT/UPDATE 없음)
  파일_작업: 이미지 읽기만 (쓰기 없음)
  세션: 필요 없음
  충돌_가능성: 0%

같은_페이지_예시:
  # 전단지 페이지에서 3개 테스트가 모두 병렬 가능
  - Tier_1: 전단지 페이지 로딩 (제목, 폼 검증)
  - Tier_2: 전단지 가격 계산 (AJAX 호출)
  - Tier_3: 전단지 파일 업로드 (세션 저장)

  이유: 같은 URL(/inserted/)이지만 서로 다른 기능 테스트
```

### 실행 예시
```typescript
// 11개 테스트 모두 동시 실행
test.describe.configure({ mode: 'parallel' });

test('1.1 전단지 페이지 로딩', async ({ page }) => {
  await page.goto('/mlangprintauto/inserted/');
  // ...
});

test('1.2 명함 페이지 로딩', async ({ page }) => {
  await page.goto('/mlangprintauto/namecard/');
  // ...
});

// ... 나머지 9개 테스트
```

---

## 🟢 Tier 2: 독립 계산 테스트 (최대 병렬)

### 특징
- **의존성**: 없음
- **리소스 사용**: DB 읽기(가격표 조회), AJAX 호출
- **병렬도**: 최대 6개 동시 실행
- **실행 시간**: ~40초

### 테스트 목록 (6개)

| # | 테스트명 | 제품 | 옵션 | AJAX | 의존성 |
|---|---------|------|------|------|--------|
| 2.1 | 전단지 0.5연 가격 | 전단지 | A4, 0.5연, 컬러 | ✅ | ❌ 없음 |
| 2.2 | 전단지 1.0연 가격 | 전단지 | A4, 1.0연, 컬러 | ✅ | ❌ 없음 |
| 2.3 | 명함 500매 가격 | 명함 | 일반, 아트지, 500매 | ✅ | ❌ 없음 |
| 2.4 | 봉투 1000매 가격 | 봉투 | 소봉투, 1000매 | ✅ | ❌ 없음 |
| 2.5 | 리플렛 접지 가격 | 리플렛 | A4, 0.5연, 2단접지 | ✅ | ❌ 없음 |
| 2.6 | 병렬 계산 테스트 | 3개 제품 | 동시 계산 | ✅ | ❌ 없음 |

### 병렬 실행 가능 이유
```yaml
이유_분석:
  기능_독립성: 각 테스트가 가격 계산 기능만 검증
  AJAX_호출: 각 테스트가 독립적인 calculate_price_ajax.php 호출
  DB_작업: 가격표 SELECT만 (INSERT/UPDATE 없음)
  상태_변경: 없음 (계산 결과만 반환)
  제품_코드: 서로 다른 product_code 사용
  세션: 필요 없음
  충돌_가능성: 0%

같은_페이지_예시:
  # 전단지 페이지에서 2개 테스트가 병렬 가능
  - Tier_2.1: 전단지 0.5연 가격 계산
  - Tier_2.2: 전단지 1.0연 가격 계산

  이유: 같은 URL(/inserted/)이지만 서로 다른 옵션 조합으로 독립 계산
```

### 실행 예시
```typescript
test.describe.configure({ mode: 'parallel' });

test('2.1 전단지 0.5연 가격', async ({ page }) => {
  await page.goto('/mlangprintauto/inserted/');
  await page.selectOption('select[name="PN_type"]', '90g 아트지');
  // ... 가격 검증
});

test('2.3 명함 500매 가격', async ({ page }) => {
  await page.goto('/mlangprintauto/namecard/');
  // ... 독립적 실행
});
```

---

## 🟡 Tier 3: 세션 격리 기능 테스트 (제한적 병렬)

### 특징
- **의존성**: 세션 스토리지만 사용
- **리소스 사용**: 임시 파일 생성, 세션 쓰기
- **병렬도**: 최대 4개 동시 실행 (디스크 I/O 고려)
- **실행 시간**: ~60초

### 테스트 목록 (4개)

| # | 테스트명 | 제품 | 파일 | 세션 격리 | 의존성 |
|---|---------|------|------|-----------|--------|
| 3.1 | 전단지 단일 업로드 | 전단지 | test_flyer.pdf | session_flyer | ❌ 없음 |
| 3.2 | 명함 다중 업로드 | 명함 | 2개 JPG | session_namecard | ❌ 없음 |
| 3.3 | 봉투 대용량 업로드 | 봉투 | 10MB AI | session_envelope | ❌ 없음 |
| 3.4 | 파일 형식 검증 | 전단지 | 다양한 형식 | session_validation | ❌ 없음 |

### 조건부 병렬 가능 이유
```yaml
이유_분석:
  기능_독립성: 각 테스트가 파일 업로드 기능만 검증
  세션_격리: 각 테스트가 독립적인 브라우저 컨텍스트 사용
  파일_경로: 서로 다른 파일명 사용 (test_flyer_001.pdf, test_namecard_002.jpg)
  DB_작업: 없음 (아직 장바구니/주문 전)
  임시_저장: 세션 스토리지만 사용
  제한_이유: 디스크 I/O 부하 (4개로 제한)
  충돌_가능성: 1% (파일명 중복 시)

같은_페이지_예시:
  # 전단지 페이지에서 2개 테스트가 병렬 가능
  - Tier_3.1: 전단지 단일 파일 업로드
  - Tier_3.4: 전단지 파일 형식 검증

  이유: 같은 URL(/inserted/)이지만 서로 다른 파일, 독립 세션
```

### 실행 예시
```typescript
// 4개까지만 병렬 실행
test.describe.configure({ mode: 'parallel', workers: 4 });

test('3.1 전단지 단일 업로드', async ({ browser }) => {
  const context = await browser.newContext(); // 독립 세션
  const page = await context.newPage();
  await page.setInputFiles('input[type="file"]', 'test_flyer_001.pdf');
  // ...
  await context.close();
});

test('3.2 명함 다중 업로드', async ({ browser }) => {
  const context = await browser.newContext(); // 독립 세션
  const page = await context.newPage();
  await page.setInputFiles('input[type="file"]', [
    'test_namecard_001.jpg',
    'test_namecard_002.jpg'
  ]);
  // ...
  await context.close();
});
```

---

## 🔴 Tier 4: E2E 순차 그룹 (상태 공유 플로우)

### 특징
- **의존성**: ❌ 단계별 순차 의존성 (상태 공유)
- **리소스 사용**: DB INSERT/UPDATE, 세션 공유, 파일 저장
- **병렬도**: 제품 간 3개 동시, **각 제품 내부는 순차 그룹**
- **실행 시간**: ~180초 (1개 제품 기준)

### 🔑 순차 그룹 개념

**각 E2E 플로우는 하나의 "순차 그룹"으로, 내부 단계는 상태를 공유하므로 병렬 불가**

```yaml
순차_그룹_예시:
  전단지_E2E_순차_그룹:
    # 이 12단계는 하나의 그룹으로 묶여야 함
    단계_1: 옵션 선택
    단계_2: 장바구니 담기 → 단계1 세션 필요
    단계_3: 장바구니 확인 → 단계2 DB 결과 필요
    단계_4: 주문 제출 → 단계3 세션 필요

  상태_공유:
    - 세션 (장바구니 상태)
    - DB 레코드 (shop_temp.id → mlangorder.temp_id)
    - 파일 업로드 정보

제품_간_병렬_가능:
  Tier_4A: 전단지_순차_그룹 (내부 순차)
  Tier_4B: 명함_순차_그룹 (내부 순차)
  Tier_4C: 봉투_순차_그룹 (내부 순차)

  이유:
    - 각 제품은 독립적인 DB 레코드 생성
    - 서로 다른 product_code 사용
    - 독립적인 세션 사용
    - 파일명에 제품 구분자 포함
```

### Tier 4A: 전단지 E2E (12단계 순차)

| 단계 | 작업 | 의존성 | DB 작업 | 세션 |
|------|------|--------|---------|------|
| 1 | 홈페이지 → 전단지 페이지 | ❌ | - | - |
| 2 | 옵션 선택 (A4, 0.5연, 컬러) | 1 | SELECT | - |
| 3 | 코팅 추가 (양면유광, +160,000원) | 2 | SELECT | - |
| 4 | 파일 업로드 (test_flyer_e2e.pdf) | 2 | - | ✅ 세션 저장 |
| 5 | 장바구니 담기 클릭 | 2,3,4 | INSERT shop_temp | ✅ |
| 6 | shop_temp INSERT 검증 | 5 | SELECT shop_temp | ✅ |
| 7 | 장바구니 페이지 확인 | 5,6 | SELECT shop_temp | ✅ |
| 8 | 주문서 페이지 이동 | 7 | SELECT shop_temp | ✅ |
| 9 | 고객 정보 입력 | 8 | - | ✅ |
| 10 | 주문 제출 | 9 | INSERT mlangorder | ✅ |
| 11 | mlangorder INSERT 검증 | 10 | SELECT mlangorder | ✅ |
| 12 | 주문 완료 페이지 확인 | 10,11 | SELECT mlangorder | ✅ |

**순차 그룹 이유**:
```yaml
상태_공유:
  - 세션: 단계 1-12 전체에서 동일 세션 사용
  - DB_레코드: shop_temp.id → mlangorder.temp_id 연결
  - 장바구니_상태: 단계2 담기 → 단계3 확인 → 단계4 주문

단계별_의존성:
  - 단계_5 ← 단계_4 (파일 업로드 결과 필요)
  - 단계_6 ← 단계_5 (DB INSERT ID 필요)
  - 단계_7 ← 단계_6 (shop_temp 레코드 필요)
  - 단계_10 ← 단계_9 (고객 정보 필요)
  - 단계_12 ← 단계_11 (mlangorder.id 필요)

결론: 이 12단계는 하나의 순차 그룹으로 묶어서 실행
```

### Tier 4B: 명함 E2E (12단계 순차)

| 단계 | 작업 | 차이점 | 의존성 |
|------|------|--------|--------|
| 1-12 | 전단지와 동일 플로우 | 제품: 명함 | 순차 의존 |
| 4 | 파일 업로드 | **2개 파일** (앞/뒤) | 2 |
| 특이사항 | product_code = 'namecard' | DB 레코드 독립 | - |

**Tier 4A와 병렬 가능**:
```yaml
이유:
  - 서로 다른 제품 코드 (inserted vs namecard)
  - 독립 DB 레코드 (shop_temp.product_code로 구분)
  - 독립 세션 (각 브라우저 컨텍스트)
  - 독립 파일 (test_flyer_e2e.pdf vs test_namecard_e2e.jpg)

순차_그룹:
  - 명함 E2E도 12단계가 하나의 순차 그룹
  - 내부적으로 상태 공유 및 단계별 의존성
```

### Tier 4C: 봉투 E2E (12단계 순차)

| 단계 | 작업 | 차이점 | 의존성 |
|------|------|--------|--------|
| 1-12 | 전단지와 동일 플로우 | 제품: 봉투 | 순차 의존 |
| 3 | 테이프 추가 | **테이프 200개** 옵션 | 2 |
| 특이사항 | product_code = 'envelope' | DB 레코드 독립 | - |

**Tier 4A, 4B와 병렬 가능**:
```yaml
이유:
  - 서로 다른 제품 코드 (inserted, namecard, envelope)
  - 독립 DB 레코드 (각 product_code로 구분)
  - 독립 세션 (각 브라우저 컨텍스트)
  - 독립 파일 (각 제품별 파일명)

3개_제품_동시_실행:
  - Tier_4A: 전단지 순차 그룹 (내부 12단계 순차)
  - Tier_4B: 명함 순차 그룹 (내부 12단계 순차)
  - Tier_4C: 봉투 순차 그룹 (내부 12단계 순차)
  → 3개 제품은 병렬 가능, 각 제품 내부는 순차
```

### 실행 예시
```typescript
// 3개 제품 E2E를 동시에 실행 (각각은 내부적으로 순차 그룹)
test.describe('E2E 병렬 실행', () => {
  test.describe.configure({ mode: 'parallel', workers: 3 });

  // Tier 4A: 전단지 순차 그룹 (12단계가 하나의 그룹)
  test('전단지 E2E - 순차 그룹', async ({ page }) => {
    // 🔴 이 12단계는 상태를 공유하므로 순차 실행
    await page.goto('/mlangprintauto/inserted/');        // Step 1
    await page.selectOption('select[name="MY_amount"]', '0.5연'); // Step 2
    await page.click('input[name="coating"]');           // Step 3
    await page.setInputFiles('input[type="file"]', 'test.pdf'); // Step 4
    await page.click('button:has-text("장바구니 담기")'); // Step 5
    // ... Step 6-12도 순차로 await
  });

  // Tier 4B: 명함 순차 그룹 (4A와 병렬 가능, 내부는 순차)
  test('명함 E2E - 순차 그룹', async ({ page }) => {
    // 🔴 명함도 12단계가 순차 그룹
    await page.goto('/mlangprintauto/namecard/');
    // ... 12단계 순차 실행
  });

  // Tier 4C: 봉투 순차 그룹 (4A, 4B와 병렬 가능, 내부는 순차)
  test('봉투 E2E - 순차 그룹', async ({ page }) => {
    // 🔴 봉투도 12단계가 순차 그룹
    await page.goto('/mlangprintauto/envelope/');
    // ... 12단계 순차 실행
  });
});

/* 실행 결과:
   - 3개 테스트가 동시에 시작 (병렬)
   - 각 테스트 내부는 Step 1→2→3→...→12 순차 실행
   - 총 시간: ~180초 (가장 긴 테스트 기준)
*/
```

---

## 📊 의존성 매트릭스

### 리소스 사용 분석

| Tier | 테스트 | DB 읽기 | DB 쓰기 | 파일 읽기 | 파일 쓰기 | 세션 쓰기 | 병렬 가능 |
|------|--------|:-------:|:-------:|:---------:|:---------:|:---------:|:--------:|
| 🟢 Tier 1 | 페이지 로딩 (11) | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ 최대 11 |
| 🟢 Tier 2 | 가격 계산 (6) | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ 최대 6 |
| 🟡 Tier 3 | 파일 업로드 (4) | ❌ | ❌ | ❌ | ✅ | ✅ | ⚠️ 최대 4 |
| 🔴 Tier 4A | 전단지 E2E (12) | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ 내부 순차 |
| 🔴 Tier 4B | 명함 E2E (12) | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ 내부 순차 |
| 🔴 Tier 4C | 봉투 E2E (12) | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ 내부 순차 |

### 충돌 가능성 분석

| 테스트 조합 | 충돌 여부 | 이유 |
|------------|:--------:|------|
| Tier 1 ∥ Tier 1 | ✅ 안전 | 서로 다른 URL, 읽기만 |
| Tier 1 ∥ Tier 2 | ✅ 안전 | 읽기 vs 계산 (쓰기 없음) |
| Tier 2 ∥ Tier 2 | ✅ 안전 | 서로 다른 제품 코드 |
| Tier 3 ∥ Tier 3 | ⚠️ 제한 | 파일 I/O 부하 (4개 제한) |
| Tier 4A ∥ Tier 4B | ✅ 안전 | 독립 DB 레코드 |
| Tier 4A ∥ Tier 4C | ✅ 안전 | 독립 DB 레코드 |
| Tier 4B ∥ Tier 4C | ✅ 안전 | 독립 DB 레코드 |
| Tier 4 내부 | ❌ 충돌 | 단계별 순차 의존성 |

---

## 🚀 실행 전략

### 전략 1: 최대 속도 (권장)

```bash
# 1단계: Tier 1 + Tier 2 병렬 (17개 동시)
npx playwright test --grep="(Tier 1|Tier 2)" --workers=11
# 예상 시간: ~40초

# 2단계: Tier 3 (4개 동시)
npx playwright test --grep="Tier 3" --workers=4
# 예상 시간: ~60초

# 3단계: Tier 4A, 4B, 4C 병렬 (3개 동시)
npx playwright test --grep="Tier 4" --workers=3
# 예상 시간: ~180초

# 총 시간: ~280초 (약 4.5분)
```

### 전략 2: 단계별 실행 (안정성 우선)

```bash
# Phase 1: 읽기 전용
npx playwright test --grep="Tier 1" --workers=11  # 30초

# Phase 2: 계산 테스트
npx playwright test --grep="Tier 2" --workers=6   # 40초

# Phase 3: 기능 테스트
npx playwright test --grep="Tier 3" --workers=4   # 60초

# Phase 4: E2E (순차)
npx playwright test --grep="Tier 4A"              # 180초
npx playwright test --grep="Tier 4B"              # 180초
npx playwright test --grep="Tier 4C"              # 180초

# 총 시간: ~670초 (약 11분)
```

### 전략 3: 하이브리드 (균형)

```bash
# 1단계: 경량 테스트 병렬
npx playwright test --grep="(Tier 1|Tier 2|Tier 3)" --workers=10
# 예상 시간: ~90초

# 2단계: E2E 병렬
npx playwright test --grep="Tier 4" --workers=3
# 예상 시간: ~180초

# 총 시간: ~270초 (약 4.5분)
```

---

## 📋 Playwright 설정 예시

### playwright.config.ts

```typescript
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',

  // 기본 병렬 설정
  fullyParallel: true,
  workers: process.env.CI ? 5 : 10,

  // 재시도
  retries: process.env.CI ? 2 : 0,

  // 타임아웃
  timeout: 60 * 1000, // 60초
  expect: { timeout: 5 * 1000 },

  use: {
    baseURL: 'http://dsp1830.shop',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    // 🟢 Tier 1: 최대 병렬
    {
      name: 'tier-1-readonly',
      testMatch: /.*tier-1.*\.spec\.ts/,
      fullyParallel: true,
      workers: 11,
    },

    // 🟢 Tier 2: 최대 병렬
    {
      name: 'tier-2-calculation',
      testMatch: /.*tier-2.*\.spec\.ts/,
      fullyParallel: true,
      workers: 6,
    },

    // 🟡 Tier 3: 제한 병렬
    {
      name: 'tier-3-upload',
      testMatch: /.*tier-3.*\.spec\.ts/,
      fullyParallel: true,
      workers: 4,
    },

    // 🔴 Tier 4: E2E (제품별 병렬, 내부 순차)
    {
      name: 'tier-4-e2e',
      testMatch: /.*tier-4.*\.spec\.ts/,
      fullyParallel: true, // 제품 간 병렬
      workers: 3,
    },
  ],
});
```

---

## ✅ 핵심 규칙 요약

### 1️⃣ 같은 페이지, 다른 기능 → 병렬 가능 ⭐
```yaml
조건: 기능 독립성, 상태 공유 없음
예시:
  전단지_페이지_(/inserted/):
    ✅ Tier_1: 페이지_로딩 (제목, 폼 검증)
    ✅ Tier_2: 가격_계산 (AJAX 호출)
    ✅ Tier_3: 파일_업로드 (세션 저장)
  → 같은 URL이지만 3개 테스트 모두 병렬 가능

실행: 독립 브라우저 컨텍스트로 동시 접근
```

### 2️⃣ 읽기 전용 → 최대 병렬
```yaml
조건: DB/파일 읽기만, 상태 변경 없음
예시: Tier 1 (11개), Tier 2 (6개)
실행: workers=11 또는 workers=6
```

### 3️⃣ 독립 세션 → 제한 병렬
```yaml
조건: 세션 스토리지만 사용, DB 쓰기 없음
예시: Tier 3 (4개)
실행: workers=4 (I/O 부하 고려)
```

### 4️⃣ 제품별 독립 → 제품 간 병렬
```yaml
조건: 서로 다른 product_code, 독립 DB 레코드
예시: Tier 4A (전단지), 4B (명함), 4C (봉투)
실행: workers=3 (3개 제품 동시)
```

### 5️⃣ 상태 공유 → 순차 그룹으로 분리 ⭐
```yaml
조건: 세션 공유, 단계별 의존성
예시:
  전단지_E2E_순차_그룹:
    Step 1: 옵션 선택
    Step 2: 장바구니 담기 → Step 1 세션 필요
    Step 3: 장바구니 확인 → Step 2 DB 결과 필요
  → 이 12단계는 하나의 순차 그룹으로 묶어야 함

실행: 각 테스트 내부는 await로 순차
```

### 6️⃣ 순차 그룹 간에는 병렬 가능
```yaml
조건: 각 그룹이 독립 리소스 사용
예시:
  ✅ 전단지_E2E_그룹 ∥ 명함_E2E_그룹 ∥ 봉투_E2E_그룹
  → 3개 그룹 동시 시작 (각 그룹 내부는 순차)

실행: workers=3 (그룹 간 병렬)
```

### 7️⃣ 파일명 격리 → 충돌 방지
```yaml
규칙: 파일명에 제품/테스트 구분자 포함
예시:
  - test_flyer_e2e.pdf
  - test_namecard_001.jpg
  - test_envelope_tier3.ai
```

### 8️⃣ 브라우저 컨텍스트 → 세션 격리
```typescript
// ✅ 올바른 방법: 독립 컨텍스트
test('테스트', async ({ browser }) => {
  const context = await browser.newContext();
  const page = await context.newPage();
  // ...
  await context.close();
});

// ❌ 잘못된 방법: 공유 페이지
test('테스트', async ({ page }) => {
  // 세션 충돌 가능
});
```

---

## 📊 성능 비교

### 순차 실행 (Worst Case)
```
Tier 1: 11 × 30초 = 330초
Tier 2: 6 × 40초 = 240초
Tier 3: 4 × 60초 = 240초
Tier 4: 3 × 180초 = 540초
---------------------------------
총: 1,350초 (약 22.5분)
```

### 병렬 실행 (Best Case - 전략 1)
```
Tier 1+2 병렬: 40초 (가장 긴 것 기준)
Tier 3: 60초
Tier 4 병렬: 180초 (3개 제품 동시)
---------------------------------
총: 280초 (약 4.5분)
```

**개선율**: 순차 대비 **80% 시간 단축** ⚡

---

## 🎯 체크리스트

병렬 실행 전 확인사항:

### 환경 설정
- [ ] Playwright 설치 및 설정 완료
- [ ] baseURL = http://dsp1830.shop 설정
- [ ] workers 수 적절히 조정 (로컬: 10, CI: 5)

### 테스트 격리
- [ ] 각 테스트가 독립 브라우저 컨텍스트 사용
- [ ] E2E 테스트는 고유 파일명 사용 (타임스탬프/UUID)
- [ ] 테스트 간 세션 공유 없음

### 데이터베이스
- [ ] E2E 테스트는 독립 DB 레코드 생성
- [ ] product_code로 레코드 구분 가능
- [ ] 테스트 후 클린업 필요 시 계획 수립

### 파일 시스템
- [ ] 업로드 파일명에 테스트 ID 포함
- [ ] 임시 디렉토리 사용 (필요 시)
- [ ] 테스트 완료 후 파일 클린업

### 실행 전략
- [ ] 전략 선택 (최대 속도 / 단계별 / 하이브리드)
- [ ] CI/CD 환경 설정 (GitHub Actions 등)
- [ ] 재시도 전략 설정 (retries: 2)

---

*Last Updated: 2025-01-03*
*Based on: dsp1830-test-plan.md*
*Total Tests: 33개 (Tier 1: 11, Tier 2: 6, Tier 3: 4, Tier 4: 12×3)*
*Expected Time: ~4.5분 (병렬), ~22.5분 (순차)*
