<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <!-- Header + Filter in one line -->
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <h1 class="text-lg font-bold text-gray-900 mr-2">회원 관리</h1>
            <input type="text" id="searchInput" placeholder="아이디, 이름, 이메일, 전화번호 검색"
                   class="w-64 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            <button id="searchBtn" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">검색</button>
        </div>

        <!-- Members Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">ID</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">아이디</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">이름</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">이메일</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">전화번호</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">가입일</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">관리</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-2 py-1 text-center text-gray-500 text-xs">로딩 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="px-3 py-1.5 border-t border-gray-200 flex items-center justify-between text-xs">
                <span class="text-gray-500">총 <span id="totalItems">0</span>명</span>
                <div id="paginationButtons" class="flex items-center gap-1"></div>
            </div>
        </div>
    </div>
</main>

<script>
var currentPage = 1;
var currentSearch = '';

async function loadMembers(page) {
    currentPage = page || 1;

    var params = new URLSearchParams({
        action: 'list',
        page: currentPage,
        search: currentSearch
    });

    try {
        var response = await fetch('/dashboard/api/members.php?' + params);
        var result = await response.json();

        if (!result.success) throw new Error(result.message);

        var tbody = document.getElementById('membersTableBody');
        var members = result.data.data;

        while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

        if (members.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 7;
            td.className = 'px-2 py-1 text-center text-gray-500 text-xs';
            td.textContent = '회원이 없습니다.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        members.forEach(function(m, idx) {
            var tr = document.createElement('tr');
            tr.className = idx % 2 === 1 ? 'hover:bg-gray-100' : 'hover:bg-gray-50';
            if (idx % 2 === 1) tr.style.backgroundColor = '#e6f7ff';

            var td1 = document.createElement('td');
            td1.className = 'px-2 py-2 whitespace-nowrap text-xs font-medium text-gray-900';
            td1.textContent = m.id;
            tr.appendChild(td1);

            var td2 = document.createElement('td');
            td2.className = 'px-2 py-2 whitespace-nowrap text-xs text-gray-900';
            td2.textContent = m.username;
            tr.appendChild(td2);

            var td3 = document.createElement('td');
            td3.className = 'px-2 py-2 whitespace-nowrap text-xs text-gray-600';
            td3.textContent = m.name;
            tr.appendChild(td3);

            var td4 = document.createElement('td');
            td4.className = 'px-2 py-2 whitespace-nowrap text-xs text-gray-600';
            td4.textContent = m.email || '-';
            tr.appendChild(td4);

            var td5 = document.createElement('td');
            td5.className = 'px-2 py-2 whitespace-nowrap text-xs text-gray-600';
            td5.textContent = m.phone || '-';
            tr.appendChild(td5);

            var td6 = document.createElement('td');
            td6.className = 'px-2 py-2 whitespace-nowrap text-xs text-gray-500';
            td6.textContent = m.created_at || '-';
            tr.appendChild(td6);

            var td7 = document.createElement('td');
            td7.className = 'px-2 py-2 whitespace-nowrap text-center text-xs';
            var a = document.createElement('a');
            a.href = '/dashboard/members/view.php?id=' + encodeURIComponent(m.id);
            a.className = 'text-blue-600 hover:text-blue-800';
            a.textContent = '상세';
            td7.appendChild(a);
            tr.appendChild(td7);

            tbody.appendChild(tr);
        });

        document.getElementById('totalItems').textContent = Number(result.data.pagination.total_items).toLocaleString();
        renderPagination(result.data.pagination);

    } catch (error) {
        console.error('Failed to load members:', error);
        var tbody = document.getElementById('membersTableBody');
        while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = 7;
        td.className = 'px-2 py-1 text-center text-red-500 text-xs';
        td.textContent = '회원 목록을 불러오는데 실패했습니다.';
        tr.appendChild(td);
        tbody.appendChild(tr);
    }
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
        else btn.addEventListener('click', function() { loadMembers(page); });
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

document.getElementById('searchBtn').addEventListener('click', function() {
    currentSearch = document.getElementById('searchInput').value;
    loadMembers(1);
});

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') document.getElementById('searchBtn').click();
});

loadMembers(1);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
