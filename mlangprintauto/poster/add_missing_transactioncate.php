<?php
include "../../db.php";

if (!$db) {
    die("Database connection failed");
}

mysqli_set_charset($db, "utf8");

echo "<h2>누락된 transactioncate 데이터 추가</h2>";

// littleprint 테이블에서 실제 사용되는 값들을 기반으로 transactioncate 데이터 추가
$insert_queries = [
    // 1. 구분 (style=590)
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (590, 'LittlePrint', '0', '소량포스터', '')",
    
    // 2. 종이규격 (Section 값들)
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (610, 'LittlePrint', '', '국2절', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (611, 'LittlePrint', '', 'A3', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (612, 'LittlePrint', '', 'A2', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (613, 'LittlePrint', '', 'A1', '590')",
    
    // 3. 종이종류 (TreeSelect 값들)
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (604, 'LittlePrint', '590', '120아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (605, 'LittlePrint', '590', '150아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (606, 'LittlePrint', '590', '180아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (607, 'LittlePrint', '590', '200아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (608, 'LittlePrint', '590', '250아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (609, 'LittlePrint', '590', '300아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (679, 'LittlePrint', '590', '80모조', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (680, 'LittlePrint', '590', '100모조', '')"
];

foreach ($insert_queries as $query) {
    if (mysqli_query($db, $query)) {
        echo "✓ 데이터 추가 성공<br>";
    } else {
        echo "✗ 데이터 추가 실패: " . mysqli_error($db) . "<br>";
    }
}

echo "<h3>추가 완료된 데이터 확인</h3>";

// 추가된 데이터 확인
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: '{$row['BigNo']}', TreeNo: '{$row['TreeNo']}'<br>";
}

mysqli_close($db);

echo "<br><strong>데이터 추가 완료! 이제 littleprint 페이지를 테스트해보세요.</strong>";
?>