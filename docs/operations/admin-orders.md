## 🏢 관리자 주문 등록 (Admin Order Registration)

### 시스템 개요

전화/비회원 주문을 관리자가 대시보드에서 직접 등록하는 시스템.

| 항목 | 값 |
|------|-----|
| **UI** | `/dashboard/admin-order/index.php` |
| **API** | `/dashboard/api/admin-order.php` |
| **사이드바** | 📋 주문등록 (주문관리 그룹) |
| **DB 테이블** | `mlangorder_printauto` (기존 주문 테이블) |

### 주요 기능

- 품목 선택 → 카테고리 자동 로드 → 수량/사이즈 입력
- 주문자 정보 (이름, 전화, 이메일, 주소)
- 가격 수동 입력 (공급가액 + VAT 자동 계산)
- 배송방법/결제방법 선택
- 택배비 선불 지원 (운임구분 착불/선불 + 택배비 금액 입력)
- 요청사항 메모

### 택배비 선불 (2026-02-19)

배송방법 "택배" 선택 시 운임구분(착불/선불) 라디오 표시:
- **착불** (기본): 추가 입력 없음
- **선불**: 택배비 금액 입력란 표시 → DB `logen_fee_type`, `logen_delivery_fee` 저장
- 저장된 값은 `OrderFormOrderTree.php`에서 자동 표시 (기존 택배비 표시 로직 연동)

### 택배비 VAT 계산 (2026-02-19)

`dashboard/orders/view.php`에서 택배비 선불 금액을 공급가액으로 처리하여 VAT 10% 합산 표시:

```php
$shipping_supply = $logen_delivery_fee;           // 공급가액 (DB 저장값)
$shipping_vat = round($shipping_supply * 0.1);    // VAT 10%
$shipping_total = $shipping_supply + $shipping_vat; // 합계
```

**표시 형식**: `5,000+VAT 500 = 5,500원` (OrderFormOrderTree.php 패턴 통일)

**적용 위치**: 금액 정보 카드 + 결제 정보 카드 (2곳)

