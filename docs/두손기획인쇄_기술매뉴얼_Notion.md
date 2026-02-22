# 두손기획인쇄 기술 매뉴얼

> **문서 기준일**: 2026-02-22 | **버전**: 1.0 | **분류**: 사내용 (비공개)
> 
> 이 문서는 두손기획인쇄 인쇄 주문 시스템의 **프로그래머 납품용 기술 매뉴얼**입니다.
> "프로그래머가 제작 후 납품한다는 개념"으로, 코드 중심의 기술적 상세(JS 트리거, AJAX 엔드포인트, DB 테이블, 파일 경로)를 담습니다.

---

## Ch0. 접속 정보

### 시스템 접속 정보

| 구분 | 접속 주소 | 아이디 | 비밀번호 |
|------|----------|--------|---------|
| 홈페이지 | https://dsp114.co.kr | - | - |
| 관리자 대시보드 | https://dsp114.co.kr/dashboard/ | admin | admin123 |
| 데이터베이스 (MySQL) | localhost:3306 | dsp1830 | ds701018 |
| FTP (운영서버) | ftp://dsp114.co.kr | dsp1830 | cH*j@yzj093BeTtc |
| GitHub | github.com/songyoungsoo | songyoungsoo | yeongsu32@gmail.com |
| 고객센터 전화 | 02-2632-1830 | - | - |

### 서버 환경

- **PHP**: 7.4+ (로컬) / 8.2 (프로덕션)
- **MySQL**: 5.7+
- **로컬 Document Root**: `/var/www/html`
- **프로덕션 FTP 웹 루트**: `/httpdocs/` ⚠️ 반드시 이 경로 사용!
- **환경 자동 감지**: `config.env.php` — `SERVER_NAME` 기반으로 localhost/production URL 자동 전환

### FTP 배포 예시

```bash
curl -T 로컬파일.php \
  ftp://dsp114.co.kr/httpdocs/경로/파일.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

---

## Ch1. 대문 페이지 (index.php)

### 캐러셀 슬라이더

- **파일**: `/index.php` (인라인 JS, 외부 라이브러리 없음)
- **슬라이드 구성**: 8개 실제 + 2개 클론 = 10개 (무한루프)
- **자동재생**: `setInterval(nextSlide, 4000)` — 4초 간격
- **전환 효과**: `CSS transition: transform 1000ms ease-in-out`
- **모바일**: `window.innerWidth <= 768` → 슬라이드 너비 `100vw`

#### 핵심 JS 함수

| 함수명 | 역할 |
|--------|------|
| `nextSlide()` | 다음 슬라이드로 이동 |
| `prevSlide()` | 이전 슬라이드로 이동 |
| `goToSlide(index)` | 특정 슬라이드로 이동 |
| `toggleHeroVideo()` | 비디오 슬라이드 재생/정지, 자동재생 중지 |

#### 슬라이드 이미지 경로

```
/slide/slide_inserted.gif       — 전단지
/slide/slide__Sticker.gif       — 스티커 1
/slide/slide_cadarok.gif        — 카다록
/slide/slide_Ncr.gif            — NCR양식지
/slide/slide__poster.gif        — 포스터
/slide/slide__Sticker_2.gif     — 스티커 2
/slide/slide__Sticker_3.gif     — 스티커 3
/media/explainer_poster.jpg     — 비디오 썸네일 (첫 번째 슬라이드)
```

### 제품 카드 그리드

- **구성**: 12개 카드 (9개 온라인주문 + 3개 별도견적: 배너, 옥외스티커, 책자인쇄)
- **CSS**: `.products-grid` (`css/product-layout.css`)
- **이미지 경로**: `/ImgFolder/gate_picto/{product}_s.png`

#### 제품 카드 이미지 경로 매핑

| 제품 | 이미지 파일 | 링크 경로 |
|------|------------|----------|
| 스티커 | `sticker_new_s.png` | `mlangprintauto/sticker_new/` |
| 전단지 | `inserted_s.png` | `mlangprintauto/inserted/` |
| 명함 | `namecard_s.png` | `mlangprintauto/namecard/` |
| 봉투 | `envelope_s.png` | `mlangprintauto/envelope/` |
| 포스터 | `littleprint_s.png` | `mlangprintauto/littleprint/` |
| 상품권 | `merchandisebond_s.png` | `mlangprintauto/merchandisebond/` |
| 카다록 | `cadarok_s.png` | `mlangprintauto/cadarok/` |
| NCR양식지 | `ncrflambeau_s.png` | `mlangprintauto/ncrflambeau/` |
| 자석스티커 | `msticker_s.png` | `mlangprintauto/msticker/` |

### 실시간 견적 위젯

- **파일**: `mlangprintauto/quote_gauge.php` (하단 영역 include)

---

## Ch2. 품목별 가격 계산기

두 가지 완전히 다른 패턴이 존재한다.

### 패턴 A: DB Cascade (8개 제품)

**대상**: 명함, 전단지, 봉투, 포스터, 상품권, 카다록, NCR양식지, 자석스티커

#### Cascade 흐름

```
종류(MY_type) → 재질(Section) → 수량(MY_amount) → 가격
```

#### AJAX 엔드포인트 (각 제품 폴더 내)

| 엔드포인트 | 메서드 | 파라미터 | 응답 |
|-----------|--------|---------|------|
| `get_paper_types.php` | GET | `style={typeValue}` | 재질 옵션 목록 (JSON) |
| `get_quantities.php` | GET | `style={}&section={}&potype={}` | 수량 옵션 목록 (JSON) |
| `calculate_price_ajax.php` | GET | `MY_type={}&Section={}&POtype={}&MY_amount={}&ordertype={}` | `{price, vat_price}` |

#### DB 테이블

| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|----------|
| `mlangprintauto_transactioncate` | 종류→재질→수량 계층 | BigNo, TreeNo, Ttable, title |
| `mlangprintauto_{product}` | 제품별 가격표 | style, Section, quantity, money |

#### 핵심 JS 함수 (제품별 `index.php` 인라인)

| 함수명 | 역할 |
|--------|------|
| `handleTypeChange()` | 종류 변경 시 재질 AJAX 로드 |
| `handleSectionChange()` | 재질 변경 시 수량 AJAX 로드 |
| `calculatePrice()` | 가격 계산 AJAX 요청 |

> **프리미엄 옵션**: 박/넘버링/미싱/귀돌이/오시 — 체크박스, JS 클라이언트 계산

---

### 패턴 B: 수학 공식 (스티커 전용)

> ⚠️ **스티커는 DB 가격표 조회가 아닌 수학 공식으로 계산한다!**

- **파일**: `mlangprintauto/sticker_new/calculate_price_ajax.php` (243줄)

#### 입력 파라미터

| 파라미터 | 설명 | 예시 |
|---------|------|------|
| `jong` | 재질 코드+이름 | `jil 아트유광코팅` |
| `garo` | 가로 (mm) | `90` |
| `sero` | 세로 (mm) | `55` |
| `mesu` | 수량 (매) | `1000` |
| `uhyung` | 편집비 | `0` / `10000` / `30000` |
| `domusong` | 모양 코드+이름 | `08000 원형` |

#### 계산 공식

```
재질코드(j1) = substr(jong, 0, 3)  → jil/jka/jsp/cka
요율(yoyo) = shop_d1~d4 테이블에서 수량별 조회

기본가격 = (가로+4) × (세로+4) × 수량 × yoyo
         + 도무송비용 (칼크기 × 수량 기반)
         + 특수용지비용 (유포지/강접/초강접)
         × 사이즈 마진비율 (소형 1.0, 대형 1.25)
         + 기본관리비(mg) × 수량/1000
         + 디자인비(uhyung)
         = 공급가액 → × 1.1 = VAT포함가
```

#### 재질별 요율 테이블

| 코드 | DB 테이블 | 재질 |
|------|----------|------|
| `jil` | `shop_d1` | 아트유광/무광/비코팅, 모조비코팅 |
| `jka` | `shop_d2` | 강접아트유광코팅 |
| `jsp` | `shop_d3` | 유포지, 은데드롱, 투명, 크라프트 |
| `cka` | `shop_d4` | 초강접아트코팅/비코팅 |

#### 핵심 JS

```javascript
// 가격 자동 계산 (debounce 150ms)
autoCalculatePrice() → fetch('./calculate_price_ajax.php', {
  method: 'POST',
  body: formData  // jong, garo, sero, mesu, uhyung, domusong
})
// 사이즈 검증: 49mm 이하 → 자동 사각도무송 적용
```

---

## Ch3. 갤러리 시스템

### 제품 페이지 갤러리

- **포함 구조**: `includes/simple_gallery_include.php` → `gallery_data_adapter.php` → `new_gallery_wrapper.php`
- **컨테이너**: 500×400px, 마우스 오버 200% 줌
- **제품별 데이터**: `$gallery_product` 변수로 로드

### 샘플더보기 팝업

- **파일**: `popup/proof_gallery.php` (524줄)
- **URL**: `popup/proof_gallery.php?cate={제품명}&page={N}`
- **레이아웃**: 24개/페이지, 6열 그리드

#### 듀얼 소스 구조

```
소스 1: 갤러리 샘플 이미지
  /ImgFolder/sample/{product}/
  /ImgFolder/samplegallery/{product}/

소스 2: 실제 주문 이미지 (DB 조회)
  /mlangorder_printauto/upload/{주문번호}/
  → DB: mlangorder_printauto.ThingCate 컬럼에서 파일명 조회
```

#### 개인정보 보호 필터

- **갤러리 이미지만** (고객 주문 이미지 제외): 명함, 봉투, 양식지, 스티커, 전단지
- **혼합 표시** (갤러리 + 실제 주문): 포스터, 카다록 등

#### 라이트박스 뷰어

- 클릭 → fixed overlay (원본 크기)
- ESC 닫기, ‹ › 화살표 네비게이션, 카운터(1/N)
- **공통 JS**: `js/common-gallery-popup.js`

#### Multi-File JSON 파싱

```php
// ThingCate 컬럼에 JSON 배열 저장 시 자동 파싱
if (strpos($thing_cate, '[{') === 0) {
    $decoded = json_decode($thing_cate, true);
    foreach ($decoded as $file_info) {
        $files_to_check[] = $file_info['saved_name'];
    }
}
```

---

## Ch4. 파일 업로드 시스템

### 업로드 모달

- **파일**: `includes/upload_modal.php` + `includes/upload_modal.js`
- **CSS**: `css/upload-modal-common.css`

#### 2가지 모드

| 모드 | 설명 | JS 함수 |
|------|------|---------|
| 완성파일 업로드 | 고객이 직접 파일 첨부 | `selectUploadMethod('upload')` |
| 디자인 의뢰 | 안내 패널(Step 1) → 파일 첨부(Step 2) | `selectUploadMethod('design')` → `proceedToDesignUpload()` |

#### 드래그&드롭

- **드롭존**: `#modalUploadDropzone`
- **파일 선택**: `<input type="file" multiple>`
- **허용 형식**: JPG, PNG, PDF, AI, EPS, PSD, ZIP
- **최대 크기**: 15MB/파일

#### 장바구니 저장 흐름

```
window.handleModalBasketAdd()  ← 제품별 구현
  → FormData 구성 (제품 옵션 + 파일)
  → POST /mlangprintauto/shop/add_to_basket.php
  → INSERT INTO shop_temp (session_id 기반)
```

### 관리자 교정 업로드

- **파일**: `dashboard/proofs/api.php`

| action | 메서드 | 설명 |
|--------|--------|------|
| `upload` | POST | 교정 파일 업로드 |
| `list` | GET | 파일 목록 조회 |

- **저장 경로**: `/mlangorder_printauto/upload/{주문번호}/`
- **이미지 뷰어**: 원본 크기 오버레이, 화살표 네비게이션

---

## Ch5. 주문 프로세스

### 전체 흐름

```
OnlineOrder_unified.php (주문 폼)
  ↓ POST
ProcessOrder_unified.php (899줄)
  ↓ INSERT
mlangorder_printauto (DB)
  ↓ redirect
OrderComplete_unified.php (완료 페이지)
  ↓ AJAX
send_order_email.php (이메일 발송)
```

### ProcessOrder_unified.php 핵심 처리

1. CSRF 토큰 검증
2. POST 데이터 수집 (주문자/사업자/배송/결제 정보)
3. `shop_temp` 장바구니 아이템 반복 처리
4. 각 아이템 → `mlangorder_printauto` INSERT (**55개 bind_param**, 3단계 검증)
5. Dual-Write: `orders` + `order_items` 테이블 동시 저장
6. 파일 이동: temp → `uploads/orders/{order_no}/`
7. 장바구니 정리: `DELETE FROM shop_temp WHERE session_id=?`

#### bind_param 3단계 검증 규칙

```php
$placeholder_count = substr_count($query, '?');  // 1단계: ? 개수
$type_count = strlen($type_string);              // 2단계: 타입 문자열 길이
$var_count = 55; // 수동 카운트                   // 3단계: 변수 개수

if ($placeholder_count === $type_count && $type_count === $var_count) {
    mysqli_stmt_bind_param($stmt, $type_string, ...);
}
```

### 주요 DB 테이블

| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|----------|
| `shop_temp` | 장바구니 | session_id, price, quantity, product_type |
| `mlangorder_printauto` | 주문 | no, name, phone, money_5, OrderStyle |
| `orders` | 주문 (신규) | order_no, user_id, total_amount |
| `order_items` | 주문 품목 (신규) | order_id, product_type, quantity |

### OrderStyle 상태 코드

| 코드 | 상태 | 설명 |
|------|------|------|
| 0 | 접수대기 | 고객 주문 제출 |
| 1 | 입금대기 | 결제 대기 중 |
| 2 | 입금확인 | 결제 확인 완료 |
| 3 | 접수완료 | 관리자 접수 처리 |
| 4 | 시안확인중 | 교정 시안 확인 단계 |
| 5 | 인쇄준비 | 인쇄 준비 중 |
| 6 | 인쇄중 | 인쇄 진행 중 |
| 7 | 후가공 | 후처리 단계 |
| 8 | 작업완료 | 제작 완료 |
| 9 | 발송완료 | 고객에게 발송 완료 |
| 10 | 수령완료/반품 | 고객 수령 또는 반품 |

### 인증 시스템 (includes/auth.php)

- **세션 유지**: 8시간 (28800초)
- **자동 로그인**: 30일 (`remember_tokens` 테이블)
- **비밀번호**: bcrypt 해시 + 평문 레거시 자동 업그레이드
- **DB**: `users` 테이블 (primary), `member` 테이블 (legacy 이중 쓰기)

---

## Ch6. 관리자 대시보드

### 기술 스택

- **프레임워크**: Tailwind CSS CDN + Chart.js
- **브랜드 컬러**: `#1E4E79`
- **인증**: `$_SESSION['admin_username']` 체크
- **레이아웃**: `h-screen overflow-hidden` (뷰포트 고정), 사이드바 독립 스크롤

### 파일 구조

```
dashboard/
├── includes/
│   ├── config.php      — $DASHBOARD_NAV (5그룹 21메뉴), $PRODUCT_TYPES
│   ├── header.php      — Tailwind CDN, Chart.js, 인증 체크
│   ├── sidebar.php     — 사이드바 메뉴 렌더링
│   └── footer.php      — 공통 푸터
├── api/
│   ├── orders.php      — 주문 CRUD
│   ├── products.php    — 제품/카테고리 관리
│   ├── email.php       — 이메일 캠페인
│   ├── settings.php    — 사이트 설정
│   └── quotes.php      — 견적 관리
├── orders/
│   ├── index.php       — 주문 목록 (인라인 상태 변경)
│   └── view.php        — 주문 상세
└── proofs/
    ├── index.php       — 교정 목록
    └── api.php         — 교정 파일 API
```

### 사이드바 메뉴 구조

| 그룹 | 메뉴 항목 |
|------|----------|
| 주문·교정 | 관리자 주문, 주문 관리, 교정 관리, 교정 등록, 결제 현황, 택배 관리, 발송 목록 |
| 소통·견적 | 이메일 발송, 채팅 관리, 견적 관리, 고객 문의 |
| 제품·가격 | 제품 관리, 가격 관리, 견적옵션, 스티커수정, 갤러리 관리, 품목옵션 |
| 관리·통계 | 회원 관리, 주문 통계, 방문자분석, 사이트 설정 |
| 기존 관리자 | 주문 관리(구), 교정 관리(구) |

### API 패턴

```
GET  /dashboard/api/orders.php?action=list&page=1
POST /dashboard/api/orders.php?action=update  {id, OrderStyle}
POST /dashboard/api/email.php?action=send     {subject, body, recipients}
GET  /dashboard/api/settings.php?action=get
POST /dashboard/api/settings.php?action=save  {key, value}
POST /dashboard/api/quotes.php?action=delete  {id}
POST /dashboard/api/quotes.php?action=bulk_delete  {ids: [...]}
```

---

## Ch7. 네비게이션 시스템

### 2모드 네비 (includes/nav.php)

| 모드 | 동작 | 설정값 |
|------|------|--------|
| Simple | 제품 클릭 → 바로 이동 | `nav_default_mode = 'simple'` |
| Detailed | 호버 → 서브메뉴 메가 패널 | `nav_default_mode = 'detailed'` |

#### 모드 전환

```javascript
toggleNavMode()
  → DB: site_settings.nav_default_mode 업데이트
  → 쿠키: nav_mode (오버라이드, 개인 설정)
```

#### 메가 패널 데이터 소스

- **DB**: `mlangprintauto_transactioncate` (BigNo/TreeNo 계층)
- BigNo=0 → 최상위 종류, BigNo=N → 하위 재질

### 사이드바 (includes/sidebar.php)

- **위치**: 우측 플로팅 메뉴 (5개 패널)
- **패널**: 고객센터, 파일전송, 업무안내, 입금안내, 운영시간
- **카카오톡**: `/TALK.svg` 벡터 아이콘

#### 사이드바 JS 동작

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

// 클릭: pinned 토글 (고정)
item.addEventListener('click', function() {
    this.classList.toggle('pinned');
});
```

### 인증 헤더 (includes/header.php)

- `$_SESSION['user_id']` 확인 → 로그인/회원가입 또는 마이페이지/로그아웃 분기
- **장바구니 카운트**: `SELECT COUNT(*) FROM shop_temp WHERE session_id=?`

### AI 챗봇 위젯 (includes/ai_chatbot_widget.php)

- **표시 조건**: 영업시간 외 (18:30~09:00)
- **API**: `POST /api/ai_chat.php?action=chat`
- **테마**: 보라색 (`#6366f1`) — 직원 채팅(주황색)과 배타적 전환
- **전환 로직**: `footer.php`의 `toggleWidgets()` (60초 간격)

---

## 부록. 주요 규칙 및 주의사항

### PHP 8.2 호환성

> ⚠️ `mysqli_close($db)` 후에 `$db`를 사용하면 PHP 8.2에서 Fatal Error 발생!

```php
// ❌ 금지: DB 닫은 뒤에 DB 사용하는 include
mysqli_close($db);
include 'quote_gauge.php';  // 내부에서 $db 사용 → Fatal Error

// ✅ 올바른 순서
include 'quote_gauge.php';  // $db 정상 사용
if (isset($db) && $db) { mysqli_close($db); }  // 페이지 끝에서 정리
```

### CSS !important 금지

```css
/* ❌ 절대 금지 */
.product-nav { display: grid !important; }

/* ✅ 명시도 계층으로 해결 */
.mobile-view .product-nav { display: grid; }
```

### FTP 배포 경로

```
✅ 올바름: /httpdocs/payment/inicis_return.php
❌ 틀림:   /payment/inicis_return.php
❌ 틀림:   /public_html/payment/inicis_return.php
```

### 스티커 가격 계산 주의

```
❌ 절대 금지: DB 가격표 조회로 스티커 가격 계산
✅ 올바름: calculateStickerPrice() 수학 공식 사용
```

---

*두손기획인쇄 기술 매뉴얼 v1.0 | 2026-02-22*
