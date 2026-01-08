<?php
// 가격 조회 테스트 스크립트
include "../../db.php";

mysqli_set_charset($db, "utf8");

// 테스트 파라미터 (콘솔에서 본 값들)
$MY_type = '802';  // 인쇄색상
$PN_type = '818';  // 종이규격
$MY_Fsd = '714';   // 종이종류
$MY_amount = $_GET['MY_amount'] ?? '100';  // 수량 (URL에서 지정)
$POtype = '1';     // 단면/양면

echo "<h2>전단지 가격 조회 테스트</h2>";
echo "<p>파라미터:</p>";
echo "<ul>";
echo "<li>MY_type (인쇄색상): $MY_type</li>";
echo "<li>PN_type (종이규격): $PN_type</li>";
echo "<li>MY_Fsd (종이종류): $MY_Fsd</li>";
echo "<li>MY_amount (수량): $MY_amount</li>";
echo "<li>POtype (단면/양면): $POtype</li>";
echo "</ul>";

$TABLE = "mlangprintauto_inserted";

// 1. 정확한 조회
$query = "SELECT * FROM $TABLE WHERE style='$MY_type' AND Section='$PN_type' AND quantity='$MY_amount' AND TreeSelect='$MY_Fsd' AND POtype='$POtype'";
echo "<h3>1. 정확한 조회 쿼리:</h3>";
echo "<pre>$query</pre>";

$result = mysqli_query($db, $query);
if ($result) {
    $count = mysqli_num_rows($result);
    echo "<p><strong>결과: $count 개</strong></p>";
    
    if ($count > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>no</th><th>style</th><th>Section</th><th>quantity</th><th>TreeSelect</th><th>POtype</th><th>money</th><th>DesignMoney</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['no'] . "</td>";
            echo "<td>" . $row['style'] . "</td>";
            echo "<td>" . $row['Section'] . "</td>";
            echo "<td>" . $row['quantity'] . "</td>";
            echo "<td>" . $row['TreeSelect'] . "</td>";
            echo "<td>" . $row['POtype'] . "</td>";
            echo "<td>" . number_format($row['money']) . "원</td>";
            echo "<td>" . number_format($row['DesignMoney']) . "원</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ 해당 조합의 가격 정보가 없습니다!</p>";
        
        // 2. 비슷한 데이터 찾기
        echo "<h3>2. 비슷한 데이터 찾기 (style, Section, TreeSelect만 일치):</h3>";
        $query2 = "SELECT * FROM $TABLE WHERE style='$MY_type' AND Section='$PN_type' AND TreeSelect='$MY_Fsd' LIMIT 10";
        echo "<pre>$query2</pre>";
        
        $result2 = mysqli_query($db, $query2);
        if ($result2 && mysqli_num_rows($result2) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>no</th><th>style</th><th>Section</th><th>quantity</th><th>TreeSelect</th><th>POtype</th><th>money</th></tr>";
            while ($row = mysqli_fetch_assoc($result2)) {
                echo "<tr>";
                echo "<td>" . $row['no'] . "</td>";
                echo "<td>" . $row['style'] . "</td>";
                echo "<td>" . $row['Section'] . "</td>";
                echo "<td>" . $row['quantity'] . "</td>";
                echo "<td>" . $row['TreeSelect'] . "</td>";
                echo "<td>" . $row['POtype'] . "</td>";
                echo "<td>" . number_format($row['money']) . "원</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p>💡 위 데이터를 참고하여 올바른 수량을 선택하세요.</p>";
        }
        
        // 3. 사용 가능한 수량 목록 확인
        echo "<h3>3. 이 조합에서 사용 가능한 수량 목록:</h3>";
        $query3 = "SELECT DISTINCT quantity FROM $TABLE WHERE style='$MY_type' AND Section='$PN_type' AND TreeSelect='$MY_Fsd' AND POtype='$POtype' ORDER BY CAST(quantity AS UNSIGNED)";
        echo "<pre>$query3</pre>";
        
        $result3 = mysqli_query($db, $query3);
        if ($result3 && mysqli_num_rows($result3) > 0) {
            echo "<p>사용 가능한 수량:</p><ul>";
            while ($row = mysqli_fetch_assoc($result3)) {
                echo "<li>" . $row['quantity'] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>이 조합에는 등록된 수량이 없습니다.</p>";
        }
    }
} else {
    echo "<p style='color: red;'>쿼리 오류: " . mysqli_error($db) . "</p>";
}

mysqli_close($db);
?>
