<?php
// 카다록 매핑 테스트 도구
include "../NameCard/db_ajax.php";

echo "<h2>카다록 매핑 테스트</h2>";

$TABLE = "MlangPrintAuto_cadarok";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 1. 카다록 카테고리 구조 확인
echo "<h3>1. 카다록 카테고리 구조</h3>";
$category_query = "SELECT * FROM $GGTABLE WHERE Ttable='cadarok' ORDER BY BigNo, no";
$category_result = mysqli_query($db, $category_query);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>TreeNo</th><th>역할</th></tr>";
while ($row = mysqli_fetch_array($category_result)) {
    $role = "";
    if ($row['BigNo'] == '0') {
        $role = "최상위 (카다록 종류)";
    } else {
        $role = "하위 옵션";
    }
    
    echo "<tr>";
    echo "<td>{$row['no']}</td>";
    echo "<td>{$row['title']}</td>";
    echo "<td>{$row['BigNo']}</td>";
    echo "<td>" . ($row['TreeNo'] ?? 'NULL') . "</td>";
    echo "<td>$role</td>";
    echo "</tr>";
}
echo "</table>";

// 2. 실제 가격 데이터 구조 확인
echo "<h3>2. 실제 가격 데이터 구조 (처음 20개)</h3>";
$price_query = "SELECT * FROM $TABLE ORDER BY style, Section, quantity, TreeSelect LIMIT 20";
$price_result = mysqli_query($db, $price_query);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>style</th><th>Section</th><th>quantity</th><th>TreeSelect</th><th>money</th><th>DesignMoney</th></tr>";
while ($row = mysqli_fetch_array($price_result)) {
    echo "<tr>";
    echo "<td>{$row['style']}</td>";
    echo "<td>{$row['Section']}</td>";
    echo "<td>{$row['quantity']}</td>";
    echo "<td>{$row['TreeSelect']}</td>";
    echo "<td>" . number_format($row['money'] ?? 0) . "</td>";
    echo "<td>" . number_format($row['DesignMoney'] ?? 0) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. 매핑 테스트
echo "<h3>3. 매핑 테스트</h3>";

// 매핑 함수
function mapCadarokBrowserToDatabase($browser_value, $type) {
    switch ($type) {
        case 'style':
            if (strlen($browser_value) > 3) {
                return substr($browser_value, 0, 3);
            }
            return $browser_value;
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

// 테스트 케이스들
$test_cases = [
    [
        'name' => '테스트 1',
        'MY_type' => '69161',
        'MY_Fsd' => '69761', 
        'MY_amount' => '1000',
        'PN_type' => '69861'
    ],
    [
        'name' => '테스트 2',
        'MY_type' => '69161',
        'MY_Fsd' => '69861', 
        'MY_amount' => '2000',
        'PN_type' => '72661'
    ]
];

foreach ($test_cases as $test) {
    echo "<h4>{$test['name']}</h4>";
    
    // 원본 값
    echo "<strong>원본 값:</strong><br>";
    echo "MY_type: {$test['MY_type']}<br>";
    echo "MY_Fsd: {$test['MY_Fsd']}<br>";
    echo "MY_amount: {$test['MY_amount']}<br>";
    echo "PN_type: {$test['PN_type']}<br><br>";
    
    // 매핑된 값
    $mapped_style = mapCadarokBrowserToDatabase($test['MY_type'], 'style');
    $mapped_section = mapCadarokBrowserToDatabase($test['MY_Fsd'], 'section');
    $mapped_quantity = mapCadarokBrowserToDatabase($test['MY_amount'], 'quantity');
    $mapped_treeselect = mapCadarokBrowserToDatabase($test['PN_type'], 'treeselect');
    
    echo "<strong>매핑된 값:</strong><br>";
    echo "style: $mapped_style<br>";
    echo "section: $mapped_section<br>";
    echo "quantity: $mapped_quantity<br>";
    echo "treeselect: $mapped_treeselect<br><br>";
    
    // 쿼리 실행
    $query = "SELECT * FROM $TABLE WHERE style=? AND Section=? AND quantity=? AND TreeSelect=?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'ssss', $mapped_style, $mapped_section, $mapped_quantity, $mapped_treeselect);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    echo "<strong>쿼리:</strong> $query<br>";
    echo "<strong>바인딩:</strong> style=$mapped_style, Section=$mapped_section, quantity=$mapped_quantity, TreeSelect=$mapped_treeselect<br>";
    
    $row = mysqli_fetch_array($result);
    if ($row) {
        echo "<strong>✅ 결과 찾음:</strong><br>";
        echo "- 인쇄비: " . number_format($row['money']) . "원<br>";
        echo "- 디자인비: " . number_format($row['DesignMoney'] ?? 0) . "원<br>";
    } else {
        echo "<strong>❌ 결과 없음</strong><br>";
        
        // 비슷한 데이터 찾기
        echo "<strong>비슷한 데이터 검색:</strong><br>";
        $similar_query = "SELECT * FROM $TABLE WHERE style=? LIMIT 5";
        $similar_stmt = mysqli_prepare($db, $similar_query);
        mysqli_stmt_bind_param($similar_stmt, 's', $mapped_style);
        mysqli_stmt_execute($similar_stmt);
        $similar_result = mysqli_stmt_get_result($similar_stmt);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>style</th><th>Section</th><th>quantity</th><th>TreeSelect</th></tr>";
        while ($similar_row = mysqli_fetch_array($similar_result)) {
            echo "<tr>";
            echo "<td>{$similar_row['style']}</td>";
            echo "<td>{$similar_row['Section']}</td>";
            echo "<td>{$similar_row['quantity']}</td>";
            echo "<td>{$similar_row['TreeSelect']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
}

// 4. 실제 브라우저에서 보내는 값 확인
echo "<h3>4. 실제 브라우저 값 확인</h3>";
echo "<p>브라우저 개발자 도구에서 실제로 어떤 값들이 전송되는지 확인해보세요:</p>";
echo "<ol>";
echo "<li>카다록 페이지에서 F12 → Network 탭 열기</li>";
echo "<li>옵션 변경 후 가격 계산 버튼 클릭</li>";
echo "<li>price_cal.php 요청 클릭 → Request 탭에서 실제 전송된 값 확인</li>";
echo "</ol>";

mysqli_close($db);
?>

<style>
table {
    margin: 10px 0;
}
th, td {
    padding: 5px 10px;
    text-align: left;
}
th {
    background-color: #f0f0f0;
}
hr {
    margin: 20px 0;
}
</style>