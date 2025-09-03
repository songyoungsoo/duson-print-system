<?php
include "db_ajax.php";

echo "<h3>명함 POtype (양면/단면) 데이터 확인</h3>";

// POtype 값들 확인
echo "<h4>전체 POtype 값들:</h4>";
$potype_result = mysqli_query($db, "SELECT DISTINCT POtype FROM MlangPrintAuto_NameCard ORDER BY POtype ASC");
if ($potype_result && mysqli_num_rows($potype_result) > 0) {
    while ($potype_row = mysqli_fetch_array($potype_result)) {
        echo "POtype: " . $potype_row['POtype'] . "<br>";
    }
} else {
    echo "POtype 데이터 없음<br>";
}

echo "<br><h4>일반명함(275) - 칼라코팅(276) - 1000매의 POtype별 가격:</h4>";
$price_result = mysqli_query($db, "SELECT POtype, money FROM MlangPrintAuto_NameCard WHERE style='275' AND Section='276' AND quantity='1000' ORDER BY POtype ASC");
if ($price_result && mysqli_num_rows($price_result) > 0) {
    while ($price_row = mysqli_fetch_array($price_result)) {
        $potype_name = ($price_row['POtype'] == '1') ? '단면' : (($price_row['POtype'] == '2') ? '양면' : 'POtype ' . $price_row['POtype']);
        echo "• {$potype_name} (POtype: {$price_row['POtype']}): " . number_format($price_row['money']) . "원<br>";
    }
} else {
    echo "해당 조건의 데이터 없음<br>";
}

echo "<br><h4>고급수입지(278) - 휘라레216g(279) - 300매의 POtype별 가격:</h4>";
$premium_result = mysqli_query($db, "SELECT POtype, money FROM MlangPrintAuto_NameCard WHERE style='278' AND Section='279' AND quantity='300' ORDER BY POtype ASC");
if ($premium_result && mysqli_num_rows($premium_result) > 0) {
    while ($premium_row = mysqli_fetch_array($premium_result)) {
        $potype_name = ($premium_row['POtype'] == '1') ? '단면' : (($premium_row['POtype'] == '2') ? '양면' : 'POtype ' . $premium_row['POtype']);
        echo "• {$potype_name} (POtype: {$premium_row['POtype']}): " . number_format($premium_row['money']) . "원<br>";
    }
} else {
    echo "해당 조건의 데이터 없음<br>";
}

mysqli_close($db);
?>