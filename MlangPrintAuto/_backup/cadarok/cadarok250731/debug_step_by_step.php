<?php
// 카다록 시스템 단계별 디버깅
echo "<h2>🔍 카다록 시스템 단계별 디버깅</h2>";

// 1. 데이터베이스 연결 확인
echo "<h3>1. 데이터베이스 연결 확인</h3>";
include "../../db_xampp.php";

if ($db) {
    echo "✅ 데이터베이스 연결 성공<br>";
} else {
    echo "❌ 데이터베이스 연결 실패<br>";
    exit;
}

// 2. 카다록 옵션 데이터 확인
echo "<h3>2. 카다록 옵션 데이터 확인</h3>";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 구분 (MY_type) 데이터
$cate_query = "SELECT * FROM $GGTABLE WHERE Ttable='cadarok' AND BigNo='0' ORDER BY no ASC";
$cate_result = mysqli_query($db, $cate_query);

echo "<h4>구분 (MY_type) 옵션:</h4>";
if ($cate_result && mysqli_num_rows($cate_result) > 0) {
    echo "<ul>";
    while ($row = mysqli_fetch_array($cate_result)) {
        echo "<li>no: {$row['no']}, title: {$row['title']}</li>";
    }
    echo "</ul>";
} else {
    echo "❌ 구분 옵션 데이터 없음<br>";
}

// 3. 첫 번째 구분의 하위 옵션들 확인
echo "<h3>3. 첫 번째 구분의 하위 옵션들 확인</h3>";
$first_cate_query = "SELECT * FROM $GGTABLE WHERE Ttable='cadarok' AND BigNo='0' ORDER BY no ASC LIMIT 1";
$first_cate_result = mysqli_query($db, $first_cate_query);

if ($first_cate_result && mysqli_num_rows($first_cate_result) > 0) {
    $first_row = mysqli_fetch_array($first_cate_result);
    $first_no = $first_row['no'];
    
    echo "<p>첫 번째 구분: no={$first_no}, title={$first_row['title']}</p>";
    
    // 규격 (MY_Fsd) 데이터 - BigNo가 첫 번째 구분의 no
    echo "<h4>규격 (MY_Fsd) 옵션:</h4>";
    $size_query = "SELECT * FROM $GGTABLE WHERE BigNo='$first_no' ORDER BY no ASC";
    $size_result = mysqli_query($db, $size_query);
    
    if ($size_result && mysqli_num_rows($size_result) > 0) {
        echo "<ul>";
        while ($row = mysqli_fetch_array($size_result)) {
            echo "<li>no: {$row['no']}, title: {$row['title']}</li>";
        }
        echo "</ul>";
    } else {
        echo "❌ 규격 옵션 데이터 없음<br>";
    }
    
    // 종이종류 (PN_type) 데이터 - TreeNo가 첫 번째 구분의 no
    echo "<h4>종이종류 (PN_type) 옵션:</h4>";
    $paper_query = "SELECT * FROM $GGTABLE WHERE TreeNo='$first_no' ORDER BY no ASC";
    $paper_result = mysqli_query($db, $paper_query);
    
    if ($paper_result && mysqli_num_rows($paper_result) > 0) {
        echo "<ul>";
        while ($row = mysqli_fetch_array($paper_result)) {
            echo "<li>no: {$row['no']}, title: {$row['title']}</li>";
        }
        echo "</ul>";
    } else {
        echo "❌ 종이종류 옵션 데이터 없음<br>";
    }
}

// 4. 카다록 가격 테이블 확인
echo "<h3>4. 카다록 가격 테이블 확인</h3>";
$TABLE = "MlangPrintAuto_cadarok";
$price_sample_query = "SELECT * FROM $TABLE LIMIT 3";
$price_sample_result = mysqli_query($db, $price_sample_query);

if ($price_sample_result && mysqli_num_rows($price_sample_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>style</th><th>Section</th><th>quantity</th><th>TreeSelect</th><th>money</th></tr>";
    while ($row = mysqli_fetch_array($price_sample_result)) {
        echo "<tr>";
        echo "<td>" . ($row['style'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['Section'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['quantity'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['TreeSelect'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['money'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ 카다록 가격 테이블에 데이터 없음<br>";
}

// 5. 실제 가격 계산 테스트
echo "<h3>5. 실제 가격 계산 테스트</h3>";

// 실제 존재하는 첫 번째 데이터로 테스트
$test_query = "SELECT * FROM $TABLE LIMIT 1";
$test_result = mysqli_query($db, $test_query);

if ($test_result && mysqli_num_rows($test_result) > 0) {
    $test_row = mysqli_fetch_array($test_result);
    
    echo "<p><strong>테스트 데이터:</strong></p>";
    echo "<ul>";
    echo "<li>style: {$test_row['style']}</li>";
    echo "<li>Section: {$test_row['Section']}</li>";
    echo "<li>quantity: {$test_row['quantity']}</li>";
    echo "<li>TreeSelect: {$test_row['TreeSelect']}</li>";
    echo "<li>money: {$test_row['money']}</li>";
    echo "</ul>";
    
    // 이 데이터로 price_cal.php 호출 URL 생성
    $test_url = "price_cal.php?ordertype=print&MY_type=69361&PN_type={$test_row['TreeSelect']}&MY_Fsd={$test_row['Section']}&MY_amount={$test_row['quantity']}";
    echo "<p><strong>테스트 URL:</strong> <a href='$test_url' target='_blank'>$test_url</a></p>";
    
    echo "<iframe src='$test_url' width='100%' height='200' style='border: 1px solid #ccc;'></iframe>";
} else {
    echo "❌ 테스트할 데이터가 없습니다.<br>";
}

mysqli_close($db);
?>