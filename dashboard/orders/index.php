<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">주문 관리</h1>
            <p class="mt-2 text-sm text-gray-600">주문 목록 조회 및 상태 관리</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">기간</label>
                    <select id="periodFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">전체</option>
                        <option value="today">오늘</option>
                        <option value="7days">최근 7일</option>
                        <option value="30days" selected>최근 30일</option>
                        <option value="3months">최근 3개월</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                    <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">전체</option>
                        <option value="1">접수</option>
                        <option value="2">진행중</option>
                        <option value="3">완료</option>
                        <option value="deleted">삭제됨</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">품목</label>
                    <select id="productFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">전체</option>
                        <option value="스티커">스티커</option>
                        <option value="명함">명함</option>
                        <option value="전단지">전단지</option>
                        <option value="봉투">봉투</option>
                        <option value="포스터">포스터</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                    <input type="text" id="searchInput" placeholder="주문번호, 이름, 이메일" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button id="searchBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    검색
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">주문번호</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">품목</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">주문자</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">금액</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">주문일시</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">관리</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">로딩 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="pagination" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    총 <span id="totalItems">0</span>건
                </div>
                <div id="paginationButtons" class="flex gap-2">
                </div>
            </div>
        </div>
    </div>
</main>

<script>
let currentPage = 1;
let currentFilters = {};

function getStatusBadge(status) {
    const badges = {
        '1': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">접수</span>',
        '2': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">진행중</span>',
        '3': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">완료</span>',
        'deleted': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">삭제됨</span>'
    };
    return badges[status] || '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' + status + '</span>';
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
            throw new Error(result.message);
        }
        
        const tbody = document.getElementById('ordersTableBody');
        const orders = result.data.data;
        
        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">주문이 없습니다.</td></tr>';
            return;
        }
        
        tbody.innerHTML = orders.map(order => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${order.no}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${order.type || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${order.name || order.email.split('@')[0]}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${order.amount.toLocaleString()}원</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${getStatusBadge(order.status)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${order.date}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                    <a href="/dashboard/orders/view.php?no=${order.no}" class="text-blue-600 hover:text-blue-800 mr-3">상세</a>
                    <button onclick="deleteOrder(${order.no})" class="text-red-600 hover:text-red-800">삭제</button>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('totalItems').textContent = result.data.pagination.total_items;
        
        renderPagination(result.data.pagination);
        
    } catch (error) {
        console.error('Failed to load orders:', error);
        document.getElementById('ordersTableBody').innerHTML = 
            '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">주문 목록을 불러오는데 실패했습니다.</td></tr>';
    }
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationButtons');
    const buttons = [];
    
    if (pagination.current_page > 1) {
        buttons.push(`<button onclick="loadOrders(${pagination.current_page - 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">이전</button>`);
    }
    
    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
        const active = i === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
        buttons.push(`<button onclick="loadOrders(${i})" class="px-3 py-1 border border-gray-300 rounded ${active}">${i}</button>`);
    }
    
    if (pagination.current_page < pagination.total_pages) {
        buttons.push(`<button onclick="loadOrders(${pagination.current_page + 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">다음</button>`);
    }
    
    container.innerHTML = buttons.join('');
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

document.getElementById('searchBtn').addEventListener('click', function() {
    currentFilters = {
        period: document.getElementById('periodFilter').value,
        status: document.getElementById('statusFilter').value,
        product_type: document.getElementById('productFilter').value,
        search: document.getElementById('searchInput').value
    };
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
