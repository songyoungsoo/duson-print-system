<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>주문 페이지 직접 테스트</title>
</head>
<body>
    <h1>주문 페이지 직접 접속 테스트</h1>

    <h2>1. 현재 세션 확인</h2>
    <?php
    session_start();
    $current_session = session_id();
    echo "<p>현재 세션 ID: <code>$current_session</code></p>";

    include "db.php";

    // 현재 세션의 장바구니 확인
    $query = "SELECT COUNT(*) as count FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $current_session);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    echo "<p>현재 세션 장바구니 아이템: <strong>{$row['count']}개</strong></p>";

    // 최근 장바구니 세션 확인
    $recent_query = "SELECT session_id, COUNT(*) as count, MAX(created_at) as last FROM shop_temp GROUP BY session_id ORDER BY last DESC LIMIT 3";
    $recent_result = mysqli_query($db, $recent_query);

    echo "<h2>2. 최근 장바구니 세션 목록</h2><ul>";
    $found_session = null;
    while ($sess = mysqli_fetch_assoc($recent_result)) {
        $highlight = ($sess['session_id'] === $current_session) ? " ✅ <strong>(현재 세션)</strong>" : "";
        echo "<li>세션: <code>{$sess['session_id']}</code> - {$sess['count']}개{$highlight}</li>";
        if ($sess['count'] > 0 && !$found_session) {
            $found_session = $sess['session_id'];
        }
    }
    echo "</ul>";
    ?>

    <h2>3. 주문 페이지 테스트 버튼</h2>

    <?php if ($row['count'] > 0): ?>
        <p style="color: green;">✅ 현재 세션에 장바구니 데이터 있음 - 바로 접속 가능</p>
        <form method="post" action="/mlangorder_printauto/OnlineOrder_unified.php" target="_blank">
            <input type="hidden" name="SubmitMode" value="OrderOne">
            <input type="hidden" name="cart_session_id" value="<?php echo $current_session; ?>">
            <button type="submit" style="padding: 15px 30px; font-size: 16px; background: #1976d2; color: white; border: none; border-radius: 4px; cursor: pointer;">
                🔗 주문 페이지 열기 (현재 세션)
            </button>
        </form>
    <?php elseif ($found_session): ?>
        <p style="color: orange;">⚠️ 현재 세션은 비어있지만, 다른 세션에 데이터 있음</p>
        <form method="post" action="/mlangorder_printauto/OnlineOrder_unified.php" target="_blank">
            <input type="hidden" name="SubmitMode" value="OrderOne">
            <input type="hidden" name="cart_session_id" value="<?php echo $found_session; ?>">
            <button type="submit" style="padding: 15px 30px; font-size: 16px; background: #ff9800; color: white; border: none; border-radius: 4px; cursor: pointer;">
                🔗 주문 페이지 열기 (세션: <?php echo substr($found_session, 0, 10); ?>...)
            </button>
        </form>
    <?php else: ?>
        <p style="color: red;">❌ 장바구니 데이터가 없습니다</p>
        <p><a href="/mlangprintauto/namecard/index.php">먼저 상품을 장바구니에 추가하세요</a></p>
    <?php endif; ?>

    <hr>
    <h2>4. 디버그 정보</h2>
    <p><a href="/mlangprintauto/shop/cart.php" target="_blank">장바구니 보기</a></p>
    <p><a href="/diagnose_order_issue.php" target="_blank">종합 진단 페이지</a></p>

</body>
</html>
<?php mysqli_close($db); ?>
