<?php
/**
 * 두손기획 통계 대시보드
 * Google Analytics 스타일 디자인
 */
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/../db.php';

// 관리자 인증 필수
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>통계 대시보드 - 두손기획</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f5f5f5;
            color: #202124;
            line-height: 1.6;
        }

        /* 헤더 */
        .header {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 500;
            color: #5f6368;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header h1::before {
            content: '📊';
            font-size: 28px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: 1px solid #dadce0;
            background: #fff;
            color: #5f6368;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn:hover {
            background: #f1f3f4;
            border-color: #5f6368;
        }

        .btn-primary {
            background: #1a73e8;
            color: #fff;
            border-color: #1a73e8;
        }

        .btn-primary:hover {
            background: #1557b0;
        }

        /* 컨테이너 */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }

        /* 요약 카드 */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            transition: box-shadow 0.2s;
        }

        .summary-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .summary-card .label {
            font-size: 14px;
            color: #5f6368;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-card .value {
            font-size: 36px;
            font-weight: 500;
            color: #202124;
        }

        .summary-card .sub-value {
            font-size: 14px;
            color: #5f6368;
            margin-top: 4px;
        }

        .summary-card .growth {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .growth.positive {
            background: #e6f4ea;
            color: #1e8e3e;
        }

        .growth.negative {
            background: #fce8e6;
            color: #d93025;
        }

        /* 차트 섹션 */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .chart-card h3 {
            font-size: 16px;
            font-weight: 500;
            color: #202124;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        /* 최근 주문 테이블 */
        .recent-orders {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .recent-orders h3 {
            font-size: 16px;
            font-weight: 500;
            color: #202124;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .orders-table th {
            background: #f8f9fa;
            font-weight: 500;
            color: #5f6368;
            font-size: 13px;
        }

        .orders-table td {
            font-size: 14px;
        }

        .orders-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-sticker { background: #e8f0fe; color: #1a73e8; }
        .badge-flyer { background: #fef7e0; color: #f9ab00; }
        .badge-namecard { background: #e6f4ea; color: #1e8e3e; }
        .badge-envelope { background: #fce8e6; color: #d93025; }
        .badge-default { background: #f1f3f4; color: #5f6368; }

        /* 로딩 */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 200px;
            color: #5f6368;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e0e0e0;
            border-top-color: #1a73e8;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* 날짜 선택 */
        .date-range {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #5f6368;
        }

        .date-range select {
            padding: 8px 12px;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 14px;
            color: #202124;
            background: #fff;
            cursor: pointer;
        }

        /* 푸터 */
        .footer {
            text-align: center;
            padding: 24px;
            color: #5f6368;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>두손기획 통계</h1>
        <div class="header-actions">
            <div class="date-range">
                <span>기간:</span>
                <select id="periodSelect" onchange="loadDailyChart()">
                    <option value="7">최근 7일</option>
                    <option value="30" selected>최근 30일</option>
                    <option value="90">최근 90일</option>
                </select>
            </div>
            <button class="btn" onclick="refreshData()">
                🔄 새로고침
            </button>
            <button class="btn" onclick="location.href='/admin/mlangprintauto/admin.php'">
                ← 관리자로 돌아가기
            </button>
        </div>
    </div>

    <div class="container">
        <!-- 요약 카드 -->
        <div class="summary-grid" id="summaryCards">
            <div class="loading"><div class="spinner"></div></div>
        </div>

        <!-- 차트 영역 -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3>📈 일별 주문 추이</h3>
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3>🥧 품목별 주문 비율</h3>
                <div class="chart-container">
                    <canvas id="productChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 월별 매출 차트 -->
        <div class="chart-card" style="margin-bottom: 24px;">
            <h3>💰 월별 매출 추이 (최근 12개월)</h3>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- 최근 주문 -->
        <div class="recent-orders">
            <h3>📋 최근 주문</h3>
            <table class="orders-table" id="recentOrdersTable">
                <thead>
                    <tr>
                        <th>주문번호</th>
                        <th>품목</th>
                        <th>주문자</th>
                        <th>금액</th>
                        <th>일시</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" class="loading"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        © <?php echo date('Y'); ?> 두손기획 - 통계 대시보드
    </div>

    <script>
        // 차트 인스턴스 저장
        let dailyChart, productChart, monthlyChart;

        // 숫자 포맷
        function formatNumber(num) {
            return new Intl.NumberFormat('ko-KR').format(num);
        }

        // 금액 포맷
        function formatCurrency(num) {
            if (num >= 100000000) {
                return (num / 100000000).toFixed(1) + '억';
            } else if (num >= 10000) {
                return (num / 10000).toFixed(0) + '만';
            }
            return formatNumber(num);
        }

        // 품목별 배지 클래스
        function getBadgeClass(type) {
            if (type.includes('스티커') || type.includes('스티카')) return 'badge-sticker';
            if (type.includes('전단지') || type.includes('리플렛')) return 'badge-flyer';
            if (type.includes('명함') || type === 'NameCard') return 'badge-namecard';
            if (type.includes('봉투')) return 'badge-envelope';
            return 'badge-default';
        }

        // 요약 카드 로드
        async function loadSummary() {
            try {
                const response = await fetch('/admin/api/stats.php?type=summary');
                const data = await response.json();

                const summaryHtml = `
                    <div class="summary-card">
                        <div class="label">📅 오늘 주문</div>
                        <div class="value">${formatNumber(data.today.orders)}건</div>
                        <div class="sub-value">매출: ${formatCurrency(data.today.revenue)}원</div>
                    </div>
                    <div class="summary-card">
                        <div class="label">📆 이번달 주문</div>
                        <div class="value">${formatNumber(data.thisMonth.orders)}건</div>
                        <div class="sub-value">
                            ${data.thisMonth.orderGrowth !== 0 ?
                                `<span class="growth ${data.thisMonth.orderGrowth >= 0 ? 'positive' : 'negative'}">
                                    ${data.thisMonth.orderGrowth >= 0 ? '▲' : '▼'} ${Math.abs(data.thisMonth.orderGrowth)}%
                                </span> 전월 대비` : ''}
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="label">💰 이번달 매출</div>
                        <div class="value">${formatCurrency(data.thisMonth.revenue)}원</div>
                        <div class="sub-value">
                            ${data.thisMonth.revenueGrowth !== 0 ?
                                `<span class="growth ${data.thisMonth.revenueGrowth >= 0 ? 'positive' : 'negative'}">
                                    ${data.thisMonth.revenueGrowth >= 0 ? '▲' : '▼'} ${Math.abs(data.thisMonth.revenueGrowth)}%
                                </span> 전월 대비` : ''}
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="label">📊 누적 주문</div>
                        <div class="value">${formatNumber(data.total.orders)}건</div>
                        <div class="sub-value">총 매출: ${formatCurrency(data.total.revenue)}원</div>
                    </div>
                `;

                document.getElementById('summaryCards').innerHTML = summaryHtml;
            } catch (error) {
                console.error('Summary load error:', error);
            }
        }

        // 일별 차트 로드
        async function loadDailyChart() {
            const days = document.getElementById('periodSelect').value;
            try {
                const response = await fetch(`/admin/api/stats.php?type=daily&days=${days}`);
                const data = await response.json();

                const labels = data.map(d => d.day.substring(5)); // MM-DD 형식
                const orders = data.map(d => d.orders);

                if (dailyChart) dailyChart.destroy();

                const ctx = document.getElementById('dailyChart').getContext('2d');
                dailyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '주문수',
                            data: orders,
                            borderColor: '#1a73e8',
                            backgroundColor: 'rgba(26, 115, 232, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f3f4' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Daily chart error:', error);
            }
        }

        // 품목별 차트 로드
        async function loadProductChart() {
            try {
                const response = await fetch('/admin/api/stats.php?type=products');
                const data = await response.json();

                const labels = data.map(d => d.category);
                const orders = data.map(d => d.orders);
                const colors = [
                    '#1a73e8', '#ea4335', '#fbbc04', '#34a853',
                    '#ff6d01', '#46bdc6', '#7baaf7', '#f07b72',
                    '#fcd04f', '#57bb8a'
                ];

                if (productChart) productChart.destroy();

                const ctx = document.getElementById('productChart').getContext('2d');
                productChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: orders,
                            backgroundColor: colors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 12,
                                    padding: 12
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Product chart error:', error);
            }
        }

        // 월별 차트 로드
        async function loadMonthlyChart() {
            try {
                const response = await fetch('/admin/api/stats.php?type=monthly');
                const data = await response.json();

                const labels = data.map(d => d.month.substring(2)); // YY-MM 형식
                const revenue = data.map(d => d.revenue / 10000); // 만원 단위

                if (monthlyChart) monthlyChart.destroy();

                const ctx = document.getElementById('monthlyChart').getContext('2d');
                monthlyChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '매출 (만원)',
                            data: revenue,
                            backgroundColor: '#34a853',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f3f4' },
                                ticks: {
                                    callback: function(value) {
                                        return formatNumber(value) + '만';
                                    }
                                }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Monthly chart error:', error);
            }
        }

        // 최근 주문 로드
        async function loadRecentOrders() {
            try {
                const response = await fetch('/admin/api/stats.php?type=recent');
                const data = await response.json();

                let html = '';
                data.forEach(order => {
                    const date = new Date(order.date);
                    const dateStr = `${date.getMonth()+1}/${date.getDate()} ${date.getHours()}:${String(date.getMinutes()).padStart(2,'0')}`;

                    html += `
                        <tr>
                            <td><strong>#${order.no}</strong></td>
                            <td><span class="badge ${getBadgeClass(order.type)}">${order.type}</span></td>
                            <td>${order.name}</td>
                            <td>${formatNumber(order.amount)}원</td>
                            <td>${dateStr}</td>
                        </tr>
                    `;
                });

                document.querySelector('#recentOrdersTable tbody').innerHTML = html;
            } catch (error) {
                console.error('Recent orders error:', error);
            }
        }

        // 전체 새로고침
        function refreshData() {
            loadSummary();
            loadDailyChart();
            loadProductChart();
            loadMonthlyChart();
            loadRecentOrders();
        }

        // 초기 로드
        document.addEventListener('DOMContentLoaded', refreshData);

        // 30초마다 자동 새로고침
        setInterval(refreshData, 30000);
    </script>
</body>
</html>
