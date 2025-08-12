<?php
echo "<h3>양면/단면 시스템 테스트</h3>";

// 양면/단면 옵션 테스트
echo "<h4>일반명함(275) - 칼라코팅(276) - 1000매의 양면/단면 옵션:</h4>";
$_GET['NC_type'] = '275';
$_GET['NC_paper'] = '276';
$_GET['NC_amount'] = '1000';
ob_start();
include "get_namecard_sides.php";
$sides_output = ob_get_clean();
echo "JSON 출력: " . $sides_output . "<br><br>";

// 수량 옵션 테스트 (용지 연동)
echo "<h4>일반명함(275) - 칼라코팅(276) 조합의 수량 옵션:</h4>";
$_GET['NC_type'] = '275';
$_GET['NC_paper'] = '276';
ob_start();
include "get_namecard_quantities.php";
$qty_output = ob_get_clean();
echo "JSON 출력: " . $qty_output . "<br><br>";

// 가격 계산 테스트 (양면)
echo "<h4>일반명함 양면 가격 계산 테스트:</h4>";
include "db_ajax.php";
$query = "SELECT * FROM MlangPrintAuto_NameCard WHERE style='275' AND Section='276' AND quantity='1000' AND POtype='2'";
echo "쿼리: $query<br>";
$result = mysqli_query($db, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
    echo "✅ 양면 가격: " . number_format($row['money']) . "원<br>";
} else {
    echo "❌ 양면 데이터 없음<br>";
}

// 단면 가격 비교
$query2 = "SELECT * FROM MlangPrintAuto_NameCard WHERE style='275' AND Section='276' AND quantity='1000' AND POtype='1'";
$result2 = mysqli_query($db, $query2);
if ($result2 && mysqli_num_rows($result2) > 0) {
    $row2 = mysqli_fetch_array($result2);
    echo "✅ 단면 가격: " . number_format($row2['money']) . "원<br>";
} else {
    echo "❌ 단면 데이터 없음<br>";
}

mysqli_close($db);
?>