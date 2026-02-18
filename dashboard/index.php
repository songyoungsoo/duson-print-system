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
$inquiry_count = 0;
try {
    $inquiry_result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM customer_inquiries WHERE replied_at IS NULL OR replied_at = ''");
    if ($inquiry_result) {
        $inquiry_data = mysqli_fetch_assoc($inquiry_result);
        $inquiry_count = intval($inquiry_data['cnt'] ?? 0);
    }
} catch (Throwable $e) {
    $inquiry_count = 0;
}

// Query: Proof pending count
$proof_pending_count = 0;
try {
    $proof_result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE OrderStyle IN ('7', '10')");
    if ($proof_result) {
        $proof_data = mysqli_fetch_assoc($proof_result);
        $proof_pending_count = intval($proof_data['cnt']);
    }
} catch (Throwable $e) {
    $proof_pending_count = 0;
}

// Query: Unread chat count
$chat_unread_count = 0;
try {
    $chat_result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM chatmessages WHERE isread = 0 AND senderid NOT LIKE 'staff%' AND senderid != 'system'");
    if ($chat_result) {
        $chat_data = mysqli_fetch_assoc($chat_result);
        $chat_unread_count = intval($chat_data['cnt']);
    }
} catch (Throwable $e) {
    $chat_unread_count = 0;
}

// Query: Quote stats
$quote_pending_count = 0;
try {
    $quote_result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM quotes WHERE status IN ('draft', 'sent')");
    if ($quote_result) {
        $quote_data = mysqli_fetch_assoc($quote_result);
        $quote_pending_count = intval($quote_data['cnt']);
    }
} catch (Throwable $e) {
    $quote_pending_count = 0;
}

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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900">대시보드</h1>
            <p class="mt-1 text-sm text-gray-600">두손기획 관리자 대시보드 - 실시간 현황</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <!-- Today's Orders -->
            <div class="bg-white rounded-lg shadow p-3 hover:shadow-lg transition-shadow">
                <div class="text-xs font-medium text-gray-600 mb-1">📦 오늘 주문</div>
                <div class="text-2xl font-bold text-gray-900" id="today-order-count"><?php echo number_format($today_count); ?>건</div>
                <div class="text-xs text-gray-500" id="today-revenue"><?php echo number_format($today_revenue); ?>원</div>
            </div>

            <!-- This Month's Orders -->
            <div class="bg-white rounded-lg shadow p-3 hover:shadow-lg transition-shadow">
                <div class="text-xs font-medium text-gray-600 mb-1">📊 이번달 주문</div>
                <div class="text-2xl font-bold text-gray-900" id="month-order-count"><?php echo number_format($month_count); ?>건</div>
                <div class="text-xs text-gray-500" id="month-revenue"><?php echo number_format($month_revenue); ?>원</div>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow p-3 hover:shadow-lg transition-shadow">
                <div class="text-xs font-medium text-gray-600 mb-1">⏳ 미처리 주문</div>
                <div class="text-2xl font-bold text-orange-600" id="pending-order-count"><?php echo number_format($pending_count); ?>건</div>
                <div class="text-xs text-gray-500">처리 필요</div>
            </div>

            <!-- Unanswered Inquiries -->
            <div class="bg-white rounded-lg shadow p-3 hover:shadow-lg transition-shadow">
                <div class="text-xs font-medium text-gray-600 mb-1">💬 미답변 문의</div>
                <div class="text-2xl font-bold text-red-600" id="inquiry-count"><?php echo number_format($inquiry_count); ?>건</div>
                <div class="text-xs text-gray-500">답변 필요</div>
            </div>
        </div>

        <!-- Daily Trend Chart -->
        <div class="bg-white rounded-lg shadow p-3 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">📈 일별 주문 추이 (최근 30일)</h3>
            <div class="relative" style="height: 180px;">
                <canvas id="dailyChart"></canvas>
            </div>
        </div>

        <!-- Quick Action Tools -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <a href="/dashboard/orders/" class="block bg-white rounded-lg shadow p-3 hover:shadow-lg hover:border-orange-300 border border-transparent transition-all group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl">📋</span>
                    <?php if ($pending_count > 0): ?>
                        <span class="bg-orange-100 text-orange-700 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </div>
                <div class="text-sm font-semibold text-gray-900 group-hover:text-orange-600">주문 확인</div>
                <div class="text-xs text-gray-500">미처리 주문 확인·처리</div>
            </a>

            <a href="/dashboard/proofs/" class="block bg-white rounded-lg shadow p-3 hover:shadow-lg hover:border-blue-300 border border-transparent transition-all group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl">🔍</span>
                    <?php if ($proof_pending_count > 0): ?>
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $proof_pending_count; ?></span>
                    <?php endif; ?>
                </div>
                <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-600">교정 관리</div>
                <div class="text-xs text-gray-500">교정보기·파일올리기</div>
            </a>

            <a href="/dashboard/chat/" class="block bg-white rounded-lg shadow p-3 hover:shadow-lg hover:border-purple-300 border border-transparent transition-all group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl">💬</span>
                    <?php if ($chat_unread_count > 0): ?>
                        <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $chat_unread_count; ?></span>
                    <?php endif; ?>
                </div>
                <div class="text-sm font-semibold text-gray-900 group-hover:text-purple-600">채팅 관리</div>
                <div class="text-xs text-gray-500">고객 채팅 응대</div>
            </a>

            <a href="/dashboard/quotes/" class="block bg-white rounded-lg shadow p-3 hover:shadow-lg hover:border-green-300 border border-transparent transition-all group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl">📋</span>
                    <?php if ($quote_pending_count > 0): ?>
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $quote_pending_count; ?></span>
                    <?php endif; ?>
                </div>
                <div class="text-sm font-semibold text-gray-900 group-hover:text-green-600">견적 관리</div>
                <div class="text-xs text-gray-500">견적서 작성·발송</div>
            </a>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow p-3 mb-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-900">최근 주문</h3>
                <a href="/dashboard/orders/" class="text-xs text-blue-600 hover:text-blue-800">전체 보기 →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">주문번호</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">품목</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">주문자</th>
                            <th class="px-2 py-1.5 text-right text-xs font-medium text-gray-500">금액</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">일시</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_orders as $order): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-1.5 text-xs text-gray-900 font-medium">#<?php echo $order['no']; ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-600"><?php echo htmlspecialchars($order['Type']); ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-600">
                                <?php echo htmlspecialchars($order['name'] ?: explode('@', $order['email'])[0]); ?>
                            </td>
                            <td class="px-2 py-1.5 text-xs text-gray-900 text-right font-medium">
                                <?php echo number_format((float)($order['money_5'] ?: 0)); ?>원
                            </td>
                            <td class="px-2 py-1.5 text-xs text-gray-400 text-center">
                                <?php echo date('m/d H:i', strtotime($order['date'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

<script>
// Function to animate numbers
function animateNumber(id, finalValue, duration = 1000, suffix = '') {
    const obj = document.getElementById(id);
    if (!obj) return;

    let startTimestamp = null;
    const startValue = 0;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const currentValue = Math.floor(progress * (finalValue - startValue) + startValue);
        obj.innerHTML = currentValue.toLocaleString() + suffix;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            obj.innerHTML = finalValue.toLocaleString() + suffix; // Ensure final value is set
        }
    };
    window.requestAnimationFrame(step);
}

document.addEventListener('DOMContentLoaded', () => {
    // Animate Today's Orders
    animateNumber('today-order-count', <?php echo $today_count; ?>, 1000, '건');
    animateNumber('today-revenue', <?php echo $today_revenue; ?>, 1000, '원');

    // Animate This Month's Orders
    animateNumber('month-order-count', <?php echo $month_count; ?>, 1000, '건');
    animateNumber('month-revenue', <?php echo $month_revenue; ?>, 1000, '원');

    // Animate Pending Orders
    animateNumber('pending-order-count', <?php echo $pending_count; ?>, 1000, '건');

    // Animate Unanswered Inquiries
    animateNumber('inquiry-count', <?php echo $inquiry_count; ?>, 1000, '건');
});
</script>
