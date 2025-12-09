# 갤러리 시스템 공통화 적용 가이드 v2.0

## 📋 개요

전단지(inserted) 갤러리의 성공한 패턴을 모든 품목에 적용하는 가이드입니다.

## 🏗️ 아키텍처

### 핵심 구조
```
전단지 갤러리 패턴 → 공통 컴포넌트 → 각 품목 적용
- API 기반 실제 주문 데이터
- 포스터 방식 호버 확대  
- 통합 모달 팝업
- 품목별 브랜드 색상
```

### 파일 구조
```
C:\xampp\htdocs\
├── includes/
│   ├── CommonGallery.php          # 메인 컴포넌트
│   ├── js/CommonGalleryAPI.js     # API 라이브러리
│   └── unified_gallery_modal.php  # 통합 모달 (기존)
├── css/
│   └── unified-gallery.css        # 통합 스타일 (기존)
└── api/
    └── get_real_orders_portfolio.php # API 엔드포인트 (기존)
```

## 🎯 품목별 적용 방법

### 1. 명함 (NameCard) 적용

**파일**: `MlangPrintAuto/NameCard/index.php`

**1단계: PHP 파일 상단에 추가**
```php
// 공통 갤러리 컴포넌트 포함
include "../../includes/CommonGallery.php";
```

**2단계: 기존 갤러리 HTML 대체**
```php
// 기존 갤러리 HTML 주석 처리하고 아래 코드 추가
echo CommonGallery::render([
    'category' => 'namecard',
    'categoryLabel' => '명함',
    'brandColor' => '#2196f3',
    'icon' => '💳',
    'containerId' => 'namecardGallery'
]);
```

**3단계: CSS/JS 포함**
```php
// head 섹션에 추가
echo CommonGallery::renderCSS();
echo CommonGallery::renderScript();
```

**4단계: API 라이브러리 포함**
```html
<!-- 공통 갤러리 API -->
<script src="../../includes/js/CommonGalleryAPI.js"></script>
```

### 2. 봉투 (Envelope) 적용

**파일**: `MlangPrintAuto/envelope/index.php`

```php
echo CommonGallery::render([
    'category' => 'envelope',
    'categoryLabel' => '봉투', 
    'brandColor' => '#ff9800',
    'icon' => '✉️',
    'containerId' => 'envelopeGallery'
]);
```

### 3. 포스터 (LittlePrint) 적용

**파일**: `MlangPrintAuto/LittlePrint/index.php`

```php
echo CommonGallery::render([
    'category' => 'littleprint',
    'categoryLabel' => '포스터',
    'brandColor' => '#9c27b0', 
    'icon' => '🖼️',
    'containerId' => 'posterGallery'
]);
```

### 4. 카탈로그 (Cadarok) 적용

**파일**: `MlangPrintAuto/cadarok/index.php`

```php
echo CommonGallery::render([
    'category' => 'cadarok',
    'categoryLabel' => '카탈로그',
    'brandColor' => '#795548',
    'icon' => '📚',
    'containerId' => 'catalogGallery'
]);
```

### 5. 상품권 (MerchandiseBond) 적용

**파일**: `MlangPrintAuto/MerchandiseBond/index.php`

```php
echo CommonGallery::render([
    'category' => 'merchandisebond',
    'categoryLabel' => '상품권',
    'brandColor' => '#f44336',
    'icon' => '🎫',
    'containerId' => 'voucherGallery'
]);
```

### 6. 양식지 (NcrFlambeau) 적용

**파일**: `MlangPrintAuto/NcrFlambeau/index.php`

```php
echo CommonGallery::render([
    'category' => 'ncrflambeau',
    'categoryLabel' => '양식지',
    'brandColor' => '#607d8b',
    'icon' => '📄',
    'containerId' => 'formGallery'
]);
```

### 7. 자석스티커 (MSticker) 적용

**파일**: `MlangPrintAuto/msticker/index.php`

```php
echo CommonGallery::render([
    'category' => 'msticker',
    'categoryLabel' => '자석스티커',
    'brandColor' => '#e91e63',
    'icon' => '🧲',
    'containerId' => 'magnetGallery'
]);
```

## 🔧 고급 설정 옵션

### 커스텀 설정 예시
```php
echo CommonGallery::render([
    'category' => 'namecard',
    'categoryLabel' => '명함',
    'brandColor' => '#2196f3',
    'icon' => '💳',
    'apiUrl' => '/custom/api/endpoint.php',    // 커스텀 API
    'thumbnailCount' => 6,                     // 썸네일 개수 변경
    'containerId' => 'customGallery'           // 고유 ID
]);
```

### 브랜드 색상 가이드

| 품목 | 메인 색상 | 설명 |
|------|----------|------|
| 전단지 | `#4caf50` | 초록 (기존) |
| 명함 | `#2196f3` | 파랑 |
| 봉투 | `#ff9800` | 주황 |
| 포스터 | `#9c27b0` | 보라 |
| 카탈로그 | `#795548` | 브라운 |
| 상품권 | `#f44336` | 빨강 |
| 양식지 | `#607d8b` | 블루그레이 |
| 자석스티커 | `#e91e63` | 핑크 |

## 📱 반응형 지원

공통 컴포넌트는 자동으로 반응형을 지원합니다:

- **데스크톱**: 메인 이미지 300px 높이, 썸네일 4개 그리드
- **태블릿**: 메인 이미지 250px 높이, 썸네일 간격 조정
- **모바일**: 메인 이미지 200px 높이, 썸네일 최적화

## 🔗 기존 시스템과의 호환성

### 1. 통합 모달 연동
```javascript
// 더보기 버튼 클릭 시 자동으로 통합 모달 호출
openUnifiedModal('명함', '💳');
```

### 2. API 호환성
```javascript
// 기존 API 엔드포인트 그대로 사용
/api/get_real_orders_portfolio.php?category=namecard&per_page=4
```

### 3. DB 스키마 호환성
- `MlangOrder_PrintAuto` 테이블 기반
- `Type` 필드로 품목 분류  
- `ThingCate` 필드로 이미지 파일명
- 기존 업로드 경로 유지

## 🚀 단계별 통합 가이드

### Phase 1: 핵심 품목 (우선순위 높음)
1. **명함** (NameCard) - 가장 사용 빈도 높음
2. **봉투** (Envelope) - 기존 갤러리 기술 적용됨
3. **포스터** (LittlePrint) - 포스터 호버 기술 원조

### Phase 2: 확장 품목 (우선순위 보통) 
4. **카탈로그** (Cadarok)
5. **상품권** (MerchandiseBond) 
6. **자석스티커** (MSticker)

### Phase 3: 추가 품목 (우선순위 낮음)
7. **양식지** (NcrFlambeau)
8. **일반스티커** (Sticker) - 별도 계산 시스템으로 인해 마지막

## ⚙️ 테스트 체크리스트

각 품목 적용 후 확인사항:

- [ ] 썸네일 4개 정상 로드
- [ ] 메인 이미지 포스터 방식 호버 확대
- [ ] 브랜드 색상 올바르게 적용
- [ ] 더보기 버튼 → 통합 모달 열림
- [ ] 모바일에서 정상 동작
- [ ] API 에러 시 플레이스홀더 표시

## 🛠️ 문제 해결

### 일반적인 문제
1. **이미지 로드 실패**: API 엔드포인트 확인
2. **브랜드 색상 미적용**: CSS 변수 확인
3. **호버 효과 없음**: JavaScript 라이브러리 로드 확인

### 디버깅 도구
```javascript
// 브라우저 콘솔에서 디버깅
console.log('갤러리 상태:', window.commonGalleryAPI);
window.commonGalleryAPI.clearCache(); // 캐시 초기화
```

## 📚 참고 자료

- **전단지 갤러리 원본**: `MlangPrintAuto/inserted/index.php`
- **포스터 호버 기술**: 포스터 갤러리의 배경 이미지 확대 방식
- **통합 모달**: `includes/unified_gallery_modal.php`
- **API 명세**: `api/get_real_orders_portfolio.php`

---

*이 가이드는 전단지 갤러리의 성공한 패턴을 모든 품목에 일관되게 적용하여, 사용자 경험을 향상시키고 유지보수성을 개선합니다.*