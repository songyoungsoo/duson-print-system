<?php
// HTML 값과 DB 값 매칭 확인
include "../../db_xampp.php";

echo "<h2>HTML 값과 DB 값 매칭 확인</h2>";

// 1. HTML에서 사용하는 값들 확인
echo "<h3>1. HTML 드롭다운 값들</h3>";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 구분 (MY_type)
echo "<h4>구분 (MY_type):</h4>";
$query1 = "SELECT * FROM $GGTABLE WHERE Ttable='cadarok' AND BigNo='0' ORDER BY no ASC";
$result1 = mysqli_query($db, $query1);
while ($row = mysqli_fetch_array($result1)) {
    echo "- no: {$row['no']}, title: {$row['title']}<br>";
}

// 첫 번째 구분의 no 값 가져오기
$first_query = "SELECT * FROM $GGTABLE WHERE Ttable='cadarok' AND BigNo='0' ORDER BY no ASC LIMIT 1";
$first_result = mysqli_query($db, $first_query);
$first_row = mysqli_fetch_array($first_result);
$first_no = $first_row['no'];

echo "<h4>규격 (MY_Fsd) - BigNo='{$first_no}':</h4>";
$query2 = "SELECT * FROM $GGTABLE WHERE BigNo='$first_no' ORDER BY no ASC";
$result2 = mysqli_query($db, $query2);
while ($row = mysqli_fetch_array($result2)) {
    echo "- no: {$row['no']}, title: {$row['title']}<br>";
}

echo "<h4>종이종류 (PN_type) - TreeNo='{$first_no}':</h4>";
$query3 = "SELECT * FROM $GGTABLE WHERE TreeNo='$first_no' ORDER BY no ASC";
$result3 = mysqli_query($db, $query3);
while ($row = mysqli_fetch_array($result3)) {
    echo "- no: {$row['no']}, title: {$row['title']}<br>";
}

// 2. 데이터베이스 가격 테이블 값들 확인
echo "<h3>2. 가격 테이블 값들</h3>";
$TABLE = "MlangPrintAuto_cadarok";
$query4 = "SELECT DISTINCT style, Section, TreeSelect, quantity FROM $TABLE ORDER BY style, Section, TreeSelect, quantity LIMIT 10";
$result4 = mysqli_query($db, $query4);
echo "<table border='1'>";
echo "<tr><th>style</th><th>Section</th><th>TreeSelect</th><th>quantity</th></tr>";
while ($row = mysqli_fetch_array($result4)) {
    echo "<tr>";
    echo "<td>{$row['style']}</td>";
    echo "<td>{$row['Section']}</td>";
    echo "<td>{$row['TreeSelect']}</td>";
    echo "<td>{$row['quantity']}</td>";
    echo "</tr>";
}
echo "</table>";

// 3. 매핑 테스트
echo "<h3>3. 매핑 테스트</h3>";
echo "<p>HTML 값 → DB 값 변환:</p>";

function mapCadarokBrowserToDatabase($browser_value, $type) {
    switch ($type) {
        case 'style':
            return '691';
        case 'section':
            if (strlen($browser_value) > 3) {
                return substr($browser_value, 0, 3);
            }
            return $browser_value;
        case 'quantity':
            return $browser_value;
        case 'treeselect':
            if (strlen($browser_value) > 3) {
                return substr($browser_value, 0, 3);
            }
            return $browser_value;
        default:
            return $browser_value;
    }
}

// 테스트 값들
$test_MY_type = $first_no;
$test_PN_type = '69961';
$test_MY_Fsd = '69361';
$test_MY_amount = '1000';

echo "HTML 값들:<br>";
echo "- MY_type: $test_MY_type<br>";
echo "- PN_type: $test_PN_type<br>";
echo "- MY_Fsd: $test_MY_Fsd<br>";
echo "- MY_amount: $test_MY_amount<br><br>";

echo "매핑된 DB 값들:<br>";
$mapped_style = mapCadarokBrowserToDatabase($test_MY_type, 'style');
$mapped_section = mapCadarokBrowserToDatabase($test_MY_Fsd, 'section');
$mapped_quantity = mapCadarokBrowserToDatabase($test_MY_amount, 'quantity');
$mapped_treeselect = mapCadarokBrowserToDatabase($test_PN_type, 'treeselect');

echo "- style: $mapped_style<br>";
echo "- section: $mapped_section<br>";
echo "- quantity: $mapped_quantity<br>";
echo "- treeselect: $mapped_treeselect<br><br>";

// 4. 실제 가격 조회 테스트
echo "<h3>4. 실제 가격 조회 테스트</h3>";
$price_query = "SELECT * FROM $TABLE WHERE 
                style='$mapped_style' AND 
                Section='$mapped_section' AND 
                quantity='$mapped_quantity' AND 
                TreeSelect='$mapped_treeselect'";

echo "쿼리: $price_query<br><br>";

$price_result = mysqli_query($db, $price_query);
if ($price_result && mysqli_num_rows($price_result) > 0) {
    $price_row = mysqli_fetch_array($price_result);
    echo "✅ 가격 찾음: {$price_row['money']}원<br>";
} else {
    echo "❌ 가격 못 찾음<br>";
    echo "MySQL 에러: " . mysqli_error($db) . "<br>";
}
?>