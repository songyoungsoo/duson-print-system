<?php
// 데이터베이스 구조 및 데이터 확인용 테스트 파일
include "../../db.php";
$connect = $db;

if (!$connect) {
    die("DB 연결 실패");
}

mysqli_set_charset($connect, "utf8");

echo "<h2>MlangPrintAuto_transactionCate 테이블 구조</h2>";

// 테이블 구조 확인
$structure_query = "DESCRIBE MlangPrintAuto_transactionCate";
$structure_result = mysqli_query($connect, $structure_query);

echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($col = mysqli_fetch_assoc($structure_result)) {
    echo "<tr>";
    echo "<td>" . $col['Field'] . "</td>";
    echo "<td>" . $col['Type'] . "</td>";
    echo "<td>" . $col['Null'] . "</td>";
    echo "<td>" . $col['Key'] . "</td>";
    echo "<td>" . $col['Default'] . "</td>";
    echo "<td>" . $col['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>envelope 관련 데이터 (BigNo = 0인 구분들)</h2>";

// envelope 관련 구분 데이터 확인
$category_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable = 'envelope' AND BigNo = 0 ORDER BY no ASC";
$category_result = mysqli_query($connect, $category_query);

echo "<table border='1'>";
echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>Ttable</th></tr>";
while ($row = mysqli_fetch_assoc($category_result)) {
    echo "<tr>";
    echo "<td>" . $row['no'] . "</td>";
    echo "<td>" . $row['title'] . "</td>";
    echo "<td>" . $row['BigNo'] . "</td>";
    echo "<td>" . $row['Ttable'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>첫 번째 구분의 하위 종류들</h2>";

// 첫 번째 구분의 하위 종류 확인
$first_category_query = "SELECT no FROM MlangPrintAuto_transactionCate WHERE Ttable = 'envelope' AND BigNo = 0 ORDER BY no ASC LIMIT 1";
$first_category_result = mysqli_query($connect, $first_category_query);
$first_category = mysqli_fetch_assoc($first_category_result);

if ($first_category) {
    $first_no = $first_category['no'];
    echo "<p>첫 번째 구분 no: " . $first_no . "</p>";
    
    $types_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE BigNo = '$first_no' AND Ttable = 'envelope' ORDER BY no ASC";
    $types_result = mysqli_query($connect, $types_query);
    
    echo "<table border='1'>";
    echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>Ttable</th></tr>";
    while ($row = mysqli_fetch_assoc($types_result)) {
        echo "<tr>";
        echo "<td>" . $row['no'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['BigNo'] . "</td>";
        echo "<td>" . $row['Ttable'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>MlangPrintAuto_envelope 테이블 샘플 데이터</h2>";

// envelope 가격 테이블 샘플 데이터 확인
$envelope_query = "SELECT * FROM MlangPrintAuto_envelope LIMIT 10";
$envelope_result = mysqli_query($connect, $envelope_query);

if ($envelope_result) {
    echo "<table border='1'>";
    echo "<tr><th>style</th><th>Section</th><th>quantity</th><th>POtype</th><th>money</th><th>DesignMoney</th></tr>";
    while ($row = mysqli_fetch_assoc($envelope_result)) {
        echo "<tr>";
        echo "<td>" . $row['style'] . "</td>";
        echo "<td>" . $row['Section'] . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "<td>" . $row['POtype'] . "</td>";
        echo "<td>" . $row['money'] . "</td>";
        echo "<td>" . $row['DesignMoney'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>MlangPrintAuto_envelope 테이블 조회 실패: " . mysqli_error($connect) . "</p>";
}

mysqli_close($connect);
?>