<?php
include "../../db.php";

if (!$db) {
    die("Database connection failed");
}

mysqli_set_charset($db, "utf8");

echo "<h2>LittlePrint 테스트 데이터 추가</h2>";

// 1. 구분 데이터 추가
$insert_queries = [
    // 구분 (BigNo='0')
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (590, 'LittlePrint', '0', '소량포스터', '')",
    
    // 종이종류 (BigNo='590')
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (680, 'LittlePrint', '590', '100모조', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (604, 'LittlePrint', '590', '120아트/스노우', '')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (679, 'LittlePrint', '590', '80모조', '')",
    
    // 종이규격 (TreeNo='590')
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (610, 'LittlePrint', '', '국2절', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (611, 'LittlePrint', '', 'A3', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (612, 'LittlePrint', '', 'A2', '590')",
    "INSERT IGNORE INTO mlangprintauto_transactioncate (no, Ttable, BigNo, title, TreeNo) VALUES (613, 'LittlePrint', '', 'A1', '590')"
];

foreach ($insert_queries as $query) {
    if (mysqli_query($db, $query)) {
        echo "✓ 데이터 추가 성공: " . $query . "<br>";
    } else {
        echo "✗ 데이터 추가 실패: " . mysqli_error($db) . "<br>";
    }
}

// 2. 가격 테이블 데이터 추가
echo "<h3>가격 테이블 데이터 추가</h3>";

$price_queries = [
    // 기본 가격 데이터
    "INSERT IGNORE INTO mlangprintauto_littleprint (no, style, Section, quantity, money, TreeSelect, DesignMoney, POtype, quantityTwo) VALUES 
     (1, '590', '610', 10, '50000', '679', '30000', '1', ''),
     (2, '590', '610', 20, '80000', '679', '30000', '1', ''),
     (3, '590', '610', 50, '150000', '679', '30000', '1', ''),
     (4, '590', '610', 100, '250000', '679', '30000', '1', ''),
     (5, '590', '610', 10, '80000', '679', '30000', '2', ''),
     (6, '590', '610', 20, '120000', '679', '30000', '2', ''),
     (7, '590', '610', 50, '220000', '679', '30000', '2', ''),
     (8, '590', '610', 100, '380000', '679', '30000', '2', '')"
];

foreach ($price_queries as $query) {
    if (mysqli_query($db, $query)) {
        echo "✓ 가격 데이터 추가 성공<br>";
    } else {
        echo "✗ 가격 데이터 추가 실패: " . mysqli_error($db) . "<br>";
    }
}

echo "<h3>추가된 데이터 확인</h3>";

// 추가된 데이터 확인
$query = "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' ORDER BY no ASC";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($result)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

mysqli_close($db);

echo "<br><strong>데이터 추가 완료! 이제 littleprint 페이지를 테스트해보세요.</strong>";
?>