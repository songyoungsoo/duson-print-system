<?php
/**
 * Payment Status Module
 * 결제 현황 조회 (Read-only)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <!-- Header + Filters in one line -->
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <h1 class="text-lg font-bold text-gray-900 mr-2">결제 현황</h1>

            <select id="period-filter" class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                <option value="all">전체</option>
                <option value="today">오늘</option>
                <option value="week">최근 7일</option>
                <option value="month" selected>최근 30일</option>
                <option value="3months">최근 3개월</option>
            </select>

            <select id="status-filter" class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                <option value="all">상태: 전체</option>
                <option value="completed">완료</option>
                <option value="pending">대기</option>
                <option value="cancelled">취소</option>
            </select>

            <select id="method-filter" class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                <option value="all">결제: 전체</option>
                <option value="계좌이체">계좌이체</option>
                <option value="카드결제">카드결제</option>
                <option value="현금">현금</option>
                <option value="기타">기타</option>
            </select>

            <input type="text" id="search-input" placeholder="주문번호, 입금자명"
                   class="w-40 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">

            <button onclick="loadPayments(1)" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">검색</button>
            <button onclick="resetFilters()" class="px-3 py-1 text-xs bg-gray-500 text-white rounded hover:bg-gray-600">초기화</button>

        </div>

        <!-- Statistics Cards (compact) -->
        <div id="stats-container" class="grid grid-cols-4 gap-2 mb-2"></div>

        <!-- Payment List Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">주문번호</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">고객명</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">결제금액</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">결제방법</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">입금자명</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">상태</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">주문일시</th>
                        </tr>
                    </thead>
                    <tbody id="payment-list" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-2 py-1 text-center text-gray-500 text-xs">
                                데이터를 불러오는 중...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="px-3 py-1.5 border-t border-gray-200 flex items-center justify-between text-xs">
                <span class="text-gray-500">총 <span id="totalItems">0</span>건</span>
                <div id="paginationButtons" class="flex items-center gap-1"></div>
            </div>
        </div>
    </div>
</main>

<script>
var currentPage = 1;

function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString('ko-KR');
}

document.addEventListener('DOMContentLoaded', function() {
    loadPayments(1);
});

async function loadPayments(page) {
    currentPage = page || 1;

    var params = new URLSearchParams({
        action: 'list',
        page: currentPage,
        period: document.getElementById('period-filter').value,
        status: document.getElementById('status-filter').value,
        method: document.getElementById('method-filter').value,
        search: document.getElementById('search-input').value
    });

    try {
        var response = await fetch('/dashboard/api/payments.php?' + params);
        var result = await response.json();

        if (result.success) {
            renderStats(result.data.stats);
            renderPayments(result.data.data);
            document.getElementById('totalItems').textContent = Number(result.data.pagination.total_items).toLocaleString();
            renderPagination(result.data.pagination);
        } else {
            showError(result.message || '데이터를 불러오는데 실패했습니다.');
        }
    } catch (error) {
        console.error('Error loading payments:', error);
        showError('서버 오류가 발생했습니다.');
    }
}

function animateNumber(el, target, prefix, suffix, duration) {
    var start = 0;
    var startTime = null;
    function easeOut(t) { return 1 - Math.pow(1 - t, 3); }
    function step(timestamp) {
        if (!startTime) startTime = timestamp;
        var progress = Math.min((timestamp - startTime) / duration, 1);
        var current = Math.round(easeOut(progress) * target);
        el.textContent = prefix + current.toLocaleString('ko-KR') + suffix;
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

function renderStats(stats) {
    var el = document.getElementById('stats-container');
    while (el.firstChild) el.removeChild(el.firstChild);
    if (!stats) return;

    var items = [
        {icon: '\uD83D\uDCB0', label: '총 결제금액', num: Number(stats.total_amount) || 0, prefix: '\u20a9', suffix: '', cls: 'text-gray-900', delay: 0},
        {icon: '\u2705', label: '완료', num: Number(stats.completed_count) || 0, prefix: '', suffix: '건', cls: 'text-green-600', delay: 100},
        {icon: '\u23F3', label: '대기', num: Number(stats.pending_count) || 0, prefix: '', suffix: '건', cls: 'text-yellow-600', delay: 200},
        {icon: '\u274C', label: '취소', num: Number(stats.cancelled_count) || 0, prefix: '', suffix: '건', cls: 'text-red-600', delay: 300}
    ];

    items.forEach(function(item) {
        var card = document.createElement('div');
        card.className = 'bg-white rounded shadow px-3 py-1.5';
        var lbl = document.createElement('div');
        lbl.className = 'text-xs text-gray-500';
        lbl.textContent = item.icon + ' ' + item.label;
        var val = document.createElement('div');
        val.className = 'text-base font-bold ' + item.cls;
        val.textContent = item.prefix + '0' + item.suffix;
        card.appendChild(lbl);
        card.appendChild(val);
        el.appendChild(card);

        setTimeout(function() {
            animateNumber(val, item.num, item.prefix, item.suffix, 800);
        }, item.delay);
    });
}

function renderPayments(payments) {
    var tbody = document.getElementById('payment-list');

    // Clear existing rows
    while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

    if (!payments || payments.length === 0) {
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = 7;
        td.className = 'px-2 py-1 text-center text-gray-500 text-xs';
        td.textContent = '결제 내역이 없습니다.';
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }

    payments.forEach(function(p, idx) {
        var tr = document.createElement('tr');
        tr.className = idx % 2 === 1 ? 'hover:bg-gray-100' : 'hover:bg-gray-50';
        if (idx % 2 === 1) tr.style.backgroundColor = '#e6f7ff';

        // 주문번호
        var td1 = document.createElement('td');
        td1.className = 'px-2 py-1 whitespace-nowrap text-xs';
        var a = document.createElement('a');
        a.href = '/admin/mlangprintauto/admin.php?mode=OrderView&no=' + encodeURIComponent(p.order_no);
        a.target = '_blank';
        a.className = 'text-blue-600 hover:text-blue-800 font-medium';
        a.textContent = p.order_no;
        td1.appendChild(a);
        tr.appendChild(td1);

        // 고객명
        var td2 = document.createElement('td');
        td2.className = 'px-2 py-1 whitespace-nowrap text-xs text-gray-900';
        td2.textContent = p.customer_name || '-';
        tr.appendChild(td2);

        // 결제금액
        var td3 = document.createElement('td');
        td3.className = 'px-2 py-1 whitespace-nowrap text-xs font-medium text-gray-900';
        td3.textContent = '\u20a9' + formatNumber(p.amount);
        tr.appendChild(td3);

        // 결제방법
        var td4 = document.createElement('td');
        td4.className = 'px-2 py-1 whitespace-nowrap text-xs text-gray-600';
        td4.textContent = p.payment_method || '-';
        tr.appendChild(td4);

        // 입금자명
        var td5 = document.createElement('td');
        td5.className = 'px-2 py-1 whitespace-nowrap text-xs text-gray-600';
        td5.textContent = p.depositor_name || '-';
        tr.appendChild(td5);

        // 상태
        var td6 = document.createElement('td');
        td6.className = 'px-2 py-1 whitespace-nowrap';
        var badge = document.createElement('span');
        badge.className = 'px-1.5 py-0.5 text-xs font-semibold rounded-full';
        var statusMap = {
            'completed': {text: '완료', bg: 'bg-green-100 text-green-800'},
            'pending': {text: '대기', bg: 'bg-yellow-100 text-yellow-800'},
            'cancelled': {text: '취소', bg: 'bg-red-100 text-red-800'}
        };
        var st = statusMap[p.status] || {text: '-', bg: 'bg-gray-100 text-gray-800'};
        badge.className += ' ' + st.bg;
        badge.textContent = st.text;
        td6.appendChild(badge);
        tr.appendChild(td6);

        // 주문일시
        var td7 = document.createElement('td');
        td7.className = 'px-2 py-1 whitespace-nowrap text-xs text-gray-500';
        td7.textContent = formatDateTime(p.order_date);
        tr.appendChild(td7);

        tbody.appendChild(tr);
    });
}

function renderPagination(pagination) {
    var container = document.getElementById('paginationButtons');
    while (container.firstChild) container.removeChild(container.firstChild);

    var cur = pagination.current_page;
    var total = pagination.total_pages;
    if (total <= 1) return;

    var btnBase = 'text-xs rounded border transition-colors ';
    var btnNavCls = btnBase + 'px-2 py-1 border-gray-300 text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed';
    var btnActiveCls = btnBase + 'px-2.5 py-1 border-blue-600 bg-blue-600 text-white font-medium';
    var btnNormalCls = btnBase + 'px-2.5 py-1 border-gray-300 text-gray-700 hover:bg-gray-50';

    function makeBtn(label, page, cls, disabled) {
        var btn = document.createElement('button');
        btn.className = cls;
        btn.textContent = label;
        if (disabled) btn.disabled = true;
        else btn.addEventListener('click', function() { loadPayments(page); });
        return btn;
    }

    function makeDots() {
        var span = document.createElement('span');
        span.className = 'px-1 text-gray-400';
        span.textContent = '\u2026';
        return span;
    }

    container.appendChild(makeBtn('\u00AB', 1, btnNavCls, cur === 1));
    container.appendChild(makeBtn('\u2039', cur - 1, btnNavCls, cur === 1));

    var delta = 2;
    var left = cur - delta, right = cur + delta;
    var pages = [1];
    if (left > 3) pages.push('...');
    else for (var i = 2; i < left; i++) pages.push(i);
    for (var i = Math.max(2, left); i <= Math.min(total - 1, right); i++) pages.push(i);
    if (right < total - 2) pages.push('...');
    else for (var i = right + 1; i < total; i++) pages.push(i);
    if (total > 1) pages.push(total);

    var seen = {};
    for (var j = 0; j < pages.length; j++) {
        var p = pages[j];
        if (p === '...') { container.appendChild(makeDots()); }
        else if (!seen[p]) {
            seen[p] = true;
            container.appendChild(makeBtn(String(p), p, p === cur ? btnActiveCls : btnNormalCls, false));
        }
    }

    container.appendChild(makeBtn('\u203A', cur + 1, btnNavCls, cur === total));
    container.appendChild(makeBtn('\u00BB', total, btnNavCls, cur === total));
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    var d = new Date(dateString);
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    var h = String(d.getHours()).padStart(2, '0');
    var min = String(d.getMinutes()).padStart(2, '0');
    return y + '-' + m + '-' + day + ' ' + h + ':' + min;
}

function resetFilters() {
    document.getElementById('period-filter').value = 'month';
    document.getElementById('status-filter').value = 'all';
    document.getElementById('method-filter').value = 'all';
    document.getElementById('search-input').value = '';
    loadPayments(1);
}

function showError(message) {
    var tbody = document.getElementById('payment-list');
    while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
    var tr = document.createElement('tr');
    var td = document.createElement('td');
    td.colSpan = 7;
    td.className = 'px-2 py-1 text-center text-red-600 text-xs';
    td.textContent = message;
    tr.appendChild(td);
    tbody.appendChild(tr);
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
