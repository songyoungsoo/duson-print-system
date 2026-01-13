---
name: duson-price-calculation
description: 두손기획 인쇄 시스템의 9개 품목별 가격 계산 로직. 데이터베이스 테이블, PHP API, JavaScript 계산 로직의 관계를 정의합니다. 가격 계산 관련 작업 시 이 문서를 참조하세요. Keywords: 가격, 계산, calculate, price, ajax, 전단지, 명함, 봉투, 스티커, 포스터
---

# 두손기획 가격 계산 시스템

## [개요] 아키텍처

```
[사용자 입력] → [JavaScript] → [AJAX] → [PHP API] → [DB 조회] → [가격 계산] → [JSON 응답] → [UI 업데이트]
```

**공통 파일 구조** (각 품목):
```
mlangprintauto/[product]/
├── index.php                    # 제품 페이지 (인라인 JS 포함)
├── calculate_price_ajax.php     # 가격 계산 API
├── calculator.js                # 계산 로직 (일부 품목)
├── add_to_basket.php            # 장바구니 저장
└── get_*.php                    # 옵션 데이터 API
```

---

## [품목 1] 전단지 (inserted)

### 파일 위치
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/inserted/calculate_price_ajax.php` |
| JS 계산 | `mlangprintauto/inserted/calculator.js` |
| 장바구니 | `mlangprintauto/inserted/add_to_basket.php` |

### 데이터베이스 테이블
**메인**: `mlangprintauto_inserted`
```sql
-- 주요 컬럼
style        VARCHAR(100)   -- 인쇄색상 코드
Section      VARCHAR(200)   -- 용지 규격
TreeSelect   INT            -- 용지 종류
quantity     FLOAT          -- 수량 (0.5, 1, 1.5 등)
POtype       VARCHAR(50)    -- 인쇄면 (1=단면, 2=양면)
money        VARCHAR(200)   -- 기본 인쇄비
DesignMoney  INT            -- 편집비 (기본 10000)
quantityTwo  VARCHAR(100)   -- 매수 표시 ("2000장" 등)
```

**추가 옵션**: `additional_options_config`
- 코팅: `option_category='coating'`
- 오시: `option_category='creasing'`

### API 파라미터 (GET)
```
MY_type      : 인쇄색상 코드 (style 매칭)
PN_type      : 용지 규격 (Section 매칭)
MY_Fsd       : 용지 종류 (TreeSelect 매칭)
MY_amount    : 수량 (float: 0.5, 1, 1.5)
POtype       : 인쇄면 (1 또는 2)
ordertype    : 주문유형 (print/design/total)
```

### 가격 계산 공식
```php
$base_price = intval($row['money']);
$design_price = ($ordertype === 'total') ? intval($row['DesignMoney']) : 0;
$subtotal = $base_price + $design_price + $additional_options_total;
$vat = floor($subtotal / 10);
$total = $subtotal + $vat;
```

### API 응답 형식
```json
{
  "success": true,
  "data": {
    "Price": "200,000",
    "DS_Price": "30,000",
    "Order_Price": "253,000",
    "PriceForm": 200000,
    "DS_PriceForm": 30000,
    "VAT_PriceForm": 23000,
    "Total_PriceForm": 253000,
    "MY_amountRight": "2000장"
  }
}
```

### JavaScript 핵심 함수
```javascript
function calculatePriceAjax() {
  var params = "MY_type=" + form.MY_type.value +
               "&PN_type=" + form.PN_type.value +
               "&MY_Fsd=" + form.MY_Fsd.value +
               "&MY_amount=" + form.MY_amount.value +
               "&POtype=" + form.POtype.value +
               "&ordertype=" + form.ordertype.value;

  // AJAX 호출 후 응답 처리
  window.currentPriceData = {
    total_price: data.Order_PriceForm,
    vat_price: data.Total_PriceForm
  };
}
```

### 하위 유형: 리플렛 (leaflet)

리플렛은 **전단지 가격 + 접지/코팅/오시 추가금**으로 계산되는 전단지의 하위 유형입니다.

**추가 파일**:
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/leaflet/calculate_price_ajax.php` |
| JS 계산 | `mlangprintauto/leaflet/calculator.js` |
| 접지 API | `mlangprintauto/leaflet/get_fold_types.php` |
| 코팅 API | `mlangprintauto/leaflet/get_coating_types.php` |
| 오시 API | `mlangprintauto/leaflet/get_creasing_types.php` |
| 장바구니 | `mlangprintauto/leaflet/add_to_basket.php` |

**추가 테이블: 접지 옵션** (`mlangprintauto_leaflet_fold`)
```sql
no               INT PRIMARY KEY
fold_type        VARCHAR(50)    -- 접지방식 (2fold, 3fold 등)
additional_price INT            -- 추가금액
description      VARCHAR(255)
display_order    INT
is_active        TINYINT(1)
```

**접지 옵션 가격**:
| fold_type | 이름 | 가격 |
|-----------|------|------|
| 2fold | 2단접지 | 40,000원 |
| 3fold | 3단접지 | 40,000원 |
| 4fold | 4단접지 | 80,000원 |
| accordion | 병풍접지 | 80,000원 |
| gate | 대문접지 | 100,000원 |
| zfold | Z접지 | 60,000원 |

**리플렛 가격 계산 공식**:
```php
// 1. 전단지 기본 가격 조회 (mlangprintauto_inserted)
$base_price = getInsertedPrice($params);

// 2. 접지 추가금 조회 (mlangprintauto_leaflet_fold)
$fold_price = getFoldPrice($fold_type);

// 3. 코팅 추가금 조회 (additional_options_config)
$coating_price = getCoatingPrice($coating_type);

// 4. 오시 추가금 조회 (additional_options_config)
$creasing_price = getCreasingPrice($creasing_type);

// 5. 합산
$subtotal = $base_price + $design_price + $fold_price + $coating_price + $creasing_price;
$vat = floor($subtotal / 10);
$total = $subtotal + $vat;
```

---

## [품목 2] 명함 (namecard)

### 파일 위치
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/namecard/calculate_price_ajax.php` |
| 장바구니 | `mlangprintauto/namecard/add_to_basket.php` |

### 데이터베이스 테이블
**메인**: `mlangprintauto_namecard`
```sql
style        VARCHAR(100)
Section      VARCHAR(200)
quantity     FLOAT
POtype       VARCHAR(50)
money        VARCHAR(200)
DesignMoney  INT
```

### 프리미엄 옵션 (하드코딩)
```php
$premium_prices = [
    'foil' => 30000,        // 박
    'numbering' => 60000,   // 넘버링
    'perforation' => 20000, // 미싱
    'rounding' => 20000,    // 귀돌이
    'creasing' => 20000     // 오시
];
```

### API 파라미터 (GET/POST)
```
MY_type      : 스타일 (style)
Section      : 섹션
POtype       : 인쇄 타입
MY_amount    : 수량
ordertype    : 주문 유형
premium_options : JSON (선택사항)
```

### 가격 계산 공식
```php
$base_price = intval($row['money']);
$design_price = ($ordertype === 'total') ? intval($row['DesignMoney']) : 0;
$premium_total = array_sum($selected_premium_prices);
$subtotal = $base_price + $design_price + $premium_total;
$total_with_vat = floor($subtotal * 1.1);
```

### 특이사항
- POtype 없이 조회하는 **폴백 메커니즘** 있음
- 프리미엄 옵션은 DB가 아닌 PHP에서 하드코딩

---

## [품목 3] 봉투 (envelope)

### 파일 위치
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/envelope/calculate_price_ajax.php` |
| 장바구니 | `mlangprintauto/envelope/add_to_basket.php` |

### 데이터베이스 테이블
**메인**: `mlangprintauto_envelope`
```sql
style        VARCHAR(100)
Section      VARCHAR(200)
quantity     FLOAT
POtype       VARCHAR(50)
money        VARCHAR(200)
DesignMoney  VARCHAR(200)
```

### 추가 옵션: 양면테이프
```php
// 테이프 가격 계산
if ($envelope_tape_enabled) {
    if ($quantity == 500) {
        $tape_price = 25000;  // 고정
    } else {
        $tape_price = $quantity * 40;  // 40원/매
    }
}
```

### 가격 계산 공식
```php
$total_price = $base_price + $design_price + $tape_price;
$total_with_vat = floor($total_price * 1.1);
```

---

## [품목 4] 스티커 (sticker_new)

### 파일 위치
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/sticker_new/calculate_price_ajax.php` |
| 장바구니 | `mlangprintauto/sticker_new/add_to_basket.php` |

### 데이터베이스 테이블
**메인**: `mlangprintauto_sticker` (재질별 단가)
```sql
jong         VARCHAR(100)   -- 재질 코드
shop_d1      INT            -- 도무송 기본비
shop_d2      INT            -- 도무송 추가비
shop_d3      INT            -- 특수용지비
shop_d4      INT            -- 기타
```

### API 파라미터 (POST 필수)
```
jong         : 재질 코드 (예: "jil아트지")
garo         : 가로 사이즈 (mm, 최대 590)
sero         : 세로 사이즈 (mm, 최대 590)
mesu         : 수량 (최대 10000)
uhyung       : 특수 효과 추가비용
domusong     : 도무송 정보
```

### 수량별 요율 테이블
```php
function getYoyoAndMg($mesu) {
    if ($mesu <= 1000) return ['yoyo' => 0.15, 'mg' => 7000];
    if ($mesu <= 4000) return ['yoyo' => 0.14, 'mg' => 6500];
    if ($mesu <= 5000) return ['yoyo' => 0.13, 'mg' => 6500];
    if ($mesu <= 9000) return ['yoyo' => 0.12, 'mg' => 6000];
    if ($mesu <= 10000) return ['yoyo' => 0.11, 'mg' => 5500];
    if ($mesu <= 50000) return ['yoyo' => 0.10, 'mg' => 5000];
    return ['yoyo' => 0.09, 'mg' => 5000];
}
```

### 가격 계산 공식 (복잡)
```php
// 사이즈 계산 (여백 4mm 추가)
$size = ($garo + 4) * ($sero + 4);

// 도무송 비용 계산
if ($mesu == 500) {
    $d1_cost = (($d1 + $d2 * 20) * 900 / 1000) + (900 * $ts);
} elseif ($mesu == 1000) {
    $d1_cost = (($d1 + $d2 * 20) * $mesu / 1000) + ($mesu * $ts);
} else {
    $d1_cost = (($d1 + $d2 * 20) * $mesu / 1000) + ($mesu * ($ts / 9));
}

// 최종 가격
if ($mesu == 500) {
    $st_price = round(($size * ($mesu + 400) * $yoyo) + $jsp + $jka + $cka + $d1_cost, -3)
                * $gase + $uhyung + ($mg * ($mesu + 400) / 1000);
} else {
    $st_price = round(($size * $mesu * $yoyo) + $jsp + $jka + $cka + $d1_cost, -3)
                * $gase + $uhyung + ($mg * $mesu / 1000);
}

$st_price_vat = floor($st_price * 1.1);
```

### 특이사항
- DB에서 가격을 직접 조회하지 않고 **공식으로 계산**
- 실제 사이즈(mm) 입력 기반
- 가장 복잡한 계산 로직

---

## [품목 5] 자석스티커 (msticker)

### 파일 위치
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/msticker/calculate_price_ajax.php` |
| 장바구니 | `mlangprintauto/msticker/add_to_basket.php` |

### 데이터베이스 테이블
**메인**: `mlangprintauto_msticker`
```sql
Section      VARCHAR(200)   -- 규격 (style 대신 사용)
quantity     FLOAT
money        VARCHAR(200)
DesignMoney  INT
-- POtype 없음
```

### 가격 계산 공식
```php
$base_price = intval($row['money']);
$design_price = ($ordertype === 'total') ? intval($row['DesignMoney']) : 0;
$total_with_vat = floor(($base_price + $design_price) * 1.1);
```

### 특이사항
- 가장 단순한 구조
- POtype 필드 없음
- Section 필드로 규격 구분

---

## [품목 6] 카다록 (cadarok)

### 파일 위치
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/cadarok/calculate_price_ajax.php` |
| 장바구니 | `mlangprintauto/cadarok/add_to_basket.php` |

### 데이터베이스 테이블
**메인**: `mlangprintauto_cadarok`
```sql
style        VARCHAR(100)
Section      VARCHAR(200)
quantity     FLOAT
POtype       VARCHAR(50)    -- 선택사항
money        VARCHAR(200)
DesignMoney  INT
```

### 가격 계산 공식
```php
$base_price = intval($row['money']);
$design_price = ($ordertype === 'total') ? intval($row['DesignMoney']) : 0;
$additional_total = intval($_GET['additional_options_total'] ?? 0);
$subtotal = $base_price + $design_price + $additional_total;
$total_with_vat = floor($subtotal * 1.1);
```

### 특이사항
- POtype 없이 조회하는 폴백 메커니즘 있음
- `additional_options_total` 파라미터 지원

---

## [품목 7] 포스터 (littleprint)

### 파일 위치
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/littleprint/calculate_price_ajax.php` |
| 장바구니 | `mlangprintauto/littleprint/add_to_basket.php` |

### 데이터베이스 테이블
**메인**: `mlangprintauto_littleprint`
```sql
style        VARCHAR(100)
Section      VARCHAR(200)
quantity     FLOAT
money        VARCHAR(200)
DesignMoney  INT
```

### Section 매핑 로직
```php
// 재질 코드 → 기본값(610)으로 변환
$section_map = [
    '604' => '610',  // 120아트/스노우
    '605' => '610',  // 150아트/스노우
    '606' => '610',  // 180아트/스노우
    '607' => '610',  // 200아트/스노우
    '608' => '610',  // 220아트/스노우
    '609' => '610',  // 250아트/스노우
];
$section = $section_map[$section] ?? $section;
```

### 특이사항
- 테이블 우선순위: littleprint > namecard (폴백)
- Section 매핑으로 다양한 재질을 하나로 통합

---

## [품목 8] 상품권 (merchandisebond)

### 파일 위치
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/merchandisebond/calculate_price_ajax.php` |
| 장바구니 | `mlangprintauto/merchandisebond/add_to_basket.php` |

### 데이터베이스 테이블
**메인**: `mlangprintauto_merchandisebond`
```sql
style        VARCHAR(100)
Section      VARCHAR(200)
quantity     FLOAT
POtype       VARCHAR(50)
money        VARCHAR(200)
DesignMoney  INT
```

### 프리미엄 옵션 (명함과 동일)
```php
$premium_prices = [
    'foil' => 30000,        // 박
    'numbering' => 60000,   // 넘버링
    'perforation' => 20000, // 미싱
    'rounding' => 20000,    // 귀돌이
    'creasing' => 20000     // 오시
];
```

### API 응답 형식 (JS 호환)
```json
{
  "success": true,
  "data": {
    "PriceForm": 50000,
    "DS_PriceForm": 10000,
    "Premium_PriceForm": 30000,
    "Total_PriceForm": 99000
  }
}
```

---

## [품목 9] 양식지 (ncrflambeau)

### 파일 위치
| 용도 | 파일 |
|------|------|
| 가격 API | `mlangprintauto/ncrflambeau/calculate_price_ajax.php` |
| 장바구니 | `mlangprintauto/ncrflambeau/add_to_basket.php` |

### 데이터베이스 테이블
**메인**: `mlangprintauto_ncrflambeau`
```sql
style        VARCHAR(100)
Section      VARCHAR(200)
TreeSelect   INT
quantity     FLOAT
money        VARCHAR(200)
DesignMoney  INT
```

### API 특징 (POST 필수)
```php
// Prepared Statement 사용 (가장 안전)
$query = "SELECT * FROM mlangprintauto_ncrflambeau
          WHERE style = ? AND Section = ? AND TreeSelect = ? AND quantity = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "sssi", $style, $section, $tree_select, $quantity);
```

### 응답 형식 (상세)
```json
{
  "success": true,
  "message": "가격 계산 완료",
  "data": {
    "base_price": 50000,
    "design_price": 10000,
    "total_price": 66000,
    "formatted": {
      "base_price": "50,000원",
      "design_price": "10,000원",
      "total_price": "66,000원"
    }
  }
}
```

---

## [공통] 추가 옵션 테이블

### additional_options_config 구조
```sql
CREATE TABLE additional_options_config (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    option_category  VARCHAR(20),   -- coating, creasing, folding, envelope_tape
    option_type      VARCHAR(20),   -- single, double, 1line, 2line 등
    option_name      VARCHAR(50),   -- 표시용 이름
    base_price       INT,           -- 추가 금액
    is_active        TINYINT(1) DEFAULT 1,
    sort_order       INT DEFAULT 0,
    created_at       TIMESTAMP,
    updated_at       TIMESTAMP
);
```

### 옵션 카테고리별 데이터
**코팅 (coating)**:
| type | name | price |
|------|------|-------|
| single_glossy | 단면유광코팅 | 80,000 |
| double_glossy | 양면유광코팅 | 160,000 |
| single_matte | 단면무광코팅 | 80,000 |
| double_matte | 양면무광코팅 | 160,000 |

**오시 (creasing)**:
| type | name | price |
|------|------|-------|
| 1line | 1줄 | 32,000 |
| 2line | 2줄 | 32,000 |
| 3line | 3줄 | 40,000 |

---

## [공통] JavaScript → 장바구니 연계

### window.currentPriceData 설정
```javascript
// 모든 품목에서 공통으로 사용
window.currentPriceData = {
    total_price: data.Order_PriceForm || data.total_price,
    vat_price: data.Total_PriceForm || data.total_with_vat,
    additional_options_total: additionalTotal
};
```

### FormData 구성 (add_to_basket.php 호출)
```javascript
const formData = new FormData();
formData.append("calculated_price", Math.round(window.currentPriceData.total_price));
formData.append("calculated_vat_price", Math.round(window.currentPriceData.vat_price));
formData.append("product_type", "[product]");
formData.append("MY_amountRight", document.getElementById("MY_amountRight")?.value || "");

fetch("add_to_basket.php", { method: "POST", body: formData })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            // 장바구니 추가 성공
        }
    });
```

### shop_temp 테이블 저장
```sql
-- 장바구니 저장 컬럼
st_price          INT        -- calculated_price
st_price_vat      INT        -- calculated_vat_price
product_type      VARCHAR    -- 품목 코드
unit              VARCHAR    -- 단위 (연/매/부)
mesu              INT        -- 매수 (전단지만)
```

---

## [품목별 비교표] (9개 품목)

### 계산 방식
| 품목 | 가격 소스 | 옵션 | 특이사항 |
|------|----------|------|----------|
| inserted | DB money | 코팅, 오시 | quantityTwo 매수 |
| ↳ leaflet | inserted + 추가 | 접지, 코팅, 오시 | 전단지 하위 유형 |
| namecard | DB money | 박, 넘버링 등 | 프리미엄 하드코딩 |
| envelope | DB money | 양면테이프 | 테이프 수량 계산 |
| sticker | 공식 계산 | 도무송 | 사이즈 입력 |
| msticker | DB money | 없음 | 가장 단순 |
| cadarok | DB money | 추가옵션 | 폴백 메커니즘 |
| littleprint | DB money | 없음 | Section 매핑 |
| merchandisebond | DB money | 박, 넘버링 등 | 명함과 동일 |
| ncrflambeau | DB money | 없음 | Prepared Stmt |

### API 방식
| 품목 | HTTP | 응답 형식 |
|------|------|----------|
| inserted | GET | PriceForm 스타일 |
| ↳ leaflet | GET | PriceForm + Fold/Coating |
| namecard | GET/POST | PriceForm 스타일 |
| envelope | GET | base_price 스타일 |
| sticker | POST | st_price 스타일 |
| msticker | GET | base_price 스타일 |
| cadarok | GET | base_price 스타일 |
| littleprint | GET | base_price 스타일 |
| merchandisebond | GET | PriceForm 스타일 |
| ncrflambeau | POST | formatted 포함 |

---

## [주의사항]

### 1. float 타입 비교 주의
```php
// 수량 비교 시 floatval() 사용 필수
$db_quantity = floatval($row['quantity']);
$input_quantity = floatval($_GET['MY_amount']);

if (abs($db_quantity - $input_quantity) < 0.001) {
    // 일치
}
```

### 2. money 컬럼은 VARCHAR
```php
// DB에서 조회 후 반드시 정수 변환
$base_price = intval($row['money']);  // "54000" → 54000
```

### 3. VAT 계산 방식 통일
```php
// 방식 1: 나누기 10 (전단지, 리플렛)
$vat = floor($subtotal / 10);
$total = $subtotal + $vat;

// 방식 2: 곱하기 1.1 (기타 품목)
$total_with_vat = floor($subtotal * 1.1);
```

### 4. 폴백 메커니즘 패턴
```php
// 1차: 모든 조건 포함
$result = query("WHERE style=? AND Section=? AND POtype=?");

if (empty($result)) {
    // 2차: POtype 제외
    $result = query("WHERE style=? AND Section=?");
}
```

---

## [관련 문서]

- [duson-print-rules] - 수량 표기 규칙, 규격/옵션 표기 규칙
- CLAUDE.md - 프로젝트 전체 규칙
- CLAUDE_DOCS/03_PRODUCTS/ - 제품별 상세 가이드

---

*Last Updated: 2025-12-29*
*적용 범위: 두손기획 인쇄 시스템 가격 계산*
