# 기술 매뉴얼 생성 (DOCX + Notion MD)

## TL;DR

> **Quick Summary**: 두손기획인쇄 시스템의 프로그래머 납품용 기술 매뉴얼을 DOCX + Notion Markdown 2종으로 생성한다. "프로그래머가 제작 후 납품한다는 개념"으로, 코드 중심의 기술적 상세(JS 트리거, AJAX 엔드포인트, DB 테이블, 파일 경로)를 8개 챕터로 구조화한다.
> 
> **Deliverables**:
> - `docs/generate_technical_manual.js` — DOCX 생성 스크립트
> - `docs/두손기획인쇄_기술매뉴얼.docx` — 최종 DOCX 파일
> - `docs/두손기획인쇄_기술매뉴얼_Notion.md` — Notion 임포트용 마크다운
> 
> **Estimated Effort**: Medium (3-4 tasks)
> **Parallel Execution**: YES — 2 waves
> **Critical Path**: Task 1 (Notion MD) → Task 2 (DOCX script) → Task 3 (Generate + Verify) → Task 4 (Commit + Deploy)

---

## Context

### Original Request
사용자 요청: "메뉴얼 작성한 것에 등장하는 품목에 관한 기술적인(코드 중심 트리거 역할 JS) 것을 메뉴얼로 작성... 프로그래머가 제작 후 납품한다는 개념으로 작성해주기 바래"

### Interview Summary
**Key Discussions**:
- 관리자 운영 매뉴얼은 이미 완료 (commit cfc4f6bd)
- 이번에는 코드 중심 기술 교육자료
- 맨 처음에 로그인 아이디/비밀번호 포함
- DOCX + Notion MD 2종
- Ch0~Ch7 총 8개 챕터 구조

**Research Findings (Previous Session)**:
- 10개 핵심 파일 코드 분석 완료 (~4000줄)
- 캐러셀: 순수 CSS+JS, 8+2클론, 4초 자동재생
- 가격계산기: 2패턴 (DB cascade vs 수학공식)
- 갤러리: 듀얼소스, 개인정보 필터링, 라이트박스
- 업로드: 2모드 모달, 드래그&드롭
- 주문: 55개 bind_param, dual-write
- 인증: bcrypt + 평문 자동업그레이드

### Metis Review
**Identified Gaps** (addressed):
- Content source: "Discoveries" handoff 내용을 task description에 직접 임베딩
- Credentials: 사용자 명시적 요청대로 포함 (SMTP 앱 비밀번호, Plesk 관리자 비밀번호는 제외)
- Helper extensions: codeBlock, inlineCode 등 generate_manual.js에 직접 추가
- Scope control: 챕터당 3-5페이지, 핵심 함수 5개 이내로 제한

---

## Work Objectives

### Core Objective
두손기획인쇄 시스템의 코드 중심 기술 매뉴얼을 DOCX + Notion Markdown 2종으로 생성하여 프로그래머 납품 문서로 활용한다.

### Concrete Deliverables
- `docs/generate_technical_manual.js` — Node.js DOCX 생성 스크립트
- `docs/두손기획인쇄_기술매뉴얼.docx` — 생성된 DOCX 파일
- `docs/두손기획인쇄_기술매뉴얼_Notion.md` — Notion 임포트용 마크다운
- Git commit with all 3 files

### Definition of Done
- [ ] `node docs/generate_technical_manual.js` 실행 성공
- [ ] DOCX 파일 크기 > 10KB
- [ ] Notion MD 파일 200줄 이상
- [ ] 문서 내 참조된 파일 경로가 실제 존재
- [ ] Git commit 완료
- [ ] FTP 프로덕션 업로드 완료

### Must Have
- Ch0: 접속 정보 (관리자/DB/FTP/GitHub 로그인 정보)
- Ch1: 대문페이지 (캐러셀 JS, 카드 이미지 경로)
- Ch2: 가격 계산기 (DB cascade + 스티커 수학 공식)
- Ch3: 갤러리 시스템 (듀얼 소스, 라이트박스)
- Ch4: 파일 업로드 (모달, 드래그&드롭, AJAX)
- Ch5: 주문 프로세스 (폼→ProcessOrder→DB 흐름)
- Ch6: 관리자 대시보드 (구조, API 엔드포인트)
- Ch7: 네비게이션 (2모드, 사이드바)
- 한국어로 작성
- "문서 기준일: 2026-02-22" 표시

### Must NOT Have (Guardrails)
- SMTP 앱 비밀번호 (`2CP3P5BTS83Y`) 포함 금지
- Plesk 관리자 비밀번호 (`h%42D9u2m`) 포함 금지
- NAS 접속 정보 포함 금지
- 영문 버전 (`/en/`) 문서화 금지
- 전체 DB 스키마 덤프 금지 (테이블명 + 핵심 컬럼 3-5개만)
- 관리자 운영 매뉴얼 내용 중복 금지
- 챕터당 5페이지 초과 금지
- 챕터당 핵심 함수 5개 초과 금지
- AGENTS.md 내용 그대로 복사 금지
- Notion MD에 HTML 태그 사용 금지

---

## Verification Strategy

> **ZERO HUMAN INTERVENTION** — ALL verification is agent-executed. No exceptions.

### Test Decision
- **Infrastructure exists**: NO (문서 생성 태스크, 코드 테스트 불필요)
- **Automated tests**: None
- **Framework**: N/A

### QA Policy
Every task includes agent-executed QA scenarios.
Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`.

---

## Execution Strategy

### Parallel Execution Waves

```
Wave 1 (Start Immediately — content generation, MAX PARALLEL):
├── Task 1: Notion Markdown 생성 [writing]
├── Task 2: DOCX 생성 스크립트 작성 [unspecified-high]

Wave 2 (After Wave 1 — build + verify):
├── Task 3: DOCX 생성 실행 + 검증 [quick]

Wave 3 (After Wave 2 — deploy):
├── Task 4: Git 커밋 + FTP 배포 [quick]

Critical Path: Task 1+2 (parallel) → Task 3 → Task 4
```

### Dependency Matrix

| Task | Depends On | Blocks |
|------|-----------|--------|
| 1 | — | 3, 4 |
| 2 | — | 3 |
| 3 | 1, 2 | 4 |
| 4 | 3 | — |

### Agent Dispatch Summary

- **Wave 1**: 2 tasks — T1 → `writing`, T2 → `unspecified-high`
- **Wave 2**: 1 task — T3 → `quick`
- **Wave 3**: 1 task — T4 → `quick` + `git-master`

---

## TODOs

- [ ] 1. Notion Markdown 기술 매뉴얼 생성

  **What to do**:
  - `docs/두손기획인쇄_기술매뉴얼_Notion.md` 파일을 생성한다
  - 아래 8개 챕터 구조로 한국어 기술 매뉴얼을 작성한다
  - Notion 임포트 호환성을 위해 순수 마크다운만 사용 (HTML 태그 금지)
  - 코드 블록은 ``` 펜스 사용 (언어 힌트 포함)
  - 테이블은 마크다운 테이블 문법 사용

  **챕터 구조 및 내용** (아래 내용을 기반으로 각 챕터 작성):

  ### Ch0. 접속 정보

  ```
  | 구분 | 접속 주소 | 아이디 | 비밀번호 |
  |------|----------|--------|---------|
  | 홈페이지 | https://dsp114.co.kr | - | - |
  | 관리자 대시보드 | https://dsp114.co.kr/dashboard/ | admin | admin123 |
  | 데이터베이스 (MySQL) | localhost:3306 | dsp1830 | ds701018 |
  | FTP (운영서버) | ftp://dsp114.co.kr | dsp1830 | cH*j@yzj093BeTtc |
  | GitHub | github.com/songyoungsoo | songyoungsoo | yeongsu32@gmail.com |
  | 고객센터 전화 | 02-2632-1830 | - | - |
  ```

  - 서버 환경: PHP 7.4+ (로컬) / PHP 8.2 (프로덕션), MySQL 5.7+
  - 로컬 개발 Document Root: `/var/www/html`
  - 프로덕션 FTP 웹 루트: `/httpdocs/` (반드시 이 경로 사용!)
  - 환경 자동 감지: `config.env.php`에서 SERVER_NAME 기반으로 localhost/production URL 자동 전환

  ### Ch1. 대문 페이지 (index.php)

  **캐러셀 슬라이더**:
  - 순수 CSS+JS 커스텀 구현 (외부 라이브러리 없음)
  - 슬라이드 구성: 8개 실제 + 2개 클론 = 10개 (무한루프)
  - 자동재생: `setInterval(nextSlide, 4000)` — 4초 간격
  - 전환 효과: `CSS transition: transform 1000ms ease-in-out`
  - 모바일: `window.innerWidth <= 768` → 슬라이드 너비 `100vw`
  - 비디오 슬라이드: 첫 번째 슬라이드, `toggleHeroVideo()` — 클릭 시 재생, 자동재생 중지
  - 핵심 JS 함수: `nextSlide()`, `prevSlide()`, `goToSlide(index)`, `toggleHeroVideo()`

  **이미지 경로**:
  ```
  /slide/slide_inserted.gif       — 전단지
  /slide/slide__Sticker.gif       — 스티커 1
  /slide/slide_cadarok.gif        — 카다록
  /slide/slide_Ncr.gif            — NCR양식지
  /slide/slide__poster.gif        — 포스터
  /slide/slide__Sticker_2.gif     — 스티커 2
  /slide/slide__Sticker_3.gif     — 스티커 3
  /media/explainer_poster.jpg     — 비디오 썸네일
  ```

  **제품 카드 그리드**:
  - 12개 카드: 9개 온라인주문 + 3개 별도견적(배너, 옥외스티커, 책자인쇄)
  - CSS Grid: `.products-grid` (`css/product-layout.css`)
  - 이미지 경로: `/ImgFolder/gate_picto/{product}_s.png`
  - 각 카드 클릭 → `mlangprintauto/{product_folder}/`

  **실시간 견적 데모**: 하단 영역에 견적 위젯 (`quote_gauge.php`)

  ### Ch2. 품목별 가격 계산기

  **2가지 패턴**:

  **패턴 A: DB Cascade (8개 제품 — 명함, 전단지, 봉투, 포스터, 상품권, 카다록, NCR, 자석스티커)**
  - Cascade 흐름: 종류(`MY_type`) → 재질(`Section`) → 수량(`MY_amount`) → 가격
  - AJAX 엔드포인트 (각 제품 폴더 내):
    - `GET get_paper_types.php?style={typeValue}` → 재질 옵션 목록
    - `GET get_quantities.php?style={}&section={}&potype={}` → 수량 옵션 목록
    - `GET calculate_price_ajax.php?MY_type={}&Section={}&POtype={}&MY_amount={}&ordertype={}` → 가격 (JSON: `{price, vat_price}`)
  - DB 테이블:
    - `mlangprintauto_transactioncate` — BigNo/TreeNo 계층 구조 (종류→재질→수량 cascade)
    - `mlangprintauto_{product}` — 제품별 가격표 (style×section×quantity → money)
  - 핵심 JS 함수 (제품별 index.php 인라인):
    - `handleTypeChange()` — 종류 변경 시 재질 로드
    - `handleSectionChange()` — 재질 변경 시 수량 로드
    - `calculatePrice()` — 가격 계산 요청
  - 프리미엄 옵션: 박/넘버링/미싱/귀돌이/오시 (체크박스, JS 클라이언트 계산)

  **패턴 B: 수학 공식 (스티커 — sticker_new)**
  - DB가 아닌 수식 기반 — `sticker_new/calculate_price_ajax.php` (243줄)
  - 입력 파라미터 6개: jong(재질), garo(가로mm), sero(세로mm), mesu(매수), uhyung(편집비), domusong(모양)
  - 재질코드: `j1 = substr(jong, 0, 3)` → `jil`/`jka`/`jsp`/`cka` → DB 테이블 `shop_d1`~`shop_d4`
  - 수량별 요율(yoyo): 0.09~0.15, 기본비용(mg): 5000~7000
  - 계산 공식:
    ```
    기본가격 = (가로+4) × (세로+4) × 수량 × 요율(yoyo)
    + 도무송비용 (칼크기×수량 기반)
    + 특수용지비용 (유포지/강접/초강접)
    × 사이즈 마진비율 (소형 1.0, 대형 1.25)
    + 기본관리비(mg) × 수량/1000
    + 디자인비(uhyung)
    = 공급가액 → ×1.1 = VAT포함가
    ```
  - 핵심 JS: `autoCalculatePrice()` → `fetch('./calculate_price_ajax.php', {method:'POST', body:formData})`
  - debounce 150ms, 사이즈 검증 (49mm 이하 → 자동 사각도무송)

  **재질별 요율 테이블 (4개)**:
  | 코드 | 테이블 | 재질 |
  |------|--------|------|
  | `jil` | `shop_d1` | 아트유광/무광/비코팅, 모조비코팅 |
  | `jka` | `shop_d2` | 강접아트유광코팅 |
  | `jsp` | `shop_d3` | 유포지, 은데드롱, 투명, 크라프트 |
  | `cka` | `shop_d4` | 초강접아트코팅/비코팅 |

  ### Ch3. 갤러리 시스템

  **제품 페이지 갤러리**:
  - 포함 구조: `includes/simple_gallery_include.php` → `gallery_data_adapter.php` → `new_gallery_wrapper.php`
  - 500×400 메인 컨테이너, 200% 마우스 오버 줌
  - `$gallery_product` 변수로 제품별 데이터 로드

  **샘플더보기 팝업** (`popup/proof_gallery.php`, 524줄):
  - 24개/페이지 그리드 (6열), pagination 지원
  - 듀얼 소스:
    1. 갤러리 샘플: `/ImgFolder/sample/{product}/` + `/ImgFolder/samplegallery/{product}/`
    2. 실제 주문 이미지: `/mlangorder_printauto/upload/{주문번호}/` (DB `mlangorder_printauto` 테이블)
  - 개인정보 보호: 명함/봉투/양식지/스티커/전단지는 갤러리 이미지만 사용 (고객 주문 이미지 제외)
  - 라이트박스 뷰어: 클릭 → fixed overlay, ESC 닫기, ‹ › 네비게이션
  - Multi-File JSON 파싱: `ThingCate` 컬럼에 JSON 배열 저장 시 자동 파싱
  - 공통 JS: `js/common-gallery-popup.js`

  ### Ch4. 파일 업로드 시스템

  **업로드 모달** (`includes/upload_modal.php` + `upload_modal.js`):
  - 2모드: 완성파일 업로드 / 디자인 의뢰
  - 디자인 의뢰: 안내 패널(Step 1) → 파일 첨부(Step 2)
  - 핵심 JS: `selectUploadMethod('upload'|'design')`, `proceedToDesignUpload()`
  - 드래그&드롭: `#modalUploadDropzone`, `<input type="file" multiple>`
  - 허용 형식: JPG, PNG, PDF, AI, EPS, PSD, ZIP (15MB 이하)
  - CSS: `css/upload-modal-common.css`

  **장바구니 저장**:
  - `window.handleModalBasketAdd()` (제품별 구현)
  - AJAX: `POST /mlangprintauto/shop/add_to_basket.php` (FormData)
  - DB: `shop_temp` 테이블에 세션 기반 저장

  **관리자 교정 업로드** (`dashboard/proofs/api.php`):
  - API: `POST dashboard/proofs/api.php?action=upload`
  - 저장 경로: `/mlangorder_printauto/upload/{주문번호}/`
  - 이미지 뷰어: 원본 크기 오버레이, 화살표 네비게이션

  ### Ch5. 주문 프로세스

  **전체 흐름**:
  ```
  OnlineOrder_unified.php (주문 폼)
    → ProcessOrder_unified.php (POST 처리, 899줄)
      → INSERT INTO mlangorder_printauto (DB 저장)
        → OrderComplete_unified.php (완료 페이지)
          → send_order_email.php (이메일 발송)
  ```

  **ProcessOrder_unified.php 핵심 처리**:
  1. CSRF 토큰 검증
  2. POST 데이터 수집 (주문자/사업자/배송/결제 정보)
  3. 장바구니(`shop_temp`) 또는 직접 주문 아이템 반복 처리
  4. 각 아이템 → `mlangorder_printauto` INSERT (55개 bind_param, 3단계 검증)
  5. Dual-Write: `orders` + `order_items` 테이블 동시 저장
  6. 파일 이동: temp → `uploads/orders/{order_no}/`
  7. 장바구니 정리: `DELETE FROM shop_temp WHERE session_id=?`

  **주요 DB 테이블**:
  | 테이블 | 용도 | 핵심 컬럼 |
  |--------|------|----------|
  | `shop_temp` | 장바구니 | session_id, price, quantity, product_type |
  | `mlangorder_printauto` | 주문 | no, name, phone, money_5, OrderStyle |
  | `orders` | 주문 (신규) | order_no, user_id, total_amount |
  | `order_items` | 주문 품목 (신규) | order_id, product_type, quantity |

  **OrderStyle 상태 코드**: 0(접수대기)→1(입금대기)→2(입금확인)→3(접수완료)→4(시안확인중)→5(인쇄준비)→6(인쇄중)→7(후가공)→8(작업완료)→9(발송완료)→10(수령완료)

  ### Ch6. 관리자 대시보드

  **구조**:
  - 프레임워크: Tailwind CSS CDN + Chart.js
  - 브랜드 컬러: `#1E4E79`
  - 인증: `$_SESSION['admin_username']` 체크
  - 레이아웃: `h-screen overflow-hidden` (뷰포트 고정), 사이드바 독립 스크롤

  **사이드바 메뉴** (5그룹 21메뉴):
  - 주문·교정: 관리자 주문, 주문 관리, 교정 관리, 교정 등록, 결제 현황, 택배 관리, 발송 목록
  - 소통·견적: 이메일 발송, 채팅 관리, 견적 관리, 고객 문의
  - 제품·가격: 제품 관리, 가격 관리, 견적옵션, 스티커수정, 갤러리 관리, 품목옵션
  - 관리·통계: 회원 관리, 주문 통계, 방문자분석, 사이트 설정
  - 기존 관리자: 주문 관리(구), 교정 관리(구)

  **API 패턴**: `dashboard/api/{module}.php` — POST/GET + action 파라미터
  - orders.php, products.php, email.php, settings.php 등

  ### Ch7. 네비게이션 시스템

  **2모드 네비** (`includes/nav.php`):
  - Simple 모드: 제품 클릭 → 바로 이동
  - Detailed 모드: 호버 → 서브메뉴 메가 패널 (DB `mlangprintauto_transactioncate` BigNo/TreeNo 기반)
  - 모드 전환: `toggleNavMode()` → DB `site_settings.nav_default_mode` + 쿠키 `nav_mode` 오버라이드

  **사이드바** (`includes/sidebar.php`):
  - 우측 플로팅 메뉴: 고객센터, 파일전송, 업무안내, 입금안내, 운영시간
  - 300ms mouseleave 딜레이 + 클릭=고정(pinned)
  - 카카오톡 TALK.svg 벡터 아이콘

  **인증 헤더** (`includes/header.php`):
  - 로그인/회원가입 또는 마이페이지/로그아웃 분기
  - `$_SESSION['user_id']` 확인
  - 장바구니 카운트 실시간 표시

  **Must NOT do**:
  - SMTP 앱 비밀번호, Plesk 관리자 비밀번호, NAS 접속정보 포함 금지
  - 영문 버전 문서화 금지
  - HTML 태그 사용 금지 (Notion 호환)

  **Recommended Agent Profile**:
  - **Category**: `writing`
    - Reason: 기술 문서 작성 태스크, 한국어 전문 문서
  - **Skills**: [`docx`]
    - `docx`: Notion 마크다운 포맷 참고

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Task 2)
  - **Blocks**: Task 3, Task 4
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `docs/두손기획인쇄_관리자매뉴얼_Notion.md` — Notion MD 포맷/구조 패턴 (동일한 한국어 문서 스타일 따라할 것)
  - `AGENTS.md` — 시스템 전체 기술 참조 (9개 제품, 가격계산, 갤러리, 인증 등)

  **Content References** (각 챕터 내용 소스):
  - `CLAUDE_DOCS/API_SPEC.md` — AJAX 엔드포인트 목록
  - `CLAUDE_DOCS/COMPONENT_REFERENCE.md` — PHP 클래스/메서드 참조
  - `CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md` — DB 스키마, 제품 매핑
  - `INSTALL_DOCS/FRONTEND_ARCHITECTURE.md` — CSS/JS 파일 구조

  **WHY Each Reference Matters**:
  - `관리자매뉴얼_Notion.md`: 동일 프로젝트의 이전 매뉴얼이므로 같은 스타일/톤/구조를 따라야 함
  - `AGENTS.md`: 가장 상세한 시스템 기술 문서, 각 챕터 내용의 primary source
  - `API_SPEC.md`: AJAX 엔드포인트 정확한 경로/파라미터 확인용
  - `COMPONENT_REFERENCE.md`: PHP 클래스 이름/메서드 시그니처 확인용

  **Acceptance Criteria**:

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Notion MD 파일 생성 확인
    Tool: Bash
    Steps:
      1. test -f docs/두손기획인쇄_기술매뉴얼_Notion.md && echo "EXISTS" || echo "MISSING"
      2. wc -l docs/두손기획인쇄_기술매뉴얼_Notion.md
    Expected Result: EXISTS, 줄 수 > 300
    Evidence: .sisyphus/evidence/task-1-notion-md-exists.txt

  Scenario: HTML 태그 미포함 확인 (Notion 호환)
    Tool: Bash
    Steps:
      1. grep -cP '<[a-z]+[\s>]' docs/두손기획인쇄_기술매뉴얼_Notion.md || echo "0"
    Expected Result: 0 (HTML 태그 없음)
    Evidence: .sisyphus/evidence/task-1-no-html.txt

  Scenario: 8개 챕터 제목 존재 확인
    Tool: Bash
    Steps:
      1. grep -c '^## ' docs/두손기획인쇄_기술매뉴얼_Notion.md
    Expected Result: 8개 이상의 ## 헤딩
    Evidence: .sisyphus/evidence/task-1-chapters.txt
  ```

  **Commit**: YES (groups with Task 2)
  - Message: `docs: 기술 매뉴얼 2종 추가 (DOCX + Notion 마크다운)`
  - Files: `docs/두손기획인쇄_기술매뉴얼_Notion.md`, `docs/generate_technical_manual.js`, `docs/두손기획인쇄_기술매뉴얼.docx`

- [ ] 2. DOCX 생성 스크립트 작성

  **What to do**:
  - `docs/generate_technical_manual.js` 파일을 생성한다
  - `docs/generate_manual.js`의 헬퍼 함수/스타일 패턴을 그대로 재사용한다
  - 추가 헬퍼 함수 구현: `codeBlock(lines)` (회색 배경 + Courier New), `inlineCode(text)` (모노스페이스 인라인), `flowArrow(steps)` (Step1 → Step2 → Step3)
  - Task 1의 Notion MD와 **동일한 내용**을 DOCX 포맷으로 구현
  - 표지: "두손기획인쇄 기술 매뉴얼", "문서 기준일: 2026-02-22", "문서 분류: 사내용 (비공개)"
  - 헤더/푸터: 기존 매뉴얼과 동일 패턴 (페이지 번호, 문서 제목)

  **Must NOT do**:
  - SMTP 앱 비밀번호, Plesk 관리자 비밀번호, NAS 접속정보 포함 금지
  - 1000줄 이상의 스크립트 금지 (700줄 이내 목표)
  - `generate_manual.js` 파일 수정 금지 (별도 파일 생성)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: Node.js 코드 생성 + 대량 콘텐츠 구조화 작업
  - **Skills**: [`docx`]
    - `docx`: DOCX 생성 API 레퍼런스 (docx npm 패키지 사용법)

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Task 1)
  - **Blocks**: Task 3
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `docs/generate_manual.js:1-100` — 헬퍼 함수 (hCell, dCell, makeTable, h1, h2, h3, p, bullet, numbered, tip, warn, spacer, pb), 스타일 상수 (C.primary, FONT 등), numbering config 패턴. 이 패턴을 그대로 복사하여 재사용
  - `docs/generate_manual.js:101-460` — Document 구조, sections 배열, cover page, header/footer, 챕터별 children 배열 구성 패턴. 동일한 구조로 기술 매뉴얼 내용 채우기

  **Content References**:
  - Task 1의 Notion MD 내용과 동일한 내용을 DOCX로 변환 (Task 1 완료 후 해당 파일 읽기)
  - 또는 AGENTS.md + CLAUDE_DOCS/ 에서 직접 추출

  **External References**:
  - `docx` npm 패키지 사용법: Load skill `docx` for API reference

  **WHY Each Reference Matters**:
  - `generate_manual.js`: 동일 프로젝트의 이전 DOCX 생성기. 색상/폰트/레이아웃을 완벽히 일치시켜야 함
  - `docx` skill: 코드 블록, 하이퍼링크 등 추가 헬퍼 구현 시 API 참조 필요

  **Acceptance Criteria**:

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: JS 파일 문법 검증
    Tool: Bash
    Steps:
      1. node -c docs/generate_technical_manual.js && echo "SYNTAX OK" || echo "SYNTAX ERROR"
    Expected Result: SYNTAX OK
    Evidence: .sisyphus/evidence/task-2-syntax-check.txt

  Scenario: DOCX 생성 실행
    Tool: Bash
    Steps:
      1. node docs/generate_technical_manual.js
      2. test -f docs/두손기획인쇄_기술매뉴얼.docx && echo "DOCX EXISTS" || echo "DOCX MISSING"
      3. stat -c%s docs/두손기획인쇄_기술매뉴얼.docx
    Expected Result: 정상 실행, DOCX EXISTS, 파일 크기 > 10000 bytes
    Evidence: .sisyphus/evidence/task-2-docx-generated.txt

  Scenario: 자격증명 누출 검사
    Tool: Bash
    Steps:
      1. node -e "const fs=require('fs'); const c=fs.readFileSync('docs/generate_technical_manual.js','utf8'); ['2CP3P5BTS83Y','h%42D9u2m','sknas205204203'].forEach(p=>{if(c.includes(p))console.log('LEAK:',p)})"
    Expected Result: 출력 없음 (누출 없음)
    Evidence: .sisyphus/evidence/task-2-no-credentials.txt
  ```

  **Commit**: YES (groups with Task 1)
  - Message: `docs: 기술 매뉴얼 2종 추가 (DOCX + Notion 마크다운)`
  - Files: `docs/generate_technical_manual.js`, `docs/두손기획인쇄_기술매뉴얼.docx`

- [ ] 3. DOCX 생성 실행 + 최종 검증

  **What to do**:
  - Task 2에서 생성한 스크립트가 이미 DOCX를 생성했다면, 결과물 검증만 수행
  - 아직 생성 안 됐다면 `node docs/generate_technical_manual.js` 실행
  - 모든 QA 시나리오 실행
  - 문제 발견 시 스크립트/MD 수정

  **Must NOT do**:
  - DOCX를 수동으로 열어서 확인하라는 지시 금지

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: 검증 + 간단한 수정 작업
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Wave 2 (sequential)
  - **Blocks**: Task 4
  - **Blocked By**: Task 1, Task 2

  **References**:
  - `docs/generate_technical_manual.js` — 생성할/생성된 DOCX 스크립트
  - `docs/두손기획인쇄_기술매뉴얼_Notion.md` — 생성된 Notion MD

  **Acceptance Criteria**:

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: DOCX 파일 존재 및 크기 검증
    Tool: Bash
    Steps:
      1. ls -la docs/두손기획인쇄_기술매뉴얼.docx
      2. stat -c%s docs/두손기획인쇄_기술매뉴얼.docx
    Expected Result: 파일 존재, 크기 > 10KB
    Evidence: .sisyphus/evidence/task-3-docx-size.txt

  Scenario: Notion MD 최종 검증
    Tool: Bash
    Steps:
      1. wc -l docs/두손기획인쇄_기술매뉴얼_Notion.md
      2. grep -c '^## ' docs/두손기획인쇄_기술매뉴얼_Notion.md
      3. grep -cP '<[a-z]+[\s>]' docs/두손기획인쇄_기술매뉴얼_Notion.md || echo "0"
    Expected Result: 줄 수 > 300, 챕터 8개 이상, HTML 태그 0개
    Evidence: .sisyphus/evidence/task-3-md-verify.txt

  Scenario: 3개 파일 모두 존재 확인
    Tool: Bash
    Steps:
      1. ls -la docs/generate_technical_manual.js docs/두손기획인쇄_기술매뉴얼.docx docs/두손기획인쇄_기술매뉴얼_Notion.md
    Expected Result: 3개 파일 모두 존재
    Evidence: .sisyphus/evidence/task-3-all-files.txt
  ```

  **Commit**: NO (Task 4에서 일괄 커밋)

- [ ] 4. Git 커밋 + FTP 프로덕션 배포

  **What to do**:
  - 3개 파일을 git add + commit:
    - `docs/generate_technical_manual.js`
    - `docs/두손기획인쇄_기술매뉴얼.docx`
    - `docs/두손기획인쇄_기술매뉴얼_Notion.md`
  - 커밋 메시지: `docs: 프로그래머 납품용 기술 매뉴얼 2종 추가 (DOCX + Notion 마크다운)`
  - FTP 프로덕션 업로드:
    ```bash
    curl -T docs/두손기획인쇄_기술매뉴얼.docx \
      ftp://dsp114.co.kr/httpdocs/docs/두손기획인쇄_기술매뉴얼.docx \
      --user "dsp1830:cH*j@yzj093BeTtc"
    curl -T docs/두손기획인쇄_기술매뉴얼_Notion.md \
      ftp://dsp114.co.kr/httpdocs/docs/두손기획인쇄_기술매뉴얼_Notion.md \
      --user "dsp1830:cH*j@yzj093BeTtc"
    ```

  **Must NOT do**:
  - git push --force 금지
  - .env 파일이나 자격증명 파일 커밋 금지

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: git + FTP 명령 실행
  - **Skills**: [`git-master`]
    - `git-master`: Git 커밋 규칙 준수

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Wave 3 (sequential, final)
  - **Blocks**: None
  - **Blocked By**: Task 3

  **References**:
  - `README.md` — FTP 배포 경로 확인 (`/httpdocs/`)
  - `DEPLOYMENT.md` — 배포 가이드

  **Acceptance Criteria**:

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Git 커밋 성공 확인
    Tool: Bash
    Steps:
      1. git log --oneline -1
      2. git status
    Expected Result: 최신 커밋에 "기술 매뉴얼" 포함, working tree clean
    Evidence: .sisyphus/evidence/task-4-git-commit.txt

  Scenario: FTP 업로드 확인
    Tool: Bash
    Steps:
      1. curl -s -o /dev/null -w "%{http_code}" "https://dsp114.co.kr/docs/두손기획인쇄_기술매뉴얼.docx"
    Expected Result: HTTP 200
    Evidence: .sisyphus/evidence/task-4-ftp-verify.txt
  ```

  **Commit**: YES
  - Message: `docs: 프로그래머 납품용 기술 매뉴얼 2종 추가 (DOCX + Notion 마크다운)`
  - Files: `docs/generate_technical_manual.js`, `docs/두손기획인쇄_기술매뉴얼.docx`, `docs/두손기획인쇄_기술매뉴얼_Notion.md`

---

## Final Verification Wave

N/A — 이 태스크는 문서 생성이므로 Final Verification Wave 불필요. Task 3에서 충분한 검증 수행.

---

## Commit Strategy

- **Wave 1+2 완료 후**: `docs: 프로그래머 납품용 기술 매뉴얼 2종 추가 (DOCX + Notion 마크다운)`
  - Files: `docs/generate_technical_manual.js`, `docs/두손기획인쇄_기술매뉴얼.docx`, `docs/두손기획인쇄_기술매뉴얼_Notion.md`
  - Pre-commit: `node -c docs/generate_technical_manual.js`

---

## Success Criteria

### Verification Commands
```bash
# DOCX 파일 존재 및 크기
test -f docs/두손기획인쇄_기술매뉴얼.docx && stat -c%s docs/두손기획인쇄_기술매뉴얼.docx
# Expected: 파일 존재, 크기 > 10000

# Notion MD 존재 및 줄 수
wc -l docs/두손기획인쇄_기술매뉴얼_Notion.md
# Expected: > 300줄

# Git 커밋 확인
git log --oneline -1
# Expected: "기술 매뉴얼" 포함

# FTP 배포 확인
curl -s -o /dev/null -w "%{http_code}" "https://dsp114.co.kr/docs/두손기획인쇄_기술매뉴얼.docx"
# Expected: 200
```

### Final Checklist
- [ ] 8개 챕터 모두 포함 (Ch0~Ch7)
- [ ] 접속정보 (아이디/비밀번호) 챕터 첫 번째에 위치
- [ ] 코드 중심 기술 상세 (JS 함수, AJAX 엔드포인트, DB 테이블)
- [ ] 한국어로 작성
- [ ] DOCX + Notion MD 2종 생성
- [ ] Git 커밋 완료
- [ ] FTP 프로덕션 배포 완료
