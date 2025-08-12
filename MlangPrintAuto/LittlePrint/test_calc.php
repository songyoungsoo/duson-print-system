<?php
// 포스터 계산 테스트
include "../../db_auto.php";

echo "<h2>포스터 계산 테스트</h2>";

// 테스트 파라미터
$test_params = [
    'MY_type' => '590',
    'PN_type' => '605', 
    'MY_Fsd' => '610',
    'MY_amount' => '1',
    'POtype' => '1',
    'ordertype' => 'total'
];

echo "<h3>테스트 파라미터:</h3>";
foreach ($test_params as $key => $value) {
    echo "$key = $value<br>";
}

// 실제 데이터 확인
$TABLE = "MlangPrintAuto_LittlePrint";
$query = "SELECT * FROM $TABLE WHERE style='{$test_params['MY_type']}' AND Section='{$test_params['PN_type']}' AND quantity='{$test_params['MY_amount']}' AND TreeSelect='{$test_params['MY_Fsd']}' AND POtype='{$test_params['POtype']}'";

echo "<h3>실행할 쿼리:</h3>";
echo "<code>$query</code><br><br>";

$result = mysqli_query($db, $query);

if (!$result) {
    echo "<span style='color:red'>쿼리 실행 실패: " . mysqli_error($db) . "</span>";
} else {
    $num_rows = mysqli_num_rows($result);
    echo "<h3>쿼리 결과: $num_rows 행</h3>";
    
    if ($num_rows > 0) {
        $row = mysqli_fetch_array($result);
        echo "<table border='1'>";
        echo "<tr><th>컬럼</th><th>값</th></tr>";
        foreach ($row as $key => $value) {
            if (!is_numeric($key)) {
                echo "<tr><td>$key</td><td>$value</td></tr>";
            }
        }
        echo "</table>";
        
        // 계산 테스트
        $ordertype = $test_params['ordertype'];
        if ($ordertype == "print") {
            $Price = $row['money'];
            $DesignMoneyOk = 0;
        } else if ($ordertype == "design") {
            $Price = 0;
            $DesignMoneyOk = $row['DesignMoney'];
        } else {
            $Price = $row['money'];
            $DesignMoneyOk = $row['DesignMoney'];
        }
        
        $Order_PricOk = $Price + $DesignMoneyOk;
        $VAT_PriceOk = $Order_PricOk / 10;
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;
        
        echo "<h3>계산 결과:</h3>";
        echo "인쇄비: " . number_format($Price) . "원<br>";
        echo "디자인비: " . number_format($DesignMoneyOk) . "원<br>";
        echo "합계: " . number_format($Order_PricOk) . "원<br>";
        echo "부가세: " . number_format($VAT_PriceOk) . "원<br>";
        echo "총액: " . number_format($Total_PriceOk) . "원<br>";
        
    } else {
        echo "<span style='color:red'>해당 조합의 데이터가 없습니다.</span><br>";
        
        // 비슷한 데이터 찾기
        echo "<h4>비슷한 데이터 찾기:</h4>";
        $similar_query = "SELECT * FROM $TABLE WHERE style='{$test_params['MY_type']}' LIMIT 5";
        $similar_result = mysqli_query($db, $similar_query);
        
        if ($similar_result && mysqli_num_rows($similar_result) > 0) {
            echo "<table border='1'>";
            echo "<tr><th>style</th><th>Section</th><th>TreeSelect</th><th>quantity</th><th>POtype</th><th>money</th></tr>";
            while ($similar_row = mysqli_fetch_array($similar_result)) {
                echo "<tr>";
                echo "<td>" . $similar_row['style'] . "</td>";
                echo "<td>" . $similar_row['Section'] . "</td>";
                echo "<td>" . $similar_row['TreeSelect'] . "</td>";
                echo "<td>" . $similar_row['quantity'] . "</td>";
                echo "<td>" . $similar_row['POtype'] . "</td>";
                echo "<td>" . $similar_row['money'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
}

// 한글 제목 매핑 확인
echo "<h3>한글 제목 매핑:</h3>";
$mapping_query = "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE Ttable='LittlePrint' AND no IN ('590', '605', '610') ORDER BY no";
$mapping_result = mysqli_query($db, $mapping_query);

if ($mapping_result && mysqli_num_rows($mapping_result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>번호</th><th>한글 제목</th></tr>";
    while ($mapping_row = mysqli_fetch_array($mapping_result)) {
        echo "<tr>";
        echo "<td>" . $mapping_row['no'] . "</td>";
        echo "<td>" . $mapping_row['title'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "매핑 데이터가 없습니다.";
}

mysqli_close($db);
?>