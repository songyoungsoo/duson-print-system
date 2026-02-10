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
$ttable = $product_config['ttable'] ?? '';
$hasTreeSelect = $product_config['hasTreeSelect'] ?? false;
$hasPOtype = $product_config['hasPOtype'] ?? false;
// 2026-02-06: 모든 제품이 동일한 BigNo/TreeNo 구조 사용 (sectionByTreeNo 플래그 제거됨)

// transactioncate에서 스타일/섹션 한글명 조회
$categoryTitles = [];
$styleOptions = [];
$sectionOptions = [];
$treeOptions = [];

if (!empty($ttable)) {
    // 1차: Ttable로 카테고리 조회
    $catQuery = "SELECT no, BigNo, title, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable = ?";
    $catStmt = mysqli_prepare($db, $catQuery);
    mysqli_stmt_bind_param($catStmt, "s", $ttable);
    mysqli_stmt_execute($catStmt);
    $catResult = mysqli_stmt_get_result($catStmt);

    $allCategories = [];
    while ($catRow = mysqli_fetch_assoc($catResult)) {
        $allCategories[] = $catRow;
        $categoryTitles[$catRow['no']] = [
            'title' => $catRow['title'],
            'bigNo' => $catRow['BigNo'],
            'treeNo' => $catRow['TreeNo']
        ];
    }
    mysqli_stmt_close($catStmt);

    // 2차: 가격 테이블에서 사용된 style/Section 중 누락된 카테고리 보충 조회
    $usedNos = [];
    $selectNos = "SELECT DISTINCT style FROM `{$table}` UNION SELECT DISTINCT Section FROM `{$table}`";
    $nosResult = mysqli_query($db, $selectNos);
    while ($nosRow = mysqli_fetch_row($nosResult)) {
        $no = $nosRow[0];
        if ($no !== '' && $no !== null && !isset($categoryTitles[$no])) {
            $usedNos[] = intval($no);
        }
    }
    if (!empty($usedNos)) {
        $placeholders = implode(',', array_fill(0, count($usedNos), '?'));
        $types = str_repeat('i', count($usedNos));
        $extraQuery = "SELECT no, BigNo, title, TreeNo FROM mlangprintauto_transactioncate WHERE no IN ({$placeholders})";
        $extraStmt = mysqli_prepare($db, $extraQuery);
        mysqli_stmt_bind_param($extraStmt, $types, ...$usedNos);
        mysqli_stmt_execute($extraStmt);
        $extraResult = mysqli_stmt_get_result($extraStmt);
        while ($extraRow = mysqli_fetch_assoc($extraResult)) {
            $allCategories[] = $extraRow;
            $categoryTitles[$extraRow['no']] = [
                'title' => $extraRow['title'],
                'bigNo' => $extraRow['BigNo'],
                'treeNo' => $extraRow['TreeNo']
            ];
        }
        mysqli_stmt_close($extraStmt);
    }

    foreach ($allCategories as $cat) {
        $no = $cat['no'];
        $bigNo = $cat['BigNo'];
        $treeNo = $cat['TreeNo'];
        $title = $cat['title'];
        $bigNoEmpty = ($bigNo === '' || $bigNo === null || $bigNo === '0' || $bigNo === 0);
        $treeNoEmpty = ($treeNo === '' || $treeNo === null);

        if ($bigNo === '0' || $bigNo === 0) {
            // Style (최상위 카테고리): BigNo = 0
            $styleOptions[$no] = $title;
        } else if (!$bigNoEmpty) {
            // Section (크기/규격): BigNo가 스타일 no를 참조
            if (!isset($sectionOptions[$bigNo])) {
                $sectionOptions[$bigNo] = [];
            }
            $sectionOptions[$bigNo][$no] = $title;
        } else if ($bigNoEmpty && !$treeNoEmpty) {
            // Tree (종이종류): TreeNo가 스타일 no를 참조
            $treeOptions[$no] = $title;
        }
    }
}

$selectCols = "no, style, Section, quantity, money, DesignMoney";
$orderCols = "style, Section";

if ($hasTreeSelect) {
    $selectCols .= ", TreeSelect";
    $orderCols .= ", TreeSelect";
}
if ($hasPOtype) {
    $selectCols .= ", POtype";
    $orderCols .= ", POtype";
}
$orderCols .= ", quantity ASC";

$query = "SELECT {$selectCols} FROM `{$table}` ORDER BY {$orderCols}";
$result = mysqli_query($db, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900"><?php echo $product_config['name']; ?> 가격 수정</h1>
                <p class="mt-1 text-xs text-gray-600">일괄 가격 인상/인하 또는 개별 수정</p>
            </div>
            <a href="/dashboard/pricing/" class="px-3 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                가격 관리
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">일괄 가격 조정</h3>
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">조정 비율 (%)</label>
                    <input type="number" id="bulkPercent" step="0.1" placeholder="예: 10 (10% 인상), -5 (5% 인하)"
                           class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                </div>
                <button id="bulkApplyBtn" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    일괄 적용
                </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">* 양수는 인상, 음수는 인하입니다. 적용 전 확인 메시지가 표시됩니다.</p>
            <p class="mt-0.5 text-xs text-blue-600">* <span class="px-1 py-0.5 text-xs bg-green-100 text-green-700 rounded">기준</span> 표시된 1연 가격을 변경하면 2~10연이 자동 계산됩니다. (0.5연은 별도 수정)</p>
            <p class="mt-0.5 text-xs text-green-600">* 디자인비를 변경하면 같은 그룹(스타일/섹션/종이/인쇄면)의 모든 수량에 <span class="px-1 py-0.5 text-xs bg-green-100 text-green-700 rounded">자동 적용</span>됩니다.</p>
        </div>

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">검색 필터</h3>
            <div class="flex gap-3 items-end flex-wrap">
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-700 mb-1">스타일</label>
                    <select id="filterStyle" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                        <option value="">전체</option>
                        <?php foreach ($styleOptions as $no => $title): ?>
                        <option value="<?php echo $no; ?>"><?php echo htmlspecialchars($title); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-700 mb-1">섹션</label>
                    <select id="filterSection" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                        <option value="">전체</option>
                    </select>
                </div>
                <?php if ($hasTreeSelect): ?>
                <div class="w-48">
                    <label class="block text-xs font-medium text-gray-700 mb-1">종이</label>
                    <select id="filterTree" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                        <option value="">전체</option>
                        <?php foreach ($treeOptions as $no => $title): ?>
                        <option value="<?php echo $no; ?>"><?php echo htmlspecialchars($title); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php if ($hasPOtype): ?>
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-700 mb-1">인쇄면</label>
                    <select id="filterPOtype" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                        <option value="">전체</option>
                        <option value="1">단면</option>
                        <option value="2">양면</option>
                    </select>
                </div>
                <?php endif; ?>
                <button id="filterResetBtn" class="px-3 py-1 text-xs bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                    필터 초기화
                </button>
            </div>
            <p class="mt-2 text-xs text-gray-500" id="filterResultCount"></p>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">No</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">스타일</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">섹션</th>
                            <?php if ($hasTreeSelect): ?>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">종이</th>
                            <?php endif; ?>
                            <?php if ($hasPOtype): ?>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">인쇄면</th>
                            <?php endif; ?>
                            <th class="px-2 py-1.5 text-right text-xs font-medium text-gray-500">수량</th>
                            <th class="px-2 py-1.5 text-right text-xs font-medium text-gray-500">인쇄비</th>
                            <th class="px-2 py-1.5 text-right text-xs font-medium text-gray-500">디자인비</th>
                            <th class="px-2 py-1.5 text-right text-xs font-medium text-gray-500">새 인쇄비</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">삭제</th>
                        </tr>
                    </thead>
                    <tbody id="priceTableBody" class="bg-white divide-y divide-gray-200">
<?php 
// 프로덕션 서버 output_buffering 한도 대응: 테이블 출력 전 버퍼 플러시
if (ob_get_level()) { ob_flush(); } flush(); 
?>
                        <?php foreach ($products as $rowIdx => $product):
                            $styleNo = $product['style'];
                            $sectionNo = $product['Section'];
                            $treeSelectNo = $hasTreeSelect ? ($product['TreeSelect'] ?? '') : '';
                            $poType = $hasPOtype ? ($product['POtype'] ?? '') : '';
                            $designMoney = is_numeric($product['DesignMoney'] ?? 0) ? (float)($product['DesignMoney'] ?? 0) : 0;
                            $money = is_numeric($product['money']) ? (float)$product['money'] : 0;
                            $quantity = $product['quantity'];
                            $groupKey = "{$styleNo}-{$sectionNo}-{$treeSelectNo}-{$poType}";
                            $styleName = isset($categoryTitles[$styleNo]) ? $categoryTitles[$styleNo]['title'] : $styleNo;
                            $sectionName = isset($categoryTitles[$sectionNo]) ? $categoryTitles[$sectionNo]['title'] : $sectionNo;
                            $treeSelectName = isset($categoryTitles[$treeSelectNo]) ? $categoryTitles[$treeSelectNo]['title'] : ($treeSelectNo ?: '-');
                            $poTypeName = ($poType == '1') ? '단면' : (($poType == '2') ? '양면' : '-');
                        ?>
                        <tr data-no="<?php echo $product['no']; ?>" data-price="<?php echo $money; ?>"
                            data-design-money="<?php echo $designMoney; ?>"
                            data-style="<?php echo $styleNo; ?>" data-section="<?php echo $sectionNo; ?>"
                            data-tree="<?php echo $treeSelectNo; ?>" data-potype="<?php echo $poType; ?>"
                            data-quantity="<?php echo $quantity; ?>" data-group-key="<?php echo $groupKey; ?>"
                            style="<?php echo ($rowIdx % 2 === 1) ? 'background-color: #e6f7ff;' : ''; ?>">
                            <td class="px-2 py-2 whitespace-nowrap text-xs font-medium text-gray-900"><?php echo $product['no']; ?></td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600">
                                <span title="코드: <?php echo $styleNo; ?>"><?php echo htmlspecialchars($styleName ?: '-'); ?></span>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600">
                                <span title="코드: <?php echo $sectionNo; ?>"><?php echo htmlspecialchars($sectionName ?: '-'); ?></span>
                            </td>
                            <?php if ($hasTreeSelect): ?>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600">
                                <span title="코드: <?php echo $treeSelectNo; ?>"><?php echo htmlspecialchars($treeSelectName); ?></span>
                            </td>
                            <?php endif; ?>
                            <?php if ($hasPOtype): ?>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-center">
                                <span class="px-2 py-1 rounded text-xs <?php echo ($poType == '1') ? 'bg-blue-100 text-blue-800' : (($poType == '2') ? 'bg-purple-100 text-purple-800' : 'text-gray-500'); ?>">
                                    <?php echo $poTypeName; ?>
                                </span>
                            </td>
                            <?php endif; ?>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 text-right">
                                <?php echo $product['quantity']; ?>
                                <?php if ($quantity == 1): ?>
                                <span class="ml-1 px-1.5 py-0.5 text-xs bg-green-100 text-green-700 rounded" title="이 가격을 변경하면 2~10연이 자동 계산됩니다">기준</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 text-right original-price">
                                <?php echo number_format($money); ?>원
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-right">
                                <input type="number" class="new-design-money w-20 px-1.5 py-0.5 border border-gray-300 rounded text-right text-xs" 
                                       value="<?php echo $designMoney; ?>" data-original="<?php echo $designMoney; ?>">
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-right">
                                <input type="number" class="new-price w-20 px-1.5 py-0.5 border border-gray-300 rounded text-right text-xs" 
                                       value="<?php echo $money; ?>" data-original="<?php echo $money; ?>">
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-center">
                                <button class="delete-btn text-red-600 hover:text-red-800 text-xs"
                                        data-no="<?php echo $product['no']; ?>">삭제</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
<?php if (ob_get_level()) { ob_flush(); } flush(); ?>

            <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
                <button id="resetBtn" class="px-3 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                    초기화
                </button>
                <button id="saveBtn" class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                    변경사항 저장
                </button>
            </div>
        </div>
    </div>
</main>

<script>
const productType = '<?php echo $type; ?>';
const hasTreeSelect = <?php echo $hasTreeSelect ? 'true' : 'false'; ?>;
const hasPOtype = <?php echo $hasPOtype ? 'true' : 'false'; ?>;
const sectionOptions = <?php echo json_encode($sectionOptions, JSON_UNESCAPED_UNICODE); ?>;

// 필터 기능
const filterStyle = document.getElementById('filterStyle');
const filterSection = document.getElementById('filterSection');
const filterTree = document.getElementById('filterTree');
const filterPOtype = document.getElementById('filterPOtype');
const filterResultCount = document.getElementById('filterResultCount');

function updateSectionOptions() {
    const selectedStyle = filterStyle.value;
    filterSection.innerHTML = '<option value="">전체</option>';
    
    if (selectedStyle && sectionOptions[selectedStyle]) {
        Object.entries(sectionOptions[selectedStyle]).forEach(([no, title]) => {
            const option = document.createElement('option');
            option.value = no;
            option.textContent = title;
            filterSection.appendChild(option);
        });
    }
}

function applyFilters() {
    const styleVal = filterStyle.value;
    const sectionVal = filterSection.value;
    const treeVal = filterTree ? filterTree.value : '';
    const poTypeVal = filterPOtype ? filterPOtype.value : '';
    
    let visibleCount = 0;
    let totalCount = 0;
    
    document.querySelectorAll('#priceTableBody tr').forEach(row => {
        totalCount++;
        const rowStyle = row.dataset.style;
        const rowSection = row.dataset.section;
        const rowTree = row.dataset.tree || '';
        const rowPOtype = row.dataset.potype || '';
        
        let visible = true;
        
        if (styleVal && rowStyle !== styleVal) visible = false;
        if (sectionVal && rowSection !== sectionVal) visible = false;
        if (treeVal && rowTree !== treeVal) visible = false;
        if (poTypeVal && rowPOtype !== poTypeVal) visible = false;
        
        row.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
    });
    
    filterResultCount.textContent = `${visibleCount} / ${totalCount}개 표시`;
}

filterStyle.addEventListener('change', function() {
    updateSectionOptions();
    filterSection.value = '';
    applyFilters();
});

filterSection.addEventListener('change', applyFilters);

if (filterTree) {
    filterTree.addEventListener('change', applyFilters);
}

if (filterPOtype) {
    filterPOtype.addEventListener('change', applyFilters);
}

document.getElementById('filterResetBtn').addEventListener('click', function() {
    filterStyle.value = '';
    filterSection.value = '';
    if (filterTree) filterTree.value = '';
    if (filterPOtype) filterPOtype.value = '';
    updateSectionOptions();
    applyFilters();
});

// 초기 필터 결과 표시
applyFilters();

// 입력값 변경 시 하이라이트
document.querySelectorAll('.new-price').forEach(input => {
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

document.querySelectorAll('.new-design-money').forEach(input => {
    input.addEventListener('input', function() {
        const original = parseFloat(this.dataset.original);
        const current = parseFloat(this.value);
        const row = this.closest('tr');
        const groupKey = row.dataset.groupKey;
        
        if (current !== original) {
            this.classList.add('bg-yellow-50', 'border-yellow-400');
        } else {
            this.classList.remove('bg-yellow-50', 'border-yellow-400');
        }
        
        if (isNaN(current) || current < 0) return;
        
        document.querySelectorAll(`#priceTableBody tr[data-group-key="${groupKey}"]`).forEach(otherRow => {
            if (otherRow === row) return;
            const otherInput = otherRow.querySelector('.new-design-money');
            otherInput.value = current;
            const otherOriginal = parseFloat(otherInput.dataset.original);
            if (current !== otherOriginal) {
                otherInput.classList.remove('bg-yellow-50', 'border-yellow-400');
                otherInput.classList.add('bg-green-50', 'border-green-400');
            } else {
                otherInput.classList.remove('bg-green-50', 'border-green-400', 'bg-yellow-50', 'border-yellow-400');
            }
        });
    });
});

// 1연 가격 변경 시 2~10연 자동 계산 (0.5연은 제외)
document.querySelectorAll('.new-price').forEach(input => {
    input.addEventListener('input', function() {
        const row = this.closest('tr');
        const quantity = parseFloat(row.dataset.quantity);
        
        // quantity=1인 경우에만 자동 계산 트리거
        if (quantity !== 1) return;
        
        const basePrice = parseFloat(this.value);
        if (isNaN(basePrice) || basePrice <= 0) return;
        
        const groupKey = row.dataset.groupKey;
        
        // 같은 그룹의 모든 행 찾기
        document.querySelectorAll(`#priceTableBody tr[data-group-key="${groupKey}"]`).forEach(otherRow => {
            const otherQty = parseFloat(otherRow.dataset.quantity);
            
            // 2~10연만 자동 계산 (0.5연, 1연, 20연 이상은 제외)
            if (otherQty >= 2 && otherQty <= 10 && Number.isInteger(otherQty)) {
                const newPrice = Math.round(basePrice * otherQty);
                const priceInput = otherRow.querySelector('.new-price');
                priceInput.value = newPrice;
                
                // 변경 하이라이트 (자동 계산은 파란색으로 구분)
                const originalPrice = parseFloat(priceInput.dataset.original);
                if (newPrice !== originalPrice) {
                    priceInput.classList.remove('bg-yellow-50', 'border-yellow-400');
                    priceInput.classList.add('bg-blue-50', 'border-blue-400');
                } else {
                    priceInput.classList.remove('bg-blue-50', 'border-blue-400', 'bg-yellow-50', 'border-yellow-400');
                }
            }
        });
    });
});

document.getElementById('bulkApplyBtn').addEventListener('click', function() {
    const percent = parseFloat(document.getElementById('bulkPercent').value);
    
    if (isNaN(percent) || percent === 0) {
        alert('유효한 비율을 입력하세요.');
        return;
    }
    
    const visibleRows = document.querySelectorAll('#priceTableBody tr:not([style*="display: none"])');
    const action = percent > 0 ? '인상' : '인하';
    
    if (visibleRows.length === 0) {
        alert('표시된 항목이 없습니다.');
        return;
    }
    
    if (!confirm(`현재 표시된 ${visibleRows.length}개 항목의 가격을 ${Math.abs(percent)}% ${action}하시겠습니까?`)) {
        return;
    }
    
    visibleRows.forEach(row => {
        const input = row.querySelector('.new-price');
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
        input.classList.remove('bg-yellow-50', 'border-yellow-400', 'bg-blue-50', 'border-blue-400');
    });
    
    document.querySelectorAll('.new-design-money').forEach(input => {
        input.value = input.dataset.original;
        input.classList.remove('bg-yellow-50', 'border-yellow-400');
    });
});

document.getElementById('saveBtn').addEventListener('click', async function() {
    const changes = [];
    
    document.querySelectorAll('#priceTableBody tr').forEach(row => {
        const no = row.dataset.no;
        const originalPrice = parseFloat(row.dataset.price);
        const originalDesignMoney = parseFloat(row.dataset.designMoney || 0);
        const newPrice = parseFloat(row.querySelector('.new-price').value);
        const newDesignMoney = parseFloat(row.querySelector('.new-design-money').value);
        
        const priceChanged = newPrice !== originalPrice;
        const designChanged = newDesignMoney !== originalDesignMoney;
        
        if (priceChanged || designChanged) {
            changes.push({ 
                no: no, 
                price: newPrice, 
                designMoney: newDesignMoney,
                priceChanged: priceChanged,
                designChanged: designChanged
            });
        }
    });
    
    if (changes.length === 0) {
        alert('변경된 가격이 없습니다.');
        return;
    }
    
    const priceChanges = changes.filter(c => c.priceChanged).length;
    const designChanges = changes.filter(c => c.designChanged).length;
    let confirmMsg = `${changes.length}개 항목을 수정하시겠습니까?`;
    if (priceChanges > 0) confirmMsg += `\n- 인쇄비: ${priceChanges}건`;
    if (designChanges > 0) confirmMsg += `\n- 디자인비: ${designChanges}건`;
    
    if (!confirm(confirmMsg)) {
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
            formData.append('designMoney', change.designMoney);
            
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
        
        changes.forEach(change => {
            const row = document.querySelector(`tr[data-no="${change.no}"]`);
            if (!row) return;
            
            const priceInput = row.querySelector('.new-price');
            const designInput = row.querySelector('.new-design-money');
            
            row.dataset.price = change.price;
            row.dataset.designMoney = change.designMoney;
            
            priceInput.dataset.original = change.price;
            priceInput.classList.remove('bg-yellow-50', 'border-yellow-400', 'bg-blue-50', 'border-blue-400');
            
            designInput.dataset.original = change.designMoney;
            designInput.classList.remove('bg-yellow-50', 'border-yellow-400');
            
            row.querySelector('.original-price').textContent = Number(change.price).toLocaleString() + '원';
        });
        
    } catch (error) {
        alert('가격 수정 중 오류가 발생했습니다.');
    }
});

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const no = this.dataset.no;
        const row = this.closest('tr');
        const styleName = row.querySelectorAll('td')[1].textContent.trim();
        const sectionName = row.querySelectorAll('td')[2].textContent.trim();
        
        if (!confirm(`정말 삭제하시겠습니까?\n\nNo: ${no}\n스타일: ${styleName}\n섹션: ${sectionName}`)) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('type', productType);
            formData.append('id', no);
            
            const response = await fetch('/dashboard/api/products.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                row.remove();
                alert('삭제되었습니다.');
            } else {
                alert('삭제 실패: ' + result.message);
            }
        } catch (error) {
            alert('삭제 중 오류가 발생했습니다.');
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
