<?php
// 매핑 관계 확인
include "../../db_auto.php";

echo "<h2>상품권 매핑 관계 확인</h2>";

// 1. transactionCate에서 상품권 데이터 확인
echo "<h3>1. MlangPrintAuto_transactionCate 상품권 데이터:</h3>";
$cate_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='MerchandiseBond' ORDER BY BigNo, no";
$cate_result = mysqli_query($db, $cate_query);

echo "<table border='1'>";
echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>Ttable</th></tr>";
while ($row = mysqli_fetch_assoc($cate_result)) {
    echo "<tr>";
    echo "<td>{$row['no']}</td>";
    echo "<td>{$row['title']}</td>";
    echo "<td>{$row['BigNo']}</td>";
    echo "<td>{$row['Ttable']}</td>";
    echo "</tr>";
}
echo "</table><br>";

// 2. MerchandiseBond에서 실제 가격 데이터 확인
echo "<h3>2. MlangPrintAuto_MerchandiseBond 가격 데이터:</h3>";
$price_query = "SELECT DISTINCT style, Section FROM MlangPrintAuto_MerchandiseBond ORDER BY style, Section";
$price_result = mysqli_query($db, $price_query);

echo "<table border='1'>";
echo "<tr><th>style</th><th>Section</th></tr>";
while ($row = mysqli_fetch_assoc($price_result)) {
    echo "<tr>";
    echo "<td>{$row['style']}</td>";
    echo "<td>{$row['Section']}</td>";
    echo "</tr>";
}
echo "</table><br>";

// 3. 매핑 관계 추정
echo "<h3>3. 매핑 관계 분석:</h3>";
echo "<p><strong>현재 상황:</strong></p>";
echo "<ul>";
echo "<li>브라우저에서 전달: MY_type=61461, PN_type=5</li>";
echo "<li>데이터베이스에 실제 존재: style='614', Section='615'</li>";
echo "</ul>";

echo "<p><strong>추정되는 매핑:</strong></p>";
echo "<ul>";
echo "<li>transactionCate의 no=61461 → MerchandiseBond의 style=614 (앞의 614만 사용?)</li>";
echo "<li>transactionCate의 no=5 → MerchandiseBond의 Section=615 (다른 매핑 규칙?)</li>";
echo "</ul>";

mysqli_close($db);
?>

<h3>4. 해결 방안:</h3>
<p><strong>방안 1: 매핑 함수 생성</strong></p>
<pre>
function mapTransactionToPrice($transactionId) {
    // 61461 → 614 변환 로직
    // 5 → 615 변환 로직
}
</pre>

<p><strong>방안 2: 데이터 통일</strong></p>
<pre>
// transactionCate의 no 값을 MerchandiseBond에 맞게 수정
// 또는 MerchandiseBond의 값을 transactionCate에 맞게 수정
</pre>

<p><strong>방안 3: 테스트 데이터 추가</strong></p>
<pre>
INSERT INTO mlangprintauto_merchandisebond 
(style, Section, quantity, money, DesignMoney, POtype) VALUES
('61461', '5', 500, '35000', '15000', '1'),
('61461', '5', 1000, '70000', '15000', '1');
</pre>