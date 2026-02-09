<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">주문 통계</h1>
                <p class="mt-1 text-sm text-gray-600">일별/월별 주문 추이 및 품목별 분석</p>
            </div>
            <div class="flex items-center gap-2">
                <select id="periodSelect" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="7">최근 7일</option>
                    <option value="30" selected>최근 30일</option>
                    <option value="90">최근 90일</option>
                </select>
                <button onclick="refreshData()" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">새로고침</button>
            </div>
        </div>

        <!-- 요약 카드 -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4" id="summaryCards">
            <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-16 bg-gray-200 rounded"></div></div>
            <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-16 bg-gray-200 rounded"></div></div>
            <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-16 bg-gray-200 rounded"></div></div>
            <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-16 bg-gray-200 rounded"></div></div>
        </div>

        <!-- 차트 영역 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">일별 주문 추이</h3>
                <div class="relative" style="height: 240px;">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">품목별 주문 비율</h3>
                <div class="relative" style="height: 240px;">
                    <canvas id="productChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">월별 매출 추이 (최근 12개월)</h3>
            <div class="relative" style="height: 200px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- 최근 주문 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">최근 주문</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">주문번호</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">품목</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">주문자</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">금액</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">일시</th>
                        </tr>
                    </thead>
                    <tbody id="recentOrdersBody" class="divide-y divide-gray-100">
                        <tr><td colspan="5" class="px-3 py-8 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
let dailyChart, productChart, monthlyChart;

function formatNumber(num) {
    return new Intl.NumberFormat('ko-KR').format(num);
}

function formatCurrency(num) {
    if (num >= 100000000) return (num / 100000000).toFixed(1) + '억';
    if (num >= 10000) return (num / 10000).toFixed(0) + '만';
    return formatNumber(num);
}

function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function getBadgeClass(type) {
    if (type.indexOf('스티커') !== -1 || type.indexOf('스티카') !== -1) return 'bg-blue-100 text-blue-700';
    if (type.indexOf('전단지') !== -1 || type.indexOf('리플렛') !== -1) return 'bg-yellow-100 text-yellow-700';
    if (type.indexOf('명함') !== -1 || type === 'NameCard') return 'bg-green-100 text-green-700';
    if (type.indexOf('봉투') !== -1) return 'bg-red-100 text-red-700';
    return 'bg-gray-100 text-gray-700';
}

function buildSummaryCard(label, value, unit, subText) {
    var card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow p-4';

    var labelEl = document.createElement('div');
    labelEl.className = 'text-xs text-gray-500 mb-1';
    labelEl.textContent = label;

    var valueEl = document.createElement('div');
    valueEl.className = 'text-2xl font-bold text-gray-900';
    valueEl.textContent = value;
    var unitSpan = document.createElement('span');
    unitSpan.className = 'text-sm font-normal text-gray-500 ml-1';
    unitSpan.textContent = unit;
    valueEl.appendChild(unitSpan);

    var subEl = document.createElement('div');
    subEl.className = 'text-xs text-gray-400 mt-1';

    card.appendChild(labelEl);
    card.appendChild(valueEl);
    card.appendChild(subEl);

    return { card: card, subEl: subEl };
}

function buildGrowthBadge(growth, parentEl) {
    if (growth === 0) {
        parentEl.textContent = '전월 대비 동일';
        parentEl.className = 'text-xs text-gray-400 mt-1';
        return;
    }
    parentEl.className = 'text-xs mt-1';
    var badge = document.createElement('span');
    badge.className = 'inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ' +
        (growth >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700');
    badge.textContent = (growth >= 0 ? '▲ ' : '▼ ') + Math.abs(growth) + '%';
    var suffix = document.createElement('span');
    suffix.className = 'text-gray-400 ml-1';
    suffix.textContent = '전월 대비';
    parentEl.appendChild(badge);
    parentEl.appendChild(suffix);
}

async function loadSummary() {
    try {
        var response = await fetch('/admin/api/stats.php?type=summary');
        var data = await response.json();
        var container = document.getElementById('summaryCards');
        container.textContent = '';

        var c1 = buildSummaryCard('오늘 주문', formatNumber(data.today.orders), '건', '');
        c1.subEl.textContent = '매출 ' + formatCurrency(data.today.revenue) + '원';
        container.appendChild(c1.card);

        var c2 = buildSummaryCard('이번달 주문', formatNumber(data.thisMonth.orders), '건', '');
        buildGrowthBadge(data.thisMonth.orderGrowth, c2.subEl);
        container.appendChild(c2.card);

        var c3 = buildSummaryCard('이번달 매출', formatCurrency(data.thisMonth.revenue), '원', '');
        buildGrowthBadge(data.thisMonth.revenueGrowth, c3.subEl);
        container.appendChild(c3.card);

        var c4 = buildSummaryCard('누적 주문', formatNumber(data.total.orders), '건', '');
        c4.subEl.textContent = '총 매출 ' + formatCurrency(data.total.revenue) + '원';
        container.appendChild(c4.card);
    } catch (e) { console.error('Summary error:', e); }
}

async function loadDailyChart() {
    var days = document.getElementById('periodSelect').value;
    try {
        var response = await fetch('/admin/api/stats.php?type=daily&days=' + days);
        var data = await response.json();
        var labels = data.map(function(d) { return d.day.substring(5); });
        var orders = data.map(function(d) { return d.orders; });

        if (dailyChart) dailyChart.destroy();
        dailyChart = new Chart(document.getElementById('dailyChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '주문수',
                    data: orders,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    } catch (e) { console.error('Daily chart error:', e); }
}

async function loadProductChart() {
    try {
        var response = await fetch('/admin/api/stats.php?type=products');
        var data = await response.json();
        var colors = ['#3b82f6','#ef4444','#f59e0b','#10b981','#f97316','#06b6d4','#8b5cf6','#ec4899','#6366f1','#14b8a6'];

        if (productChart) productChart.destroy();
        productChart = new Chart(document.getElementById('productChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.map(function(d) { return d.category; }),
                datasets: [{
                    data: data.map(function(d) { return d.orders; }),
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var total = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                return ctx.label + ': ' + ctx.parsed + '건 (' + ((ctx.parsed / total) * 100).toFixed(1) + '%)';
                            }
                        }
                    }
                }
            }
        });
    } catch (e) { console.error('Product chart error:', e); }
}

async function loadMonthlyChart() {
    try {
        var response = await fetch('/admin/api/stats.php?type=monthly');
        var data = await response.json();
        var labels = data.map(function(d) { return d.month.substring(2); });
        var revenue = data.map(function(d) { return d.revenue / 10000; });

        if (monthlyChart) monthlyChart.destroy();
        monthlyChart = new Chart(document.getElementById('monthlyChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출 (만원)',
                    data: revenue,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { callback: function(v) { return formatNumber(v) + '만'; } } },
                    x: { grid: { display: false } }
                }
            }
        });
    } catch (e) { console.error('Monthly chart error:', e); }
}

async function loadRecentOrders() {
    try {
        var response = await fetch('/admin/api/stats.php?type=recent');
        var data = await response.json();
        var tbody = document.getElementById('recentOrdersBody');
        tbody.textContent = '';

        data.forEach(function(order) {
            var d = new Date(order.date);
            var dateStr = (d.getMonth()+1) + '/' + d.getDate() + ' ' + d.getHours() + ':' + String(d.getMinutes()).padStart(2,'0');

            var tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';

            var tdNo = document.createElement('td');
            tdNo.className = 'px-3 py-2 text-sm font-medium text-gray-900';
            tdNo.textContent = '#' + order.no;

            var tdType = document.createElement('td');
            tdType.className = 'px-3 py-2';
            var badge = document.createElement('span');
            badge.className = 'px-2 py-0.5 text-xs font-medium rounded-full ' + getBadgeClass(order.type);
            badge.textContent = order.type;
            tdType.appendChild(badge);

            var tdName = document.createElement('td');
            tdName.className = 'px-3 py-2 text-sm text-gray-700';
            tdName.textContent = order.name;

            var tdAmount = document.createElement('td');
            tdAmount.className = 'px-3 py-2 text-sm text-gray-900 text-right';
            tdAmount.textContent = formatNumber(order.amount) + '원';

            var tdDate = document.createElement('td');
            tdDate.className = 'px-3 py-2 text-sm text-gray-500';
            tdDate.textContent = dateStr;

            tr.appendChild(tdNo);
            tr.appendChild(tdType);
            tr.appendChild(tdName);
            tr.appendChild(tdAmount);
            tr.appendChild(tdDate);
            tbody.appendChild(tr);
        });
    } catch (e) { console.error('Recent orders error:', e); }
}

function refreshData() {
    loadSummary();
    loadDailyChart();
    loadProductChart();
    loadMonthlyChart();
    loadRecentOrders();
}

document.getElementById('periodSelect').addEventListener('change', loadDailyChart);
refreshData();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
