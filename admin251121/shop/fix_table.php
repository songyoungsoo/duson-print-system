<?php
session_start();
require_once('../lib/func.php');
$connect = dbconn();

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

echo "<h2>shop_temp 테이블 추가 옵션 컬럼 생성</h2>";

// 추가해야 할 컬럼들
$columns_to_add = [
    'coating_enabled' => "INT DEFAULT 0",
    'coating_type' => "VARCHAR(50) DEFAULT NULL",
    'coating_price' => "INT DEFAULT 0",
    'folding_enabled' => "INT DEFAULT 0",
    'folding_type' => "VARCHAR(50) DEFAULT NULL",
    'folding_price' => "INT DEFAULT 0",
    'creasing_enabled' => "INT DEFAULT 0",
    'creasing_lines' => "INT DEFAULT 0",
    'creasing_price' => "INT DEFAULT 0",
    'additional_options_total' => "INT DEFAULT 0"
];

$added_count = 0;
$existing_count = 0;
$error_count = 0;

foreach ($columns_to_add as $column_name => $column_definition) {
    // 컬럼이 이미 존재하는지 확인
    $check_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $check_result = mysqli_query($connect, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        // 컬럼이 없으면 추가
        $add_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        echo "추가 시도: $column_name ... ";
        
        if (mysqli_query($connect, $add_query)) {
            echo "<span style='color: green;'>✅ 성공</span><br>";
            $added_count++;
        } else {
            echo "<span style='color: red;'>❌ 실패 - " . mysqli_error($connect) . "</span><br>";
            $error_count++;
        }
    } else {
        echo "$column_name - <span style='color: blue;'>이미 존재</span><br>";
        $existing_count++;
    }
}

echo "<br>";
echo "<h3>결과 요약:</h3>";
echo "✅ 새로 추가된 컬럼: $added_count 개<br>";
echo "ℹ️ 이미 존재하는 컬럼: $existing_count 개<br>";
echo "❌ 추가 실패: $error_count 개<br>";

// 테이블 구조 확인
echo "<br><h3>최종 테이블 구조:</h3>";
$query = "SHOW COLUMNS FROM shop_temp";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Default</th></tr>";
    
    $option_columns = array_keys($columns_to_add);
    
    while ($row = mysqli_fetch_array($result)) {
        if (in_array($row['Field'], $option_columns)) {
            echo "<tr style='background-color: #e0f7fa;'>";
            echo "<td><strong>{$row['Field']}</strong></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
}

mysqli_close($connect);
?>

<br><br>
<a href="check_table.php">테이블 확인 페이지로</a> | 
<a href="basket.php">장바구니로 돌아가기</a>