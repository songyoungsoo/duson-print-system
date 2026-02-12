<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// 프리미엄 옵션 대상 품목 (스티커/자석스티커/NCR 제외)
$premium_products = [
    'namecard' => '명함',
    'merchandisebond' => '상품권',
    'inserted' => '전단지',
    'littleprint' => '포스터',
    'cadarok' => '카다록',
    'envelope' => '봉투',
];
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

    <div class="mb-4">
        <h1 class="text-lg font-bold text-gray-900">프리미엄 옵션 관리</h1>
        <p class="mt-1 text-xs text-gray-600">품목별 프리미엄 옵션(박, 코팅, 넘버링 등) 가격을 DB에서 관리합니다</p>
    </div>

    <!-- 품목 탭 -->
    <div class="flex gap-1 mb-4 border-b border-gray-200 overflow-x-auto">
        <?php $first = true; foreach ($premium_products as $key => $name): ?>
        <button onclick="switchProduct('<?php echo $key; ?>')"
                data-product="<?php echo $key; ?>"
                class="product-tab px-3 py-2 text-xs font-medium border-b-2 whitespace-nowrap <?php echo $first ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
            <?php echo $name; ?>
        </button>
        <?php $first = false; endforeach; ?>
    </div>

    <!-- 로딩 -->
    <div id="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-500">옵션 로딩 중...</p>
    </div>

    <!-- 옵션 목록 -->
    <div id="optionsContainer" class="hidden space-y-4">
    </div>

    <!-- 빈 상태 -->
    <div id="emptyState" class="hidden text-center py-12 text-gray-400">
        <div class="text-4xl mb-2">⚙️</div>
        <p class="text-sm">등록된 옵션이 없습니다</p>
        <button onclick="openAddOptionModal()" class="mt-3 px-4 py-2 bg-blue-600 text-white text-xs rounded-lg hover:bg-blue-700">
            새 옵션 추가
        </button>
    </div>

    <!-- 하단 액션 -->
    <div id="bottomActions" class="hidden mt-6 flex gap-3">
        <button onclick="openAddOptionModal()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            + 새 옵션 추가
        </button>
        <button onclick="previewRecalculate()" class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700">
            주문 재계산 미리보기
        </button>
    </div>
</div>
</main>

<!-- 새 옵션 추가 모달 -->
<div id="addOptionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50" onclick="if(event.target===this)closeModal('addOptionModal')">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
        <h3 class="text-base font-bold text-gray-900 mb-4">새 옵션 카테고리 추가</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">옵션 이름</label>
                <input type="text" id="newOptionName" placeholder="예: 형압, 에폭시" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button onclick="closeModal('addOptionModal')" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">취소</button>
            <button onclick="createOption()" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">추가</button>
        </div>
    </div>
</div>

<!-- 새 Variant 추가 모달 -->
<div id="addVariantModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50" onclick="if(event.target===this)closeModal('addVariantModal')">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6">
        <h3 class="text-base font-bold text-gray-900 mb-4">새 종류 추가</h3>
        <input type="hidden" id="variantOptionId" value="">
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">종류 이름</label>
                <input type="text" id="newVariantName" placeholder="예: 홀로그램박, 4단접지" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div id="variantPriceFields" class="space-y-2">
                <!-- JS에서 동적 생성 -->
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button onclick="closeModal('addVariantModal')" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">취소</button>
            <button onclick="createVariant()" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">추가</button>
        </div>
    </div>
</div>

<!-- 재계산 모달 -->
<div id="recalcModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50" onclick="if(event.target===this)closeModal('recalcModal')">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 p-6 max-h-[80vh] overflow-y-auto">
        <h3 class="text-base font-bold text-gray-900 mb-4">주문 재계산</h3>
        <div id="recalcContent" class="text-sm text-gray-700"></div>
        <div id="recalcActions" class="flex justify-end gap-2 mt-5">
            <button onclick="closeModal('recalcModal')" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">닫기</button>
        </div>
    </div>
</div>

<!-- 토스트 알림 -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden">
    <div class="px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white bg-green-600" id="toastContent"></div>
</div>

<script>
const API_ADMIN = '/dashboard/api/premium_options.php';
let currentProduct = 'namecard';
let currentOptions = [];

// 가격 패턴 분류
const PATTERN_A_PRODUCTS = ['namecard', 'merchandisebond']; // base_500, per_unit
const PATTERN_B_PRODUCTS = ['inserted', 'littleprint', 'cadarok']; // base_price, per_unit
const PATTERN_C_PRODUCTS = ['envelope']; // tiers

function getPricingPattern(product) {
    if (PATTERN_A_PRODUCTS.includes(product)) return 'A';
    if (PATTERN_B_PRODUCTS.includes(product)) return 'B';
    if (PATTERN_C_PRODUCTS.includes(product)) return 'C';
    return 'A';
}

// ─── 품목 탭 전환 ───
function switchProduct(productType) {
    currentProduct = productType;
    document.querySelectorAll('.product-tab').forEach(btn => {
        const isActive = btn.getAttribute('data-product') === productType;
        btn.classList.toggle('border-blue-600', isActive);
        btn.classList.toggle('text-blue-600', isActive);
        btn.classList.toggle('border-transparent', !isActive);
        btn.classList.toggle('text-gray-500', !isActive);
    });
    loadOptions(productType);
}

// ─── 옵션 로드 ───
async function loadOptions(productType) {
    const loading = document.getElementById('loading');
    const container = document.getElementById('optionsContainer');
    const empty = document.getElementById('emptyState');
    const bottom = document.getElementById('bottomActions');

    loading.classList.remove('hidden');
    container.classList.add('hidden');
    empty.classList.add('hidden');
    bottom.classList.add('hidden');

    try {
        const res = await fetch(API_ADMIN + '?action=list&product_type=' + productType);
        const data = await res.json();

        if (!data.success) throw new Error(data.message);

        currentOptions = data.data.options || [];
        loading.classList.add('hidden');

        if (currentOptions.length === 0) {
            empty.classList.remove('hidden');
            return;
        }

        container.classList.remove('hidden');
        bottom.classList.remove('hidden');
        renderOptions(currentOptions);
    } catch (err) {
        loading.classList.add('hidden');
        empty.classList.remove('hidden');
        showToast('로드 실패: ' + err.message, 'error');
    }
}

// ─── 옵션 카드 렌더링 ───
function renderOptions(options) {
    const container = document.getElementById('optionsContainer');
    container.textContent = ''; // 안전한 초기화

    const pattern = getPricingPattern(currentProduct);

    options.forEach(option => {
        const card = document.createElement('div');
        card.className = 'bg-white rounded-lg shadow';

        // 헤더
        const header = document.createElement('div');
        header.className = 'flex items-center justify-between px-4 py-3 border-b border-gray-100';

        const titleWrap = document.createElement('div');
        titleWrap.className = 'flex items-center gap-2';

        const title = document.createElement('h3');
        title.className = 'text-sm font-bold text-gray-900';
        title.textContent = option.option_name;

        const badge = document.createElement('span');
        badge.className = option.is_active
            ? 'px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700'
            : 'px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500';
        badge.textContent = option.is_active ? '활성' : '비활성';

        titleWrap.appendChild(title);
        titleWrap.appendChild(badge);

        const btnWrap = document.createElement('div');
        btnWrap.className = 'flex gap-2';

        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'text-xs px-2 py-1 rounded border ' + (option.is_active
            ? 'border-red-200 text-red-600 hover:bg-red-50'
            : 'border-green-200 text-green-600 hover:bg-green-50');
        toggleBtn.textContent = option.is_active ? '비활성화' : '활성화';
        toggleBtn.addEventListener('click', () => toggleOption(option.option_id));

        const addBtn = document.createElement('button');
        addBtn.className = 'text-xs px-2 py-1 rounded border border-blue-200 text-blue-600 hover:bg-blue-50';
        addBtn.textContent = '+ 종류 추가';
        addBtn.addEventListener('click', () => openAddVariantModal(option.option_id, option.option_name));

        btnWrap.appendChild(toggleBtn);
        btnWrap.appendChild(addBtn);
        header.appendChild(titleWrap);
        header.appendChild(btnWrap);
        card.appendChild(header);

        // variant 테이블
        if (option.variants && option.variants.length > 0) {
            const tableWrap = document.createElement('div');
            tableWrap.className = 'overflow-x-auto';

            const table = document.createElement('table');
            table.className = 'w-full text-sm';

            // thead
            const thead = document.createElement('thead');
            const headRow = document.createElement('tr');
            headRow.className = 'bg-gray-50 text-xs text-gray-500';

            const cols = getColumnsForPattern(pattern);
            cols.forEach(col => {
                const th = document.createElement('th');
                th.className = 'px-4 py-2 text-left font-medium';
                th.textContent = col.label;
                headRow.appendChild(th);
            });

            // 액션 컬럼
            const thAction = document.createElement('th');
            thAction.className = 'px-4 py-2 text-center font-medium';
            thAction.textContent = '액션';
            headRow.appendChild(thAction);

            thead.appendChild(headRow);
            table.appendChild(thead);

            // tbody
            const tbody = document.createElement('tbody');
            option.variants.forEach(variant => {
                const row = document.createElement('tr');
                row.className = 'border-t border-gray-100 hover:bg-gray-50';
                if (!variant.is_active) row.classList.add('opacity-50');

                // variant 이름
                const tdName = document.createElement('td');
                tdName.className = 'px-4 py-2 font-medium text-gray-900';
                tdName.textContent = variant.variant_name;
                if (variant.is_default) {
                    const defBadge = document.createElement('span');
                    defBadge.className = 'ml-1 text-xs text-blue-500';
                    defBadge.textContent = '(기본)';
                    tdName.appendChild(defBadge);
                }
                row.appendChild(tdName);

                // 가격 필드들
                const pc = variant.pricing_config || {};
                const priceFields = getPriceFieldsForPattern(pattern);
                priceFields.forEach(field => {
                    const td = document.createElement('td');
                    td.className = 'px-4 py-1';

                    const input = document.createElement('input');
                    input.type = 'number';
                    input.className = 'w-24 px-2 py-1 text-sm border border-gray-200 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500';
                    input.value = pc[field.key] ?? '';
                    input.setAttribute('data-variant-id', variant.variant_id);
                    input.setAttribute('data-field', field.key);
                    input.addEventListener('change', handlePriceChange);
                    td.appendChild(input);

                    row.appendChild(td);
                });

                // 액션
                const tdAction = document.createElement('td');
                tdAction.className = 'px-4 py-2 text-center';

                const delBtn = document.createElement('button');
                delBtn.className = 'text-xs text-red-500 hover:text-red-700 hover:underline';
                delBtn.textContent = '삭제';
                delBtn.addEventListener('click', () => deleteVariant(variant.variant_id, variant.variant_name));
                tdAction.appendChild(delBtn);

                row.appendChild(tdAction);
                tbody.appendChild(row);
            });

            table.appendChild(tbody);
            tableWrap.appendChild(table);
            card.appendChild(tableWrap);
        } else {
            const noData = document.createElement('div');
            noData.className = 'px-4 py-6 text-center text-gray-400 text-sm';
            noData.textContent = '등록된 종류가 없습니다';
            card.appendChild(noData);
        }

        container.appendChild(card);
    });
}

// ─── 패턴별 컬럼 정의 ───
function getColumnsForPattern(pattern) {
    const nameCol = { label: '종류', key: 'name' };
    switch (pattern) {
        case 'A': return [nameCol, { label: '기본가(500매)' }, { label: '추가 단가(/매)' }, { label: '추가비' }];
        case 'B': return [nameCol, { label: '기본가' }, { label: '단가(/단위)' }];
        case 'C': return [nameCol, { label: '500매 이하' }, { label: '501~1000' }, { label: '1000초과 단가' }];
        default: return [nameCol, { label: '가격' }];
    }
}

function getPriceFieldsForPattern(pattern) {
    switch (pattern) {
        case 'A': return [
            { key: 'base_500', label: '기본가(500매)' },
            { key: 'per_unit', label: '추가 단가' },
            { key: 'additional_fee', label: '추가비' }
        ];
        case 'B': return [
            { key: 'base_price', label: '기본가' },
            { key: 'per_unit', label: '단가' }
        ];
        case 'C': return [
            { key: 'tier_1_price', label: '500매 이하' },
            { key: 'tier_2_price', label: '501~1000' },
            { key: 'over_1000_per_unit', label: '1000초과 단가' }
        ];
        default: return [{ key: 'base_price', label: '가격' }];
    }
}

// ─── 가격 변경 핸들러 ───
let saveTimeout = null;
function handlePriceChange(e) {
    const variantId = parseInt(e.target.getAttribute('data-variant-id'));
    const field = e.target.getAttribute('data-field');
    const value = parseInt(e.target.value) || 0;

    // 현재 variant의 pricing_config 재구성
    const variant = findVariant(variantId);
    if (!variant) return;

    variant.pricing_config[field] = value;

    // 디바운스 저장
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => saveVariant(variantId, variant.pricing_config), 600);
}

function findVariant(variantId) {
    for (const opt of currentOptions) {
        for (const v of opt.variants) {
            if (v.variant_id === variantId) return v;
        }
    }
    return null;
}

// ─── API 호출 ───
async function saveVariant(variantId, pricingConfig) {
    try {
        const res = await fetch(API_ADMIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_variant',
                variant_id: variantId,
                pricing_config: pricingConfig
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast('저장 완료');
        } else {
            showToast('저장 실패: ' + data.message, 'error');
        }
    } catch (err) {
        showToast('저장 오류: ' + err.message, 'error');
    }
}

async function toggleOption(optionId) {
    try {
        const res = await fetch(API_ADMIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle_option', option_id: optionId })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message);
            loadOptions(currentProduct);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('오류: ' + err.message, 'error');
    }
}

async function deleteVariant(variantId, variantName) {
    if (!confirm(variantName + ' 종류를 삭제하시겠습니까?')) return;

    try {
        const res = await fetch(API_ADMIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_variant', variant_id: variantId })
        });
        const data = await res.json();
        if (data.success) {
            showToast('삭제 완료');
            loadOptions(currentProduct);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('오류: ' + err.message, 'error');
    }
}

// ─── 새 옵션 추가 ───
function openAddOptionModal() {
    document.getElementById('newOptionName').value = '';
    openModal('addOptionModal');
}

async function createOption() {
    const name = document.getElementById('newOptionName').value.trim();
    if (!name) { closeModal('addOptionModal'); showToast('옵션 이름을 입력하세요', 'error'); return; }

    try {
        const res = await fetch(API_ADMIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'create_option', product_type: currentProduct, option_name: name })
        });
        const data = await res.json();
        closeModal('addOptionModal');
        if (data.success) {
            showToast('옵션 추가 완료');
            loadOptions(currentProduct);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        closeModal('addOptionModal');
        showToast('오류: ' + err.message, 'error');
    }
}

// ─── 새 Variant 추가 ───
function openAddVariantModal(optionId, optionName) {
    document.getElementById('variantOptionId').value = optionId;
    document.getElementById('newVariantName').value = '';

    const container = document.getElementById('variantPriceFields');
    container.textContent = '';

    const pattern = getPricingPattern(currentProduct);
    const fields = getPriceFieldsForPattern(pattern);

    fields.forEach(field => {
        const div = document.createElement('div');
        const label = document.createElement('label');
        label.className = 'block text-xs font-medium text-gray-700 mb-1';
        label.textContent = field.label;
        div.appendChild(label);

        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm';
        input.setAttribute('data-price-field', field.key);
        input.placeholder = '0';
        div.appendChild(input);

        container.appendChild(div);
    });

    openModal('addVariantModal');
}

async function createVariant() {
    const optionId = parseInt(document.getElementById('variantOptionId').value);
    const name = document.getElementById('newVariantName').value.trim();
    if (!name) { closeModal('addVariantModal'); showToast('종류 이름을 입력하세요', 'error'); return; }

    const pricingConfig = {};
    document.querySelectorAll('#variantPriceFields input[data-price-field]').forEach(input => {
        pricingConfig[input.getAttribute('data-price-field')] = parseInt(input.value) || 0;
    });

    try {
        const res = await fetch(API_ADMIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'create_variant',
                option_id: optionId,
                variant_name: name,
                pricing_config: pricingConfig
            })
        });
        const data = await res.json();
        closeModal('addVariantModal');
        if (data.success) {
            showToast('종류 추가 완료');
            loadOptions(currentProduct);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        closeModal('addVariantModal');
        showToast('오류: ' + err.message, 'error');
    }
}

// ─── 재계산 ───
let lastRecalcData = null;

async function previewRecalculate() {
    const el = document.getElementById('recalcContent');
    const actions = document.getElementById('recalcActions');
    el.textContent = '';
    actions.innerHTML = '<button onclick="closeModal(\'recalcModal\')" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">닫기</button>';

    // 로딩
    const loading = document.createElement('div');
    loading.className = 'text-center py-4';
    loading.innerHTML = '<div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div><p class="mt-2 text-xs text-gray-500">미리보기 계산 중...</p>';
    el.appendChild(loading);
    openModal('recalcModal');

    try {
        const res = await fetch(API_ADMIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'recalculate_orders', product_type: currentProduct })
        });
        const data = await res.json();
        el.textContent = '';

        if (!data.success) { showToast(data.message, 'error'); closeModal('recalcModal'); return; }

        const info = data.data;
        lastRecalcData = info;

        // 요약
        const summary = document.createElement('div');
        summary.className = 'mb-4 p-3 rounded-lg ' + (info.affected_orders > 0 ? 'bg-amber-50 border border-amber-200' : 'bg-green-50 border border-green-200');
        summary.innerHTML = '<p class="font-medium">' + (info.message || '') + '</p>';
        if (info.affected_orders > 0 && info.total_diff !== undefined) {
            const diffSign = info.total_diff >= 0 ? '+' : '';
            summary.innerHTML += '<p class="text-xs mt-1 text-gray-600">총 차이: ' + diffSign + info.total_diff.toLocaleString() + '원</p>';
        }
        el.appendChild(summary);

        // 변동 주문 테이블
        if (info.changes && info.changes.length > 0) {
            const tableWrap = document.createElement('div');
            tableWrap.className = 'overflow-x-auto max-h-60 overflow-y-auto border border-gray-200 rounded-lg';

            let html = '<table class="w-full text-xs">';
            html += '<thead class="bg-gray-50 sticky top-0"><tr>';
            html += '<th class="px-3 py-2 text-left">주문번호</th>';
            html += '<th class="px-3 py-2 text-right">수량</th>';
            html += '<th class="px-3 py-2 text-right">기존 금액</th>';
            html += '<th class="px-3 py-2 text-right">새 금액</th>';
            html += '<th class="px-3 py-2 text-right">차이</th>';
            html += '<th class="px-3 py-2 text-left">상세</th>';
            html += '</tr></thead><tbody>';

            info.changes.forEach(c => {
                const diffClass = c.diff > 0 ? 'text-red-600' : c.diff < 0 ? 'text-green-600' : 'text-gray-500';
                const diffSign = c.diff >= 0 ? '+' : '';
                const details = (c.details || []).map(d => d.option + '(' + d.variant + '): ' + d.old_price.toLocaleString() + '→' + d.new_price.toLocaleString()).join(', ');

                html += '<tr class="border-t border-gray-100">';
                html += '<td class="px-3 py-1.5 font-mono">#' + c.no + '</td>';
                html += '<td class="px-3 py-1.5 text-right">' + c.quantity + '</td>';
                html += '<td class="px-3 py-1.5 text-right">' + c.old_total.toLocaleString() + '원</td>';
                html += '<td class="px-3 py-1.5 text-right font-medium">' + c.new_total.toLocaleString() + '원</td>';
                html += '<td class="px-3 py-1.5 text-right ' + diffClass + '">' + diffSign + c.diff.toLocaleString() + '</td>';
                html += '<td class="px-3 py-1.5 text-gray-500">' + details + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            tableWrap.innerHTML = html;
            el.appendChild(tableWrap);

            if (info.changes.length >= 50) {
                const note = document.createElement('p');
                note.className = 'text-xs text-gray-400 mt-1';
                note.textContent = '최대 50건까지 표시됩니다.';
                el.appendChild(note);
            }

            // 실행 버튼 추가
            actions.innerHTML = '';
            const closeBtn = document.createElement('button');
            closeBtn.className = 'px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200';
            closeBtn.textContent = '취소';
            closeBtn.addEventListener('click', () => closeModal('recalcModal'));

            const execBtn = document.createElement('button');
            execBtn.className = 'px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 font-medium';
            execBtn.textContent = '재계산 실행 (' + info.affected_orders + '건)';
            execBtn.addEventListener('click', () => executeRecalculate());

            actions.appendChild(closeBtn);
            actions.appendChild(execBtn);
        }

    } catch (err) {
        el.textContent = '';
        showToast('오류: ' + err.message, 'error');
        closeModal('recalcModal');
    }
}

async function executeRecalculate() {
    if (!confirm('정말 ' + (lastRecalcData?.affected_orders || 0) + '건의 주문을 재계산하시겠습니까?\n이 작업은 되돌릴 수 없습니다.')) return;

    const el = document.getElementById('recalcContent');
    const actions = document.getElementById('recalcActions');
    el.textContent = '';
    actions.innerHTML = '';

    const loading = document.createElement('div');
    loading.className = 'text-center py-4';
    loading.innerHTML = '<div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-red-600"></div><p class="mt-2 text-xs text-gray-500">재계산 실행 중...</p>';
    el.appendChild(loading);

    try {
        const res = await fetch(API_ADMIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'recalculate_orders', product_type: currentProduct, execute: true })
        });
        const data = await res.json();
        el.textContent = '';

        if (data.success) {
            const info = data.data;
            const result = document.createElement('div');
            result.className = 'p-4 bg-green-50 border border-green-200 rounded-lg text-center';
            result.innerHTML = '<div class="text-2xl mb-2">✓</div><p class="font-medium text-green-800">' + (info.message || '재계산 완료') + '</p>';
            if (info.errors && info.errors.length > 0) {
                result.innerHTML += '<div class="mt-2 text-xs text-red-600">' + info.errors.join('<br>') + '</div>';
            }
            el.appendChild(result);
        } else {
            const err = document.createElement('div');
            err.className = 'p-4 bg-red-50 border border-red-200 rounded-lg text-center';
            err.innerHTML = '<p class="text-red-800">' + (data.message || '실행 실패') + '</p>';
            el.appendChild(err);
        }

        actions.innerHTML = '<button onclick="closeModal(\'recalcModal\')" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">닫기</button>';
    } catch (err) {
        el.textContent = '';
        showToast('실행 오류: ' + err.message, 'error');
        closeModal('recalcModal');
    }
}

// ─── 모달 유틸 ───
function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
}
function closeModal(id) {
    const el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
}
function closeAllModals() {
    ['addOptionModal', 'addVariantModal', 'recalcModal'].forEach(closeModal);
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllModals(); });

// ─── 토스트 ───
function showToast(msg, type) {
    const toast = document.getElementById('toast');
    const content = document.getElementById('toastContent');
    content.textContent = msg;
    content.className = 'px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white ' +
        (type === 'error' ? 'bg-red-600' : 'bg-green-600');
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

// ─── 초기 로드 ───
document.addEventListener('DOMContentLoaded', () => loadOptions('namecard'));
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
