<?php
// 가장 기본적인 테스트
echo "<h2>카다록 기본 테스트</h2>";

// 1. 데이터베이스 연결
include "../../db_xampp.php";
echo "1. 데이터베이스 연결: " . ($db ? "성공" : "실패") . "<br>";

// 2. 카다록 옵션 데이터 확인
$GGTABLE = "MlangPrintAuto_transactionCate";
$query = "SELECT * FROM $GGTABLE WHERE Ttable='cadarok' AND BigNo='0' LIMIT 1";
$result = mysqli_query($db, $query);
$has_options = mysqli_num_rows($result) > 0;
echo "2. 카다록 옵션 데이터: " . ($has_options ? "있음" : "없음") . "<br>";

// 3. 카다록 가격 데이터 확인
$TABLE = "MlangPrintAuto_cadarok";
$query2 = "SELECT * FROM $TABLE LIMIT 1";
$result2 = mysqli_query($db, $query2);
$has_prices = mysqli_num_rows($result2) > 0;
echo "3. 카다록 가격 데이터: " . ($has_prices ? "있음" : "없음") . "<br>";

// 4. price_cal.php 파일 존재 확인
$price_file_exists = file_exists("price_cal.php");
echo "4. price_cal.php 파일: " . ($price_file_exists ? "있음" : "없음") . "<br>";

echo "<br><strong>결론:</strong><br>";
if (!$db) {
    echo "❌ 데이터베이스 연결 문제";
} elseif (!$has_options) {
    echo "❌ 카다록 옵션 데이터 없음";
} elseif (!$has_prices) {
    echo "❌ 카다록 가격 데이터 없음";
} elseif (!$price_file_exists) {
    echo "❌ price_cal.php 파일 없음";
} else {
    echo "✅ 모든 기본 요소 정상";
}
?>