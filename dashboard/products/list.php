<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$type = $_GET['type'] ?? '';

if (!isset($PRODUCT_TYPES[$type])) {
    header('Location: /dashboard/products/');
    exit;
}

$product_config = $PRODUCT_TYPES[$type];
$table = $product_config['table'];
$ttable = $product_config['ttable'] ?? '';
$hasTreeSelect = $product_config['hasTreeSelect'] ?? false;
$hasPOtype = $product_config['hasPOtype'] ?? false;

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

// 제품 데이터 조회
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
                <h1 class="text-lg font-bold text-gray-900"><?php echo $product_config['name']; ?> 옵션 관리</h1>
                <p class="mt-1 text-xs text-gray-600">테이블: <?php echo $table; ?> | 단위: <?php echo $product_config['unit']; ?></p>
            </div>
            <div class="flex gap-2">
                <button id="addProductBtn" class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                    + 제품 추가
                </button>
                <a href="/dashboard/products/" class="px-3 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                    제품 목록
                </a>
            </div>
        </div>

        <!-- 검색 필터 -->
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

        <!-- 제품 테이블 -->
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
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">관리</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
                        <?php foreach ($products as $rowIdx => $product):
                            $styleNo = $product['style'];
                            $sectionNo = $product['Section'];
                            $treeSelectNo = $hasTreeSelect ? ($product['TreeSelect'] ?? '') : '';
                            $poType = $hasPOtype ? ($product['POtype'] ?? '') : '';
                            $designMoney = $product['DesignMoney'] ?? 0;
                            $styleName = isset($categoryTitles[$styleNo]) ? $categoryTitles[$styleNo]['title'] : $styleNo;
                            $sectionName = isset($categoryTitles[$sectionNo]) ? $categoryTitles[$sectionNo]['title'] : $sectionNo;
                            $treeSelectName = isset($categoryTitles[$treeSelectNo]) ? $categoryTitles[$treeSelectNo]['title'] : ($treeSelectNo ?: '-');
                            $poTypeName = ($poType == '1') ? '단면' : (($poType == '2') ? '양면' : '-');
                        ?>
                        <tr data-no="<?php echo $product['no']; ?>"
                            data-style="<?php echo $styleNo; ?>" data-section="<?php echo $sectionNo; ?>"
                            data-tree="<?php echo $treeSelectNo; ?>" data-potype="<?php echo $poType; ?>"
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
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 text-right"><?php echo $product['quantity']; ?></td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 text-right"><?php echo number_format((int)$product['money']); ?>원</td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 text-right"><?php echo number_format((int)$designMoney); ?>원</td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-center">
                                <button class="edit-btn text-blue-600 hover:text-blue-800 mr-2" 
                                        data-no="<?php echo $product['no']; ?>"
                                        data-style="<?php echo $styleNo; ?>"
                                        data-section="<?php echo $sectionNo; ?>"
                                        data-tree="<?php echo $treeSelectNo; ?>"
                                        data-potype="<?php echo $poType; ?>"
                                        data-quantity="<?php echo $product['quantity']; ?>"
                                        data-money="<?php echo $product['money']; ?>"
                                        data-design-money="<?php echo $designMoney; ?>">수정</button>
                                <button class="delete-btn text-red-600 hover:text-red-800" data-no="<?php echo $product['no']; ?>">삭제</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200">
                <div class="text-xs text-gray-700">
                    총 <span id="totalItems"><?php echo count($products); ?></span>개
                </div>
            </div>
        </div>
    </div>
</main>

<!-- 제품 추가/수정 모달 -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">제품 추가</h3>
        </div>
        <form id="productForm" class="px-6 py-4 space-y-4">
            <input type="hidden" id="formNo" name="no" value="">
            <input type="hidden" id="formAction" name="formAction" value="create">
            
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">스타일 *</label>
                <select id="formStyle" name="style" required class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                    <option value="">선택하세요</option>
                    <?php foreach ($styleOptions as $no => $title): ?>
                    <option value="<?php echo $no; ?>"><?php echo htmlspecialchars($title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">섹션 *</label>
                <select id="formSection" name="section" required class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                    <option value="">스타일을 먼저 선택하세요</option>
                </select>
            </div>
            
            <?php if ($hasTreeSelect): ?>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">종이</label>
                <select id="formTree" name="treeSelect" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                    <option value="">선택하세요</option>
                    <?php foreach ($treeOptions as $no => $title): ?>
                    <option value="<?php echo $no; ?>"><?php echo htmlspecialchars($title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <?php if ($hasPOtype): ?>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">인쇄면</label>
                <select id="formPOtype" name="poType" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                    <option value="">선택하세요</option>
                    <option value="1">단면</option>
                    <option value="2">양면</option>
                </select>
            </div>
            <?php endif; ?>
            
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">수량 *</label>
                <input type="number" id="formQuantity" name="quantity" required step="0.5" min="0.5"
                       class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">인쇄비 *</label>
                    <input type="number" id="formMoney" name="money" required min="0"
                           class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">디자인비</label>
                    <input type="number" id="formDesignMoney" name="designMoney" min="0" value="10000"
                           class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                </div>
            </div>
        </form>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button type="button" id="modalCancelBtn" class="px-3 py-1 text-xs bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                취소
            </button>
            <button type="button" id="modalSaveBtn" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                저장
            </button>
        </div>
    </div>
</div>

<script>
const productType = '<?php echo $type; ?>';
const hasTreeSelect = <?php echo $hasTreeSelect ? 'true' : 'false'; ?>;
const hasPOtype = <?php echo $hasPOtype ? 'true' : 'false'; ?>;
const sectionOptions = <?php echo json_encode($sectionOptions, JSON_UNESCAPED_UNICODE); ?>;
const categoryTitles = <?php echo json_encode(array_map(function($v){ return $v['title']; }, $categoryTitles), JSON_UNESCAPED_UNICODE); ?>;

// 필터 기능
const filterStyle = document.getElementById('filterStyle');
const filterSection = document.getElementById('filterSection');
const filterTree = document.getElementById('filterTree');
const filterPOtype = document.getElementById('filterPOtype');
const filterResultCount = document.getElementById('filterResultCount');

function updateSectionOptions(selectElement, styleValue) {
    selectElement.innerHTML = '<option value="">전체</option>';
    
    if (styleValue && sectionOptions[styleValue]) {
        Object.entries(sectionOptions[styleValue]).forEach(([no, title]) => {
            const option = document.createElement('option');
            option.value = no;
            option.textContent = title;
            selectElement.appendChild(option);
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
    
    document.querySelectorAll('#productsTableBody tr').forEach(row => {
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
    document.getElementById('totalItems').textContent = visibleCount;
}

filterStyle.addEventListener('change', function() {
    updateSectionOptions(filterSection, this.value);
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
    updateSectionOptions(filterSection, '');
    applyFilters();
});

// 초기 필터 결과 표시
applyFilters();

// 모달 관련
const modal = document.getElementById('productModal');
const modalTitle = document.getElementById('modalTitle');
const formStyle = document.getElementById('formStyle');
const formSection = document.getElementById('formSection');

// 스타일 변경 시 섹션 옵션 업데이트 (모달용)
formStyle.addEventListener('change', function() {
    formSection.innerHTML = '<option value="">선택하세요</option>';
    
    if (this.value && sectionOptions[this.value]) {
        Object.entries(sectionOptions[this.value]).forEach(([no, title]) => {
            const option = document.createElement('option');
            option.value = no;
            option.textContent = title;
            formSection.appendChild(option);
        });
    }
});

// 제품 추가 버튼
document.getElementById('addProductBtn').addEventListener('click', function() {
    modalTitle.textContent = '제품 추가';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formNo').value = '';
    document.getElementById('productForm').reset();
    document.getElementById('formDesignMoney').value = '10000';
    formSection.innerHTML = '<option value="">스타일을 먼저 선택하세요</option>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
});

// 수정 버튼
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        modalTitle.textContent = '제품 수정';
        document.getElementById('formAction').value = 'update';
        document.getElementById('formNo').value = this.dataset.no;
        
        // 스타일 설정 후 섹션 옵션 로드
        formStyle.value = this.dataset.style;
        formStyle.dispatchEvent(new Event('change'));
        
        // 약간의 딜레이 후 섹션 값 설정
        setTimeout(() => {
            formSection.value = this.dataset.section;
        }, 50);
        
        if (hasTreeSelect && document.getElementById('formTree')) {
            document.getElementById('formTree').value = this.dataset.tree || '';
        }
        if (hasPOtype && document.getElementById('formPOtype')) {
            document.getElementById('formPOtype').value = this.dataset.potype || '';
        }
        document.getElementById('formQuantity').value = this.dataset.quantity;
        document.getElementById('formMoney').value = this.dataset.money;
        document.getElementById('formDesignMoney').value = this.dataset.designMoney || '10000';
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });
});

// 모달 취소
document.getElementById('modalCancelBtn').addEventListener('click', function() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
});

// 모달 외부 클릭 시 닫기
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
});

// 모달 저장
document.getElementById('modalSaveBtn').addEventListener('click', async function() {
    const form = document.getElementById('productForm');
    const formAction = document.getElementById('formAction').value;
    
    // 필수 필드 검증
    if (!formStyle.value || !formSection.value || !document.getElementById('formQuantity').value || !document.getElementById('formMoney').value) {
        alert('필수 항목을 모두 입력하세요.');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', formAction);
    formData.append('type', productType);
    
    if (formAction === 'update') {
        formData.append('id', document.getElementById('formNo').value);
    }
    
    formData.append('style', formStyle.value);
    formData.append('section', formSection.value);
    formData.append('quantity', document.getElementById('formQuantity').value);
    formData.append('money', document.getElementById('formMoney').value);
    formData.append('designMoney', document.getElementById('formDesignMoney').value || '10000');
    
    if (hasTreeSelect && document.getElementById('formTree')) {
        formData.append('treeSelect', document.getElementById('formTree').value);
    }
    if (hasPOtype && document.getElementById('formPOtype')) {
        formData.append('poType', document.getElementById('formPOtype').value);
    }
    
    try {
        const response = await fetch('/dashboard/api/products.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            const savedStyle = formStyle.value;
            const savedSection = formSection.value;
            const savedTree = hasTreeSelect && document.getElementById('formTree') ? document.getElementById('formTree').value : '';
            const savedPOtype = hasPOtype && document.getElementById('formPOtype') ? document.getElementById('formPOtype').value : '';
            const savedQty = document.getElementById('formQuantity').value;
            const savedMoney = document.getElementById('formMoney').value;
            const savedDesign = document.getElementById('formDesignMoney').value || '10000';
            const styleName = categoryTitles[savedStyle] || savedStyle;
            const sectionName = categoryTitles[savedSection] || savedSection;
            const treeName = categoryTitles[savedTree] || savedTree || '-';
            const poTypeName = savedPOtype === '1' ? '단면' : (savedPOtype === '2' ? '양면' : '-');
            
            if (formAction === 'update') {
                const rowNo = document.getElementById('formNo').value;
                const row = document.querySelector(`tr[data-no="${rowNo}"]`);
                if (row) {
                    row.dataset.style = savedStyle;
                    row.dataset.section = savedSection;
                    row.dataset.tree = savedTree;
                    row.dataset.potype = savedPOtype;
                    
                    const cells = row.querySelectorAll('td');
                    let ci = 1;
                    cells[ci++].querySelector('span').textContent = styleName;
                    cells[ci++].querySelector('span').textContent = sectionName;
                    if (hasTreeSelect) cells[ci++].querySelector('span').textContent = treeName;
                    if (hasPOtype) {
                        const badge = cells[ci++].querySelector('span');
                        badge.textContent = poTypeName;
                        badge.className = 'px-2 py-1 rounded text-xs ' + 
                            (savedPOtype === '1' ? 'bg-blue-100 text-blue-800' : (savedPOtype === '2' ? 'bg-purple-100 text-purple-800' : 'text-gray-500'));
                    }
                    cells[ci++].textContent = savedQty;
                    cells[ci++].textContent = Number(savedMoney).toLocaleString() + '원';
                    cells[ci++].textContent = Number(savedDesign).toLocaleString() + '원';
                    
                    const editBtn = row.querySelector('.edit-btn');
                    if (editBtn) {
                        editBtn.dataset.style = savedStyle;
                        editBtn.dataset.section = savedSection;
                        editBtn.dataset.tree = savedTree;
                        editBtn.dataset.potype = savedPOtype;
                        editBtn.dataset.quantity = savedQty;
                        editBtn.dataset.money = savedMoney;
                        editBtn.dataset.designMoney = savedDesign;
                    }
                }
            } else {
                // 새 행 추가 시에는 reload (새 no 값 필요)
                location.reload();
                return;
            }
            
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            applyFilters();
        } else {
            alert('저장 실패: ' + result.message);
        }
    } catch (error) {
        alert('저장 중 오류가 발생했습니다.');
    }
});

// 삭제 버튼
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const no = this.dataset.no;
        const row = this.closest('tr');
        
        if (!confirm(`정말 삭제하시겠습니까?\n\nNo: ${no}`)) {
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
                applyFilters(); // 카운트 업데이트
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
