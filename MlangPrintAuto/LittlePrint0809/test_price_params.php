<?php
/**
 * 특정 파라미터로 가격 데이터가 있는지 테스트
 */

// 테스트할 파라미터들
$MY_type = 590;
$PN_type = 610; 
$MY_Fsd = 604;
$MY_amount = 100;
$ordertype = 'total';
$POtype = 2;

echo "<h1>LittlePrint 가격 데이터 테스트</h1>";
echo "<h2>테스트 파라미터:</h2>";
echo "<ul>";
echo "<li>MY_type (종류): {$MY_type}</li>";
echo "<li>PN_type (종이규격): {$PN_type}</li>";
echo "<li>MY_Fsd (종이종류): {$MY_Fsd}</li>";
echo "<li>MY_amount (수량): {$MY_amount}</li>";
echo "<li>ordertype (주문형태): {$ordertype}</li>";
echo "<li>POtype (인쇄면): {$POtype}</li>";
echo "</ul>";

// 데이터베이스 연결
include "../../db.php";
$TABLE = "MlangPrintAuto_LittlePrint";

echo "<h2>데이터베이스 쿼리:</h2>";
$query = "SELECT * FROM $TABLE WHERE style='$MY_type' AND Section='$PN_type' AND quantity='$MY_amount' AND TreeSelect='$MY_Fsd' AND POtype='$POtype'";
echo "<pre>$query</pre>";

$result = mysqli_query($db, $query);
$row = mysqli_fetch_array($result);

echo "<h2>쿼리 결과:</h2>";
if ($row) {
    echo "<p style='color: green;'>✅ 데이터 찾음!</p>";
    echo "<pre>" . print_r($row, true) . "</pre>";
    
    // 가격 계산
    if ($ordertype == "print") {
        $Price = $row['money'];
        $DesignMoneyOk = 0;
        $Order_PricOk = $Price + $DesignMoneyOk;
    } elseif ($ordertype == "design") {
        $Price = 0;
        $DesignMoneyOk = $row['DesignMoney'];
        $Order_PricOk = $Price + $DesignMoneyOk;
    } else {
        $Price = $row['money'];
        $DesignMoneyOk = $row['DesignMoney'];
        $Order_PricOk = $Price + $DesignMoneyOk;
    }
    
    $VAT_PriceOk = $Order_PricOk / 10;
    $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;
    
    echo "<h2>계산된 가격:</h2>";
    echo "<ul>";
    echo "<li>인쇄비: " . number_format($Price) . "원</li>";
    echo "<li>디자인비: " . number_format($DesignMoneyOk) . "원</li>";
    echo "<li>합계: " . number_format($Order_PricOk) . "원</li>";
    echo "<li>부가세: " . number_format($VAT_PriceOk) . "원</li>";
    echo "<li>총액: " . number_format($Total_PriceOk) . "원</li>";
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>❌ 데이터 없음</p>";
    
    // 비슷한 데이터 찾기
    echo "<h3>비슷한 데이터 찾기:</h3>";
    $similar_query = "SELECT * FROM $TABLE WHERE style='$MY_type' LIMIT 5";
    echo "<p>같은 종류의 다른 데이터:</p>";
    echo "<pre>$similar_query</pre>";
    
    $similar_result = mysqli_query($db, $similar_query);
    if (mysqli_num_rows($similar_result) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>style</th><th>Section</th><th>TreeSelect</th><th>quantity</th><th>POtype</th><th>money</th></tr>";
        while ($similar_row = mysqli_fetch_array($similar_result)) {
            echo "<tr>";
            echo "<td>{$similar_row['style']}</td>";
            echo "<td>{$similar_row['Section']}</td>";
            echo "<td>{$similar_row['TreeSelect']}</td>";
            echo "<td>{$similar_row['quantity']}</td>";
            echo "<td>{$similar_row['POtype']}</td>";
            echo "<td>{$similar_row['money']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

mysqli_close($db);
?>