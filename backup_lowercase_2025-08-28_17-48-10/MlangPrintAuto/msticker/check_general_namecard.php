<?php
include "db_ajax.php";

echo "<h3>일반명함 수량 데이터 정확한 확인</h3>";

// 일반명함(275)의 실제 사용 가능한 수량들 확인
echo "<h4>일반명함(275) - 칼라코팅(276) 조합의 수량들:</h4>";
$query = "SELECT DISTINCT quantity, money FROM mlangprintauto_namecard WHERE style='275' AND Section='276' ORDER BY quantity ASC";
$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "사용 가능한 수량들:<br>";
    while ($row = mysqli_fetch_array($result)) {
        $qty = $row['quantity'];
        $money = number_format($row['money']);
        echo "• {$qty}매 - {$money}원<br>";
    }
} else {
    echo "데이터 없음<br>";
}

echo "<br><h4>일반명함(275) - 칼라비코팅(277) 조합의 수량들:</h4>";
$query2 = "SELECT DISTINCT quantity, money FROM mlangprintauto_namecard WHERE style='275' AND Section='277' ORDER BY quantity ASC";
$result2 = mysqli_query($db, $query2);

if ($result2 && mysqli_num_rows($result2) > 0) {
    echo "사용 가능한 수량들:<br>";
    while ($row2 = mysqli_fetch_array($result2)) {
        $qty = $row2['quantity'];
        $money = number_format($row2['money']);
        echo "• {$qty}매 - {$money}원<br>";
    }
} else {
    echo "데이터 없음<br>";
}

mysqli_close($db);
?>