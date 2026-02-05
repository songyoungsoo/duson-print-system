<?php
/**
 * Main Dashboard - 두손기획 관리자 대시보드
 * Summary cards, daily trend chart, recent orders
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../db.php';

// Get today's date
$today = date('Y-m-d');
$thisMonth = date('Y-m');

// Query: Today's orders
$today_result = mysqli_query($db, "SELECT COUNT(*) as cnt, SUM(money_5) as total FROM mlangorder_printauto WHERE DATE(date) = '$today'");
$today_data = mysqli_fetch_assoc($today_result);
$today_count = intval($today_data['cnt']);
$today_revenue = intval($today_data['total'] ?? 0);

// Query: This month's orders
$month_result = mysqli_query($db, "SELECT COUNT(*) as cnt, SUM(money_5) as total FROM mlangorder_printauto WHERE DATE_FORMAT(date, '%Y-%m') = '$thisMonth'");
$month_data = mysqli_fetch_assoc($month_result);
$month_count = intval($month_data['cnt']);
$month_revenue = intval($month_data['total'] ?? 0);

// Query: Pending orders (미처리 주문)
$pending_result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE OrderStyle IN ('1', '2', '3') OR OrderStyle IS NULL OR OrderStyle = ''");
$pending_data = mysqli_fetch_assoc($pending_result);
$pending_count = intval($pending_data['cnt']);

// Query: Unanswered inquiries (미답변 문의)
$inquiry_result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM customer_inquiries WHERE replied_at IS NULL OR replied_at = ''");
$inquiry_data = mysqli_fetch_assoc($inquiry_result);
$inquiry_count = intval($inquiry_data['cnt'] ?? 0);

// Query: Daily trend (last 30 days)
$daily_result = mysqli_query($db, "
    SELECT
        DATE(date) as day,
        COUNT(*) as orders
    FROM mlangorder_printauto
    WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(date)
    ORDER BY day ASC
");

$daily_dates = [];
$daily_counts = [];
while ($row = mysqli_fetch_assoc($daily_result)) {
    $daily_dates[] = $row['day'];
    $daily_counts[] = intval($row['orders']);
}

// Query: Recent orders (최근 5건)
$recent_result = mysqli_query($db, "
    SELECT no, Type, name, email, money_5, date
    FROM mlangorder_printauto
    ORDER BY no DESC
    LIMIT 5
");

$recent_orders = [];
while ($row = mysqli_fetch_assoc($recent_result)) {
    $recent_orders[] = $row;
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">대시보드</h1>
            <p class="mt-2 text-sm text-gray-600">두손기획 관리자 대시보드 - 실시간 현황</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Today's Orders -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-medium text-gray-600">📦 오늘 주문</div>
                </div>
                <div class="text-3xl font-bold text-gray-900"><?php echo number_format($today_count); ?>건</div>
                <div class="mt-2 text-sm text-gray-500"><?php echo number_format($today_revenue); ?>원</div>
            </div>

            <!-- This Month's Orders -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-medium text-gray-600">📊 이번달 주문</div>
                </div>
                <div class="text-3xl font-bold text-gray-900"><?php echo number_format($month_count); ?>건</div>
                <div class="mt-2 text-sm text-gray-500"><?php echo number_format($month_revenue); ?>원</div>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-medium text-gray-600">⏳ 미처리 주문</div>
                </div>
                <div class="text-3xl font-bold text-orange-600"><?php echo number_format($pending_count); ?>건</div>
                <div class="mt-2 text-sm text-gray-500">처리 필요</div>
            </div>

            <!-- Unanswered Inquiries -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-medium text-gray-600">💬 미답변 문의</div>
                </div>
                <div class="text-3xl font-bold text-red-600"><?php echo number_format($inquiry_count); ?>건</div>
                <div class="mt-2 text-sm text-gray-500">답변 필요</div>
            </div>
        </div>

        <!-- Daily Trend Chart -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 일별 주문 추이 (최근 30일)</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="dailyChart"></canvas>
            </div>
        </div>

        <!-- Recent Orders & Quick Links -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">최근 주문</h3>
                    <a href="/dashboard/orders/" class="text-sm text-blue-600 hover:text-blue-800">전체 보기 →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">주문번호</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">품목</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">주문자</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">금액</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_orders as $order): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">#<?php echo $order['no']; ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($order['Type']); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($order['name'] ?: explode('@', $order['email'])[0]); ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                    <?php echo number_format($order['money_5']); ?>원
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">빠른 이동</h3>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach ($DASHBOARD_MODULES as $key => $module): ?>
                        <?php if ($key !== 'home'): ?>
                        <a href="<?php echo $module['path']; ?>" 
                           class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                            <span class="text-2xl mr-3"><?php echo $module['icon']; ?></span>
                            <span class="text-sm font-medium text-gray-700"><?php echo $module['name']; ?></span>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Daily Trend Chart
const ctx = document.getElementById('dailyChart').getContext('2d');
const dailyChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($daily_dates); ?>,
        datasets: [{
            label: '주문 건수',
            data: <?php echo json_encode($daily_counts); ?>,
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
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
