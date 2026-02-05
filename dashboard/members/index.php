<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">회원 관리</h1>
            <p class="mt-2 text-sm text-gray-600">회원 목록 조회 및 정보 관리</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex gap-4">
                <input type="text" id="searchInput" placeholder="아이디, 이름, 이메일, 전화번호 검색" 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <button id="searchBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    검색
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">아이디</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">이름</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">이메일</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">전화번호</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">가입일</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">관리</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">로딩 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="pagination" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    총 <span id="totalItems">0</span>명
                </div>
                <div id="paginationButtons" class="flex gap-2">
                </div>
            </div>
        </div>
    </div>
</main>

<script>
let currentPage = 1;
let currentSearch = '';

async function loadMembers(page = 1) {
    currentPage = page;
    
    const params = new URLSearchParams({
        action: 'list',
        page: page,
        search: currentSearch
    });
    
    try {
        const response = await fetch(`/dashboard/api/members.php?${params}`);
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }
        
        const tbody = document.getElementById('membersTableBody');
        const members = result.data.data;
        
        if (members.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">회원이 없습니다.</td></tr>';
            return;
        }
        
        tbody.innerHTML = members.map(member => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${member.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${member.username}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${member.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${member.email || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${member.phone || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${member.created_at || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                    <a href="/dashboard/members/view.php?id=${member.id}" class="text-blue-600 hover:text-blue-800">상세</a>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('totalItems').textContent = result.data.pagination.total_items;
        
        renderPagination(result.data.pagination);
        
    } catch (error) {
        console.error('Failed to load members:', error);
        document.getElementById('membersTableBody').innerHTML = 
            '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">회원 목록을 불러오는데 실패했습니다.</td></tr>';
    }
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationButtons');
    const buttons = [];
    
    if (pagination.current_page > 1) {
        buttons.push(`<button onclick="loadMembers(${pagination.current_page - 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">이전</button>`);
    }
    
    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
        const active = i === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
        buttons.push(`<button onclick="loadMembers(${i})" class="px-3 py-1 border border-gray-300 rounded ${active}">${i}</button>`);
    }
    
    if (pagination.current_page < pagination.total_pages) {
        buttons.push(`<button onclick="loadMembers(${pagination.current_page + 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">다음</button>`);
    }
    
    container.innerHTML = buttons.join('');
}

document.getElementById('searchBtn').addEventListener('click', function() {
    currentSearch = document.getElementById('searchInput').value;
    loadMembers(1);
});

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('searchBtn').click();
    }
});

loadMembers(1);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
