# API 명세서 (API Specification)

두손기획인쇄 시스템의 API 엔드포인트 명세서

**Version**: 1.0
**Last Updated**: 2026-01-17
**Base URL**: `http://localhost` (dev) / `http://dsp1830.shop` (prod)

---

## 목차

1. [가격 계산 API](#1-가격-계산-api)
2. [장바구니 API](#2-장바구니-api)
3. [주문 API](#3-주문-api)
4. [견적서 API](#4-견적서-api)
5. [갤러리/포트폴리오 API](#5-갤러리포트폴리오-api)
6. [공통 응답 형식](#6-공통-응답-형식)
7. [에러 코드](#7-에러-코드)

---

## 1. 가격 계산 API

각 품목별 가격을 계산하는 API. 모든 API는 `PriceCalculationService`를 통해 중앙 집중 처리됨.

### 1.1 전단지 (Inserted/Leaflet)

**Endpoint**: `GET /mlangprintauto/inserted/calculate_price_ajax.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| MY_type | string | ✅ | 규격 (A4, A5, B5 등) |
| Section | string | ✅ | 용지 종류 (스노우, 아트지 등) |
| POtype | string | ✅ | 도수 (1=단면, 2=양면) |
| MY_amount | string | ✅ | 수량 (연 단위: 0.5, 1, 2...) |
| ordertype | string | ✅ | 디자인 유형 (디자인의뢰, 직접입력) |
| premium_options_total | int | ❌ | 추가 옵션 총액 |

**Response**:
```json
{
  "success": true,
  "data": {
    "Price": 50000,
    "DS_Price": 10000,
    "Order_Price": 60000,
    "Additional_Options": "5,000",
    "PriceForm": "50,000",
    "DS_PriceForm": "10,000",
    "Order_PriceForm": "60,000",
    "Additional_Options_Form": 5000,
    "VAT_PriceForm": "6,500",
    "Total_PriceForm": "71,500",
    "StyleForm": "A4",
    "SectionForm": "스노우 150g",
    "QuantityForm": "1연 (4,000매)",
    "DesignForm": "디자인의뢰",
    "MY_amountRight": "4,000매"
  }
}
```

### 1.2 명함 (Namecard)

**Endpoint**: `GET /mlangprintauto/namecard/calculate_price_ajax.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| MY_type | string | ✅ | 규격 (90x50, 86x54) |
| Section | string | ✅ | 용지 (스노우화이트, 모조지 등) |
| POtype | string | ✅ | 도수 (1=단면, 2=양면) |
| MY_amount | string | ✅ | 수량 (1=1,000매, 2=2,000매...) |
| ordertype | string | ✅ | 디자인 유형 |
| premium_options_total | int | ❌ | 추가 옵션 총액 |

**Response**:
```json
{
  "success": true,
  "base_price": 15000,
  "design_price": 5000,
  "premium_total": 3000,
  "total_price": 23000,
  "total_with_vat": 25300,
  "display": {
    "size": "90×50mm",
    "material": "스노우화이트 250g",
    "quantity": "1,000매",
    "sides": "양면"
  }
}
```

### 1.3 스티커 (Sticker New)

**Endpoint**: `GET /mlangprintauto/sticker_new/calculate_price_ajax.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| MY_type | string | ✅ | 규격 ID |
| Section | string | ✅ | 재질 ID |
| MY_amount | string | ✅ | 수량 (매 단위) |
| coating_type | string | ❌ | 코팅 종류 (무광, 유광) |
| cutting_type | string | ❌ | 재단 종류 (사각, 도무송) |

### 1.4 카다록 (Cadarok)

**Endpoint**: `GET /mlangprintauto/cadarok/calculate_price_ajax.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| MY_type | string | ✅ | 규격 (A4, A5) |
| Section | string | ✅ | 용지 ID |
| POtype | string | ✅ | 도수 |
| MY_amount | string | ✅ | 수량 (부 단위) |
| page_count | int | ✅ | 페이지 수 |
| binding_type | string | ❌ | 제본 방식 |

### 1.5 봉투 (Envelope)

**Endpoint**: `GET /mlangprintauto/envelope/calculate_price_ajax.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| MY_type | string | ✅ | 봉투 규격 (소봉투, 대봉투) |
| Section | string | ✅ | 용지 종류 |
| POtype | string | ✅ | 인쇄 도수 |
| MY_amount | string | ✅ | 수량 (1=1,000매) |
| ordertype | string | ✅ | 디자인 유형 |

### 1.6 포스터 (Littleprint)

**Endpoint**: `GET /mlangprintauto/littleprint/calculate_price_ajax.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| MY_type | string | ✅ | 규격 (A1, A2, B1) |
| Section | string | ✅ | 용지 |
| MY_amount | string | ✅ | 수량 (매 단위) |
| coating | string | ❌ | 코팅 옵션 |

### 1.7 자석스티커 (Msticker)

**Endpoint**: `GET /mlangprintauto/msticker/calculate_price_ajax.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| MY_type | string | ✅ | 규격 ID |
| Section | string | ✅ | 재질 (자석시트) |
| MY_amount | string | ✅ | 수량 (매 단위) |
| cutting_type | string | ❌ | 재단 유형 |

### 1.8 상품권 (Merchandisebond)

**Endpoint**: `GET /mlangprintauto/merchandisebond/calculate_price_ajax.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| MY_type | string | ✅ | 상품권 규격 |
| Section | string | ✅ | 용지 |
| MY_amount | string | ✅ | 수량 (매 단위) |
| numbering | boolean | ❌ | 일련번호 여부 |

### 1.9 NCR양식지 (Ncrflambeau)

**Endpoint**: `GET /mlangprintauto/ncrflambeau/calculate_price_ajax.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| MY_type | string | ✅ | 규격 |
| Section | string | ✅ | NCR 용지 종류 |
| MY_amount | string | ✅ | 수량 (권 단위) |
| copy_count | int | ✅ | 복사 매수 (2매, 3매, 4매) |
| POtype | string | ✅ | 도수 |

---

## 2. 장바구니 API

### 2.1 장바구니 추가

**Endpoint**: `POST /mlangprintauto/{product}/add_to_basket.php`

**공통 Request Body**:
```json
{
  "action": "add_to_basket",
  "MY_type": "규격 ID",
  "Section": "용지/재질 ID",
  "POtype": "도수",
  "MY_amount": "수량",
  "ordertype": "디자인 유형",
  "price": 50000,
  "vat_price": 55000,
  "product_type": "inserted",
  "work_memo": "작업 메모",
  "upload_method": "upload",
  "additional_options": "{코팅/접지/오시 옵션 JSON}",
  "additional_options_total": 5000,
  "quantity_display": "1연 (4,000매)"
}
```

**파일 업로드** (multipart/form-data):
- `file[]`: 업로드 파일 (최대 10개)
- 지원 형식: jpg, jpeg, png, gif, pdf, ai, psd, eps, cdr

**Response**:
```json
{
  "success": true,
  "data": {
    "basket_id": 12345,
    "uploaded_files_count": 2,
    "img_folder": "cadarok/2026/01/17/abc123",
    "thing_cate": "Cadarok"
  },
  "message": "장바구니에 추가되었습니다."
}
```

### 2.2 장바구니 조회

**Endpoint**: `GET /mlangprintauto/shop/cart.php`

세션 기반 장바구니 페이지 (HTML 반환)

### 2.3 장바구니 삭제

**Endpoint**: `POST /mlangprintauto/shop/cart_delete.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| no | int | ✅ | 장바구니 항목 ID |

---

## 3. 주문 API

### 3.1 주문 처리

**Endpoint**: `POST /mlangorder_printauto/ProcessOrder_unified.php`

**Request Body**:
```json
{
  "cart_items": [1, 2, 3],
  "payment_method": "account",
  "receiver_name": "홍길동",
  "receiver_phone": "010-1234-5678",
  "receiver_addr": "서울시 강남구...",
  "receiver_memo": "배송 메모"
}
```

**Response**:
```json
{
  "success": true,
  "order_no": 20260117001,
  "redirect_url": "/mlangorder_printauto/OrderComplete_universal.php?no=20260117001"
}
```

### 3.2 재주문

**Endpoint**: `GET|POST /api/orders/reorder.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_no | int | ✅ | 원본 주문번호 |

**인증 필요**: 세션 로그인 상태 (본인 주문만 재주문 가능)

**Response**: JavaScript redirect to 새 주문 상세 페이지

### 3.3 주문 취소

**Endpoint**: `POST /api/orders/cancel.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_no | int | ✅ | 주문번호 |
| reason | string | ❌ | 취소 사유 |

---

## 4. 견적서 API

### 4.1 견적서 PDF 생성

**Endpoint**: `GET /api/generate_quotation_api.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_no | int | ✅ | 주문번호 |
| download | int | ❌ | 1=PDF 다운로드, 0=JSON 응답 (기본) |

**JSON Response** (download=0):
```json
{
  "success": true,
  "order_no": "12345",
  "customer": "홍길동",
  "pdf_size_kb": 125.5,
  "pdf_data": "JVBERi0xLjQK..." // Base64 encoded PDF
}
```

**PDF Download** (download=1):
- Content-Type: application/pdf
- Content-Disposition: attachment; filename="quotation_12345.pdf"

---

## 5. 갤러리/포트폴리오 API

### 5.1 포트폴리오 갤러리 조회

**Endpoint**: `GET /api/get_portfolio_gallery.php`

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| category | string | ❌ | all | 품목 카테고리 |
| page | int | ❌ | 1 | 페이지 번호 |
| per_page | int | ❌ | 24 | 페이지당 항목 수 (max: 50) |
| search | string | ❌ | | 검색어 |

**Available Categories**:
- `sticker`: 스티커
- `namecard`: 명함
- `leaflet`: 전단지
- `cadarok`: 카다록
- `envelope`: 봉투
- `littleprint`: 포스터
- `msticker`: 자석스티커
- `merchandisebond`: 상품권
- `ncrflambeau`: NCR양식지
- `all`: 전체

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "sticker_1",
      "title": "🏷️ 스티커 샘플 1",
      "category": "스티커",
      "detected_category": "sticker",
      "thumbnail": "/bbs/upload/portfolio/sample1.jpg",
      "full_image": "/bbs/upload/portfolio/sample1.jpg",
      "description": "🏷️ 스티커 샘플 - sample1.jpg",
      "tags": ["스티커", "sticker"],
      "upload_date": "2026-01-17 10:30:00",
      "file_size": 125000
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 24,
    "total_count": 100,
    "total_pages": 5,
    "has_next": true,
    "has_prev": false
  },
  "category": "sticker",
  "search": "",
  "available_categories": ["sticker", "namecard", "leaflet", ...]
}
```

### 5.2 전단지 샘플 조회

**Endpoint**: `GET /api/get_leaflet_samples.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| category | string | ❌ | 하위 카테고리 필터 |

### 5.3 스티커 갤러리 조회

**Endpoint**: `GET /api/get_sticker_gallery.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | int | ❌ | 페이지 번호 |
| per_page | int | ❌ | 페이지당 항목 수 |

### 5.4 갤러리 항목 조회

**Endpoint**: `GET /api/gallery_items.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| type | string | ✅ | 품목 타입 |
| page | int | ❌ | 페이지 번호 |

---

## 6. 공통 응답 형식

### 성공 응답
```json
{
  "success": true,
  "data": { ... },
  "message": "처리 완료 메시지"
}
```

### 실패 응답
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "에러 메시지"
  }
}
```

---

## 7. 에러 코드

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `INVALID_PARAMS` | 400 | 필수 파라미터 누락 또는 잘못된 형식 |
| `UNAUTHORIZED` | 401 | 인증 필요 (로그인 필요) |
| `NOT_FOUND` | 404 | 요청한 리소스 없음 |
| `DB_ERROR` | 500 | 데이터베이스 연결/쿼리 오류 |
| `UPLOAD_ERROR` | 500 | 파일 업로드 처리 오류 |
| `PRICE_CALC_ERROR` | 500 | 가격 계산 오류 |

---

## 8. 인증

### 세션 기반 인증
- 로그인: `POST /member/login.php`
- 로그아웃: `GET /member/logout.php`
- 세션 유효시간: 8시간

### 세션 체크 방식
```php
$is_logged_in = isset($_SESSION['user_id']) ||
                isset($_SESSION['id_login_ok']) ||
                isset($_COOKIE['id_login_ok']);
```

---

## 9. 파일 업로드 처리

### StandardUploadHandler

모든 제품의 파일 업로드는 `StandardUploadHandler` 클래스를 통해 처리됨.

**업로드 경로 규칙**:
```
/bbs/upload/{product}/{YYYY}/{MM}/{DD}/{unique_id}/
예: /bbs/upload/cadarok/2026/01/17/abc123def456/
```

**지원 파일 형식**:
- 이미지: jpg, jpeg, png, gif
- 디자인: pdf, ai, psd, eps, cdr

**파일 크기 제한**: 50MB per file

---

## 10. 단위 코드 체계

| Code | 단위 | 적용 제품 |
|------|------|----------|
| R | 연 | inserted, leaflet (전단지) |
| S | 매 | sticker, namecard, envelope, littleprint, msticker, merchandisebond |
| B | 부 | cadarok (카다록) |
| V | 권 | ncrflambeau (NCR양식지) |

### 수량 포맷팅 예시
- 전단지: `0.5연 (2,000매)`, `1연 (4,000매)`
- 명함: `1,000매`, `2,000매`
- 카다록: `100부`, `500부`
- NCR: `10권`, `20권`

---

## 11. API 호출 예시

### JavaScript (Fetch)
```javascript
// 가격 계산
const response = await fetch('/mlangprintauto/namecard/calculate_price_ajax.php?' + new URLSearchParams({
    MY_type: '1',
    Section: '2',
    POtype: '2',
    MY_amount: '1',
    ordertype: '디자인의뢰'
}));
const result = await response.json();

// 장바구니 추가 (FormData)
const formData = new FormData();
formData.append('action', 'add_to_basket');
formData.append('MY_type', '1');
formData.append('Section', '2');
formData.append('price', 15000);
formData.append('file[]', fileInput.files[0]);

const response = await fetch('/mlangprintauto/namecard/add_to_basket.php', {
    method: 'POST',
    body: formData
});
```

### cURL
```bash
# 가격 계산
curl "http://localhost/mlangprintauto/namecard/calculate_price_ajax.php?MY_type=1&Section=2&POtype=2&MY_amount=1&ordertype=디자인의뢰"

# 견적서 다운로드
curl -o quotation.pdf "http://localhost/api/generate_quotation_api.php?order_no=12345&download=1"
```

---

*API Spec Version: 1.0*
*Last Updated: 2026-01-17*
*Maintained by: Claude AI Assistant*
