<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/includes/ProductConfig.php';

echo "=== 대시보드 통계 데이터 테스트 ===\n\n";

// 1. 전체 통계
$stats_query = "
    SELECT
        COUNT(*) as total_orders,
        SUM(CAST(money_2 AS UNSIGNED)) as total_revenue,
        COUNT(DISTINCT name) as total_customers
    FROM mlangorder_printauto
";
$stats_result = mysqli_query($db, $stats_query);
$overall_stats = mysqli_fetch_assoc($stats_result);

echo "📊 전체 통계:\n";
echo "- 전체 주문: " . number_format($overall_stats['total_orders']) . "\n";
echo "- 총 매출: ₩" . number_format($overall_stats['total_revenue']) . "\n";
echo "- 고객 수: " . number_format($overall_stats['total_customers']) . "\n\n";

// 2. 제품별 통계
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

echo "📦 제품별 주문 통계:\n";
while ($row = mysqli_fetch_assoc($product_stats_result)) {
    echo "- {$row['product_type']}: " . number_format($row['order_count']) . "건 (₩" . number_format($row['revenue']) . ")\n";
}

echo "\n";

// 3. 주문 상태별 통계
$status_stats_query = "
    SELECT
        OrderStyle,
        COUNT(*) as count
    FROM mlangorder_printauto
    GROUP BY OrderStyle
    ORDER BY OrderStyle
";
$status_stats_result = mysqli_query($db, $status_stats_query);

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

echo "🎯 주문 상태별 통계:\n";
while ($row = mysqli_fetch_assoc($status_stats_result)) {
    $status_label = $order_statuses[$row['OrderStyle']] ?? $row['OrderStyle'];
    echo "- {$status_label}: " . number_format($row['count']) . "건\n";
}

echo "\n";

// 4. 최근 7일 일별 통계
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

echo "📈 최근 7일 주문 추이:\n";
while ($row = mysqli_fetch_assoc($daily_stats_result)) {
    echo "- {$row['order_date']}: " . number_format($row['order_count']) . "건 (₩" . number_format($row['revenue']) . ")\n";
}

echo "\n=== 테스트 완료 ===\n";
?>
