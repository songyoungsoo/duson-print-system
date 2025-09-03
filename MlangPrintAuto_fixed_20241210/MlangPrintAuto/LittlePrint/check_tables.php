<?php
// 테이블 구조 확인용 파일
include "../../db_auto.php";

echo "<h2>LittlePrint 테이블 구조 확인</h2>";

// 1. mlangprintauto_transactioncate 테이블 확인
echo "<h3>1. mlangprintauto_transactioncate 테이블 (LittlePrint 관련)</h3>";
$query1 = "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='LittlePrint' ORDER BY BigNo, TreeNo, no";
$result1 = mysqli_query($db, $query1);

if ($result1 && mysqli_num_rows($result1) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>TreeNo</th><th>Ttable</th></tr>";
    while ($row = mysqli_fetch_array($result1)) {
        echo "<tr>";
        echo "<td>" . $row['no'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['BigNo'] . "</td>";
        echo "<td>" . $row['TreeNo'] . "</td>";
        echo "<td>" . $row['Ttable'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "데이터가 없거나 테이블이 존재하지 않습니다.";
}

echo "<br><br>";

// 2. mlangprintauto_littleprint 테이블 확인
echo "<h3>2. mlangprintauto_littleprint 테이블 (가격 데이터)</h3>";
$query2 = "SELECT * FROM MlangPrintAuto_LittlePrint LIMIT 10";
$result2 = mysqli_query($db, $query2);

if ($result2 && mysqli_num_rows($result2) > 0) {
    echo "<table border='1'>";
    // 컬럼명 가져오기
    $fields = mysqli_fetch_fields($result2);
    echo "<tr>";
    foreach ($fields as $field) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    
    // 데이터 표시
    mysqli_data_seek($result2, 0); // 결과셋 포인터 리셋
    while ($row = mysqli_fetch_array($result2)) {
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<td>" . ($row[$field->name] ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "데이터가 없거나 테이블이 존재하지 않습니다.<br>";
    echo "에러: " . mysqli_error($db);
}

echo "<br><br>";

// 3. 실제 사용 가능한 조합 확인
echo "<h3>3. 실제 사용 가능한 옵션 조합</h3>";
$query3 = "SELECT DISTINCT style, Section, TreeSelect, quantity, POtype FROM MlangPrintAuto_LittlePrint ORDER BY style, Section, TreeSelect, quantity";
$result3 = mysqli_query($db, $query3);

if ($result3 && mysqli_num_rows($result3) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>style</th><th>Section</th><th>TreeSelect</th><th>quantity</th><th>POtype</th></tr>";
    while ($row = mysqli_fetch_array($result3)) {
        echo "<tr>";
        echo "<td>" . $row['style'] . "</td>";
        echo "<td>" . $row['Section'] . "</td>";
        echo "<td>" . $row['TreeSelect'] . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "<td>" . $row['POtype'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "사용 가능한 조합이 없습니다.";
}

mysqli_close($db);
?>