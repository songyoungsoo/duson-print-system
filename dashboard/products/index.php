<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">제품 관리</h1>
            <p class="mt-2 text-sm text-gray-600">제품 옵션 및 가격 관리 (9개 제품 유형)</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($PRODUCT_TYPES as $key => $config): ?>
            <a href="/dashboard/products/list.php?type=<?php echo $key; ?>" 
               class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900"><?php echo $config['name']; ?></h3>
                    <span class="text-2xl">📦</span>
                </div>
                <div class="text-sm text-gray-600">
                    <div>테이블: <?php echo $config['table']; ?></div>
                    <div>단위: <?php echo $config['unit']; ?></div>
                </div>
                <div class="mt-4 text-blue-600 text-sm font-medium">
                    옵션 관리 →
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
