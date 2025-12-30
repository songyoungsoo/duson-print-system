---
name: duson-gallery-system
description: 두손기획 인쇄 시스템의 갤러리 시스템 아키텍처. 품목별 메인 갤러리(4개 이미지), 썸네일, 모달 팝업 갤러리 구현 규칙을 정의합니다. 개인정보 보호 정책과 이미지 소스 우선순위를 포함합니다. Keywords: 두손기획, 갤러리, 이미지, 샘플, 교정, 모달, 팝업, 개인정보, 썸네일
---

# 두손기획 갤러리 시스템 아키텍처

## [핵심 개요]

### 시스템 구성 (3계층)

```
┌────────────────────────────────────────────────────────────────┐
│                    메인 갤러리 (Main Gallery)                   │
│ ┌────────────────────────────────────────────────────────────┐ │
│ │              메인 이미지 (500×400px, zoom 지원)              │ │
│ └────────────────────────────────────────────────────────────┘ │
│ ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐ ┌───────────┐                 │
│ │ 1   │ │ 2   │ │ 3   │ │ 4   │ │ 샘플더보기 │                 │
│ └─────┘ └─────┘ └─────┘ └─────┘ └───────────┘                 │
│   4개 썸네일 (클릭 시 메인 이미지 교체)     모달 팝업 트리거     │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│               모달 팝업 갤러리 (Window Center)                  │
│ ┌────────────────────────────────────────────────────────────┐ │
│ │                      24개 이미지 그리드                      │ │
│ │    (6열 × 4행, 페이지네이션 지원)                            │ │
│ └────────────────────────────────────────────────────────────┘ │
│                      [1] [2] [3] ... [N]                       │
└────────────────────────────────────────────────────────────────┘
```

---

## [이미지 소스 우선순위]

### 1단계: 갤러리 폴더 (Curated Images)
**경로**: `/ImgFolder/{product}/gallery/`
**특징**: 수작업으로 선별된 고품질 샘플 이미지

### 2단계: 고객 주문 파일 (Order Images)
**경로**: `/mlangorder_printauto/upload/{orderNo}/`
**조건**: `date >= '2022-01-01' AND date <= '2024-12-31'` (3년간 데이터)

### 품목별 갤러리 폴더 매핑

| 품목 | 갤러리 경로 | 비고 |
|------|------------|------|
| 명함 | `/ImgFolder/namecard/gallery/` | 🔒 개인정보 보호 |
| 전단지 | `/ImgFolder/inserted/gallery/` | - |
| 봉투 | `/ImgFolder/envelope/gallery/` | 🔒 개인정보 보호 |
| 스티커 | `/ImgFolder/sticker/gallery/` | - |
| 자석스티커 | `/ImgFolder/msticker/gallery/` | - |
| 카다록 | `/ImgFolder/cadarok/gallery/` + `/ImgFolder/leaflet/gallery/` | 다중 폴더 |
| 포스터 | `/ImgFolder/littleprint/gallery/` | - |
| 상품권 | `/ImgFolder/merchandisebond/gallery/` | - |
| 양식지 | `/ImgFolder/ncrflambeau/gallery/` | 🔒 개인정보 보호 |

---

## [개인정보 보호 정책] 🔒

### 보호 대상 품목

| 품목 | 이유 | 정책 |
|------|------|------|
| **명함** | 이름, 연락처, 주소 노출 | 갤러리 이미지만 사용 |
| **봉투** | 회사/개인 주소 노출 | 갤러리 이미지만 사용 |
| **양식지** | 거래 정보, 금액 노출 | 갤러리 이미지만 사용 |

### 구현 코드 (`proof_gallery.php`)

```php
// 🔒 개인정보 보호: 명함, 봉투, 양식지는 갤러리 이미지만 사용
$privacy_protected_categories = ['명함', '봉투', '양식지'];
$skip_db_query = in_array($cate, $privacy_protected_categories);

if ($skip_db_query) {
    // DB 쿼리 건너뜀 - 갤러리 폴더 이미지만 표시
} else {
    // 갤러리 + DB 주문 이미지 병합
}
```

---

## [핵심 파일 구조]

### PHP 백엔드

| 파일 | 역할 | 설명 |
|------|------|------|
| `popup/proof_gallery.php` | 모달 갤러리 | 24개/페이지, 페이지네이션, 라이트박스 |
| `includes/gallery_data_adapter.php` | 데이터 로더 | 품목별 이미지 로드, 우선순위 처리 |
| `includes/new_gallery_wrapper.php` | 메인 갤러리 | 500×400 뷰어, 4개 썸네일 렌더링 |
| `includes/simple_gallery_include.php` | 헬퍼 | 한 줄 include 패턴 |

### JavaScript 프론트엔드

| 파일 | 역할 | 설명 |
|------|------|------|
| `js/common-gallery-popup.js` | 모달 트리거 | window.open() 호출, 카테고리 매핑 |

### CSS 스타일

| 파일 | 역할 |
|------|------|
| `css/unified-gallery.css` | 갤러리 통합 스타일 |

---

## [메인 갤러리 구현]

### 제품 페이지에서 갤러리 포함

```php
// 제품 index.php (예: mlangprintauto/inserted/index.php)
$gallery_product = 'inserted';  // 품목 코드
if (file_exists('../../includes/simple_gallery_include.php')) {
    include '../../includes/simple_gallery_include.php';
}
```

### gallery_data_adapter.php 로더 등록

```php
function load_gallery_items($product, $dateFilter = null, $thumbCount = 4, $modalPerPage = 12) {
    // 품목별 분기 처리
    if ($product === 'inserted') {
        return load_inserted_gallery_unified($thumbCount, $modalPerPage);
    }
    if ($product === 'leaflet') {
        return load_leaflet_gallery_unified($thumbCount, $modalPerPage);
    }
    // ... 기타 품목

    // 기본: 샘플 폴더에서 로드
    return load_sample_gallery($product, $thumbCount);
}
```

### 이미지 배열 구조

```php
[
    'src' => '/ImgFolder/inserted/gallery/sample01.jpg',  // 이미지 URL
    'alt' => 'sample01',                                   // 대체 텍스트
    'title' => 'sample01',                                 // 제목
    'orderNo' => null,                                     // 주문번호 (갤러리면 null)
    'type' => 'gallery'                                    // 'gallery' 또는 'order'
]
```

---

## [모달 팝업 갤러리]

### 호출 방법

```javascript
// common-gallery-popup.js
const categoryMap = {
    'inserted': '전단지',
    'namecard': '명함',
    'envelope': '봉투',
    'sticker_new': '스티커',
    'msticker': '자석스티커',
    'cadarok': '카탈로그',
    'leaflet': '전단지',
    'littleprint': '포스터',
    'merchandisebond': '상품권',
    'ncrflambeau': '양식지'
};

function openGalleryPopup(productCode) {
    const category = categoryMap[productCode] || productCode;
    window.open(
        '/popup/proof_gallery.php?cate=' + encodeURIComponent(category),
        'gallery_popup',
        'width=1200,height=800,scrollbars=yes'
    );
}
```

### 페이지네이션 규칙

- **한 페이지**: 24개 이미지 (6열 × 4행)
- **정렬**: 갤러리 이미지 먼저 → DB 주문 이미지
- **날짜 필터**: 2022.01.01 ~ 2024.12.31 (3년)

### DB 타입 매핑 (`proof_gallery.php`)

```php
$type_mapping = [
    '명함' => ['NameCard'],
    '전단지' => ['전단지'],
    '스티커' => 'LIKE',  // 모든 스티커 변형 포함
    '상품권' => ['쿠폰', '상품권', '금액쿠폰'],
    '봉투' => ['봉투', '소봉투', '대봉투', '자켓봉투', '자켓소봉투', '중봉투', '창봉투'],
    '양식지' => ['NCR 양식지', '양식지', '거래명세서'],
    '카탈로그' => ['카다록', '카다로그', 'leaflet', 'cadarok'],
    '포스터' => ['포스터', 'LittlePrint', 'littleprint', 'poster', 'Poster'],
    '자석스티커' => 'LIKE'  // 모든 자석스티커 변형 포함
];
```

---

## [스티커 LIKE 검색 특수 처리]

### 문제
스티커는 37가지 이상 변형이 있음 (투명스티커, 유포지스티커, 스티카 오타 등)

### 해결

```php
if ($cate === '스티커') {
    // 스티커 + 스티카(오타) 포함, 자석스티커 제외
    $type_where = "((Type LIKE '%스티커%' OR Type LIKE '%스티카%') AND Type NOT LIKE '%자석%')";
}
```

---

## [디버그 모드]

### URL 파라미터

```
http://localhost/popup/proof_gallery.php?cate=스티커&debug=1
```

### 출력 예시

```html
<!-- DEBUG: Category = 스티커, Type WHERE = ((Type LIKE '%스티커%'...) -->
<!-- DEBUG: Privacy protected category '명함' - skipping DB query -->
<!-- DEBUG: Gallery images = 155 -->
<!-- DEBUG: Order images = 1478 -->
<!-- DEBUG: Total = 1633, Pages = 69 -->
```

---

## [현재 이미지 현황] (2025-12 기준)

### 갤러리 폴더 이미지 수

| 품목 | 갤러리 이미지 | 상태 |
|------|-------------|------|
| 명함 | 44개 | ✅ 충분 |
| 전단지 | 76개 | ✅ 충분 |
| 스티커 | 155개 | ✅ 충분 |
| 봉투 | 6개 | ⚠️ 보충 필요 |
| 포스터 | 1개 | 🔴 보충 필요 |
| 카다록 | 20개 | ✅ 적정 |
| 상품권 | 8개 | ⚠️ 보충 권장 |
| 자석스티커 | 5개 | ⚠️ 보충 권장 |
| 양식지 | 17개 | ✅ 적정 |

### 개선 필요 사항

1. **포스터**: `/ImgFolder/littleprint/gallery/`에 샘플 이미지 추가 필요
2. **봉투**: 갤러리 이미지 보충 필요 (현재 6개)
3. **sticker_new/gallery/**: 폴더가 비어 있음 - `/ImgFolder/sticker/gallery/` 사용

---

## [구현 체크리스트]

### 새 품목 갤러리 추가 시

- [ ] `/ImgFolder/{product}/gallery/` 폴더 생성
- [ ] 샘플 이미지 최소 10개 업로드
- [ ] `gallery_data_adapter.php`에 로더 함수 추가
- [ ] `proof_gallery.php`의 `$gallery_folders` 배열에 추가
- [ ] `proof_gallery.php`의 `$type_mapping` 배열에 DB 타입 추가
- [ ] `common-gallery-popup.js`의 `categoryMap`에 영문코드 → 한글 매핑 추가
- [ ] 개인정보 민감 품목이면 `$privacy_protected_categories`에 추가

### 갤러리 이미지 추가 시

- [ ] 지원 확장자: jpg, jpeg, png, gif, webp
- [ ] `/ImgFolder/{product}/gallery/` 폴더에 업로드
- [ ] 파일명에 한글/공백 가능 (rawurlencode 처리됨)
- [ ] 코드 수정 불필요 (자동 감지)

---

## [관련 문서]

- CLAUDE.md - 프로젝트 전체 규칙
- duson-print-rules SKILL.md - 수량 표기 및 옵션 규칙
- CLAUDE_DOCS/03_PRODUCTS/ - 제품별 상세 문서

---

## [식별된 개선사항] ⚠️

### 🔴 긴급 개선 필요

| 항목 | 현황 | 권장 조치 |
|------|------|----------|
| **포스터 갤러리** | 1개 이미지만 존재 | 최소 10개 샘플 추가 필요 |
| **sticker_new/gallery/** | 폴더 비어 있음 | `/ImgFolder/sticker/gallery/` 사용 또는 폴더 채움 |

### ⚠️ 권장 개선

| 항목 | 현황 | 권장 조치 |
|------|------|----------|
| **봉투 갤러리** | 6개 이미지 | 15~20개로 보충 권장 |
| **상품권 갤러리** | 8개 이미지 | 15개 이상으로 보충 권장 |
| **자석스티커 갤러리** | 5개 이미지 | 10개 이상으로 보충 권장 |

### 💡 시스템 개선 제안

1. **갤러리 폴더 자동 감지 개선**
   - 현재: 각 품목별로 하드코딩된 경로
   - 제안: `/ImgFolder/{product}/gallery/` 패턴 자동 감지

2. **이미지 메타데이터 캐싱**
   - 현재: 매 요청 시 디렉토리 스캔
   - 제안: Redis/파일 캐시로 목록 캐싱 (1시간 TTL)

3. **레이지 로딩 구현**
   - 현재: 모든 이미지 한 번에 로드
   - 제안: Intersection Observer API로 스크롤 시 로드

4. **WebP 자동 변환**
   - 현재: 원본 이미지 그대로 제공
   - 제안: on-the-fly WebP 변환으로 대역폭 절약

### 📋 유지보수 가이드

**갤러리 이미지 추가 방법**:
1. FTP로 `/ImgFolder/{product}/gallery/` 폴더에 업로드
2. 파일명: 한글/영문/숫자 모두 가능 (공백 OK)
3. 확장자: jpg, jpeg, png, gif, webp
4. 코드 수정 불필요 (자동 감지)

**개인정보 보호 품목 추가 방법**:
1. `proof_gallery.php` 파일 열기
2. `$privacy_protected_categories` 배열에 품목 추가
3. 예: `$privacy_protected_categories = ['명함', '봉투', '양식지', '새품목'];`

**새 품목 모달 갤러리 연동**:
1. `$gallery_folders` 배열에 폴더 경로 추가
2. `$type_mapping` 배열에 DB Type 매핑 추가
3. `common-gallery-popup.js`의 `categoryMap`에 영문코드 추가

---

*Last Updated: 2025-12-29*
*적용 범위: 두손기획 인쇄 시스템 갤러리 전체*
