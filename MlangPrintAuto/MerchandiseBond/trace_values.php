<?php
// 값의 출처 추적
include "../../db_auto.php";

echo "<h2>상품권 값 출처 추적</h2>";

// 1. 브라우저에서 전달된 값들의 출처 확인
echo "<h3>1. 브라우저에서 전달된 값들:</h3>";
echo "<ul>";
echo "<li><strong>MY_type = 61461</strong> (상품권 종류)</li>";
echo "<li><strong>PN_type = 5</strong> (후가공)</li>";
echo "</ul>";

// 2. MlangPrintAuto_transactionCate에서 해당 값들 찾기
echo "<h3>2. MlangPrintAuto_transactionCate에서 해당 값 확인:</h3>";

// MY_type = 61461 찾기
$my_type_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE no='61461'";
$my_type_result = mysqli_query($db, $my_type_query);

if ($my_type_row = mysqli_fetch_assoc($my_type_result)) {
    echo "<h4>MY_type = 61461:</h4>";
    echo "<table border='1'>";
    echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>Ttable</th></tr>";
    echo "<tr>";
    echo "<td>{$my_type_row['no']}</td>";
    echo "<td>{$my_type_row['title']}</td>";
    echo "<td>{$my_type_row['BigNo']}</td>";
    echo "<td>{$my_type_row['Ttable']}</td>";
    echo "</tr>";
    echo "</table>";
} else {
    echo "<p>❌ MY_type = 61461을 찾을 수 없습니다.</p>";
}

// PN_type = 5 찾기
$pn_type_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE no='5'";
$pn_type_result = mysqli_query($db, $pn_type_query);

if ($pn_type_row = mysqli_fetch_assoc($pn_type_result)) {
    echo "<h4>PN_type = 5:</h4>";
    echo "<table border='1'>";
    echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>Ttable</th></tr>";
    echo "<tr>";
    echo "<td>{$pn_type_row['no']}</td>";
    echo "<td>{$pn_type_row['title']}</td>";
    echo "<td>{$pn_type_row['BigNo']}</td>";
    echo "<td>{$pn_type_row['Ttable']}</td>";
    echo "</tr>";
    echo "</table>";
} else {
    echo "<p>❌ PN_type = 5를 찾을 수 없습니다.</p>";
}

// 3. 상품권 관련 모든 transactionCate 데이터 확인
echo "<h3>3. 상품권 관련 모든 transactionCate 데이터:</h3>";
$all_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='MerchandiseBond' ORDER BY BigNo, no";
$all_result = mysqli_query($db, $all_query);

echo "<table border='1'>";
echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>Ttable</th><th>설명</th></tr>";
while ($row = mysqli_fetch_assoc($all_result)) {
    $description = "";
    if ($row['BigNo'] == '0') {
        $description = "최상위 카테고리 (상품권 종류)";
    } else {
        $description = "하위 카테고리 (후가공 옵션)";
    }
    
    echo "<tr>";
    echo "<td>{$row['no']}</td>";
    echo "<td>{$row['title']}</td>";
    echo "<td>{$row['BigNo']}</td>";
    echo "<td>{$row['Ttable']}</td>";
    echo "<td>$description</td>";
    echo "</tr>";
}
echo "</table>";

// 4. HTML에서 어떻게 로드되는지 확인
echo "<h3>4. HTML에서 로드되는 과정:</h3>";
echo "<ol>";
echo "<li><strong>상품권 종류 드롭다운</strong>: <code>SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='MerchandiseBond' AND BigNo='0'</code></li>";
echo "<li><strong>후가공 드롭다운</strong>: <code>SELECT * FROM MlangPrintAuto_transactionCate WHERE BigNo='선택된상품권종류ID'</code></li>";
echo "</ol>";

// 5. 실제 HTML 생성 시뮬레이션
echo "<h3>5. 실제 HTML 생성 시뮬레이션:</h3>";

echo "<h4>상품권 종류 옵션:</h4>";
$main_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='MerchandiseBond' AND BigNo='0' ORDER BY no ASC";
$main_result = mysqli_query($db, $main_query);

echo "<select name='MY_type'>";
while ($row = mysqli_fetch_assoc($main_result)) {
    echo "<option value='{$row['no']}'>{$row['title']}</option>";
}
echo "</select>";

echo "<h4>첫 번째 상품권 종류의 후가공 옵션:</h4>";
$first_main_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='MerchandiseBond' AND BigNo='0' ORDER BY no ASC LIMIT 1";
$first_main_result = mysqli_query($db, $first_main_query);

if ($first_main_row = mysqli_fetch_assoc($first_main_result)) {
    $first_id = $first_main_row['no'];
    echo "<p>첫 번째 상품권 종류 ID: $first_id ({$first_main_row['title']})</p>";
    
    $sub_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE BigNo='$first_id' ORDER BY no ASC";
    $sub_result = mysqli_query($db, $sub_query);
    
    echo "<select name='PN_type'>";
    while ($row = mysqli_fetch_assoc($sub_result)) {
        echo "<option value='{$row['no']}'>{$row['title']}</option>";
    }
    echo "</select>";
}

mysqli_close($db);
?>

<h3>6. 결론:</h3>
<p><strong>MY_type = 61461</strong>은 다음에서 나왔습니다:</p>
<ul>
<li>사용자가 상품권 종류 드롭다운에서 선택한 값</li>
<li>이 값은 <code>MlangPrintAuto_transactionCate</code> 테이블의 <code>no</code> 필드</li>
<li>조건: <code>Ttable='MerchandiseBond' AND BigNo='0'</code></li>
</ul>

<p><strong>PN_type = 5</strong>는 다음에서 나왔습니다:</p>
<ul>
<li>후가공 드롭다운에서 선택한 값</li>
<li>이 값은 <code>MlangPrintAuto_transactionCate</code> 테이블의 <code>no</code> 필드</li>
<li>조건: <code>BigNo='선택된상품권종류ID'</code></li>
</ul>