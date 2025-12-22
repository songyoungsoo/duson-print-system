# CommonGallery 공통갤러리 시스템 사양서

## 📋 최신 사양 (2025-09-23 확정)

### 핵심 구조
- **5-썸네일 레이아웃**: 4개 실제 이미지 + 1개 더보기 버튼
- **제목 없음**: 갤러리 제목 표시 안함 (깔끔한 디자인)
- **메인 확대뷰**: 포스터 방식 backgroundImage 사용
- **더보기 기능**: 5번째 썸네일 위치에 내장된 더보기 버튼

### 레이아웃 사양

#### 썸네일 구성
```
[메인 확대 이미지 - 300px 높이]

[썸네일1] [썸네일2] [썸네일3] [썸네일4] [더보기버튼]
   80px      80px      80px      80px      80px
```

#### 더보기 버튼 사양
- **크기**: 썸네일과 동일 (80x80px)
- **아이콘**: 📂 폴더 아이콘 (24px)
- **텍스트**: "더보기" (10px, Noto Sans KR)
- **배경**: 그라데이션 (#f7fafc → #edf2f7)
- **호버**: 색상 변화 + 2px 상승 효과
- **기능**: 더보기 모달/페이지 연결 내장

### 기술 구조

#### PHP 호출 방식
```php
<?php
echo CommonGallery::render([
    'category' => 'product_name',
    'categoryLabel' => '제품명',
    'brandColor' => '#색상코드',
    'icon' => '🎨',
    'apiUrl' => '/api/get_real_orders_portfolio.php',
    'thumbnailCount' => 5,
    'containerId' => 'galleryId'
]);
?>
```

#### JavaScript 오버라이드
각 제품에서 5-썸네일 전용 함수 사용:
```javascript
// 제품별 갤러리 초기화 (5-썸네일)
async function init[Product]GalleryWith5Thumbs(containerId, category, categoryLabel)

// 5-썸네일 렌더링 (4개 이미지 + 1개 더보기 버튼)
function render[Product]GalleryWith5Thumbs(container, images, category, categoryLabel)
```

### CSS 클래스 구조

#### 메인 컨테이너
```css
.common-gallery-section    /* 전체 갤러리 섹션 */
.gallery-section          /* 갤러리 콘텐츠 영역 */
.proof-gallery           /* 포스터 방식 갤러리 */
```

#### 확대뷰
```css
.proof-large             /* 메인 이미지 컨테이너 */
.lightbox-viewer         /* 확대 이미지 (300px 높이) */
```

#### 썸네일
```css
.proof-thumbs            /* 썸네일 그리드 컨테이너 */
.thumb                   /* 개별 썸네일 (80x80px) */
.thumb.active           /* 선택된 썸네일 */
.more-button            /* 5번째 더보기 버튼 */
```

### 더보기 버튼 세부 사양

#### HTML 구조
```html
<div class="more-button" onclick="showMoreSamples('제품명')">
    <div class="folder-icon">📂</div>
    <div class="more-text">더보기</div>
</div>
```

#### CSS 스타일링
```css
.more-button {
    width: 80px;
    height: 80px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.more-button:hover {
    border-color: var(--brand-color, #4CAF50);
    transform: translateY(-2px);
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
}
```

### 파일 구조

#### 핵심 파일
```
includes/CommonGallery.php        # 메인 갤러리 시스템
css/gallery-common.css           # 갤러리 전용 CSS
api/get_real_orders_portfolio.php # 이미지 API
```

#### 제품별 구현
```
mlangprintauto/[제품]/index.php   # CommonGallery::render() 호출
mlangprintauto/[제품]/js/         # 5-썸네일 오버라이드 JavaScript
```

### API 호출 규격

#### 요청 형식
```
GET /api/get_real_orders_portfolio.php?category=[제품명]&per_page=5
```

#### 응답 형식
```json
{
    "success": true,
    "data": [
        {
            "id": "번호",
            "image_url": "이미지경로",
            "title": "제목",
            "description": "설명"
        }
    ],
    "total": 5
}
```

### 반응형 규격

#### 데스크톱 (>768px)
- 썸네일: 80x80px
- 메인뷰: 300px 높이
- 5개 썸네일 가로 배치

#### 모바일 (≤768px)
- 썸네일: 70x70px
- 메인뷰: 250px 높이
- 필요시 썸네일 줄바꿈

### 사용 가이드

#### 새 제품 갤러리 적용
1. `CommonGallery::render()` 호출 추가
2. 제품별 5-썸네일 JavaScript 함수 작성
3. CSS 브랜드 색상 설정
4. API 카테고리 매핑 확인

#### 기존 갤러리 마이그레이션
1. 인라인 갤러리 코드 제거 (평균 600+ 라인)
2. CommonGallery 호출로 대체
3. 제품별 커스텀 스타일링 적용
4. 기능 테스트 및 검증

### 표준 브랜드 색상
```css
스티커: #ff5722
명함: #2196F3
포스터: #4CAF50
전단지: #FF9800
봉투: #9C27B0
상품권: #F44336
```

## 🎯 핵심 특징

✅ **깔끔한 디자인**: 제목 없는 미니멀 갤러리
✅ **5-썸네일 표준**: 4개 이미지 + 1개 더보기 버튼
✅ **통합 시스템**: 모든 제품에서 동일한 구조 사용
✅ **반응형 지원**: 데스크톱/모바일 자동 최적화
✅ **확장 가능**: 새 제품 쉽게 추가 가능

## 🔄 마이그레이션 완료 제품

- ✅ 스티커 (sticker_new) - 5-썸네일 + 더보기 버튼 완료

## 📅 다음 적용 예정

- 명함 (namecard)
- 포스터 (littleprint)
- 전단지 (inserted)
- 기타 7개 제품