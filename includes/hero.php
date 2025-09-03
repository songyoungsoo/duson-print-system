<?php
/**
 * DSP Hero Slider Component
 * /slide/ 폴더의 이미지를 자동으로 스캔하여 슬라이더 생성
 */

// 설정값
$hero_config = [
    'slide_dir' => $_SERVER['DOCUMENT_ROOT'] . '/slide/',
    'slide_url' => '/slide/',
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'cache_buster' => true, // 캐시 버스터 사용 여부
    'fallback_images' => [ // 기본 이미지가 없을 때 사용할 대체 이미지
        '/images/placeholder.jpg',
        '/images/default-slide.jpg'
    ]
];

/**
 * 슬라이드 이미지 스캔 함수
 */
function scanSlideImages($config) {
    $images = [];
    
    // 디렉토리 존재 확인
    if (!is_dir($config['slide_dir'])) {
        error_log("DSP Hero: Slide directory not found: " . $config['slide_dir']);
        return $images;
    }
    
    try {
        $files = scandir($config['slide_dir']);
        
        if ($files === false) {
            error_log("DSP Hero: Failed to scan slide directory");
            return $images;
        }
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $filename = pathinfo($file, PATHINFO_FILENAME);
            
            if (in_array($extension, $config['allowed_extensions'])) {
                $images[] = [
                    'filename' => $file,
                    'name' => $filename,
                    'url' => $config['slide_url'] . $file,
                    'path' => $config['slide_dir'] . $file,
                    'extension' => $extension,
                    'alt' => generateAltText($filename)
                ];
            }
        }
        
        // 파일명 순으로 정렬
        usort($images, function($a, $b) {
            return strcasecmp($a['filename'], $b['filename']);
        });
        
    } catch (Exception $e) {
        error_log("DSP Hero: Error scanning images - " . $e->getMessage());
    }
    
    return $images;
}

/**
 * 파일명 기반 ALT 텍스트 생성
 */
function generateAltText($filename) {
    // 파일명 패턴에 따른 한국어 ALT 텍스트 매핑
    $alt_mapping = [
        'slide_inserted' => '전단지 인쇄 서비스',
        'slide__Sticker' => '스티커 인쇄 서비스',
        'slide__Sticker_2' => '스티커 제작 서비스 2',
        'slide__Sticker_3' => '스티커 제작 서비스 3',
        'slide_cadarok' => '카다록 인쇄 서비스',
        'slide_Ncr' => 'NCR 양식지 인쇄 서비스',
        'slide__poster' => '포스터 인쇄 서비스'
    ];
    
    // 정확한 매칭 먼저 시도
    if (isset($alt_mapping[$filename])) {
        return $alt_mapping[$filename];
    }
    
    // 패턴 매칭
    foreach ($alt_mapping as $pattern => $alt) {
        if (strpos($filename, str_replace('slide_', '', $pattern)) !== false) {
            return $alt;
        }
    }
    
    // 기본값: 파일명을 읽기 쉽게 변환
    $clean_name = str_replace(['_', '-'], ' ', $filename);
    return ucfirst($clean_name) . ' 인쇄 서비스';
}

/**
 * 캐시 버스터 생성
 */
function getCacheBuster() {
    return '?v=' . filemtime(__FILE__) . '_' . rand(1000, 9999);
}

// 이미지 스캔 실행
$slide_images = scanSlideImages($hero_config);

// 캐시 버스터
$cache_buster = $hero_config['cache_buster'] ? getCacheBuster() : '';
?>

<!-- DSP Hero Slider Section -->
<section class="relative w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (empty($slide_images)): ?>
        <!-- 빈 상태 UI -->
        <div class="dsp-hero" role="region" aria-label="슬라이드쇼 영역">
            <div class="dsp-hero__empty">
                <div class="dsp-hero__empty-icon">📷</div>
                <div class="dsp-hero__empty-text">슬라이드 이미지가 없습니다</div>
                <div class="dsp-hero__empty-subtext">
                    /slide/ 폴더에 이미지 파일을 추가해주세요<br>
                    <small>지원 형식: JPG, PNG, GIF, WebP</small>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- 히어로 슬라이더 -->
        <div class="dsp-hero" 
             role="region" 
             aria-label="제품 소개 슬라이드쇼"
             data-autoplay-interval="<?php echo $hero_config['autoplay_interval'] ?? 2000; ?>">
            
            <!-- 슬라이더 컨테이너 -->
            <div class="dsp-hero__slider">
                <?php foreach ($slide_images as $index => $image): ?>
                    <div class="dsp-hero__slide <?php echo $index === 0 ? 'is-active' : ''; ?>" 
                         role="tabpanel" 
                         aria-label="슬라이드 <?php echo $index + 1; ?> / <?php echo count($slide_images); ?>"
                         tabindex="<?php echo $index === 0 ? '0' : '-1'; ?>">
                        
                        <img class="dsp-hero__image" 
                             src="<?php echo $index === 0 ? htmlspecialchars($image['url'] . $cache_buster) : ''; ?>"
                             <?php if ($index > 0): ?>
                                 data-src="<?php echo htmlspecialchars($image['url'] . $cache_buster); ?>"
                             <?php endif; ?>
                             alt="<?php echo htmlspecialchars($image['alt']); ?>"
                             <?php if ($index === 0): ?>
                                 loading="eager"
                             <?php else: ?>
                                 loading="lazy"
                             <?php endif; ?>
                             width="1200" 
                             height="600">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($slide_images) > 1): ?>
                <!-- 네비게이션 화살표 -->
                <button class="dsp-hero__nav dsp-hero__nav--prev" 
                        aria-label="이전 슬라이드" 
                        type="button">
                    &#8249;
                </button>
                
                <button class="dsp-hero__nav dsp-hero__nav--next" 
                        aria-label="다음 슬라이드" 
                        type="button">
                    &#8250;
                </button>
                
                <!-- 도트 인디케이터 -->
                <div class="dsp-hero__dots" role="tablist" aria-label="슬라이드 선택">
                    <?php foreach ($slide_images as $index => $image): ?>
                        <button class="dsp-hero__dot <?php echo $index === 0 ? 'is-active' : ''; ?>" 
                                role="tab"
                                aria-label="<?php echo htmlspecialchars($image['alt']); ?> 보기"
                                <?php echo $index === 0 ? 'aria-current="true"' : ''; ?>
                                data-slide="<?php echo $index; ?>"
                                type="button">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 첫 번째 이미지 프리로드 -->
        <?php if (isset($slide_images[0])): ?>
            <link rel="preload" as="image" href="<?php echo htmlspecialchars($slide_images[0]['url'] . $cache_buster); ?>">
        <?php endif; ?>
    <?php endif; ?>
</section>

<!-- 제품 카드 그리드 섹션 -->
<section class="dsp-cards">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 섹션 헤더 -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-slate-800 mb-4">주요 인쇄 서비스</h2>
            <p class="text-lg text-slate-600">사무용에 최적화된 전문 인쇄 솔루션</p>
        </div>
        
        <!-- 카드 그리드 -->
        <div class="dsp-cards__grid">
            <!-- 스티커 -->
            <div class="dsp-cards__item" onclick="window.location.href='/mlangprintauto/sticker_new/index.php'">
                <div class="dsp-cards__header" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);">
                    <h3>스티커</h3>
                </div>
                <div class="dsp-cards__content">
                    <div class="dsp-cards__title">스티커</div>
                    <div class="dsp-cards__desc">브랜드 홍보와 제품 라벨링에 최적. 다양한 재질과 사이즈로 맞춤 제작</div>
                    <a href="/mlangprintauto/sticker_new/index.php" class="dsp-cards__link">
                        견적받기 <span class="dsp-cards__arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 전단지 -->
            <div class="dsp-cards__item" onclick="window.location.href='/mlangprintauto/inserted/index.php'">
                <div class="dsp-cards__header" style="background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);">
                    <h3>전단지</h3>
                </div>
                <div class="dsp-cards__content">
                    <div class="dsp-cards__title">전단지·리플렛</div>
                    <div class="dsp-cards__desc">마케팅 캠페인과 행사 홍보용. 소량부터 대량까지 합리적인 가격</div>
                    <a href="/mlangprintauto/inserted/index.php" class="dsp-cards__link">
                        견적받기 <span class="dsp-cards__arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 자석스티커 -->
            <div class="dsp-cards__item" onclick="window.location.href='/mlangprintauto/msticker/index.php'">
                <div class="dsp-cards__header" style="background: linear-gradient(135deg, #2196F3 0%, #1976d2 100%);">
                    <h3>자석스티커</h3>
                </div>
                <div class="dsp-cards__content">
                    <div class="dsp-cards__title">자석스티커</div>
                    <div class="dsp-cards__desc">냉장고나 철제 표면에 부착 가능. 생활 밀착형 광고 매체</div>
                    <a href="/mlangprintauto/msticker/index.php" class="dsp-cards__link">
                        견적받기 <span class="dsp-cards__arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 명함 -->
            <div class="dsp-cards__item" onclick="window.location.href='/mlangprintauto/namecard/index.php'">
                <div class="dsp-cards__header" style="background: linear-gradient(135deg, #9C27B0 0%, #7b1fa2 100%);">
                    <h3>명함</h3>
                </div>
                <div class="dsp-cards__content">
                    <div class="dsp-cards__title">명함</div>
                    <div class="dsp-cards__desc">비즈니스 첫인상을 좌우하는 필수 아이템. 프리미엄 용지와 후가공</div>
                    <a href="/mlangprintauto/namecard/index.php" class="dsp-cards__link">
                        견적받기 <span class="dsp-cards__arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 상품권 -->
            <div class="dsp-cards__item" onclick="window.location.href='/mlangprintauto/merchandisebond/index.php?page=MerchandiseBond'">
                <div class="dsp-cards__header" style="background: linear-gradient(135deg, #E91E63 0%, #c2185b 100%);">
                    <h3>상품권</h3>
                </div>
                <div class="dsp-cards__content">
                    <div class="dsp-cards__title">상품권</div>
                    <div class="dsp-cards__desc">고객 충성도 프로그램과 프로모션용. 넘버링과 미싱 옵션 제공</div>
                    <a href="/mlangprintauto/merchandisebond/index.php?page=MerchandiseBond" class="dsp-cards__link">
                        견적받기 <span class="dsp-cards__arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 봉투 -->
            <div class="dsp-cards__item" onclick="window.location.href='/mlangprintauto/envelope/index.php?page=envelope'">
                <div class="dsp-cards__header" style="background: linear-gradient(135deg, #795548 0%, #5d4037 100%);">
                    <h3>봉투</h3>
                </div>
                <div class="dsp-cards__content">
                    <div class="dsp-cards__title">봉투</div>
                    <div class="dsp-cards__desc">공식 문서와 우편물 발송용. 다양한 크기와 용지 옵션</div>
                    <a href="/mlangprintauto/envelope/index.php?page=envelope" class="dsp-cards__link">
                        견적받기 <span class="dsp-cards__arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 양식지 -->
            <div class="dsp-cards__item" onclick="window.location.href='/mlangprintauto/ncrflambeau/index.php?page=ncrflambeau'">
                <div class="dsp-cards__header" style="background: linear-gradient(135deg, #607D8B 0%, #455a64 100%);">
                    <h3>양식지</h3>
                </div>
                <div class="dsp-cards__content">
                    <div class="dsp-cards__title">양식지</div>
                    <div class="dsp-cards__desc">전표, 영수증, 거래명세서 등. NCR 복사지와 넘버링 지원</div>
                    <a href="/mlangprintauto/ncrflambeau/index.php?page=ncrflambeau" class="dsp-cards__link">
                        견적받기 <span class="dsp-cards__arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 카다록 -->
            <div class="dsp-cards__item" onclick="window.location.href='/mlangprintauto/cadarok/index.php'">
                <div class="dsp-cards__header" style="background: linear-gradient(135deg, #8BC34A 0%, #689f38 100%);">
                    <h3>카다록</h3>
                </div>
                <div class="dsp-cards__content">
                    <div class="dsp-cards__title">카다록</div>
                    <div class="dsp-cards__desc">제품 소개 및 회사 홍보용 책자. 다양한 제본 방식 선택</div>
                    <a href="/mlangprintauto/cadarok/index.php" class="dsp-cards__link">
                        견적받기 <span class="dsp-cards__arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 포스터 -->
            <div class="dsp-cards__item" onclick="window.location.href='/mlangprintauto/littleprint/index_compact.php?page=LittlePrint'">
                <div class="dsp-cards__header" style="background: linear-gradient(135deg, #FF5722 0%, #d84315 100%);">
                    <h3>포스터</h3>
                </div>
                <div class="dsp-cards__content">
                    <div class="dsp-cards__title">포스터</div>
                    <div class="dsp-cards__desc">행사, 전시, 인테리어용 대형 인쇄물. 고화질 출력으로 선명한 품질</div>
                    <a href="/mlangprintauto/littleprint/index_compact.php?page=LittlePrint" class="dsp-cards__link">
                        견적받기 <span class="dsp-cards__arrow">→</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- 일괄 주문 버튼 -->
        <div class="text-center mt-12">
            <a href="/shop/cart.php" 
               class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-sky-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-sky-600 hover:to-indigo-700 transition-all duration-300 hover:-translate-y-1 shadow-lg">
                🛒 장바구니에서 일괄 주문
            </a>
        </div>
    </div>
</section>

<?php
// 개발 모드에서 디버그 정보 출력
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo "<!-- DSP Hero Debug: Found " . count($slide_images) . " slide images -->\n";
    foreach ($slide_images as $i => $img) {
        echo "<!-- Slide $i: {$img['filename']} - {$img['alt']} -->\n";
    }
}
?>

<style>
/* 인라인 스타일로 즉시 적용되는 기본 스타일 */
.dsp-hero {
    position: relative;
    width: 100%;
    height: 66vh;
    min-height: 400px;
    overflow: hidden;
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    background: #f8fafc;
}

@media (max-width: 768px) {
    .dsp-hero { height: 50vh; border-radius: 16px; }
}

@media (max-width: 640px) {
    .dsp-hero { height: 40vh; }
}
</style>