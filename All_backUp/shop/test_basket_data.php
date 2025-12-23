<?php
session_start();
$session_id = session_id();

require_once('../lib/func.php');
$connect = dbconn();

if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

echo "<h2>🔍 장바구니 테스트 - 실제 데이터 확인</h2>";
echo "<p><strong>현재 세션 ID:</strong> $session_id</p>";

// 1. 현재 세션의 모든 데이터 확인
$query = "SELECT * FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC";
$result = mysqli_query($connect, $query);

echo "<h3>📊 장바구니 데이터 총 개수: " . mysqli_num_rows($result) . "개</h3>";

if (mysqli_num_rows($result) > 0) {
    echo "<div style='overflow-x: auto;'>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='font-size: 12px; width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>NO</th><th>product_type</th><th>st_price</th><th>st_price_vat</th>";
    echo "<th style='background: #ffffcc;'>coating_enabled</th><th style='background: #ffffcc;'>coating_price</th>";
    echo "<th style='background: #ffe0cc;'>folding_enabled</th><th style='background: #ffe0cc;'>folding_price</th>";  
    echo "<th style='background: #e0ffcc;'>creasing_enabled</th><th style='background: #e0ffcc;'>creasing_price</th>";
    echo "<th style='background: #e0e0ff;'>additional_options_total</th>";
    echo "<th>MY_type</th><th>PN_type</th><th>MY_amount</th>";
    echo "</tr>";
    
    while ($data = mysqli_fetch_array($result)) {
        echo "<tr>";
        echo "<td>{$data['no']}</td>";
        echo "<td>" . ($data['product_type'] ?? 'NULL') . "</td>";
        echo "<td>" . number_format($data['st_price'] ?? 0) . "원</td>";
        echo "<td>" . number_format($data['st_price_vat'] ?? 0) . "원</td>";
        
        // 옵션 필드들 (배경색으로 구분)
        echo "<td style='background: #ffffcc;'>" . ($data['coating_enabled'] ?? 'NULL') . "</td>";
        echo "<td style='background: #ffffcc;'>" . ($data['coating_price'] ?? 'NULL') . "</td>";
        echo "<td style='background: #ffe0cc;'>" . ($data['folding_enabled'] ?? 'NULL') . "</td>";
        echo "<td style='background: #ffe0cc;'>" . ($data['folding_price'] ?? 'NULL') . "</td>";
        echo "<td style='background: #e0ffcc;'>" . ($data['creasing_enabled'] ?? 'NULL') . "</td>";
        echo "<td style='background: #e0ffcc;'>" . ($data['creasing_price'] ?? 'NULL') . "</td>";
        echo "<td style='background: #e0e0ff;'>" . ($data['additional_options_total'] ?? 'NULL') . "</td>";
        
        echo "<td>" . ($data['MY_type'] ?? 'NULL') . "</td>";
        echo "<td>" . ($data['PN_type'] ?? 'NULL') . "</td>";
        echo "<td>" . ($data['MY_amount'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
} else {
    echo "<div style='background: #ffeeee; border: 2px solid red; padding: 20px; border-radius: 5px;'>";
    echo "<h3>⚠️ 장바구니가 비어있습니다!</h3>";
    echo "<p>먼저 <a href='../mlangprintauto/inserted/index.php' target='_blank'>전단지 페이지</a>에서 옵션을 선택하고 장바구니에 담아보세요.</p>";
    echo "</div>";
}

// 2. 테이블 구조 확인
echo "<h3>🗂️ shop_temp 테이블 구조</h3>";
$structure_query = "SHOW COLUMNS FROM shop_temp";
$structure_result = mysqli_query($connect, $structure_query);

if ($structure_result) {
    echo "<table border='1' cellpadding='5' cellspacing='0' style='font-size: 11px; border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Default</th></tr>";
    while ($col = mysqli_fetch_array($structure_result)) {
        $highlight = '';
        if (strpos($col['Field'], 'coating') !== false || 
            strpos($col['Field'], 'folding') !== false || 
            strpos($col['Field'], 'creasing') !== false ||
            strpos($col['Field'], 'additional') !== false) {
            $highlight = "style='background: #ffffaa;'";
        }
        echo "<tr $highlight>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($connect);
?>

<br><br>
<div style="text-align: center;">
    <a href="basket.php" style="background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🛒 장바구니로 이동</a>
    <a href="quotation.php" target="_blank" style="background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">📄 견적서 보기</a>
    <a href="../mlangprintauto/inserted/index.php" target="_blank" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">📝 전단지 주문하기</a>
</div>