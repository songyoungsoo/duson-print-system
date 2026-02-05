<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">고객 문의</h1>
            <p class="mt-2 text-sm text-gray-600">고객 문의 조회 및 답변 관리</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex gap-4">
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">전체</option>
                    <option value="pending">미답변</option>
                    <option value="answered">답변완료</option>
                </select>
                <button id="filterBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    필터 적용
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">제목</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작성자</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">분류</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작성일</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">관리</th>
                        </tr>
                    </thead>
                    <tbody id="inquiriesTableBody" class="bg-white divide-y divide-gray-200">
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
let currentStatus = '';

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">미답변</span>',
        'answered': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">답변완료</span>'
    };
    return badges[status] || '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' + status + '</span>';
}

async function loadInquiries(page = 1) {
    currentPage = page;
    
    const params = new URLSearchParams({
        action: 'list',
        page: page,
        status: currentStatus
    });
    
    try {
        const response = await fetch(`/dashboard/api/inquiries.php?${params}`);
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }
        
        const tbody = document.getElementById('inquiriesTableBody');
        const inquiries = result.data.data;
        
        if (inquiries.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">문의가 없습니다.</td></tr>';
            return;
        }
        
        tbody.innerHTML = inquiries.map(inquiry => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${inquiry.id}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${inquiry.subject}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${inquiry.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${inquiry.category}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${getStatusBadge(inquiry.status)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${inquiry.created_at}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                    <a href="/dashboard/inquiries/view.php?id=${inquiry.id}" class="text-blue-600 hover:text-blue-800">상세</a>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('totalItems').textContent = result.data.pagination.total_items;
        
        renderPagination(result.data.pagination);
        
    } catch (error) {
        console.error('Failed to load inquiries:', error);
        document.getElementById('inquiriesTableBody').innerHTML = 
            '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">문의 목록을 불러오는데 실패했습니다.</td></tr>';
    }
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationButtons');
    const buttons = [];
    
    if (pagination.current_page > 1) {
        buttons.push(`<button onclick="loadInquiries(${pagination.current_page - 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">이전</button>`);
    }
    
    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
        const active = i === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
        buttons.push(`<button onclick="loadInquiries(${i})" class="px-3 py-1 border border-gray-300 rounded ${active}">${i}</button>`);
    }
    
    if (pagination.current_page < pagination.total_pages) {
        buttons.push(`<button onclick="loadInquiries(${pagination.current_page + 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">다음</button>`);
    }
    
    container.innerHTML = buttons.join('');
}

document.getElementById('filterBtn').addEventListener('click', function() {
    currentStatus = document.getElementById('statusFilter').value;
    loadInquiries(1);
});

loadInquiries(1);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
