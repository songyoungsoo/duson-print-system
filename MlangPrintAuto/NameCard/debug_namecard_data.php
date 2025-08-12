<?php
include "db.php";

echo "<h3>명함 데이터베이스 구조 확인</h3>";

// 명함 관련 테이블 확인
$GGTABLE = "MlangPrintAuto_transactionCate";
$TABLE = "MlangPrintAuto_NameCard";

echo "<h4>명함 카테고리 데이터 (GGTABLE):</h4>";
$cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='NameCard' ORDER BY no ASC");
while ($cate_row = mysqli_fetch_array($cate_result)) {
    echo "no: {$cate_row['no']}, title: {$cate_row['title']}, BigNo: {$cate_row['BigNo']}, TreeNo: {$cate_row['TreeNo']}<br>";
}

echo "<h4>명함 가격 테이블 구조:</h4>";
$structure_result = mysqli_query($db, "DESCRIBE $TABLE");
if ($structure_result) {
    while ($structure_row = mysqli_fetch_array($structure_result)) {
        echo $structure_row['Field'] . " (" . $structure_row['Type'] . ")<br>";
    }
} else {
    echo "테이블이 존재하지 않습니다.<br>";
}

echo "<h4>명함 가격 샘플 데이터:</h4>";
$sample_result = mysqli_query($db, "SELECT * FROM $TABLE LIMIT 10");
if ($sample_result) {
    while ($sample_row = mysqli_fetch_array($sample_result)) {
        echo "style: {$sample_row['style']}, Section: {$sample_row['Section']}, quantity: {$sample_row['quantity']}, TreeSelect: {$sample_row['TreeSelect']}, money: {$sample_row['money']}<br>";
    }
} else {
    echo "가격 데이터가 없습니다.<br>";
}

mysqli_close($db);
?>