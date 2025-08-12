<?php
session_start();
include "../lib/func.php";
$connect = dbconn();

echo "<h2>🛒 현재 장바구니 상태 확인</h2>";

$session_id = session_id();
echo "<h3>세션 ID: $session_id</h3>";

// 장바구니 아이템 조회
$query = "SELECT * FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC";
$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<h3>✅ 장바구니에 " . mysqli_num_rows($result) . "개 아이템이 있습니다:</h3>";
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>NO</th><th>재질</th><th>가로</th><th>세로</th><th>수량</th><th>도무송</th><th>가격</th><th>VAT포함</th><th>등록시간</th>";
    echo "</tr>";
    
    $total = 0;
    $total_vat = 0;
    
    while ($data = mysqli_fetch_array($result)) {
        echo "<tr>";
        echo "<td>{$data['no']}</td>";
        echo "<td>" . substr($data['jong'], 4, 12) . "</td>";
        echo "<td>{$data['garo']}</td>";
        echo "<td>{$data['sero']}</td>";
        echo "<td>{$data['mesu']}</td>";
        $domusong_parts = explode(' ', $data['domusong'], 2);
        $domusong_name = isset($domusong_parts[1]) ? $domusong_parts[1] : $data['domusong'];
        echo "<td>" . htmlspecialchars($domusong_name) . "</td>";
        echo "<td>" . number_format($data['st_price']) . "</td>";
        echo "<td>" . number_format($data['st_price_vat']) . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', $data['regdate']) . "</td>";
        echo "</tr>";
        
        $total += $data['st_price'];
        $total_vat += $data['st_price_vat'];
    }
    
    echo "<tr style='background: #e8f5e8; font-weight: bold;'>";
    echo "<td colspan='6'>합계</td>";
    echo "<td>" . number_format($total) . "</td>";
    echo "<td>" . number_format($total_vat) . "</td>";
    echo "<td></td>";
    echo "</tr>";
    echo "</table>";
    
} else {
    echo "<h3>📭 장바구니가 비어있습니다.</h3>";
}

// 장바구니 관리 버튼들
echo "<div style='margin-top: 20px;'>";
echo "<a href='view.php' style='background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>상품 추가하기</a>";
echo "<a href='basket.php' style='background: #FF6B35; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>주문하기</a>";
echo "<a href='#' onclick='clearBasket()' style='background: #f44336; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>장바구니 비우기</a>";
echo "</div>";

echo "<script>";
echo "function clearBasket() {";
echo "  if (confirm('장바구니를 모두 비우시겠습니까?')) {";
echo "    fetch('clear_basket.php', { method: 'POST' })";
echo "    .then(() => location.reload());";
echo "  }";
echo "}";
echo "</script>";

if ($connect) {
    mysqli_close($connect);
}
?>