<?php
include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

echo "<h2>명함 데이터베이스 구조 디버그</h2>";

// 1. transactioncate 테이블에서 명함 카테고리 확인
echo "<h3>1. 명함 카테고리 (transactioncate)</h3>";
$query = "SELECT no, title, BigNo FROM mlangprintauto_transactioncate WHERE Ttable='NameCard' ORDER BY BigNo, no";
$result = mysqli_query($db, $query);
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>no</th><th>title</th><th>BigNo</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>{$row['no']}</td><td>{$row['title']}</td><td>{$row['BigNo']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "오류: " . mysqli_error($db);
}

// 2. namecard 테이블 구조 확인
echo "<h3>2. 명함 테이블 구조 (mlangprintauto_namecard)</h3>";
$query = "DESCRIBE mlangprintauto_namecard";
$result = mysqli_query($db, $query);
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "오류: " . mysqli_error($db);
}

// 3. namecard 테이블 샘플 데이터 확인
echo "<h3>3. 명함 테이블 샘플 데이터 (상위 10개)</h3>";
$query = "SELECT * FROM mlangprintauto_namecard LIMIT 10";
$result = mysqli_query($db, $query);
if ($result) {
    echo "<table border='1'>";
    $first = true;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($first) {
            echo "<tr>";
            foreach (array_keys($row) as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            $first = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "오류: " . mysqli_error($db);
}

// 4. 특정 조합으로 수량 조회 테스트
echo "<h3>4. 수량 조회 테스트</h3>";
$test_style = '275'; // 일반명함(쿠폰)
$test_section = '276'; // 칼라코팅
$test_potype = '1'; // 단면

echo "<p>테스트 조건: style=$test_style, section=$test_section, potype=$test_potype</p>";

$query = "SELECT DISTINCT quantity FROM mlangprintauto_namecard 
          WHERE style='$test_style' AND Section='$test_section' AND POtype='$test_potype'
          ORDER BY CAST(quantity AS UNSIGNED) ASC";
echo "<p>쿼리: $query</p>";

$result = mysqli_query($db, $query);
if ($result) {
    echo "<p>결과:</p>";
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>{$row['quantity']}매</li>";
    }
    echo "</ul>";
} else {
    echo "오류: " . mysqli_error($db);
}

mysqli_close($db);
?>