<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

// 스티커 요율 데이터 조회
$stickerTypes = [
    'd1' => ['name' => '일반아트지스티커', 'table' => 'shop_d1', 'prefix' => 'il'],
    'd2' => ['name' => '강접스티커', 'table' => 'shop_d2', 'prefix' => 'ka'],
    'd3' => ['name' => '특수지스티커', 'table' => 'shop_d3', 'prefix' => 'sp'],
    'd4' => ['name' => '초강접스티커', 'table' => 'shop_d4', 'prefix' => 'ck'],
];

$quantityRanges = [
    '0' => '1,000',
    '1' => '2~4,000',
    '2' => '5,000',
    '3' => '6~9,000',
    '4' => '10,000',
    '5' => '2~50,000',
    '6' => '50,000이상',
];

// 각 타입별 데이터 조회
$stickerData = [];
foreach ($stickerTypes as $key => $type) {
    $query = "SELECT * FROM {$type['table']}";
    $result = mysqli_query($db, $query);
    $stickerData[$key] = mysqli_fetch_assoc($result);
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">스티커 가격 수정</h1>
                <p class="mt-1 text-sm text-gray-600">일반아트지, 강접, 특수지, 초강접 스티커 요율 관리</p>
            </div>
            <a href="/dashboard/pricing/" class="px-3 py-1.5 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                가격 관리
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">일괄 가격 조정</h3>
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">조정 비율 (%)</label>
                    <input type="number" id="bulkPercent" step="0.1" placeholder="예: 10 (10% 인상), -5 (5% 인하)"
                           class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="w-48">
                    <label class="block text-xs font-medium text-gray-700 mb-1">대상 스티커 타입</label>
                    <select id="bulkTarget" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all">전체</option>
                        <?php foreach ($stickerTypes as $key => $type): ?>
                        <option value="<?php echo $key; ?>"><?php echo $type['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button id="bulkApplyBtn" class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    일괄 적용
                </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">* 양수는 인상, 음수는 인하입니다. 적용 전 확인 메시지가 표시됩니다.</p>
        </div>

        <form id="stickerPriceForm">
            <?php foreach ($stickerTypes as $key => $type): ?>
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-3"><?php echo $type['name']; ?> 금액 수정</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">수량 구간</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">현재 금액</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">새 금액</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php for ($i = 0; $i <= 6; $i++): ?>
                            <?php
                            $fieldName = $type['prefix'] . $i;
                            $currentValue = $stickerData[$key][$fieldName] ?? 0;
                            ?>
                            <tr data-type="<?php echo $key; ?>" data-field="<?php echo $fieldName; ?>" data-original="<?php echo $currentValue; ?>">
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo $quantityRanges[$i]; ?>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right original-price">
                                    <?php echo number_format($currentValue); ?>원
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
                                    <input type="number"
                                           class="price-input w-32 px-2 py-1 border border-gray-300 rounded text-right text-sm"
                                           name="<?php echo $fieldName; ?>"
                                           value="<?php echo $currentValue; ?>"
                                           data-type="<?php echo $key; ?>"
                                           data-field="<?php echo $fieldName; ?>"
                                           data-original="<?php echo $currentValue; ?>">
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-end gap-3">
                    <button type="button" id="resetBtn" class="px-4 py-1.5 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        초기화
                    </button>
                    <button type="submit" class="px-4 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        변경사항 저장
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
// 입력값 변경 시 하이라이트
document.querySelectorAll('.price-input').forEach(input => {
    input.addEventListener('input', function() {
        const original = parseFloat(this.dataset.original);
        const current = parseFloat(this.value);
        if (current !== original) {
            this.classList.add('bg-yellow-50', 'border-yellow-400');
        } else {
            this.classList.remove('bg-yellow-50', 'border-yellow-400');
        }
    });
});

// 일괄 적용
document.getElementById('bulkApplyBtn').addEventListener('click', function() {
    const percent = parseFloat(document.getElementById('bulkPercent').value);
    const target = document.getElementById('bulkTarget').value;

    if (isNaN(percent) || percent === 0) {
        alert('유효한 비율을 입력하세요.');
        return;
    }

    const action = percent > 0 ? '인상' : '인하';
    let targetText = target === 'all' ? '전체' : document.querySelector(`#bulkTarget option[value="${target}"]`).textContent;

    if (!confirm(`${targetText} 스티커 가격을 ${Math.abs(percent)}% ${action}하시겠습니까?`)) {
        return;
    }

    document.querySelectorAll('.price-input').forEach(input => {
        if (target !== 'all' && input.dataset.type !== target) return;

        const original = parseFloat(input.dataset.original);
        const newPrice = Math.round(original * (1 + percent / 100));
        input.value = newPrice;

        if (newPrice !== original) {
            input.classList.add('bg-yellow-50', 'border-yellow-400');
        }
    });
});

// 초기화
document.getElementById('resetBtn').addEventListener('click', function() {
    if (!confirm('모든 변경사항을 초기화하시겠습니까?')) {
        return;
    }

    document.querySelectorAll('.price-input').forEach(input => {
        input.value = input.dataset.original;
        input.classList.remove('bg-yellow-50', 'border-yellow-400');
    });
});

// 폼 제출
document.getElementById('stickerPriceForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData();
    const changes = [];

    document.querySelectorAll('.price-input').forEach(input => {
        const original = parseFloat(input.dataset.original);
        const current = parseFloat(input.value);

        if (current !== original) {
            formData.append(input.name, current);
            changes.push({
                field: input.name,
                original: original,
                current: current
            });
        }
    });

    if (changes.length === 0) {
        alert('변경된 가격이 없습니다.');
        return;
    }

    if (!confirm(`${changes.length}개 항목을 수정하시겠습니까?`)) {
        return;
    }

    try {
        const response = await fetch('/dashboard/api/sticker.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            alert('가격이 성공적으로 수정되었습니다.');

            // 원래 값 업데이트 및 하이라이트 제거
            document.querySelectorAll('.price-input').forEach(input => {
                input.dataset.original = input.value;
                input.classList.remove('bg-yellow-50', 'border-yellow-400');

                // 현재 금액 표시 업데이트
                const row = input.closest('tr');
                const originalPriceCell = row.querySelector('.original-price');
                originalPriceCell.textContent = Number(input.value).toLocaleString() + '원';
            });
        } else {
            alert('수정 실패: ' + result.message);
        }
    } catch (error) {
        alert('가격 수정 중 오류가 발생했습니다.');
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
