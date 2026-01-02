<?php
/**
 * 실제 주문 데이터 조회 스크립트 (가격 정보 포함)
 */

require_once __DIR__ . '/../db.php';

// money_1이 있는 최근 주문 조회
$query = "
    SELECT
        no,
        Type,
        name,
        email,
        phone,
        money_1,
        money_2,
        money_3,
        ThingCate,
        mesu,
        product_type,
        coating_enabled,
        coating_type,
        coating_price,
        folding_enabled,
        folding_type,
        folding_price,
        creasing_enabled,
        creasing_lines,
        creasing_price,
        additional_options_total,
        premium_options,
        premium_options_total,
        date,
        zip1,
        zip2
    FROM mlangorder_printauto
    WHERE name NOT LIKE '%테스트%'
        AND name NOT LIKE '%test%'
        AND email IS NOT NULL
        AND email != ''
        AND Type IS NOT NULL
        AND money_1 IS NOT NULL
        AND money_1 != ''
    ORDER BY no DESC
    LIMIT 3
";

$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $orders = [];
    while ($order = mysqli_fetch_assoc($result)) {
        $orders[] = $order;
    }

    // JSON으로 출력
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'error' => 'No orders found',
        'query_error' => mysqli_error($db)
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($db);
?>
