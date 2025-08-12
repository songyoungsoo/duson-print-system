<?php
// 매핑 테스트
include "../../db_auto.php";

echo "<h2>상품권 매핑 테스트</h2>";

// 매핑 함수 (price_cal_ajax.php와 동일)
function mapBrowserToDatabase($browser_value, $type) {
    switch ($type) {
        case 'style':
            // transactionCate의 no 값을 MerchandiseBond의 style 값으로 변환
            if (strlen($browser_value) > 3) {
                return substr($browser_value, 0, 3);
            }
            return $browser_value;
            
        case 'quantity':
            return $browser_value;
            
        case 'potype':
            return $browser_value;
            
        default:
            return $browser_value;
    }
}

// 실제 브라우저에서 전달되는 값들
$browser_values = [
    'MY_type' => '61461',
    'MY_amount' => '500',
    'POtype' => '1'
];

echo "<h3>1. 매핑 테스트</h3>";
echo "<table border='1'>";
echo "<tr><th>필드</th><th>브라우저 값</th><th>매핑된 값</th></tr>";

$mapped_style = mapBrowserToDatabase($browser_values['MY_type'], 'style');
$mapped_quantity = mapBrowserToDatabase($browser_values['MY_amount'], 'quantity');
$mapped_potype = mapBrowserToDatabase($browser_values['POtype'], 'potype');

echo "<tr><td>style</td><td>{$browser_values['MY_type']}</td><td>$mapped_style</td></tr>";
echo "<tr><td>quantity</td><td>{$browser_values['MY_amount']}</td><td>$mapped_quantity</td></tr>";
echo "<tr><td>potype</td><td>{$browser_values['POtype']}</td><td>$mapped_potype</td></tr>";
echo "</table>";

// 매핑된 값으로 데이터베이스 검색
echo "<h3>2. 매핑된 값으로 데이터베이스 검색</h3>";
$TABLE = "MlangPrintAuto_MerchandiseBond";
$query = "SELECT * FROM $TABLE WHERE style=? AND quantity=? AND POtype=? LIMIT 1";

echo "<p><strong>쿼리:</strong> $query</p>";
echo "<p><strong>파라미터:</strong> style='$mapped_style', quantity='$mapped_quantity', POtype='$mapped_potype'</p>";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'sss', $mapped_style, $mapped_quantity, $mapped_potype);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo "<h3>✅ 매핑 성공!</h3>";
    echo "<table border='1'>";
    foreach ($row as $key => $value) {
        echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
    }
    echo "</table>";
    
    // 가격 계산 테스트
    $ordertype = 'total';
    $Price = $row['money'];
    $DesignMoneyOk = $row['DesignMoney'] ?? 0;
    $Order_PricOk = $Price + $DesignMoneyOk;
    $VAT_PriceOk = $Order_PricOk / 10;
    $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;
    
    echo "<h3>계산된 가격:</h3>";
    echo "<p>인쇄비: " . number_format($Price) . "원</p>";
    echo "<p>편집비: " . number_format($DesignMoneyOk) . "원</p>";
    echo "<p>합계: " . number_format($Order_PricOk) . "원</p>";
    echo "<p>부가세: " . number_format($VAT_PriceOk) . "원</p>";
    echo "<p>총액: " . number_format($Total_PriceOk) . "원</p>";
    
} else {
    echo "<h3>❌ 매핑 실패</h3>";
    
    // 다른 매핑 방법 시도
    echo "<h3>3. 다른 매핑 방법 시도</h3>";
    
    // 방법 1: 전체 값 사용
    $alt_query1 = "SELECT * FROM $TABLE WHERE style='{$browser_values['MY_type']}' AND quantity='$mapped_quantity' AND POtype='$mapped_potype' LIMIT 1";
    $alt_result1 = mysqli_query($db, $alt_query1);
    echo "<p><strong>방법 1 (전체 값):</strong> " . mysqli_num_rows($alt_result1) . " 개 결과</p>";
    
    // 방법 2: 뒤 3자리 사용
    $alt_style2 = substr($browser_values['MY_type'], -3);
    $alt_query2 = "SELECT * FROM $TABLE WHERE style='$alt_style2' AND quantity='$mapped_quantity' AND POtype='$mapped_potype' LIMIT 1";
    $alt_result2 = mysqli_query($db, $alt_query2);
    echo "<p><strong>방법 2 (뒤 3자리 '$alt_style2'):</strong> " . mysqli_num_rows($alt_result2) . " 개 결과</p>";
    
    // 방법 3: 중간 3자리 사용
    if (strlen($browser_values['MY_type']) >= 5) {
        $alt_style3 = substr($browser_values['MY_type'], 1, 3);
        $alt_query3 = "SELECT * FROM $TABLE WHERE style='$alt_style3' AND quantity='$mapped_quantity' AND POtype='$mapped_potype' LIMIT 1";
        $alt_result3 = mysqli_query($db, $alt_query3);
        echo "<p><strong>방법 3 (중간 3자리 '$alt_style3'):</strong> " . mysqli_num_rows($alt_result3) . " 개 결과</p>";
    }
    
    // 실제 데이터베이스에 있는 style 값들 확인
    echo "<h3>4. 실제 데이터베이스의 style 값들</h3>";
    $styles_query = "SELECT DISTINCT style FROM $TABLE ORDER BY style";
    $styles_result = mysqli_query($db, $styles_query);
    echo "<p><strong>사용 가능한 style 값들:</strong> ";
    while ($style_row = mysqli_fetch_assoc($styles_result)) {
        echo "'{$style_row['style']}', ";
    }
    echo "</p>";
}

mysqli_close($db);
?>

<h3>5. 매핑 함수 수정 제안</h3>
<p>위 결과를 보고 올바른 매핑 규칙을 찾아서 price_cal_ajax.php의 mapBrowserToDatabase 함수를 수정하세요.</p>