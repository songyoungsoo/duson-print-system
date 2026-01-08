<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>시스템 검증</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h1>🔍 두손기획 시스템 검증</h1>

    <?php
    include "db.php";

    // 1. 장바구니 최신 데이터
    echo "<h2>1. 장바구니 (shop_temp)</h2>";
    $cart_query = "SELECT no, product_type, spec_type, quantity_display, price_vat, data_version FROM shop_temp ORDER BY created_at DESC LIMIT 3";
    $cart_result = mysqli_query($db, $cart_query);

    echo "<table>";
    echo "<tr><th>No</th><th>제품</th><th>규격</th><th>수량</th><th>가격</th><th>버전</th><th>상태</th></tr>";
    while ($row = mysqli_fetch_assoc($cart_result)) {
        $status = (!empty($row['spec_type']) && !empty($row['quantity_display']) && $row['data_version'] == 2)
            ? "<span class='pass'>✅ PASS</span>"
            : "<span class='fail'>❌ FAIL</span>";
        echo "<tr>";
        echo "<td>{$row['no']}</td>";
        echo "<td>{$row['product_type']}</td>";
        echo "<td>" . ($row['spec_type'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['quantity_display'] ?? 'NULL') . "</td>";
        echo "<td>" . number_format($row['price_vat']) . "</td>";
        echo "<td>{$row['data_version']}</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 2. 주문 최신 데이터
    echo "<h2>2. 주문 (mlangorder_printauto)</h2>";
    $order_query = "SELECT no, Type as product, spec_type, quantity_display, money_5 as price_vat, data_version FROM mlangorder_printauto ORDER BY no DESC LIMIT 3";
    $order_result = mysqli_query($db, $order_query);

    echo "<table>";
    echo "<tr><th>No</th><th>제품</th><th>규격</th><th>수량</th><th>가격</th><th>버전</th><th>상태</th></tr>";
    while ($row = mysqli_fetch_assoc($order_result)) {
        $status = (!empty($row['spec_type']) && !empty($row['quantity_display']) && $row['data_version'] == 2)
            ? "<span class='pass'>✅ PASS</span>"
            : "<span class='fail'>❌ FAIL</span>";
        echo "<tr>";
        echo "<td>{$row['no']}</td>";
        echo "<td>{$row['product']}</td>";
        echo "<td>" . ($row['spec_type'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['quantity_display'] ?? 'NULL') . "</td>";
        echo "<td>" . number_format($row['price_vat']) . "</td>";
        echo "<td>" . ($row['data_version'] ?? '1') . "</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 3. 시스템 상태
    echo "<h2>3. 시스템 상태 요약</h2>";

    $cart_count = mysqli_num_rows(mysqli_query($db, "SELECT 1 FROM shop_temp WHERE data_version = 2 LIMIT 1"));
    $order_count = mysqli_num_rows(mysqli_query($db, "SELECT 1 FROM mlangorder_printauto WHERE data_version = 2 LIMIT 1"));

    echo "<p><strong>장바구니 Phase 3:</strong> " . ($cart_count > 0 ? "<span class='pass'>활성화됨</span>" : "<span class='fail'>비활성화됨</span>") . "</p>";
    echo "<p><strong>주문 Phase 3:</strong> " . ($order_count > 0 ? "<span class='pass'>활성화됨</span>" : "<span class='fail'>비활성화됨</span>") . "</p>";

    echo "<hr>";
    echo "<p><a href='/mlangprintauto/namecard/index.php'>명함 주문하기</a> | ";
    echo "<a href='/mlangprintauto/inserted/index.php'>전단지 주문하기</a> | ";
    echo "<a href='/mlangprintauto/shop/cart.php'>장바구니</a></p>";

    mysqli_close($db);
    ?>
</body>
</html>
