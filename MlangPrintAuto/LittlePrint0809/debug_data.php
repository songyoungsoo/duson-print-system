<?php
include "../../db.php";

if (!$db) {
    die("Database connection failed");
}

mysqli_set_charset($db, "utf8");

echo "<h2>LittlePrint 카테고리 데이터 확인</h2>";

// 1. 구분 (BigNo='0')
echo "<h3>1. 구분 (BigNo='0')</h3>";
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' AND BigNo='0' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

// 2. 종이종류 (BigNo='590')
echo "<h3>2. 종이종류 (BigNo='590')</h3>";
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' AND BigNo='590' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

// 3. 종이규격 (TreeNo='590')
echo "<h3>3. 종이규격 (TreeNo='590')</h3>";
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' AND TreeNo='590' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

// 4. 전체 LittlePrint 데이터
echo "<h3>4. 전체 LittlePrint 데이터</h3>";
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

mysqli_close($db);
?>