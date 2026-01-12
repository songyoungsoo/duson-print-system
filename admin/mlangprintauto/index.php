<?php
/**
 * 관리자 대시보드
 *
 * 통계 및 빠른 접근 링크 제공
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/includes/ProductConfig.php';

// 관리자 인증 필수
requireAdminAuth();

// 전체 통계 조회
$stats_query = "
    SELECT
        COUNT(*) as total_orders,
        SUM(CAST(money_2 AS UNSIGNED)) as total_revenue,
        COUNT(DISTINCT name) as total_customers
    FROM mlangorder_printauto
";
$stats_result = mysqli_query($db, $stats_query);
$overall_stats = mysqli_fetch_assoc($stats_result);

// 제품별 주문 통계
$product_stats_query = "
    SELECT
        Type as product_type,
        COUNT(*) as order_count,
        SUM(CAST(money_2 AS UNSIGNED)) as revenue
    FROM mlangorder_printauto
    WHERE Type IS NOT NULL AND Type != ''
    GROUP BY Type
    ORDER BY order_count DESC
";
$product_stats_result = mysqli_query($db, $product_stats_query);

// 주문 상태별 통계
$status_stats_query = "
    SELECT
        OrderStyle,
        COUNT(*) as count
    FROM mlangorder_printauto
    GROUP BY OrderStyle
    ORDER BY OrderStyle
";
$status_stats_result = mysqli_query($db, $status_stats_query);

// 견적 통계
$quote_stats_query = "
    SELECT
        COUNT(*) as total_quotes,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
        SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_count
    FROM quotes
";
$quote_stats_result = mysqli_query($db, $quote_stats_query);
$quote_stats = mysqli_fetch_assoc($quote_stats_result);

// 최근 7일간 일별 주문 통계
$daily_stats_query = "
    SELECT
        DATE(date) as order_date,
        COUNT(*) as order_count,
        SUM(CAST(money_2 AS UNSIGNED)) as revenue
    FROM mlangorder_printauto
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(date)
    ORDER BY order_date
";
$daily_stats_result = mysqli_query($db, $daily_stats_query);

// 최근 주문 10개
$recent_orders_query = "
    SELECT *
    FROM mlangorder_printauto
    ORDER BY no DESC
    LIMIT 10
";
$recent_orders_result = mysqli_query($db, $recent_orders_query);

$order_statuses = [
    '0' => '미선택',
    '1' => '견적접수',
    '2' => '주문접수',
    '3' => '접수완료',
    '4' => '입금대기',
    '5' => '시안제작중',
    '6' => '시안',
    '7' => '교정',
    '8' => '작업완료',
    '9' => '작업중',
    '10' => '교정작업중'
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 대시보드 - 두손기획인쇄</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .stat-card.blue::before { background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); }
        .stat-card.green::before { background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%); }
        .stat-card.purple::before { background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%); }
        .stat-card.orange::before { background: linear-gradient(90deg, #fa709a 0%, #fee140 100%); }

        .stat-label {
            color: #999;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chart-card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .quick-link {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.2s;
        }

        .quick-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .quick-link-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .quick-link-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .quick-link-desc {
            font-size: 12px;
            color: #999;
        }

        .recent-orders {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .recent-orders h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .recent-orders table {
            width: 100%;
            border-collapse: collapse;
        }

        .recent-orders th,
        .recent-orders td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        .recent-orders th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }

        .recent-orders tr:hover {
            background: #f9f9f9;
        }

        .order-no {
            font-weight: bold;
            color: #4CAF50;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }

        .status-0 { background: #eee; color: #666; }
        .status-1 { background: #fff3cd; color: #856404; }
        .status-2 { background: #d1ecf1; color: #0c5460; }
        .status-3 { background: #d4edda; color: #155724; }
        .status-4 { background: #f8d7da; color: #721c24; }
        .status-5, .status-6 { background: #cce5ff; color: #004085; }
        .status-7, .status-10 { background: #fff3cd; color: #856404; }
        .status-8 { background: #d4edda; color: #155724; }
        .status-9 { background: #cce5ff; color: #004085; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 관리자 대시보드</h1>
            <p>두손기획인쇄 통합 관리 시스템</p>
        </div>

        <!-- 주요 통계 -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-label">전체 주문</div>
                <div class="stat-value"><?= number_format($overall_stats['total_orders']) ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">총 매출</div>
                <div class="stat-value">₩<?= number_format($overall_stats['total_revenue']) ?></div>
            </div>
            <div class="stat-card purple">
                <div class="stat-label">고객 수</div>
                <div class="stat-value"><?= number_format($overall_stats['total_customers']) ?></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">전체 견적</div>
                <div class="stat-value"><?= number_format($quote_stats['total_quotes']) ?></div>
            </div>
        </div>

        <!-- 빠른 링크 -->
        <div class="quick-links">
            <a href="product_manager.php" class="quick-link">
                <div class="quick-link-icon">📦</div>
                <div class="quick-link-title">제품 관리</div>
                <div class="quick-link-desc">9개 제품 통합 관리</div>
            </a>
            <a href="order_manager.php" class="quick-link">
                <div class="quick-link-icon">📋</div>
                <div class="quick-link-title">주문 관리</div>
                <div class="quick-link-desc">전체 주문 조회 및 관리</div>
            </a>
            <a href="quote_manager.php" class="quick-link">
                <div class="quick-link-icon">💰</div>
                <div class="quick-link-title">견적 관리</div>
                <div class="quick-link-desc">고객 견적 통합 관리</div>
            </a>
            <a href="../../../mlangprintauto/" class="quick-link">
                <div class="quick-link-icon">🏠</div>
                <div class="quick-link-title">사이트 홈</div>
                <div class="quick-link-desc">메인 사이트로 이동</div>
            </a>
        </div>

        <!-- 차트 -->
        <div class="charts-grid">
            <div class="chart-card">
                <h2>📊 제품별 주문 통계</h2>
                <div class="chart-container">
                    <canvas id="productChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h2>📈 최근 7일 주문 추이</h2>
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h2>🎯 주문 상태별 분포</h2>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 최근 주문 -->
        <div class="recent-orders">
            <h2>📋 최근 주문 10개</h2>
            <table>
                <thead>
                    <tr>
                        <th>주문번호</th>
                        <th>주문일</th>
                        <th>제품</th>
                        <th>고객명</th>
                        <th>금액</th>
                        <th>상태</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                        <?php
                        // Type 매핑 (레거시 이름 → 새 product_type)
                        $type_mapping = [
                            '전단지' => 'inserted',
                            '명함' => 'namecard',
                            '봉투' => 'envelope',
                            '스티카' => 'sticker',
                            '자석스티카' => 'msticker',
                            '카다록' => 'cadarok',
                            '소량인쇄' => 'littleprint',
                            '상품권' => 'merchandisebond',
                            'NCR양식지' => 'ncrflambeau'
                        ];
                        $product_key = $type_mapping[$order['Type']] ?? null;
                        $product_name = $product_key ? (ProductConfig::getConfig($product_key)['name'] ?? $order['Type']) : $order['Type'];
                        $status_label = $order_statuses[$order['OrderStyle']] ?? $order['OrderStyle'];
                        $price = is_numeric($order['money_2']) ? $order['money_2'] : 0;
                        ?>
                        <tr>
                            <td class="order-no">#<?= $order['no'] ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($order['date'])) ?></td>
                            <td><?= htmlspecialchars($product_name) ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td><?= number_format($price) ?>원</td>
                            <td><span class="status-badge status-<?= $order['OrderStyle'] ?>"><?= $status_label ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // 제품별 주문 통계 차트
        <?php
        mysqli_data_seek($product_stats_result, 0);
        $product_labels = [];
        $product_data = [];
        $type_mapping = [
            '전단지' => 'inserted',
            '명함' => 'namecard',
            '봉투' => 'envelope',
            '스티카' => 'sticker',
            '자석스티카' => 'msticker',
            '카다록' => 'cadarok',
            '소량인쇄' => 'littleprint',
            '상품권' => 'merchandisebond',
            'NCR양식지' => 'ncrflambeau'
        ];
        while ($row = mysqli_fetch_assoc($product_stats_result)) {
            $product_key = $type_mapping[$row['product_type']] ?? null;
            $product_name = $product_key ? (ProductConfig::getConfig($product_key)['name'] ?? $row['product_type']) : $row['product_type'];
            $product_labels[] = $product_name;
            $product_data[] = $row['order_count'];
        }
        ?>
        new Chart(document.getElementById('productChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($product_labels) ?>,
                datasets: [{
                    data: <?= json_encode($product_data) ?>,
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(118, 75, 162, 0.8)',
                        'rgba(67, 233, 123, 0.8)',
                        'rgba(56, 249, 215, 0.8)',
                        'rgba(240, 147, 251, 0.8)',
                        'rgba(245, 87, 108, 0.8)',
                        'rgba(79, 172, 254, 0.8)',
                        'rgba(0, 242, 254, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // 일별 주문 추이 차트
        <?php
        mysqli_data_seek($daily_stats_result, 0);
        $daily_labels = [];
        $daily_data = [];
        while ($row = mysqli_fetch_assoc($daily_stats_result)) {
            $daily_labels[] = $row['order_date'];
            $daily_data[] = $row['order_count'];
        }
        ?>
        new Chart(document.getElementById('dailyChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($daily_labels) ?>,
                datasets: [{
                    label: '주문 수',
                    data: <?= json_encode($daily_data) ?>,
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // 주문 상태별 통계 차트
        <?php
        mysqli_data_seek($status_stats_result, 0);
        $status_labels = [];
        $status_data = [];
        while ($row = mysqli_fetch_assoc($status_stats_result)) {
            $status_labels[] = $order_statuses[$row['OrderStyle']] ?? $row['OrderStyle'];
            $status_data[] = $row['count'];
        }
        ?>
        new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($status_labels) ?>,
                datasets: [{
                    label: '주문 수',
                    data: <?= json_encode($status_data) ?>,
                    backgroundColor: 'rgba(67, 233, 123, 0.8)',
                    borderColor: 'rgba(67, 233, 123, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
