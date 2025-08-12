<?php
include "db_ajax.php";

echo "<h3>명함 가격 계산 테스트</h3>";

// 실제 명함 폼 값들로 테스트
$NC_type = '275';    // 일반명함(쿠폰)
$NC_paper = '276';   // 칼라코팅
$NC_amount = '1000'; // 1000매
$POtype = '1';

echo "테스트 파라미터:<br>";
echo "NC_type (명함종류): $NC_type<br>";
echo "NC_paper (용지종류): $NC_paper<br>";
echo "NC_amount (수량): $NC_amount<br>";
echo "POtype: $POtype<br><br>";

// 명함 가격 쿼리
$query = "SELECT * FROM MlangPrintAuto_NameCard WHERE style='$NC_type' AND Section='$NC_paper' AND quantity='$NC_amount' AND POtype='$POtype'";

echo "쿼리:<br>$query<br><br>";

$result = mysqli_query($db, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
    echo "✅ 결과 찾음!<br>";
    echo "Money: " . number_format($row['money']) . "원<br>";
    echo "DesignMoney: " . number_format($row['DesignMoney'] ?? 30000) . "원<br>";
} else {
    echo "❌ 결과 없음<br>";
    
    // 비슷한 데이터가 있는지 확인
    echo "<br>비슷한 데이터 찾기:<br>";
    $similar_query = "SELECT * FROM MlangPrintAuto_NameCard WHERE style='$NC_type' LIMIT 5";
    $similar_result = mysqli_query($db, $similar_query);
    if ($similar_result) {
        while ($similar_row = mysqli_fetch_array($similar_result)) {
            echo "Style: {$similar_row['style']}, Section: {$similar_row['Section']}, Quantity: {$similar_row['quantity']}, POtype: {$similar_row['POtype']}, Money: {$similar_row['money']}<br>";
        }
    }
}

mysqli_close($db);
?>