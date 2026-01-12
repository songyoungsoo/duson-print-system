# 규격/수량 표시 통합 규칙

> **최종 업데이트**: 2026-01-12
> **적용 범위**: 장바구니, 주문페이지, 주문완료페이지, 관리자 주문정보, 주문서 출력

## 핵심 원칙

1. **모든 페이지에서 동일한 서비스 사용**: `SpecDisplayService` + `ProductSpecFormatter`
2. **수량과 단위 분리**: 수량 칼럼(숫자), 단위 칼럼(매/부/권/연)
3. **레거시 데이터 호환**: 소수점 제거, 단위 보정 자동 처리

---

## 1. 핵심 서비스 파일

### SpecDisplayService (통합 출력 서비스)
**파일**: `/includes/SpecDisplayService.php`

```php
$specDisplayService = new SpecDisplayService($db);
$displayData = $specDisplayService->getDisplayData($item);

// 반환값
$displayData['line1'];           // "카다록,리플렛 / 24절(127*260)3단"
$displayData['line2'];           // "단면칼라 / 1,000부 / 인쇄만"
$displayData['quantity_display']; // "1,000부"
$displayData['quantity_value'];   // 1000 (숫자만)
$displayData['unit'];            // "부"
$displayData['product_type'];    // "cadarok"
```

### ProductSpecFormatter (2줄 형식 포맷터)
**파일**: `/includes/ProductSpecFormatter.php`

```php
$specFormatter = new ProductSpecFormatter($db);
$specs = $specFormatter->format($item);

// 반환값
$specs['line1'];     // "카다록,리플렛 / 24절(127*260)3단"
$specs['line2'];     // "단면칼라 / 1,000부 / 인쇄만"
$specs['additional']; // 추가 옵션 (코팅, 접지 등)
```

---

## 2. 제품별 단위 규칙

| 제품 타입 | product_type | 단위 | 표시 예시 |
|----------|--------------|------|-----------|
| 전단지 | inserted | 연 | 0.5연 (2,000매) |
| 리플렛 | leaflet | 연 | 1연 (4,000매) |
| 카다록 | cadarok | 부 | 1,000부 |
| 양식지/NCR | ncrflambeau | 권 | 10권 |
| 명함 | namecard | 매 | 500매 |
| 봉투 | envelope | 매 | 1,000매 |
| 스티커 | sticker | 매 | 1,000매 |
| 자석스티커 | msticker | 매 | 1,000매 |
| 포스터 | littleprint | 매 | 10매 |
| 상품권 | merchandisebond | 매 | 500매 |

---

## 3. 테이블 컬럼 너비 규격

### 관리자 주문 상세 (OrderFormOrderTree.php)
```
| NO | 품목 | 규격/옵션 | 수량 | 단위 | 공급가액 |
| 6% | 17%  |    44%    | 11%  |  9%  |   13%   |
```

### 주문서 출력 (영수증/거래명세표)
```
| NO | 품목 | 규격/옵션 | 수량 | 단위 | 공급가액 |
| 6% | 17%  |    44%    | 11%  |  9%  |   13%   |
```

### 주문 페이지 (OnlineOrder_unified.php)
```
| 품목 | 규격/옵션 | 수량 | 단위 | 공급가액 |
| 15%  |    42%    | 10%  |  8%  |   25%   |
```

### 주문 완료 페이지 (OrderComplete_universal.php)
```
| 주문번호 | 품목 | 규격/옵션 | 수량 | 단위 | 공급가액 | 상태 |
|   10%   | 12%  |    38%    | 10%  |  8%  |   12%   | 10%  |
```

### 장바구니 (cart.php)
```
| 품목 | 규격/옵션 | 수량 | 단위 | 공급가액 | 관리 |
| 20%  |    35%    | 10%  |  8%  |   15%   | 12%  |
```

---

## 4. 레거시 데이터 처리

### 소수점 수량 정리
```php
// SpecDisplayService::parseLegacyType1() 에서 자동 처리
// "10.00권" → "10권"
// "1000.00부" → "1,000부"
// "500.00매" → "500매"

if (preg_match('/([0-9,\.]+)\s*([매연부권개장])/u', $value, $matches)) {
    $num = floatval(str_replace(',', '', $matches[1]));
    $unit = $matches[2];
    if (floor($num) == $num) {
        $cleanedValue = number_format($num) . $unit;
    }
}
```

### 레거시 텍스트 필드 파싱
```php
// Type_1 레거시 형식 예시:
// "구분: 카다록,리플렛\n규격: \n종이종류: \n수량: 1000.00부\n주문방법: 인쇄만"

// 파싱되는 필드:
// - 구분 → spec_type
// - 규격 → spec_size
// - 색상 → spec_sides
// - 종이종류 → spec_material
// - 주문방법 → spec_design
// - 수량 → quantity_display, quantity_value, quantity_unit
```

---

## 5. 페이지별 구현 코드

### 장바구니 (cart.php)
```php
// Line 347-361
$displayData = $specDisplayService->getDisplayData($item);
$quantity_display = $displayData['quantity_display'];
$unit = $displayData['unit'];

// Line 377-390: 규격/옵션 표시
$specFormatter = new ProductSpecFormatter($connect);
$specs = $specFormatter->format($item);
echo $specs['line1'];
echo $specs['line2'];

// Line 419-431: 수량/단위 분리 표시
if ($is_flyer) {
    echo $quantity_display;  // 전단지: "0.5연 (2,000매)"
} else {
    echo number_format($displayData['quantity_value']);  // 숫자만
}
echo $displayData['unit'];  // 단위만
```

### 주문 페이지 (OnlineOrder_unified.php)
```php
$displayData = $specDisplayService->getDisplayData($item);
$specFormatter = new ProductSpecFormatter($db);
$specs = $specFormatter->format($item);

// 규격/옵션
echo $specs['line1'];
echo $specs['line2'];

// 수량 (숫자만)
echo number_format($displayData['quantity_value']);

// 단위
echo $displayData['unit'];
```

### 주문 완료 (OrderComplete_universal.php)
```php
$displayData = $specDisplayService->getDisplayData($item);

// Line1: 규격
echo $displayData['line1'];

// Line2: 옵션
echo $displayData['line2'];

// 수량/단위 분리
echo $displayData['quantity_value'];
echo $displayData['unit'];
```

### 관리자 주문 상세 (OrderFormOrderTree.php)
```php
// Line 142-320: extractOrderInfo() 함수
// SpecDisplayService 또는 ProductSpecFormatter 사용

$specFormatter = new ProductSpecFormatter($db);
$specs = $specFormatter->format($item);

// 규격/옵션 2줄 표시
echo $specs['line1'];
echo $specs['line2'];

// 수량/단위
echo number_format($displayData['quantity_value']);
echo $displayData['unit'];
```

---

## 6. 주문서 출력 스타일

### 부가세포함 금액 폰트
```php
// 공급가액 컬럼이 13%로 좁아져서 폰트 크기 축소
<td style="font-size: 9pt;"><?= number_format($vat_total) ?> 원</td>
```

### 규격/옵션 셀 스타일
```php
// 넓은 컬럼(44%)에서 2줄 표시
<td style="text-align: left; vertical-align: top;">
    <div><?= $specs['line1'] ?></div>
    <div style="color: #666;"><?= $specs['line2'] ?></div>
</td>
```

---

## 7. 파일 동기화 체크리스트

### 핵심 파일 (항상 동일 버전 유지)
- [ ] `/includes/SpecDisplayService.php`
- [ ] `/includes/ProductSpecFormatter.php`

### 동기화 필요 파일
- [ ] `/mlangprintauto/quote/includes/ProductSpecFormatter.php` (견적서용)

### 배포 시 확인
```bash
# 프로덕션 배포 명령
curl -T /var/www/html/includes/SpecDisplayService.php \
  -u dsp1830:ds701018 ftp://dsp1830.shop/includes/SpecDisplayService.php

curl -T /var/www/html/includes/ProductSpecFormatter.php \
  -u dsp1830:ds701018 ftp://dsp1830.shop/includes/ProductSpecFormatter.php

curl -T /var/www/html/mlangorder_printauto/OrderFormOrderTree.php \
  -u dsp1830:ds701018 ftp://dsp1830.shop/mlangorder_printauto/OrderFormOrderTree.php
```

---

## 8. 변경 이력

### 2026-01-12
- **소수점 제거**: `parseLegacyType1()`에서 "10.00권" → "10권" 자동 변환
- **레거시 필드 파싱 추가**: 구분, 규격, 색상, 종이종류, 주문방법
- **컬럼 너비 조정**: 규격/옵션 35%→44%, 공급가액 22%→13%
- **폰트 크기 조정**: 부가세포함 금액 12pt→9pt
- **견적서 동기화**: ProductSpecFormatter 최신 버전 복사

---

## 9. 트러블슈팅

### 문제: 수량에 소수점 표시 (10.00권)
**원인**: 레거시 DB 데이터에 소수점 저장됨
**해결**: SpecDisplayService가 자동으로 정리

### 문제: 단위가 "매"로 표시됨 (카다록인데)
**원인**: DB의 `unit` 컬럼에 잘못된 기본값
**해결**: SpecDisplayService가 `quantity_unit` 또는 제품 타입 기반으로 보정

### 문제: 규격 정보가 비어있음
**원인**: 레거시 주문의 Type_1에 해당 필드 없음
**해결**: 데이터 입력 시점 문제 (코드 문제 아님)

### 문제: 견적서와 주문 페이지 표시가 다름
**원인**: ProductSpecFormatter 버전 불일치
**해결**: `/mlangprintauto/quote/includes/ProductSpecFormatter.php` 동기화
