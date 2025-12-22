<?php
session_start();
$session_id = session_id();

require_once('../lib/func.php');
$connect = dbconn();

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

echo "<h2>shop_temp 테이블 구조 확인</h2>";

// 테이블 컬럼 확인
$query = "SHOW COLUMNS FROM shop_temp";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<h3>테이블 컬럼 목록:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $option_columns = ['coating_enabled', 'coating_type', 'coating_price', 
                      'folding_enabled', 'folding_type', 'folding_price',
                      'creasing_enabled', 'creasing_lines', 'creasing_price',
                      'additional_options_total'];
    $found_columns = [];
    
    while ($row = mysqli_fetch_array($result)) {
        $highlight = in_array($row['Field'], $option_columns) ? "style='background-color: yellow;'" : "";
        echo "<tr $highlight>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
        
        if (in_array($row['Field'], $option_columns)) {
            $found_columns[] = $row['Field'];
        }
    }
    echo "</table>";
    
    echo "<h3>추가 옵션 컬럼 확인:</h3>";
    foreach ($option_columns as $col) {
        if (in_array($col, $found_columns)) {
            echo "✅ $col - <span style='color: green;'>존재</span><br>";
        } else {
            echo "❌ $col - <span style='color: red;'>없음</span><br>";
        }
    }
} else {
    echo "테이블 조회 실패: " . mysqli_error($connect);
}

// 현재 세션의 장바구니 데이터 확인
echo "<h2>현재 장바구니 데이터 확인 (session_id: $session_id)</h2>";

$query = "SELECT * FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC";
$result = mysqli_query($connect, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($data = mysqli_fetch_array($result)) {
        echo "<h3>아이템 #{$data['no']}</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>필드</th><th>값</th></tr>";
        
        // 기본 정보
        echo "<tr><td>product_type</td><td>{$data['product_type']}</td></tr>";
        echo "<tr><td>st_price</td><td>" . number_format($data['st_price']) . "원</td></tr>";
        echo "<tr><td>st_price_vat</td><td>" . number_format($data['st_price_vat']) . "원</td></tr>";
        
        // 추가 옵션 정보
        echo "<tr style='background-color: #f0f0f0;'><td colspan='2'><strong>추가 옵션 정보:</strong></td></tr>";
        
        $option_fields = [
            'coating_enabled' => '코팅 활성화',
            'coating_type' => '코팅 타입',
            'coating_price' => '코팅 가격',
            'folding_enabled' => '접지 활성화',
            'folding_type' => '접지 타입',
            'folding_price' => '접지 가격',
            'creasing_enabled' => '오시 활성화',
            'creasing_lines' => '오시 줄수',
            'creasing_price' => '오시 가격',
            'additional_options_total' => '추가옵션 총액'
        ];
        
        foreach ($option_fields as $field => $label) {
            if (isset($data[$field])) {
                $value = $data[$field];
                if (strpos($field, 'price') !== false || $field == 'additional_options_total') {
                    $value = number_format($value) . '원';
                }
                echo "<tr><td>$label ($field)</td><td>$value</td></tr>";
            } else {
                echo "<tr><td>$label ($field)</td><td style='color: red;'>필드 없음</td></tr>";
            }
        }
        
        echo "</table><br>";
    }
} else {
    echo "장바구니가 비어있습니다.";
}

mysqli_close($connect);
?>

<br><br>
<a href="basket.php">장바구니로 돌아가기</a>