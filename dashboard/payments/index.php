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
<main class="flex-1 overflow-y-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900">💳 결제 현황</h1>
            <p class="mt-1 text-sm text-gray-600">주문별 결제 정보 조회</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">검색 필터</h3>
            <div class="flex gap-3 items-end flex-wrap">
                <!-- Period Filter -->
                <div class="w-32">
                    <label class="block text-xs font-medium text-gray-700 mb-1">기간</label>
                    <select id="period-filter" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all">전체</option>
                        <option value="today">오늘</option>
                        <option value="week">최근 7일</option>
                        <option value="month" selected>최근 30일</option>
                        <option value="3months">최근 3개월</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-700 mb-1">결제상태</label>
                    <select id="status-filter" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all">전체</option>
                        <option value="completed">완료</option>
                        <option value="pending">대기</option>
                        <option value="cancelled">취소</option>
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-700 mb-1">결제방법</label>
                    <select id="method-filter" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all">전체</option>
                        <option value="계좌이체">계좌이체</option>
                        <option value="카드결제">카드결제</option>
                        <option value="현금">현금</option>
                        <option value="기타">기타</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-700 mb-1">검색</label>
                    <input type="text" id="search-input" placeholder="주문번호, 입금자명" 
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <button onclick="loadPayments(1)" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    검색
                </button>
                <button onclick="resetFilters()" class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    초기화
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div id="stats-container" class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <!-- Stats will be loaded via JavaScript -->
        </div>

        <!-- Payment List Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">주문번호</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">고객명</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">결제금액</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">결제방법</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">입금자명</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">주문일시</th>
                        </tr>
                    </thead>
                    <tbody id="payment-list" class="bg-white divide-y divide-gray-200">
                        <!-- Payments will be loaded via JavaScript -->
                        <tr>
                            <td colspan="7" class="px-3 py-4 text-center text-gray-500 text-sm">
                                데이터를 불러오는 중...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="px-4 py-3 border-t border-gray-200">
                <!-- Pagination will be loaded via JavaScript -->
            </div>
        </div>
    </div>
</main>

<script>
let currentPage = 1;

// Format number with comma separators
function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString('ko-KR');
}

// Load payments on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPayments(1);
});

// Load payments function
async function loadPayments(page = 1) {
    currentPage = page;
    
    const period = document.getElementById('period-filter').value;
    const status = document.getElementById('status-filter').value;
    const method = document.getElementById('method-filter').value;
    const search = document.getElementById('search-input').value;
    
    const params = new URLSearchParams({
        action: 'list',
        page: page,
        period: period,
        status: status,
        method: method,
        search: search
    });
    
    try {
        const response = await fetch(`/dashboard/api/payments.php?${params}`);
        const result = await response.json();
        
        if (result.success) {
            renderStats(result.data.stats);
            renderPayments(result.data.data);
            renderPagination(result.data.pagination);
        } else {
            showError(result.message || '데이터를 불러오는데 실패했습니다.');
        }
    } catch (error) {
        console.error('Error loading payments:', error);
        showError('서버 오류가 발생했습니다.');
    }
}

// Render statistics
function renderStats(stats) {
    const statsHtml = `
        <div class="bg-white rounded-lg shadow p-3 hover:shadow-lg transition-shadow">
            <div class="text-xs font-medium text-gray-600 mb-1">💰 총 결제금액</div>
            <div class="text-2xl font-bold text-gray-900">₩${formatNumber(stats.total_amount)}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-3 hover:shadow-lg transition-shadow">
            <div class="text-xs font-medium text-gray-600 mb-1">✅ 완료</div>
            <div class="text-2xl font-bold text-green-600">${formatNumber(stats.completed_count)}건</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-3 hover:shadow-lg transition-shadow">
            <div class="text-xs font-medium text-gray-600 mb-1">⏳ 대기</div>
            <div class="text-2xl font-bold text-yellow-600">${formatNumber(stats.pending_count)}건</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-3 hover:shadow-lg transition-shadow">
            <div class="text-xs font-medium text-gray-600 mb-1">❌ 취소</div>
            <div class="text-2xl font-bold text-red-600">${formatNumber(stats.cancelled_count)}건</div>
        </div>
    `;
    
    document.getElementById('stats-container').innerHTML = statsHtml;
}

// Render payments table
function renderPayments(payments) {
    const tbody = document.getElementById('payment-list');
    
    if (payments.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-3 py-4 text-center text-gray-500 text-sm">
                    결제 내역이 없습니다.
                </td>
            </tr>
        `;
        return;
    }
    
    const rows = payments.map(payment => {
        const statusBadge = getStatusBadge(payment.status);
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 whitespace-nowrap text-sm">
                    <a href="/admin/mlangprintauto/admin.php?mode=OrderView&no=${payment.order_no}" 
                       target="_blank"
                       class="text-blue-600 hover:text-blue-800 font-medium">
                        ${payment.order_no}
                    </a>
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                    ${payment.customer_name || '-'}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                    ₩${formatNumber(payment.amount)}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">
                    ${payment.payment_method || '-'}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">
                    ${payment.depositor_name || '-'}
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    ${statusBadge}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                    ${formatDateTime(payment.order_date)}
                </td>
            </tr>
        `;
    }).join('');
    
    tbody.innerHTML = rows;
}

// Render pagination
function renderPagination(pagination) {
    const container = document.getElementById('pagination');
    
    if (pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="flex items-center justify-between">';
    html += `<div class="text-sm text-gray-700">
        전체 <span class="font-medium">${formatNumber(pagination.total_items)}</span>건 
        (${pagination.current_page} / ${pagination.total_pages} 페이지)
    </div>`;
    
    html += '<div class="flex gap-2">';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `<button onclick="loadPayments(${pagination.current_page - 1})" 
                        class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    이전
                </button>`;
    }
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 5);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 5);
    
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page 
            ? 'bg-blue-600 text-white' 
            : 'border border-gray-300 hover:bg-gray-50';
        
        html += `<button onclick="loadPayments(${i})" 
                        class="px-3 py-1 rounded-lg transition-colors ${activeClass}">
                    ${i}
                </button>`;
    }
    
    // Next button
    if (pagination.current_page < pagination.total_pages) {
        html += `<button onclick="loadPayments(${pagination.current_page + 1})" 
                        class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    다음
                </button>`;
    }
    
    html += '</div></div>';
    container.innerHTML = html;
}

// Get status badge HTML
function getStatusBadge(status) {
    const badges = {
        'completed': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">완료</span>',
        'pending': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">대기</span>',
        'cancelled': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">취소</span>'
    };
    
    return badges[status] || '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">알 수 없음</span>';
}

// Format date time
function formatDateTime(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day} ${hours}:${minutes}`;
}

// Reset filters
function resetFilters() {
    document.getElementById('period-filter').value = 'month';
    document.getElementById('status-filter').value = 'all';
    document.getElementById('method-filter').value = 'all';
    document.getElementById('search-input').value = '';
    loadPayments(1);
}

// Show error message
function showError(message) {
    const tbody = document.getElementById('payment-list');
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="px-3 py-4 text-center text-red-600 text-sm">
                ${message}
            </td>
        </tr>
    `;
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
