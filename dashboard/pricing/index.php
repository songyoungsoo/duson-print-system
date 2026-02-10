<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="mb-4">
            <h1 class="text-lg font-bold text-gray-900">가격 관리</h1>
            <p class="mt-1 text-xs text-gray-600">제품 가격 일괄 수정 (9개 제품 유형)</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($PRODUCT_TYPES as $key => $config): ?>
            <a href="/dashboard/pricing/edit.php?type=<?php echo $key; ?>" 
               class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-900"><?php echo $config['name']; ?></h3>
                    <span class="text-lg">💰</span>
                </div>
                <div class="text-xs text-gray-600">
                    <div>테이블: <?php echo $config['table']; ?></div>
                    <div>단위: <?php echo $config['unit']; ?></div>
                </div>
                <div class="mt-2 text-blue-600 text-xs font-medium">
                    가격 수정 →
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
