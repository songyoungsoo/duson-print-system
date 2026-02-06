<?php
include "../../db.php";

if (!$db) {
    die("Database connection failed");
}

mysqli_set_charset($db, "utf8");

echo "<h2>mlangprintauto_transactioncate 테이블 데이터 추가</h2>";

// transactioncate 테이블 데이터 추가 (inserted와 동일한 구조)
$insert_queries = [
    // 1. 구분 (BigNo='0')
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (590, 'LittlePrint', '0', '소량포스터', '')",
    
    // 2. 종이규격/Section (BigNo='590') - inserted와 동일
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (610, 'LittlePrint', '590', '국2절', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (611, 'LittlePrint', '590', 'A3', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (612, 'LittlePrint', '590', 'A2', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (613, 'LittlePrint', '590', 'A1', '')",
    
    // 3. 종이종류/TreeSelect (TreeNo='590') - inserted와 동일
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (604, 'LittlePrint', '', '120아트/스노우', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (605, 'LittlePrint', '', '150아트/스노우', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (606, 'LittlePrint', '', '180아트/스노우', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (607, 'LittlePrint', '', '200아트/스노우', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (608, 'LittlePrint', '', '250아트/스노우', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (609, 'LittlePrint', '', '300아트/스노우', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (679, 'LittlePrint', '', '80모조', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (680, 'LittlePrint', '', '100모조', '590')"
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
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' AND BigNo='0' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

// 2. 종이규격/Section 확인 (BigNo='590') - inserted와 동일
echo "<h4>2. 종이규격/Section (BigNo='590')</h4>";
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' AND BigNo='590' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

// 3. 종이종류/TreeSelect 확인 (TreeNo='590') - inserted와 동일
echo "<h4>3. 종이종류/TreeSelect (TreeNo='590')</h4>";
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' AND TreeNo='590' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

mysqli_close($db);

echo "<br><strong>데이터 추가 완료! 이제 littleprint 페이지를 테스트해보세요.</strong>";
?>