<?php
// 장바구니 디버그 파일
include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

echo "<h2>장바구니 디버그</h2>";

// shop_temp 테이블 구조 확인
echo "<h3>shop_temp 테이블 구조:</h3>";
$desc_query = "DESCRIBE shop_temp";
$desc_result = mysqli_query($db, $desc_query);
if ($desc_result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_array($desc_result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "테이블 구조 조회 실패: " . mysqli_error($db);
}

// 테스트 데이터로 INSERT 시도
echo "<h3>테스트 INSERT:</h3>";
$test_query = "INSERT INTO shop_temp (
    NC_type, NC_name, NC_size, NC_color, NC_amount, NC_design, 
    NC_price, NC_vat_price, NC_comment, NC_date, NC_ip
) VALUES (
    'ncrflambeau',
    '양식(100매철)',
    '계약서(A4).기타서식(A4)',
    '1도',
    '60권',
    '디자인+인쇄',
    '150000',
    '165000',
    '테스트 코멘트',
    NOW(),
    '127.0.0.1'
)";

echo "<pre>$test_query</pre>";

if (mysqli_query($db, $test_query)) {
    echo "<p style='color: green;'>테스트 INSERT 성공!</p>";
    $insert_id = mysqli_insert_id($db);
    echo "<p>Insert ID: $insert_id</p>";
    
    // 방금 삽입한 데이터 확인
    $select_query = "SELECT * FROM shop_temp WHERE no = $insert_id";
    $select_result = mysqli_query($db, $select_query);
    if ($select_result && $row = mysqli_fetch_array($select_result)) {
        echo "<h4>삽입된 데이터:</h4>";
        echo "<pre>" . print_r($row, true) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>테스트 INSERT 실패: " . mysqli_error($db) . "</p>";
}

mysqli_close($db);
?>