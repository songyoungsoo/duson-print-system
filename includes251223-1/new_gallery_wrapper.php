<?php
/**
 * 갤러리 렌더링 함수
 * gallery_data_adapter.php의 데이터를 사용하여 HTML 생성
 */

// gallery_data_adapter.php 로드 (load_gallery_items 함수 정의)
require_once __DIR__ . '/gallery_data_adapter.php';

function render_new_gallery_with_existing_data($product) {
    // gallery_data_adapter.php에서 이미지 데이터 로드
    $items = load_gallery_items($product, null, 4, 12);

    if (empty($items)) {
        return '<div class="gallery-container"><p>표시할 이미지가 없습니다.</p></div>';
    }

    // 첫 번째 이미지를 메인으로 사용
    $mainImage = $items[0];

    ob_start();
    ?>
    <div class="gallery-container">
        <div class="lightbox-viewer">
            <img id="mainGalleryImage"
                 src="<?php echo htmlspecialchars($mainImage['src']); ?>"
                 alt="<?php echo htmlspecialchars($mainImage['alt']); ?>"
                 style="width: 100%; height: 100%; object-fit: contain; cursor: zoom-in;">
        </div>
        <div class="thumbnail-strip">
            <?php foreach ($items as $index => $item): ?>
                <img src="<?php echo htmlspecialchars($item['src']); ?>"
                     alt="<?php echo htmlspecialchars($item['alt']); ?>"
                     class="<?php echo $index === 0 ? 'active' : ''; ?>"
                     onclick="changeMainGalleryImage('<?php echo htmlspecialchars($item['src'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($item['alt'], ENT_QUOTES); ?>', this)">
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    function changeMainGalleryImage(src, alt, thumbnail) {
        const mainImage = document.getElementById('mainGalleryImage');
        if (mainImage) {
            mainImage.src = src;
            mainImage.alt = alt;
        }

        // 썸네일 active 클래스 변경
        const thumbnails = document.querySelectorAll('.thumbnail-strip img');
        thumbnails.forEach(thumb => thumb.classList.remove('active'));
        if (thumbnail) {
            thumbnail.classList.add('active');
        }
    }
    </script>
    <?php

    return ob_get_clean();
}
?>
