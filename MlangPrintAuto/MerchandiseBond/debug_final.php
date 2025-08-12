<?php
// 최종 디버깅 - 실제 전달되는 값으로 테스트
header('Content-Type: text/html; charset=utf-8');
include "../../db_auto.php";

echo "<h2>상품권 가격 계산 최종 디버깅</h2>";

// 1. 현재 브라우저에서 전달되는 실제 값들로 테스트
echo "<h3>1. 실제 전달 값으로 테스트</h3>";

// 실제 브라우저에서 전달되는 값들 (콘솔에서 확인된 값)
$test_values = [
    'MY_type' => '61461',  // 실제 브라우저에서 전달되는 값
    'MY_amount' => '500',
    'POtype' => '1',
    'ordertype' => 'total'
];

echo "<p><strong>테스트 파라미터:</strong></p>";
foreach ($test_values as $key => $value) {
    echo "<p>$key = '$value'</p>";
}

// 2. 데이터베이스에서 매칭 시도
$TABLE = "MlangPrintAuto_MerchandiseBond";
$query = "SELECT * FROM $TABLE WHERE style=? AND quantity=? AND POtype=? LIMIT 1";

echo "<h3>2. 쿼리 실행</h3>";
echo "<p><strong>쿼리:</strong> $query</p>";
echo "<p><strong>파라미터:</strong> style='{$test_values['MY_type']}', quantity='{$test_values['MY_amount']}', POtype='{$test_values['POtype']}'</p>";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'sss', $test_values['MY_type'], $test_values['MY_amount'], $test_values['POtype']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo "<h3>✅ 매칭 성공!</h3>";
    echo "<table border='1'>";
    foreach ($row as $key => $value) {
        echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
    }
    echo "</table>";
    
    // 가격 계산
    $ordertype = $test_values['ordertype'];
    if ($ordertype == "print") {
        $Price = $row['money'];
        $DesignMoneyOk = 0;
    } else if ($ordertype == "design") {
        $Price = 0;
        $DesignMoneyOk = $row['DesignMoney'] ?? 0;
    } else {
        $Price = $row['money'];
        $DesignMoneyOk = $row['DesignMoney'] ?? 0;
    }
    
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
    echo "<h3>❌ 매칭 실패</h3>";
    
    // 3. 각 조건별로 확인
    echo "<h3>3. 각 조건별 데이터 확인</h3>";
    
    // style 확인
    $style_query = "SELECT COUNT(*) as cnt, GROUP_CONCAT(DISTINCT style) as styles FROM $TABLE WHERE style='{$test_values['MY_type']}'";
    $style_result = mysqli_query($db, $style_query);
    $style_data = mysqli_fetch_assoc($style_result);
    echo "<p><strong>style='{$test_values['MY_type']}'</strong> 데이터 개수: {$style_data['cnt']}</p>";
    
    if ($style_data['cnt'] == 0) {
        // 사용 가능한 style 값들 확인
        $available_styles = mysqli_query($db, "SELECT DISTINCT style FROM $TABLE ORDER BY style");
        echo "<p><strong>사용 가능한 style 값들:</strong> ";
        while ($style_row = mysqli_fetch_assoc($available_styles)) {
            echo "'{$style_row['style']}', ";
        }
        echo "</p>";
    }
    
    // quantity 확인
    $quantity_query = "SELECT COUNT(*) as cnt FROM $TABLE WHERE quantity='{$test_values['MY_amount']}'";
    $quantity_result = mysqli_query($db, $quantity_query);
    $quantity_data = mysqli_fetch_assoc($quantity_result);
    echo "<p><strong>quantity='{$test_values['MY_amount']}'</strong> 데이터 개수: {$quantity_data['cnt']}</p>";
    
    if ($quantity_data['cnt'] == 0) {
        // 사용 가능한 quantity 값들 확인
        $available_quantities = mysqli_query($db, "SELECT DISTINCT quantity FROM $TABLE ORDER BY CAST(quantity AS UNSIGNED)");
        echo "<p><strong>사용 가능한 quantity 값들:</strong> ";
        while ($quantity_row = mysqli_fetch_assoc($available_quantities)) {
            echo "'{$quantity_row['quantity']}', ";
        }
        echo "</p>";
    }
    
    // POtype 확인
    $potype_query = "SELECT COUNT(*) as cnt FROM $TABLE WHERE POtype='{$test_values['POtype']}'";
    $potype_result = mysqli_query($db, $potype_query);
    $potype_data = mysqli_fetch_assoc($potype_result);
    echo "<p><strong>POtype='{$test_values['POtype']}'</strong> 데이터 개수: {$potype_data['cnt']}</p>";
    
    if ($potype_data['cnt'] == 0) {
        // 사용 가능한 POtype 값들 확인
        $available_potypes = mysqli_query($db, "SELECT DISTINCT POtype FROM $TABLE ORDER BY POtype");
        echo "<p><strong>사용 가능한 POtype 값들:</strong> ";
        while ($potype_row = mysqli_fetch_assoc($available_potypes)) {
            echo "'{$potype_row['POtype']}', ";
        }
        echo "</p>";
    }
    
    // 4. 가장 비슷한 데이터 찾기
    echo "<h3>4. 전체 데이터 샘플</h3>";
    $sample_query = "SELECT * FROM $TABLE LIMIT 5";
    $sample_result = mysqli_query($db, $sample_query);
    
    echo "<table border='1'>";
    echo "<tr><th>no</th><th>style</th><th>Section</th><th>quantity</th><th>money</th><th>DesignMoney</th><th>POtype</th></tr>";
    while ($sample_row = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>";
        echo "<td>{$sample_row['no']}</td>";
        echo "<td>{$sample_row['style']}</td>";
        echo "<td>{$sample_row['Section']}</td>";
        echo "<td>{$sample_row['quantity']}</td>";
        echo "<td>{$sample_row['money']}</td>";
        echo "<td>{$sample_row['DesignMoney']}</td>";
        echo "<td>{$sample_row['POtype']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($db);
?>

<h3>5. 해결 방안</h3>
<p>위 결과를 보고 다음 중 하나를 선택하세요:</p>
<ol>
<li><strong>데이터 추가:</strong> 브라우저에서 전달하는 값에 맞는 데이터를 데이터베이스에 추가</li>
<li><strong>매핑 수정:</strong> 브라우저에서 전달하는 값을 데이터베이스에 있는 값으로 변환</li>
<li><strong>기본값 사용:</strong> 매칭되지 않을 때 기본 가격 사용</li>
</ol>