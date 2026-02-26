## 🎨 UI/UX Improvements

### 방문자분석 URL 한글화 (2026-02-12)
**구현 위치**: `dashboard/visitors/index.php`

URL 경로 → 한글 제품명 매핑 (클릭 가능한 링크):
- `/mlangprintauto/sticker_new/index.php` → **스티커** (파란색 링크)
- `/mlangprintauto/inserted/index.php` → **전단지**
- 9개 제품 + 로그인/회원가입/주문서/장바구니 등 30개 경로 매핑

**매핑 구조**:
- `PAGE_NAME_MAP`: 정확 경로 매칭 (30개)
- `PAGE_PATH_PATTERNS`: 부분 경로 매칭 (17개 패턴)
- `getPageName(url)`: 2단계 매칭 함수

**적용 위치**: 인기 페이지, 진입/이탈 페이지, 실시간 방문자 테이블

### 주문통계 숫자 카운트업 애니메이션 (2026-02-12)
**구현 위치**: `dashboard/stats/index.php`

요약 카드 4개에 0→목표값 카운트업 애니메이션:
- `animateNumber(el, target, 800, isCurrency)` 함수
- easeOutExpo 이징 (`1 - Math.pow(2, -10 * progress)`)
- 통화 축약값(만/억) 애니메이션 중 포맷 유지
- `requestAnimationFrame` 기반 부드러운 렌더링

### 명함 재질 Hover 효과 (2026-01-28)
**변경 전**:
- 돋보기 아이콘 🔍 표시
- 어두운 overlay 배경 (rgba(0,0,0,0.4))
- 이미지 1.1배 확대

**변경 후**:
- ✅ "클릭하면 확대되어보입니다" 텍스트 메시지
- ✅ 투명 overlay (깔끔한 UI)
- ✅ 이미지 1.1배 확대 유지
- ✅ 부드러운 fade-in 애니메이션

**구현 위치**: `mlangprintauto/namecard/explane_namecard.php`

### 카톡상담 버튼 SVG 원형 이미지 교체 (2026-02-16)
**구현 위치**: `includes/sidebar.php`

우측 플로팅 메뉴의 카톡상담 버튼을 TALK.svg 벡터 원형 이미지로 교체:

**변경 전**:
- CSS 노란 원형 배경 (`#FEE500`) + 50×50 `talk_icon.png` 아이콘 + 별도 "카톡상담" HTML 라벨
- 3개 요소 (배경/아이콘/텍스트) 조합

**변경 후**:
- ✅ `TALK.svg` 벡터 이미지가 원형 버튼 전체를 차지 (노란 원형 + 말풍선 TALK + 카톡상담 텍스트 일체형)
- ✅ SVG 4KB (기존 PNG 대비 5배 작음)
- ✅ 반응형 전 구간 (100px/70px/52px) 벡터 스케일링으로 깨짐 없음
- ✅ "TALK" 글자가 path 데이터라 폰트 미설치 환경에서도 정확 렌더링

**관련 파일**:
- `/TALK.svg` — 카카오톡 원형 벡터 아이콘 (425.2×425.2 viewBox)
- `/TALK.png` — PNG 래스터 백업 (426×426, 미사용)
- `/TALK.ai` — Illustrator 원본 (웹 사용 불가)

**CSS 변경**: `.fm-kakao-circle`에서 background/border 제거, `.fm-kakao-full` 클래스 추가 (100% fill)

### 사이드바 패널 호버 UX 개선 (2026-02-16)
**구현 위치**: `includes/sidebar.php`

**문제**: 패널이 마우스 호버로 열리지만, 마우스가 버튼→패널 사이 빈 공간을 지날 때 패널이 즉시 사라짐

**해결 (2가지 병행)**:
1. **300ms mouseleave 딜레이** — 마우스가 버튼을 벗어나도 300ms 유예, 패널 위에 도달하면 타이머 취소
2. **📌 클릭=고정 힌트** — 전 패널(5개) 헤더에 `<span class="fm-pin-hint">📌 클릭=고정</span>` 표시, 고정(pinned) 상태에서는 자동 숨김

**JS 동작** (line 519~553):
```javascript
// mouseleave: 300ms 딜레이 후 닫기
item.addEventListener('mouseleave', function() {
    if (this.classList.contains('pinned')) return;
    this.dataset.closeTimer = setTimeout(() => {
        this.classList.remove('active');
    }, 300);
});

// mouseenter: 타이머 취소 (패널 위에 도달)
item.addEventListener('mouseenter', function() {
    clearTimeout(this.dataset.closeTimer);
});
```

**CSS**:
- `.fm-panel-title` → `display: flex; justify-content: space-between;` (제목+힌트 양쪽 정렬)
- `.fm-pin-hint` → `font-size: 10px; opacity: 0.7;` (작고 은은하게)
- `.fm-item.pinned .fm-pin-hint` → `display: none;` (고정 시 힌트 숨김)

**적용 패널**: 고객센터, 파일전송, 업무안내, 입금안내, 운영시간 (전체 5개)

### 대시보드 레이아웃 최적화 (2026-02-17)

**구현 위치**: `dashboard/includes/header.php`, `dashboard/includes/sidebar.php`, `dashboard/includes/footer.php`, `dashboard/orders/view.php`

**변경 전**: 대시보드 페이지 높이가 사이드바 메뉴(982px)에 의해 결정 → 주문 상세 1,350px, 뷰포트(900px) 초과로 스크롤 필요

**변경 후**:
- ✅ `header.php`: 레이아웃 컨테이너 `min-h-screen` → `h-screen overflow-hidden` (고정 높이)
- ✅ `sidebar.php`: `overflow-y-auto` 추가 (사이드바 독립 스크롤, 페이지 높이에 영향 안 줌)
- ✅ `footer.php`: 푸터 HTML 제거 (53px 절약, 관리자 페이지에 불필요)
- ✅ `view.php`: 모든 카드 `p-4`→`p-3`, 간격/마진 축소, 요청사항 `max-h-32 overflow-y-auto`
- 결과: **1,350px → 900px** (뷰포트에 스크롤 없이 모든 정보 표시)

**레이아웃 구조**:
```
<div class="flex h-screen pt-11 overflow-hidden">  ← 뷰포트 고정
  <aside overflow-y-auto>  ← 사이드바 독립 스크롤
  <main overflow-y-auto>   ← 메인 콘텐츠 독립 스크롤
</div>
```

**영향 범위**: 대시보드 전체 페이지 (header/sidebar/footer 공통 컴포넌트)

### 마이페이지 주문 상태 OrderStyle 통일 (2026-02-17)

**구현 위치**: `mypage/index.php`

**변경 전**: `level` 컬럼(5단계) 기반 — 대시보드 `OrderStyle` 변경이 반영 안 됨

**변경 후**:
- ✅ `OrderStyle` 컬럼 기반으로 통일 (SSOT)
- ✅ `getCustomerStatus()` 함수: OrderStyle 11가지 → 고객용 5단계 그룹핑
  - 주문접수: OrderStyle 0,1,2
  - 접수완료: OrderStyle 3,4
  - 작업중: OrderStyle 5,6,7,9,10
  - 작업완료: OrderStyle 8
  - 배송중: 송장번호 존재 시
- ✅ 필터/쿼리/표시 모두 OrderStyle 기반

**상태 변경 경로**: `dashboard/orders/view.php` → 상태 드롭다운 → POST `/dashboard/api/orders.php?action=update` → `UPDATE mlangorder_printauto SET OrderStyle = ?` → 마이페이지에 즉시 반영

### 프로필 사업자 상세주소 레거시 파싱 개선 (2026-02-17)

**구현 위치**: `mypage/profile.php`

**문제**: `business_address`에 `|||` 구분자 없이 저장된 레거시 데이터가 전부 readonly 메인 주소 필드에 들어가서 상세주소 필드가 빈 상태로 남음. 사용자가 상세주소를 수정할 수 없음.

**예시 데이터**: `[07301] 서울 영등포구 영등포로36길 9 1층 두손기획인쇄 (영등포동4가)` (구분자 없음)

**해결**: DOMContentLoaded 파싱 시 도로명주소 패턴(`/^(.+(?:로|길|가)\s*\d+(?:-\d+)?)\s+(.+)$/`)으로 자동 분리:
- 메인 주소(readonly): `서울 영등포구 영등포로36길 9`
- 상세주소(editable): `1층 두손기획인쇄`
- 참고항목(editable): `(영등포동4가)`

**정규화**: 페이지 로드 시 `|||` 없는 레거시 데이터를 즉시 정규 형식으로 변환 (`updateBusinessAddress()` 자동 호출)

**저장 형식**: `[우편번호] 메인주소|||상세주소 (참고항목)` — 이후 페이지 로드 시 `|||` 기반 정상 파싱

