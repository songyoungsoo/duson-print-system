# 견적서(create.php) 리디자인 핸드오프 문서

> **작성일**: 2026-02-11
> **작성자**: Claude (Anthropic)
> **대상 AI**: Gemini, GLM, 또는 다른 AI 어시스턴트
> **프로젝트**: 두손기획인쇄 (Duson Planning Print System)

---

## 1. 프로젝트 개요

### 환경
- **로컬 개발**: `/var/www/html/` (WSL2 Ubuntu, Apache 2.4, PHP 7.4, MySQL 5.7)
- **프로덕션**: `dsp114.co.kr` (Plesk: nginx + Apache, PHP 8.2)
- **FTP 배포**: `dsp1830:cH*j@yzj093BeTtc` → `/httpdocs/` (웹루트)
- **DB**: `dsp1830` / `ds701018` (로컬) | `dsp1830` / `t3zn?5R56` (프로덕션)

### 핵심 규칙 (CLAUDE.md 참조)
- `.htaccess` 사용 절대 금지 (nginx가 무시 → 500 에러)
- `bind_param` 3번 검증 필수
- 테이블명 항상 소문자
- CSS `!important` 사용 금지
- 품목 폴더명 변경 금지 (inserted, sticker_new, namecard 등 9개 고정)

---

## 2. 완료된 작업 목록

### 2.1 견적서 페이지 Tailwind 리디자인
**파일**: `/admin/mlangprintauto/quote/create.php`

**변경 전**: `excel-style.css` (회색 배경, Win2000 스타일)
**변경 후**: Tailwind CSS + 대시보드 header/sidebar/footer 통합

주요 변경사항:
1. **인증 변경**: `$_SESSION['admin_logged_in']` 직접 체크 → `requireAdminAuth()` 사용
2. **레이아웃**: 대시보드 공통 구조 (header.php + sidebar.php + main + footer.php)
3. **사이드바**: `$_SERVER['REQUEST_URI'] = '/dashboard/quotes/'` 으로 활성 메뉴 표시
4. **섹션 헤더**: `#1E4E79` (브랜드 네이비) 배경 + 흰색 텍스트 (고객정보, 품목목록, 메모)
5. **컬럼 헤더**: `rgba(30,78,121,0.08)` 배경 (NO, 품목, 규격/옵션, 수량, 단가, 공급가액)
6. **그리드 레이아웃**: `grid-template-columns: 36px 1fr 2fr 70px 80px 90px 28px` (7열)
7. **택배비 필드**: 합계에 자동 반영
8. **메모 확장**: textarea `rows="5"` (이전 2줄)
9. **계산기 모달**: 홈페이지 제품 카드 축소 스타일 (3x3 그리드, 그라디언트 헤더)
10. **전단지 수량 2줄 표시**: "1연" + "(4,000매)" 줄바꿈

### 2.2 프로덕션 배포 완료
- `create.php` FTP 업로드: `/httpdocs/admin/mlangprintauto/quote/create.php`

---

## 3. 현재 파일 구조

### create.php 전체 구조 (567줄)
```
Lines 1-27: PHP (auth, DB, AdminQuoteManager, header/sidebar include)
Lines 29-135: HTML (main 영역)
  - 29-42: 헤더 (견적번호, 버튼)
  - 44-63: 고객정보 카드
  - 65-116: 품목목록 카드 (컬럼헤더 + itemsBody + 택배비 + 합계)
  - 118-134: 메모 카드
Lines 137-177: 수동입력 모달
Lines 179-301: 계산기 선택 모달 + iframe 모달
Lines 303-319: JS items 배열 초기화 (PHP → JSON)
Lines 325-423: renderItems() - 그리드 행 렌더링
Lines 425-434: updateTotals() - 합계 계산
Lines 436-501: 모달/저장 함수들
Lines 503-563: 계산기 연동 (iframe postMessage)
Line 566-567: footer include
```

### 핵심 JS 함수들

#### `renderItems()` (line 325)
- DOM API로 그리드 행 생성 (innerHTML 미사용)
- `grid-template-columns: 36px 1fr 2fr 70px 80px 90px 28px`
- 전단지: `quantity_display`에 괄호가 있으면 2줄 표시
- 단가: `unit_price > 0` 이면 사용, 아니면 `supply_price / quantity`

#### `updateTotals()` (line 425)
```javascript
function updateTotals() {
    let supply = 0;
    items.forEach(item => supply += parseInt(item.supply_price) || 0);
    const shipping = parseInt(document.getElementById('shipping_cost').value) || 0;
    supply += shipping;
    const vat = Math.round(supply * 0.1);
    // supplyTotal, vatTotal, grandTotal DOM 업데이트
}
```

#### `saveQuote()` (line 477)
- `shipping_cost` 포함
- items를 `source_type`, `product_type`, `supply_price` 등으로 매핑하여 전송
- API: `api/save.php`

#### postMessage 핸들러 (line 536)
- `ADMIN_QUOTE_ITEM_ADDED` 또는 `CALCULATOR_PRICE_DATA` 수신
- `payload.options`를 평탄화 (flattening)
- `api/add_calculator_item.php`로 저장
- 응답 받아 items 배열에 push → renderItems()

---

## 4. 가격 계산 흐름 (중요!)

### 4.1 정상 흐름 (코팅 등 추가옵션 포함)

```
[Widget JS] calculatePrice()
  ├── additionalTotal 계산 (코팅/접지/오시 가격 × 수량)
  ├── API 호출: POST /api/quote/calculate_price.php
  │     ├── params: {style, Section, TreeSelect, quantity, POtype, ordertype, premium_options_total}
  │     └── PriceCalculationService::calculate()
  │           ├── DB에서 base_price 조회
  │           ├── calculateAdditionalOptions() → premium_options_total 반환
  │           └── orderPrice = base + design + additionalOptions
  ├── 응답: {supply_price: 이미 코팅 포함된 금액}
  └── currentPayload에 저장

[Widget JS] applyToQuote()
  └── postMessage → parent

[Parent JS] message handler
  ├── payload.options 평탄화
  └── POST api/add_calculator_item.php
        ├── supplyPrice = payload.supply_price (코팅 이미 포함)
        ├── st_price = supplyPrice
        ├── additional_options_total = options.premium_options_total
        └── DB 저장 (admin_quotation_temp)
```

### 4.2 검증 결과 (2026-02-11)

**API 직접 테스트 수행**:
```
# 코팅 없이 (전단지 1연, 단면, 인쇄만)
base_price: 54,000  |  additional_options_total: 0  |  order_price: 54,000

# 양면유광코팅 포함 (premium_options_total=160,000)
base_price: 54,000  |  additional_options_total: 160,000  |  order_price: 214,000
```

**결론**: PriceCalculationService는 정상적으로 추가옵션 금액을 합산합니다.

### 4.3 추가옵션 가격표 (DB: additional_options_config)

| 카테고리 | 타입 | 이름 | 기본가 |
|---------|------|------|--------|
| coating | single | 단면유광코팅 | 80,000 |
| coating | double | 양면유광코팅 | 160,000 |
| coating | single_matte | 단면무광코팅 | 90,000 |
| coating | double_matte | 양면무광코팅 | 180,000 |
| folding | 2fold | 2단접지 | 40,000 |
| folding | 3fold | 3단접지 | 40,000 |
| folding | accordion | 병풍접지 | 60,000 |
| folding | gate | 대문접지 | 100,000 |
| creasing | 1line | 1줄오시 | 32,000 |
| creasing | 2line | 2줄오시 | 32,000 |
| creasing | 3line | 3줄오시 | 40,000 |

**가격 계산 공식**: `코팅가격 = base_price × 수량(연)` (최소 1)

---

## 5. 관련 파일 목록

### 견적서 핵심 파일
| 파일 | 역할 |
|------|------|
| `/admin/mlangprintauto/quote/create.php` | 견적서 작성 페이지 (메인) |
| `/admin/mlangprintauto/quote/index.php` | 견적서 목록 |
| `/admin/mlangprintauto/quote/detail.php` | 견적서 상세 |
| `/admin/mlangprintauto/quote/api/save.php` | 견적서 저장 API |
| `/admin/mlangprintauto/quote/api/add_calculator_item.php` | 계산기 품목 추가 API |
| `/admin/mlangprintauto/quote/api/add_manual_item.php` | 수동 품목 추가 API |
| `/admin/mlangprintauto/quote/api/delete_temp_item.php` | 임시 품목 삭제 API |

### 위젯 (iframe으로 로드)
| 파일 | 제품 |
|------|------|
| `/admin/mlangprintauto/quote/widgets/inserted.php` | 전단지 계산기 |
| `/admin/mlangprintauto/quote/widgets/sticker.php` | 스티커 계산기 |
| `/admin/mlangprintauto/quote/widgets/namecard.php` | 명함 계산기 |
| `/admin/mlangprintauto/quote/widgets/envelope.php` | 봉투 계산기 |
| `/admin/mlangprintauto/quote/widgets/littleprint.php` | 포스터 계산기 |
| `/admin/mlangprintauto/quote/widgets/cadarok.php` | 카다록 계산기 |
| `/admin/mlangprintauto/quote/widgets/ncrflambeau.php` | NCR양식 계산기 |
| `/admin/mlangprintauto/quote/widgets/msticker.php` | 자석스티커 계산기 |
| `/admin/mlangprintauto/quote/widgets/merchandisebond.php` | 상품권 계산기 |
| `/admin/mlangprintauto/quote/widgets/api/get_options.php` | 드롭다운 옵션 API |

### 어댑터 (가격 정규화)
| 파일 | 역할 |
|------|------|
| `/admin/mlangprintauto/quote/includes/adapters/InsertedAdapter.php` | 전단지 어댑터 |
| `/admin/mlangprintauto/quote/includes/QuoteAdapterFactory.php` | 어댑터 팩토리 |
| `/admin/mlangprintauto/quote/includes/QuoteItemPayload.php` | 페이로드 DTO |
| `/admin/mlangprintauto/quote/includes/QuoteAdapterInterface.php` | 어댑터 인터페이스 |

### 가격 계산 서비스
| 파일 | 역할 |
|------|------|
| `/includes/PriceCalculationService.php` | 가격 계산 핵심 (line 323: orderPrice 합산) |
| `/api/quote/calculate_price.php` | 가격 계산 API 엔드포인트 |
| `/admin/mlangprintauto/quote/includes/AdminQuoteManager.php` | 견적 데이터 관리 |
| `/admin/mlangprintauto/quote/includes/PriceHelper.php` | 가격 유틸리티 |

### 대시보드 공통
| 파일 | 역할 |
|------|------|
| `/dashboard/includes/header.php` | 상단 헤더 (#1E4E79 네이비 바) |
| `/dashboard/includes/sidebar.php` | 좌측 사이드바 (200px, 다크 네이비) |
| `/dashboard/includes/footer.php` | 하단 푸터 + JS 유틸 |
| `/dashboard/includes/config.php` | 대시보드 설정 + 네비게이션 |

---

## 6. UI 스타일 가이드

### 브랜드 컬러
- **Primary**: `#1E4E79` (네이비)
- **Dark**: `#153A5A`
- **Light**: `#2D6FA8`
- **Tailwind config**: `brand.DEFAULT`, `brand.dark`, `brand.light`

### 섹션 헤더 스타일
```html
<div style="background:#1E4E79;color:#fff;" class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg">
    섹션 제목
</div>
```

### 컬럼 헤더 스타일
```html
<div style="grid-template-columns:36px 1fr 2fr 70px 80px 90px 28px;background:rgba(30,78,121,0.08);color:#1E4E79;"
     class="grid items-center text-xs font-semibold px-1 border-b border-gray-200">
```

### 행 높이: 30px 이하
- 폼 input: `py-1` (약 26px)
- 테이블 행: `py-1.5` (약 28px)
- 카드 헤더: `py-1.5` (약 28px)

### 계산기 선택 모달
- `.qc-card` 클래스 사용
- 그라디언트 헤더, 체크마크 기능 목록, 제품 이미지 썸네일
- 3x3 그리드, `max-w-2xl` 모달

---

## 7. 미해결/진행 중 이슈

### 7.1 사용자 보고: "추가옵션 금액이 더해지지 않는다"

**상태**: 서버 측 검증 완료 — 코드상 정상 동작 확인

**검증 내용**:
1. `PriceCalculationService::calculateAdditionalOptions()` — `premium_options_total` 파라미터를 정상 수신하여 `$orderPrice`에 합산 (line 323)
2. API 직접 테스트 — 코팅 없이 54,000원, 양면유광코팅 포함 시 214,000원으로 정상
3. `InsertedAdapter::normalize()` — `Order_PriceForm` (이미 코팅 포함)을 `supply_price`로 사용
4. `add_calculator_item.php` — `supply_price` 그대로 `st_price`로 저장

**가능한 원인**:
- 사용자가 위젯에서 코팅 체크박스를 선택하지 않고 테스트했을 가능성
- 또는 "추가옵션 금액이 별도로 표시되지 않는다"는 의미였을 수 있음 (현재는 합산된 총액만 표시)
- 브라우저 캐시 문제 (프로덕션 배포 후 캐시 미갱신)

**다음 단계**:
- 사용자에게 실제 시나리오 재확인 요청
- 필요시: 견적서 품목 목록에 추가옵션 금액을 별도 표시하는 기능 추가

### 7.2 DB 기반 옵션 가격 확장 제안

**현재 상태**: 전단지(inserted)만 DB 기반 옵션 가격 사용
- DB 테이블: `additional_options_config`
- 카테고리: coating, folding, creasing (전단지 전용)
- premium (명함용 후가공): foil, numbering, perforation, rounding, creasing

**제안 완료**:
- 스티커 칼선/편집 옵션도 DB화 가능 (`sticker_diecut`, `sticker_editing` 카테고리)
- 기존 위젯의 하드코딩된 가격을 DB 조회로 대체하는 SQL INSERT 예시 제공
- 사용자가 다른 이슈 제기로 보류됨

---

## 8. DB 스키마 참조

### admin_quotation_temp (임시 품목)
주요 컬럼:
```
no (PK), session_id, is_manual, product_type, specification,
unit_price, qty_sheets, mesu, MY_amount, POtype, ordertype,
st_price (공급가액), st_price_vat (부가세 포함),
coating_enabled, coating_type, coating_price,
folding_enabled, folding_type, folding_price,
creasing_enabled, creasing_lines, creasing_price,
additional_options_total, quantity_display, Section,
spec_type, spec_material, spec_size, spec_sides, spec_design,
manual_product_name, manual_specification, manual_quantity,
manual_unit, manual_supply_price, created_at
```

### additional_options_config (추가옵션 가격표)
```
id (PK), option_category, option_type, option_name,
base_price, description, is_active, sort_order,
created_at, updated_at
```

### mlangprintauto_inserted (전단지 가격)
```
style, Section, TreeSelect, quantity, money (인쇄가),
DesignMoney (디자인비), POtype, quantityTwo (매수)
```

---

## 9. FTP 배포 방법

```bash
# 단일 파일 업로드
curl -T "/var/www/html/admin/mlangprintauto/quote/create.php" \
  "ftp://dsp114.co.kr/httpdocs/admin/mlangprintauto/quote/create.php" \
  --user "dsp1830:cH*j@yzj093BeTtc"

# 디렉토리 내 모든 PHP 파일
for f in /var/www/html/admin/mlangprintauto/quote/api/*.php; do
  curl -T "$f" \
    "ftp://dsp114.co.kr/httpdocs/admin/mlangprintauto/quote/api/$(basename $f)" \
    --user "dsp1830:cH*j@yzj093BeTtc"
done
```

---

## 10. 테스트 체크리스트

- [ ] 로컬: `http://localhost/admin/mlangprintauto/quote/create.php` 접속
- [ ] 사이드바/헤더 정상 표시
- [ ] 고객정보 입력
- [ ] 계산기 → 전단지 선택 → 옵션 선택 → 가격 확인 → "견적서에 적용"
- [ ] 품목 목록에 추가된 항목 표시 확인
- [ ] 택배비 입력 → 합계 자동 갱신
- [ ] 수동 품목 추가
- [ ] 품목 삭제
- [ ] 저장/임시저장 → detail.php 리다이렉트
- [ ] 프로덕션 배포 후 동일 테스트

---

## 11. 주의사항 (다른 AI가 작업 시)

1. **CLAUDE.md 필독**: `/var/www/html/CLAUDE.md` — 모든 규칙과 금지사항
2. **bind_param 3번 검증**: SQL 쿼리 작성 시 필수
3. **품목 폴더명**: 절대 변경 금지 (inserted ≠ leaflet, littleprint ≠ poster)
4. **CSS !important 금지**: 명시도(specificity)로 해결
5. **.htaccess 금지**: nginx가 무시 → 500 에러
6. **Tailwind CDN**: 빌드 없음, CDN 직접 사용
7. **postMessage 보안**: `e.origin !== window.location.origin` 체크 필수
8. **PHP 호환성**: 로컬 PHP 7.4, 프로덕션 PHP 8.2 — 양쪽 호환 코드 작성
