<?php
/**
 * 새 갤러리 래퍼 - 기존 데이터/모달 사용 + 새 줌 기능
 *
 * 기존 시스템 활용:
 * - gallery_data_adapter.php: 이미지 로드
 * - unified_gallery_modal.php: 샘플 더보기 모달
 * - gallery-system.js: 모달 제어
 *
 * 새 기능:
 * - 500×400 고정 메인 컨테이너
 * - 200% 마우스 트래킹 줌
 * - 0.5초 부드러운 전환
 */

/**
 * 새 갤러리 렌더링 (기존 데이터 사용)
 */
function render_new_gallery_with_existing_data($product) {
    // 기존 데이터 어댑터 사용
    if (!function_exists('load_gallery_items')) {
        include_once __DIR__ . '/gallery_data_adapter.php';
    }

    // 기존 방식으로 이미지 로드
    $items = load_gallery_items($product, null, 4, 12);

    if (empty($items)) {
        return '<p>샘플 이미지가 없습니다.</p>';
    }

    // 썸네일 4개 선택
    $thumbnails = array_slice($items, 0, 4);
    $mainImage = $thumbnails[0] ?? ['src' => '', 'alt' => '이미지 없음'];

    $galleryId = "gallery-" . $product;

    // 기존 샘플 더보기 버튼 SVG
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

    ob_start();
    ?>
    <style>
    /* 새 갤러리 스타일 v2.0 */
    .new-gallery-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        margin: 20px 0;
    }

    .new-main-container {
        width: 500px;
        height: 400px;
        max-width: 100%;
        overflow: hidden;
        position: relative;
        border: 1px solid #ccc;
        border-radius: 8px;
        cursor: zoom-in;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f5f5f5;
    }

    .new-main-container img {
        width: auto;
        height: 400px;
        max-width: 100%;
        object-fit: contain;
        transition: transform 0.5s ease;
        transform-origin: center center;
    }

    .new-main-container[data-zoom="true"] {
        cursor: zoom-out;
    }

    .new-main-container[data-zoom="true"] img {
        transform: scale(2);
    }

    .new-gallery-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        max-width: 500px;
    }

    .new-thumbnails {
        display: flex;
        gap: 8px;
        flex: 1;
    }

    .new-thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border: 2px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .new-thumbnail:hover {
        border-color: #4CAF50;
        transform: scale(1.05);
    }

    .new-thumbnail.active {
        border-color: #4CAF50;
        box-shadow: 0 0 8px rgba(76, 175, 80, 0.5);
    }

    .new-btn-view-more {
        width: 80px;
        height: 80px;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .new-btn-view-more:hover {
        transform: scale(1.05);
        opacity: 0.9;
    }

    .new-btn-view-more img {
        width: 100%;
        height: 100%;
        display: block;
    }

    /* 반응형 디자인 */
    @media (max-width: 768px) {
        .new-gallery-wrapper {
            margin: 0;
            margin-bottom: 0;
            gap: 8px;
        }

        .new-main-container {
            width: 100%;
            height: 300px;
        }

        .new-main-container img {
            height: 300px;
        }

        .new-thumbnail {
            width: 60px;
            height: 60px;
        }

        .new-btn-view-more {
            width: 60px;
            height: 60px;
        }
    }
    </style>

    <!-- 새 갤러리 래퍼 -->
    <div class="new-gallery-wrapper" id="<?php echo $galleryId; ?>">
        <!-- 메인 이미지 컨테이너 (500×400 고정) -->
        <div class="new-main-container" data-zoom="false">
            <img
                src="<?php echo htmlspecialchars($mainImage['src']); ?>"
                alt="<?php echo htmlspecialchars($mainImage['alt']); ?>"
                class="new-main-image"
                id="<?php echo $galleryId; ?>-main"
            >
        </div>

        <!-- 썸네일 + 더보기 버튼 -->
        <div class="new-gallery-controls">
            <!-- 썸네일 리스트 -->
            <div class="new-thumbnails">
                <?php foreach ($thumbnails as $index => $thumb): ?>
                <img
                    src="<?php echo htmlspecialchars($thumb['src']); ?>"
                    alt="<?php echo htmlspecialchars($thumb['alt']); ?>"
                    class="new-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                    data-full="<?php echo htmlspecialchars($thumb['src']); ?>"
                    onclick="changeNewMainImage(this, '<?php echo $galleryId; ?>')"
                >
                <?php endforeach; ?>
            </div>

            <!-- 샘플 더보기 버튼 (기존 디자인 + 기존 모달) -->
            <button
                type="button"
                class="new-btn-view-more gallery-more-thumb"
                data-product="<?php echo htmlspecialchars($product); ?>"
            >
                <img src="<?php echo $moreBtnSvg; ?>" alt="샘플 더보기">
            </button>
        </div>
    </div>

    <script>
    // 메인 이미지 변경 함수
    function changeNewMainImage(thumbnail, galleryId) {
        const mainImg = document.getElementById(galleryId + '-main');
        const fullImageUrl = thumbnail.getAttribute('data-full');

        // 줌 상태 초기화
        const container = thumbnail.closest('.new-gallery-wrapper').querySelector('.new-main-container');
        container.setAttribute('data-zoom', 'false');

        // 이미지 변경
        mainImg.src = fullImageUrl;
        mainImg.alt = thumbnail.alt;

        // 활성 썸네일 표시
        const allThumbs = thumbnail.closest('.new-thumbnails').querySelectorAll('.new-thumbnail');
        allThumbs.forEach(t => t.classList.remove('active'));
        thumbnail.classList.add('active');
    }

    // 줌 기능 초기화 (마우스 오버 방식)
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.new-main-container');

        containers.forEach(container => {
            const img = container.querySelector('img');

            // 마우스 진입 시 줌 활성화
            container.addEventListener('mouseenter', function() {
                // 이미지가 로드되지 않았으면 무시
                if (!img.complete || img.naturalHeight === 0) return;
                this.setAttribute('data-zoom', 'true');
            });

            // 마우스 이동 시 줌 위치 조정
            container.addEventListener('mousemove', function(e) {
                if (this.getAttribute('data-zoom') === 'true') {
                    const rect = this.getBoundingClientRect();
                    const x = ((e.clientX - rect.left) / rect.width) * 100;
                    const y = ((e.clientY - rect.top) / rect.height) * 100;
                    img.style.transformOrigin = `${x}% ${y}%`;
                }
            });

            // 마우스 아웃 시 줌 해제
            container.addEventListener('mouseleave', function() {
                this.setAttribute('data-zoom', 'false');
                img.style.transformOrigin = 'center center';
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

/**
 * 원클릭 갤러리 포함 (기존 시스템 통합)
 */
function include_new_gallery_integrated($product) {
    // 갤러리 시스템 활성화
    if (!defined('GALLERY_UNIFIED')) {
        define('GALLERY_UNIFIED', true);
    }

    // 새 갤러리 렌더링 (기존 데이터 사용)
    echo render_new_gallery_with_existing_data($product);

    // 기존 모달 포함 (한 번만)
    if (!defined('GALLERY_MODAL_INCLUDED')) {
        if (function_exists('render_gallery_modal')) {
            echo render_gallery_modal();
        } else {
            // 직접 포함
            include_once __DIR__ . '/unified_gallery_modal.php';
        }
        define('GALLERY_MODAL_INCLUDED', true);
    }
}
?>
