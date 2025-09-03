<?php
include "../../db.php";

if (!$db) {
    die("Database connection failed");
}

mysqli_set_charset($db, "utf8");

echo "<h2>mlangprintauto_transactioncate 테이블 데이터 추가</h2>";

// transactioncate 테이블 데이터 추가
$insert_queries = [
    // 1. 구분 (BigNo='0')
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (590, 'littleprint', '0', '소량포스터', '')",
    
    // 2. 종이규격 (TreeNo='590') - 실제 데이터에서 Section 값들
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (610, 'littleprint', '', '국2절', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (611, 'littleprint', '', 'A3', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (612, 'littleprint', '', 'A2', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (613, 'littleprint', '', 'A1', '590')",
    
    // 3. 종이종류 (BigNo='590') - 실제 데이터에서 TreeSelect 값들
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (604, 'littleprint', '590', '120아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (605, 'littleprint', '590', '150아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (606, 'littleprint', '590', '180아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (607, 'littleprint', '590', '200아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (608, 'littleprint', '590', '250아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (609, 'littleprint', '590', '300아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (679, 'littleprint', '590', '80모조', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (680, 'littleprint', '590', '100모조', '')"
];

foreach ($insert_queries as $query) {
    if (mysqli_query($db, $query)) {
        echo "✓ 데이터 추가 성공: " . $query . "<br>";
    } else {
        echo "✗ 데이터 추가 실패: " . mysqli_error($db) . "<br>";
    }
}

echo "<h3>추가된 데이터 확인</h3>";

// 1. 구분 확인
echo "<h4>1. 구분 (BigNo='0')</h4>";
$query = "SELECT no, title, BigNo, TreeNo FROM MlangPrintAuto_transactionCate WHERE Ttable='littleprint' AND BigNo='0' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

// 2. 종이종류 확인
echo "<h4>2. 종이종류 (BigNo='590')</h4>";
$query = "SELECT no, title, BigNo, TreeNo FROM MlangPrintAuto_transactionCate WHERE Ttable='littleprint' AND BigNo='590' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

// 3. 종이규격 확인
echo "<h4>3. 종이규격 (TreeNo='590')</h4>";
$query = "SELECT no, title, BigNo, TreeNo FROM MlangPrintAuto_transactionCate WHERE Ttable='littleprint' AND TreeNo='590' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

mysqli_close($db);

echo "<br><strong>데이터 추가 완료! 이제 littleprint 페이지를 테스트해보세요.</strong>";
?>