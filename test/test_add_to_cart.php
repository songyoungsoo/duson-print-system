<?php
/**
 * 장바구니 추가 테스트 (명함)
 */

session_start();
$test_session_id = 'test_order_' . date('YmdHis');

include "db.php";
mysqli_set_charset($db, "utf8");

echo "<h1>장바구니 추가 테스트</h1>";

// 테스트 데이터 준비
$product_type = 'namecard';
$MY_type = '1'; // 일반명함
$Section = '1'; // 기본 재질
$POtype = '1'; // 단면
$MY_amount = '500'; // 500매
$ordertype = 'print'; // 인쇄만

// 가격 계산
$price = 50000;
$vat_price = 55000;

// shop_temp에 추가
$regdate = time();
$query = "INSERT INTO shop_temp
          (session_id, product_type, st_price, st_price_vat, MY_type, Section, POtype, MY_amount, ordertype, regdate)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ssiisssssi',
        $test_session_id,
        $product_type,
        $price,
        $vat_price,
        $MY_type,
        $Section,
        $POtype,
        $MY_amount,
        $ordertype,
        $regdate
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✅ 장바구니에 테스트 상품 추가 완료!</p>";
        echo "<p><strong>세션 ID:</strong> " . htmlspecialchars($test_session_id) . "</p>";
        echo "<p><strong>제품:</strong> 명함 (일반명함, 단면, 500매, 인쇄만)</p>";
        echo "<p><strong>가격:</strong> " . number_format($price) . "원 (VAT 포함: " . number_format($vat_price) . "원)</p>";

        echo "<hr>";
        echo "<h2>다음 단계</h2>";
        echo "<p><a href='/mlangorder_printauto/OnlineOrder_unified.php?session_id=" . urlencode($test_session_id) . "' target='_blank' style='font-size: 18px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; display: inline-block; border-radius: 5px;'>주문서 작성하기 →</a></p>";

    } else {
        echo "<p style='color: red;'>❌ 장바구니 추가 실패: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color: red;'>❌ 쿼리 준비 실패: " . htmlspecialchars(mysqli_error($db)) . "</p>";
}

mysqli_close($db);
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}
h1 {
    color: #333;
    border-bottom: 3px solid #4CAF50;
    padding-bottom: 10px;
}
</style>
