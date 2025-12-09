<?php
session_start();
$session_id = session_id();

require_once('../lib/func.php');
$connect = dbconn();

if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

echo "<h2>장바구니 디버그 - 옵션 가격 확인</h2>";
echo "<p>현재 세션 ID: $session_id</p>";

$query = "SELECT * FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC LIMIT 5";
$result = mysqli_query($connect, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($data = mysqli_fetch_array($result)) {
        echo "<hr>";
        echo "<h3>아이템 #{$data['no']}</h3>";
        echo "<table border='1' cellpadding='5'>";
        
        // 기본 정보
        echo "<tr><td><b>기본 정보</b></td><td></td></tr>";
        echo "<tr><td>product_type</td><td>{$data['product_type']}</td></tr>";
        echo "<tr><td>st_price (기본가격)</td><td>" . number_format($data['st_price']) . "원</td></tr>";
        echo "<tr><td>st_price_vat (VAT포함)</td><td>" . number_format($data['st_price_vat']) . "원</td></tr>";
        
        // 옵션 관련 필드들 확인
        echo "<tr><td><b>옵션 필드들</b></td><td></td></tr>";
        echo "<tr><td>coating_enabled</td><td>" . (isset($data['coating_enabled']) ? $data['coating_enabled'] : 'NULL') . "</td></tr>";
        echo "<tr><td>coating_type</td><td>" . (isset($data['coating_type']) ? $data['coating_type'] : 'NULL') . "</td></tr>";
        echo "<tr><td>coating_price</td><td>" . (isset($data['coating_price']) ? $data['coating_price'] : 'NULL') . "</td></tr>";
        
        echo "<tr><td>folding_enabled</td><td>" . (isset($data['folding_enabled']) ? $data['folding_enabled'] : 'NULL') . "</td></tr>";
        echo "<tr><td>folding_type</td><td>" . (isset($data['folding_type']) ? $data['folding_type'] : 'NULL') . "</td></tr>";
        echo "<tr><td>folding_price</td><td>" . (isset($data['folding_price']) ? $data['folding_price'] : 'NULL') . "</td></tr>";
        
        echo "<tr><td>creasing_enabled</td><td>" . (isset($data['creasing_enabled']) ? $data['creasing_enabled'] : 'NULL') . "</td></tr>";
        echo "<tr><td>creasing_lines</td><td>" . (isset($data['creasing_lines']) ? $data['creasing_lines'] : 'NULL') . "</td></tr>";
        echo "<tr><td>creasing_price</td><td>" . (isset($data['creasing_price']) ? $data['creasing_price'] : 'NULL') . "</td></tr>";
        
        echo "<tr><td>additional_options_total</td><td>" . (isset($data['additional_options_total']) ? $data['additional_options_total'] : 'NULL') . "</td></tr>";
        echo "<tr><td>selected_options</td><td>" . (isset($data['selected_options']) ? $data['selected_options'] : 'NULL') . "</td></tr>";
        
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>장바구니에 데이터가 없습니다.</p>";
    echo "<p>먼저 <a href='../mlangprintauto/inserted/index.php'>전단지 페이지</a>에서 옵션을 선택하고 장바구니에 담아보세요.</p>";
}

mysqli_close($connect);
?>

<br><br>
<a href="basket.php">장바구니로 돌아가기</a> | <a href="quotation.php">견적서 보기</a>