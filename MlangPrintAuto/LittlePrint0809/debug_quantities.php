<?php
// 수량 로드 디버그 파일
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

echo "<h2>수량 로드 디버그</h2>";

// 테스트 파라미터
$style = '590';        // 소량포스터
$Section = '610';      // 국2절
$TreeSelect = '679';   // 80모조

echo "<h3>테스트 파라미터:</h3>";
echo "style: $style<br>";
echo "Section: $Section<br>";
echo "TreeSelect: $TreeSelect<br>";

$TABLE = "MlangPrintAuto_littleprint";

// 쿼리 실행
$query = "SELECT DISTINCT quantity 
          FROM $TABLE 
          WHERE style='" . mysqli_real_escape_string($db, $style) . "' 
          AND Section='" . mysqli_real_escape_string($db, $Section) . "' 
          AND TreeSelect='" . mysqli_real_escape_string($db, $TreeSelect) . "'
          AND quantity IS NOT NULL 
          ORDER BY CAST(quantity AS UNSIGNED) ASC";

echo "<h3>실행 쿼리:</h3>";
echo "<pre>$query</pre>";

$result = mysqli_query($db, $query);

if (!$result) {
    echo "<h3>쿼리 오류:</h3>";
    echo mysqli_error($db);
} else {
    echo "<h3>쿼리 결과:</h3>";
    $quantities = [];
    
    while ($row = mysqli_fetch_array($result)) {
        $quantities[] = [
            'value' => $row['quantity'],
            'text' => format_number($row['quantity']) . '매'
        ];
        echo "quantity: " . $row['quantity'] . " -> " . format_number($row['quantity']) . "매<br>";
    }
    
    echo "<h3>JSON 응답:</h3>";
    echo "<pre>" . json_encode($quantities, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<h3>총 " . count($quantities) . "개의 수량 옵션</h3>";
}

// 테이블 구조 확인
echo "<h3>테이블 구조 확인:</h3>";
$desc_query = "DESCRIBE $TABLE";
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
}

// 전체 데이터 확인
echo "<h3>전체 데이터 확인 (처음 10개):</h3>";
$all_query = "SELECT * FROM $TABLE LIMIT 10";
$all_result = mysqli_query($db, $all_query);
if ($all_result) {
    echo "<table border='1'>";
    $first_row = true;
    while ($row = mysqli_fetch_array($all_result)) {
        if ($first_row) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                if (!is_numeric($key)) {
                    echo "<th>$key</th>";
                }
            }
            echo "</tr>";
            $first_row = false;
        }
        
        echo "<tr>";
        foreach ($row as $key => $value) {
            if (!is_numeric($key)) {
                echo "<td>$value</td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($db);
?>