---
name: duson-print-rules
description: 두손기획 인쇄 시스템의 비즈니스 규칙. 수량 표기, 가격 계산, 제품별 단위, 규격/옵션 표기 등 프로젝트 전반에 적용되는 규칙을 정의합니다. 두손기획 프로젝트 작업 시 항상 이 규칙을 따라야 합니다. Keywords: 두손기획, 수량, 연, 매수, 단위, 전단지, 명함, 가격, 주문, 규격, 옵션
---

# 두손기획 인쇄 시스템 비즈니스 규칙

## [핵심 규칙 1] 수량 표기

### 규칙 1-1: 전단지/리플렛 수량 표기

**형식**: `X연 (Y매)` 또는 `X연 (Y,YYY매)`

| 연수 | 매수 (A4 기준) | 표기 예시 |
|------|---------------|----------|
| 0.5연 | 2,000매 | "0.5연 (2,000매)" |
| 1연 | 4,000매 | "1연 (4,000매)" |
| 2연 | 8,000매 | "2연 (8,000매)" |

**핵심 원칙**:
- **0.5연만 소수점 표시**, 나머지는 정수 (전 품목 통틀어 0.5연만 소수점 사용)
- 매수는 **천 단위 콤마** 표시
- 매수는 **계산하지 않고 DB에서 가져옴** (quantityTwo 또는 mesu 필드)
- **`includes/quantity_formatter.php` 사용 권장**:
  ```php
  // [O] 권장 - 공통 함수 사용
  include "includes/quantity_formatter.php";
  echo formatQuantity($quantity, 'inserted');  // "0.5연" 또는 "1연"
  echo formatQuantityValue($quantity, 'inserted');  // "0.5" 또는 "1" (단위 제외)

  // [O] 직접 구현 시 - 0.5만 소수점 처리
  $display = ($quantity == 0.5) ? '0.5' : number_format(intval($quantity));
  ```

### 규칙 1-2: 전단지/리플렛 외 제품 수량 표기

**형식**: 정수 + 단위

| 제품 | 단위 | 표기 예시 |
|------|------|----------|
| 명함 | 매 | "500매", "1,000매" |
| 봉투 | 매 | "500매", "1,000매" |
| 스티커 | 매 | "1,000매", "3,000매" |
| 자석스티커 | 매 | "500매" |
| 상품권 | 매 | "1,000매" |
| 포스터 | 매 | "100매", "500매" |
| 카다록 | 부 | "500부", "1,000부" |
| 양식지 | 매/권 | "500매", "10권" |

**핵심 원칙**:
- 수량은 **항상 정수** (소수점 없음)
- `number_format(intval($quantity))` 사용
- 단위는 DB의 `unit` 필드에서 가져옴

---

## [핵심 규칙 2] 규격/옵션 표기

### 규칙 2-1: 표기 형식 (2줄 슬래시 방식)

**형식**: 2줄로 압축 표기, 각 항목은 ` / `로 구분

```
[1줄: 규격] 종류 / 용지 / 규격 등
[2줄: 옵션] 인쇄면 / 수량 / 디자인 등
```

**핵심 원칙**:
- 라벨 없이 값만 표시 (종류: X → X)
- 각 항목은 ` / ` (공백-슬래시-공백)로 구분
- 빈 값은 제외
- 추가옵션(코팅, 접지 등)이 있으면 별도 행으로 표시

### 규칙 2-2: 품목별 2줄 구성

| 품목 | 1줄 (규격) | 2줄 (옵션) |
|------|-----------|-----------|
| 전단지(리플렛) | 종류 / 용지 / 규격 | 인쇄면 / 수량 / 디자인|
| 명함 | 종류 / 용지 / 규격 | 인쇄면 / 수량 / 디자인 |
| 봉투 | 종류 / 규격 | 수량 / 디자인 |
| 스티커 | 종류 / 용지 / 규격 | 인쇄면 / 수량 / 디자인 |
| 자석스티커 | 종류 / 용지 / 규격 | 수량 / 디자인 |
| 상품권 | 종류 / 용지 | 수량 / 인쇄면 / 디자인|
| 포스터 | 종류 / 용지 / 규격 | 인쇄면 / 수량 / 디자인|
| 카다록 | 종류 / 용지 / 규격 | 인쇄면 / 수량 / 디자인|
| 양식지 | 종류 / 용지 / 규격 | 수량 / 인쇄도수
 / 디자인|

### 규칙 2-3: 품목별 추가옵션

| 품목 | 추가옵션 | 추가옵션 종류 |
|------|----------|--------------|
| 전단지(리플렛) | O | 코팅(150g 이상만), 접지, 오시 |
| 명함 | O | 박, 넘버링, 미싱, 귀돌이, 오시 |
| 봉투 | O | 테이프 |
| 스티커 | O | 도무송 |
| 자석스티커 | X | - |
| 상품권 | O | 박, 넘버링, 미싱, 귀돌이, 오시 (명함과 동일) |
| 포스터 | O | 코팅, 접지 |
| 카다록 | O | 제본방식, 코팅 |
| 양식지 | X | - |

### 규칙 2-4: 표기 예시

**전단지 예시**:
```
90g아트지(합판인쇄) / A4 (210x297mm)
단면컬러인쇄 / 1연 (4,000매)
[추가옵션: 유광코팅]
```

**명함 예시**:
```
스노우화이트 250g / 90x50mm
양면컬러인쇄 / 500매 / 인쇄의뢰
[추가옵션: 귀돌이]
```

**봉투 예시**:
```
대봉투 / A4
1,000매 / 디자인+인쇄
```

**포스터 예시**:
```
포스터 A2 / 150g스노우지
단면컬러인쇄 / 100매
[추가옵션: 유광코팅]
```

**카다록 예시**:
```
카다록 A4 / 150g아트지 / 16페이지
양면컬러인쇄 / 500부
[추가옵션: 무선제본, 유광코팅]
```

### 규칙 2-5: 적용 위치

- 장바구니 상품 목록
- 주문 페이지 상품 정보
- 주문 완료 페이지 상세
- 관리자 주문서
- 이메일 본문
- 견적서 상품 설명

---

## [핵심 규칙 3] 이모지 사용 금지

**모든 출력에서 이모지 사용 금지**

```php
// [X] 잘못된 표기
echo "✅ 주문이 완료되었습니다";
echo "📦 배송 준비중";
echo "🎉 감사합니다";

// [O] 올바른 표기
echo "주문이 완료되었습니다";
echo "배송 준비중";
echo "감사합니다";
```

**적용 범위**:
- 고객 화면 (장바구니, 주문, 주문완료)
- 관리자 화면
- 이메일 발송 본문
- 견적서/PDF
- 에러 메시지
- 시스템 알림

---

## [수량 표기 적용 위치]

### 1. 장바구니 (cart.php)
**파일**: `mlangprintauto/shop/cart.php`
**위치**: 라인 556-574 (상품 목록 테이블)
```php
// 전단지/리플렛
echo $yeon_display . '연 (' . number_format($mesu) . '매)';
// 기타 제품
echo number_format(intval($quantity)) . $unit;
```

### 2. 주문 페이지 (OnlineOrder_unified.php)
**파일**: `mlangorder_printauto/OnlineOrder_unified.php`
**위치**: 주문 상품 목록 섹션
```php
// 전단지/리플렛: "0.5연 (2,000매)"
// 기타 제품: "500매"
```

### 3. 주문 완료 페이지
**파일들**:
- `mlangorder_printauto/OrderComplete_universal.php`
- `mlangorder_printauto/OrderComplete_unified.php`
**위치**: 주문 상세 정보 섹션
```php
// Type_1 JSON에서 MY_amount, mesu 추출
$my_amount = $json_data['MY_amount'] ?? $order['MY_amount'];
$mesu = $json_data['mesu'] ?? $order['mesu'];
```

### 4. 관리자 주문 상세 (OrderFormOrderTree.php)
**파일**: `mlangorder_printauto/OrderFormOrderTree.php`
**위치**: 라인 1059-1076 (수량/단위 컬럼)
```php
// 전단지/리플렛: 수량 칸에 "X연 (Y매)" 통합, 단위 칸은 '-'
// 기타 제품: 수량/단위 분리 표시
```

### 5. 관리자 목록 (admin.php)
**파일**: `admin/mlangprintauto/admin.php`
**위치**: 주문 목록 테이블
```php
// OrderView 모드에서 수량 표시
```

### 6. 이메일 발송
**파일들**:
- `mlangorder_printauto/ProcessOrder_unified.php` (주문 확인 메일)
- 기타 메일 발송 스크립트
```php
// 메일 본문에 수량 표기 시 동일한 규칙 적용
```

### 7. 견적서 시스템
**파일들**:
- `mlangprintauto/quote/detail.php` (관리자 상세)
- `mlangprintauto/quote/public/view.php` (고객용 공개)
- `mlangprintauto/quote/api/generate_pdf.php` (PDF 생성)
- `mlangprintauto/quote/revise.php` (견적 수정)
- `mlangprintauto/quote/create.php` (견적 생성)
```php
// 전단지: "0.5연 (2,000매)" 한 줄 형식
// 기타: "500매" 정수 형식
```

---

## [구현 패턴]

### 공통 함수 파일 사용 (권장)

**파일**: `includes/quantity_formatter.php`

```php
// 파일 include
include "includes/quantity_formatter.php";

// 수량 + 단위 표시 (자동 단위 판단)
echo formatQuantity($quantity, $product_type);
// 전단지: "0.5연" 또는 "1연"
// 명함: "500매"
// 카다록: "500부"

// 수량만 표시 (단위 제외)
echo formatQuantityValue($quantity, $product_type);
// "0.5" 또는 "1" 또는 "500"

// 단위 지정
echo formatQuantity($quantity, $product_type, '권');
// "10권"
```

### 핵심 로직 (0.5만 소수점)

```php
/**
 * 수량 포맷팅 - 간소화 버전
 * 0.5만 소수점 표시, 나머지는 정수
 */
function formatQuantity($quantity, $product_type = '', $unit = null) {
    $qty = floatval($quantity);

    // 단위 자동 판단
    if ($unit === null) {
        $unit = in_array($product_type, ['inserted', 'leaflet']) ? '연' : '매';
    }

    // 0.5만 소수점, 나머지 정수
    if ($qty == 0.5) {
        return '0.5' . $unit;
    }
    return number_format(intval($qty), 0) . $unit;
}
```

### 전단지 연 + 매수 통합 표시

```php
/**
 * 전단지: "0.5연 (2,000매)" 형식
 */
function formatFlyerWithMesu($yeon, $mesu) {
    // 연수: 0.5만 소수점
    $yeon_display = ($yeon == 0.5) ? '0.5' : number_format(intval($yeon));

    // 매수: 천 단위 콤마
    $mesu_display = number_format(intval($mesu));

    return $yeon_display . '연 (' . $mesu_display . '매)';
}
```

---

## [제품별 단위 매핑]

| product_type | 제품명 | unit 값 | 비고 |
|--------------|--------|---------|------|
| inserted | 전단지 | 연 | 매수는 별도 표시 |
| leaflet | 리플렛 | 연 | 매수는 별도 표시 |
| namecard | 명함 | 매 | 정수만 |
| envelope | 봉투 | 매 | 정수만 |
| sticker | 스티커 | 매 | 정수만 |
| msticker | 자석스티커 | 매 | 정수만 |
| merchandisebond | 상품권 | 매 | 정수만 |
| littleprint | 포스터 | 매 | 정수만 |
| cadarok | 카다록 | 부 | 정수만 |
| ncrflambeau | 양식지 | 매/권 | 정수만 |

---

## [주의사항]

### 1. 전단지 코팅 옵션 제한
```php
// 전단지(리플렛) 150g 미만 용지는 코팅 옵션 비활성화
// [X] 80g, 90g, 100g, 120g -> 코팅 선택 불가
// [O] 150g, 180g, 200g 이상 -> 코팅 선택 가능

if ($paper_weight < 150) {
    // 코팅 체크박스 disabled 처리
    echo '<input type="checkbox" disabled> 코팅';
} else {
    echo '<input type="checkbox" name="coating"> 코팅';
}
```

### 2. 매수(mesu)는 계산하지 않음
```php
// [X] 잘못된 방식 - 직접 계산
$mesu = $yeon * 4000;  // 절대 금지!

// [O] 올바른 방식 - DB에서 가져옴
$mesu = $item['mesu'];  // shop_temp.mesu
$mesu = $json_data['quantityTwo'];  // Type_1 JSON
$mesu = $order['mesu'];  // mlangorder_printauto.mesu
```

### 3. 전단지 0.5연 number_format() 주의
```php
// 전단지만 0.5연이 있음 (다른 제품은 모두 정수)
// [X] 0.5가 1로 반올림됨
number_format(0.5)  // 결과: "1"

// [O] 전단지 연수 표시 시 소수점 처리 필수
number_format(0.5, 1)  // 결과: "0.5"
```

### 4. 데이터 흐름
```
[1] add_to_basket.php: MY_amountRight에서 mesu 추출 -> shop_temp.mesu 저장
[2] cart.php: shop_temp.mesu 읽어서 표시
[3] ProcessOrder_unified.php: mesu -> Type_1.quantityTwo로 복사
[4] OrderComplete*.php: Type_1 JSON에서 추출하여 표시
[5] OrderFormOrderTree.php: Type_1 JSON에서 추출하여 표시
[6] 견적서: sourceData['mesu'] 사용
```

### 5. 카다록 필드 매핑 (2025-12-29 수정)
```php
// [X] 잘못된 필드 매핑 (버그)
$section_name = getCategoryName($connect, $item['PN_type']);  // PN_type은 비어있음!
$style_name = getCategoryName($connect, $item['MY_Fsd']);     // MY_Fsd도 비어있음!

// [O] 올바른 필드 매핑
$section_name = getCategoryName($connect, $item['Section']);  // Section 필드 사용

// 카다록 shop_temp 필드 매핑:
// - MY_type: 종류 (예: 691 = "카다록,리플렛")
// - Section: 규격 (예: 692 = "24절(127*260)3단")
// - PN_type: 용지 (대부분 비어있음)
// - MY_Fsd: 미사용
// - POtype: 인쇄면 (1=단면, 2=양면)
```

**관련 파일**: `mlangorder_printauto/ProcessOrder_unified.php` (cadarok case)

---

## [검증 체크리스트]

수량 표기 관련 코드 수정 시 다음을 확인:

- [ ] 전단지/리플렛은 "X연 (Y매)" 형식인가?
- [ ] 기타 제품은 정수 + 단위 형식인가?
- [ ] number_format() 소수점 처리가 올바른가?
- [ ] mesu는 DB에서 가져오고 있는가? (계산 아님)
- [ ] 규격/옵션이 2줄 슬래시 형식으로 표기되는가? (종류/용지 | 인쇄면/수량)
- [ ] 이모지가 사용되지 않았는가?
- [ ] 모든 적용 위치에서 일관된 형식인가?
  - [ ] 장바구니
  - [ ] 주문 페이지
  - [ ] 주문 완료 페이지
  - [ ] 관리자 페이지
  - [ ] 이메일
  - [ ] 견적서

---

## [관련 문서]

- CLAUDE.md - 프로젝트 전체 규칙
- CLAUDE_DOCS/03_PRODUCTS/FLYER_QUANTITY_SYSTEM.md - 전단지 연/매수 상세
- 업로드다운로드251118.md - 파일 업로드 시스템

---

*Last Updated: 2025-12-30*
*적용 범위: 두손기획 인쇄 시스템 전체*
