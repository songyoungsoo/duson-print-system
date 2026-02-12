<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="mb-4">
            <h1 class="text-lg font-bold text-gray-900">제품 관리</h1>
            <p class="mt-1 text-xs text-gray-600">제품 옵션 및 가격 관리 (9개 제품 유형)</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($PRODUCT_TYPES as $key => $config): ?>
            <a href="/dashboard/products/list.php?type=<?php echo $key; ?>" 
               class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-900"><?php echo $config['name']; ?></h3>
                    <span class="text-lg">📦</span>
                </div>
                <div class="text-xs text-gray-600">
                    <div>테이블: <?php echo $config['table']; ?></div>
                    <div>단위: <?php echo $config['unit']; ?></div>
                </div>
                <div class="mt-2 text-blue-600 text-xs font-medium">
                    옵션 관리 →
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="mt-6 mb-4">
            <h2 class="text-sm font-bold text-gray-900 mb-2">재단 영상</h2>
            <div class="bg-black rounded-lg overflow-hidden shadow-lg">
                <video id="cuttingVideo" controls muted class="w-full" style="max-height: 80vh;"
                       src="/media/cutting.mp4"
                       preload="metadata">
                    브라우저가 비디오를 지원하지 않습니다.
                </video>
            </div>
        </div>
    </div>
</main>

<script>
(function() {
    var video = document.getElementById('cuttingVideo');
    var scrollContainer = document.querySelector('main.overflow-y-auto') || window;

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                video.play();
            } else {
                video.pause();
            }
        });
    }, {
        root: scrollContainer === window ? null : scrollContainer,
        threshold: 0.3
    });

    observer.observe(video);
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
