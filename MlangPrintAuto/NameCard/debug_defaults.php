<?php
// 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 세션 및 기본 설정
check_session();
check_db_connection($db);
mysqli_set_charset($db, "utf8");

echo "<h2>🔍 명함 기본값 디버그</h2>";

// 기본값 설정 로직 (메인 페이지와 동일)
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

echo "<h3>📊 초기 기본값:</h3>";
echo "<pre>";
print_r($default_values);
echo "</pre>";

// 첫 번째 명함 종류 가져오기 (일반명함(쿠폰) 우선)
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='NameCard' AND BigNo='0' 
               ORDER BY CASE WHEN title LIKE '%일반명함%' THEN 1 ELSE 2 END, no ASC 
               LIMIT 1";
echo "<h3>🔍 명함 종류 쿼리:</h3>";
echo "<code>$type_query</code><br><br>";

$type_result = mysqli_query($db, $type_query);
if ($type_row = mysqli_fetch_assoc($type_result)) {
    $default_values['MY_type'] = $type_row['no'];
    echo "<h3>✅ 선택된 명함 종류:</h3>";
    echo "번호: " . $type_row['no'] . "<br>";
    echo "이름: " . $type_row['title'] . "<br><br>";
    
    // 해당 명함 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                      WHERE Ttable='NameCard' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    echo "<h3>🔍 명함 재질 쿼리:</h3>";
    echo "<code>$section_query</code><br><br>";
    
    $section_result = mysqli_query($db, $section_query);
    if ($section_row = mysqli_fetch_assoc($section_result)) {
        $default_values['Section'] = $section_row['no'];
        echo "<h3>✅ 선택된 명함 재질:</h3>";
        echo "번호: " . $section_row['no'] . "<br>";
        echo "이름: " . $section_row['title'] . "<br><br>";
        
        // 해당 조합의 기본 수량 가져오기 (500매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM MlangPrintAuto_namecard 
                          WHERE style='" . $type_row['no'] . "' AND Section='" . $section_row['no'] . "' 
                          ORDER BY CASE WHEN quantity='500' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC 
                          LIMIT 1";
        echo "<h3>🔍 수량 쿼리:</h3>";
        echo "<code>$quantity_query</code><br><br>";
        
        $quantity_result = mysqli_query($db, $quantity_query);
        if ($quantity_row = mysqli_fetch_assoc($quantity_result)) {
            $default_values['MY_amount'] = $quantity_row['quantity'];
            echo "<h3>✅ 선택된 수량:</h3>";
            echo "수량: " . $quantity_row['quantity'] . "매<br><br>";
        } else {
            echo "<h3>❌ 수량 조회 실패</h3>";
            echo "오류: " . mysqli_error($db) . "<br><br>";
        }
    } else {
        echo "<h3>❌ 명함 재질 조회 실패</h3>";
        echo "오류: " . mysqli_error($db) . "<br><br>";
    }
} else {
    echo "<h3>❌ 명함 종류 조회 실패</h3>";
    echo "오류: " . mysqli_error($db) . "<br><br>";
}

echo "<h3>🎯 최종 기본값:</h3>";
echo "<pre>";
print_r($default_values);
echo "</pre>";

echo "<h3>📋 모든 명함 종류:</h3>";
$all_types_query = "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE Ttable='NameCard' AND BigNo='0' ORDER BY no ASC";
$all_types_result = mysqli_query($db, $all_types_query);
echo "<table border='1'>";
echo "<tr><th>번호</th><th>이름</th></tr>";
while ($row = mysqli_fetch_assoc($all_types_result)) {
    echo "<tr><td>" . $row['no'] . "</td><td>" . $row['title'] . "</td></tr>";
}
echo "</table>";

mysqli_close($db);
?>