# 관리자 대시보드 구축 계획

## TL;DR

> **Quick Summary**: `/dashboard/` 폴더에 Tailwind CSS 기반의 현대적 관리자 대시보드를 구축. 7개 핵심 모듈(주문, 회원, 제품, 통계, 결제, 문의, 제품수정)을 MPA 구조로 구현하며, 기존 admin 인증 시스템을 재사용.
> 
> **Deliverables**:
> - `/dashboard/` 폴더 구조 및 공통 레이아웃
> - 7개 관리 모듈 (각각 CRUD 지원)
> - 9개 제품의 옵션/가격 수정 UI
> - JSON API 엔드포인트
> - Playwright E2E 테스트
> 
> **Estimated Effort**: Large (3-5일)
> **Parallel Execution**: YES - 6 waves
> **Critical Path**: 폴더구조 → 레이아웃 → 메인대시보드 → 모듈들 → 테스트

---

## Context

### Original Request
사용자가 `/dashboard/` 폴더에 새로운 독립적인 관리자 대시보드를 요청. 기존 `/admin/dashboard.php`와 별개로 종합 관리 기능을 제공하는 현대적 UI.

### Interview Summary
**Key Discussions**:
- **목적**: 별도 독립 대시보드 (기존 유지)
- **핵심 기능**: 7개 모듈 - 제품, 회원, 주문, 통계, 결제, 문의, 제품수정
- **UI**: Tailwind CSS CDN + Chart.js
- **반응형**: 필수 (모바일/태블릿)
- **인증**: 기존 admin_auth.php 재사용
- **구조**: MPA (Multi Page App)
- **CRUD**: 전체 지원
- **삭제**: Soft Delete
- **제품**: 9개 전체
- **테스트**: Playwright E2E

**Research Findings**:
- 기존 `/admin/dashboard.php`: Chart.js 통계, Google Analytics 스타일
- 기존 `/admin/api/stats.php`: JSON API 패턴
- DB 테이블: `mlangorder_printauto` (84K+ 주문), `users`, `mlangprintauto_*` (9개)
- 인증: 세션 기반, CSRF 보호, 8시간 타임아웃

### Metis Review
**Identified Gaps** (addressed):
- CSS 프레임워크 불일치: Tailwind CDN으로 해결
- 9개 제품 테이블 구조 상이: 제품별 Config 패턴 적용
- 대용량 데이터: 페이지네이션 필수, 인덱스 확인
- 삭제 정책: Soft Delete로 결정

---

## Work Objectives

### Core Objective
PHP 7.4 + MySQL 환경에서 Tailwind CSS 기반의 현대적 관리자 대시보드를 `/dashboard/` 폴더에 구현. 7개 관리 모듈을 MPA 구조로 제공하고, 기존 admin 인증 시스템을 재사용.

### Concrete Deliverables
- `/dashboard/index.php` - 메인 대시보드 (오늘/이번달 요약)
- `/dashboard/orders/` - 주문 관리 CRUD
- `/dashboard/members/` - 회원 관리 CRUD
- `/dashboard/products/` - 제품 관리 CRUD
- `/dashboard/stats/` - 주문 통계 차트
- `/dashboard/payments/` - 결제 현황 조회
- `/dashboard/inquiries/` - 고객 문의 관리
- `/dashboard/pricing/` - 제품 옵션/가격 수정
- `/dashboard/api/*.php` - JSON API 엔드포인트
- `/dashboard/includes/` - 공통 컴포넌트
- `tests/dashboard/*.spec.ts` - Playwright E2E 테스트

### Definition of Done
- [ ] `curl http://localhost/dashboard/` → 200 OK, 대시보드 HTML
- [ ] `curl http://localhost/dashboard/api/orders.php?action=list` → JSON 응답
- [ ] `npx playwright test tests/dashboard/` → All tests pass
- [ ] 모바일 뷰에서 사이드바 토글 작동

### Must Have
- 7개 관리 모듈 전체 CRUD
- 9개 제품의 가격/옵션 수정
- Tailwind CSS 반응형 UI
- Chart.js 통계 차트
- 기존 admin_auth.php 인증 연동
- JSON API 엔드포인트
- Soft Delete 구현

### Must NOT Have (Guardrails)
- ❌ 기존 `/admin/` 파일 수정 금지
- ❌ 새 인증 시스템 구현 금지
- ❌ CSS `!important` 사용 금지
- ❌ 결제 처리/환불 로직 금지 (조회만)
- ❌ 이메일/SMS 발송 금지
- ❌ PDF/Excel 내보내기 금지 (화면 표시만)
- ❌ 새 제품 유형 추가 금지 (기존 9개만)

---

## Verification Strategy (MANDATORY)

> **UNIVERSAL RULE: ZERO HUMAN INTERVENTION**
>
> ALL tasks in this plan MUST be verifiable WITHOUT any human action.
> This is NOT conditional — it applies to EVERY task, regardless of test strategy.

### Test Decision
- **Infrastructure exists**: YES (Playwright 설정 완료)
- **Automated tests**: Tests-after (구현 후 테스트)
- **Framework**: Playwright (E2E)

### Agent-Executed QA Scenarios (MANDATORY — ALL tasks)

**Verification Tool by Deliverable Type:**

| Type | Tool | How Agent Verifies |
|------|------|-------------------|
| **페이지 로드** | Playwright | Navigate, assert title/elements |
| **API 응답** | Bash (curl) | POST/GET, parse JSON, assert fields |
| **폼 제출** | Playwright | Fill, submit, verify result |
| **차트** | Playwright | Check canvas element exists |

---

## Execution Strategy

### Parallel Execution Waves

```
Wave 1 (Start Immediately):
├── Task 1: 폴더 구조 생성
└── Task 2: 공통 레이아웃 + Tailwind 설정

Wave 2 (After Wave 1):
├── Task 3: 인증 연동 + 라우팅
└── Task 4: API 기본 구조

Wave 3 (After Wave 2):
├── Task 5: 메인 대시보드 (요약 페이지)
├── Task 6: 주문 통계 모듈
└── Task 7: 결제 현황 모듈

Wave 4 (After Wave 3):
├── Task 8: 주문 관리 모듈
├── Task 9: 회원 관리 모듈
└── Task 10: 제품 관리 모듈

Wave 5 (After Wave 4):
├── Task 11: 고객 문의 모듈
└── Task 12: 제품 옵션/가격 수정

Wave 6 (After Wave 5):
└── Task 13: Playwright E2E 테스트

Critical Path: Task 1 → 2 → 3 → 5 → 8 → 13
```

### Dependency Matrix

| Task | Depends On | Blocks | Can Parallelize With |
|------|------------|--------|---------------------|
| 1 | None | 2, 3, 4 | None |
| 2 | 1 | 3, 5-12 | None |
| 3 | 1, 2 | 5-12 | 4 |
| 4 | 1 | 5-12 | 3 |
| 5 | 2, 3, 4 | 13 | 6, 7 |
| 6 | 2, 3, 4 | 13 | 5, 7 |
| 7 | 2, 3, 4 | 13 | 5, 6 |
| 8 | 3, 4 | 13 | 9, 10 |
| 9 | 3, 4 | 13 | 8, 10 |
| 10 | 3, 4 | 13 | 8, 9 |
| 11 | 3, 4 | 13 | 12 |
| 12 | 3, 4, 10 | 13 | 11 |
| 13 | 5-12 | None | None |

---

## TODOs

- [x] 1. 폴더 구조 생성

  **What to do**:
  - `/dashboard/` 디렉토리 생성
  - 하위 폴더 생성: `api/`, `includes/`, `orders/`, `members/`, `products/`, `stats/`, `payments/`, `inquiries/`, `pricing/`, `assets/`
  - 각 폴더에 `.gitkeep` 또는 기본 `index.php` 생성

  **Must NOT do**:
  - 기존 `/admin/` 폴더 수정 금지

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: 단순 파일/폴더 생성 작업
  - **Skills**: []
    - 기본 bash 명령어로 충분

  **Parallelization**:
  - **Can Run In Parallel**: NO (첫 번째 작업)
  - **Parallel Group**: Wave 1 (단독)
  - **Blocks**: Tasks 2, 3, 4
  - **Blocked By**: None

  **References**:
  - **Pattern References**: 없음 (새 구조)

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 폴더 구조 확인
    Tool: Bash
    Preconditions: None
    Steps:
      1. ls -la /var/www/html/dashboard/
      2. Assert: api/, includes/, orders/, members/, products/ 등 폴더 존재
    Expected Result: 10개 하위 폴더 존재
    Evidence: ls 출력 캡처
  ```

  **Commit**: YES
  - Message: `feat(dashboard): create folder structure`
  - Files: `dashboard/**`

---

- [x] 2. 공통 레이아웃 + Tailwind 설정

  **What to do**:
  - `/dashboard/includes/header.php` - HTML head, Tailwind CDN, 네비게이션
  - `/dashboard/includes/sidebar.php` - 좌측 사이드바 메뉴 (반응형 토글)
  - `/dashboard/includes/footer.php` - 푸터, JS 공통 로드
  - `/dashboard/includes/config.php` - 대시보드 설정 상수
  - Tailwind CDN Play CDN 사용: `<script src="https://cdn.tailwindcss.com"></script>`
  - Chart.js CDN: `<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>`
  - 반응형 사이드바: 모바일에서 햄버거 메뉴로 토글

  **Must NOT do**:
  - CSS `!important` 사용 금지
  - 별도 CSS 파일 생성 (Tailwind 유틸리티만 사용)

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: UI 컴포넌트, 반응형 레이아웃 작업
  - **Skills**: [`frontend-ui-ux`]
    - `frontend-ui-ux`: 현대적 UI 디자인 패턴

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Wave 1 (Task 1 직후)
  - **Blocks**: Tasks 3, 5-12
  - **Blocked By**: Task 1

  **References**:
  - **Pattern References**:
    - `/admin/dashboard.php:1-100` - 헤더 구조, 스타일링 참고
  - **External References**:
    - Tailwind Play CDN: https://tailwindcss.com/docs/installation/play-cdn

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 레이아웃 파일 로드 확인
    Tool: Bash
    Preconditions: None
    Steps:
      1. cat /var/www/html/dashboard/includes/header.php
      2. Assert: "cdn.tailwindcss.com" 포함
      3. Assert: "chart.js" 포함
    Expected Result: CDN 링크 존재
    Evidence: 파일 내용

  Scenario: 반응형 사이드바 (모바일)
    Tool: Playwright
    Preconditions: 서버 실행 중
    Steps:
      1. Set viewport: 375x667 (iPhone SE)
      2. Navigate to: http://localhost/dashboard/
      3. Assert: 사이드바 기본 숨김 (hidden 또는 -translate-x-full)
      4. Click: 햄버거 메뉴 버튼
      5. Assert: 사이드바 표시됨
    Expected Result: 모바일에서 토글 작동
    Evidence: .sisyphus/evidence/task-2-sidebar-mobile.png
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add common layout with Tailwind CSS`
  - Files: `dashboard/includes/*.php`

---

- [x] 3. 인증 연동 + 라우팅

  **What to do**:
  - `/dashboard/includes/auth.php` - admin_auth.php 래퍼
    ```php
    <?php
    require_once __DIR__ . '/../../admin/includes/admin_auth.php';
    requireAdminAuth('/dashboard/');
    ```
  - 모든 페이지에서 `require_once __DIR__ . '/includes/auth.php';` 호출
  - 로그인 안 된 경우 `/admin/mlangprintauto/login.php` 로 리다이렉트

  **Must NOT do**:
  - 새 인증 시스템 구현 금지
  - 기존 admin_auth.php 수정 금지

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: 간단한 래퍼 파일 생성
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Task 4)
  - **Blocks**: Tasks 5-12
  - **Blocked By**: Tasks 1, 2

  **References**:
  - **Pattern References**:
    - `/admin/includes/admin_auth.php:303-326` - requireAdminAuth() 함수
    - `/admin/dashboard.php:6-10` - 인증 호출 패턴

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 비로그인 상태에서 대시보드 접근
    Tool: Bash (curl)
    Preconditions: 세션 없는 상태
    Steps:
      1. curl -s -o /dev/null -w "%{http_code}" http://localhost/dashboard/
      2. Assert: 302 (리다이렉트) 또는 로그인 페이지 내용
    Expected Result: 로그인 페이지로 리다이렉트
    Evidence: HTTP 상태 코드

  Scenario: 로그인 후 대시보드 접근
    Tool: Playwright
    Preconditions: None
    Steps:
      1. Navigate to: http://localhost/admin/mlangprintauto/login.php
      2. Fill: input[name="username"] → "admin"
      3. Fill: input[name="password"] → "admin123"
      4. Click: button[type="submit"]
      5. Navigate to: http://localhost/dashboard/
      6. Assert: h1 contains "대시보드"
    Expected Result: 대시보드 페이지 표시
    Evidence: .sisyphus/evidence/task-3-auth-success.png
  ```

  **Commit**: YES
  - Message: `feat(dashboard): integrate admin authentication`
  - Files: `dashboard/includes/auth.php`

---

- [x] 4. API 기본 구조

  **What to do**:
  - `/dashboard/api/base.php` - API 공통 함수
    ```php
    <?php
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../../db.php';
    
    function jsonResponse($success, $message, $data = null) {
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }
    ```
  - `/dashboard/api/orders.php` - 주문 API 스켈레톤
  - `/dashboard/api/members.php` - 회원 API 스켈레톤
  - `/dashboard/api/products.php` - 제품 API 스켈레톤
  - `/dashboard/api/stats.php` - 통계 API 스켈레톤

  **Must NOT do**:
  - bind_param 없이 쿼리 실행 금지

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: API 스켈레톤 파일 생성
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Task 3)
  - **Blocks**: Tasks 5-12
  - **Blocked By**: Task 1

  **References**:
  - **Pattern References**:
    - `/admin/api/stats.php` - 기존 통계 API 패턴
    - `/admin/mlangprintauto/api/product_crud.php` - CRUD API 패턴

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: API base 로드 확인
    Tool: Bash
    Preconditions: None
    Steps:
      1. php -l /var/www/html/dashboard/api/base.php
      2. Assert: "No syntax errors"
    Expected Result: PHP 문법 오류 없음
    Evidence: php -l 출력

  Scenario: API 스켈레톤 응답 확인
    Tool: Bash (curl)
    Preconditions: 서버 실행, 로그인 세션
    Steps:
      1. curl http://localhost/dashboard/api/orders.php
      2. Assert: JSON 형식 응답 (success, message 키)
    Expected Result: 스켈레톤 응답 반환
    Evidence: JSON 응답
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add API base structure`
  - Files: `dashboard/api/*.php`

---

- [x] 5. 메인 대시보드 (요약 페이지)

  **What to do**:
  - `/dashboard/index.php` - 메인 대시보드
  - 요약 카드 4개:
    - 오늘 주문 건수/매출
    - 이번달 주문 건수/매출
    - 미처리 주문 건수
    - 미답변 문의 건수
  - 일별 주문 추이 차트 (Chart.js line)
  - 최근 주문 5건 목록
  - 각 모듈로 이동하는 퀵 링크

  **Must NOT do**:
  - 기존 `/admin/api/stats.php` 수정 금지 (새로 만들기)

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: 대시보드 UI, 차트 통합
  - **Skills**: [`frontend-ui-ux`]

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 6, 7)
  - **Blocks**: Task 13
  - **Blocked By**: Tasks 2, 3, 4

  **References**:
  - **Pattern References**:
    - `/admin/dashboard.php:329-450` - 요약 카드, 차트 로드 패턴
    - `/admin/api/stats.php` - 통계 쿼리 패턴

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 메인 대시보드 로드
    Tool: Playwright
    Preconditions: 로그인 상태
    Steps:
      1. Navigate to: http://localhost/dashboard/
      2. Wait for: .summary-card visible (timeout: 5s)
      3. Assert: 4개의 summary-card 존재
      4. Assert: canvas#dailyChart 존재 (차트)
      5. Screenshot: .sisyphus/evidence/task-5-main-dashboard.png
    Expected Result: 대시보드 UI 렌더링 완료
    Evidence: 스크린샷

  Scenario: 통계 API 응답 확인
    Tool: Bash (curl)
    Preconditions: 세션 쿠키
    Steps:
      1. curl http://localhost/dashboard/api/stats.php?type=summary
      2. Assert: JSON 포함 today, thisMonth 키
    Expected Result: 통계 데이터 반환
    Evidence: JSON 응답
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add main dashboard with summary cards`
  - Files: `dashboard/index.php`, `dashboard/api/stats.php`

---

- [x] 6. 주문 통계 모듈

  **What to do**:
  - `/dashboard/stats/index.php` - 통계 페이지
  - 기간 선택 (7일, 30일, 90일)
  - 일별 주문 추이 차트 (line)
  - 월별 매출 차트 (bar)
  - 품목별 비율 차트 (doughnut)
  - `/dashboard/api/stats.php` 확장:
    - `?type=daily&days=30` - 일별 추이
    - `?type=monthly` - 월별 매출
    - `?type=products` - 품목별 비율

  **Must NOT do**:
  - 복잡한 BI 분석 금지 (기본 차트만)

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: 차트 UI 구현
  - **Skills**: [`frontend-ui-ux`]

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 5, 7)
  - **Blocks**: Task 13
  - **Blocked By**: Tasks 2, 3, 4

  **References**:
  - **Pattern References**:
    - `/admin/dashboard.php:456-600` - Chart.js 구현 패턴
    - `/admin/api/stats.php` - 통계 쿼리

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 통계 페이지 차트 렌더링
    Tool: Playwright
    Preconditions: 로그인 상태
    Steps:
      1. Navigate to: http://localhost/dashboard/stats/
      2. Wait for: canvas 요소 3개 (dailyChart, monthlyChart, productChart)
      3. Select: 기간 "최근 7일"
      4. Wait for: 차트 업데이트 (500ms)
      5. Screenshot: .sisyphus/evidence/task-6-stats.png
    Expected Result: 3개 차트 렌더링
    Evidence: 스크린샷
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add order statistics module`
  - Files: `dashboard/stats/index.php`, `dashboard/api/stats.php`

---

- [x] 7. 결제 현황 모듈

  **What to do**:
  - `/dashboard/payments/index.php` - 결제 목록
  - 필터: 기간, 결제상태 (완료/대기/취소)
  - 테이블: 주문번호, 결제금액, 결제방법, 상태, 일시
  - 페이지네이션 (30건)
  - `/dashboard/api/payments.php`:
    - `?action=list&page=1&status=completed`

  **Must NOT do**:
  - 결제 처리/환불 로직 금지 (조회만)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-low`
    - Reason: 표준 CRUD 조회
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 5, 6)
  - **Blocks**: Task 13
  - **Blocked By**: Tasks 2, 3, 4

  **References**:
  - **Pattern References**:
    - `/admin/mlangprintauto/quote_manager.php` - 페이지네이션 패턴
  - **API/Type References**:
    - `payment_inicis` 테이블

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 결제 목록 조회
    Tool: Bash (curl)
    Preconditions: 세션 쿠키
    Steps:
      1. curl http://localhost/dashboard/api/payments.php?action=list&page=1
      2. Assert: JSON { success: true, data: [...], total: number }
    Expected Result: 결제 목록 반환
    Evidence: JSON 응답

  Scenario: 결제 페이지 테이블 확인
    Tool: Playwright
    Preconditions: 로그인
    Steps:
      1. Navigate to: http://localhost/dashboard/payments/
      2. Assert: table.payments-table 존재
      3. Assert: th 포함 "주문번호", "결제금액", "상태"
    Expected Result: 테이블 렌더링
    Evidence: 스크린샷
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add payment status module`
  - Files: `dashboard/payments/index.php`, `dashboard/api/payments.php`

---

- [x] 8. 주문 관리 모듈

  **What to do**:
  - `/dashboard/orders/index.php` - 주문 목록
  - `/dashboard/orders/view.php?no=123` - 주문 상세
  - `/dashboard/orders/edit.php?no=123` - 주문 수정
  - 필터: 기간, 상태(OrderStyle), 품목, 검색어
  - 테이블: 주문번호, 품목, 주문자, 금액, 상태, 일시
  - 상태 변경: 접수 → 진행중 → 완료
  - Soft Delete 지원 (is_deleted 플래그)
  - `/dashboard/api/orders.php`:
    - `?action=list&page=1&status=pending`
    - `?action=view&no=123`
    - `POST action=update&no=123` (상태 변경)
    - `POST action=delete&no=123` (soft delete)

  **Must NOT do**:
  - 주문 생성 금지 (조회/수정/삭제만)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: 복잡한 CRUD, 상태 관리
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 4 (with Tasks 9, 10)
  - **Blocks**: Task 13
  - **Blocked By**: Tasks 3, 4

  **References**:
  - **Pattern References**:
    - `/admin/MlangOrder_PrintAuto/OnlineOrder.php` - 주문 관리 패턴
    - `/mlangorder_printauto/OrderFormOrderTree.php` - 주문 렌더링
  - **API/Type References**:
    - `mlangorder_printauto` 테이블 - 주문 데이터

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 주문 목록 API
    Tool: Bash (curl)
    Steps:
      1. curl http://localhost/dashboard/api/orders.php?action=list&page=1
      2. Assert: { success: true, data: [...], total: number, page: 1 }
    Expected Result: 주문 목록
    Evidence: JSON

  Scenario: 주문 상세 보기
    Tool: Playwright
    Steps:
      1. Navigate to: http://localhost/dashboard/orders/
      2. Click: 첫 번째 주문 행
      3. Wait for: /dashboard/orders/view.php
      4. Assert: .order-detail 존재
    Expected Result: 주문 상세 페이지
    Evidence: 스크린샷

  Scenario: 주문 상태 변경
    Tool: Bash (curl)
    Steps:
      1. curl -X POST http://localhost/dashboard/api/orders.php \
           -d "action=update&no=12345&status=3"
      2. Assert: { success: true }
    Expected Result: 상태 변경 성공
    Evidence: JSON

  Scenario: Soft Delete
    Tool: Bash (curl)
    Steps:
      1. curl -X POST http://localhost/dashboard/api/orders.php \
           -d "action=delete&no=12345"
      2. Assert: { success: true }
      3. curl http://localhost/dashboard/api/orders.php?action=view&no=12345
      4. Assert: { success: false } 또는 is_deleted: true
    Expected Result: 삭제 후 조회 불가
    Evidence: JSON
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add order management module`
  - Files: `dashboard/orders/*.php`, `dashboard/api/orders.php`

---

- [x] 9. 회원 관리 모듈

  **What to do**:
  - `/dashboard/members/index.php` - 회원 목록
  - `/dashboard/members/view.php?id=123` - 회원 상세
  - `/dashboard/members/edit.php?id=123` - 회원 수정
  - 필터: 가입일, 검색어 (이름, 이메일, 전화)
  - 테이블: ID, 이름, 이메일, 전화, 가입일
  - 회원 정보 수정 (이름, 연락처 등)
  - Soft Delete (is_deleted 플래그)
  - `/dashboard/api/members.php`:
    - `?action=list&page=1&search=홍길동`
    - `?action=view&id=123`
    - `POST action=update&id=123`
    - `POST action=delete&id=123`

  **Must NOT do**:
  - 회원 생성 금지 (회원가입은 프론트에서)
  - 비밀번호 조회/수정 금지

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: CRUD + 검색 기능
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 4 (with Tasks 8, 10)
  - **Blocks**: Task 13
  - **Blocked By**: Tasks 3, 4

  **References**:
  - **Pattern References**:
    - `/admin/member/` - 기존 회원 관리
  - **API/Type References**:
    - `users` 테이블

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 회원 검색
    Tool: Bash (curl)
    Steps:
      1. curl "http://localhost/dashboard/api/members.php?action=list&search=test"
      2. Assert: { success: true, data: [...] }
    Expected Result: 검색 결과
    Evidence: JSON

  Scenario: 회원 정보 수정
    Tool: Playwright
    Steps:
      1. Navigate to: http://localhost/dashboard/members/edit.php?id=1
      2. Fill: input[name="name"] → "테스트 사용자"
      3. Click: 저장 버튼
      4. Assert: 성공 메시지 표시
    Expected Result: 수정 완료
    Evidence: 스크린샷
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add member management module`
  - Files: `dashboard/members/*.php`, `dashboard/api/members.php`

---

- [x] 10. 제품 관리 모듈

  **What to do**:
  - `/dashboard/products/index.php` - 제품 목록 (9개 제품 유형)
  - `/dashboard/products/list.php?type=namecard` - 특정 제품 옵션 목록
  - `/dashboard/products/view.php?type=namecard&id=1` - 옵션 상세
  - `/dashboard/products/edit.php?type=namecard&id=1` - 옵션 수정
  - 9개 제품 테이블별 매핑:
    - namecard, sticker_new, inserted, envelope, littleprint
    - merchandisebond, cadarok, ncrflambeau, msticker
  - 각 제품별 Config 클래스로 테이블 구조 추상화
  - `/dashboard/api/products.php`:
    - `?action=types` - 제품 유형 목록
    - `?action=list&type=namecard&page=1`
    - `POST action=update&type=namecard&id=1`
    - `POST action=delete&type=namecard&id=1`

  **Must NOT do**:
  - 새 제품 유형 추가 금지
  - 테이블 스키마 변경 금지

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: 9개 제품 테이블 처리, Config 패턴
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 4 (with Tasks 8, 9)
  - **Blocks**: Task 12, 13
  - **Blocked By**: Tasks 3, 4

  **References**:
  - **Pattern References**:
    - `/admin/mlangprintauto/api/product_crud.php` - CRUD 패턴
    - `/mlangprintauto/namecard/explane_namecard.php` - 제품 구조
  - **API/Type References**:
    - `mlangprintauto_namecard`, `mlangprintauto_sticker` 등 9개 테이블

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 제품 유형 목록
    Tool: Bash (curl)
    Steps:
      1. curl http://localhost/dashboard/api/products.php?action=types
      2. Assert: 9개 제품 유형 포함 (namecard, sticker_new, ...)
    Expected Result: 제품 유형 배열
    Evidence: JSON

  Scenario: 명함 옵션 목록
    Tool: Bash (curl)
    Steps:
      1. curl "http://localhost/dashboard/api/products.php?action=list&type=namecard&page=1"
      2. Assert: { success: true, data: [...], total: number }
    Expected Result: 명함 옵션 목록
    Evidence: JSON

  Scenario: 제품 옵션 수정
    Tool: Playwright
    Steps:
      1. Navigate to: http://localhost/dashboard/products/edit.php?type=namecard&id=1
      2. Update: money 필드
      3. Click: 저장
      4. Assert: 성공 메시지
    Expected Result: 가격 수정 완료
    Evidence: 스크린샷
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add product management module`
  - Files: `dashboard/products/*.php`, `dashboard/api/products.php`

---

- [ ] 11. 고객 문의 모듈

  **What to do**:
  - `/dashboard/inquiries/index.php` - 문의 목록
  - `/dashboard/inquiries/view.php?id=123` - 문의 상세 + 답변
  - 필터: 상태 (미답변/답변완료), 기간
  - 테이블: ID, 제목, 작성자, 상태, 일시
  - 답변 작성/수정 기능
  - `/dashboard/api/inquiries.php`:
    - `?action=list&status=pending`
    - `?action=view&id=123`
    - `POST action=reply&id=123&content=...`

  **Must NOT do**:
  - 이메일 발송 금지

  **Recommended Agent Profile**:
  - **Category**: `unspecified-low`
    - Reason: 단순 CRUD + 답변
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 5 (with Task 12)
  - **Blocks**: Task 13
  - **Blocked By**: Tasks 3, 4

  **References**:
  - **Pattern References**:
    - `/admin/customer_inquiries.php` - 기존 문의 관리
  - **API/Type References**:
    - `customer_inquiries` 테이블

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 미답변 문의 필터
    Tool: Bash (curl)
    Steps:
      1. curl "http://localhost/dashboard/api/inquiries.php?action=list&status=pending"
      2. Assert: 모든 항목의 replied_at이 null
    Expected Result: 미답변 문의만 반환
    Evidence: JSON

  Scenario: 답변 작성
    Tool: Playwright
    Steps:
      1. Navigate to: http://localhost/dashboard/inquiries/view.php?id=1
      2. Fill: textarea[name="reply"] → "답변 내용입니다."
      3. Click: 답변 등록 버튼
      4. Assert: 성공 메시지
      5. Assert: 상태가 "답변완료"로 변경
    Expected Result: 답변 저장 완료
    Evidence: 스크린샷
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add customer inquiry module`
  - Files: `dashboard/inquiries/*.php`, `dashboard/api/inquiries.php`

---

- [ ] 12. 제품 옵션/가격 수정

  **What to do**:
  - `/dashboard/pricing/index.php` - 가격 관리 메인
  - `/dashboard/pricing/edit.php?type=namecard` - 특정 제품 가격 일괄 수정
  - 9개 제품별 가격 필드 매핑:
    - namecard: money, DesignMoney
    - sticker_new: money, DesignMoney
    - inserted: money, DesignMoney
    - ... (각 제품별 상이)
  - 일괄 퍼센트 인상/인하 기능
  - 수정 이력 로깅 (선택사항)
  - `/dashboard/api/pricing.php`:
    - `?action=list&type=namecard`
    - `POST action=update` (개별 수정)
    - `POST action=bulk_update` (일괄 수정)

  **Must NOT do**:
  - 테이블 스키마 변경 금지

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: 9개 제품 가격 처리, 일괄 수정
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 5 (with Task 11)
  - **Blocks**: Task 13
  - **Blocked By**: Tasks 3, 4, 10

  **References**:
  - **Pattern References**:
    - `/admin/roll_sticker_settings.php` - 가격 설정 패턴
  - **API/Type References**:
    - 9개 `mlangprintauto_*` 테이블의 money 컬럼

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 가격 목록 조회
    Tool: Bash (curl)
    Steps:
      1. curl "http://localhost/dashboard/api/pricing.php?action=list&type=namecard"
      2. Assert: 각 항목에 money 필드 존재
    Expected Result: 가격 목록
    Evidence: JSON

  Scenario: 일괄 가격 인상
    Tool: Playwright
    Steps:
      1. Navigate to: http://localhost/dashboard/pricing/edit.php?type=namecard
      2. Fill: input[name="percent"] → "10"
      3. Click: "일괄 인상" 버튼
      4. Assert: 확인 모달 표시
      5. Click: 확인
      6. Assert: 성공 메시지
    Expected Result: 10% 인상 적용
    Evidence: 스크린샷
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add product pricing management`
  - Files: `dashboard/pricing/*.php`, `dashboard/api/pricing.php`

---

- [ ] 13. Playwright E2E 테스트

  **What to do**:
  - `tests/dashboard/auth.spec.ts` - 인증 테스트
  - `tests/dashboard/main.spec.ts` - 메인 대시보드 테스트
  - `tests/dashboard/orders.spec.ts` - 주문 CRUD 테스트
  - `tests/dashboard/members.spec.ts` - 회원 CRUD 테스트
  - `tests/dashboard/products.spec.ts` - 제품 CRUD 테스트
  - `tests/dashboard/stats.spec.ts` - 통계 차트 테스트
  - 각 테스트 파일에서:
    - 페이지 로드 확인
    - CRUD 동작 확인
    - 반응형 UI 확인

  **Must NOT do**:
  - 실제 데이터 삭제 금지 (테스트 데이터만)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: 종합 E2E 테스트
  - **Skills**: [`playwright`]
    - `playwright`: Playwright 테스트 작성

  **Parallelization**:
  - **Can Run In Parallel**: NO (최종 작업)
  - **Parallel Group**: Wave 6 (단독)
  - **Blocks**: None
  - **Blocked By**: Tasks 5-12

  **References**:
  - **Pattern References**:
    - `tests/tier-1-readonly/*.spec.ts` - 기존 테스트 패턴
    - `playwright.config.ts` - 설정

  **Acceptance Criteria**:

  **Agent-Executed QA Scenarios:**

  ```
  Scenario: 전체 테스트 실행
    Tool: Bash
    Steps:
      1. npx playwright test tests/dashboard/ --reporter=list
      2. Assert: Exit code 0
      3. Assert: All tests passed
    Expected Result: 모든 테스트 통과
    Evidence: 테스트 출력

  Scenario: 테스트 리포트 생성
    Tool: Bash
    Steps:
      1. npx playwright test tests/dashboard/ --reporter=html
      2. ls playwright-report/index.html
      3. Assert: 파일 존재
    Expected Result: HTML 리포트 생성
    Evidence: 리포트 파일
  ```

  **Commit**: YES
  - Message: `test(dashboard): add Playwright E2E tests`
  - Files: `tests/dashboard/*.spec.ts`

---

## Commit Strategy

| After Task | Message | Files | Verification |
|------------|---------|-------|--------------|
| 1 | `feat(dashboard): create folder structure` | dashboard/** | ls |
| 2 | `feat(dashboard): add common layout with Tailwind CSS` | dashboard/includes/*.php | php -l |
| 3 | `feat(dashboard): integrate admin authentication` | dashboard/includes/auth.php | curl |
| 4 | `feat(dashboard): add API base structure` | dashboard/api/*.php | php -l |
| 5 | `feat(dashboard): add main dashboard with summary cards` | dashboard/index.php, api/stats.php | curl |
| 6 | `feat(dashboard): add order statistics module` | dashboard/stats/*.php | Playwright |
| 7 | `feat(dashboard): add payment status module` | dashboard/payments/*.php | curl |
| 8 | `feat(dashboard): add order management module` | dashboard/orders/*.php | curl |
| 9 | `feat(dashboard): add member management module` | dashboard/members/*.php | curl |
| 10 | `feat(dashboard): add product management module` | dashboard/products/*.php | curl |
| 11 | `feat(dashboard): add customer inquiry module` | dashboard/inquiries/*.php | Playwright |
| 12 | `feat(dashboard): add product pricing management` | dashboard/pricing/*.php | curl |
| 13 | `test(dashboard): add Playwright E2E tests` | tests/dashboard/*.spec.ts | npx playwright test |

---

## Success Criteria

### Verification Commands
```bash
# 서버 접근 확인
curl -s http://localhost/dashboard/ | grep -q "대시보드" && echo "PASS" || echo "FAIL"

# API 응답 확인
curl -s http://localhost/dashboard/api/orders.php?action=list | jq '.success' # Expected: true

# 테스트 실행
npx playwright test tests/dashboard/ --reporter=line
# Expected: All tests passed
```

### Final Checklist
- [ ] 7개 관리 모듈 모두 CRUD 작동
- [ ] 9개 제품 가격 수정 가능
- [ ] 모바일에서 사이드바 토글 작동
- [ ] 차트 3종 렌더링 (일별, 월별, 품목별)
- [ ] Soft Delete 작동 (is_deleted 플래그)
- [ ] 기존 /admin/ 파일 수정 없음
- [ ] 모든 Playwright 테스트 통과
