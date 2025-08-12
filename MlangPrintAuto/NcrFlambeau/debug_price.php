<?php
// 양식지 가격 계산 디버그 파일
echo "<h2>🔍 양식지 가격 계산 디버그</h2>";

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

echo "<h3>1. 데이터베이스 연결 상태</h3>";
if ($db) {
    echo "✅ 데이터베이스 연결 성공<br>";
} else {
    echo "❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "<br>";
    exit;
}

echo "<h3>2. 양식지 테이블 존재 확인</h3>";
$TABLE = "MlangPrintAuto_ncrflambeau";
$table_check = mysqli_query($db, "SHOW TABLES LIKE '$TABLE'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ $TABLE 테이블 존재<br>";
} else {
    echo "❌ $TABLE 테이블이 존재하지 않음<br>";
    
    // 다른 가능한 테이블명들 확인
    $possible_tables = ['mlangprintauto_ncrflambeau', 'MlangPrintAuto_NcrFlambeau', 'ncrflambeau'];
    foreach ($possible_tables as $table_name) {
        $check = mysqli_query($db, "SHOW TABLES LIKE '$table_name'");
        if (mysqli_num_rows($check) > 0) {
            echo "🔍 발견된 유사 테이블: $table_name<br>";
        }
    }
}

echo "<h3>3. 테이블 구조 확인</h3>";
$structure_query = mysqli_query($db, "DESCRIBE $TABLE");
if ($structure_query) {
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr><th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($field = mysqli_fetch_assoc($structure_query)) {
        echo "<tr>";
        echo "<td>" . $field['Field'] . "</td>";
        echo "<td>" . $field['Type'] . "</td>";
        echo "<td>" . $field['Null'] . "</td>";
        echo "<td>" . $field['Key'] . "</td>";
        echo "<td>" . $field['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ 테이블 구조 조회 실패: " . mysqli_error($db) . "<br>";
}

echo "<h3>4. 샘플 데이터 확인</h3>";
$sample_query = "SELECT * FROM $TABLE LIMIT 10";
$sample_result = mysqli_query($db, $sample_query);

if ($sample_result) {
    $count = mysqli_num_rows($sample_result);
    echo "✅ 샘플 데이터 조회 성공, 총 {$count}개 레코드<br><br>";
    
    if ($count > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr><th>style</th><th>Section</th><th>TreeSelect</th><th>quantity</th><th>money</th><th>DesignMoney</th></tr>";
        
        while ($row = mysqli_fetch_assoc($sample_result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['style'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['Section'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['TreeSelect'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['quantity'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['money'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['DesignMoney'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ 테이블에 데이터가 없습니다.</p>";
    }
} else {
    echo "❌ 샘플 데이터 조회 실패: " . mysqli_error($db) . "<br>";
}

echo "<h3>5. 카테고리 데이터 확인</h3>";
$cate_table = "MlangPrintAuto_transactionCate";
$cate_query = "SELECT no, title FROM $cate_table WHERE Ttable='NcrFlambeau' ORDER BY no ASC";
$cate_result = mysqli_query($db, $cate_query);

if ($cate_result) {
    echo "<h4>구분 (BigNo = '0'):</h4>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr><th>번호</th><th>제목</th></tr>";
    
    while ($row = mysqli_fetch_assoc($cate_result)) {
        echo "<tr>";
        echo "<td>" . $row['no'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>6. 실제 가격 계산 테스트</h3>";
// 첫 번째 데이터로 테스트
$test_query = "SELECT * FROM $TABLE LIMIT 1";
$test_result = mysqli_query($db, $test_query);

if ($test_result && mysqli_num_rows($test_result) > 0) {
    $test_data = mysqli_fetch_assoc($test_result);
    
    echo "<h4>테스트 조건:</h4>";
    echo "<ul>";
    echo "<li>style: " . $test_data['style'] . "</li>";
    echo "<li>Section: " . $test_data['Section'] . "</li>";
    echo "<li>TreeSelect: " . $test_data['TreeSelect'] . "</li>";
    echo "<li>quantity: " . $test_data['quantity'] . "</li>";
    echo "</ul>";
    
    // 공통함수로 가격 계산 테스트
    $conditions = [
        'style' => $test_data['style'],
        'Section' => $test_data['Section'],
        'TreeSelect' => $test_data['TreeSelect'],
        'quantity' => $test_data['quantity']
    ];
    
    echo "<h4>가격 계산 결과:</h4>";
    $price_result = calculateProductPrice($db, $TABLE, $conditions, 'total');
    
    if ($price_result) {
        echo "<ul>";
        echo "<li>기본 가격: " . number_format($price_result['base_price']) . "원</li>";
        echo "<li>디자인 비용: " . number_format($price_result['design_price']) . "원</li>";
        echo "<li>총 가격: " . number_format($price_result['total_price']) . "원</li>";
        echo "<li>부가세 포함: " . number_format($price_result['total_with_vat']) . "원</li>";
        echo "</ul>";
    } else {
        echo "❌ 가격 계산 실패<br>";
    }
}

echo "<h3>7. AJAX 파일 직접 테스트</h3>";
if (!empty($test_data)) {
    $test_url = "calculate_price_ajax.php?MY_type=" . $test_data['style'] . 
                "&MY_Fsd=" . $test_data['Section'] . 
                "&PN_type=" . $test_data['TreeSelect'] . 
                "&MY_amount=" . $test_data['quantity'] . 
                "&ordertype=total";
    
    echo "<p><strong>테스트 URL:</strong></p>";
    echo "<p><a href='$test_url' target='_blank'>$test_url</a></p>";
}

mysqli_close($db);
?>

<style>
table {
    margin: 10px 0;
    font-size: 12px;
}
th {
    background-color: #f0f0f0;
    font-weight: bold;
}
td, th {
    padding: 5px;
    border: 1px solid #ccc;
}
</style>