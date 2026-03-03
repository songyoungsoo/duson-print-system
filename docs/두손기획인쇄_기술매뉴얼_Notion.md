# 두손기획인쇄 기술 매뉴얼 V2 — 듀얼 도메인 하이브리드 운영 체계

> **문서 기준일**: 2026-02-26 | **버전**: 2.1 | **분류**: 사내용 (비공개)
> **주 도메인**: dsp114.com | **보조 도메인**: dsp114.co.kr | **서버**: 175.119.156.249
> 
> 이 문서는 두손기획인쇄 인쇄 주문 시스템의 **프로그래머 납품용 기술 매뉴얼**입니다.
> "프로그래머가 제작 후 납품한다는 개념"으로, 코드 중심의 기술적 상세(JS 트리거, AJAX 엔드포인트, DB 테이블, 파일 경로)를 담습니다.
> 
> **V2 변경사항**: dsp114.com을 주 도메인으로, dsp114.co.kr을 보조 도메인으로 운영하는
> 듀얼 도메인 하이브리드 체계를 반영. 모든 URL은 `SITE_URL` 상수를 통해 접속 도메인에 따라 자동 전환됩니다.

---

## Ch0. 접속 정보

### 시스템 접속 정보

| 구분 | 접속 주소 | 아이디 | 비밀번호 | 비고 |
|------|----------|--------|---------|------|
| 홈페이지 (주) | https://dsp114.com | - | - | **주 도메인** |
| 홈페이지 (보조) | https://dsp114.co.kr | - | - | 보조 도메인 (동일 서버) |
| 관리자 대시보드 | https://dsp114.com/dashboard/ | admin | admin123 | 두 도메인 모두 접속 가능 |
| 데이터베이스 (MySQL) | localhost:3306 | dsp1830 | ds701018 (로컬) / t3zn?5R56 (프로덕션) | 환경별 자동 전환 |
| FTP (운영서버) | ftp://dsp114.co.kr | dsp1830 | cH*j@yzj093BeTtc | 서버 호스트명 |
| GitHub | github.com/songyoungsoo | songyoungsoo | yeongsu32@gmail.com | |
| 고객센터 전화 | 02-2632-1830 | - | - | |
| Plesk 관리 패널 | https://cmshom.co.kr:8443 | 두손기획 | h%42D9u2m | 서버/도메인/SSL 관리 |

### 서버 환경

- **주 도메인**: `dsp114.com` (2026-02-26 전환)
- **보조 도메인**: `dsp114.co.kr` (동일 서버, 동일 코드, 동일 DB)
- **서버 IP**: `175.119.156.249` (Plesk + nginx + PHP 8.2)
- **PHP**: 7.4+ (로컬) / 8.2 (프로덕션)
- **MySQL**: 5.7+
- **로컬 Document Root**: `/var/www/html`
- **프로덕션 FTP 웹 루트**: `/httpdocs/` ⚠️ 반드시 이 경로 사용!
- **도메인 자동 감지**: `config.env.php`의 `SITE_URL`/`SITE_DOMAIN` 상수 — 접속 도메인에 따라 자동 전환

### FTP 배포 예시

```bash
curl -T 로컬파일.php \
  ftp://dsp114.co.kr/httpdocs/경로/파일.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

---

## Ch0.5 듀얼 도메인 하이브리드 시스템 (V2 신규)

> ⚡ **이 챕터는 V2에서 새로 추가된 핵심 내용입니다.**
> dsp114.com(주)과 dsp114.co.kr(보조), 두 도메인이 하나의 서버에서 동시에 운영되는 구조를 설명합니다.

### 0.5.1 아키텍처 개요

```
┌────────────────────────────────────────────────────────────┐
│  고객 접속                                                  │
│                                                            │
│  dsp114.com (주 도메인) ──┐                                │
│                            ├──▶ 175.119.156.249 (Plesk)   │
│  dsp114.co.kr (보조) ─────┘    nginx + PHP 8.2            │
│                                 └── /httpdocs/ (웹루트)    │
│                                     └── config.env.php     │
│                                         ├ SITE_DOMAIN 감지 │
│                                         └ SITE_URL 생성    │
│                                                            │
│  localhost (개발) ──────────▶ Apache + PHP 7.4             │
│                                └── /var/www/html/          │
└────────────────────────────────────────────────────────────┘
```

**핵심 원리**: 두 도메인 모두 **같은 서버, 같은 코드, 같은 DB**를 바라봅니다.
`config.env.php`가 접속 도메인을 자동 감지하여 `SITE_URL`, `SITE_DOMAIN` 상수를 설정하고,
모든 파일에서 하드코딩 URL 대신 이 상수를 사용합니다.

### 0.5.2 도메인 자동 감지 메커니즘

**파일**: `config.env.php` (라인 227~245)

```php
// 접속 도메인 자동 감지
function get_site_domain() {
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    return strtolower(preg_replace('/:\d+$/', '', $host));
}

function get_site_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . get_site_domain();
}

// 전역 상수 — 모든 파일에서 사용 가능
define('SITE_DOMAIN', get_site_domain());
define('SITE_URL', get_site_url());
```

**동작 예시:**

| 접속 도메인 | SITE_DOMAIN | SITE_URL |
|------------|-------------|----------|
| dsp114.com | `dsp114.com` | `https://dsp114.com` |
| dsp114.co.kr | `dsp114.co.kr` | `https://dsp114.co.kr` |
| localhost | `localhost` | `http://localhost` |

### 0.5.3 환경 감지 (로컬 vs 프로덕션)

`config.env.php`의 `EnvironmentDetector` 클래스가 접속 환경을 자동 판별합니다:

```php
// 운영 환경으로 인식되는 도메인들
if (
    strpos($host, 'dsp114.co.kr') !== false ||
    strpos($host, 'dsp114.com') !== false ||
    strpos($host, 'dsp1830.shop') !== false
) {
    self::$environment = 'production';
}
```

- **로컬 환경**: `localhost`, `127.0.0.1`, XAMPP/WAMP 경로 감지 → DB: `ds701018`
- **프로덕션**: `dsp114.com`, `dsp114.co.kr` 등 → DB: `t3zn?5R56`

### 0.5.4 KG이니시스 결제 — 듀얼 도메인 대응

| 항목 | 값 | 비고 |
|------|-----|------|
| MID | `dsp1147479` | 사업자번호 귀속, 도메인 무관 |
| Sign Key | `cEdnbCtISFZ1QUNpNm5hbG1JY1RlQT09` | 두 도메인 공용 |
| returnUrl | `SITE_URL . "/payment/inicis_return.php"` | 접속 도메인 자동 감지 |
| closeUrl | `SITE_URL . "/payment/inicis_close.php"` | 접속 도메인 자동 감지 |

```
dsp114.com에서 결제 요청
  → returnUrl = https://dsp114.com/payment/inicis_return.php ✅

dsp114.co.kr에서 결제 요청
  → returnUrl = https://dsp114.co.kr/payment/inicis_return.php ✅

localhost에서 결제 요청 (테스트 모드)
  → returnUrl = http://localhost/payment/inicis_return.php ✅
```

> 이니시스 기술지원팀 확인: 같은 MID + Sign Key로 여러 도메인에서 결제 가능 (사업자번호 기준)

### 0.5.5 KB에스크로 — 도메인별 mHValue

KB에스크로는 **도메인별로 별도의 mHValue**가 등록되어 있습니다:

| 도메인 | mHValue | 등록일 | 적용 상태 |
|--------|---------|--------|----------|
| dsp114.com (주) | `eb30fbb0bc1da7fdcaf800c0bceebbff201111241043905` | 2011.11.24 | ✅ **현재 적용** |
| dsp114.co.kr (보조) | `ef04cec95f1a7298f1f686bfe3159ade` | 2026.02.06 | 주석에 보존 |

- **가맹점 코드(cc)**: `b034066:b035526` (양쪽 동일)
- **적용 파일**: `right.htm` (라인 111), `includes/footer.php` (라인 85)

**롤백 방법** (dsp114.co.kr 전용으로 되돌릴 때):
```bash
git checkout e6554898 -- right.htm includes/footer.php
```

> ⚠️ 도메인 전환 시 KB에스크로 mHValue도 반드시 교체해야 합니다. 인증마크 팝업이 도메인과 일치하지 않으면 정상 표시되지 않습니다.

### 0.5.6 하드코딩 → 동적 감지 변환 완료 파일 (11개)

| # | 파일 | 변경 내용 |
|---|------|----------|
| 1 | `config.env.php` | `SITE_URL`/`SITE_DOMAIN` 동적 감지 함수 추가 |
| 2 | `db.php` | 환경 감지 조건에 `dsp114.com` 추가 |
| 3 | `payment/inicis_config.production.php` | returnUrl/closeUrl에 `SITE_URL` 사용 |
| 4 | `payment/request.php` | returnUrl에 `SITE_URL` 사용 |
| 5 | `dashboard/api/email.php` | 이메일 본문 링크에 `SITE_URL` 사용 |
| 6 | `en/index.php` | 영문 사이트 baseUrl에 `SITE_URL` 사용 |
| 7 | `includes/quote_request_api.php` | 견적 링크에 `SITE_URL` 사용 |
| 8 | `includes/shipping_api.php` | 배송 알림 링크에 `SITE_URL` 사용 |
| 9 | `member/password_reset_simple_fixed.php` | 비밀번호 재설정 링크에 `SITE_URL` 사용 |
| 10 | `mlangorder_printauto/OrderComplete_universal.php` | 주문완료 URL에 `SITE_URL` 사용 |
| 11 | `mlangprintauto/shop/send_cart_quotation.php` | 장바구니 견적 링크에 `SITE_URL` 사용 |

### 0.5.7 새 파일 작성 시 규칙

```php
// ❌ 절대 금지: URL 하드코딩
$url = "https://dsp114.com/payment/inicis_return.php";
$link = "https://dsp114.co.kr/mypage/order_detail.php?no=" . $orderNo;

// ✅ 올바른 방법: SITE_URL 상수 사용
require_once __DIR__ . '/config.env.php';  // 또는 db.php (내부에서 config.env.php 로드)
$url = SITE_URL . "/payment/inicis_return.php";
$link = SITE_URL . "/mypage/order_detail.php?no=" . $orderNo;
```

```html
<!-- ❌ 절대 금지: HTML에 도메인 하드코딩 -->
<a href="https://dsp114.com/dashboard/">관리자</a>

<!-- ✅ 올바른 방법: PHP로 SITE_URL 삽입 -->
<a href="<?= SITE_URL ?>/dashboard/">관리자</a>
```

### 0.5.8 듀얼 도메인 체크리스트 (새 기능 추가 시)

| # | 확인 항목 | 방법 |
|---|----------|------|
| 1 | URL에 도메인 하드코딩 없는지? | `SITE_URL` 상수 사용 여부 확인 |
| 2 | 이메일 본문 링크가 동적인지? | 접속 도메인 기준으로 링크 생성 확인 |
| 3 | dsp114.com에서 정상 동작? | 브라우저에서 직접 테스트 |
| 4 | dsp114.co.kr에서도 동작? | 보조 도메인에서도 테스트 |
| 5 | localhost에서 개발 가능? | 로컬 환경 감지 정상 확인 |

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

> **접속**: https://dsp114.com/dashboard/ (ID: admin / PW: admin123)
> dsp114.co.kr/dashboard/ 로도 동일하게 접속 가능 (듀얼 도메인)
>
> 대시보드는 주문/교정/견적/회원/통계 등 모든 관리 기능을 하나의 인터페이스에서 제공합니다.
> 이 챕터에서는 각 페이지의 **기술 구조(파일, API, DB)**와 **사용법**을 상세히 설명합니다.

---

### 6.1 기술 스택 및 레이아웃

#### 프론트엔드 기술

| 기술 | CDN/버전 | 용도 |
|------|----------|------|
| Tailwind CSS | CDN (`cdn.tailwindcss.com`) | 전체 UI 스타일링 |
| Chart.js | CDN (`cdn.jsdelivr.net`) | 통계 차트 (일별추이, 품목비율, 매출) |
| Google Fonts | Noto Sans KR | 한국어 폰트 |

- **브랜드 컬러**: `#1E4E79` (사이드바 헤더, 카드 상단바, 버튼 등)
- **아이콘**: 이모지 기반 (별도 아이콘 라이브러리 미사용)

#### 레이아웃 구조

```
<div class="flex h-screen pt-11 overflow-hidden">
  <aside class="w-56 overflow-y-auto">    <!-- 사이드바: 독립 스크롤 -->
  <main class="flex-1 overflow-y-auto">   <!-- 메인 콘텐츠: 독립 스크롤 -->
</div>
```

- `h-screen overflow-hidden`: 뷰포트 고정 (전체 페이지 스크롤 없음)
- 사이드바와 메인 콘텐츠가 각각 독립적으로 스크롤
- 상단 헤더바 높이 `pt-11` (44px) 고정

#### 인증 체크 흐름

```
모든 대시보드 페이지 → includes/auth.php include
  → $_SESSION['admin_username'] 존재 여부 확인
  → 미인증 시 → /admin/mlangprintauto/login.php?redirect={현재URL} 리다이렉트
  → 인증 완료 → 페이지 렌더링 진행
```

- **세션 키**: `$_SESSION['admin_username']`
- **로그인 페이지**: `/admin/mlangprintauto/login.php`
- **리다이렉트**: 로그인 후 원래 요청 페이지로 자동 복귀

---

### 6.2 전체 파일 구조 (45개 PHP 파일)

```
dashboard/
├── index.php                         ← 메인 대시보드 (요약카드 + 차트 + 최근주문)
├── embed.php                         ← 레거시 관리자 iframe 임베드
│
├── includes/
│   ├── config.php                    ← $DASHBOARD_NAV (6그룹), $PRODUCT_TYPES (9제품)
│   ├── auth.php                      ← 인증 체크 ($_SESSION['admin_username'])
│   ├── header.php                    ← Tailwind CDN, Chart.js CDN, 상단바
│   ├── sidebar.php                   ← 사이드바 네비게이션 (카드형, 채팅 배지)
│   └── footer.php                    ← 공통 스크립트, 토스트 알림
│
├── api/                              ← 모든 API 엔드포인트 (16개 파일)
│   ├── base.php                      ← jsonResponse() 헬퍼, DB 연결, 인증
│   ├── orders.php                    ← 주문 list/view/update/delete/bulk_delete
│   ├── products.php                  ← 제품/카테고리 CRUD
│   ├── email.php                     ← 이메일 캠페인 (SMTP)
│   ├── gallery.php                   ← 갤러리 이미지 CRUD
│   ├── inquiries.php                 ← 고객 문의 CRUD
│   ├── members.php                   ← 회원 목록/상세/이메일오타
│   ├── payments.php                  ← 결제 현황 조회
│   ├── premium_options.php           ← 품목옵션 관리
│   ├── quotes.php                    ← 견적 CRUD + 일괄삭제
│   ├── settings.php                  ← 사이트 설정 get/save
│   ├── stats.php                     ← 주문 통계 (일별/월별/품목별)
│   ├── sticker.php                   ← 스티커 가격 관리
│   ├── admin-order.php               ← 관리자 주문 등록
│   └── visitor_stats.php             ← 방문자 분석
│
├── orders/
│   ├── index.php                     ← 주문 목록 (필터, 인라인 상태변경, 페이지네이션)
│   └── view.php                      ← 주문 상세 (규격파싱, 원고, 금액, 배송)
│
├── proofs/
│   ├── index.php                     ← 교정 관리 (이미지뷰어, 줌/팬, 업로드)
│   └── api.php                       ← 교정 파일 API (6개 action)
│
├── admin-order/index.php             ← 관리자 주문 등록
├── email/index.php                   ← 이메일 발송 (3탭: 작성/이력/템플릿)
├── chat/index.php                    ← 채팅 관리
├── quotes/index.php                  ← 견적 관리
├── inquiries/
│   ├── index.php                     ← 문의 목록
│   └── view.php                      ← 문의 상세/답변
├── members/
│   ├── index.php                     ← 회원 목록 (검색, 오타검사)
│   └── view.php                      ← 회원 상세
├── payments/index.php                ← 결제 현황
├── products/
│   ├── index.php                     ← 제품 관리
│   └── list.php                      ← 제품 목록
├── pricing/
│   ├── index.php                     ← 가격 관리 (3단 구조)
│   ├── edit.php                      ← 가격 수정
│   └── sticker.php                   ← 스티커 가격 전용
├── premium-options/index.php         ← 품목옵션 관리
├── gallery/index.php                 ← 갤러리 관리
├── stats/index.php                   ← 주문 통계 (3종 차트)
├── visitors/index.php                ← 방문자 분석
└── settings/index.php                ← 사이트 설정 (3가지 토글)
```

---

### 6.3 사이드바 네비게이션

#### 기술 구조

- **설정 파일**: `dashboard/includes/config.php` (104줄)
- **렌더링**: `dashboard/includes/sidebar.php` (245줄)
- **데이터**: `$DASHBOARD_NAV` 배열 → 6개 그룹으로 구성

#### $DASHBOARD_NAV 6개 그룹

| 그룹 ID | 표시명 | 메뉴 항목 (아이콘 포함) |
|---------|--------|----------------------|
| `main` | 메인 | 대시보드 |
| `order_group` | 주문/교정 | 관리자 주문, 주문 관리, 교정 관리, 교정 등록*, 결제 현황, 택배 관리*, 발송 목록* |
| `comm_group` | 소통/견적 | 이메일 발송, 채팅 관리, 견적 관리, 고객 문의 |
| `product_group` | 제품/가격 | 제품 관리, 가격 관리, 견적옵션*, 스티커수정, 갤러리 관리, 품목옵션 |
| `admin_group` | 관리/통계 | 회원 관리, 주문 통계, 방문자분석, 사이트 설정 |
| `legacy_group` | 기존 관리자 | 주문 관리(구)*, 교정 관리(구)* |

> `*` 표시: `embed.php`를 통한 레거시 iframe 임베드 메뉴

#### 채팅 배지 (실시간 미읽음 카운트)

```php
// sidebar.php — 채팅 메뉴 옆 빨간 배지
$unread_query = "SELECT COUNT(*) as cnt FROM chatmessages WHERE isread = 0";
// → <span class="bg-red-500 text-white rounded-full px-1.5 text-xs">{cnt}</span>
```

#### 활성 메뉴 하이라이트

```php
// sidebar.php — 현재 URL 기반 활성 메뉴 판별
$current_url = $_SERVER['REQUEST_URI'];
// 각 메뉴의 href와 비교 → 일치 시 bg-blue-50 + text-blue-700 클래스 적용
```

#### 사용법

1. 좌측 사이드바에서 원하는 그룹 클릭 → 메뉴 항목 펼침
2. 각 메뉴 클릭 → 해당 관리 페이지로 이동
3. 채팅 관리 메뉴에 빨간 배지 숫자 = 미읽음 채팅 수

---

### 6.4 메인 대시보드 (index.php)

#### 기술 구조

- **파일**: `dashboard/index.php` (329줄)
- **의존성**: Chart.js (일별 주문추이 차트)
- **DB 쿼리**: 요약카드 4종 + 일별 추이 7일 + 최근주문 5건

#### 화면 구성 (4개 영역)

**영역 1: 요약 카드 4개 (상단)**

| 카드 | DB 쿼리 | 표시 |
|------|---------|------|
| 오늘 주문 | `SELECT COUNT(*) FROM mlangorder_printauto WHERE DATE(regdate)=CURDATE()` | 건수 |
| 이번달 매출 | `SELECT SUM(money_5) FROM mlangorder_printauto WHERE MONTH(regdate)=MONTH(NOW())` | 금액 (만원) |
| 미확인 주문 | `SELECT COUNT(*) FROM mlangorder_printauto WHERE OrderStyle IN ('0','1','2')` | 건수 |
| 전체 회원수 | `SELECT COUNT(*) FROM users WHERE is_admin=0` | 명 |

- 카운트업 애니메이션: `animateNumber(el, target, 800, isCurrency)` (easeOutExpo)

**영역 2: 일별 주문추이 차트**

```javascript
// Chart.js 라인 차트 (최근 7일)
new Chart(ctx, {
  type: 'line',
  data: {
    labels: ['2/15', '2/16', ...],      // PHP에서 생성
    datasets: [{
      label: '주문 수',
      data: [3, 5, 2, ...],             // DB 집계
      borderColor: '#1E4E79',
    }]
  }
});
```

**영역 3: 퀵 액션 버튼 4개**

| 버튼 | 링크 |
|------|------|
| 주문 등록 | `/dashboard/admin-order/` |
| 교정 관리 | `/dashboard/proofs/` |
| 이메일 발송 | `/dashboard/email/` |
| 견적 작성 | `/admin/mlangprintauto/quote/create.php` |

**영역 4: 최근 주문 5건 테이블**

```sql
SELECT no, name, Pname, money_5, OrderStyle, regdate
FROM mlangorder_printauto
WHERE OrderStyle != 'deleted'
ORDER BY no DESC LIMIT 5
```

#### 사용법

1. 대시보드 접속 시 **자동으로 오늘 현황** 표시
2. 요약 카드 클릭 → 각 관리 페이지로 이동
3. 퀵 액션 버튼으로 자주 쓰는 기능 바로 접근
4. 최근 주문 행 클릭 → 주문 상세 페이지로 이동

---

### 6.5 주문 관리 — 목록 (orders/index.php)

#### 기술 구조

- **파일**: `dashboard/orders/index.php` (574줄)
- **API**: `dashboard/api/orders.php?action=list`
- **기능**: 필터 4종, 인라인 상태변경, 일괄삭제, 페이지네이션

#### 필터 4종

| 필터 | 타입 | 파라미터 |
|------|------|---------|
| 기간 | date range | `from`, `to` |
| 상태 | select | `status` (OrderStyle 값) |
| 품목 | select | `product` (9개 제품) |
| 검색 | text | `search` (주문번호/이름/연락처) |

#### 인라인 상태 변경

```javascript
// 주문 목록에서 직접 상태 드롭다운 변경
document.querySelectorAll('.order-status-select').forEach(select => {
  select.addEventListener('change', function() {
    const orderId = this.dataset.orderId;
    const newStatus = this.value;
    fetch('/dashboard/api/orders.php', {
      method: 'POST',
      body: JSON.stringify({ action: 'update', id: orderId, OrderStyle: newStatus })
    });
    // 성공 시 행 배경색 flash → 복원
  });
});
```

#### 일괄 삭제

```javascript
// 체크박스 선택 → "선택 삭제" 버튼
function bulkDelete() {
  const ids = [...document.querySelectorAll('.order-checkbox:checked')]
    .map(cb => cb.value);
  fetch('/dashboard/api/orders.php', {
    method: 'POST',
    body: JSON.stringify({ action: 'bulk_delete', ids })
  });
}
```

#### 배송 컬럼

목록 테이블에 배송방법/운임구분/택배비 표시:
- 택배 선불 시: `선불 5,000원` 형태
- 착불/직접수령: 해당 텍스트만 표시

#### API 응답 형식

```
GET /dashboard/api/orders.php?action=list&page=1&status=3&search=홍길동
응답: { success: true, data: { orders: [...], total: 45, page: 1, per_page: 20 } }
```

#### 사용법

1. **필터 설정**: 상단 필터바에서 기간/상태/품목/검색어 조합
2. **상태 변경**: 각 행의 드롭다운에서 직접 상태 선택 (저장 자동)
3. **상세 보기**: 주문번호 클릭 → `orders/view.php` 이동
4. **일괄 삭제**: 행 앞 체크박스 선택 → 하단 "선택 삭제" 클릭
5. **페이지 이동**: 하단 `총 N건 / X/Y 페이지` 네비게이션

---

### 6.6 주문 관리 — 상세 (orders/view.php)

#### 기술 구조

- **파일**: `dashboard/orders/view.php` (622줄)
- **API**: `dashboard/api/orders.php?action=view&id={no}`

#### 화면 구성 (6개 카드)

| 카드 | 내용 |
|------|------|
| 주문 정보 | 주문번호, 품목, 주문일시, 상태 드롭다운 |
| 제품 규격 | Type_1 파싱 결과 (종류/재질/수량/인쇄면 등) |
| 주문자 정보 | 이름, 전화, 이메일, 주소 |
| 금액 정보 | 공급가액, VAT, 합계, 택배비(선불 시) |
| 배송 정보 | 배송방법, 운임구분, 택배비, 송장번호 |
| 원고 파일 | 업로드된 파일 목록 + 이미지 미리보기 |

#### Type_1 필드 파싱 (3가지 형식)

주문의 `Type_1` 컬럼에는 제품 옵션이 저장되며, 3가지 형식이 혼재합니다:

**형식 1: JSON v2** (최신)
```json
{"product_type":"namecard","style":"일반명함 86x50","section":"소프트코팅","quantity":"500","potype":"양면인쇄"}
```

**형식 2: 파이프 구분** (레거시)
```
일반명함 86x50|소프트코팅|양면인쇄|500
```

**형식 3: 키:값 줄바꿈** (구형)
```
종류: 일반명함 86x50
재질: 소프트코팅
인쇄: 양면인쇄
수량: 500
```

```php
// view.php — Type_1 파싱 로직
$type1 = $order['Type_1'];
if (json_decode($type1)) {
    // JSON v2 파싱
} elseif (strpos($type1, '|') !== false) {
    // 파이프 구분 파싱
} else {
    // 키:값 줄바꿈 파싱
}
```

#### 품목별 규격 라벨 매핑

| 품목 | 라벨 순서 |
|------|----------|
| 명함 | 종류 → 재질 → 인쇄면 → 수량 |
| 전단지 | 규격 → 용지 → 인쇄도수 → 수량 |
| 스티커 | 재질 → 가로 → 세로 → 수량 → 모양 |
| 봉투 | 종류 → 재질 → 인쇄면 → 수량 |
| 포스터 | 규격 → 용지 → 인쇄도수 → 수량 |
| 카다록 | 종류 → 용지 → 페이지수 → 수량 |
| NCR양식지 | 구분 → 규격 → 색상 → 수량 |

#### 택배비 VAT 계산

```php
// 선불 택배비의 공급가액 + VAT 10% 합산 표시
$shipping_supply = $logen_delivery_fee;              // 공급가액
$shipping_vat = round($shipping_supply * 0.1);       // VAT
$shipping_total = $shipping_supply + $shipping_vat;   // 합계
// 표시: "5,000+VAT 500 = 5,500원"
```

#### 입금자명 불일치 강조

주문자명과 입금자명이 다를 경우 **적색 배경 + 흰색 글씨**로 강조:
```php
if ($order['name'] !== $order['bankname'] && !empty($order['bankname'])) {
    echo '<span class="bg-red-600 text-white px-2 py-0.5 rounded">'
       . htmlspecialchars($order['bankname']) . '</span>';
}
```

#### 상태 변경 (드롭다운)

```javascript
// 상태 드롭다운 변경 → API POST
document.getElementById('orderStatus').addEventListener('change', function() {
  fetch('/dashboard/api/orders.php', {
    method: 'POST',
    body: JSON.stringify({
      action: 'update',
      id: orderId,
      OrderStyle: this.value
    })
  }).then(() => showToast('상태가 변경되었습니다.'));
});
```

#### 사용법

1. 주문 목록에서 **주문번호 클릭** → 상세 페이지 열림
2. **상태 변경**: 상단 드롭다운에서 원하는 상태 선택 (자동 저장)
3. **제품 규격**: Type_1 파싱 결과가 라벨과 함께 표 형태로 표시
4. **원고 파일**: 이미지는 썸네일로, 비이미지는 파일명으로 표시 (클릭 시 다운로드)
5. **택배비**: 선불인 경우 공급가액+VAT 계산 자동 표시

---

### 6.7 교정 관리 (proofs/index.php)

#### 기술 구조

- **파일**: `dashboard/proofs/index.php` (1,295줄 — 대시보드 최대 파일)
- **API**: `dashboard/proofs/api.php` (6개 action)
- **교정 파일 경로**: `/mlangorder_printauto/upload/{주문번호}/`

#### 화면 구성

| 영역 | 기능 |
|------|------|
| 주문 목록 테이블 | 주문번호, 품목, 주문자, 교정상태, 파일수, 액션 |
| 이미지 뷰어 오버레이 | 풀스크린 이미지 원본 보기, 줌/팬 |
| 파일 업로드 폼 | 드래그앤드롭 + 파일선택, 다중파일 지원 |
| 교정 확정 버튼 | 교정 완료 처리 (OrderStyle 변경) |

#### 이미지 뷰어 (Windows Photo Viewer 스타일)

```javascript
// 풀스크린 오버레이 + 줌/팬
function openViewer(images, startIndex) {
  // fixed overlay (z-index: 9999)
  // 배경 클릭 = 닫기
  // ESC = 닫기
  // ← → 방향키 = 이전/다음
}

// 마우스 휠 줌
viewer.addEventListener('wheel', function(e) {
  e.preventDefault();
  const delta = e.deltaY > 0 ? -0.1 : 0.1;
  currentZoom = Math.max(0.1, Math.min(5, currentZoom + delta));
  img.style.transform = `scale(${currentZoom}) translate(${panX}px, ${panY}px)`;
});

// 마우스 드래그 팬
viewer.addEventListener('mousedown', startDrag);
viewer.addEventListener('mousemove', doDrag);
viewer.addEventListener('mouseup', stopDrag);
```

- **줌 범위**: 10% ~ 500%
- **팬**: 마우스 드래그로 이미지 이동
- **썸네일바**: 하단에 모든 이미지 썸네일 가로 배치, 클릭 시 전환
- **카운터**: `1 / 5` 형태로 현재 위치 표시

#### 교정 파일 API (proofs/api.php)

| action | 메서드 | 파라미터 | 설명 |
|--------|--------|---------|------|
| `files` | GET | `order_no` | 해당 주문의 교정파일 목록 |
| `upload` | POST | `order_no`, `files[]` | 교정파일 업로드 (다중) |
| `delete_file` | POST | `order_no`, `filename` | 개별 파일 삭제 |
| `save_phone` | POST | `order_no`, `phone` | 연락처 수정 |
| `check_proof_status` | GET | `order_no` | 교정 상태 확인 |
| `confirm_proofreading` | POST | `order_no` | 교정 확정 (OrderStyle 변경) |

#### 파일 업로드 동작

```javascript
// 드래그앤드롭 업로드
const dropzone = document.querySelector('.upload-dropzone');
dropzone.addEventListener('drop', function(e) {
  e.preventDefault();
  const files = e.dataTransfer.files;
  const formData = new FormData();
  formData.append('action', 'upload');
  formData.append('order_no', orderNo);
  for (let f of files) formData.append('files[]', f);

  fetch('/dashboard/proofs/api.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) refreshFileList(orderNo);
    });
});
```

- **허용 형식**: jpg, jpeg, png, gif, pdf, ai, psd, zip
- **최대 크기**: 20MB/파일
- **파일명**: `{YYYYMMDD}_{랜덤hex}.{확장자}` 자동 생성

#### 교정 확정 처리

```javascript
function confirmProofreading(orderNo) {
  if (!confirm('교정을 확정하시겠습니까?')) return;
  fetch('/dashboard/proofs/api.php', {
    method: 'POST',
    body: JSON.stringify({ action: 'confirm_proofreading', order_no: orderNo })
  });
  // → OrderStyle 변경 (교정 → 작업중)
}
```

#### 사용법

1. **교정 목록**: 주문 목록에서 교정이 필요한 주문 확인 (교정상태 컬럼)
2. **파일 보기**: "보기" 버튼 클릭 → 이미지 뷰어 오버레이 열림
3. **이미지 조작**: 마우스 휠로 줌, 드래그로 팬, 방향키로 이전/다음
4. **파일 업로드**: 드래그앤드롭 또는 파일선택으로 교정파일 추가
5. **교정 확정**: 교정 완료 시 "교정확정" 버튼 → 주문 상태 자동 변경

---

### 6.8 관리자 주문 등록 (admin-order/index.php)

#### 기술 구조

- **파일**: `dashboard/admin-order/index.php` (808줄)
- **API**: `dashboard/api/admin-order.php`
- **용도**: 전화/비회원 주문을 관리자가 직접 등록

#### 화면 구성

| 영역 | 내용 |
|------|------|
| 품목 선택 | 9개 제품 드롭다운 → 카테고리 자동 로드 |
| 옵션 입력 | 품목별 cascade (종류→재질→수량) |
| 수동 품목 추가 | 자유 텍스트로 품목명+가격 직접 입력 |
| 주문자 정보 | 이름, 전화, 이메일, 주소 |
| 가격 입력 | 공급가액 입력 → VAT 자동 계산 (x1.1) |
| 배송/결제 | 배송방법, 결제방법, 택배 선불 지원 |

#### 품목 추가 JS

```javascript
// 품목 선택 → 계산기 AJAX 연동
function loadCategories(productType) {
  fetch(`/mlangprintauto/${productType}/get_paper_types.php?style=...`)
    .then(r => r.json())
    .then(data => populateSelect('category-select', data));
}

// 수동 품목 추가 (자유 텍스트)
function addManualItem() {
  const name = document.getElementById('manual-name').value;
  const price = document.getElementById('manual-price').value;
  appendToItemList({ name, price, type: 'manual' });
}
```

#### 택배비 선불 입력

```javascript
// 배송방법 "택배" 선택 시 운임구분 라디오 표시
document.getElementById('delivery_method').addEventListener('change', function() {
  if (this.value === '택배') {
    document.getElementById('fee-type-section').style.display = 'block';
  }
});

// "선불" 선택 시 택배비 금액 입력란 표시
document.querySelector('input[name="fee_type"][value="선불"]').addEventListener('change', function() {
  document.getElementById('delivery-fee-input').style.display = 'block';
});
```

#### DB 저장

```
POST /dashboard/api/admin-order.php
Body: { name, phone, email, address, items[], delivery_method, payment_method,
        fee_type, delivery_fee, memo }
→ INSERT INTO mlangorder_printauto (관리자 주문으로 표시)
```

#### 사용법

1. **품목 선택**: 드롭다운에서 제품 선택 → 종류/재질/수량 cascade 자동 로드
2. **수동 추가**: "수동 품목 추가" 버튼 → 품목명과 가격 직접 입력
3. **주문자 정보**: 이름/전화/이메일/주소 입력
4. **가격**: 공급가액 입력 시 VAT(10%) 자동 계산 표시
5. **배송**: 택배 선택 → 착불/선불 선택 → 선불 시 택배비 입력
6. **등록**: "주문 등록" 클릭 → DB 저장 + 주문 목록으로 이동

---

### 6.9 이메일 발송 (email/index.php)

#### 기술 구조

- **파일**: `dashboard/email/index.php` (1,347줄)
- **API**: `dashboard/api/email.php` (12개 action)
- **SMTP**: 네이버 (`smtp.naver.com:465`, dsp1830@naver.com)

#### 3탭 구조

| 탭 | 기능 |
|----|------|
| 작성 | 수신자 선택, 제목/본문 편집, 테스트/발송 |
| 이력 | 발송 캠페인 목록, 상태(발송중/완료), 성공/실패 카운트 |
| 템플릿 | 저장된 이메일 템플릿 목록, 불러오기/삭제 |

#### 수신자 필터 3종

| 필터 | 설명 |
|------|------|
| 전체 회원 | `users` 테이블에서 admin/test/봇 제외 |
| 조건 필터 | 최근 로그인 기간 + 이메일 도메인 필터 |
| 직접 입력 | 쉼표 구분 이메일 주소 직접 입력 |

#### WYSIWYG 에디터

3가지 편집 모드:
- **편집기** (기본): `contenteditable` div + 서식 도구모음 (B, I, U, H1, H2, 링크, 이미지, 목록, 색상)
- **HTML편집**: raw textarea (고급 사용자용)
- **미리보기**: 렌더링된 HTML 확인

```javascript
// 모드 전환 시 콘텐츠 동기화
function switchMode(mode) {
  if (mode === 'html') {
    document.getElementById('email-body').value =
      document.getElementById('wysiwyg-editor').innerHTML;
  } else if (mode === 'wysiwyg') {
    document.getElementById('wysiwyg-editor').innerHTML =
      document.getElementById('email-body').value;
  }
}
```

#### 이미지 업로드

```javascript
// 에디터 내 이미지 삽입
function uploadImage(file) {
  const formData = new FormData();
  formData.append('action', 'upload_image');
  formData.append('image', file);
  fetch('/dashboard/api/email.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      // 업로드된 이미지 URL을 에디터에 삽입
      document.execCommand('insertImage', false, data.url);
    });
}
// 저장 경로: /dashboard/email/uploads/
// 제한: 5MB, JPG/PNG/GIF/WebP
```

#### 발송 흐름

```
1. "이메일 발송" 클릭
   → action=send: email_campaigns INSERT + email_send_log INSERT (수신자별)
2. 배치 발송 시작
   → action=send_batch: 100명씩 mailer() 호출
   → 3초 대기 → 다음 배치
3. 전체 완료
   → campaign status = 'completed'
```

#### {{name}} 치환

이메일 본문에서 `{{name}}`은 수신자 이름으로 자동 치환됩니다. 이름이 없으면 "고객"으로 표시.

#### 네이버 SMTP 제한

| 항목 | 제한값 |
|------|--------|
| 1회 최대 | 100명 |
| 일일 한도 | 약 500통 |
| 배치 간격 | 3초 대기 |
| Gmail 수신 | 스팸 분류 가능성 있음 |

#### 사용법

1. **수신자 설정**: "전체 회원" 또는 조건 필터 / 직접 입력
2. **제목 입력**: 이메일 제목 작성
3. **본문 편집**: 편집기 도구모음 사용 또는 HTML 직접 편집
4. **이미지 삽입**: 도구모음 이미지 버튼 → 파일 선택 → 자동 업로드+삽입
5. **테스트 발송**: "테스트" 버튼 → dsp1830@naver.com으로 미리보기 발송
6. **실제 발송**: "발송" 버튼 → 확인 후 100명씩 배치 발송 시작
7. **이력 확인**: "이력" 탭에서 발송 상태/성공률 확인

---

### 6.10 회원 관리 (members/index.php)

#### 기술 구조

- **파일**: `dashboard/members/index.php` (336줄)
- **API**: `dashboard/api/members.php`
- **DB**: `users` 테이블

#### 기능

| 기능 | 설명 |
|------|------|
| 회원 목록 | 이름, 이메일, 전화, 가입일, 최근로그인 |
| 검색 | 이름/이메일/전화번호로 실시간 검색 |
| 이메일 오타 검사 | `action=scan_typos` — nate.ocm, naver.vom 등 오타 자동 감지 |
| 페이지네이션 | 20명/페이지 |

#### 이메일 오타 검사 (scan_typos)

```php
// api/members.php — 흔한 오타 패턴 검사
$typo_patterns = [
    'naver.vom', 'naver.coml', 'naver.co.kr',  // naver.com 오타
    'nate.ocm', 'nate.co.kr',                   // nate.com 오타
    'gmail.co', 'gamil.com',                     // gmail.com 오타
    'hanmail.com',                               // hanmail.net 오타
];
```

#### 사용법

1. **회원 목록**: 가입일/최근로그인 순 정렬
2. **검색**: 상단 검색바에 이름/이메일/전화 입력 → 실시간 필터
3. **오타 검사**: "이메일 오타 검사" 버튼 → 문제 이메일 목록 표시
4. **상세 보기**: 회원 행 클릭 → `members/view.php` 이동 (가입정보, 주문내역, 사업자정보)

---

### 6.11 견적 관리 (quotes/index.php)

#### 기술 구조

- **파일**: `dashboard/quotes/index.php`
- **API**: `dashboard/api/quotes.php`
- **DB**: `admin_quotes` + `admin_quote_items`

#### 견적서 상태 흐름

```
draft (임시저장) → sent (발송) → viewed (열람) → accepted/rejected (승인/거절)
```

#### 주요 기능

| 기능 | 동작 |
|------|------|
| 견적 목록 | 번호, 고객명, 금액, 상태, 생성일 |
| 새 견적 | 팝업 창으로 create.php 열림 |
| 수정 | 팝업 창으로 edit.php 열림 |
| 미리보기 | 팝업 창으로 preview.php 열림 (인쇄용) |
| 이메일 발송 | 견적서 PDF 첨부 이메일 발송 → 상태 `sent` 변경 |
| 삭제 | 개별 삭제 + 일괄 삭제 (체크박스) |

#### 견적번호 체계

| 접두어 | 형식 | 예시 |
|--------|------|------|
| AQ | `AQ-YYYYMMDD-NNNN` | AQ-20260208-0004 |

#### 팝업 창 동작

```javascript
// 견적 상세/수정/미리보기 → 팝업 창으로 열림
function openQuotePopup(url) {
  window.open(url, 'quotePopup', 'width=960,height=' + (screen.height * 0.92));
}
// 페이지 로드 후 콘텐츠 높이 측정 → 자동 리사이즈 + 화면 중앙 배치
```

#### 사용법

1. **견적 목록**: 상태별 필터 (전체/임시/발송/열람/승인/거절)
2. **새 견적**: "새 견적" 버튼 → 팝업에서 고객정보+품목+금액 입력
3. **발송**: "발송" 버튼 → 고객 이메일로 견적서 PDF 첨부 발송
4. **삭제**: 개별 "삭제" 링크 또는 체크박스 → "선택 삭제"

---

### 6.12 주문 통계 (stats/index.php)

#### 기술 구조

- **파일**: `dashboard/stats/index.php` (393줄)
- **API**: `dashboard/api/stats.php`
- **차트**: Chart.js (3종 차트)

#### 3종 차트

| 차트 | 유형 | 데이터 |
|------|------|--------|
| 일별 주문추이 | Line | 최근 30일 일별 주문 건수/금액 |
| 품목별 비율 | Doughnut | 9개 제품별 주문 비율 (%) |
| 월별 매출 | Bar | 최근 12개월 월별 총 매출액 |

#### API 엔드포인트

```
GET /dashboard/api/stats.php?action=daily&days=30
→ { labels: ['2/1', '2/2', ...], orders: [3, 5, ...], revenue: [150000, ...] }

GET /dashboard/api/stats.php?action=products
→ { labels: ['스티커', '전단지', ...], data: [45, 32, ...] }

GET /dashboard/api/stats.php?action=monthly&months=12
→ { labels: ['3월', '4월', ...], data: [2500000, ...] }
```

#### 카운트업 애니메이션

```javascript
function animateNumber(el, target, duration, isCurrency) {
  const start = performance.now();
  function update(now) {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(2, -10 * progress);  // easeOutExpo
    const current = Math.round(target * eased);
    el.textContent = isCurrency ? formatCurrency(current) : current.toLocaleString();
    if (progress < 1) requestAnimationFrame(update);
  }
  requestAnimationFrame(update);
}
```

#### 사용법

1. **일별 추이**: 상단 기간 선택 (7일/30일/90일) → 차트 자동 갱신
2. **품목 비율**: 도넛 차트에서 각 품목 호버 → 비율/건수 표시
3. **월별 매출**: 막대 차트에서 월별 매출 비교

---

### 6.13 방문자 분석 (visitors/index.php)

#### 기술 구조

- **파일**: `dashboard/visitors/index.php`
- **API**: `dashboard/api/visitor_stats.php`

#### 주요 기능

| 기능 | 설명 |
|------|------|
| 실시간 방문자 | 현재 접속중인 방문자 IP/UA/페이지 |
| 인기 페이지 | 방문 횟수 상위 페이지 (한글화 표시) |
| 진입/이탈 페이지 | 첫 방문 페이지, 마지막 페이지 |
| 시간대별 분포 | 시간대별 방문 건수 |

#### URL 한글화 매핑

```javascript
// visitors/index.php — 30개 경로 → 한글 매핑
const PAGE_NAME_MAP = {
  '/mlangprintauto/sticker_new/index.php': '스티커',
  '/mlangprintauto/inserted/index.php': '전단지',
  '/mlangprintauto/namecard/index.php': '명함',
  // ... 30개 정확 매칭
};
const PAGE_PATH_PATTERNS = {
  '/mlangprintauto/sticker_new/': '스티커',
  '/member/login': '로그인',
  // ... 17개 부분 매칭
};
function getPageName(url) {
  return PAGE_NAME_MAP[url] || findPattern(url) || url;
}
```

#### 사용법

1. **실시간 탭**: 현재 접속자 목록 (자동 갱신)
2. **인기 페이지**: 기간 선택 → 방문 TOP 10 페이지 (한글명 + 파란색 링크)
3. **시간대 분포**: 0~23시 히스토그램 차트

---

### 6.14 사이트 설정 (settings/index.php)

#### 기술 구조

- **파일**: `dashboard/settings/index.php` (296줄)
- **API**: `dashboard/api/settings.php`
- **DB**: `site_settings` 테이블 (key-value 구조)

#### 3가지 토글 설정

| 설정키 | 기본값 | 설명 |
|--------|--------|------|
| `nav_default_mode` | `simple` | 네비 모드: simple(바로이동) / detailed(메가메뉴) |
| `en_version_enabled` | `0` | 영문 버전 표시: 0=한국어만, 1=한국어+영어 |
| `quote_widget_enabled` | `1` | 플로팅 견적 위젯: 0=끔, 1=켬 |

#### API 패턴

```
GET  /dashboard/api/settings.php?action=get
→ { success: true, data: { nav_default_mode: 'simple', en_version_enabled: '1', ... } }

POST /dashboard/api/settings.php?action=save
Body: { key: 'en_version_enabled', value: '1' }
→ { success: true, message: '설정이 저장되었습니다.' }
```

#### 사용법

1. **네비 모드**: Simple/Detailed 라디오 선택 → 즉시 저장 + 홈페이지 반영
2. **영문 버전**: ON 토글 → 홈페이지 헤더에 EN 버튼 표시
3. **견적 위젯**: ON/OFF 토글 → 홈페이지 하단 플로팅 견적 위젯 표시/숨김

---

### 6.15 결제 현황 (payments/index.php)

#### 기술 구조

- **파일**: `dashboard/payments/index.php`
- **API**: `dashboard/api/payments.php`
- **DB**: `payment_inicis` 테이블 (KG이니시스 결제 기록)

#### 결제 목록 컬럼

| 컬럼 | 내용 |
|------|------|
| 주문번호 | 연결된 주문번호 (클릭 시 주문 상세) |
| 거래번호 | 이니시스 TID |
| 결제금액 | VAT 포함 금액 |
| 결제수단 | 카드/무통장 |
| 결제일시 | 결제 완료 시각 |
| 상태 | 성공/실패/취소 |

#### 사용법

1. **결제 목록**: 기간 필터 + 상태 필터
2. **페이지네이션**: 하단 `총 N건 / X/Y 페이지`
3. **주문 연결**: 주문번호 클릭 → 주문 상세 페이지

---

### 6.16 API 패턴 총정리

#### 공통 구조

모든 대시보드 API는 동일한 패턴을 따릅니다:

```php
// api/base.php — 공통 헬퍼
require_once __DIR__ . '/base.php';  // DB 연결 + 인증 + jsonResponse()

function jsonResponse(bool $success, string $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
```

- **인증**: 모든 API는 `auth.php`를 통해 세션 검증
- **에러 처리**: `jsonResponse(false, '에러메시지')` 형식
- **분기**: `$_GET['action']` 또는 `$_POST['action']` 파라미터

#### 전체 API 엔드포인트 목록

| 파일 | action | 메서드 | 파라미터 | 설명 |
|------|--------|--------|---------|------|
| orders.php | list | GET | page, status, product, search, from, to | 주문 목록 |
| orders.php | view | GET | id | 주문 상세 |
| orders.php | update | POST | id, OrderStyle | 상태 변경 |
| orders.php | delete | POST | id | 주문 삭제 |
| orders.php | bulk_delete | POST | ids[] | 일괄 삭제 |
| products.php | category_list | GET | product, style | 카테고리 목록 |
| products.php | category_create | POST | code, name, desc | 카테고리 추가 |
| products.php | category_update | POST | id, name, desc | 카테고리 수정 |
| products.php | category_delete | POST | id | 카테고리 삭제 |
| email.php | get_recipients | GET | filter, domain | 수신자 목록 |
| email.php | send | POST | subject, body, recipients | 발송 시작 |
| email.php | send_batch | POST | campaign_id | 배치 발송 |
| email.php | send_test | POST | subject, body | 테스트 발송 |
| email.php | save_draft | POST | subject, body | 임시저장 |
| email.php | campaigns | GET | page | 캠페인 이력 |
| email.php | campaign_detail | GET | id | 캠페인 상세 |
| email.php | templates | GET | - | 템플릿 목록 |
| email.php | load_template | GET | id | 템플릿 불러오기 |
| email.php | save_template | POST | name, subject, body | 템플릿 저장 |
| email.php | delete_template | POST | id | 템플릿 삭제 |
| email.php | upload_image | POST | image (file) | 이미지 업로드 |
| members.php | list | GET | page, search | 회원 목록 |
| members.php | view | GET | id | 회원 상세 |
| members.php | scan_typos | GET | - | 이메일 오타검사 |
| stats.php | daily | GET | days | 일별 통계 |
| stats.php | products | GET | - | 품목별 비율 |
| stats.php | monthly | GET | months | 월별 매출 |
| settings.php | get | GET | - | 전체 설정 조회 |
| settings.php | save | POST | key, value | 설정 저장 |
| quotes.php | list | GET | page, status | 견적 목록 |
| quotes.php | delete | POST | id | 견적 삭제 |
| quotes.php | bulk_delete | POST | ids[] | 일괄 삭제 |
| payments.php | list | GET | page, from, to, status | 결제 목록 |
| admin-order.php | save | POST | (주문 데이터) | 관리자 주문 등록 |
| visitor_stats.php | realtime | GET | - | 실시간 방문자 |
| visitor_stats.php | pages | GET | from, to | 인기 페이지 |
| proofs/api.php | files | GET | order_no | 교정파일 목록 |
| proofs/api.php | upload | POST | order_no, files[] | 교정파일 업로드 |
| proofs/api.php | delete_file | POST | order_no, filename | 파일 삭제 |
| proofs/api.php | save_phone | POST | order_no, phone | 연락처 수정 |
| proofs/api.php | check_proof_status | GET | order_no | 교정 상태 확인 |
| proofs/api.php | confirm_proofreading | POST | order_no | 교정 확정 |

---

### 6.17 레거시 임베드 (embed.php)

#### 기술 구조

- **파일**: `dashboard/embed.php`
- **방식**: `<iframe>` 태그로 기존 관리자 페이지를 대시보드 안에 임베드

#### 임베드 대상 페이지

| 사이드바 메뉴 | 임베드 URL | 원본 위치 |
|-------------|-----------|----------|
| 교정 등록 | `embed.php?url=/admin/mlangprintauto/admin.php?mode=sian` | 교정시안 등록 (구) |
| 택배 관리 | `embed.php?url=/shop_admin/post_list74.php` | 로젠택배 관리 |
| 발송 목록 | `embed.php?url=/shop_admin/post_list.php` | 발송 목록 |
| 견적옵션 | `embed.php?url=/admin/mlangprintauto/option_prices.php` | 옵션 가격 관리 |
| 주문 관리(구) | `embed.php?url=/admin/mlangprintauto/admin.php` | 레거시 주문 관리 |
| 교정 관리(구) | `embed.php?url=/admin/mlangprintauto/admin.php?mode=sian` | 레거시 교정 관리 |

```php
// embed.php — iframe 임베드 구조
$url = $_GET['url'] ?? '';
// 보안: 허용된 URL 패턴만 임베드
?>
<div class="flex-1 overflow-hidden">
  <iframe src="<?= htmlspecialchars($url) ?>"
          class="w-full h-full border-0"
          sandbox="allow-same-origin allow-scripts allow-forms">
  </iframe>
</div>
```

#### 사용법

1. 사이드바에서 `(구)` 또는 `*` 표시된 메뉴 클릭
2. 대시보드 레이아웃 안에서 기존 관리자 페이지가 iframe으로 로드
3. iframe 내에서 기존 관리자 기능 그대로 사용 가능

---

### 6.18 가격 관리 (pricing/index.php)

#### 기술 구조

- **파일**: `dashboard/pricing/index.php`, `edit.php`, `sticker.php`
- **API**: `dashboard/api/products.php`
- **DB**: `mlangprintauto_transactioncate` + `mlangprintauto_{product}`

#### 3단 구조

```
1단: 품목 선택 (9개 제품 드롭다운)
  → 2단: Section(종류) 목록 로드
    → 3단: 해당 Section의 가격표 (수량별 가격 그리드)
```

#### 가격표 구조

| 컬럼 | 설명 |
|------|------|
| style | 종류 코드 |
| Section | 재질/규격 코드 |
| quantity | 수량 |
| money | 가격 (원) |

#### 스티커 가격 전용 (sticker.php)

스티커는 수학 공식 기반이므로, 별도의 요율 테이블(`shop_d1~d4`)을 관리:
- 각 재질별 수량 구간 요율 수정
- 기본관리비(mg), 톰슨비 수정
- 수정 후 실시간 시뮬레이션 미리보기

#### 사용법

1. **품목 선택**: 드롭다운에서 제품 선택
2. **종류 확인**: Section 목록에서 종류(재질/규격) 확인
3. **가격 수정**: 수량별 가격 셀 클릭 → 직접 수정 → 저장
4. **스티커**: 별도 "스티커수정" 메뉴에서 요율 테이블 관리

---

### 6.19 품목옵션 관리 (premium-options/index.php)

#### 기술 구조

- **파일**: `dashboard/premium-options/index.php`
- **API**: `dashboard/api/premium_options.php`

#### 프리미엄 옵션 종류

| 옵션 | 설명 | 적용 제품 |
|------|------|----------|
| 박 | 금박/은박 가공 | 명함, 상품권 |
| 넘버링 | 일련번호 인쇄 | 상품권 |
| 미싱 | 절취선 가공 | 전단지, 상품권 |
| 귀돌이 | 모서리 라운딩 | 명함 |
| 오시 | 접는 선 가공 | 전단지, 카다록 |

#### 사용법

1. 품목 선택 → 해당 품목의 프리미엄 옵션 목록 표시
2. 각 옵션의 가격/설명 수정 → 저장
3. 옵션 ON/OFF 토글 → 고객 주문 페이지에서 표시/숨김

---

### 6.20 갤러리 관리 (gallery/index.php)

#### 기술 구조

- **파일**: `dashboard/gallery/index.php`
- **API**: `dashboard/api/gallery.php`
- **이미지 경로**: `/ImgFolder/sample/{product}/`, `/ImgFolder/samplegallery/{product}/`

#### 기능

| 기능 | 설명 |
|------|------|
| 갤러리 이미지 목록 | 품목별 샘플 이미지 표시 |
| 이미지 업로드 | 갤러리 샘플 이미지 추가 |
| 이미지 삭제 | 불필요한 이미지 제거 |
| 정렬 | 드래그앤드롭으로 표시 순서 변경 |

#### 사용법

1. 품목 드롭다운 선택 → 해당 품목 갤러리 이미지 표시
2. "이미지 추가" → 파일 선택 → 업로드
3. 이미지에 마우스 호버 → "삭제" 버튼 표시

---

### 6.21 고객 문의 (inquiries/)

#### 기술 구조

- **목록**: `dashboard/inquiries/index.php`
- **상세**: `dashboard/inquiries/view.php`
- **API**: `dashboard/api/inquiries.php`

#### 기능

| 기능 | 설명 |
|------|------|
| 문의 목록 | 제목, 작성자, 일시, 답변상태 |
| 문의 상세 | 문의 내용 + 첨부파일 |
| 답변 작성 | 관리자 답변 입력 + 이메일 알림 |

#### 사용법

1. **문의 목록**: 미답변 문의 우선 표시 (빨간 배지)
2. **답변 작성**: 문의 상세 페이지 하단 답변 폼 작성 → 저장 시 고객 이메일 자동 알림

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

## Ch8. Knowledge Vault (KB) 시스템

> **접속**: https://dsp114.com/kb/ (비밀번호: `duson2026!kb`)
> dsp114.co.kr/kb/ 로도 동일하게 접속 가능 (듀얼 도메인)
> localhost에서는 비밀번호 없이 자동 접속됩니다.
>
> AI 대화 결과를 저장하고 검색하는 개인 지식 관리 시스템입니다.
> 토큰 소모 없이 기존 정보를 재활용할 수 있습니다.

---

### 8.1 시스템 개요

| 항목 | 값 |
|------|-----|
| 경로 | `/kb/` |
| 인증 | localhost 자동 우회, 프로덕션 비밀번호: `duson2026!kb` |
| DB 테이블 | `knowledge_base` (FULLTEXT INDEX on title, content, tags) |
| 테마 | 다크 모드 (#0f172a 배경, #6366f1 포인트) |
| 마크다운 | marked.js + highlight.js 코드 하이라이팅 |

### 8.2 파일 구조

```
/kb/
├── kb_auth.php    ← 인증 모듈 (64줄)
├── api.php        ← CRUD + FULLTEXT 검색 API (141줄)
├── index.php      ← 메인 검색/목록 페이지 (260줄)
└── article.php    ← 문서 상세/편집 페이지 (281줄)
```

---

### 8.3 인증 모듈 (kb_auth.php)

#### 소스 코드

```php
<?php
define('KB_PASSWORD', 'duson2026!kb');

function kb_is_local() {
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
}

function kb_check_auth() {
    if (kb_is_local()) return;                    // localhost → 자동 우회

    if (isset($_POST['kb_password'])) {           // 비밀번호 POST 처리
        if ($_POST['kb_password'] === KB_PASSWORD) {
            $_SESSION['kb_auth'] = true;
            if (basename($_SERVER['SCRIPT_NAME']) !== 'api.php') {
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            }
            return;
        }
    }

    if (!empty($_SESSION['kb_auth'])) return;     // 세션 인증 확인

    if (basename($_SERVER['SCRIPT_NAME']) === 'api.php') {
        http_response_code(401);                  // API → 401 JSON 응답
        exit(json_encode(['error' => 'auth required']));
    }

    kb_show_login();                              // 페이지 → 로그인 폼 표시
    exit;
}
```

#### 인증 흐름

```
요청 수신
  ├─ localhost (127.0.0.1 / ::1) → 자동 통과 (인증 불필요)
  │
  ├─ POST kb_password 존재?
  │   ├─ 비밀번호 일치 → $_SESSION['kb_auth'] = true → 리다이렉트
  │   └─ 불일치 → 다음 단계로
  │
  ├─ $_SESSION['kb_auth'] 존재? → 통과
  │
  └─ 미인증
      ├─ api.php → HTTP 401 + JSON {"error":"auth required"}
      └─ 페이지 → 로그인 폼 (kb_show_login())
```

#### 로그인 폼 UI

- 다크 테마 (`#0f172a` 배경, `#1e293b` 카드)
- 제목: **KB** Knowledge Vault (KB는 `#6366f1` 보라색)
- 비밀번호 입력 + 로그인 버튼
- 중앙 정렬 (flexbox)

#### 보안 특성

| 항목 | 방식 |
|------|------|
| 비밀번호 저장 | PHP 상수 (평문 비교) |
| 세션 유지 | `$_SESSION['kb_auth']` |
| localhost 우회 | `REMOTE_ADDR` 체크 |
| API 보호 | 401 + JSON 에러 |
| CSRF 보호 | 없음 (내부용 시스템) |

---

### 8.4 API (api.php)

#### 기본 구조

```php
session_start();
require_once __DIR__ . '/kb_auth.php';
kb_check_auth();                              // 모든 API 호출 시 인증 필수
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';          // DB 연결
$action = $_GET['action'] ?? $_POST['action'] ?? '';
```

#### API 엔드포인트

| action | 메서드 | 파라미터 | 응답 | 설명 |
|--------|--------|---------|------|------|
| `search` | GET | `q`, `category`, `page` | `{items, total, page, pages, categories}` | FULLTEXT 검색 |
| `get` | GET | `id` | `{id, title, content, tags, ...}` | 단일 문서 조회 |
| `create` | POST | `title`, `content`, `tags`, `category` | `{success, id}` | 문서 생성 |
| `update` | POST | `id`, `title`, `content`, `tags`, `category` | `{success}` | 문서 수정 |
| `delete` | POST | `id` | `{success}` | 문서 삭제 |
| `categories` | GET | - | `["general", "code", ...]` | 카테고리 목록 |

#### FULLTEXT 검색 동작

```php
// 검색어 있을 때: FULLTEXT Boolean Mode
$where = "MATCH(title, content, tags) AGAINST(? IN BOOLEAN MODE)";
$params = [$q . '*'];    // 와일드카드 자동 추가 (부분 매칭)
$order = "MATCH(...) DESC";  // 관련도순 정렬

// 검색어 없을 때: 최신순 전체 목록
$order = 'updated_at DESC';
```

- 와일드카드(`*`): 입력한 검색어로 시작하는 모든 단어 매칭
- Boolean Mode: 한국어+영문 모두 지원
- 페이지네이션: 20건/페이지

#### 카테고리 7종

| 코드 | 이름 | 용도 |
|------|------|------|
| `general` | 일반 | 분류 미지정 |
| `setup` | 설치가이드 | 환경 설정, 설치 방법 |
| `config` | 설정 | 시스템/앱 설정 |
| `troubleshoot` | 트러블슈팅 | 문제 해결 기록 |
| `code` | 코드/스니펫 | 코드 조각, 레시피 |
| `reference` | 참조 | 참조 문서, 링크 |
| `workflow` | 워크플로우 | 작업 절차, 프로세스 |

#### DB 안전 종료

```php
// api.php 마지막 줄 — PHP 8.2 호환
if (isset($db) && $db) mysqli_close($db);
```

---

### 8.5 메인 페이지 (index.php)

#### 기술 구조

- **파일**: `kb/index.php` (260줄)
- **의존성**: 없음 (순수 JS + CSS)
- **테마**: 다크 (#0f172a 배경, #6366f1 포인트)

#### 화면 구성

| 영역 | 기능 |
|------|------|
| 헤더 | "KB Knowledge Vault" 제목 + "새 문서" 버튼 |
| 검색바 | 실시간 FULLTEXT 검색 (250ms 디바운스) |
| 카테고리 탭 | 전체 + 7개 카테고리 필터 버튼 |
| 문서 목록 | 카드형 리스트 (제목, 스니펫, 태그, 날짜) |
| 페이지네이션 | 이전/다음 + 현재 페이지 표시 |

#### 실시간 검색 JS

```javascript
let searchTimer;
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentPage = 1;
        loadItems();       // → fetch('/kb/api.php?action=search&q=...')
    }, 250);               // 250ms 디바운스
});
```

#### 새 문서 생성

"새 문서" 버튼 클릭 시:
1. `api.php?action=create` POST (기본 제목 "새 문서")
2. 응답에서 `id` 수신
3. `article.php?id={id}` 페이지로 이동 (편집 모드)

#### 사용법

1. **검색**: 상단 검색바에 키워드 입력 → 250ms 후 실시간 결과
2. **카테고리 필터**: 카테고리 버튼 클릭 → 해당 분류만 표시
3. **문서 열기**: 카드 클릭 → `article.php?id={id}` 이동
4. **새 문서**: 우상단 "새 문서" 버튼 클릭

---

### 8.6 문서 상세/편집 (article.php)

#### 기술 구조

- **파일**: `kb/article.php` (281줄)
- **의존성**: marked.js (마크다운 렌더링), highlight.js (코드 하이라이팅)
- **CDN**: `cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/`

#### 화면 구성

| 영역 | 기능 |
|------|------|
| 상단 바 | ← 뒤로가기 + 수정/삭제 버튼 |
| 제목 영역 | 문서 제목 (큰 글씨) + 태그 + 카테고리 + 날짜 |
| 본문 영역 | 마크다운 렌더링 + 코드 하이라이팅 |

#### 마크다운 렌더링

```javascript
// marked.js로 마크다운 → HTML 변환
const html = marked.parse(article.content);
document.querySelector('.content').innerHTML = html;

// highlight.js로 코드 블록 자동 하이라이팅
document.querySelectorAll('pre code').forEach(block => {
    hljs.highlightElement(block);
});
```

#### 코드 블록 복사 버튼

각 코드 블록(`<pre>`)에 "복사" 버튼 자동 추가:
```javascript
document.querySelectorAll('pre').forEach(pre => {
    const btn = document.createElement('button');
    btn.textContent = '복사';
    btn.onclick = () => {
        navigator.clipboard.writeText(pre.querySelector('code').textContent);
        btn.textContent = '복사됨!';
        setTimeout(() => btn.textContent = '복사', 1500);
    };
    pre.appendChild(btn);
});
```

#### 편집 모드

"수정" 버튼 클릭 시:
1. 본문 영역 → `<textarea>` (마크다운 원본) 전환
2. 제목/태그/카테고리 → 편집 가능 `<input>` 전환
3. "저장" 버튼 → `api.php?action=update` POST
4. 저장 완료 → 뷰 모드로 복귀 (마크다운 재렌더링)

#### 삭제

```javascript
function deleteArticle() {
    if (!confirm('정말 삭제하시겠습니까?')) return;
    fetch('/kb/api.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'delete', id: articleId })
    }).then(() => location.href = 'index.php');
}
```

#### 사용법

1. **문서 읽기**: 마크다운으로 렌더링된 본문 + 코드 하이라이팅
2. **코드 복사**: 코드 블록 우상단 "복사" 버튼 클릭
3. **수정**: "수정" 버튼 → 마크다운 원본 편집 → "저장"
4. **삭제**: "삭제" 버튼 → 확인 다이얼로그 → 삭제 후 목록으로

---

### 8.7 DB 테이블 구조

```sql
CREATE TABLE knowledge_base (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    content LONGTEXT NOT NULL,
    tags VARCHAR(500) DEFAULT '',
    category VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT INDEX ft_search (title, content, tags)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

| 컬럼 | 타입 | 설명 |
|------|------|------|
| `id` | INT (PK) | 자동 증가 |
| `title` | VARCHAR(500) | 문서 제목 |
| `content` | LONGTEXT | 마크다운 본문 |
| `tags` | VARCHAR(500) | 쉼표 구분 태그 |
| `category` | VARCHAR(50) | 카테고리 코드 (7종) |
| `created_at` | TIMESTAMP | 생성일 |
| `updated_at` | TIMESTAMP | 수정일 (자동 갱신) |

- **FULLTEXT INDEX**: `title`, `content`, `tags` 3개 컬럼에 걸쳐 전문 검색 지원
- **InnoDB**: MySQL 5.7+ 에서 InnoDB FULLTEXT 지원

---


## Ch9. 결제 시스템 (KG이니시스)

### 9.1 시스템 개요

KG이니시스 표준결제(PC 웹) 연동. 카드결제 중심, 팝업 방식.

| 항목 | 값 |
|------|-----|
| **PG사** | KG이니시스 |
| **상점 MID** | `dsp1147479` (운영) / `INIpayTest` (테스트) |
| **도메인** | `https://dsp114.com` (주) / `https://dsp114.co.kr` (보조) — SITE_URL 자동 감지 |
| **결제 방식** | 팝업 (popup) |
| **Sign Key** | 두 도메인 공용 (이니시스 기술지원 확인 완료) |

### 9.2 설정 파일

| 파일 | 용도 |
|------|------|
| `payment/inicis_config.php` | 메인 설정 (환경 자동 감지) |
| `payment/config.php` | 레거시 설정 (하위 호환) |
| `payment/README_PAYMENT.md` | 설정 가이드 |

### 9.3 환경 자동 감지 (듀얼 도메인 대응)

```php
// inicis_config.php — SITE_URL 기반 자동 전환 (듀얼 도메인 대응)
// dsp114.com, dsp114.co.kr 모두 production으로 인식
if (EnvironmentDetector::isProduction()) {
    define('INICIS_TEST_MODE', false);  // 운영 모드
    $returnUrl = SITE_URL . "/payment/inicis_return.php";
    // dsp114.com 접속 → https://dsp114.com/payment/inicis_return.php
    // dsp114.co.kr 접속 → https://dsp114.co.kr/payment/inicis_return.php
} else {
    define('INICIS_TEST_MODE', true);   // 테스트 모드
    $returnUrl = "http://localhost/payment/inicis_return.php";
}
```

> ⚠️ localhost에서 운영 모드를 활성화하면 실제 결제가 발생합니다. 절대 금지.
> ✅ 두 도메인 모두 같은 MID + Sign Key 사용 가능 (이니시스 기술지원 확인 완료)

### 9.4 결제 흐름 (팝업 처리)

```
1. inicis_request.php → 결제 요청 (팝업 열림)
2. 이니시스 결제창 (카드사 인증)
3-a. 결제 완료 → inicis_return.php → 팝업 닫기 + 부모창 success.php 이동
3-b. 결제 취소 → inicis_close.php → 팝업 닫기 + 부모창 OrderComplete 이동
```

**팝업/iframe 자동 감지 로직** (`inicis_return.php`, `inicis_close.php`):

```javascript
if (window.opener && !window.opener.closed) {
    window.opener.location.href = redirectUrl;
    window.close();
} else if (window.parent && window.parent !== window) {
    window.parent.location.href = redirectUrl;
} else {
    window.location.href = redirectUrl;
}
```

### 9.5 택배비 선불 합산 결제

택배 선불 주문의 경우, 인쇄비에 택배비(+VAT)를 합산하여 결제합니다.

```php
// inicis_request.php — 결제 금액 산출
$price = $money_5;  // 인쇄비 (VAT 포함)
if ($logen_fee_type === '선불' && $logen_delivery_fee > 0) {
    $price += $logen_delivery_fee + round($logen_delivery_fee * 0.1);  // +택배비+VAT
}
```

### 9.6 관리자 알림 이메일

카드결제 완료 시 관리자(`dsp1830@naver.com`)에게 자동 이메일 알림 발송.

**발송 조건**: 결제 성공 (`resultCode = '0000'` 또는 `'00'`) + 주문 상태 업데이트 성공

**이메일 내용**: 주문번호, 결제금액, 결제수단, 거래번호(TID), 주문자명, 연락처, 결제시각, 관리자 페이지 바로가기 링크

```php
// mailer() 함수 사용
$mail_result = mailer(
    '두손기획인쇄',           // 발신자명
    'dsp1830@naver.com',      // 발신 이메일
    $admin_email,              // 수신 이메일
    $admin_subject,            // 제목
    $admin_body,               // 본문 (HTML)
    1,                         // 타입: 1=HTML
    ""                         // 첨부파일: 빈 문자열 필수!
);
```

### 9.7 테스트 카드 번호 (테스트 모드 전용)

| 은행 | 카드번호 | 유효기간 | CVC |
|------|----------|----------|-----|
| 신한 | 9410-1234-5678-1234 | 임의 미래일 | 123 |
| 국민 | 9430-1234-5678-1234 | 임의 미래일 | 123 |
| 삼성 | 9435-1234-5678-1234 | 임의 미래일 | 123 |

### 9.8 운영 배포 체크리스트

- [ ] `INICIS_TEST_MODE = false` 설정 (운영 서버만)
- [ ] `config.env.php` 도메인 감지 정상 확인 (dsp114.com + dsp114.co.kr 모두)
- [ ] 소액 테스트 결제 (100~1,000원) — **두 도메인 모두에서 테스트**
- [ ] 로그 확인 (`/payment/logs/`)
- [ ] DB `payment_inicis` 테이블 업데이트 확인
- [ ] returnUrl이 접속 도메인과 일치하는지 확인

---

## Ch10. 배송 추정 시스템

### 10.1 시스템 개요

택배 시 **무게 추정 + 박스수/택배비 자동 계산** 시스템. 관리자 페이지(post_list52, delivery_manager)에서는 자동 계산값을 기본 표시하고, 관리자가 수정 가능. 주문 페이지/관리자 OrderView에서는 무게만 추정 표시.

| 항목 | 값 |
|------|-----|
| **공통 모듈** | `includes/ShippingCalculator.php` |
| **AJAX API** | `includes/shipping_api.php` |
| **주문 페이지** | `mlangorder_printauto/OnlineOrder_unified.php` (고객용) |
| **관리자 OrderView** | `mlangorder_printauto/OrderFormOrderTree.php` |
| **관리자 주문목록** | `admin/mlangprintauto/orderlist.php` (배송 모달) |
| **로젠택배 관리(구)** | `shop_admin/post_list52.php` |
| **로젠택배 관리(신)** | `shop_admin/delivery_manager.php` |
| **DB 테이블** | `shipping_rates` (요금표), `mlangorder_printauto` (logen_* 컬럼) |

### 10.2 무게 계산 공식

```
용지무게(g) = 평량(gsm) × 절당면적(m²) × 매수
코팅가산: 유광/무광 ×1.04, 라미네이팅 ×1.12
총무게 = 종이무게 (부자재 제외)
```

> 부자재 무게는 제외 — 용지 무게만으로 계산 (2026-02-23 확정).

### 10.3 박스 분리 기준

모든 제품 공통: **20kg 초과 시 박스 분리**. 20kg 이하는 무조건 1박스.

```php
$boxes = max(1, (int)ceil($totalWeightKg / 20));
```

### 10.4 봉투 택배비 계산 (펼침면 크기 기반)

봉투는 다른 제품과 달리 **펼침면 크기 × gsm**으로 무게 계산 후 택배비 산출.

**봉투별 펼침면 사이즈:**

| 봉투 종류 | 펼침면 (mm) | 기본 평량 | 비고 |
|----------|------------|----------|------|
| 대봉투 | 510 × 387 | 120gsm | 로젠 특약 3,500원/box |
| 소봉투 | 238 × 262 | 100gsm | 무게별 차등 요금 |
| A4자켓봉투 | 262 × 238 | 100gsm | 무게별 차등 요금 |

**택배비 결정 로직:**

```
1. 무게 = gsm × (가로mm/1000) × (세로mm/1000) × 수량 ÷ 1000 (kg)
2. 박스 = ceil(무게 / 20)  ← 20kg 초과 시 분리
3. 대봉투: 특약 3,500원 × 박스수 (로젠 계약)
4. 소봉투/자켓: 무게별 차등 × 박스수
   ≤3kg: 3,000원, ≤10kg: 3,500원, ≤15kg: 4,000원, ≤20kg: 5,000원, >20kg: 6,000원
```

### 10.5 전단지 규격별 택배비 룩업

전단지는 규격 × 연수로 고정 룩업:

| 규격 | 박스/연 | 택배비/연 | 비고 |
|------|--------|----------|------|
| A6 | 1 | 6,000원 | A4 1연 동일무게 |
| B6 | 1 | 4,000원 | |
| A5 | 1 | 6,000원 | |
| A4 | 1 | 6,000원 | 0.5연(2000매) 이하 = 특약 3,500원 |
| B5(16절) | 2 | 7,000원 | 16절특약 3,500원/box 고정 |
| B4(8절)/A3 | 2 | 12,000원 | |

### 10.6 ShippingCalculator 메서드

| 메서드 | 용도 | 입력 |
|--------|------|------|
| `estimateFromCart($cartItems)` | 고객 주문 페이지 (AJAX) | 장바구니 배열 |
| `estimateFromOrder($orderData)` | 관리자 주문 상세/목록 | DB 주문 row |
| `loadRates($db)` | DB 요금표 로드 (캐싱) | DB 커넥션 |
| `getRatesForDisplay($db)` | 요금표 반환 | DB 커넥션 |

### 10.7 DB 테이블: shipping_rates

```sql
CREATE TABLE shipping_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rate_group VARCHAR(50) NOT NULL,  -- 'logen_weight' 또는 'logen_16'
    label VARCHAR(100),
    max_kg DECIMAL(5,1) NOT NULL,
    fee INT NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

초기 데이터: `logen_weight` (3kg/3000, 10kg/3500, 15kg/4000, 20kg/5000, 23kg/6000), `logen_16` (16절 고정 3500원)

### 10.8 관리자 화면 동작

**추정 영역** (자동 계산, 읽기 전용):

```
📦 배송 정보 [추정]
예상 무게: 약 12.7kg
※ 추정치이며 실제와 다를 수 있습니다.
```

**확정 영역** (관리자 수동 입력):

```
운임구분: [착불/선불] 선택
박스 수량: [ ] 직접 입력
택배비:   [ ] 직접 입력
송장번호: [ ] 직접 입력
💾 저장 → shipping_api.php?action=logen_save
```

### 10.9 주문 페이지 동작 (고객용)

```
배송방법 "택배" 선택 → 운임구분(착불/선불) 라디오 표시
  ├─ 착불: 기본값, 추가 정보 없음
  └─ 선불: AJAX로 무게 추정 표시
      ├─ "⚠ 추정" 배지 + "실제 무게는 다를 수 있습니다"
      ├─ 추정 무게: 약 X.Xkg
      └─ 📞 02-2632-1830 전화 안내
```

### 10.10 택배 선불 고객 프로세스

관리자가 택배비를 확정하면 고객에게 자동 표시 + 결제 가능:

```
고객: 주문 페이지에서 택배 선불 선택 → 주문완료
  ↓ OrderComplete: "택배비 확정 대기" + 결제버튼 비활성 + 30초 폴링
관리자: 대시보드에서 택배비 입력 → logen_save → 고객 이메일 자동 발송
  ↓ 이메일: 택배비 내역 + "마이페이지에서 결제하기" 버튼 링크
고객: 마이페이지 → 주문상세 → "결제하기" 버튼 (카드/무통장 선택)
  ↓ 카드: /payment/inicis_request.php (인쇄비+택배비 합산 결제)
  ↓ 무통장: 계좌번호 표시 (국민/신한/농협)
```

**적용 위치 6곳:**

| 위치 | 파일 | 동작 |
|------|------|------|
| 주문 페이지 | `OnlineOrder_unified.php` | 선불 선택 시 적색 배지 안내 |
| 주문완료 페이지 | `OrderComplete_universal.php` | 미확정 시 결제버튼 비활성 + 30초 AJAX 폴링 |
| 마이페이지 주문목록 | `mypage/index.php` | 미결제 주문 알림 + "+택배 N원" 표시 |
| 마이페이지 주문상세 | `mypage/order_detail.php` | 결제 섹션 (카드/무통장) + 택배비 대기 안내 |
| 이니시스 결제 | `payment/inicis_request.php` | 선불 택배비(+VAT) 자동 합산 결제 |
| 관리자 택배비 저장 | `includes/shipping_api.php` | 이메일 발송 + 마이페이지 결제 링크 포함 |

### 10.11 핵심 규칙

- 택배비(선불) 확정 시 합계금액에 합산 표시 — DB `money_5`는 수정하지 않고 화면 표시만
- 품목 계산 코드와 얽히면 안 됨 — `PriceCalculationService` 수정 금지
- 무게만 추정 — 고객/관리자 모두 무게 추정값만 표시
- "추정"임을 반드시 명시 — 실제 무게와 다를 수 있음
- 확정 정보는 관리자 수동 입력 — 박스수/택배비/송장번호

---

## Ch11. 회원 인증 시스템

### 11.1 4개 독립 인증 레이어

| # | 레이어 | 파일 | DB 테이블 |
|---|--------|------|-----------|
| 1 | 사용자 인증 | `includes/auth.php`, `member/login_unified.php` | `users` (bcrypt), `member` (레거시) |
| 2 | 관리자 인증 | `admin/includes/admin_auth.php` | `admin_users` |
| 3 | 주문 관리 인증 | `sub/checkboard_auth.php` | 주문번호 + 비밀번호 |
| 4 | 고객 주문 조회 | `sub/my_orders_auth.php` | 전화번호 + 비밀번호 |

### 11.2 비밀번호 저장 표준

**Bcrypt (신규)**:

```php
// 신규 비밀번호는 항상 bcrypt
$hash = password_hash($password, PASSWORD_DEFAULT);
// 결과: $2y$10$... (60자)
```

**Plaintext 지원 + 자동 업그레이드 (레거시)**:

```php
if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
    // Bcrypt 검증
    $login_success = password_verify($password, $stored_password);
} else {
    // Plaintext 검증 + 자동 업그레이드
    if ($password === $stored_password) {
        $login_success = true;
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        // UPDATE users SET password = $new_hash WHERE id = ?
    }
}
```

> 모든 로그인 포인트(헤더, 주문 페이지, 마이페이지)에서 동일한 검증 로직을 사용해야 합니다. 불일치 시 같은 사용자가 특정 페이지에서 로그인 불가 현상 발생.

### 11.3 세션 관리

| 항목 | 값 |
|------|-----|
| 세션 유효기간 | 8시간 |
| Remember Token | 30일 (`remember_tokens` 테이블) |
| 장바구니 세션 보존 | 로그인/회원가입 시 hidden field로 session_id 전달 |

### 11.4 장바구니 (shop_temp) 시스템

```
1. 제품 페이지 "장바구니 담기" → shop_temp INSERT
2. 장바구니/주문 페이지 → shop_temp 조회 (session_id로)
3. "주문완료" 클릭 → mlangorder_printauto INSERT + shop_temp DELETE
```

**세션 만료 시**: 이전 session_id와 달라서 장바구니 조회 불가. 데이터는 DB에 남아있음 (orphaned).

**자동 정리**: `shop_temp_helper.php`의 `cleanupOldCartItems()` — 장바구니 조회 시 7일 이상 된 데이터 자동 삭제.

### 11.5 member → users 마이그레이션 완료

모든 활성 PHP 코드가 `users` 테이블을 primary로 사용. `member` 테이블은 backward compatibility를 위해 유지 (이중 쓰기).

**의도적으로 member 참조를 유지하는 파일:**

| 파일 | 이유 |
|------|------|
| `member/register_process.php` | users INSERT + member 이중 INSERT |
| `member/change_password.php` | users UPDATE + member sync UPDATE |
| `member/password_reset.php` | users UPDATE + member sync UPDATE |
| `admin/AdminConfig.php` | users UPDATE + member sync UPDATE |
| `bbs/PointChick.php` | member.money (포인트 시스템, users에 컬럼 없음) |

### 11.6 핵심 규칙

- 모든 로그인 포인트에서 bcrypt + plaintext 자동 업그레이드 동일 로직 사용
- 로그인 시 장바구니 세션 보존 필수 (hidden field로 session_id 전달)
- bcrypt만 지원하면 레거시 사용자 로그인 불가
- 자동 업그레이드 누락 시 사용자가 plaintext 상태로 영구 고정

---

## Ch12. 마이페이지

### 12.1 파일 구조

| 파일 | 용도 |
|------|------|
| `mypage/index.php` | 주문 목록 (상태 필터, 페이지네이션) |
| `mypage/order_detail.php` | 주문 상세 + 결제 섹션 |
| `mypage/profile.php` | 회원 정보 수정 (사업자 정보 포함) |

### 12.2 주문 상태 — OrderStyle 기반 통일

마이페이지는 `OrderStyle` 컬럼 기반으로 상태를 표시합니다 (SSOT). `level` 컬럼은 사용하지 않습니다.

`getCustomerStatus()` 함수가 OrderStyle 11가지를 고객용 5단계로 그룹핑:

| 고객 상태 | OrderStyle 값 |
|----------|--------------|
| 주문접수 | 0, 1, 2 |
| 접수완료 | 3, 4 |
| 작업중 | 5, 6, 7, 9, 10 |
| 작업완료 | 8 |
| 배송중 | 송장번호 존재 시 |

**상태 변경 경로**: `dashboard/orders/view.php` → 상태 드롭다운 → POST `/dashboard/api/orders.php?action=update` → `UPDATE mlangorder_printauto SET OrderStyle = ?` → 마이페이지에 즉시 반영

### 12.3 결제 조건 (order_detail.php)

| 조건 | 동작 |
|------|------|
| `OrderStyle IN ('2','3','4')` (미결제) | 결제 섹션 표시 |
| 선불 아닌 경우 | 바로 결제 가능 |
| 선불 + `logen_delivery_fee > 0` | 택배비 확정 → 결제 가능 |
| 선불 + 택배비 미확정 | "택배비 확정 대기중" 안내 표시 |

### 12.4 프로필 사업자 상세주소 레거시 파싱

`business_address`에 `|||` 구분자 없이 저장된 레거시 데이터를 자동 분리:

```
입력: "[07301] 서울 영등포구 영등포로36길 9 1층 두손기획인쇄 (영등포동4가)"

파싱 결과:
  메인 주소(readonly): 서울 영등포구 영등포로36길 9
  상세주소(editable):  1층 두손기획인쇄
  참고항목(editable):  (영등포동4가)
```

도로명주소 패턴(`/^(.+(?:로|길|가)\s*\d+(?:-\d+)?)\s+(.+)$/`)으로 자동 분리. 페이지 로드 시 `|||` 없는 레거시 데이터를 즉시 정규 형식으로 변환.

---

## Ch13. 영문 버전 (English Version)

### 13.1 시스템 개요

해외 고객용 영문 주문 사이트. 한국어 사이트와 동일한 DB/백엔드를 공유하며, 프론트엔드만 영문화.

| 항목 | 값 |
|------|-----|
| **경로** | `/en/` (로컬: `http://localhost/en/`, 프로덕션: `https://dsp114.com/en/` 또는 `https://dsp114.co.kr/en/`) |
| **대시보드 토글** | 설정 → 영문 버전 표시 (ON/OFF) → `site_settings.en_version_enabled` |
| **환율 API** | `/en/includes/exchange_rate.php` (USD 실시간 환율) |

### 13.2 파일 구조

```
/en/
├── includes/
│   ├── nav.php              ← 공유 네비게이션 (탑 네비 + 9개 제품 바)
│   └── exchange_rate.php    ← USD 환율 조회
├── index.php                ← EN 홈페이지
├── cart.php                 ← 장바구니
├── checkout.php             ← 주문서 작성
├── order_complete.php       ← 주문 완료
└── products/
    ├── index.php            ← 제품 목록
    ├── order.php            ← 8개 제품 주문 (type 파라미터)
    └── order_sticker.php    ← 스티커 전용 (수식 기반 가격)
```

### 13.3 공유 네비게이션 (en/includes/nav.php)

모든 EN 페이지에서 `include`하는 자체 포함형 컴포넌트 (CSS + HTML + JS 일체):

- **탑 네비**: 로고, Products, Cart, Why Us, Contact, EN|한국어 전환, Get Free Quote CTA
- **제품 바**: 9개 제품 버튼 + Cart (가로 스크롤, 모바일 반응형)
- **Active 상태**: `$_en_current_page` 변수로 현재 제품 하이라이트
- **CSS 클래스 접두어**: `.en-nav-*` (탑 네비), `.en-pbar-*` (제품 바) — 한국어 사이트 CSS와 충돌 방지

```php
// 사용법 (각 페이지에서)
<?php $_en_current_page = 'namecard'; include __DIR__ . '/../includes/nav.php'; ?>
```

### 13.4 주문 플로우

```
홈페이지 (/en/) → 제품 선택 (제품 바 또는 카드)
  → 제품 주문 페이지 (order.php?type=namecard)
    → 옵션 cascade 선택 (Type→Paper→PrintSide→Quantity→Design)
    → 가격 표시 (₩ KRW + ≈ $ USD)
    → Add to Cart → 장바구니 (cart.php)
      → Proceed to Order → 체크아웃 (checkout.php)
        → 주문자 정보 + 배송주소 + 결제방법 입력
        → Place Order → 주문 완료 (order_complete.php)
```

### 13.5 백엔드 공유 (한국어 사이트와 동일)

| 기능 | 공유 API |
|------|----------|
| 옵션 로드 | `/mlangprintauto/{product}/get_*.php` |
| 가격 계산 | `/mlangprintauto/{product}/calculate_price_ajax.php` |
| 장바구니 | `/mlangprintauto/{product}/add_to_basket.php` |
| 주문 처리 | `/mlangorder_printauto/ProcessOrder_unified.php` |
| DB 테이블 | `shop_temp` (장바구니), `mlangorder_printauto` (주문) |

### 13.6 대시보드 EN 토글

| 파일 | 역할 |
|------|------|
| `dashboard/settings/index.php` | 토글 UI |
| `dashboard/api/settings.php` | `en_version_enabled` 키 whitelist |
| `includes/header.php` | `site_settings` 조회 → EN 버튼 조건부 표시 |
| `includes/header-ui.php` | 동일 조건부 표시 |

### 13.7 제품 바 버튼 매핑

| 버튼 | key | 링크 |
|------|-----|------|
| Stickers | sticker | `/en/products/order_sticker.php` |
| Flyers | inserted | `/en/products/order.php?type=inserted` |
| Business Cards | namecard | `/en/products/order.php?type=namecard` |
| Envelopes | envelope | `/en/products/order.php?type=envelope` |
| Catalogs | cadarok | `/en/products/order.php?type=cadarok` |
| Posters | littleprint | `/en/products/order.php?type=littleprint` |
| NCR Forms | ncrflambeau | `/en/products/order.php?type=ncrflambeau` |
| Gift Vouchers | merchandisebond | `/en/products/order.php?type=merchandisebond` |
| Magnetic Stickers | msticker | `/en/products/order.php?type=msticker` |

### 13.8 핵심 규칙

- `formData.append('action', 'add_to_basket')` — EN order.php에서 장바구니 API 호출 시 반드시 포함
- `$_en_current_page` 변수를 `include nav.php` 앞에 설정
- CSS 클래스는 `en-nav-*`, `en-pbar-*` 접두어 사용 (한국어 사이트 충돌 방지)
- sticky sidebar `top: 128px` (64px 네비 + 44px 제품 바 + 20px 간격)
- 한국어 네비 `/includes/nav.php` 수정 금지 — EN 네비는 별도 파일
- 드롭다운 옵션 번역 없음 — "Option labels are shown in Korean" 안내 표시

---

## Ch14. 직원 실시간 채팅

### 14.1 시스템 개요

영업시간(09:00~18:30) 중 고객과 직원 간 실시간 채팅 위젯. 주황색 테마.

| 항목 | 값 |
|------|-----|
| **위젯 파일** | `includes/chat_widget.php` |
| **JS 엔진** | `chat/chat.js` (동적 위젯 생성) |
| **API** | `chat/api.php` |
| **관리자 페이지** | `chat/admin.php` |
| **DB 테이블** | `chat_rooms`, `chat_messages`, `chat_admins` |
| **표시 조건** | 09:00~18:30 (footer.php 통합 토글) |
| **테마** | 주황색 (#F97316) — 보라색 AI 챗봇과 구분 |

### 14.2 DB 스키마

**chat_rooms** (채팅방):

| 컬럼 | 타입 | 설명 |
|------|------|------|
| `id` | INT PK | 채팅방 ID |
| `room_key` | VARCHAR(64) UNIQUE | 고유 키 (UUID) |
| `customer_name` | VARCHAR(100) | 고객명 |
| `customer_email` | VARCHAR(255) | 고객 이메일 |
| `status` | ENUM('active','closed','archived') | 상태 |
| `created_at` | TIMESTAMP | 생성일 |
| `updated_at` | TIMESTAMP | 수정일 |
| `closed_at` | TIMESTAMP NULL | 종료일 |

**chat_messages** (메시지):

| 컬럼 | 타입 | 설명 |
|------|------|------|
| `id` | INT PK | 메시지 ID |
| `room_id` | INT FK | 채팅방 ID |
| `sender_type` | ENUM('customer','admin','system') | 발신자 유형 |
| `sender_name` | VARCHAR(100) | 발신자명 |
| `message` | TEXT | 메시지 내용 |
| `is_read` | TINYINT(1) | 읽음 여부 |
| `created_at` | TIMESTAMP | 발신 시각 |

**chat_admins** (관리자):

| 컬럼 | 타입 | 설명 |
|------|------|------|
| `id` | INT PK | 관리자 ID |
| `username` | VARCHAR(50) UNIQUE | 로그인 ID |
| `password` | VARCHAR(255) | bcrypt 해시 |
| `name` | VARCHAR(100) | 표시명 |
| `is_active` | TINYINT(1) | 활성 여부 |
| `last_login` | TIMESTAMP NULL | 마지막 로그인 |

### 14.3 API 엔드포인트 (chat/api.php)

| action | Method | 용도 |
|--------|--------|------|
| `create_room` | POST | 채팅방 생성 (고객명/이메일) |
| `send_message` | POST | 메시지 전송 |
| `get_messages` | GET | 메시지 목록 조회 (room_key, since_id) |
| `get_rooms` | GET | 채팅방 목록 (관리자용) |
| `close_room` | POST | 채팅방 종료 |
| `mark_read` | POST | 메시지 읽음 처리 |

### 14.4 고객 위젯 동작

```
1. chat.js가 DOM에 위젯 동적 생성 (주황색 원형 버튼)
2. 클릭 → 이름/이메일 입력 폼
3. 입력 완료 → create_room API → 채팅 시작
4. 메시지 입력 → send_message API
5. 3초 간격 폴링 → get_messages API (since_id로 새 메시지만)
6. 관리자 응답 → 실시간 표시
```

### 14.5 관리자 페이지 (chat/admin.php)

```
좌측: 채팅방 목록 (active/closed 필터, 안 읽은 메시지 배지)
우측: 선택된 채팅방 메시지 + 답변 입력
상단: 관리자 로그인/로그아웃
```

- 5초 간격 자동 새로고침 (새 메시지/새 채팅방 감지)
- 채팅방 종료 시 고객에게 시스템 메시지 표시
- 관리자 인증: `chat_admins` 테이블 (bcrypt)

### 14.6 footer.php 통합 토글

직원 채팅과 AI 챗봇은 `footer.php`의 `toggleWidgets()` 함수에서 시간대별 배타적 전환:

```javascript
function toggleWidgets() {
    var biz = isBusinessHours();  // 09:00~18:30
    var staff = document.querySelector('.chat-widget');   // chat.js 동적 생성
    var ai = document.getElementById('ai-chatbot-widget'); // 정적 HTML
    if (staff) staff.style.display = biz ? '' : 'none';
    if (ai) ai.style.display = biz ? 'none' : 'block';
}
setInterval(toggleWidgets, 60000);  // 60초 간격
```

---

## Ch15. AI 챗봇 위젯 (야간/당번)

### 15.1 시스템 개요

영업시간(09:00~18:30) 외 시간에 자동으로 표시되는 AI 챗봇 위젯. 보라색 테마. DB 기반 실시간 가격 조회 제공.

| 항목 | 값 |
|------|-----|
| **위젯 파일** | `/includes/ai_chatbot_widget.php` |
| **API 엔드포인트** | `/api/ai_chat.php` |
| **ChatbotService** | `/v2/src/Services/AI/ChatbotService.php` (직접 require) |
| **지식 베이스** | `/v2/src/Services/AI/ChatbotKnowledge.php` |
| **표시 조건** | 18:30 이후 ~ 09:00 이전 (footer.php 통합 토글) |
| **테마** | 보라색 그라디언트 (#6366f1) — 주황색 직원 채팅과 구분 |

### 15.2 API 구조 (api/ai_chat.php)

| action | Method | 용도 |
|--------|--------|------|
| `chat` | POST | 메시지 전송 → ChatbotService.chat() 호출 |
| `reset` | POST | 대화 세션 초기화 |

- `V2_ROOT` 상수 정의 후 ChatbotService 직접 require (composer autoloader 불필요)
- `.env` 파일의 `GEMINI_API_KEY` 로드 (없어도 DB 기반 가격 조회는 정상 동작)
- Same-origin Referer 체크 (CSRF 대체)
- 세션 기반 대화 상태 유지 (`$_SESSION['chatbot']`)

### 15.3 대화 흐름

```
제품 선택 (빠른 버튼 or 텍스트)
  → 종류 선택 (클릭형 버튼)
    → 용지 선택
      → 수량 선택
        → 인쇄면 선택
          → 디자인 선택
            → 가격 표시 (VAT 포함)
```

### 15.4 위젯 UI 구성

- **토글 버튼**: 79×79px 보라색 원형 (모바일 63×63px), "야간/당번" 라벨
- **채팅 창**: 310×420px, position:fixed, 16px border-radius
- **드래그 이동**: 헤더 바를 마우스/터치로 드래그하여 채팅창 자유 이동 (뷰포트 경계 제한)
- **빠른 선택 버튼**: 스티커/라벨, 전단지/리플렛, 명함/쿠폰, 자석스티커, 봉투 | 카다록, 포스터, 양식지, 상품권 (2줄 배치)
- **클릭형 선택지**: 번호 입력 대신 클릭으로 옵션 선택 (`.ai-opt-btn` 버튼), 선택 후 이전 버튼 비활성화
- **스크롤 격리**: `overscroll-behavior: contain` — 채팅창 스크롤 끝 도달 시 바깥 페이지 스크롤 전파 방지
- **메시지 버블**: 사용자(보라색 우측) / 봇(회색 좌측, "야간당번" 아바타)

### 15.5 한국어 조사 자동 판별

`getParticle()` 헬퍼 — 마지막 글자 받침 유무로 을/를 자동 선택:

```php
private function getParticle(string $text, string $withBatchim, string $withoutBatchim): string
{
    $lastChar = mb_substr($text, -1);
    $code = mb_ord($lastChar);
    if ($code >= 0xAC00 && $code <= 0xD7A3) {
        return (($code - 0xAC00) % 28 === 0) ? $withoutBatchim : $withBatchim;
    }
    return $withBatchim;
}
// 사용: "규격을 선택해주세요" vs "수량을" 자동 처리
```

### 15.6 지식 기반 Q&A

제품 가격 조회 외에 인쇄 가이드/규약/디자인비 등의 질문에도 AI가 답변.

```
사용자 메시지 → chat()
  ├─ 제품 키워드 감지 → 가격 조회 플로우 (DB 기반)
  ├─ 지식 키워드 감지 (isKnowledgeQuestion) → callAiForFreeQuestion → Gemini API
  └─ 둘 다 아님 → 품목 선택 메뉴 표시
```

**지식 베이스 컨텐츠** (`ChatbotKnowledge.php`):

- 회사 정보 (연락처, 계좌, 운영시간, 주소)
- 작업 규약 (교정 2회, 납기, 환불, 색상차이, 파일보관 등)
- 디자인 비용표 (서식/카탈로그/전단지/포스터/명함/봉투/스티커/북디자인)
- 파일 제출 안내 (포맷, 해상도, CMYK, 일러스트 윤곽선)
- 인쇄물 규격 사이즈표 (32절~A2, 명함)

### 15.7 NCR양식지 단계 순서

NCR양식지의 챗봇 대화 단계는 제품 페이지 드롭다운 순서와 반드시 일치해야 합니다:

```php
// ChatbotService.php — NCR 단계 설정
'ncrflambeau' => [
    'steps' => ['style', 'section', 'tree', 'quantity', 'design'],
    'stepLabels' => ['구분', '규격', '색상', '수량', '디자인'],
],
```

> stepLabels는 제품 페이지 실제 드롭다운 라벨과 일치시킬 것. 불일치 시 사용자 혼란 발생.

### 15.8 핵심 규칙

- `.env` 파일 없어도 동작해야 함 — DB 연결만으로 가격 조회 가능
- v2 composer autoloader 의존 금지 — 직접 `require_once`로 로드
- 에러 발생 시 "전화 문의" 안내로 graceful fallback
- 세션 쿠키로 대화 상태 유지 (페이지 이동해도 대화 계속)
- 선택지는 클릭형 버튼으로 제공 (API `options` 배열 → 프론트 `.ai-opt-btn` 렌더링)
- `detectProduct()` 키워드 순서: `msticker`를 `sticker`보다 반드시 먼저 배치 (부분문자열 매칭 방지)
- 지식 베이스(`ChatbotKnowledge.php`) 수정 시 Gemini 시스템 프롬프트 토큰 한도 내 유지

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

### 듀얼 도메인 규칙 (V2 신규)

```php
// ❌ 절대 금지: URL 하드코딩
$url = "https://dsp114.com/payment/inicis_return.php";
$link = "https://dsp114.co.kr/mypage/...";

// ✅ 올바른 방법: SITE_URL 상수 사용
$url = SITE_URL . "/payment/inicis_return.php";
$link = SITE_URL . "/mypage/...";
```

```
⚠️ KB에스크로 mHValue: 도메인 전환 시 반드시 교체 필요
   dsp114.com용:   eb30fbb0bc1da7fdcaf800c0bceebbff201111241043905
   dsp114.co.kr용: ef04cec95f1a7298f1f686bfe3159ade
   롤백: git checkout e6554898 -- right.htm includes/footer.php

⚠️ 새 파일에서 URL 생성 시: 반드시 SITE_URL 상수 사용 (Ch0.5.7 참조)
⚠️ 이메일 본문 링크: SITE_URL 사용 (접속 도메인 기준 자동 감지)
⚠️ KG이니시스: returnUrl/closeUrl에 SITE_URL 사용 (두 도메인 자동 대응)
```

---

*두손기획인쇄 기술 매뉴얼 V2.1 | 2026-02-26*
*듀얼 도메인(dsp114.com + dsp114.co.kr) 하이브리드 운영 체계 반영*
