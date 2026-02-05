<?php
require_once __DIR__ . '/base.php';

$type = $_GET['type'] ?? 'daily';

switch ($type) {
    case 'daily':
        $days = intval($_GET['days'] ?? 30);
        $result = mysqli_query($db, "
            SELECT
                DATE(date) as day,
                COUNT(*) as orders,
                SUM(money_5) as revenue
            FROM mlangorder_printauto
            WHERE date >= DATE_SUB(NOW(), INTERVAL $days DAY)
            GROUP BY DATE(date)
            ORDER BY day ASC
        ");
        
        $dates = [];
        $counts = [];
        $revenues = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $dates[] = $row['day'];
            $counts[] = intval($row['orders']);
            $revenues[] = intval($row['revenue'] ?? 0);
        }
        
        jsonResponse(true, 'Daily stats', [
            'dates' => $dates,
            'counts' => $counts,
            'revenues' => $revenues
        ]);
        break;
        
    case 'monthly':
        $result = mysqli_query($db, "
            SELECT
                DATE_FORMAT(date, '%Y-%m') as month,
                COUNT(*) as orders,
                SUM(money_5) as revenue
            FROM mlangorder_printauto
            WHERE date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(date, '%Y-%m')
            ORDER BY month ASC
        ");
        
        $months = [];
        $counts = [];
        $revenues = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $months[] = $row['month'];
            $counts[] = intval($row['orders']);
            $revenues[] = intval($row['revenue'] ?? 0);
        }
        
        jsonResponse(true, 'Monthly stats', [
            'months' => $months,
            'counts' => $counts,
            'revenues' => $revenues
        ]);
        break;
        
    case 'products':
        $result = mysqli_query($db, "
            SELECT
                CASE
                    WHEN Type LIKE '%스티커%' OR Type LIKE '%스티카%' THEN '스티커'
                    WHEN Type IN ('명함', 'NameCard', 'namecard') THEN '명함'
                    WHEN Type IN ('전단지', 'inserted', '리플렛', 'leaflet') THEN '전단지/리플렛'
                    WHEN Type LIKE '%봉투%' THEN '봉투'
                    WHEN Type LIKE '%포스터%' OR Type = 'littleprint' THEN '포스터'
                    WHEN Type LIKE '%상품권%' OR Type = 'merchandisebond' THEN '상품권'
                    WHEN Type LIKE '%양식%' OR Type = 'ncrflambeau' THEN '양식지'
                    WHEN Type LIKE '%카다%' OR Type = 'cadarok' THEN '카다록'
                    ELSE '기타'
                END as category,
                COUNT(*) as orders,
                SUM(money_5) as revenue
            FROM mlangorder_printauto
            GROUP BY category
            ORDER BY orders DESC
            LIMIT 10
        ");
        
        $categories = [];
        $counts = [];
        $revenues = [];
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['category'] != '' && $row['orders'] > 0) {
                $categories[] = $row['category'];
                $counts[] = intval($row['orders']);
                $revenues[] = intval($row['revenue'] ?? 0);
            }
        }
        
        jsonResponse(true, 'Product stats', [
            'categories' => $categories,
            'counts' => $counts,
            'revenues' => $revenues
        ]);
        break;
        
    default:
        jsonResponse(false, 'Invalid type');
}
