## 📦 배송 추정 시스템 (Shipping Calculator)

### 시스템 개요
택배 시 **무게 추정 + 박스수/택배비 자동 계산** 시스템.
로젠택배 관리 페이지(post_list52, delivery_manager)에서는 규격/봉투별 자동 계산값을 기본 표시하고, 관리자가 수정 가능.
주문 페이지/관리자 OrderView에서는 무게만 추정 표시하고, 박스수/택배비/송장번호는 관리자가 직접 입력.
| 항목 | 값 |
|------|-----|
| **공통 모듈** | `includes/ShippingCalculator.php` |
| **AJAX API** | `includes/shipping_api.php` (estimate/rates/rates_save/order_estimate/logen_save) |
| **주문 페이지** | `mlangorder_printauto/OnlineOrder_unified.php` (고객용) |
| **관리자 OrderView** | `mlangorder_printauto/OrderFormOrderTree.php` (주문 상세) |
| **관리자 주문목록** | `admin/mlangprintauto/orderlist.php` (배송 모달) |
| **로젠택배 관리(구)** | `shop_admin/post_list52.php` (택배비 자동계산 + CSV/엑셀 내보내기) |
| **로젠택배 관리(신)** | `shop_admin/delivery_manager.php` (택배비 자동계산 + 운송장 일괄등록) |
| **DB 테이블** | `shipping_rates` (요금표), `mlangorder_printauto` (logen_* 컬럼) |
### 무게 계산 공식

```
용지무게(g) = 평량(gsm) × 절당면적(m²) × 매수
코팅가산: 유광/무광 ×1.04, 라미네이팅 ×1.12
총무게 = 종이무게 (부자재 제외)
```

**⚠️ 부자재 무게는 제외** — 용지 무게만으로 계산 (2026-02-23 확정).

### 스티커 무게 계산 (gsm + 후지 120g, 2026-02-24)

스티커는 다른 제품과 달리 **재질 gsm + 후지(이형지) 120gsm**을 합산하여 무게 계산.
후지는 스티커 점착면을 보호하는 이형지로, 모든 스티커에 공통 적용.

```
스티커 무게(g) = (재질gsm + 후지120gsm) × (가로mm/1000) × (세로mm/1000) × 수량
```

**재질별 실제 무게 gsm (후지 포함):**

| 재질 | 재질 gsm | + 후지 | = 실제 gsm |
|------|---------|--------|-----------|
| 아트유광/무광/비코팅 | 90g | +120g | **210g** |
| 모조비코팅 | 80g | +120g | **200g** |
| 강접/초강접 아트 | 90g | +120g | **210g** |
| 유포지 | 80g | +120g | **200g** |
| 은데드롱 | 25g | +120g | **145g** |
| 투명스티커 | 25g | +120g | **145g** |
| 크라프트지 | 57g | +120g | **177g** |

**구현 위치**: `ShippingCalculator.php`
- `STICKER_BACKING_GSM = 120` — 후지 상수
- `STICKER_GSM_MAP` — 레거시 데이터 키워드→gsm fallback
- `parseStickerGSM()` — 재질명에서 gsm 파싱
- `estimateStickerItem()` — 스티커 전용 무게 추정
- `isSmallProduct()`에서 'sticker' 제거 (더 이상 간이 계산 안 함)

### 박스 분리 기준 (CRITICAL — 2026-02-23)

**모든 제품 공통: 20kg 초과 시 박스 분리. 20kg 이하는 무조건 1박스.**

```php
$boxes = max(1, (int)ceil($totalWeightKg / ShippingCalculator::MAX_BOX_WEIGHT_KG));
```

### 박스 그룹핑 규칙 (2026-03-09)

**복수 품목 주문 시, 합포장/별도포장 규칙을 적용하여 박스 수와 택배비를 계산.**

#### 합포장 가능 (소량 품목 1박스에 혼합)

| 제품 | 폴더명 | 조건 |
|------|----------|------|
| 명함 | `namecard` | 합포장 그룹 내 총량 20kg까지 |
| 스티커 | `sticker` | 합포장 그룹 내 총량 20kg까지 |
| 상품권 | `merchandisebond` | 합포장 그룹 내 총량 20kg까지 |

**예시**: 명함 3kg + 스티커 3kg + 상품권 3kg = **1박스** (총 9kg < 20kg)

#### 별도 박스 필수 (다른 품목과 절대 혼합 불가)

| 제품 | 폴더명 | 별도 조건 | 운임구분 |
|------|----------|----------|----------|
| 봉투 | `envelope` | 무조건 별도 박스 | 선불/착불 선택 |
| 전단지 | `inserted` | 무조건 별도 박스 | 선불/착불 선택 |
| 양식지(NCR) | `ncrflambeau` | 무조건 별도 박스 | 선불/착불 선택 |
| 포스터 | `littleprint` | 무조건 별도 박스 | 선불/착불 선택 |
| 카다록 | `cadarok` | 무조건 별도 박스 | 선불/착불 선택 |
| 자석스티커 | `msticker` | 무조건 별도 박스 | ⚠️ **무조건 착불** |

#### 그룹주문 (같은 종류 여러 건)

같은 제품 타입의 여러 주문은 자동으로 같은 그룹에 합산:
- 스티커 A주문 5kg + 스티커 B주문 8kg = **1박스** (총 13kg < 20kg)
- 전단지 A주문 15kg + 전단지 B주문 12kg = **2박스** (총 27kg > 20kg)

```
┌───────────────────────────────────────────────────────────────┐
│  장바구니 품목 예시:                                           │
│  1. 명함 3,000매 (3kg)                                       │
│  2. 스티커 2,000매 (5kg)                                      │
│  3. 상품권 1,000매 (2kg)                                     │
│  4. 전단지 A4 1연 (12kg)                                     │
│  5. 자석스티커 500매 (7kg)                                    │
│                                                              │
│  → 박스 그룹핑 결과:                                         │
│  [합포장] 명함+스티커+상품권 = 10kg → 1박스 (3,500원)      │
│  [별도]   전단지              = 12kg → 1박스 (6,000원특약) │
│  [별도]   자석스티커           = 7kg  → 1박스 (착불)      │
│  ──────────────────────────────────────────────────────────────│
│  선불 택배비 합계: 9,500원 + 착불 1건                       │
│  총 박스: 3개                                               │
└───────────────────────────────────────────────────────────────┘
```

**구현 위치**: `ShippingCalculator.php`
- `MIXABLE_PRODUCTS` — 합포장 가능 제품 목록
- `SEPARATE_BOX_PRODUCTS` — 별도 박스 제품 목록
- `ALWAYS_COD_PRODUCTS` — 무조건 착불 제품 목록
- `getBoxGroup()` — 제품별 그룹 분류
- `isAlwaysCOD()` — 착불 여부 확인
- `estimateFromCart($items, 'bundle')` — 그룹핑 적용한 묶음배송 계산

### 봉투 택배비 계산 (펼침면 크기 기반, 2026-02-23)

봉투는 다른 제품(전단지 등)과 달리 **펼침면 크기 × gsm**으로 무게 계산 후 택배비 산출.

**봉투별 펼침면 사이즈 (불변):**

| 봉투 종류 | 펼침면 (mm) | 기본 평량 | 비고 |
||----------|------------|----------|------|
| 대봉투 | 510 × 387 | 120g / 150g (주문 선택) | 로젠 특약 3,500원/box |
| 소봉투 | 238 × 262 | 100gsm | 무게별 차등 요금 |
| A4자켓봉투 | 262 × 238 | 100gsm | 무게별 차등 요금 |

**대봉투 감지 fallback 체인** (estimateEnvelopeItem + estimateEnvelopeFromOrder 공통):
```
1. spec_size/PN_type 텍스트에 '대봉투' 포함 → 대봉투
2. MY_type = 466 → 대봉투
3. Section ∈ [473,474,741,935,936,985,994] → 대봉투
4. 위 모두 해당 안 됨 → 소봉투 (기본값)
```

**택배비 결정 로직:**
```
1. 무게 = gsm × (가로mm/1000) × (세로mm/1000) × 수량 ÷ 1000 (kg)
2. 박스 = ceil(무게 / 20)  ← 20kg 초과 시 분리
3. 대봉투: 특약 3,500원 × 박스수 (로젠 계약)
4. 소봉투/자켓: 무게별 차등 × 박스수
   ≤3kg: 3,000원, ≤10kg: 3,500원, ≤15kg: 4,000원, ≤20kg: 5,000원, >20kg: 6,000원
```

**검증 결과표:**

| 봉투 | 수량 | 총무게 | 박스 | 택배비 |
||------|------|--------|------|--------|
| 대봉투(120g) | 500매 | 11.8kg | 1box | 3,500원(특약) |
| 대봉투(120g) | 1000매 | 23.7kg | 2box | 7,000원(특약) |
| 대봉투(150g) | 1000매 | 29.6kg | 2box | 7,000원(특약) |
| 소봉투(100g) | 500매 | 3.1kg | 1box | 3,500원 |
| 소봉투(100g) | 1000매 | 6.2kg | 1box | 3,500원 |
| 소봉투(100g) | 3000매 | 18.7kg | 1box | 5,000원 |
| 자켓봉투(100g) | 4000매 | 24.9kg | 2box | 8,000원 |

### 전단지 규격별 택배비 룩업 (2026-03-09 업데이트)

전단지(합판 90g)는 `delivery_rules_config.php`에서 config key별 고정 룩업.
**SSOT**: `shop_admin/delivery_rules_config.php`

| 규격 | config key | 1연=매수 | 0.5연 택배비 | 1연 택배비 | 비고 |
|------|-----------|---------|-------------|-----------|------|
| A4 | `inserted_a4` | 4,000매 | 3,500원/1박스 | 6,000원/1박스 | A4특약 |
| A5 | `inserted_a5` | 8,000매 | 3,500원/1박스 | 6,000원/1박스 | |
| B5(16절) | `inserted_b5` | 8,000매 | 3,500원/1박스 | 7,000원/2박스 | 16절특약 |
| B6(32절) | `inserted_b6` | 16,000매 | 3,500원/1박스 | 7,000원/2박스 | 16절특약 귀속 |
| B4(8절) | `inserted_b4` | 4,000매 | 3,500원/1박스 | 7,000원/2박스 | |
| A3 | `inserted_a3` | 2,000매 | 3,500원/1박스 | 6,000원/1박스 | |

### 선불 택배비 자동 분류 시스템 (2026-03-09)

**제품별 분류 규칙:**

| 분류 | config key | 제품 | 택배비 |
|------|-----------|------|--------|
| auto | `namecard` | 명함 | 5000매이하 3,000원 / 10000매 4,000원 |
| auto | `sticker` | 스티커 | 5000매이하 3,000원 / 10000매 4,000원 |
| auto | `merchandisebond` | 상품권 | 5000매이하 3,000원 / 10000매 4,000원 |
| auto | `envelope_small` | 소봉투 | 2000매이하 3,500원 |
| auto | `envelope_large` | 대봉투 | 500매이하 3,500원 |
| auto | `littleprint` | 포스터 | 200매이하 3,000원 |
| auto | `ncrflambeau` | NCR양식지 | 30권이하 3,000원 |
| auto | `inserted_a4` ~ `inserted_a3` | 전단지(90g) | 사이즈별 상기 표 참조 |
| call_required | `cadarok` | 카다록 | 전화요망 |
| call_required | `inserted_large` | 전단지(120g+) | 전화요망 |
| cod_only | `msticker` | 자석스티커 | 무조건 착불 |

**코드 fallback 체인** (DB 데이터에서 spec_size/material 비어있을 때):
```
spec_size 비어있음 → pnTypeCodeToSize(PN_type) → normalizePaperSize() → sizeMap
  PN_type: 818=B5, 820=B6, 821=A4, 822=A5, 823=B4, 824=A3
spec_material 비어있음 → myFsdCodeToMaterial(MY_Fsd) → extractGsmFromSpec()
  MY_Fsd: 626=90g아트지, 714=120g, 715=150g, 716=180g ...
MY_amount(연수) → getSheetsPerReam() × 연수 = 매수
봉투 → envelopeFsdCodeToType(MY_Fsd) → 소봉투/대봉투 분류
  대봉투 MY_Fsd: 473, 474, 935, 936, 985, 994
```

**구현 위치**:
- `ShippingCalculator::classifyPrepaid()` — 분류 엔진
- `ShippingCalculator::classifyItemPrepaid()` — 품목별 분류
- `ShippingCalculator::getDeliveryConfigKey()` — 제품→config key 매핑
- `ShippingCalculator::getQuantityForConfig()` — 수량 추출 (연수→매수 변환 포함)
- `shipping_api.php?action=prepaid_classify` — AJAX API 엔드포인트
- `shop_admin/delivery_rules_config.php` — 가격 규칙 SSOT

### NCR양식지 무게 계산 (2026-02-25)

NCR양식지는 2가지 용지 유형으로 무게가 다름:
- **NCR지** (상지/중지/하지): 공통 60gsm
- **일반양식지**: 이름의 숫자가 gsm ("80모조"=80g, "100모조"=100g)

**권수 → 매수 변환 (1권 = 50조):**

| 유형 | 겹수 | 1권=매수 | gsm | 비고 |
|------|------|---------|-----|------|
| NCR 2매 | 2 | 100매 | 60g | 가장 일반적 |
| NCR 3매 | 3 | 150매 | 60g | |
| NCR 4매 | 4 | 200매 | 60g | |
| 양식지 | 1 | 100매 | 모조gsm | "80모조"=80g, "100모조"=100g |
| 빌지/영수증 | 1 | 100매 | 80g(기본) | A6 사이즈(85×190mm) |
| 거래명세표 | 1 | 100매 | 모조gsm | 매철 표기 없으면 100매/권 |

**무게 계산 공식:**
```
무게(g) = 총매수 × gsm × 용지면적(m²)
총매수 = 권수 × 매/권 (매철 기준)
박스 = ceil(무게 / 20kg)
택배비 = 무게별 차등
```

**규격 → 용지 사이즈 매핑:**

| 규격명 키워드 | 용지 사이즈 | 면적(m²) |
|-------------|---------|---------|
| A4, 계약서, 거래명세서 | A4 | 0.06237 |
| A5 | A5 | 0.03108 |
| 16절 | B5 | 0.04677 |
| 32절, 거래명세표 | B6 | 0.02330 |
| 빌지, 영수증, 48절 | A6 | 0.01554 |

**검증 결과표:**

| 유형 | 권수 | 매수 | gsm | 사이즈 | 무게 | 박스 |
|------|------|------|-----|--------|------|------|
| NCR 2매 A4 | 30 | 3,000 | 60g | A4 | 11.2kg | 1 |
| NCR 3매 B5 | 50 | 7,500 | 60g | B5 | 21.0kg | 2 |
| 양식 80모조 A4 | 40 | 4,000 | 80g | A4 | 20.0kg | 1 |
| 빌지 | 20 | 2,000 | 80g | A6 | 2.5kg | 1 |
| 거래명세표 100모조 | 10 | 1,000 | 100g | A4 | 6.2kg | 1 |

**구현 위치**: `ShippingCalculator.php`
- `estimateNcrItem()` — 장바구니 경로
- `estimateNcrFromOrder()` — 관리자 경로
- `NCR_SIZE_MAP` — 규격명 키워드 → 용지 사이즈 매핑
- `NCR_GSM = 60`, `NCR_SETS_PER_VOLUME = 50`

### ShippingCalculator 메서드

| 메서드 | 용도 | 입력 |
|--------|------|------|
| `estimateFromCart($cartItems)` | 고객 주문 페이지 (AJAX) | 장바구니 배열 |
| `estimateFromOrder($orderData)` | 관리자 주문 상세/목록 | DB 주문 row |
| `loadRates($db)` | DB 요금표 로드 (캐싱) | DB 커넥션 |
| `getRatesForDisplay($db)` | 요금표 반환 | DB 커넥션 |

### DB 테이블: shipping_rates

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
-- 초기 데이터: logen_weight (3kg/3000, 10kg/3500, 15kg/4000, 20kg/5000, 23kg/6000)
-- logen_16 (16절 고정 3500원)
```

### 관리자 화면 동작 (2026-02-16 통일)

**추정 영역** (자동 계산, 읽기 전용):
```
📦 배송 정보 [추정]
예상 무게: 약 12.7kg (부자재 포함)
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

**적용 위치**:
- `OrderFormOrderTree.php` — 주문 상세 페이지 (추정 무게 + 확정 입력 폼)
- `orderlist.php` — 주문 목록 배송 모달 (추정 무게 + 확정 입력 폼)

### 주문 페이지 동작 — 선불 택배비 자동 분류 (2026-03-09)

```
배송방법 "택배" 선택 → 운임구분(착불/선불) 라디오 표시
  ├─ 착불: 기본값
  └─ 선불: AJAX prepaid_classify 호출 → 3가지 UI 패널 중 하나 표시
      ├─ [auto] 📦 선불택배비: N원 ✅결제포함 | 예상 X~Ykg
      ├─ [call_required] 📞 배송비 별도 확인 (카다록/120g+ 전단지)
      └─ [cod_only] 🚚 착불만 가능 (자석스티커)
```

**auto 제품**: 명함, 스티커(≤10kg), 상품권, 봉투, 포스터, NCR, 합판전단지(90g)
**call_required**: 카다록, 독판전단지(120g+), 스티커(>10kg), 합포장 합산>10kg
**cod_only**: 자석스티커 (무조건 착불 자동 전환)

**10kg 초과 전화요망 규칙 (2026-03-10):**
- 스티커 단독 >10kg → `call_required` (관리자 개입)
- 합포장 그룹(스티커+명함+상품권) 합산 >10kg → `call_required` (관리자 개입)
- 개별 품목이 각각 ≤10kg이라도 합산 초과 시 전화요망으로 전환

**hidden field**: `prepaid_shipping_fee` → ProcessOrder에서 `logen_delivery_fee` 컬럼에 INSERT

### Critical Rules

1. ✅ **택배비(선불) 확정 시 합계금액에 합산 표시** — DB money_5는 수정하지 않고 화면 표시만 (인쇄 레이아웃 포함)
2. ❌ **품목 계산 코드와 얽히면 안 됨** — PriceCalculationService 수정 금지
3. ✅ **auto 제품은 주문 시 자동 선불 택배비 계산** — `delivery_rules_config.php` 기반 고정 가격 (2026-03-09)
4. ❌ **call_required/cod_only 제품은 택배비 자동계산 안 함** — 관리자 직접 입력 또는 착불
5. ✅ **무게는 추정** — ±2kg 범위로 고지 ("추정" 명시)
6. ✅ **확정 정보는 관리자 수동 입력** — 박스수/택배비/송장번호 (auto 제품 제외)

### 택배 선불 고객 화면 (2026-02-21)

관리자가 택배비를 확정하면 고객에게도 자동으로 표시 + 결제 가능한 시스템.

**전체 프로세스 흐름:**
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
| 주문 페이지 | `OnlineOrder_unified.php` | 선불 선택 시 적색 배지 "☎ 전화 후 택배비 확정" |
| 주문완료 페이지 | `OrderComplete_universal.php` | 택배비 미확정 시 결제버튼 비활성 + 30초 AJAX 폴링 |
| 마이페이지 주문목록 | `mypage/index.php` | 미결제 주문 알림 + "+택배 N원" 표시 |
| 마이페이지 주문상세 | `mypage/order_detail.php` | **결제 섹션** (카드결제/무통장입금) + 택배비 대기 안내 |
| 이니시스 결제 | `payment/inicis_request.php` | 선불 택배비(+VAT) 자동 합산 결제 |
| 관리자 택배비 저장 | `includes/shipping_api.php` | 이메일 발송 + **마이페이지 결제 링크** 포함 |

**마이페이지 결제 조건 (order_detail.php):**
- `OrderStyle IN ('2','3','4')` (미결제)
- 선불 아닌 경우: 바로 결제 가능
- 선불인 경우: `logen_delivery_fee > 0` (택배비 확정) 일 때만 결제 가능
- 택배비 미확정: "택배비 확정 대기중" 안내 표시

**이니시스 결제 금액 (inicis_request.php):**
```php
$price = money_5;  // 인쇄비 (VAT포함)
if (logen_fee_type === '선불' && logen_delivery_fee > 0) {
    $price += logen_delivery_fee + round(logen_delivery_fee * 0.1);  // +택배비+VAT
}
```

**이메일 알림 내용:**
- 주문번호, 운임구분(선불), 택배비 (VAT 별도)
- **"마이페이지에서 결제하기" 버튼** → `dsp114.co.kr/mypage/order_detail.php?no={orderNo}`
- "궁금한 점은 02-2632-1830" 안내
- `mailer()` 함수 사용 (7번째 파라미터 `""` 필수, `ob_start()`/`ob_end_clean()` 래핑)

