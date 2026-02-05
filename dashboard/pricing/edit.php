<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$type = $_GET['type'] ?? '';

if (!isset($PRODUCT_TYPES[$type])) {
    header('Location: /dashboard/pricing/');
    exit;
}

$product_config = $PRODUCT_TYPES[$type];
$table = $product_config['table'];

$query = "SELECT no, style, Section, quantity, money FROM `{$table}` ORDER BY no ASC";
$result = mysqli_query($db, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo $product_config['name']; ?> 가격 수정</h1>
                <p class="mt-2 text-sm text-gray-600">일괄 가격 인상/인하 또는 개별 수정</p>
            </div>
            <a href="/dashboard/pricing/" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                가격 관리
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">일괄 가격 조정</h3>
            <div class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">조정 비율 (%)</label>
                    <input type="number" id="bulkPercent" step="0.1" placeholder="예: 10 (10% 인상), -5 (5% 인하)" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <button id="bulkApplyBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    일괄 적용
                </button>
            </div>
            <p class="mt-2 text-sm text-gray-500">* 양수는 인상, 음수는 인하입니다. 적용 전 확인 메시지가 표시됩니다.</p>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">스타일</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">섹션</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">수량</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">현재 가격</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">새 가격</th>
                        </tr>
                    </thead>
                    <tbody id="priceTableBody" class="bg-white divide-y divide-gray-200">
                        <?php foreach ($products as $product): ?>
                        <tr data-no="<?php echo $product['no']; ?>" data-price="<?php echo $product['money']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $product['no']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($product['style'] ?: '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($product['Section'] ?: '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right"><?php echo $product['quantity']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right original-price">
                                <?php echo number_format($product['money']); ?>원
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <input type="number" class="new-price w-32 px-2 py-1 border border-gray-300 rounded text-right" 
                                       value="<?php echo $product['money']; ?>" data-original="<?php echo $product['money']; ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-4">
                <button id="resetBtn" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    초기화
                </button>
                <button id="saveBtn" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    변경사항 저장
                </button>
            </div>
        </div>
    </div>
</main>

<script>
const productType = '<?php echo $type; ?>';

document.getElementById('bulkApplyBtn').addEventListener('click', function() {
    const percent = parseFloat(document.getElementById('bulkPercent').value);
    
    if (isNaN(percent) || percent === 0) {
        alert('유효한 비율을 입력하세요.');
        return;
    }
    
    const action = percent > 0 ? '인상' : '인하';
    if (!confirm(`모든 가격을 ${Math.abs(percent)}% ${action}하시겠습니까?`)) {
        return;
    }
    
    document.querySelectorAll('.new-price').forEach(input => {
        const original = parseFloat(input.dataset.original);
        const newPrice = Math.round(original * (1 + percent / 100));
        input.value = newPrice;
        
        if (newPrice !== original) {
            input.classList.add('bg-yellow-50', 'border-yellow-400');
        }
    });
});

document.getElementById('resetBtn').addEventListener('click', function() {
    if (!confirm('모든 변경사항을 초기화하시겠습니까?')) {
        return;
    }
    
    document.querySelectorAll('.new-price').forEach(input => {
        input.value = input.dataset.original;
        input.classList.remove('bg-yellow-50', 'border-yellow-400');
    });
});

document.getElementById('saveBtn').addEventListener('click', async function() {
    const changes = [];
    
    document.querySelectorAll('#priceTableBody tr').forEach(row => {
        const no = row.dataset.no;
        const originalPrice = parseFloat(row.dataset.price);
        const newPrice = parseFloat(row.querySelector('.new-price').value);
        
        if (newPrice !== originalPrice) {
            changes.push({ no: no, price: newPrice });
        }
    });
    
    if (changes.length === 0) {
        alert('변경된 가격이 없습니다.');
        return;
    }
    
    if (!confirm(`${changes.length}개 항목의 가격을 변경하시겠습니까?`)) {
        return;
    }
    
    try {
        let successCount = 0;
        
        for (const change of changes) {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('type', productType);
            formData.append('id', change.no);
            formData.append('money', change.price);
            
            const response = await fetch('/dashboard/api/products.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                successCount++;
            }
        }
        
        alert(`${successCount}/${changes.length}개 항목이 성공적으로 수정되었습니다.`);
        location.reload();
        
    } catch (error) {
        alert('가격 수정 중 오류가 발생했습니다.');
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
