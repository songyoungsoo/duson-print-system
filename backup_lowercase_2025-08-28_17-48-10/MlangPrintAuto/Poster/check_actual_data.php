<?php
include "../../db.php";

if (!$db) {
    die("Database connection failed");
}

mysqli_set_charset($db, "utf8");

echo "<h2>실제 데이터베이스 구조 확인</h2>";

// 1. transactioncate 테이블에서 LittlePrint 관련 데이터 확인
echo "<h3>1. transactioncate 테이블 - LittlePrint 데이터</h3>";
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='littleprint' ORDER BY no ASC";
$result = mysqli_query($db, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        echo "no: {$row['no']}, title: {$row['title']}, BigNo: '{$row['BigNo']}', TreeNo: '{$row['TreeNo']}'<br>";
    }
} else {
    echo "LittlePrint 데이터가 없습니다.<br>";
}

// 2. littleprint 테이블에서 실제 사용되는 값들 확인
echo "<h3>2. littleprint 테이블 - 실제 사용되는 값들</h3>";

echo "<h4>style 값들 (구분):</h4>";
$query = "SELECT DISTINCT style FROM mlangprintauto_littleprint ORDER BY style";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "style: {$row['style']}<br>";
}

echo "<h4>Section 값들 (종이규격):</h4>";
$query = "SELECT DISTINCT Section FROM mlangprintauto_littleprint ORDER BY Section";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "Section: {$row['Section']}<br>";
}

echo "<h4>TreeSelect 값들 (종이종류):</h4>";
$query = "SELECT DISTINCT TreeSelect FROM mlangprintauto_littleprint ORDER BY TreeSelect";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "TreeSelect: {$row['TreeSelect']}<br>";
}

echo "<h4>POtype 값들 (인쇄면):</h4>";
$query = "SELECT DISTINCT POtype FROM mlangprintauto_littleprint ORDER BY POtype";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "POtype: {$row['POtype']}<br>";
}

// 3. 샘플 데이터 몇 개 확인
echo "<h3>3. 샘플 데이터 (처음 10개)</h3>";
$query = "SELECT no, style, Section, TreeSelect, POtype, quantity, money FROM mlangprintauto_littleprint LIMIT 10";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, style: {$row['style']}, Section: {$row['Section']}, TreeSelect: {$row['TreeSelect']}, POtype: {$row['POtype']}, quantity: {$row['quantity']}, money: {$row['money']}<br>";
}

mysqli_close($db);
?>