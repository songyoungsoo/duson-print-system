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
            
            <div>
                <label for="periodSelect" class="text-sm font-medium text-gray-700 mr-2">기간:</label>
                <select id="periodSelect" class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="7">최근 7일</option>
                    <option value="30" selected>최근 30일</option>
                    <option value="90">최근 90일</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">📈 일별 주문 추이</h3>
                <div class="relative" style="height: 200px;">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">🍩 품목별 비율</h3>
                <div class="relative" style="height: 200px;">
                    <canvas id="productChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">📊 월별 매출 추이 (최근 12개월)</h3>
            <div class="relative" style="height: 180px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
</main>

<script>
let dailyChart, monthlyChart, productChart;

async function loadDailyChart(days = 30) {
    const response = await fetch(`/dashboard/api/stats.php?type=daily&days=${days}`);
    const result = await response.json();
    
    if (!result.success) {
        console.error('Failed to load daily stats');
        return;
    }
    
    const ctx = document.getElementById('dailyChart').getContext('2d');
    
    if (dailyChart) {
        dailyChart.data.labels = result.data.dates;
        dailyChart.data.datasets[0].data = result.data.counts;
        dailyChart.update();
    } else {
        dailyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: result.data.dates,
                datasets: [{
                    label: '주문 건수',
                    data: result.data.counts,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }
}

async function loadMonthlyChart() {
    const response = await fetch('/dashboard/api/stats.php?type=monthly');
    const result = await response.json();
    
    if (!result.success) {
        console.error('Failed to load monthly stats');
        return;
    }
    
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: result.data.months,
            datasets: [{
                label: '매출 (원)',
                data: result.data.revenues,
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + '원';
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

async function loadProductChart() {
    const response = await fetch('/dashboard/api/stats.php?type=products');
    const result = await response.json();
    
    if (!result.success) {
        console.error('Failed to load product stats');
        return;
    }
    
    const ctx = document.getElementById('productChart').getContext('2d');
    productChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: result.data.categories,
            datasets: [{
                data: result.data.counts,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(20, 184, 166, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(168, 85, 247, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + '건 (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

document.getElementById('periodSelect').addEventListener('change', function() {
    loadDailyChart(parseInt(this.value));
});

loadDailyChart(30);
loadMonthlyChart();
loadProductChart();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
