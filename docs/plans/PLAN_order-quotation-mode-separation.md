# Phase 1: 주문/견적 모드 명확화 구현 계획

**생성일**: 2025-12-26
**예상 소요**: 1-2일 (8-16시간)
**담당**: Development Team
**우선순위**: 🔴 HIGH

---

## ⚠️ CRITICAL INSTRUCTIONS

**품질 게이트 준수 필수**:
1. ✅ 각 단계 완료 후 체크박스 체크
2. 🧪 모든 품질 게이트 항목 검증
3. ⚠️ 게이트 통과 전 다음 단계 진행 금지
4. 📅 진행 시 "Last Updated" 업데이트
5. 📝 이슈 발생 시 Notes 섹션에 기록

---

## 📋 목차

1. [개요](#개요)
2. [현재 문제점](#현재-문제점)
3. [목표 및 성공 기준](#목표-및-성공-기준)
4. [아키텍처 결정사항](#아키텍처-결정사항)
5. [위험 평가](#위험-평가)
6. [단계별 구현 계획](#단계별-구현-계획)
7. [품질 게이트](#품질-게이트)
8. [롤백 전략](#롤백-전략)
9. [진행 상황](#진행-상황)
10. [Notes & Learnings](#notes--learnings)

---

## 개요

### 배경
현재 일반주문과 견적서 시스템이 동일한 제품 페이지와 계산기를 공유하면서 다음 문제가 발생:
- 버튼 혼동 (장바구니 vs 견적서)
- 모드 구분 불명확 (`?mode=quotation` 파라미터만 의존)
- 세션 데이터 충돌 가능성

### 목적
일반주문과 견적서 워크플로우를 명확히 분리하여 사용자 혼란 제거 및 데이터 무결성 보장

### 범위
- 9개 제품 페이지 모드 구분 UI 개선
- 계산기 API 모드 필드 추가
- 세션 네임스페이스 분리
- **범위 외**: 가격 조정, 관리자 대시보드 (Phase 2-4)

---

## 현재 문제점

### 문제 1: 버튼 혼동
**현상**:
- 모든 제품 페이지에 "장바구니 담기" 버튼만 존재
- 견적서 모드(`?mode=quotation`)에서도 동일한 버튼 표시

**영향**:
- 사용자가 견적서 작성 중인지 일반 주문 중인지 인지 불가
- 잘못된 워크플로우로 진입 가능

**파일**:
- `/var/www/html/mlangprintauto/namecard/index.php:280-290`
- 동일 패턴이 9개 제품 전체에 존재

---

### 문제 2: 계산기 API 모드 미구분
**현상**:
```php
// calculate_price_ajax.php (현재)
return [
    'total_price' => 100000,
    'vat_price' => 110000
];
// 모드 정보 없음
```

**영향**:
- API 응답만으로 주문용인지 견적용인지 구분 불가
- 디버깅 시 데이터 출처 파악 어려움

---

### 문제 3: 세션 충돌 가능성
**현상**:
```javascript
// 현재 (namecard-premium-options.js 등)
window.currentPriceData = result;
// 일반주문/견적서 구분 없이 덮어쓰기
```

**영향**:
- 같은 탭에서 일반주문 ↔ 견적서 전환 시 데이터 뒤섞임
- 가격 데이터 불일치 발생 가능

---

## 목표 및 성공 기준

### 목표
1. **명확한 UI 구분**: 모드별 버튼 및 안내 문구 차별화
2. **데이터 무결성**: 세션 네임스페이스 분리로 충돌 방지
3. **추적 가능성**: 계산기 API에 모드 메타데이터 추가

### 성공 기준
- [ ] 9개 제품 페이지 모두 모드별 버튼 분리 완료
- [ ] 계산기 API 응답에 `mode` 필드 포함
- [ ] 세션 데이터 네임스페이스 분리 (`order.*` vs `quotation.*`)
- [ ] 수동 테스트: 일반주문 ↔ 견적서 전환 시 데이터 무결성 유지
- [ ] 기존 기능 회귀 없음 (장바구니, 견적서 생성 정상 작동)

---

## 아키텍처 결정사항

### 결정 1: 버튼 분리 방식
**선택**: 조건부 렌더링 (PHP 서버사이드)

**이유**:
- ✅ SEO 친화적 (클라이언트 숨김보다 우수)
- ✅ JavaScript 비활성화 환경에서도 작동
- ✅ 명확한 의도 표현

**대안**: CSS `display:none` (비추천, 혼란 가중)

```php
// 채택 방식
<?php if ($isQuotationMode): ?>
    <button class="btn-add-to-quotation">견적서에 추가</button>
<?php else: ?>
    <button class="btn-add-to-cart">장바구니에 담기</button>
<?php endif; ?>
```

---

### 결정 2: 세션 네임스페이스
**선택**: JavaScript 전역 객체 분리

**이유**:
- ✅ 최소 코드 변경
- ✅ 기존 로직과 호환성 유지
- ✅ 명확한 데이터 소유권

```javascript
// Before
window.currentPriceData = {...};

// After
window.orderPriceData = {...};    // 일반주문용
window.quotationPriceData = {...}; // 견적서용
```

---

### 결정 3: API 응답 확장
**선택**: 하위 호환성 유지하며 필드 추가

**이유**:
- ✅ 기존 클라이언트 코드 영향 없음
- ✅ 점진적 마이그레이션 가능

```php
// API 응답
return [
    // 기존 필드 (하위 호환)
    'total_price' => 100000,
    'vat_price' => 110000,

    // 신규 메타데이터
    'mode' => 'quotation',
    'source_table' => 'mlangprintauto_namecard',
    'calculated_at' => '2025-12-26 10:30:15'
];
```

---

## 위험 평가

### 위험 1: 9개 제품 일관성 유지 실패
- **확률**: 🟡 Medium
- **영향**: 🔴 High
- **완화**: 공통 함수 생성 후 include 방식 사용

### 위험 2: 기존 JavaScript 코드와 충돌
- **확률**: 🟢 Low
- **영향**: 🟡 Medium
- **완화**: 하위 호환성 유지 (기존 변수명 병행 사용)

### 위험 3: 세션 데이터 마이그레이션 누락
- **확률**: 🟢 Low
- **영향**: 🟡 Medium
- **완화**: 폴백 로직 구현 (신규 변수 없으면 기존 변수 사용)

---

## 단계별 구현 계획

### Phase 1.1: 공통 함수 및 유틸리티 작성 (2-3시간)

#### 목표
재사용 가능한 모드 감지 및 버튼 렌더링 함수 생성

#### 작업 항목

- [ ] **Task 1.1.1**: 모드 감지 함수 작성
  - 파일: `/var/www/html/includes/mode_helper.php` (신규)
  - 기능: `?mode=quotation` 파라미터 감지 및 검증
  ```php
  /**
   * 현재 페이지 모드 감지
   * @return string 'order' | 'quotation'
   */
  function detectPageMode() {
      $mode = $_GET['mode'] ?? 'order';
      return in_array($mode, ['order', 'quotation']) ? $mode : 'order';
  }

  /**
   * 견적서 모드 여부
   * @return bool
   */
  function isQuotationMode() {
      return detectPageMode() === 'quotation';
  }
  ```

- [ ] **Task 1.1.2**: 버튼 렌더링 컴포넌트 작성
  - 파일: `/var/www/html/includes/action_buttons.php` (신규)
  - 기능: 모드별 버튼 HTML 생성
  ```php
  /**
   * 모드별 액션 버튼 렌더링
   * @param string $mode 'order' | 'quotation'
   * @param string $productType 제품 타입
   */
  function renderActionButtons($mode, $productType) {
      if ($mode === 'quotation') {
          echo '<button class="btn-add-to-quotation" data-product="' . htmlspecialchars($productType) . '">';
          echo '견적서에 추가';
          echo '</button>';
      } else {
          echo '<button class="btn-add-to-cart" data-product="' . htmlspecialchars($productType) . '">';
          echo '장바구니에 담기';
          echo '</button>';
      }
  }
  ```

- [ ] **Task 1.1.3**: 모드별 안내 메시지 함수
  - 파일: `/var/www/html/includes/mode_helper.php`
  - 기능: 페이지 상단 모드 안내
  ```php
  /**
   * 모드 안내 배너 렌더링
   */
  function renderModeBanner($mode) {
      if ($mode === 'quotation') {
          echo '<div class="mode-banner quotation-mode">';
          echo '<span class="icon">📋</span>';
          echo '<span class="text">견적서 작성 모드입니다</span>';
          echo '</div>';
      }
  }
  ```

#### 품질 게이트 1.1
- [ ] PHP 문법 오류 없음 (`php -l includes/mode_helper.php`)
- [ ] 함수 PHPDoc 주석 완비
- [ ] XSS 방지 (`htmlspecialchars` 사용 확인)

---

### Phase 1.2: 계산기 API 모드 필드 추가 (2-3시간)

#### 목표
9개 제품의 `calculate_price_ajax.php`에 모드 메타데이터 추가

#### 작업 항목

- [ ] **Task 1.2.1**: 명함 계산기 API 수정
  - 파일: `/var/www/html/mlangprintauto/namecard/calculate_price_ajax.php`
  - 변경사항:
  ```php
  // POST에서 mode 파라미터 받기
  $mode = $_POST['mode'] ?? 'order';

  // 응답에 메타데이터 추가
  $response_data = [
      'success' => true,
      'base_price' => $base_price,
      'design_price' => $design_price,
      'premium_total' => $premium_total,
      'total_price' => $total_price,
      'vat_price' => $vat_price,

      // 신규 메타데이터
      'mode' => $mode,
      'source_table' => 'mlangprintauto_namecard',
      'calculated_at' => date('Y-m-d H:i:s')
  ];
  ```

- [ ] **Task 1.2.2**: 전단지 계산기 API 수정
  - 파일: `/var/www/html/mlangprintauto/inserted/calculate_price_ajax.php`
  - 동일 패턴 적용

- [ ] **Task 1.2.3**: 스티커 계산기 API 수정
  - 파일: `/var/www/html/mlangprintauto/sticker_new/calculate_price_ajax.php`
  - 동일 패턴 적용

- [ ] **Task 1.2.4**: 나머지 6개 제품 일괄 수정
  - 파일:
    - `/var/www/html/mlangprintauto/envelope/calculate_price_ajax.php`
    - `/var/www/html/mlangprintauto/msticker/calculate_price_ajax.php`
    - `/var/www/html/mlangprintauto/cadarok/calculate_price_ajax.php`
    - `/var/www/html/mlangprintauto/littleprint/calculate_price_ajax.php`
    - `/var/www/html/mlangprintauto/merchandisebond/calculate_price_ajax.php`
    - `/var/www/html/mlangprintauto/ncrflambeau/calculate_price_ajax.php`

#### 품질 게이트 1.2
- [ ] 9개 API 모두 `mode` 필드 추가 완료
- [ ] 기존 필드 응답 변경 없음 (하위 호환성)
- [ ] JSON 구조 검증 (`json_decode` 테스트)
- [ ] AJAX 호출 시 mode 파라미터 전달 확인

---

### Phase 1.3: 제품 페이지 UI 수정 (3-4시간)

#### 목표
9개 제품 페이지에서 모드별 버튼 및 안내 표시

#### 작업 항목

- [ ] **Task 1.3.1**: 명함 페이지 수정
  - 파일: `/var/www/html/mlangprintauto/namecard/index.php`
  - 변경사항:
  ```php
  // 상단에 추가
  require_once __DIR__ . '/../../includes/mode_helper.php';

  $pageMode = detectPageMode();
  $isQuotationMode = isQuotationMode();

  // 페이지 상단 (line 150 근처)
  <?php renderModeBanner($pageMode); ?>

  // 버튼 영역 (line 280-290 근처)
  <?php
  require_once __DIR__ . '/../../includes/action_buttons.php';
  renderActionButtons($pageMode, 'namecard');
  ?>
  ```

- [ ] **Task 1.3.2**: 전단지 페이지 수정
  - 파일: `/var/www/html/mlangprintauto/inserted/index.php`
  - 동일 패턴 적용

- [ ] **Task 1.3.3**: 나머지 7개 제품 페이지 수정
  - 파일 목록:
    - `sticker_new/index.php`
    - `envelope/index.php`
    - `msticker/index.php`
    - `cadarok/index.php`
    - `littleprint/index.php`
    - `merchandisebond/index.php`
    - `ncrflambeau/index.php`

#### 품질 게이트 1.3
- [ ] 9개 페이지 모두 `mode_helper.php` include 완료
- [ ] 모드 배너 렌더링 확인 (`?mode=quotation` 접속 시)
- [ ] 버튼 텍스트 정확성 (일반: "장바구니에 담기", 견적: "견적서에 추가")
- [ ] 기존 CSS 클래스 충돌 없음

---

### Phase 1.4: JavaScript 세션 네임스페이스 분리 (2-3시간)

#### 목표
일반주문과 견적서 가격 데이터를 별도 변수로 관리

#### 작업 항목

- [ ] **Task 1.4.1**: 공통 가격 관리 유틸리티 작성
  - 파일: `/var/www/html/js/price-data-manager.js` (신규)
  - 기능:
  ```javascript
  /**
   * 가격 데이터 매니저
   */
  const PriceDataManager = {
      // 데이터 저장
      order: null,
      quotation: null,

      /**
       * 모드별 가격 데이터 저장
       * @param {string} mode - 'order' | 'quotation'
       * @param {object} data - 가격 데이터
       */
      set(mode, data) {
          if (mode === 'quotation') {
              this.quotation = data;
              window.quotationPriceData = data; // 하위 호환
          } else {
              this.order = data;
              window.orderPriceData = data; // 하위 호환
          }

          // 기존 변수도 업데이트 (완전 하위 호환)
          window.currentPriceData = data;
      },

      /**
       * 모드별 가격 데이터 조회
       * @param {string} mode - 'order' | 'quotation'
       * @returns {object|null}
       */
      get(mode) {
          return mode === 'quotation' ? this.quotation : this.order;
      }
  };
  ```

- [ ] **Task 1.4.2**: 명함 계산기 JS 수정
  - 파일: `/var/www/html/js/namecard-premium-options.js`
  - 변경사항:
  ```javascript
  // 기존
  window.currentPriceData = result;

  // 수정
  const mode = document.querySelector('[name="mode"]')?.value || 'order';
  PriceDataManager.set(mode, result);
  ```

- [ ] **Task 1.4.3**: 전단지 계산기 JS 수정
  - 파일: `/var/www/html/js/leaflet-calculator.js`
  - 동일 패턴 적용

- [ ] **Task 1.4.4**: 나머지 제품 JS 파일 수정
  - 파일 목록:
    - `sticker-calculator.js`
    - `envelope-calculator.js`
    - (기타 제품별 JS)

#### 품질 게이트 1.4
- [ ] `PriceDataManager` 모든 제품에서 사용
- [ ] 하위 호환성 유지 (`window.currentPriceData` 여전히 작동)
- [ ] 콘솔 에러 없음
- [ ] 모드 전환 시 데이터 격리 확인

---

### Phase 1.5: 버튼 이벤트 핸들러 분리 (2-3시간)

#### 목표
모드별 버튼 클릭 시 올바른 API 호출

#### 작업 항목

- [ ] **Task 1.5.1**: 공통 이벤트 핸들러 작성
  - 파일: `/var/www/html/js/action-button-handlers.js` (신규)
  - 기능:
  ```javascript
  /**
   * 장바구니 추가 핸들러
   */
  function handleAddToCart(productType) {
      const priceData = PriceDataManager.get('order');

      if (!priceData) {
          alert('먼저 가격을 계산해주세요');
          return;
      }

      // 기존 add_to_basket.php 호출
      fetch('/mlangprintauto/shop/add_to_basket.php', {
          method: 'POST',
          body: createFormData(productType, priceData, 'order')
      });
  }

  /**
   * 견적서 추가 핸들러
   */
  function handleAddToQuotation(productType) {
      const priceData = PriceDataManager.get('quotation');

      if (!priceData) {
          alert('먼저 가격을 계산해주세요');
          return;
      }

      // 견적서 API 호출
      fetch('/mlangprintauto/quote/add_to_quotation_temp.php', {
          method: 'POST',
          body: createFormData(productType, priceData, 'quotation')
      });
  }
  ```

- [ ] **Task 1.5.2**: 버튼 이벤트 리스너 등록
  - 파일: `/var/www/html/js/action-button-handlers.js`
  - 기능:
  ```javascript
  document.addEventListener('DOMContentLoaded', () => {
      // 장바구니 버튼
      document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
          btn.addEventListener('click', () => {
              const productType = btn.dataset.product;
              handleAddToCart(productType);
          });
      });

      // 견적서 버튼
      document.querySelectorAll('.btn-add-to-quotation').forEach(btn => {
          btn.addEventListener('click', () => {
              const productType = btn.dataset.product;
              handleAddToQuotation(productType);
          });
      });
  });
  ```

- [ ] **Task 1.5.3**: 9개 제품 페이지에 JS include
  - 모든 `index.php`에 추가:
  ```html
  <script src="../../js/price-data-manager.js" defer></script>
  <script src="../../js/action-button-handlers.js" defer></script>
  ```

#### 품질 게이트 1.5
- [ ] 일반주문 버튼 클릭 → `add_to_basket.php` 호출 확인
- [ ] 견적서 버튼 클릭 → `add_to_quotation_temp.php` 호출 확인
- [ ] 네트워크 탭에서 올바른 `mode` 파라미터 전송 확인
- [ ] 에러 처리 검증 (가격 미계산 시 alert)

---

### Phase 1.6: 통합 테스트 및 검증 (2-3시간)

#### 목표
전체 시나리오 테스트 및 회귀 검증

#### 테스트 시나리오

- [ ] **시나리오 1: 일반주문 플로우**
  1. 명함 페이지 접속 (`/mlangprintauto/namecard/`)
  2. 옵션 선택 → 가격 계산
  3. "장바구니에 담기" 버튼 클릭
  4. 장바구니 페이지 확인 → 제품 존재
  5. 주문 진행 → 정상 처리

- [ ] **시나리오 2: 견적서 플로우**
  1. 명함 페이지 견적 모드 접속 (`/mlangprintauto/namecard/?mode=quotation`)
  2. 모드 배너 표시 확인
  3. 옵션 선택 → 가격 계산
  4. "견적서에 추가" 버튼 클릭
  5. 견적서 작성 페이지 이동 → 제품 존재
  6. 견적서 생성 → PDF 다운로드

- [ ] **시나리오 3: 모드 전환 테스트**
  1. 일반주문 페이지 접속 → 가격 계산
  2. 같은 탭에서 견적서 모드로 전환 (`?mode=quotation`)
  3. 가격 다시 계산
  4. 두 가격 데이터가 분리 저장되는지 확인
  5. 각 버튼 클릭 시 올바른 API 호출 확인

- [ ] **시나리오 4: 다중 제품 테스트**
  1. 명함 + 전단지 + 스티커 각각 일반주문
  2. 장바구니에 3개 제품 모두 존재 확인
  3. 명함 + 전단지 각각 견적서
  4. 견적서 작성 페이지에 2개 제품 존재 확인

#### 회귀 테스트

- [ ] **기존 기능 검증**
  - 프리미엄 옵션 (박, 넘버링 등) 정상 작동
  - 파일 업로드 정상 작동
  - 가격 계산 정확성 (기존과 동일)
  - 장바구니 수량 변경 정상 작동
  - 주문 완료 페이지 정상 표시

#### 품질 게이트 1.6
- [ ] 전체 시나리오 100% 통과
- [ ] 회귀 테스트 모두 통과
- [ ] 9개 제품 모두 동일하게 작동
- [ ] 브라우저 콘솔 에러 0건
- [ ] 네트워크 에러 0건

---

## 품질 게이트 (전체)

### Build & Compilation
- [ ] PHP 파일 문법 검증 (`find . -name "*.php" -exec php -l {} \;`)
- [ ] JavaScript Lint 검증 (`eslint js/`)

### Code Quality
- [ ] XSS 방지 확인 (모든 출력에 `htmlspecialchars`)
- [ ] SQL Injection 방지 (prepared statements 사용)
- [ ] 하드코딩 없음 (설정 파일 사용)

### Functionality
- [ ] 9개 제품 모두 모드 구분 작동
- [ ] 일반주문 플로우 정상
- [ ] 견적서 플로우 정상
- [ ] 기존 기능 회귀 없음

### Performance
- [ ] 페이지 로드 시간 변화 없음 (±5% 이내)
- [ ] API 응답 시간 변화 없음

### Security
- [ ] 모드 파라미터 검증 (화이트리스트 방식)
- [ ] CSRF 토큰 유지 (기존 시스템 따름)

---

## 롤백 전략

### Phase 1.1-1.2 롤백
**트리거**: 공통 함수 또는 API 수정 실패

**절차**:
1. 신규 파일 삭제
   ```bash
   rm /var/www/html/includes/mode_helper.php
   rm /var/www/html/includes/action_buttons.php
   ```
2. Git revert
   ```bash
   git revert HEAD~3..HEAD
   ```

### Phase 1.3-1.4 롤백
**트리거**: 제품 페이지 또는 JS 수정으로 인한 에러

**절차**:
1. 백업에서 복원
   ```bash
   cp /var/www/html/All_backUp/mlangprintauto/namecard/index.php \
      /var/www/html/mlangprintauto/namecard/
   # 9개 제품 반복
   ```
2. JavaScript 파일 롤백
   ```bash
   git checkout -- js/namecard-premium-options.js
   ```

### Phase 1.5-1.6 롤백
**트리거**: 통합 테스트 실패

**절차**:
1. 전체 Phase 1 롤백
   ```bash
   git revert --no-commit <phase1_start_commit>..HEAD
   git commit -m "Rollback: Phase 1 전체 취소"
   ```
2. DB 변경사항 없음 (이 Phase는 코드만 수정)

---

## 진행 상황

**Last Updated**: 2025-12-26

### 완료된 단계
- [ ] Phase 1.1: 공통 함수 작성
- [ ] Phase 1.2: 계산기 API 수정
- [ ] Phase 1.3: 제품 페이지 UI
- [ ] Phase 1.4: JS 네임스페이스 분리
- [ ] Phase 1.5: 이벤트 핸들러 분리
- [ ] Phase 1.6: 통합 테스트

### 진행 중
- 현재 진행 중인 단계: 없음 (시작 전)

### 블로커
- 현재 블로커: 없음

---

## Notes & Learnings

### 구현 시 주의사항
1. **9개 제품 일관성**: 명함으로 먼저 구현 후 패턴 복사
2. **하위 호환성**: 기존 변수명 병행 사용으로 점진적 전환
3. **Git 커밋 전략**: 각 Phase마다 별도 커밋 (롤백 용이)

### 개선 아이디어
- [ ] 공통 함수를 Composer 패키지로 분리 고려
- [ ] TypeScript 도입으로 타입 안정성 강화 검토

### 트러블슈팅 기록
(구현 중 발생한 이슈 기록)

---

## 체크리스트 (시작 전 확인)

- [ ] Git 저장소 최신 상태 (`git pull`)
- [ ] 로컬 환경 정상 작동 확인
- [ ] 데이터베이스 백업 완료
- [ ] `/var/www/html/All_backUp/` 백업 확인
- [ ] 이 문서 읽고 이해 완료

---

**준비 완료 시 Phase 1.1부터 시작하세요!** 🚀
