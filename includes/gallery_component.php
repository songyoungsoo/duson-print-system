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
        'mainSize' => [450, 300],
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
    
    // 썸네일 4개만 선택
    $thumbnails = array_slice($items, 0, 4);
    
    // SVG 플레이스홀더 생성
    $placeholderSvg = 'data:image/svg+xml;base64,' . base64_encode('
        <svg xmlns="http://www.w3.org/2000/svg" width="450" height="300" viewBox="0 0 450 300">
            <rect width="450" height="300" fill="#f5f5f5" stroke="#ddd" stroke-width="2"/>
            <text x="225" y="140" font-family="Arial" font-size="16" fill="#999" text-anchor="middle">이미지 준비중</text>
            <text x="225" y="165" font-family="Arial" font-size="12" fill="#bbb" text-anchor="middle">Image Loading...</text>
        </svg>
    ');
    
    $thumbPlaceholderSvg = 'data:image/svg+xml;base64,' . base64_encode('
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">
            <rect width="80" height="80" fill="#f5f5f5" stroke="#ddd" stroke-width="1"/>
            <text x="40" y="45" font-family="Arial" font-size="10" fill="#999" text-anchor="middle">준비중</text>
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
        <!-- 메인 이미지 영역: 450×300px -->
        <div class="gallery-main" style="width: <?php echo $opts['mainSize'][0]; ?>px; height: <?php echo $opts['mainSize'][1]; ?>px;">
            <img src="<?php echo htmlspecialchars($mainImage['src']); ?>" 
                 alt="<?php echo htmlspecialchars($mainImage['alt']); ?>"
                 class="gallery-main-img"
                 tabindex="0"
                 role="img"
                 aria-label="메인 이미지: <?php echo htmlspecialchars($mainImage['alt']); ?>">
        </div>
        
        <!-- 썸네일 그리드: 80×80px × 4개 -->
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
        </div>
        
        <?php if ($opts['enableModal']): ?>
        <!-- 더 많은 샘플 보기 버튼 -->
        <button class="gallery-more-btn" 
                data-gallery-id="<?php echo htmlspecialchars($id); ?>"
                data-product="<?php echo htmlspecialchars($opts['product']); ?>"
                aria-label="더 많은 샘플 보기">
            더 많은 샘플 보기
        </button>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * 갤러리 모달 렌더링 함수
 * 1200×800px 정중앙 팝업
 */
function render_gallery_modal() {
    if (!defined('GALLERY_UNIFIED') || GALLERY_UNIFIED !== true) {
        return '';
    }
    
    ob_start();
    ?>
    <div id="gallery-modal" class="gallery-modal" style="display: none;">
        <div class="gallery-modal-backdrop"></div>
        <div class="gallery-modal-content" style="width: 1200px; height: 800px;">
            <div class="gallery-modal-header">
                <h2 id="gallery-modal-title">갤러리</h2>
                <button class="gallery-modal-close" aria-label="닫기">&times;</button>
            </div>
            <div class="gallery-modal-body">
                <div class="gallery-modal-grid" id="modal-gallery-grid">
                    <!-- AJAX로 동적 로드 -->
                </div>
            </div>
            <div class="gallery-modal-footer">
                <div class="gallery-pagination">
                    <button class="pagination-prev" aria-label="이전 페이지">&laquo;</button>
                    <span class="pagination-info">
                        <span class="current-page">1</span> / <span class="total-pages">1</span>
                    </span>
                    <button class="pagination-next" aria-label="다음 페이지">&raquo;</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>