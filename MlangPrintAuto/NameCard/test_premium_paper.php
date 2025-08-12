<?php
include "db_ajax.php";

echo "<h3>고급수입지 가격 계산 테스트</h3>";

// 고급수입지 특정 수량으로 테스트
$NC_type = '278';    // 고급수입지
$NC_paper = '279';   // 휘라레216g
$NC_amount = '300';  // 300매 (고급수입지 전용 수량)
$POtype = '1';

echo "테스트 파라미터:<br>";
echo "NC_type (명함종류): $NC_type (고급수입지)<br>";
echo "NC_paper (용지종류): $NC_paper (휘라레216g)<br>";
echo "NC_amount (수량): $NC_amount (300매)<br>";
echo "POtype: $POtype<br><br>";

// 고급수입지 가격 쿼리
$query = "SELECT * FROM MlangPrintAuto_NameCard WHERE style='$NC_type' AND Section='$NC_paper' AND quantity='$NC_amount' AND POtype='$POtype'";

echo "쿼리:<br>$query<br><br>";

$result = mysqli_query($db, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
    echo "✅ 고급수입지 300매 결과 찾음!<br>";
    echo "Money: " . number_format($row['money']) . "원<br>";
    echo "DesignMoney: " . number_format($row['DesignMoney'] ?? 30000) . "원<br>";
} else {
    echo "❌ 결과 없음<br>";
    
    // 해당 스타일의 다른 수량들 확인
    echo "<br>고급수입지(278)의 다른 수량들:<br>";
    $other_query = "SELECT quantity, money FROM MlangPrintAuto_NameCard WHERE style='$NC_type' AND Section='$NC_paper' ORDER BY quantity ASC";
    $other_result = mysqli_query($db, $other_query);
    if ($other_result) {
        while ($other_row = mysqli_fetch_array($other_result)) {
            echo "수량: {$other_row['quantity']}매, 가격: " . number_format($other_row['money']) . "원<br>";
        }
    }
}

mysqli_close($db);
?>