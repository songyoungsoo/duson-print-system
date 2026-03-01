# 📋 정보수집 에이전트 (Collector Agent)

## 역할
인쇄 제품의 내부 데이터를 수집하고 구조화한다.
LLM 호출 없이 DB와 파일 시스템에서 직접 데이터를 추출한다.

## 입력
- `product_type`: 제품 코드 (예: `namecard`, `sticker_new`, `inserted`)

## 출력
- `product_brief.json`: 구조화된 제품 정보

## 수집 항목

### 1. 제품 기본 정보
- 제품명 (한국어/영어)
- 폴더명 (forced folder name)
- 단위 (매, 연, 부, 권)
- 가격 방식 (db_lookup / formula)

### 2. 옵션 데이터 (DB 조회)
- 용지 종류 (paper_types): `get_paper_types.php` 참조
- 사이즈 옵션: `get_sizes.php` 참조
- 수량 옵션: `get_quantities.php` 참조
- 후가공/프리미엄 옵션: `AdditionalOptions.php` 참조

### 3. 가격 데이터
- 최저가~최고가 범위
- 수량별 가격표 (대표 5개 구간)
- 스티커: formula 계산 예시 (재질×사이즈×수량)

### 4. 기존 콘텐츠 추출
- `explane_*.php`에서 기존 상세 텍스트 추출
- `ImgFolder/{product}/`에서 갤러리 이미지 목록
- `config/*.config.php`에서 제품 설정값

### 5. 배송 정보
- 제작 소요일 (기본 2~3일)
- 배송 방법 (택배/퀵/직접수령)
- 배송비 안내 (택배 착불 기본)

## 출력 JSON 스키마

```json
{
  "product": {
    "type": "namecard",
    "name_ko": "명함",
    "name_en": "Business Card",
    "folder": "namecard",
    "unit": "매",
    "pricing_method": "db_lookup"
  },
  "specs": {
    "paper_types": ["아트지 250g", "스노우지 300g"],
    "sizes": ["90×50mm", "90×55mm"],
    "quantities": [100, 200, 500, 1000, 2000],
    "premium_options": ["박가공", "형압", "에폭시", "귀돌이"]
  },
  "pricing": {
    "min_price": "3,000원",
    "max_price": "120,000원",
    "price_table": [
      {"qty": 100, "price": "3,000원"},
      {"qty": 200, "price": "5,500원"},
      {"qty": 500, "price": "11,000원"}
    ]
  },
  "existing_content": {
    "description": "기존 explane에서 추출한 텍스트",
    "gallery_images": ["img1.jpg", "img2.jpg"],
    "features": ["양면 컬러 인쇄", "다양한 용지 선택"]
  },
  "shipping": {
    "production_days": "2~3일",
    "delivery_methods": ["택배", "퀵", "직접수령"],
    "default_shipping": "택배 착불 (수취인 부담)",
    "prepaid_option": "선불 선택 시 택배비 별도 안내"
  },
  "site_info": {
    "company": "두손기획인쇄",
    "url": "dsp114.com",
    "phone": "02-2632-1830",
    "brand_color": "#2C5F8A"
  }
}
```

## 데이터 소스 매핑

| 데이터 | 소스 파일 |
|--------|----------|
| 용지 종류 | `mlangprintauto/{product}/get_paper_types.php` |
| 수량 옵션 | `mlangprintauto/{product}/get_quantities.php` |
| 사이즈 | `mlangprintauto/{product}/get_sizes.php` |
| 가격 | `mlangprintauto/{product}/calculate_price_ajax.php` |
| 제품 설명 | `mlangprintauto/{product}/explane_*.php` |
| 프리미엄 옵션 | `includes/AdditionalOptions.php` |
| 갤러리 이미지 | `ImgFolder/{product}/` |
| 제품 설정 | `mlangprintauto/{product}/config/*.config.php` |
