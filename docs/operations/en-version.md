## 🌐 영문 버전 (English Version)

### 시스템 개요

해외 고객용 영문 주문 사이트. 한국어 사이트와 동일한 DB/백엔드를 공유하며, 프론트엔드만 영문화.

| 항목 | 값 |
|------|-----|
| **경로** | `/en/` (로컬: `http://localhost/en/`, 프로덕션: `https://dsp114.co.kr/en/`) |
| **대시보드 토글** | 설정 → 영문 버전 표시 (ON/OFF) → `site_settings.en_version_enabled` |
| **환율 API** | `/en/includes/exchange_rate.php` (USD 실시간 환율) |

### 파일 구조

```
/en/
├── includes/
│   ├── nav.php              ← 공유 네비게이션 (탑 네비 + 9개 제품 바)
│   └── exchange_rate.php    ← USD 환율 조회
├── index.php                ← EN 홈페이지 (히어로, 제품, 견적 폼)
├── cart.php                 ← 장바구니
├── checkout.php             ← 주문서 작성
├── order_complete.php       ← 주문 완료
└── products/
    ├── index.php            ← 제품 목록
    ├── order.php            ← 8개 제품 주문 (type 파라미터)
    └── order_sticker.php    ← 스티커 전용 (수식 기반 가격)
```

### 공유 네비게이션 (`en/includes/nav.php`)

모든 EN 페이지에서 `include`하는 자체 포함형 컴포넌트 (CSS + HTML + JS 일체):

- **탑 네비**: 로고, Products, Cart, Why Us, Contact, EN|한국어 전환, Get Free Quote CTA
- **제품 바**: 9개 제품 버튼 + Cart (가로 스크롤, 모바일 반응형)
- **Active 상태**: `$_en_current_page` 변수로 현재 제품 하이라이트

```php
// 사용법 (각 페이지에서)
<?php $_en_current_page = 'namecard'; include __DIR__ . '/../includes/nav.php'; ?>
```

**CSS 클래스 접두어**: `.en-nav-*` (탑 네비), `.en-pbar-*` (제품 바) — 한국어 사이트 CSS와 충돌 방지

### 주문 플로우

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

### 백엔드 공유 (한국어 사이트와 동일)

| 기능 | 공유 API |
|------|----------|
| 옵션 로드 | `/mlangprintauto/{product}/get_*.php` |
| 가격 계산 | `/mlangprintauto/{product}/calculate_price_ajax.php` |
| 장바구니 | `/mlangprintauto/{product}/add_to_basket.php` |
| 주문 처리 | `/mlangorder_printauto/ProcessOrder_unified.php` |
| DB 테이블 | `shop_temp` (장바구니), `mlangorder_printauto` (주문) |

### 대시보드 EN 토글

| 파일 | 역할 |
|------|------|
| `dashboard/settings/index.php` | 토글 UI (🇰🇷한국어만 / 🌐한국어+영어) |
| `dashboard/api/settings.php` | `en_version_enabled` 키 whitelist |
| `includes/header.php` | `site_settings` 조회 → EN 버튼 조건부 표시 |
| `includes/header-ui.php` | 동일 조건부 표시 |

### Critical Rules

1. ✅ `formData.append('action', 'add_to_basket')` — EN order.php에서 장바구니 API 호출 시 반드시 포함
2. ✅ `$_en_current_page` 변수를 `include nav.php` 앞에 설정
3. ✅ CSS 클래스는 `en-nav-*`, `en-pbar-*` 접두어 사용 (한국어 사이트 충돌 방지)
4. ✅ sticky sidebar `top: 128px` (64px 네비 + 44px 제품 바 + 20px 간격)
5. ❌ 한국어 네비 `/includes/nav.php` 수정 금지 — EN 네비는 별도 파일
6. ❌ 드롭다운 옵션 번역 없음 — "Option labels are shown in Korean" 안내 표시

### EN 버전 버그 수정 기록 (2026-02-20)

#### 디자인비 구분 오류 수정
- **문제**: `order.php`의 `ordertype` 값이 `'1'`/`'2'`로 설정 → `PriceCalculationService.php`의 `design_type` 분기에서 `else` 진입 → 디자인 없음 선택 시에도 디자인비 포함
- **원인**: `LEGACY_PARAM_MAP`이 `ordertype` → `design_type` 매핑. 값은 `'print'`(디자인 없음) 또는 `'total'`(디자인 포함)이어야 함
- **수정**: 8개 제품의 `ordertype` 값 `'1'`→`'print'`, `'2'`→`'total'`로 변경
- **파일**: `en/products/order.php`

#### 스티커 장바구니 빈 응답 수정
- **문제**: 장바구니에 스티커 추가 후 `get_basket_items.php`가 빈 body 반환
- **원인**: `substr()`로 한글 텍스트(예: `"jil 아트유광코팅"`) 절단 → UTF-8 깨짐 → `json_encode()` 실패 → `false` 반환
- **수정**: `substr()` → `mb_substr(…, 'UTF-8')`, `JSON_UNESCAPED_UNICODE` 플래그 추가, Fatal Error shutdown handler 추가
- **파일**: `mlangprintauto/shop/get_basket_items.php`

#### 자석스티커 사이즈 드롭다운 비어있음 수정
- **문제**: `get_paper_types.php`에서 `'Ttable' => 'NameCard'` (복사 실수)
- **수정**: `'Ttable' => 'msticker'`
- **파일**: `mlangprintauto/msticker/get_paper_types.php`

#### 자석스티커 장바구니 안됨 수정
- **문제**: `add_to_basket.php`가 `POtype` 필수인데 EN `order.php`의 msticker 설정에 `POtype` 드롭다운 누락
- **수정**: msticker 설정에 `POtype` static 드롭다운 추가 (단면인쇄/양면인쇄)
- **파일**: `en/products/order.php`

### 제품 바 버튼 매핑

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

### 대시보드 주문관리 개선 (2026-02-20)

**구현 위치**: `dashboard/orders/index.php`, `dashboard/api/orders.php`

**인라인 상태 변경**: 주문 목록에서 직접 상태 드롭다운으로 OrderStyle 변경 (기존: view.php 진입 필요)
```javascript
// 드롭다운 변경 → fetch('/dashboard/api/orders.php', {action:'update', id, OrderStyle})
// 성공 시 행 배경색 flash + 원래 색상 복원
```

**배송 컬럼 추가**: 주문 목록에 배송방법/운임구분/택배비 표시 (택배 선불 시 금액 표시)

**스크롤 + 페이지네이션**: `overflow-y-auto` + 하단 `총 N건 · X/Y 페이지` 네비게이션

### 대시보드 결제현황 개선 (2026-02-20)

**구현 위치**: `dashboard/payments/index.php`

- `main` 요소에 `overflow-y-auto` 추가
- 하단 페이지네이션 `총 N건 · X/Y 페이지` 형식으로 개선

### 대시보드 이모지 제거 (2026-02-20)

**적용 위치**: `dashboard/orders/index.php`, `dashboard/orders/view.php`, `dashboard/proofs/index.php`

모든 섹션 제목/카드 헤더에서 이모지 제거. 아이콘 대신 텍스트만 사용.

