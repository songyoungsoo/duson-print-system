<?php
// 카다록 시스템 데이터베이스 구조 및 매핑 확인
include "../NameCard/db_ajax.php";

echo "<h2>카다록 시스템 데이터베이스 분석</h2>";

// 1. 데이터베이스 연결 확인
echo "<h3>1. 데이터베이스 연결 상태</h3>";
if ($db) {
    echo "✅ 데이터베이스 연결 성공<br>";
} else {
    echo "❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "<br>";
    exit;
}

$TABLE = "MlangPrintAuto_cadarok";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 2. 카다록 테이블 구조 확인
echo "<h3>2. 카다록 테이블 구조</h3>";
$structure_query = "DESCRIBE $TABLE";
$structure_result = mysqli_query($db, $structure_query);

if ($structure_result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_array($structure_result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ 테이블 구조 조회 실패<br>";
}

// 3. 카다록 카테고리 데이터 확인
echo "<h3>3. 카다록 카테고리 데이터</h3>";
$category_query = "SELECT * FROM $GGTABLE WHERE Ttable='cadarok' ORDER BY BigNo, no";
$category_result = mysqli_query($db, $category_query);

if ($category_result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>TreeNo</th><th>Ttable</th></tr>";
    while ($row = mysqli_fetch_array($category_result)) {
        echo "<tr>";
        echo "<td>{$row['no']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['BigNo']}</td>";
        echo "<td>" . ($row['TreeNo'] ?? 'NULL') . "</td>";
        echo "<td>{$row['Ttable']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ 카테고리 데이터 조회 실패<br>";
}

// 4. 카다록 가격 데이터 샘플 확인
echo "<h3>4. 카다록 가격 데이터 샘플 (처음 10개)</h3>";
$price_query = "SELECT * FROM $TABLE ORDER BY style, Section, quantity, TreeSelect LIMIT 10";
$price_result = mysqli_query($db, $price_query);

if ($price_result) {
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
} else {
    echo "❌ 가격 데이터 조회 실패<br>";
}

// 5. 고유한 값들 확인
echo "<h3>5. 각 필드의 고유한 값들</h3>";

$fields = ['style', 'Section', 'quantity', 'TreeSelect'];
foreach ($fields as $field) {
    echo "<h4>{$field} 필드의 고유한 값들:</h4>";
    $unique_query = "SELECT DISTINCT $field FROM $TABLE ORDER BY $field";
    $unique_result = mysqli_query($db, $unique_query);
    
    if ($unique_result) {
        $values = [];
        while ($row = mysqli_fetch_array($unique_result)) {
            $values[] = $row[$field];
        }
        echo implode(', ', $values) . "<br><br>";
    } else {
        echo "❌ {$field} 고유값 조회 실패<br><br>";
    }
}

// 6. 테스트 쿼리 실행
echo "<h3>6. 테스트 쿼리 실행</h3>";
$test_params = [
    'MY_type' => '61461',
    'MY_Fsd' => '61462', 
    'MY_amount' => '1000',
    'PN_type' => '61463'
];

echo "테스트 파라미터:<br>";
foreach ($test_params as $key => $value) {
    echo "- $key: $value<br>";
}

$test_query = "SELECT * FROM $TABLE WHERE style=? AND Section=? AND quantity=? AND TreeSelect=?";
$test_stmt = mysqli_prepare($db, $test_query);
mysqli_stmt_bind_param($test_stmt, 'ssss', $test_params['MY_type'], $test_params['MY_Fsd'], $test_params['MY_amount'], $test_params['PN_type']);
mysqli_stmt_execute($test_stmt);
$test_result = mysqli_stmt_get_result($test_stmt);

echo "<br>실행된 쿼리: $test_query<br>";
echo "바인딩된 값: style={$test_params['MY_type']}, Section={$test_params['MY_Fsd']}, quantity={$test_params['MY_amount']}, TreeSelect={$test_params['PN_type']}<br>";

$test_row = mysqli_fetch_array($test_result);
if ($test_row) {
    echo "<br>✅ 테스트 쿼리 결과 찾음:<br>";
    echo "- 인쇄비: " . number_format($test_row['money']) . "원<br>";
    echo "- 디자인비: " . number_format($test_row['DesignMoney'] ?? 0) . "원<br>";
} else {
    echo "<br>❌ 테스트 쿼리 결과 없음<br>";
    echo "<strong>가능한 원인:</strong><br>";
    echo "1. 해당 조합의 데이터가 데이터베이스에 없음<br>";
    echo "2. 매개변수 값이 실제 데이터와 일치하지 않음<br>";
    echo "3. 데이터 타입 불일치<br>";
}

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
</style>