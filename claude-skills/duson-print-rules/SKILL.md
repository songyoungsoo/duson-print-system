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
| 1.5연 | 6,000매 | "1.5연 (6,000매)" |
| 2연 | 8,000매 | "2연 (8,000매)" |

**핵심 원칙**:
- 연수는 **소수점 허용** (0.5, 1.5 등)
- 매수는 **천 단위 콤마** 표시
- 매수는 **계산하지 않고 DB에서 가져옴** (quantityTwo 또는 mesu 필드)
- `number_format()` 사용 시 정수/소수 구분 필수:
  ```php
  // [O] 올바른 코드
  floor($quantity) == $quantity
      ? number_format($quantity)      // 정수: 1 -> "1"
      : number_format($quantity, 1)   // 소수: 0.5 -> "0.5"

  // [X] 잘못된 코드 - 0.5가 1로 반올림됨
  number_format($quantity)  // 0.5 -> "1"
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

### 규칙 2-1: 표기 형식

**규격**: 2줄로 표기
**옵션**: 품목에 따라 있는 경우에만 표기 (최대 2줄)

```
[규격 1줄]
[규격 2줄]
[옵션 1줄] (있는 경우)
[옵션 2줄] (있는 경우)
```

### 규칙 2-2: 품목별 옵션 유무

| 품목 | 옵션 | 옵션 종류 |
|------|------|----------|
| 전단지(리플렛) | O | 코팅(150g 이상만), 접지, 오시 |
| 명함 | O | 박, 넘버링, 미싱, 귀돌이, 오시 |
| 봉투 | O | 테이프 |
| 스티커 | O | 도무송 |
| 자석스티커 | X | - |
| 상품권 | O | 박, 넘버링, 미싱, 귀돌이, 오시 (명함과 동일) |
| 포스터 | O | 코팅, 접지 |
| 카다록 | O | 제본방식, 코팅 |
| 양식지 | X | - |

### 규칙 2-3: 표기 예시

**전단지 예시 (옵션 있음)**:
```
90g아트지(합판인쇄)
A4 (210x297mm)
단면컬러인쇄
유광코팅
```

**명함 예시 (옵션 있음)**:
```
명함_소량
스노우화이트 250g
양면컬러인쇄
귀돌이
```

**포스터 예시 (옵션 있음)**:
```
포스터 A2
150g스노우지
단면컬러인쇄
유광코팅
```

### 규칙 2-4: 적용 위치

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

### 전단지/리플렛 수량 표시 함수
```php
/**
 * 전단지/리플렛 수량 표시
 * @param float $yeon 연수 (0.5, 1, 1.5 등)
 * @param int $mesu 매수 (DB에서 가져온 값)
 * @return string "0.5연 (2,000매)" 형식
 */
function formatFlyerQuantity($yeon, $mesu) {
    // 연수: 정수/소수 구분
    $yeon_display = floor($yeon) == $yeon
        ? number_format($yeon)
        : number_format($yeon, 1);

    // 매수: 천 단위 콤마
    $mesu_display = number_format(intval($mesu));

    return $yeon_display . '연 (' . $mesu_display . '매)';
}
```

### 일반 제품 수량 표시 함수
```php
/**
 * 일반 제품 수량 표시
 * @param int $quantity 수량
 * @param string $unit 단위 (매, 부, 권 등)
 * @return string "500매" 형식
 */
function formatProductQuantity($quantity, $unit) {
    return number_format(intval($quantity)) . $unit;
}
```

### 제품별 분기 처리
```php
/**
 * 제품 유형에 따른 수량 표시
 */
function displayQuantity($product_type, $quantity, $unit, $mesu = null) {
    // 전단지/리플렛: 연 + 매수
    if (in_array($product_type, ['inserted', 'leaflet'])) {
        if ($mesu && $mesu > 0) {
            return formatFlyerQuantity($quantity, $mesu);
        }
        // mesu 없으면 연수만 표시
        $yeon_display = floor($quantity) == $quantity
            ? number_format($quantity)
            : number_format($quantity, 1);
        return $yeon_display . '연';
    }

    // 기타 제품: 정수 + 단위
    return formatProductQuantity($quantity, $unit);
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

---

## [검증 체크리스트]

수량 표기 관련 코드 수정 시 다음을 확인:

- [ ] 전단지/리플렛은 "X연 (Y매)" 형식인가?
- [ ] 기타 제품은 정수 + 단위 형식인가?
- [ ] number_format() 소수점 처리가 올바른가?
- [ ] mesu는 DB에서 가져오고 있는가? (계산 아님)
- [ ] 규격은 2줄, 옵션은 2줄로 표기되는가?
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

*Last Updated: 2025-12-29*
*적용 범위: 두손기획 인쇄 시스템 전체*
