<?php
// 카다록 가격 계산 디버그 파일
header('Content-Type: text/html; charset=utf-8');

echo "<h2>카다록 가격 계산 디버그</h2>";

// 데이터베이스 연결 테스트
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$connect = mysqli_connect($host, $user, $password, $dataname);
if (!$connect) {
    echo "<p style='color: red;'>❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ 데이터베이스 연결 성공</p>";
}

mysqli_set_charset($connect, "utf8");

// 테이블 존재 확인
$tables_to_check = ['MlangPrintAuto_transactionCate', 'MlangPrintAuto_cadarok'];
foreach ($tables_to_check as $table) {
    $result = mysqli_query($connect, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✅ 테이블 '$table' 존재</p>";
        
        // 데이터 개수 확인
        $count_result = mysqli_query($connect, "SELECT COUNT(*) as count FROM $table");
        $count_row = mysqli_fetch_assoc($count_result);
        echo "<p>📊 '$table' 데이터 개수: {$count_row['count']}개</p>";
        
        if ($table == 'MlangPrintAuto_transactionCate') {
            // 카다록 관련 카테고리 확인
            $cat_result = mysqli_query($connect, "SELECT no, title FROM $table WHERE Ttable='cadarok' AND BigNo='0' ORDER BY no ASC LIMIT 5");
            echo "<p><strong>카다록 카테고리:</strong></p><ul>";
            while ($row = mysqli_fetch_assoc($cat_result)) {
                echo "<li>{$row['no']}: {$row['title']}</li>";
            }
            echo "</ul>";
        }
        
        if ($table == 'MlangPrintAuto_cadarok') {
            // 카다록 가격 데이터 확인
            $price_result = mysqli_query($connect, "SELECT style, Section, TreeSelect, quantity, money FROM $table ORDER BY no ASC LIMIT 5");
            echo "<p><strong>카다록 가격 데이터:</strong></p><ul>";
            while ($row = mysqli_fetch_assoc($price_result)) {
                echo "<li>Style: {$row['style']}, Section: {$row['Section']}, TreeSelect: {$row['TreeSelect']}, Quantity: {$row['quantity']}, Money: {$row['money']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>❌ 테이블 '$table' 없음</p>";
    }
}

// 샘플 가격 계산 테스트
echo "<h3>샘플 가격 계산 테스트</h3>";
$test_params = [
    'MY_type' => '691',
    'MY_Fsd' => '692', 
    'PN_type' => '699',
    'MY_amount' => '1000',
    'ordertype' => 'print'
];

echo "<p><strong>테스트 파라미터:</strong></p>";
foreach ($test_params as $key => $value) {
    echo "<p>$key: $value</p>";
}

// 실제 가격 계산 로직 실행
$query = "SELECT * FROM MlangPrintAuto_cadarok WHERE style = ? AND Section = ? AND TreeSelect = ? AND quantity = ?";
$stmt = mysqli_prepare($connect, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssss", $test_params['MY_type'], $test_params['MY_Fsd'], $test_params['PN_type'], $test_params['MY_amount']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo "<p style='color: green;'>✅ 가격 데이터 찾음:</p>";
        echo "<ul>";
        echo "<li>기본 가격: " . number_format($row['money']) . "원</li>";
        echo "<li>디자인비: " . number_format($row['DesignMoney']) . "원</li>";
        echo "</ul>";
        
        $print_price = (int)$row['money'];
        $design_price = (int)$row['DesignMoney'];
        $total_price = $print_price + $design_price;
        $vat = (int)round($total_price * 0.1);
        $total_with_vat = $total_price + $vat;
        
        echo "<p><strong>계산 결과:</strong></p>";
        echo "<ul>";
        echo "<li>인쇄비: " . number_format($print_price) . "원</li>";
        echo "<li>디자인비: " . number_format($design_price) . "원</li>";
        echo "<li>소계: " . number_format($total_price) . "원</li>";
        echo "<li>부가세: " . number_format($vat) . "원</li>";
        echo "<li>총액: " . number_format($total_with_vat) . "원</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ 해당 조건의 가격 데이터 없음</p>";
        
        // 비슷한 데이터 찾기
        $similar_query = "SELECT * FROM MlangPrintAuto_cadarok WHERE style = ? LIMIT 5";
        $similar_stmt = mysqli_prepare($connect, $similar_query);
        mysqli_stmt_bind_param($similar_stmt, "s", $test_params['MY_type']);
        mysqli_stmt_execute($similar_stmt);
        $similar_result = mysqli_stmt_get_result($similar_stmt);
        
        echo "<p><strong>해당 스타일의 다른 데이터:</strong></p><ul>";
        while ($similar_row = mysqli_fetch_assoc($similar_result)) {
            echo "<li>Section: {$similar_row['Section']}, TreeSelect: {$similar_row['TreeSelect']}, Quantity: {$similar_row['quantity']}, Money: " . number_format($similar_row['money']) . "원</li>";
        }
        echo "</ul>";
        mysqli_stmt_close($similar_stmt);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color: red;'>❌ SQL 준비 실패: " . mysqli_error($connect) . "</p>";
}

mysqli_close($connect);

echo "<hr>";
echo "<p><strong>다음 단계:</strong></p>";
echo "<ol>";
echo "<li>브라우저 개발자 도구(F12) → Console 탭에서 JavaScript 오류 확인</li>";
echo "<li>Network 탭에서 price_cal.php 요청/응답 확인</li>";
echo "<li>위 테스트 결과를 바탕으로 문제점 파악</li>";
echo "</ol>";
?>