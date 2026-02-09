<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900">주문 관리</h1>
            <p class="mt-1 text-sm text-gray-600">주문 목록 조회 및 상태 관리</p>
        </div>

        <div class="bg-white rounded-lg shadow p-3 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                <select id="periodFilter" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">기간: 전체</option>
                    <option value="today">오늘</option>
                    <option value="7days">최근 7일</option>
                    <option value="30days" selected>최근 30일</option>
                    <option value="3months">최근 3개월</option>
                </select>
                <select id="statusFilter" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">상태: 전체</option>
                    <option value="1">견적접수</option>
                    <option value="2">주문접수</option>
                    <option value="3">접수완료</option>
                    <option value="4">입금대기</option>
                    <option value="5">시안제작중</option>
                    <option value="6">시안</option>
                    <option value="7">교정</option>
                    <option value="8">작업완료</option>
                    <option value="9">작업중</option>
                    <option value="10">교정작업중</option>
                    <option value="deleted">삭제됨</option>
                </select>
                <select id="productFilter" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">품목: 전체</option>
                    <option value="스티커">스티커</option>
                    <option value="명함">명함</option>
                    <option value="전단지">전단지</option>
                    <option value="봉투">봉투</option>
                    <option value="포스터">포스터</option>
                </select>
                <div class="flex-1 min-w-[200px]">
                    <input type="text" id="searchInput" placeholder="주문번호, 이름, 이메일"
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button id="searchBtn" class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">검색</button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-2 text-center w-10"><input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-gray-300 text-blue-600 cursor-pointer"></th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">주문번호</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">품목</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">주문자</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">금액</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">주문일시</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">관리</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="8" class="px-3 py-3 text-center text-gray-500">로딩 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="bulkActionBar" class="px-4 py-2 border-t border-gray-200 bg-red-50 items-center justify-between hidden">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-red-700"><span id="selectedCount" class="font-bold">0</span>건 선택됨</span>
                    <button id="bulkDeleteBtn" class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                        선택 삭제
                    </button>
                    <button id="clearSelectionBtn" class="px-3 py-1.5 bg-white text-gray-600 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                        선택 해제
                    </button>
                </div>
            </div>
            <div id="pagination" class="px-4 py-3 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    총 <span id="totalItems" class="font-semibold text-gray-900">0</span>건
                    <span id="pageInfo" class="ml-2 text-gray-400"></span>
                </div>
                <div id="paginationButtons" class="flex items-center gap-1">
                </div>
            </div>
        </div>
    </div>
</main>

<script>
let currentPage = 1;
let currentFilters = {};
let selectedOrders = new Set();

function getStatusInfo(status) {
    var s = String(status);
    var map = {
        '0':  {label: '미선택',    bg: 'bg-gray-100',   text: 'text-gray-600'},
        '1':  {label: '견적접수',  bg: 'bg-slate-100',  text: 'text-slate-700'},
        '2':  {label: '주문접수',  bg: 'bg-yellow-100', text: 'text-yellow-800'},
        '3':  {label: '접수완료',  bg: 'bg-amber-100',  text: 'text-amber-800'},
        '4':  {label: '입금대기',  bg: 'bg-orange-100', text: 'text-orange-800'},
        '5':  {label: '시안제작중', bg: 'bg-indigo-100', text: 'text-indigo-700'},
        '6':  {label: '시안',      bg: 'bg-violet-100', text: 'text-violet-700'},
        '7':  {label: '교정',      bg: 'bg-blue-100',   text: 'text-blue-700'},
        '8':  {label: '작업완료',  bg: 'bg-green-100',  text: 'text-green-800'},
        '9':  {label: '작업중',    bg: 'bg-purple-100', text: 'text-purple-700'},
        '10': {label: '교정작업중', bg: 'bg-cyan-100',  text: 'text-cyan-700'},
        'deleted': {label: '삭제됨', bg: 'bg-red-100',  text: 'text-red-800'}
    };
    return map[s] || {label: s, bg: 'bg-gray-100', text: 'text-gray-800'};
}

async function loadOrders(page = 1) {
    currentPage = page;
    
    const params = new URLSearchParams({
        action: 'list',
        page: page,
        ...currentFilters
    });
    
    try {
        const response = await fetch(`/dashboard/api/orders.php?${params}`);
        const result = await response.json();
        
        if (!result.success) {
            if (response.status === 401) {
                window.location.href = '/admin/mlangprintauto/login.php?redirect=/dashboard/orders/';
                return;
            }
            throw new Error(result.message);
        }
        
        const tbody = document.getElementById('ordersTableBody');
        const orders = result.data.data;
        
        if (orders.length === 0) {
            tbody.textContent = '';
            var emptyRow = document.createElement('tr');
            var emptyTd = document.createElement('td');
            emptyTd.colSpan = 8;
            emptyTd.className = 'px-3 py-3 text-center text-gray-500';
            emptyTd.textContent = '주문이 없습니다.';
            emptyRow.appendChild(emptyTd);
            tbody.appendChild(emptyRow);
            updateSelectionUI();
            return;
        }

        tbody.textContent = '';
        orders.forEach(function(order) {
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';
            if (selectedOrders.has(order.no)) tr.classList.add('bg-blue-50');

            // 체크박스
            var tdCheck = document.createElement('td');
            tdCheck.className = 'px-2 py-2 text-center';
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.className = 'order-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 cursor-pointer';
            cb.dataset.no = order.no;
            cb.checked = selectedOrders.has(order.no);
            cb.addEventListener('change', function() {
                if (this.checked) {
                    selectedOrders.add(order.no);
                    tr.classList.add('bg-blue-50');
                } else {
                    selectedOrders.delete(order.no);
                    tr.classList.remove('bg-blue-50');
                }
                updateSelectionUI();
            });
            tdCheck.appendChild(cb);
            tr.appendChild(tdCheck);

            // 주문번호
            var tdNo = document.createElement('td');
            tdNo.className = 'px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900';
            tdNo.textContent = '#' + order.no;
            tr.appendChild(tdNo);

            // 품목
            var tdType = document.createElement('td');
            tdType.className = 'px-3 py-2 whitespace-nowrap text-sm text-gray-600';
            tdType.textContent = order.type || '-';
            tr.appendChild(tdType);

            // 주문자
            var tdName = document.createElement('td');
            tdName.className = 'px-3 py-2 whitespace-nowrap text-sm text-gray-600';
            tdName.textContent = order.name || (order.email ? order.email.split('@')[0] : '-');
            tr.appendChild(tdName);

            // 금액
            var tdAmount = document.createElement('td');
            tdAmount.className = 'px-3 py-2 whitespace-nowrap text-sm text-gray-900 text-right';
            tdAmount.textContent = (order.amount || 0).toLocaleString() + '원';
            tr.appendChild(tdAmount);

            // 상태
            var tdStatus = document.createElement('td');
            tdStatus.className = 'px-3 py-2 whitespace-nowrap text-center';
            var statusInfo = getStatusInfo(order.status);
            var badge = document.createElement('span');
            badge.className = 'px-2 py-1 text-xs font-semibold rounded-full ' + statusInfo.bg + ' ' + statusInfo.text;
            badge.textContent = statusInfo.label;
            tdStatus.appendChild(badge);
            tr.appendChild(tdStatus);

            // 주문일시
            var tdDate = document.createElement('td');
            tdDate.className = 'px-3 py-2 whitespace-nowrap text-sm text-gray-600';
            tdDate.textContent = order.date || '-';
            tr.appendChild(tdDate);

            // 관리
            var tdAction = document.createElement('td');
            tdAction.className = 'px-3 py-2 whitespace-nowrap text-center text-sm';
            var link = document.createElement('a');
            link.href = '/dashboard/orders/view.php?no=' + order.no;
            link.className = 'text-blue-600 hover:text-blue-800 mr-3';
            link.textContent = '상세';
            var delBtn = document.createElement('button');
            delBtn.className = 'text-red-600 hover:text-red-800';
            delBtn.textContent = '삭제';
            delBtn.addEventListener('click', function() { deleteOrder(order.no); });
            tdAction.appendChild(link);
            tdAction.appendChild(delBtn);
            tr.appendChild(tdAction);

            tbody.appendChild(tr);
        });

        updateSelectionUI();
        
        document.getElementById('totalItems').textContent = result.data.pagination.total_items;
        
        renderPagination(result.data.pagination);
        
    } catch (error) {
        console.error('Failed to load orders:', error);
        var tbody = document.getElementById('ordersTableBody');
        tbody.textContent = '';
        var errRow = document.createElement('tr');
        var errTd = document.createElement('td');
        errTd.colSpan = 8;
        errTd.className = 'px-3 py-3 text-center text-red-500';
        errTd.textContent = '주문 목록을 불러오는데 실패했습니다.';
        errRow.appendChild(errTd);
        tbody.appendChild(errRow);
    }
}

function renderPagination(pagination) {
    var container = document.getElementById('paginationButtons');
    var cur = pagination.current_page;
    var total = pagination.total_pages;
    var pageInfo = document.getElementById('pageInfo');

    while (container.firstChild) container.removeChild(container.firstChild);

    if (total <= 1) {
        pageInfo.textContent = '';
        return;
    }

    pageInfo.textContent = '(' + cur + ' / ' + total + ' 페이지)';

    var btnBase = 'min-w-[32px] h-8 text-sm rounded border transition-colors ';
    var btnNavCls = btnBase + 'px-2 border-gray-300 text-gray-500 hover:bg-gray-100 hover:text-gray-700 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white';
    var btnActiveCls = btnBase + 'px-1 border-blue-600 bg-blue-600 text-white font-semibold';
    var btnNormalCls = btnBase + 'px-1 border-gray-300 text-gray-700 hover:bg-gray-50';

    function makeBtn(label, page, cls, disabled) {
        var btn = document.createElement('button');
        btn.className = cls;
        btn.innerHTML = label;
        if (disabled) {
            btn.disabled = true;
        } else {
            btn.addEventListener('click', function() { loadOrders(page); });
        }
        return btn;
    }

    function makeDots() {
        var span = document.createElement('span');
        span.className = 'px-1 text-gray-400 select-none';
        span.textContent = '...';
        return span;
    }

    // 처음 / 이전
    container.appendChild(makeBtn('&laquo;', 1, btnNavCls, cur === 1));
    container.appendChild(makeBtn('&lsaquo;', cur - 1, btnNavCls, cur === 1));

    // 페이지 번호 계산
    var pages = [];
    var delta = 2;
    var left = cur - delta;
    var right = cur + delta;

    pages.push(1);
    if (left > 3) {
        pages.push('...');
    } else {
        for (var i = 2; i < left; i++) pages.push(i);
    }
    for (var i = Math.max(2, left); i <= Math.min(total - 1, right); i++) {
        pages.push(i);
    }
    if (right < total - 2) {
        pages.push('...');
    } else {
        for (var i = right + 1; i < total; i++) pages.push(i);
    }
    if (total > 1) pages.push(total);

    // 중복 제거
    var seen = {};
    var unique = [];
    for (var j = 0; j < pages.length; j++) {
        var p = pages[j];
        if (p === '...' || !seen[p]) {
            unique.push(p);
            if (p !== '...') seen[p] = true;
        }
    }

    for (var j = 0; j < unique.length; j++) {
        var p = unique[j];
        if (p === '...') {
            container.appendChild(makeDots());
        } else if (p === cur) {
            container.appendChild(makeBtn(String(p), p, btnActiveCls, false));
        } else {
            container.appendChild(makeBtn(String(p), p, btnNormalCls, false));
        }
    }

    // 다음 / 끝
    container.appendChild(makeBtn('&rsaquo;', cur + 1, btnNavCls, cur === total));
    container.appendChild(makeBtn('&raquo;', total, btnNavCls, cur === total));
}

async function deleteOrder(no) {
    if (!confirm('정말 이 주문을 삭제하시겠습니까?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('no', no);
        
        const response = await fetch('/dashboard/api/orders.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('주문이 삭제되었습니다.');
            loadOrders(currentPage);
        } else {
            alert('삭제 실패: ' + result.message);
        }
    } catch (error) {
        alert('삭제 중 오류가 발생했습니다.');
    }
}

// 선택 UI 업데이트
function updateSelectionUI() {
    var bar = document.getElementById('bulkActionBar');
    var countEl = document.getElementById('selectedCount');
    var selectAllCb = document.getElementById('selectAll');
    var checkboxes = document.querySelectorAll('.order-checkbox');
    var count = selectedOrders.size;

    countEl.textContent = count;
    if (count > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
    }

    // 전체선택 체크박스 상태
    if (checkboxes.length > 0) {
        var allChecked = true;
        checkboxes.forEach(function(cb) { if (!cb.checked) allChecked = false; });
        selectAllCb.checked = allChecked && checkboxes.length > 0;
        selectAllCb.indeterminate = count > 0 && !allChecked;
    } else {
        selectAllCb.checked = false;
        selectAllCb.indeterminate = false;
    }
}

// 전체 선택/해제
document.getElementById('selectAll').addEventListener('change', function() {
    var checked = this.checked;
    document.querySelectorAll('.order-checkbox').forEach(function(cb) {
        cb.checked = checked;
        var no = parseInt(cb.dataset.no);
        var row = cb.closest('tr');
        if (checked) {
            selectedOrders.add(no);
            row.classList.add('bg-blue-50');
        } else {
            selectedOrders.delete(no);
            row.classList.remove('bg-blue-50');
        }
    });
    updateSelectionUI();
});

// 선택 해제 버튼
document.getElementById('clearSelectionBtn').addEventListener('click', function() {
    selectedOrders.clear();
    document.querySelectorAll('.order-checkbox').forEach(function(cb) {
        cb.checked = false;
        cb.closest('tr').classList.remove('bg-blue-50');
    });
    document.getElementById('selectAll').checked = false;
    document.getElementById('selectAll').indeterminate = false;
    updateSelectionUI();
});

// 선택 삭제
document.getElementById('bulkDeleteBtn').addEventListener('click', async function() {
    var count = selectedOrders.size;
    if (count === 0) return;

    if (!confirm(count + '건의 주문을 삭제하시겠습니까?')) return;

    try {
        var response = await fetch('/dashboard/api/orders.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'bulk_delete', nos: Array.from(selectedOrders)})
        });
        var result = await response.json();

        if (result.success) {
            alert(result.message);
            selectedOrders.clear();
            loadOrders(currentPage);
        } else {
            alert('삭제 실패: ' + result.message);
        }
    } catch (error) {
        alert('삭제 중 오류가 발생했습니다.');
    }
});

document.getElementById('searchBtn').addEventListener('click', function() {
    currentFilters = {
        period: document.getElementById('periodFilter').value,
        status: document.getElementById('statusFilter').value,
        product_type: document.getElementById('productFilter').value,
        search: document.getElementById('searchInput').value
    };
    selectedOrders.clear();
    loadOrders(1);
});

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('searchBtn').click();
    }
});

loadOrders(1);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
