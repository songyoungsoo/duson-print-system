<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 bg-gray-50 overflow-y-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <!-- 헤더 + 필터 한 줄 -->
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <h1 class="text-lg font-bold text-gray-900 mr-2">주문 관리</h1>
            <select id="periodFilter" class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">기간: 전체</option>
                <option value="today">오늘</option>
                <option value="7days">최근 7일</option>
                <option value="30days" selected>최근 30일</option>
                <option value="3months">최근 3개월</option>
            </select>
            <select id="statusFilter" class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
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
            <select id="productFilter" class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">품목: 전체</option>
                <option value="스티커">스티커</option>
                <option value="명함">명함</option>
                <option value="전단지">전단지</option>
                <option value="봉투">봉투</option>
                <option value="포스터">포스터</option>
            </select>
            <div class="flex-1 min-w-[180px]">
                <input type="text" id="searchInput" placeholder="주문번호, 이름, 이메일"
                       class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button id="searchBtn" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">검색</button>
        </div>

        <!-- 테이블 영역 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-2 py-1.5 text-center w-8"><input type="checkbox" id="selectAll" class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 cursor-pointer"></th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 tracking-wider">주문번호</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 tracking-wider">품목</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 tracking-wider">주문자</th>
                            <th class="px-2 py-1.5 text-right text-xs font-medium text-gray-500 tracking-wider">금액</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 tracking-wider">배송</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 tracking-wider">상태</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 tracking-wider">주문일시</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 tracking-wider">관리</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="9" class="px-3 py-2 text-center text-sm text-gray-500">로딩 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="bulkActionBar" class="px-3 py-1.5 border-t border-gray-200 bg-red-50 items-center justify-between hidden">
                <div class="flex items-center gap-3">
                    <span class="text-xs text-red-700"><span id="selectedCount" class="font-bold">0</span>건 선택됨</span>
                    <button id="bulkDeleteBtn" class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                        선택 삭제
                    </button>
                    <button id="clearSelectionBtn" class="px-2 py-1 bg-white text-gray-600 text-xs rounded border border-gray-300 hover:bg-gray-50 transition-colors">
                        선택 해제
                    </button>
                </div>
            </div>
            <div id="pagination" class="px-3 py-2 border-t border-gray-200 flex items-center justify-between text-xs">
                <span class="text-gray-500">총 <span id="totalItems">0</span>건 · <span id="pageInfo"></span></span>
                <div id="paginationButtons" class="flex items-center gap-0.5">
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
            emptyTd.colSpan = 9;
            emptyTd.className = 'px-3 py-3 text-center text-gray-500';
            emptyTd.textContent = '주문이 없습니다.';
            emptyRow.appendChild(emptyTd);
            tbody.appendChild(emptyRow);
            updateSelectionUI();
            return;
        }

        tbody.textContent = '';
        orders.forEach(function(order, idx) {
            var tr = document.createElement('tr');
            tr.className = idx % 2 === 1 ? 'hover:bg-gray-100' : 'hover:bg-gray-50';
            if (idx % 2 === 1) tr.style.backgroundColor = '#e6f7ff';
            if (selectedOrders.has(order.no)) tr.classList.add('bg-blue-50');

            // 체크박스
            var tdCheck = document.createElement('td');
            tdCheck.className = 'px-2 py-1 text-center';
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.className = 'order-checkbox w-3.5 h-3.5 rounded border-gray-300 text-blue-600 cursor-pointer';
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
            tdNo.className = 'px-2 py-1 whitespace-nowrap text-xs font-medium text-gray-900';
            tdNo.textContent = '#' + order.no;
            tr.appendChild(tdNo);

            // 품목
            var tdType = document.createElement('td');
            tdType.className = 'px-2 py-1 whitespace-nowrap text-xs text-gray-600';
            tdType.textContent = order.type || '-';
            tr.appendChild(tdType);

            // 주문자
            var tdName = document.createElement('td');
            tdName.className = 'px-2 py-1 whitespace-nowrap text-xs text-gray-600';
            tdName.textContent = order.name || (order.email ? order.email.split('@')[0] : '-');
            tr.appendChild(tdName);

            // 금액
            var tdAmount = document.createElement('td');
            tdAmount.className = 'px-2 py-1 whitespace-nowrap text-xs text-gray-900 text-right';
            tdAmount.textContent = (order.amount || 0).toLocaleString() + '원';
            if (order.logen_fee_type === '선불') {
                var shippingDiv = document.createElement('div');
                shippingDiv.style.marginTop = '1px';
                if (order.logen_delivery_fee > 0) {
                    var fee = order.logen_delivery_fee;
                    var vat = Math.round(fee * 0.1);
                    shippingDiv.className = 'text-green-700';
                    shippingDiv.style.fontSize = '10px';
                    shippingDiv.textContent = '+ 택배 ₩' + (fee + vat).toLocaleString();
                } else {
                    shippingDiv.className = 'text-orange-500';
                    shippingDiv.style.fontSize = '10px';
                    shippingDiv.textContent = '+ 택배 확인중';
                }
                tdAmount.appendChild(shippingDiv);
            }
            tr.appendChild(tdAmount);

            // 배송
            var tdDelivery = document.createElement('td');
            tdDelivery.className = 'px-2 py-1 whitespace-nowrap text-center text-xs';
            var dv = (order.delivery || '').trim();
            var ft = order.logen_fee_type || '';
            if (dv === '택배') {
                var label = '택배';
                var cls = 'bg-blue-100 text-blue-700';
                if (ft === '선불') { label += ' 선불'; cls = 'bg-green-100 text-green-700'; }
                else if (ft === '착불') { label += ' 착불'; }
                var span = document.createElement('span');
                span.className = 'px-1.5 py-0.5 text-xs font-medium rounded-full ' + cls;
                span.textContent = label;
                tdDelivery.appendChild(span);
            } else if (dv === '방문') {
                var span = document.createElement('span');
                span.className = 'px-1.5 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700';
                span.textContent = '방문';
                tdDelivery.appendChild(span);
            } else if (dv === '퀵' || dv === '오토바이') {
                var span = document.createElement('span');
                span.className = 'px-1.5 py-0.5 text-xs font-medium rounded-full bg-orange-100 text-orange-700';
                span.textContent = '퀵';
                tdDelivery.appendChild(span);
            } else if (dv === '다마스') {
                var span = document.createElement('span');
                span.className = 'px-1.5 py-0.5 text-xs font-medium rounded-full bg-orange-100 text-orange-700';
                span.textContent = '다마스';
                tdDelivery.appendChild(span);
            } else if (dv) {
                tdDelivery.textContent = dv;
            } else {
                tdDelivery.innerHTML = '<span class="text-gray-300">-</span>';
            }
            tr.appendChild(tdDelivery);

            // 상태 (인라인 드롭다운)
            var tdStatus = document.createElement('td');
            tdStatus.className = 'px-2 py-1 whitespace-nowrap text-center';
            var statusSelect = document.createElement('select');
            statusSelect.className = 'text-xs border border-gray-300 rounded px-1 py-0.5 focus:ring-1 focus:ring-blue-500 cursor-pointer';
            var statusOptions = [
                {v:'1',l:'견적접수'},{v:'2',l:'주문접수'},{v:'3',l:'접수완료'},{v:'4',l:'입금대기'},
                {v:'5',l:'시안제작중'},{v:'6',l:'시안'},{v:'7',l:'교정'},{v:'8',l:'작업완료'},
                {v:'9',l:'작업중'},{v:'10',l:'교정작업중'}
            ];
            statusOptions.forEach(function(opt) {
                var o = document.createElement('option');
                o.value = opt.v;
                o.textContent = opt.l;
                if (String(order.status) === opt.v) o.selected = true;
                statusSelect.appendChild(o);
            });
            var statusColors = {'1':'#64748b','2':'#d97706','3':'#d97706','4':'#ea580c','5':'#4f46e5','6':'#7c3aed','7':'#2563eb','8':'#16a34a','9':'#9333ea','10':'#0891b2'};
            statusSelect.style.color = statusColors[String(order.status)] || '#333';
            statusSelect.style.fontWeight = '600';
            statusSelect.addEventListener('change', function() {
                changeOrderStatus(order.no, this.value, this);
            });
            tdStatus.appendChild(statusSelect);
            tr.appendChild(tdStatus);

            // 주문일시
            var tdDate = document.createElement('td');
            tdDate.className = 'px-2 py-1 whitespace-nowrap text-xs text-gray-600';
            tdDate.textContent = order.date || '-';
            tr.appendChild(tdDate);

            // 관리
            var tdAction = document.createElement('td');
            tdAction.className = 'px-2 py-1 whitespace-nowrap text-center text-xs';
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
        
        document.getElementById('totalItems').textContent = Number(result.data.pagination.total_items).toLocaleString();
        var pi = document.getElementById('pageInfo');
        if (pi) pi.textContent = result.data.pagination.current_page + ' / ' + result.data.pagination.total_pages + ' 페이지';
        
        renderPagination(result.data.pagination);
        
    } catch (error) {
        console.error('Failed to load orders:', error);
        var tbody = document.getElementById('ordersTableBody');
        tbody.textContent = '';
        var errRow = document.createElement('tr');
        var errTd = document.createElement('td');
        errTd.colSpan = 9;
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
    while (container.firstChild) container.removeChild(container.firstChild);

    if (total <= 1) {
        return;
    }

    var btnBase = 'text-xs rounded border transition-colors ';
    var btnNavCls = btnBase + 'px-2 py-1 border-gray-300 text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white';
    var btnActiveCls = btnBase + 'px-2.5 py-1 border-blue-600 bg-blue-600 text-white font-medium';
    var btnNormalCls = btnBase + 'px-2.5 py-1 border-gray-300 text-gray-700 hover:bg-gray-50';

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

async function changeOrderStatus(no, newStatus, selectEl) {
    var statusColors = {'1':'#64748b','2':'#d97706','3':'#d97706','4':'#ea580c','5':'#4f46e5','6':'#7c3aed','7':'#2563eb','8':'#16a34a','9':'#9333ea','10':'#0891b2'};
    try {
        var formData = new FormData();
        formData.append('action', 'update');
        formData.append('no', no);
        formData.append('order_style', newStatus);
        var response = await fetch('/dashboard/api/orders.php', { method: 'POST', body: formData });
        var result = await response.json();
        if (result.success) {
            selectEl.style.color = statusColors[newStatus] || '#333';
        } else {
            alert('상태 변경 실패: ' + result.message);
            loadOrders(currentPage);
        }
    } catch (error) {
        alert('상태 변경 중 오류가 발생했습니다.');
        loadOrders(currentPage);
    }
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
