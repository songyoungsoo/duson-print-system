# 🏗️ 통합 갤러리 시스템 아키텍처 가이드

## 📋 개요

전단지 페이지의 성공적인 갤러리 시스템을 바탕으로, 모든 제품 페이지에 일관된 갤러리 경험을 제공하기 위한 표준화 가이드입니다.

## 🎯 현재 전단지 시스템 분석 결과

### ✅ 검증된 워크플로우
```
메인 갤러리 썸네일 → "더 많은 샘플 보기" 버튼 → 통합 팝업 갤러리 → 개별 이미지 라이트박스
```

### 🔧 핵심 컴포넌트
- **메인 갤러리**: 4개 썸네일 + 큰 이미지 미리보기
- **팝업 갤러리**: `unified_gallery_modal.php` (통합 모달)
- **API 시스템**: `/api/get_real_orders_portfolio.php` (실제 주문 데이터)
- **라이트박스**: 개별 이미지 확대 보기

---

## 🏗️ 표준 아키텍처 패턴

### 1. 디렉토리 구조
```
MlangPrintAuto/
├── [제품명]/
│   ├── index.php                    # 메인 제품 페이지
│   ├── css/[제품명]-compact.css     # 제품별 스타일
│   └── js/[제품명].js               # 제품별 로직 (선택사항)
├── components/
│   ├── ProofGalleryInline.php       # 재사용 갤러리 컴포넌트
│   └── product_config.php           # 제품별 설정
├── includes/
│   ├── unified_gallery_modal.php    # 통합 팝업 갤러리
│   └── js/UnifiedGallery.js         # 갤러리 JavaScript 라이브러리
└── api/
    └── get_real_orders_portfolio.php # 통합 API 엔드포인트
```

### 2. HTML 표준 템플릿

#### 메인 갤러리 구조
```html
<section class="[product]-gallery" aria-label="[제품명] 샘플 갤러리">
    <div class="gallery-section">
        <!-- 갤러리 제목 -->
        <div class="gallery-title">
            🖼️ [제품명] 샘플 갤러리
        </div>
        
        <!-- 갤러리 콘텐츠 -->
        <div id="[product]Gallery">
            <?php 
            $galleryConfig = [
                'category' => '[category_code]',
                'title' => '[제품명]',
                'icon' => '[emoji]',
                'hover_system' => 'poster' // 또는 'lightbox', 'zoom'
            ];
            include $_SERVER['DOCUMENT_ROOT']."/components/ProofGalleryInline.php"; 
            ?>
            
            <!-- 더 많은 샘플 보기 버튼 -->
            <button 
                class="btn-primary"
                onclick="openUnifiedModal('[제품명]', '[emoji]')"
                aria-label="더 많은 샘플 보기"
            >
                <span aria-hidden="true">📂</span> 더 많은 샘플 보기
            </button>
        </div>
    </div>
</section>
```

### 3. JavaScript 표준 패턴

#### 초기화 로직
```javascript
document.addEventListener('DOMContentLoaded', function() {
    console.log('[제품명] 갤러리 시스템 초기화');
    
    // 1. 메인 갤러리 로드
    load[Product]Gallery();
    
    // 2. 통합 팝업 갤러리는 unified_gallery_modal.php에서 자동 처리
});

async function load[Product]Gallery() {
    try {
        const response = await fetch('/api/get_real_orders_portfolio.php?category=[category]&per_page=4');
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            render[Product]Gallery(data.data);
        } else {
            renderPlaceholderGallery();
        }
    } catch (error) {
        console.error('[제품명] 갤러리 로드 실패:', error);
        renderPlaceholderGallery();
    }
}
```

### 4. CSS 브랜드 시스템

#### 제품별 브랜드 색상 변수
```css
/* 제품별 브랜드 색상 정의 */
:root {
    --leaflet-primary: #4caf50;      /* 전단지 - 그린 */
    --leaflet-secondary: #45a049;
    
    --namecard-primary: #2196f3;     /* 명함 - 블루 */
    --namecard-secondary: #1976d2;
    
    --sticker-primary: #ff9800;      /* 스티커 - 오렌지 */
    --sticker-secondary: #f57c00;
    
    --envelope-primary: #9c27b0;     /* 봉투 - 퍼플 */
    --envelope-secondary: #7b1fa2;
    
    --cadarok-primary: #8bc34a;      /* 카탈로그 - 라이트 그린 */
    --cadarok-secondary: #689f38;
    
    --poster-primary: #ff5722;       /* 포스터 - 딥 오렌지 */
    --poster-secondary: #d84315;
    
    --msticker-primary: #00bcd4;     /* 자석스티커 - 시안 */
    --msticker-secondary: #0097a7;
    
    --ncrflambeau-primary: #607d8b;  /* 양식지 - 블루 그레이 */
    --ncrflambeau-secondary: #455a64;
    
    --merchandisebond-primary: #e91e63; /* 상품권 - 핑크 */
    --merchandisebond-secondary: #c2185b;
}

/* 표준 갤러리 스타일 */
.[product]-gallery .gallery-title {
    background: linear-gradient(135deg, var(--[product]-primary) 0%, var(--[product]-secondary) 100%);
    color: white;
    padding: 18px 20px;
    margin: -25px -25px 20px -25px;
    border-radius: 15px 15px 0 0;
    font-size: 1.1rem;
    font-weight: 600;
    text-align: center;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.9);
}

.[product]-gallery .gallery-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.9);
}
```

---

## 🔧 제품별 구현 가이드

### 1. 제품 설정 파일 생성

#### `/components/product_config.php`
```php
<?php
return [
    'inserted' => [
        'title' => '전단지',
        'icon' => '📄',
        'category' => 'inserted',
        'primary_color' => '#4caf50',
        'secondary_color' => '#45a049',
        'hover_system' => 'poster'
    ],
    'namecard' => [
        'title' => '명함',
        'icon' => '💼',
        'category' => 'namecard',
        'primary_color' => '#2196f3',
        'secondary_color' => '#1976d2',
        'hover_system' => 'lightbox'
    ],
    'sticker' => [
        'title' => '스티커',
        'icon' => '🏷️',
        'category' => 'sticker',
        'primary_color' => '#ff9800',
        'secondary_color' => '#f57c00',
        'hover_system' => 'zoom'
    ],
    'envelope' => [
        'title' => '봉투',
        'icon' => '✉️',
        'category' => 'envelope',
        'primary_color' => '#9c27b0',
        'secondary_color' => '#7b1fa2',
        'hover_system' => 'lightbox'
    ],
    'cadarok' => [
        'title' => '카탈로그',
        'icon' => '📖',
        'category' => 'cadarok',
        'primary_color' => '#8bc34a',
        'secondary_color' => '#689f38',
        'hover_system' => 'poster'
    ],
    'littleprint' => [
        'title' => '포스터',
        'icon' => '🎨',
        'category' => 'littleprint',
        'primary_color' => '#ff5722',
        'secondary_color' => '#d84315',
        'hover_system' => 'poster'
    ],
    'msticker' => [
        'title' => '자석스티커',
        'icon' => '🧲',
        'category' => 'msticker',
        'primary_color' => '#00bcd4',
        'secondary_color' => '#0097a7',
        'hover_system' => 'zoom'
    ],
    'ncrflambeau' => [
        'title' => '양식지',
        'icon' => '📋',
        'category' => 'ncrflambeau',
        'primary_color' => '#607d8b',
        'secondary_color' => '#455a64',
        'hover_system' => 'lightbox'
    ],
    'merchandisebond' => [
        'title' => '상품권',
        'icon' => '🎫',
        'category' => 'merchandisebond',
        'primary_color' => '#e91e63',
        'secondary_color' => '#c2185b',
        'hover_system' => 'lightbox'
    ]
];
?>
```

### 2. 재사용 갤러리 컴포넌트

#### `/components/ProofGalleryInline.php`
```php
<?php
$productConfigs = include __DIR__ . '/product_config.php';
$currentProduct = $galleryConfig['category'] ?? 'inserted';
$config = $productConfigs[$currentProduct];
?>

<div class="proof-gallery">
    <!-- 큰 이미지 영역 -->
    <div class="proof-large">
        <?php if ($config['hover_system'] === 'poster'): ?>
            <div class="lightbox-viewer" id="posterZoomBox"></div>
        <?php else: ?>
            <img id="mainProofImage" src="" alt="<?= $config['title'] ?> 샘플" />
        <?php endif; ?>
    </div>
    
    <!-- 썸네일 영역 -->
    <div class="proof-thumbs" id="proofThumbs">
        <!-- JavaScript로 동적 생성 -->
    </div>
</div>

<script>
// 제품별 갤러리 로드 함수 자동 생성
window.load<?= ucfirst($currentProduct) ?>Gallery = async function() {
    try {
        const response = await fetch('/api/get_real_orders_portfolio.php?category=<?= $currentProduct ?>&per_page=4');
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            render<?= ucfirst($currentProduct) ?>Gallery(data.data);
        } else {
            renderPlaceholderGallery('<?= $config['title'] ?>');
        }
    } catch (error) {
        console.error('<?= $config['title'] ?> 갤러리 로드 실패:', error);
        renderPlaceholderGallery('<?= $config['title'] ?>');
    }
};
</script>
```

---

## 🚀 구현 단계별 가이드

### Phase 1: 기존 제품 페이지 표준화
1. **명함 페이지** - 현재 UnifiedGallery 시스템을 표준 패턴으로 마이그레이션
2. **봉투 페이지** - 갤러리 추가 및 표준 패턴 적용
3. **스티커 페이지** - 복잡한 계산기와 갤러리 통합

### Phase 2: 신규 제품 페이지 적용
4. **카탈로그 페이지** - 표준 패턴으로 갤러리 구현
5. **포스터 페이지** - 전단지와 유사한 포스터 호버 시스템
6. **양식지 페이지** - 현재 시스템을 표준 패턴으로 리팩토링

### Phase 3: 고급 기능 추가
7. **성능 최적화** - 이미지 레이지 로딩, WebP 변환
8. **접근성 강화** - 키보드 네비게이션, 스크린 리더 지원
9. **모바일 최적화** - 터치 제스처, 반응형 그리드

---

## 📊 성과 지표 및 품질 기준

### KPI 측정
- **로딩 속도**: 갤러리 로드 < 2초
- **사용자 참여도**: 팝업 갤러리 오픈율 > 25%
- **모바일 호환성**: 모든 기기에서 정상 작동
- **접근성 점수**: WCAG 2.1 AA 수준 달성

### 코드 품질 기준
- **재사용성**: 80% 이상 공통 코드 활용
- **유지보수성**: 제품 추가 시 < 1시간 구현
- **성능**: Lighthouse 성능 점수 > 90점
- **호환성**: IE11+ 브라우저 지원

---

## 🔄 유지보수 및 확장 가이드

### 새 제품 추가 시
1. `product_config.php`에 설정 추가
2. CSS 변수로 브랜드 색상 정의
3. 제품 페이지에 표준 HTML 템플릿 적용
4. API 카테고리 매핑 확인

### 기능 개선 시
1. `unified_gallery_modal.php`에서 공통 기능 수정
2. 제품별 특화 기능은 개별 CSS/JS로 처리
3. API 변경 시 모든 제품에 일괄 적용
4. 버전 관리를 통한 롤백 지원

---

## 📝 결론

전단지 페이지의 검증된 갤러리 시스템을 바탕으로 한 이 아키텍처는:

- **🏗️ 확장성**: 새 제품 추가 시 최소 코드 변경
- **🔧 유지보수성**: 중앙집중식 컴포넌트 관리
- **🎨 일관성**: 모든 제품에 동일한 UX 제공
- **⚡ 성능**: 검증된 최적화 패턴 활용

이를 통해 모든 제품 페이지에서 **일관되고 우수한 갤러리 경험**을 제공할 수 있습니다.