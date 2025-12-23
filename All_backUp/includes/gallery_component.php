<?php
/**
 * 갤러리 컴포넌트 v1.0
 * 픽셀 단위 정확한 렌더링: 썸네일 80×80, 메인 450×300
 * 
 * @param string $id 갤러리 고유 ID
 * @param array $items 이미지 아이템 배열
 * @param array $options 옵션 설정
 * @return string HTML 출력
 */

function render_gallery($id, $items, $options = []) {
    $defaults = [
        'product' => '',
        'thumbCols' => 4,
        'mainSize' => [500, 300],
        'thumbSize' => [80, 80],
        'enableModal' => true,
        'randomize' => true
    ];
    $opts = array_merge($defaults, $options);
    
    // 기능 플래그 체크
    if (!defined('GALLERY_UNIFIED') || GALLERY_UNIFIED !== true) {
        return '<!-- 통합 갤러리 비활성화 -->';
    }
    
    // 랜덤 셔플 (요구사항: 썸네일 무작위)
    if ($opts['randomize'] && count($items) > 4) {
        shuffle($items);
    }
    
    // 썸네일 4개만 선택 (5번째는 더보기 버튼용)
    $thumbnails = array_slice($items, 0, 4);
    
    // SVG 플레이스홀더 생성
    $placeholderSvg = 'data:image/svg+xml;base64,' . base64_encode('
        <svg xmlns="http://www.w3.org/2000/svg" width="500" height="300" viewBox="0 0 500 300">
            <rect width="500" height="300" fill="#f5f5f5" stroke="#ddd" stroke-width="2"/>
            <text x="250" y="140" font-family="Arial" font-size="16" fill="#999" text-anchor="middle">이미지 준비중</text>
            <text x="250" y="165" font-family="Arial" font-size="12" fill="#bbb" text-anchor="middle">Image Loading...</text>
        </svg>
    ');
    
    $thumbPlaceholderSvg = 'data:image/svg+xml;base64,' . base64_encode('
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">
            <rect width="80" height="80" fill="#f5f5f5" stroke="#ddd" stroke-width="1"/>
            <text x="40" y="45" font-family="Arial" font-size="10" fill="#999" text-anchor="middle">준비중</text>
        </svg>
    ');
    
    // 더보기 버튼용 SVG
    $moreBtnSvg = 'data:image/svg+xml;base64,' . base64_encode('
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">
            <rect width="80" height="80" fill="rgba(30, 78, 121, 0.8)" stroke="rgba(30, 78, 121, 1)" stroke-width="2" rx="8"/>
            <text x="40" y="28" font-family="Arial" font-size="14" fill="white" text-anchor="middle" font-weight="bold">샘플</text>
            <text x="40" y="48" font-family="Arial" font-size="14" fill="white" text-anchor="middle" font-weight="bold">더보기</text>
            <circle cx="40" cy="65" r="3" fill="white"/>
            <circle cx="32" cy="65" r="2" fill="rgba(255,255,255,0.7)"/>
            <circle cx="48" cy="65" r="2" fill="rgba(255,255,255,0.7)"/>
        </svg>
    ');
    
    $mainImage = $thumbnails[0] ?? ['src' => $placeholderSvg, 'alt' => '이미지 없음'];
    
    // 플레이스홀더 채우기 (4개 미만일 경우)
    while (count($thumbnails) < 4) {
        $thumbnails[] = ['src' => $thumbPlaceholderSvg, 'alt' => '이미지 준비중'];
    }
    
    ob_start();
    ?>
    <div id="<?php echo htmlspecialchars($id); ?>" class="gallery-container" data-product="<?php echo htmlspecialchars($opts['product']); ?>">
        <!-- 메인 이미지 영역: 500×300px -->
        <div class="gallery-main" style="width: <?php echo $opts['mainSize'][0]; ?>px; height: <?php echo $opts['mainSize'][1]; ?>px;">
            <img src="<?php echo htmlspecialchars($mainImage['src']); ?>" 
                 alt="<?php echo htmlspecialchars($mainImage['alt']); ?>"
                 class="gallery-main-img"
                 tabindex="0"
                 role="img"
                 aria-label="메인 이미지: <?php echo htmlspecialchars($mainImage['alt']); ?>">
        </div>
        
        <!-- 썸네일 그리드: 80×80px × 5개 (4개 썸네일 + 1개 더보기 버튼) -->
        <div class="gallery-thumbs" role="list" aria-label="썸네일 목록">
            <?php foreach ($thumbnails as $index => $thumb): ?>
            <button class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" 
                    data-src="<?php echo htmlspecialchars($thumb['src']); ?>"
                    data-alt="<?php echo htmlspecialchars($thumb['alt']); ?>"
                    style="width: <?php echo $opts['thumbSize'][0]; ?>px; height: <?php echo $opts['thumbSize'][1]; ?>px;"
                    role="listitem"
                    aria-label="썸네일 <?php echo $index + 1; ?>: <?php echo htmlspecialchars($thumb['alt']); ?>"
                    tabindex="0">
                <img src="<?php echo htmlspecialchars($thumb['src']); ?>" 
                     alt="<?php echo htmlspecialchars($thumb['alt']); ?>">
            </button>
            <?php endforeach; ?>
            
            <?php if ($opts['enableModal']): ?>
            <!-- 5번째 썸네일: 샘플 더보기 버튼 -->
            <button class="gallery-thumb gallery-more-thumb" 
                    data-gallery-id="<?php echo htmlspecialchars($id); ?>"
                    data-product="<?php echo htmlspecialchars($opts['product']); ?>"
                    style="width: <?php echo $opts['thumbSize'][0]; ?>px; height: <?php echo $opts['thumbSize'][1]; ?>px;"
                    role="listitem"
                    aria-label="샘플 더보기"
                    tabindex="0">
                <img src="<?php echo $moreBtnSvg; ?>" 
                     alt="샘플 더보기">
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * 갤러리 모달 렌더링 함수
 * 통합 갤러리 모달 포함
 */
function render_gallery_modal() {
    if (!defined('GALLERY_UNIFIED') || GALLERY_UNIFIED !== true) {
        return '';
    }
    
    // 통합 갤러리 모달 파일 포함
    ob_start();
    include __DIR__ . '/unified_gallery_modal.php';
    return ob_get_clean();
}
?>