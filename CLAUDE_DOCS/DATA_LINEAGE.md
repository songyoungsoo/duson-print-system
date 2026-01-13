# Data Lineage: 수량 데이터 흐름도

두손기획인쇄 Grand Design 아키텍처의 데이터 흐름 문서

## 1. 수량 데이터 흐름 (Quantity Data Flow)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           DATA LINEAGE DIAGRAM                              │
│                        수량 데이터 단일 진실 공급원                            │
└─────────────────────────────────────────────────────────────────────────────┘

                    ┌──────────────────────────────────┐
                    │     mlangprintauto_inserted     │
                    │    (레거시 가격표 - REFERENCE)    │
                    │  ┌────────────┬───────────────┐ │
                    │  │  quantity  │  quantityTwo  │ │
                    │  │  (연수)    │  (매수)       │ │
                    │  │    0.5     │     2000      │ │
                    │  │    1.0     │     4000      │ │
                    │  └────────────┴───────────────┘ │
                    └──────────────────────────────────┘
                                    │
                                    │ lookupInsertedSheets()
                                    │ (샛밥 방식 - DB 조회 Only)
                                    ▼
┌───────────────────────────────────────────────────────────────────────────────┐
│                            SSOT: QuantityFormatter                            │
│                         lib/core_print_logic.php                              │
│  ┌─────────────────────────────────────────────────────────────────────────┐  │
│  │  format($value, $unitCode, $sheets)                                     │  │
│  │  ─────────────────────────────────────────────────────────────────────  │  │
│  │  Input:  value=0.5, unitCode='R', sheets=2000                          │  │
│  │  Output: "0.5연 (2,000매)"                                              │  │
│  └─────────────────────────────────────────────────────────────────────────┘  │
└───────────────────────────────────────────────────────────────────────────────┘
                                    │
                    ┌───────────────┼───────────────────┐
                    │               │                   │
                    ▼               ▼                   ▼
    ┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐
    │   장바구니 페이지   │ │   주문서 페이지   │ │   주문완료 페이지  │
    │   (OrderForm)     │ │ (ProcessOrder)   │ │ (OrderComplete)  │
    │                   │ │                  │ │                  │
    │ ProductSpec       │ │ OrderFormOrder   │ │ OrderComplete    │
    │ Formatter         │ │ Tree.php         │ │ _universal.php   │
    │ .formatQuantity() │ │                  │ │                  │
    └──────────────────┘ └──────────────────┘ └──────────────────┘
                    │               │                   │
                    └───────────────┼───────────────────┘
                                    │
                                    ▼
                    ┌──────────────────────────────────┐
                    │         관리자 페이지             │
                    │    admin/mlangprintauto/admin   │
                    │                                  │
                    │  SpecDisplayService              │
                    │  .ensureQuantityUnit()           │
                    └──────────────────────────────────┘
```

## 2. 핵심 원칙

### 2.1 SSOT (Single Source of Truth)
- **저장**: qty_value (DECIMAL) + qty_unit_code (CHAR) + qty_sheets (INT)
- **표시**: QuantityFormatter::format() 함수 호출
- **절대 금지**: quantity_display를 직접 저장하거나 계산

### 2.2 샛밥 방식 (Separate DB Lookup)
```php
// ✅ 올바른 방식: DB에서 매수 조회
$sheets = lookupInsertedSheets($reams);  // mlangprintauto_inserted 조회

// ❌ 금지된 방식: 매수 계산
$sheets = $reams * 4000;  // 계산 금지!
```

### 2.3 단위 코드 체계
| 코드 | 단위 | 제품 |
|------|------|------|
| R | 연 | inserted, leaflet |
| S | 매 | sticker_new, namecard, envelope, littleprint, merchandisebond |
| B | 부 | cadarok |
| V | 권 | ncrflambeau |
| E | 개 | 기타 |

## 3. 파일별 역할

### 3.1 Core SSOT Files
| 파일 | 역할 | 데이터 흐름 |
|------|------|-------------|
| `lib/core_print_logic.php` | 중앙 진입점 | 모든 클래스 로드 |
| `includes/QuantityFormatter.php` | 수량 포맷팅 SSOT | format() 함수 |
| `includes/ProductSpecFormatter.php` | 제품 사양 표시 | formatQuantity() → QuantityFormatter |
| `includes/SpecDisplayService.php` | 사양 조회 서비스 | ensureQuantityUnit() |

### 3.2 Consumer Files (SSOT 사용)
| 파일 | 용도 | 호출 함수 |
|------|------|----------|
| `mlangorder_printauto/OrderFormOrderTree.php` | 주문서 트리 | lookupInsertedSheets() |
| `mlangorder_printauto/ProcessOrder_unified.php` | 주문 처리 | - |
| `mlangorder_printauto/OrderComplete_universal.php` | 주문 완료 | ProductSpecFormatter |

## 4. 검증 체크포인트

### 4.1 장바구니 → 주문서
```
✅ 체크: MY_amount + mesu → quantity_display 일관성
✅ 경로: ProductSpecFormatter::formatQuantity()
✅ 기대값: "0.5연 (2,000매)"
```

### 4.2 주문서 → DB 저장
```
✅ 체크: quantity_display 저장 시 단위 포함 여부
✅ 경로: ProcessOrder_unified.php
✅ 기대값: DB에 "0.5연 (2,000매)" 저장
```

### 4.3 DB → 주문완료/관리자
```
✅ 체크: 조회 후 단위 보정 여부
✅ 경로: SpecDisplayService::ensureQuantityUnit()
✅ 기대값: 단위 없으면 자동 보정
```

## 5. Cross-Check 테스트 케이스

### TC-01: 전단지 0.5연 표시 일관성
```
위치: 장바구니, 주문서, 주문완료, 관리자
입력: MY_amount=0.5, quantityTwo=2000
기대: "0.5연 (2,000매)"
```

### TC-02: 명함 1,000매 표시
```
위치: 장바구니, 주문서, 주문완료, 관리자
입력: MY_amount=1 (천단위 변환), mesu=1000
기대: "1,000매"
```

### TC-03: 스티커 100매 표시
```
위치: 장바구니, 주문서, 주문완료, 관리자
입력: mesu=100
기대: "100매"
```

---

*Last Updated: 2026-01-13*
*Version: 1.0.0*
