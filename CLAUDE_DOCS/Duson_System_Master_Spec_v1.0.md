# Duson System Master Spec v1.0

**두손기획인쇄 통합 기술 명세서**

> **선언**: 이 문서는 현재 서버에서 실제로 가동 중인 코드를 역공학(Reverse Engineering)하여 작성되었습니다.
> 과거의 기획 의도나 미완성 기능이 아닌, **실제 동작하는 로직만** 기술합니다.

---

## Revision History

| 날짜 | 수정자 | 수정항목 | 관련 파일 |
|------|--------|----------|-----------|
| 2026-01-14 | Claude | 최초 작성 - 코드 역공학 기반 | 전체 |

---

## 1. 시스템 개요

### 1.1 기술 스택
| 항목 | 값 |
|------|-----|
| PHP | 7.4+ |
| MySQL | 5.7+ (utf8mb4) |
| Web Server | Apache 2.4+ |
| Document Root | `/var/www/html` |

### 1.2 핵심 파일 구조
```
/var/www/html/
├── db.php                          # DB 연결 (환경 자동 감지)
├── lib/
│   └── core_print_logic.php        # SSOT 중앙 진입점
├── includes/
│   ├── QuantityFormatter.php       # 수량/단위 SSOT
│   ├── ProductSpecFormatter.php    # 제품 사양 포맷팅
│   └── SpecDisplayService.php      # 사양 표시 서비스
├── mlangprintauto/[product]/       # 제품별 프론트엔드
│   ├── index.php                   # 제품 페이지
│   └── calculate_price_ajax.php    # 가격 계산 API
└── mlangorder_printauto/           # 주문 처리
    ├── ProcessOrder_unified.php    # 주문 저장
    ├── OrderFormOrderTree.php      # 주문서 표시
    └── OrderComplete_universal.php # 주문 완료
```

---

## 2. 제품 체계 (ACTUAL)

### 2.1 제품 코드 ↔ 폴더 ↔ 단위 매핑

| product_type | 폴더명 | 단위코드 | 단위명 | 가격표 테이블 | 비고 |
|--------------|--------|----------|--------|---------------|------|
| `inserted` | mlangprintauto/inserted/ | R | 연 | mlangprintauto_inserted (745) | 전단지+리플렛 통합 |
| `namecard` | mlangprintauto/namecard/ | S | 매 | mlangprintauto_namecard (289) | |
| `envelope` | mlangprintauto/envelope/ | S | 매 | mlangprintauto_envelope (180) | |
| `sticker_new` | mlangprintauto/sticker_new/ | S | 매 | mlangprintauto_sticker_new (0) | 수학계산 기반 |
| `msticker` | mlangprintauto/msticker/ | S | 매 | mlangprintauto_msticker (724) | 자석스티커 |
| `cadarok` | mlangprintauto/cadarok/ | B | 부 | mlangprintauto_cadarok (154) | |
| `ncrflambeau` | mlangprintauto/ncrflambeau/ | V | 권 | mlangprintauto_ncrflambeau (287) | NCR양식지 |
| `littleprint` | mlangprintauto/littleprint/ | S | 매 | mlangprintauto_littleprint (206) | 포스터 (레거시명) |
| `merchandisebond` | mlangprintauto/merchandisebond/ | S | 매 | mlangprintauto_merchandisebond (18) | 상품권 |

### 2.2 단위 코드 체계 (UNIT_CODES)

```php
// 실제 코드: /includes/QuantityFormatter.php:20-27
const UNIT_CODES = [
    'R' => '연',  // Ream - 전단지/리플렛
    'S' => '매',  // Sheet - 스티커/명함/봉투/포스터
    'B' => '부',  // Bundle - 카다록
    'V' => '권',  // Volume - NCR양식지
    'P' => '장',  // Piece - 개별 인쇄물
    'E' => '개'   // Each - 기타
];
```

### 2.3 레거시 주의사항 (ACTUAL)

| 레거시 코드 | 실제 의미 | 처리 방식 |
|-------------|-----------|-----------|
| `sticker` | 미사용 (폴더만 존재) | `sticker_new`로 대체 |
| `leaflet` | 이미지 경로용 | 주문은 `inserted`로 처리 |
| `littleprint` | 포스터 | 코드 변경 금지 |
| `poster` | littleprint의 별칭 | 내부적으로 `littleprint` 사용 |
| `msticker_01` | msticker의 별칭 | 동일 처리 |

---

## 3. 데이터베이스 스키마 (ACTUAL)

### 3.1 주문 테이블: `mlangorder_printauto`

| 필드명 | 타입 | 용도 |
|--------|------|------|
| `no` | mediumint unsigned | PK, 주문번호 |
| `Type` | varchar(250) | 제품명 (한글) |
| `Type_1` | text | JSON 상세정보 |
| `money_4` | varchar(20) | 공급가액 |
| `money_5` | varchar(20) | VAT포함 총액 |
| `name` | varchar(250) | 주문자명 |
| `email` | text | 이메일 |
| `date` | datetime | 주문일시 |
| `OrderStyle` | varchar(100) | 주문상태 |
| `coating_enabled` | tinyint(1) | 코팅 여부 |
| `coating_type` | varchar(50) | 코팅 종류 |
| `coating_price` | int | 코팅 가격 |
| `folding_enabled` | tinyint(1) | 접지 여부 |
| `folding_type` | varchar(50) | 접지 종류 |
| `folding_price` | int | 접지 가격 |
| `creasing_enabled` | tinyint(1) | 오시 여부 |
| `creasing_lines` | int | 오시 줄수 |
| `creasing_price` | int | 오시 가격 |
| `premium_options` | text | JSON 프리미엄옵션 |
| `spec_type` | varchar(50) | 규격 종류 |
| `spec_material` | varchar(50) | 용지/재질 |
| `spec_size` | varchar(100) | 크기 |
| `quantity_value` | decimal(10,2) | 수량 값 |
| `quantity_unit` | varchar(10) | 단위 |
| `data_version` | tinyint | 데이터 버전 |

### 3.2 가격표 테이블 구조 (예: mlangprintauto_inserted)

| 필드명 | 용도 |
|--------|------|
| `quantity` | 연수 (0.5, 1, 2, ...) |
| `quantityTwo` | 매수 (2000, 4000, ...) |
| `paper_*` | 용지별 가격 |

---

## 4. 비즈니스 로직 (ACTUAL CODE)

### 4.1 수량 포맷팅 (SSOT)

**파일**: `/includes/QuantityFormatter.php`
**함수**: `QuantityFormatter::format()`

```php
// 실제 동작 로직
public static function format(float $value, string $unitCode, ?int $sheets = null): string {
    $unitName = self::UNIT_CODES[$unitCode] ?? '개';

    // 정수면 소수점 없이, 소수면 필요한 만큼만
    if (floor($value) == $value) {
        $formatted = number_format($value);
    } else {
        $formatted = rtrim(rtrim(number_format($value, 2), '0'), '.');
    }

    $display = $formatted . $unitName;

    // 연 단위이고 매수가 있으면 "(X매)" 추가
    if ($unitCode === 'R' && $sheets !== null && $sheets > 0) {
        $display .= ' (' . number_format($sheets) . '매)';
    }

    return $display;
}
```

**입출력 예시**:
| 입력 | 출력 |
|------|------|
| `format(1000, 'S')` | "1,000매" |
| `format(0.5, 'R', 2000)` | "0.5연 (2,000매)" |
| `format(10, 'B')` | "10부" |
| `format(5, 'V')` | "5권" |

### 4.2 전단지 매수 조회 (샛밥 방식)

**핵심 원칙**: **절대 계산하지 않음**, DB에서만 조회

```php
// 실제 코드: /includes/ProductSpecFormatter.php
private function lookupInsertedSheets(float $reams): int {
    $stmt = mysqli_prepare($this->db,
        "SELECT quantityTwo FROM mlangprintauto_inserted WHERE quantity = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "d", $reams);
    mysqli_stmt_execute($stmt);
    // ...
    return intval($row['quantityTwo']);  // 또는 0 (조회 실패 시)
}
```

**데이터 매핑** (mlangprintauto_inserted 테이블):
| quantity (연) | quantityTwo (매) |
|---------------|------------------|
| 0.5 | 2000 |
| 1 | 4000 |
| 2 | 8000 |
| 3 | 12000 |
| 5 | 20000 |

### 4.3 명함/봉투 천단위 변환

```php
// 실제 코드: /includes/QuantityFormatter.php:136-149
case 'namecard':
case 'envelope':
    if (!empty($data['mesu']) && $data['mesu'] != '0') {
        $value = intval($data['mesu']);
    } else {
        $amount = floatval($data['MY_amount'] ?? 0);
        if ($amount > 0 && $amount < 10) {
            $value = intval($amount * 1000);  // 1 → 1000
        } else {
            $value = intval($amount);
        }
    }
    break;
```

**변환 규칙**:
- `mesu` 필드가 있으면 → 그대로 사용
- `MY_amount < 10` → ×1000 (예: 1 → 1000매)
- `MY_amount >= 10` → 그대로 사용

### 4.4 가격 계산 (VAT)

```
공급가액 = money_4
VAT = money_5 - money_4
총액 = money_5

// VAT 계산: 공급가액의 10%
```

---

## 5. 데이터 흐름 (Data Flow)

### 5.1 주문 프로세스

```
1. 장바구니 (basket.php)
   └─ 세션에 상품 정보 저장

2. 주문서 작성 (OrderFormOrderTree.php)
   └─ 세션 데이터 → 주문서 폼 표시

3. 주문 처리 (ProcessOrder_unified.php)
   └─ POST 데이터 → mlangorder_printauto INSERT

4. 주문 완료 (OrderComplete_universal.php)
   └─ 주문번호로 DB 조회 → 완료 화면 표시
```

### 5.2 수량 데이터 흐름

```
┌─────────────────────────────────────────────┐
│  mlangprintauto_inserted (가격표)           │
│  quantity=0.5, quantityTwo=2000            │
└──────────────────┬──────────────────────────┘
                   │ lookupInsertedSheets()
                   ▼
┌─────────────────────────────────────────────┐
│  QuantityFormatter::format(0.5, 'R', 2000)  │
│  → "0.5연 (2,000매)"                        │
└──────────────────┬──────────────────────────┘
                   │
      ┌────────────┼────────────┐
      ▼            ▼            ▼
   장바구니      주문서       완료페이지
```

---

## 6. 옵션 체계 (ACTUAL)

### 6.1 코팅 옵션

| coating_type | 표시명 |
|--------------|--------|
| `single` | 단면유광코팅 |
| `double` | 양면유광코팅 |
| `single_matte` | 단면무광코팅 |
| `double_matte` | 양면무광코팅 |

### 6.2 접지 옵션

| folding_type | 표시명 |
|--------------|--------|
| `2fold` | 2단접지 |
| `3fold` | 3단접지 |
| `accordion` | 병풍접지 |
| `gate` | 대문접지 |

### 6.3 프리미엄 옵션 (JSON)

```json
{
    "foil_enabled": true,
    "foil_type": "gold_matte",
    "foil_price": 5000,
    "numbering_enabled": true,
    "numbering_type": "single",
    "numbering_price": 3000
}
```

---

## 7. 검증 체크리스트

### 7.1 코드 위치 검증

| 기능 | 파일 | 함수/메서드 |
|------|------|-------------|
| 수량 포맷팅 | QuantityFormatter.php | format() |
| 전단지 매수 조회 | ProductSpecFormatter.php | lookupInsertedSheets() |
| 주문 저장 | ProcessOrder_unified.php | (메인 로직) |
| 규격 표시 | ProductSpecFormatter.php | formatStandardized() |

### 7.2 필수 테스트 케이스

| 테스트 | 입력 | 기대 출력 |
|--------|------|-----------|
| TC-01 | 전단지 0.5연 | "0.5연 (2,000매)" |
| TC-02 | 명함 1000매 | "1,000매" |
| TC-03 | 카다록 10부 | "10부" |
| TC-04 | NCR 5권 | "5권" |

**검증 스크립트**: `/check/verify_data_lineage.php`

---

## 8. 금지 사항

### 8.1 절대 금지

1. **수량 계산 금지**: 전단지 매수는 반드시 DB 조회 (샛밥 방식)
2. **littleprint 이름 변경 금지**: 시스템 전체 오류 발생
3. **bind_param 개수 불일치**: 주문자명 '0' 저장 버그 발생

### 8.2 코드 규칙

```php
// ❌ 금지: 매수 계산
$sheets = $reams * 4000;

// ✅ 필수: DB 조회
$sheets = lookupInsertedSheets($reams);
```

---

## 부록 A: 헬퍼 함수

```php
// lib/core_print_logic.php
duson_format_qty(500, 'S');     // → "500매"
duson_lookup_sheets(0.5);       // → 2000
duson_get_unit('inserted');     // → "연"
```

---

*Document Version: 1.0*
*Generated: 2026-01-14*
*Method: Reverse Engineering from Production Code*
*Authority: This document reflects ACTUAL running code, not intentions*
