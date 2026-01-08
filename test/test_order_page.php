<?php
/**
 * 주문 페이지 테스트 스크립트
 * 경로: /test_order_page.php
 */
session_start();
include "db.php";

$session_id = session_id();
echo "<h1>주문 페이지 테스트</h1>";
echo "<p>현재 세션 ID: <strong>$session_id</strong></p>";

// 1. shop_temp 데이터 확인
$query = "SELECT * FROM shop_temp WHERE session_id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

echo "<h2>1. 장바구니 데이터 확인</h2>";
if (empty($items)) {
    echo "<p style='color: red;'>❌ 장바구니가 비어있습니다!</p>";
    echo "<p><a href='/mlangprintauto/namecard/index.php'>명함 주문하기</a></p>";
} else {
    echo "<p style='color: green;'>✅ 장바구니에 {count($items)}개 아이템 있음</p>";
    echo "<ul>";
    foreach ($items as $item) {
        echo "<li>{$item['product_type']} - " . number_format($item['st_price_vat']) . "원</li>";
    }
    echo "</ul>";
}

// 2. 주문 페이지 접속 테스트
echo "<h2>2. 주문 페이지 접속</h2>";
if (empty($items)) {
    echo "<p style='color: orange;'>⚠️ 먼저 장바구니에 상품을 추가해주세요</p>";
} else {
    echo "<p>✅ 주문 페이지 접속 준비 완료</p>";
    echo "<form method='post' action='/mlangorder_printauto/OnlineOrder_unified.php'>";
    echo "<input type='hidden' name='SubmitMode' value='OrderOne'>";
    echo "<input type='hidden' name='cart_session_id' value='$session_id'>";
    echo "<button type='submit' style='padding: 15px 30px; font-size: 16px; background: #1976d2; color: white; border: none; border-radius: 4px; cursor: pointer;'>주문 페이지로 이동</button>";
    echo "</form>";
}

// 3. 세션 정보
echo "<h2>3. 세션 정보</h2>";
echo "<pre>";
echo "세션 ID: " . $session_id . "\n";
echo "세션 저장 경로: " . session_save_path() . "\n";
echo "쿠키 경로: " . session_get_cookie_params()['path'] . "\n";
echo "</pre>";

// 4. 장바구니 페이지 링크
echo "<h2>4. 빠른 링크</h2>";
echo "<p><a href='/mlangprintauto/shop/cart.php'>장바구니 보기</a></p>";
echo "<p><a href='/mlangorder_printauto/OnlineOrder_unified.php'>주문 페이지 직접 접속 (세션 자동)</a></p>";

mysqli_close($db);
?>
